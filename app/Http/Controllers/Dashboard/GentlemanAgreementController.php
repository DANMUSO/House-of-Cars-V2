<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HirePurchaseAgreement;
use App\Models\GentlemanAgreement;
use App\Models\HirePurchasePayment;
use App\Models\LoanSetting;
use App\Models\PaymentSchedule;
use App\Models\VehicleInspection;
use App\Models\CarImport;
use App\Models\CustomerVehicle;
use App\Models\InCash;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // ADD THIS LINE
use Carbon\Carbon;

class GentlemanAgreementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gentlemanAgreements = GentlemanAgreement::with(['payments', 'approvedBy', 'carImport', 'customerVehicle'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Collect IDs from InCash, HirePurchaseAgreement, and GentlemanAgreement
        $importedIds = array_merge(
            InCash::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            HirePurchaseAgreement::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            GentlemanAgreement::whereNotNull('imported_id')->pluck('imported_id')->toArray()
        );

        $customerIds = array_merge(
            InCash::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            HirePurchaseAgreement::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            GentlemanAgreement::whereNotNull('customer_id')->pluck('customer_id')->toArray()
        );

        // Filter VehicleInspections where cars are not already in InCash, HirePurchase, or GentlemanAgreement
        $cars = VehicleInspection::with('carsImport', 'customerVehicle')
            ->whereDoesntHave('carsImport', function ($query) use ($importedIds) {
                $query->whereIn('id', $importedIds);
            })
            ->whereDoesntHave('customerVehicle', function ($query) use ($customerIds) {
                $query->whereIn('id', $customerIds);
            })
            ->latest()
            ->get();


        // Group imported and customer cars for display
        $importCars = CarImport::whereIn('id', $gentlemanAgreements->where('car_type', 'import')->pluck('car_id'))->get()->keyBy('id');
        $customerCars = CustomerVehicle::whereIn('id', $gentlemanAgreements->where('car_type', 'customer')->pluck('car_id'))->get()->keyBy('id');


        return view('gentlemanagreement.index', compact('cars', 'gentlemanAgreements', 'importCars', 'customerCars'));
    }

    /**
     * Store method for Gentleman's Agreement (NO INTEREST, SIMPLE PAYMENT PLAN)
     */
    public function store(Request $request)
    {
        try {
            Log::info('Gentleman Agreement Store Request:', $request->all());
            
            $validated = $request->validate([
                'client_name' => 'required|string|max:100',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:100',
                'national_id' => 'required|string|max:20',
                'kra_pin' => 'nullable|string|max:20',
                'phone_numberalt' => 'nullable|string|max:20',
                'emailalt' => 'nullable|string|max:20',
                'vehicle_id' => 'required|string',
                'vehicle_price' => 'required|numeric|min:1',
                'deposit_amount' => 'required|numeric|min:1',
                'duration_months' => 'required|integer',
                'first_due_date' => 'required|date',
            ], [
                'client_name.required' => 'Client name is required.',
                'phone_number.required' => 'Phone number is required.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'national_id.required' => 'National ID is required.',
                'vehicle_id.required' => 'Please select a vehicle.',
                'vehicle_price.required' => 'Vehicle price is required.',
                'vehicle_price.min' => 'Vehicle price must be greater than 0.',
                'deposit_amount.required' => 'Deposit amount is required.',
                'deposit_amount.min' => 'Deposit amount must be greater than 0.',
                'duration_months.required' => 'Payment duration is required.',
                'duration_months.min' => 'Minimum payment duration is 1 month.',
                'duration_months.max' => 'Maximum payment duration is 60 months.',
                'first_due_date.required' => 'First payment due date is required.',
                'first_due_date.after' => 'First payment due date must be after today.',
            ]);

            // Calculate loan details for gentleman's agreement (NO INTEREST)
            $vehiclePrice = $validated['vehicle_price'];
            $depositAmount = $validated['deposit_amount'];
            $depositPercentage = ($depositAmount / $vehiclePrice) * 100;

            // Validate minimum deposit (recommended 10% but not enforced)
            if ($depositPercentage < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum recommended deposit is 1% of vehicle price.',
                    'errors' => [
                        'deposit_amount' => ['Minimum recommended deposit is 1% of vehicle price.']
                    ]
                ], 422);
            }

            // Simple calculation: NO INTEREST, NO FEES
            $loanAmount = $vehiclePrice - $depositAmount;
            $durationMonths = $validated['duration_months'];
            
            // Simple monthly payment: loan amount divided by duration
            $monthlyPayment = $loanAmount / $durationMonths;
            
            // Total amount is just the vehicle price (no additional costs)
            $totalAmount = $vehiclePrice;

            Log::info('Simple Gentleman Agreement Calculations:', [
                'vehicle_price' => $vehiclePrice,
                'deposit_amount' => $depositAmount,
                'loan_amount' => $loanAmount,
                'duration_months' => $durationMonths,
                'monthly_payment' => $monthlyPayment,
                'total_amount' => $totalAmount,
                'no_interest' => true,
                'no_fees' => true
            ]);

            // Parse vehicle information
            [$vehicleType, $vehicleId] = explode('-', $validated['vehicle_id'], 2);
            $vehicleInfo = $this->getVehicleInfo($vehicleType, $vehicleId);

            // Validate vehicle exists
            if (!$vehicleInfo || $vehicleInfo['make'] === 'Unknown') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected vehicle not found. Please select a valid vehicle.',
                    'errors' => [
                        'vehicle_id' => ['Selected vehicle not found.']
                    ]
                ], 422);
            }

            DB::beginTransaction();
            
            $agreementData = [
                'client_name' => $validated['client_name'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'national_id' => $validated['national_id'],
                'kra_pin' => $validated['kra_pin'] ?? null,
                'phone_numberalt' => $validated['phone_numberalt'],
                'emailalt' => $validated['emailalt'],
                'address' => $validated['address'] ?? null,
                'car_type' => $vehicleType,
                'car_id' => $vehicleId,
                'vehicle_make' => $vehicleInfo['make'] ?? 'Unknown',
                'vehicle_model' => $vehicleInfo['model'] ?? 'Unknown',
                'vehicle_year' => $vehicleInfo['year'] ?? null,
                'vehicle_plate' => $vehicleInfo['plate'] ?? null,
                'vehicle_price' => $vehiclePrice,
                'deposit_amount' => $depositAmount,
                'loan_amount' => $loanAmount,
                'duration_months' => $durationMonths,
                'monthly_payment' => $monthlyPayment,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'outstanding_balance' => $loanAmount,
                'payment_progress' => 0,
                'payments_made' => 0,
                'payments_remaining' => $durationMonths,
                'agreement_date' => today(),
                'first_due_date' => $validated['first_due_date'],
                'expected_completion_date' => Carbon::parse($validated['first_due_date'])->addMonths($durationMonths - 1),
                'status' => 'pending',
                'is_overdue' => false,
                'overdue_days' => 0
            ];

            // Set the appropriate foreign key based on vehicle type
            if ($vehicleType === 'import') {
                $agreementData['imported_id'] = $vehicleId;
            } elseif ($vehicleType === 'customer') {
                $agreementData['customer_id'] = $vehicleId;
            }

            $agreement = GentlemanAgreement::create($agreementData);

            // Generate simple payment schedule (NO INTEREST)
            $this->generateSimplePaymentSchedule($agreement);

            DB::commit();

            Log::info("Gentleman Agreement created successfully", [
                'agreement_id' => $agreement->id,
                'client_name' => $agreement->client_name,
                'vehicle' => $agreement->vehicle_make . ' ' . $agreement->vehicle_model,
                'monthly_payment' => $monthlyPayment,
                'no_interest' => true,
                'simple_payment_plan' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => "Gentleman's Agreement created successfully for {$agreement->client_name}!",
                'data' => [
                    'agreement_id' => $agreement->id,
                    'client_name' => $agreement->client_name,
                    'vehicle' => $agreement->vehicle_make . ' ' . $agreement->vehicle_model,
                    'loan_amount' => number_format($agreement->loan_amount, 2),
                    'monthly_payment' => number_format($agreement->monthly_payment, 2),
                    'duration' => $agreement->duration_months,
                    'no_interest' => true,
                    'redirect_url' => route('gentlemanagreement.index')
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Gentleman Agreement Validation Error:', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Please check the form data and try again.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Gentleman Agreement Creation Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again or contact support.',
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
  
    /**
 * Record payment for Gentleman's Agreement (alias for storePayment)
 * This method handles the route mismatch between recordPayment and storePayment
 */
public function recordPayment(Request $request)
{
    return $this->storePayment($request);
}

/**
 * Store regular payment for Gentleman's Agreement
 */

public function storePayment(Request $request)
{
    try {
        $validated = $request->validate([
            'agreement_id' => 'required|integer|exists:gentlemanagreements,id',
            'payment_amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,mpesa,cheque,card',
            'payment_reference' => 'nullable|string|max:100',
            'payment_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        $agreement = GentlemanAgreement::with('paymentSchedule')->findOrFail($request->agreement_id);
        
        if ($agreement->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add payment to a completed agreement.'
            ], 422);
        }
        
        $actualOutstanding = $this->calculateCurrentOutstandingFromSchedule($agreement);
        
        if ($request->payment_amount > ($actualOutstanding + 0.01)) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount cannot exceed outstanding balance of KSh ' . number_format($actualOutstanding, 2)
            ], 422);
        }

        // Calculate payment number
        $lastPayment = DB::table('hire_purchase_payments')
            ->where('agreement_id', $agreement->id)
            ->max('payment_number');
        
        $paymentNumber = ($lastPayment ?? 0) + 1;

        // Calculate balances
        $balanceBefore = $actualOutstanding;
        $balanceAfter = max(0, $balanceBefore - $request->payment_amount);

        // Determine payment type
        $paymentType = 'regular';
        if ($request->payment_amount < $agreement->monthly_payment) {
            $paymentType = 'partial';
        } elseif ($balanceAfter <= 0) {
            $paymentType = 'final';
        }

        // Create payment record using only available fillable fields
        $paymentData = [
            'agreement_id' => $agreement->id,
            'amount' => $request->payment_amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->payment_reference,
            'notes' => $request->payment_notes,
            'payment_type' => $paymentType,
            'penalty_amount' => 0, // No penalties in gentleman's agreement
            'payment_number' => $paymentNumber,
            'recorded_by' => auth()->id() ?? 1,
            'recorded_at' => now(),
            'is_verified' => false,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter
        ];

        $paymentId = DB::table('hire_purchase_payments')->insertGetId($paymentData);

        // Update Payment Schedule
        $this->updatePaymentSchedule($agreement->id, $request->payment_amount, $request->payment_date);

        // Update agreement
        $newAmountPaid = $agreement->amount_paid + $request->payment_amount;
        $newOutstanding = max(0, $actualOutstanding - $request->payment_amount);
        
        // Calculate payment progress based on vehicle price (no interest)
        $totalAmount = $agreement->vehicle_price;
        $totalPaid = $newAmountPaid + $agreement->deposit_amount;
        $paymentProgress = ($totalAmount > 0) ? ($totalPaid / $totalAmount) * 100 : 0;
        
        $paymentsMade = DB::table('hire_purchase_payments')
            ->where('agreement_id', $agreement->id)
            ->count();

        // Calculate payments remaining
        $paymentsRemaining = max(0, $agreement->duration_months - $paymentsMade);

        // Update status
        $newStatus = $agreement->status;
        if ($newOutstanding <= 0) {
            $newStatus = 'completed';
            $paymentsRemaining = 0;
        } elseif ($agreement->status === 'pending') {
            $newStatus = 'active';
        }

        DB::table('gentlemanagreements')
            ->where('id', $agreement->id)
            ->update([
                'amount_paid' => $newAmountPaid,
                'outstanding_balance' => $newOutstanding,
                'payment_progress' => $paymentProgress,
                'payments_made' => $paymentsMade,
                'payments_remaining' => $paymentsRemaining,
                'last_payment_date' => $request->payment_date,
                'status' => $newStatus,
                'updated_at' => now()
            ]);

        DB::commit();

        $createdPayment = DB::table('hire_purchase_payments')
            ->where('id', $paymentId)
            ->first();

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'completed' ? 
                'Payment recorded successfully! Gentleman\'s Agreement completed!' : 
                'Payment recorded successfully!',
            'payment' => $createdPayment,
            'payment_number' => $paymentNumber,
            'new_balance' => $newOutstanding,
            'payment_progress' => round($paymentProgress, 1),
            'is_completed' => $newStatus === 'completed',
            'payments_remaining' => $paymentsRemaining,
            'agreement_status' => $newStatus
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Please check your input.',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollback();
        
        Log::error('Gentleman Agreement Payment Recording Error:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while recording the payment: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Generate simple payment schedule (NO INTEREST)
     */
    private function generateSimplePaymentSchedule($agreement)
    {
        $remainingBalance = $agreement->loan_amount;
        $monthlyPayment = $agreement->monthly_payment;
        $firstDueDate = Carbon::parse($agreement->first_due_date);
        
        Log::info('Generating simple payment schedule (NO INTEREST):', [
            'loan_amount' => $remainingBalance,
            'monthly_payment' => $monthlyPayment,
            'duration' => $agreement->duration_months,
            'no_interest' => true
        ]);
        
        for ($month = 1; $month <= $agreement->duration_months; $month++) {
            // For the last payment, pay the remaining balance exactly
            if ($month == $agreement->duration_months) {
                $principalAmount = $remainingBalance;
                $actualPayment = $remainingBalance;
                $newRemainingBalance = 0;
            } else {
                $principalAmount = $monthlyPayment;
                $actualPayment = $monthlyPayment;
                $newRemainingBalance = $remainingBalance - $monthlyPayment;
            }
            
            // Log first 3 payments for verification
            if ($month <= 3) {
                Log::info("Payment {$month} Details (Simple - No Interest):", [
                    'Starting Balance' => round($remainingBalance, 2),
                    'Payment' => round($actualPayment, 2),
                    'Principal' => round($principalAmount, 2),
                    'Interest' => 0, // NO INTEREST
                    'Ending Balance' => round($newRemainingBalance, 2)
                ]);
            }
            
            // Create payment schedule record
            PaymentSchedule::create([
                'agreement_id' => $agreement->id,
                'installment_number' => $month,
                'due_date' => $firstDueDate->copy()->addMonths($month - 1),
                'principal_amount' => round($principalAmount, 2),
                'interest_amount' => 0, // NO INTEREST
                'total_amount' => round($actualPayment, 2),
                'balance_after' => round($newRemainingBalance, 2),
                'status' => 'pending',
                'amount_paid' => 0,
                'date_paid' => null,
                'days_overdue' => 0
            ]);
            
            // Update remaining balance for next iteration
            $remainingBalance = $newRemainingBalance;
        }
        
        Log::info('✅ Simple payment schedule generated successfully (NO INTEREST)');
    }


    /**
     * Update Payment Schedule when payment is made
     */
    private function updatePaymentSchedule($agreementId, $paymentAmount, $paymentDate)
    {
        try {
            $remainingAmount = $paymentAmount;
            
            $paymentSchedules = PaymentSchedule::where('agreement_id', $agreementId)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderByRaw("
                    CASE 
                        WHEN status = 'overdue' THEN 1 
                        WHEN status = 'partial' THEN 2 
                        WHEN status = 'pending' THEN 3 
                    END
                ")
                ->orderBy('due_date', 'asc')
                ->get();

            if ($paymentSchedules->isEmpty()) {
                Log::info('No payment schedules found to update for agreement: ' . $agreementId);
                return;
            }

            Log::info('Updating payment schedule for agreement: ' . $agreementId . ' with amount: ' . $paymentAmount);

            foreach ($paymentSchedules as $schedule) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $currentPaid = $schedule->amount_paid ?? 0;
                $amountDue = $schedule->total_amount - $currentPaid;
                
                if ($amountDue <= 0) {
                    continue;
                }

                if ($remainingAmount >= $amountDue) {
                    // Fully pay this installment
                    $schedule->update([
                        'amount_paid' => $schedule->total_amount,
                        'status' => 'paid',
                        'date_paid' => $paymentDate,
                        'days_overdue' => 0
                    ]);
                    
                    $remainingAmount -= $amountDue;
                    
                    Log::info('Fully paid installment ' . $schedule->installment_number . ' for agreement ' . $agreementId);
                    
                } else {
                    // Partially pay this installment
                    $newAmountPaid = $currentPaid + $remainingAmount;
                    $newStatus = 'partial';
                    
                    if ($schedule->status === 'overdue') {
                        $newStatus = 'overdue';
                    }
                    
                    $schedule->update([
                        'amount_paid' => $newAmountPaid,
                        'status' => $newStatus,
                        'date_paid' => $paymentDate
                    ]);
                    
                    Log::info('Partially paid installment ' . $schedule->installment_number . ' for agreement ' . $agreementId . '. Amount: ' . $remainingAmount);
                    
                    $remainingAmount = 0;
                }
            }

            if ($remainingAmount > 0) {
                Log::info('Overpayment of ' . $remainingAmount . ' for agreement ' . $agreementId);
            }

            $this->updateOverdueStatus($agreementId);

        } catch (\Exception $e) {
            Log::error('Payment schedule update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update overdue status for payment schedules
     */
    private function updateOverdueStatus($agreementId)
    {
        try {
            $today = now()->toDateString();
            
            PaymentSchedule::where('agreement_id', $agreementId)
                ->where('due_date', '<', $today)
                ->where('status', 'pending')
                ->update([
                    'status' => 'overdue',
                    'days_overdue' => DB::raw("DATEDIFF('$today', due_date)")
                ]);

            PaymentSchedule::where('agreement_id', $agreementId)
                ->where('status', 'overdue')
                ->update([
                    'days_overdue' => DB::raw("DATEDIFF('$today', due_date)")
                ]);

        } catch (\Exception $e) {
            Log::error('Overdue status update failed: ' . $e->getMessage());
        }
    }

    /**
     * Show agreement details
     */
    public function show($id)
    {
        $agreement = GentlemanAgreement::with([
            'customerVehicle',
            'carImport',
            'payments', 
            'paymentSchedule', 
            'approvedBy'
        ])->findOrFail($id);
        
        // Calculate accurate outstanding balance from payment schedule
        $totalScheduledAmount = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('total_amount') : 0;
        $totalPaidFromSchedule = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('amount_paid') : 0;
        $calculatedOutstanding = $totalScheduledAmount - $totalPaidFromSchedule;
        $actualOutstanding = $totalScheduledAmount > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
        
        // Calculate other metrics
        $totalAmountPaid = $agreement->deposit_amount + $agreement->amount_paid;
        $paymentProgress = $agreement->total_amount > 0 ? 
            (($totalAmountPaid) / $agreement->total_amount) * 100 : 0;
        
        $nextDueInstallment = $agreement->paymentSchedule ? 
            $agreement->paymentSchedule->whereIn('status', ['pending', 'overdue', 'partial'])->first() : null;
        
        $overdueAmount = $agreement->paymentSchedule ? 
            $agreement->paymentSchedule->where('status', 'overdue')->sum('total_amount') : 0;
        
        return view('gentlemanagreement.loan-management', compact(
            'agreement',
            'actualOutstanding',
            'totalAmountPaid',
            'paymentProgress',
            'nextDueInstallment',
            'overdueAmount'
        ));
    }

    /**
     * Approve an agreement
     */
    public function approve($id)
    {
        $agreement = GentlemanAgreement::findOrFail($id);
        
        if ($agreement->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Agreement is not in pending status']);
        }

        $agreement->update([
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Agreement approved successfully']);
    }

    /**
     * Delete an agreement
     */
    public function destroy($agreementId)
    {
        try {
            Log::info('Attempting to delete gentleman agreement', ['id' => $agreementId]);
            
            $agreement = DB::table('gentlemanagreements')
                ->where('id', $agreementId)
                ->first();
            
            if (!$agreement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agreement not found.'
                ], 404);
            }
            
            $deleted = DB::table('gentlemanagreements')
                ->where('id', $agreementId)
                ->delete();
            
            if ($deleted) {
                Log::info('Gentleman agreement deleted successfully', ['id' => $agreementId]);
                return response()->json([
                    'success' => true,
                    'message' => 'Agreement deleted successfully!'
                ]);
            } else {
                throw new \Exception('No rows were deleted');
            }

        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get calculation for AJAX requests (NO INTEREST)
     */
    public function getCalculation(Request $request)
    {
        $vehiclePrice = $request->get('vehicle_price');
        $depositAmount = $request->get('deposit_amount');
        $duration = $request->get('duration');

        if (!$vehiclePrice || !$depositAmount || !$duration) {
            return response()->json(['error' => 'Missing required parameters']);
        }

        $depositPercentage = ($depositAmount / $vehiclePrice) * 100;
        $loanAmount = $vehiclePrice - $depositAmount;
        
        // Simple calculation: NO INTEREST
        $monthlyPayment = $loanAmount / $duration;
        $totalAmount = $vehiclePrice; // No additional costs

        return response()->json([
            'loan_amount' => $loanAmount,
            'monthly_payment' => round($monthlyPayment, 2),
            'total_amount' => round($totalAmount, 2),
            'deposit_percentage' => round($depositPercentage, 2),
            'no_interest' => true
        ]);
    }

    /**
     * Get vehicle information
     */
    private function getVehicleInfo($type, $id)
    {
        try {
            if ($type === 'import') {
                $car = CarImport::find($id);
                if ($car) {
                    return [
                        'make' => $car->make ?? 'Unknown',
                        'model' => $car->model ?? 'Unknown',
                        'year' => $car->year ?? null,
                        'plate' => null
                    ];
                }
            } elseif ($type === 'customer') {
                $car = CustomerVehicle::find($id);
                if ($car) {
                    return [
                        'make' => $car->vehicle_make ?? 'Unknown',
                        'model' => $car->vehicle_model ?? 'Unknown',
                        'year' => $car->year ?? null,
                        'plate' => $car->number_plate ?? null
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle info:', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'make' => 'Unknown',
            'model' => 'Unknown',
            'year' => null,
            'plate' => null
        ];
    }

    /**
     * Helper method to calculate outstanding balance from schedule
     */
    private function calculateCurrentOutstandingFromSchedule($agreement)
    {
        Log::info('=== CALCULATING CURRENT OUTSTANDING BALANCE (NO INTEREST) ===', [
            'agreement_id' => $agreement->id
        ]);
        
        if (!$agreement->paymentSchedule || $agreement->paymentSchedule->isEmpty()) {
            Log::info('No payment schedule found, using agreement outstanding balance', [
                'outstanding_balance' => $agreement->outstanding_balance
            ]);
            return $agreement->outstanding_balance;
        }
        
        $totalScheduled = $agreement->paymentSchedule->sum('total_amount');
        $totalPaid = $agreement->paymentSchedule->sum('amount_paid');
        $calculatedOutstanding = $totalScheduled - $totalPaid;
        
        Log::info('Outstanding balance calculation:', [
            'total_scheduled' => $totalScheduled,
            'total_paid' => $totalPaid,
            'calculated_outstanding' => $calculatedOutstanding,
            'agreement_outstanding' => $agreement->outstanding_balance
        ]);
        
        $finalOutstanding = $totalScheduled > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
        $finalOutstanding = max(0, $finalOutstanding);
        
        Log::info('Final outstanding balance:', ['amount' => $finalOutstanding]);
        
        return $finalOutstanding;
    }

    /**
     * Export agreements report
     */
    public function export()
    {
        // Implementation for Excel export
        return response()->download('gentleman_agreements_export.xlsx');
    }

    /**
     * Get payment schedule for an agreement
     */
    public function paymentSchedule($id)
    {
        $agreement = GentlemanAgreement::with('paymentSchedule')->findOrFail($id);
        return view('gentlemanagreement.schedule', compact('agreement'));
    }

    /**
     * Print agreement
     */
    public function printAgreement($id)
    {
        $agreement = GentlemanAgreement::findOrFail($id);
        return view('gentlemanagreement.print', compact('agreement'));
    }

    /**
     * Send reminder
     */
    public function sendReminder($id)
    {
        $agreement = GentlemanAgreement::findOrFail($id);
        
        // Implementation for sending payment reminder
        // This could be SMS, email, or both
        
        return response()->json(['success' => true, 'message' => 'Reminder sent successfully']);
    }

    /**
     * Dashboard view
     */
    public function dashboard()
    {
        $stats = [
            'total_agreements' => GentlemanAgreement::count(),
            'active_agreements' => GentlemanAgreement::active()->count(),
            'overdue_agreements' => GentlemanAgreement::overdue()->count(),
            'total_portfolio' => GentlemanAgreement::sum('total_amount'),
            'outstanding_balance' => GentlemanAgreement::sum('outstanding_balance'),
            'payments_today' => HirePurchasePayment::whereDate('created_at', today())->sum('amount'),
            'payments_this_month' => HirePurchasePayment::whereMonth('created_at', now()->month)->sum('amount'),
        ];

        $recentPayments = HirePurchasePayment::with('agreement')
            ->latest()
            ->limit(10)
            ->get();

        $overdueAgreements = GentlemanAgreement::overdue()
            ->orderBy('overdue_days', 'desc')
            ->limit(10)
            ->get();

        return view('gentlemanagreement.dashboard', compact('stats', 'recentPayments', 'overdueAgreements'));
    }

    /**
     * Verify a payment
     */
    public function verifyPayment(Request $request, $paymentId)
    {
        try {
            $payment = DB::table('hire_purchase_payments')
                ->where('id', $paymentId)
                ->first();
            
            if (!$payment) {
                return response()->json([
                    'message' => 'Payment not found.'
                ], 404);
            }
            
            if ($payment->is_verified) {
                return response()->json([
                    'message' => 'Payment is already verified.'
                ], 422);
            }
            
            DB::table('hire_purchase_payments')
                ->where('id', $paymentId)
                ->update([
                    'is_verified' => true,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'Payment verified successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'An error occurred while verifying the payment.'
            ], 500);
        }
    }

    /**
     * Update agreement terms (simple modification without complex rescheduling)
     */
    public function updateAgreement(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'client_name' => 'required|string|max:100',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:100',
                'national_id' => 'required|string|max:20',
                'kra_pin' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'deposit_amount' => 'required|numeric|min:1',
                'duration_months' => 'required|integer|min:3|max:60',
            ]);

            $agreement = GentlemanAgreement::findOrFail($id);

            // Only allow updates for pending agreements
            if ($agreement->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only update pending agreements.'
                ], 422);
            }

            DB::beginTransaction();

            // Update basic client information
            $agreement->update([
                'client_name' => $validated['client_name'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'national_id' => $validated['national_id'],
                'kra_pin' => $validated['kra_pin'],
                'address' => $validated['address'],
            ]);

            // If deposit or duration changed, recalculate payment schedule
            if ($agreement->deposit_amount != $validated['deposit_amount'] || 
                $agreement->duration_months != $validated['duration_months']) {

                $newLoanAmount = $agreement->vehicle_price - $validated['deposit_amount'];
                $newMonthlyPayment = $newLoanAmount / $validated['duration_months'];
                $newExpectedCompletion = Carbon::parse($agreement->first_due_date)->addMonths($validated['duration_months'] - 1);

                $agreement->update([
                    'deposit_amount' => $validated['deposit_amount'],
                    'loan_amount' => $newLoanAmount,
                    'duration_months' => $validated['duration_months'],
                    'monthly_payment' => $newMonthlyPayment,
                    'outstanding_balance' => $newLoanAmount,
                    'payments_remaining' => $validated['duration_months'],
                    'expected_completion_date' => $newExpectedCompletion,
                ]);

                // Regenerate payment schedule
                PaymentSchedule::where('agreement_id', $agreement->id)->delete();
                $this->generateSimplePaymentSchedule($agreement);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Agreement updated successfully!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Agreement update error:', [
                'message' => $e->getMessage(),
                'agreement_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the agreement.'
            ], 500);
        }
    }

    /**
     * Get payment history for an agreement
     */
    public function getPaymentHistory($id)
    {
        $agreement = GentlemanAgreement::findOrFail($id);
        
        $payments = DB::table('hire_purchase_payments')
            ->where('agreement_id', $id)
            ->orderBy('payment_date', 'desc')
            ->get();

        $paymentSchedule = PaymentSchedule::where('agreement_id', $id)
            ->orderBy('installment_number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'agreement' => $agreement,
            'payments' => $payments,
            'payment_schedule' => $paymentSchedule
        ]);
    }

    /**
     * Generate payment receipt
     */
    public function generateReceipt($paymentId)
    {
        $payment = DB::table('hire_purchase_payments as hp')
            ->join('gentlemanagreements as ga', 'hp.agreement_id', '=', 'ga.id')
            ->where('hp.id', $paymentId)
            ->select('hp.*', 'ga.client_name', 'ga.vehicle_make', 'ga.vehicle_model', 'ga.national_id')
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return view('gentlemanagreement.receipt', compact('payment'));
    }

    /**
     * Get agreement summary statistics
     */
    public function getAgreementStats()
    {
        $stats = [
            'total_agreements' => GentlemanAgreement::count(),
            'pending_agreements' => GentlemanAgreement::where('status', 'pending')->count(),
            'active_agreements' => GentlemanAgreement::where('status', 'active')->count(),
            'completed_agreements' => GentlemanAgreement::where('status', 'completed')->count(),
            'overdue_agreements' => GentlemanAgreement::where('is_overdue', true)->count(),
            'total_portfolio_value' => GentlemanAgreement::sum('total_amount'),
            'total_outstanding' => GentlemanAgreement::sum('outstanding_balance'),
            'total_collected' => GentlemanAgreement::sum('amount_paid'),
            'average_agreement_value' => GentlemanAgreement::avg('total_amount'),
            'average_monthly_payment' => GentlemanAgreement::avg('monthly_payment'),
        ];

        // Monthly payment collections for the last 12 months
        $monthlyCollections = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $total = DB::table('hire_purchase_payments')
                ->whereMonth('payment_date', $month->month)
                ->whereYear('payment_date', $month->year)
                ->sum('amount');
            
            $monthlyCollections[] = [
                'month' => $month->format('M Y'),
                'total' => $total
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'monthly_collections' => $monthlyCollections
        ]);
    }

    /**
     * Search agreements
     */
    public function search(Request $request)
    {
        $query = GentlemanAgreement::query();

        // Search by client name
        if ($request->filled('client_name')) {
            $query->where('client_name', 'like', '%' . $request->client_name . '%');
        }

        // Search by national ID
        if ($request->filled('national_id')) {
            $query->where('national_id', 'like', '%' . $request->national_id . '%');
        }

        // Search by phone number
        if ($request->filled('phone_number')) {
            $query->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }

        // Search by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by vehicle
        if ($request->filled('vehicle')) {
            $query->where(function($q) use ($request) {
                $q->where('vehicle_make', 'like', '%' . $request->vehicle . '%')
                  ->orWhere('vehicle_model', 'like', '%' . $request->vehicle . '%');
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('agreement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('agreement_date', '<=', $request->date_to);
        }

        $agreements = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'agreements' => $agreements
        ]);
    }

    /**
     * Bulk operations on agreements
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,suspend,delete',
            'agreement_ids' => 'required|array',
            'agreement_ids.*' => 'integer|exists:gentlemanagreements,id'
        ]);

        $affectedCount = 0;

        try {
            DB::beginTransaction();

            switch ($validated['action']) {
                case 'activate':
                    $affectedCount = GentlemanAgreement::whereIn('id', $validated['agreement_ids'])
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'active',
                            'approved_by' => auth()->id(),
                            'approved_at' => now()
                        ]);
                    break;

                case 'suspend':
                    $affectedCount = GentlemanAgreement::whereIn('id', $validated['agreement_ids'])
                        ->whereIn('status', ['pending', 'active'])
                        ->update(['status' => 'suspended']);
                    break;

                case 'delete':
                    $affectedCount = GentlemanAgreement::whereIn('id', $validated['agreement_ids'])
                        ->where('status', 'pending')
                        ->delete();
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully {$validated['action']}d {$affectedCount} agreements."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Bulk action error:', [
                'action' => $validated['action'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk operation.'
            ], 500);
        }
    }

    /**
     * Update payment schedule to ensure interest amount is always 0
     */
    private function ensureZeroInterest($agreementId)
    {
        PaymentSchedule::where('agreement_id', $agreementId)
            ->update(['interest_amount' => 0]);
        
        Log::info('Ensured zero interest for all payment schedules', ['agreement_id' => $agreementId]);
    }

    /**
     * Validate agreement data integrity
     */
    public function validateAgreementIntegrity($id)
    {
        $agreement = GentlemanAgreement::with('paymentSchedule')->findOrFail($id);
        
        $issues = [];
        
        // Check if payment schedule exists
        if ($agreement->paymentSchedule->isEmpty()) {
            $issues[] = 'No payment schedule found';
        } else {
            // Check if any payment schedule has interest (should be 0)
            $interestPayments = $agreement->paymentSchedule->where('interest_amount', '>', 0);
            if ($interestPayments->count() > 0) {
                $issues[] = 'Found ' . $interestPayments->count() . ' payments with interest (should be 0)';
                
                // Fix the issue automatically
                PaymentSchedule::where('agreement_id', $id)
                    ->where('interest_amount', '>', 0)
                    ->update(['interest_amount' => 0]);
                
                $issues[] = 'Automatically fixed interest amounts to 0';
            }
            
            // Check total scheduled vs loan amount
            $totalScheduled = $agreement->paymentSchedule->sum('total_amount');
            if (abs($totalScheduled - $agreement->loan_amount) > 1) {
                $issues[] = "Total scheduled amount ($totalScheduled) doesn't match loan amount ({$agreement->loan_amount})";
            }
        }
        
        // Check outstanding balance calculation
        $calculatedOutstanding = $this->calculateCurrentOutstandingFromSchedule($agreement);
        if (abs($calculatedOutstanding - $agreement->outstanding_balance) > 1) {
            $issues[] = "Calculated outstanding ($calculatedOutstanding) doesn't match stored balance ({$agreement->outstanding_balance})";
        }
        
        return response()->json([
            'success' => true,
            'agreement_id' => $id,
            'issues' => $issues,
            'is_valid' => empty($issues)
        ]);
    }
}