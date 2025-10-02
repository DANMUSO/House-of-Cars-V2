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
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\PenaltyService;
use App\Models\Penalty;
use App\Models\Repossession;
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

    // Get IDs of agreements that have been repossessed (exclude from "in-use")
    $repossessedAgreementIds = Repossession::whereIn('status', ['repossessed', 'pending_sale'])
        ->pluck('agreement_id')
        ->toArray();

    // Collect IDs from ACTIVE (non-repossessed) agreements only
    $importedIds = array_merge(
        InCash::whereNotNull('imported_id')
            ->whereNotIn('id', $repossessedAgreementIds)
            ->pluck('imported_id')
            ->toArray(),
        HirePurchaseAgreement::whereNotNull('imported_id')
            ->whereNotIn('id', $repossessedAgreementIds)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('imported_id')
            ->toArray(),
        GentlemanAgreement::whereNotNull('imported_id')
            ->whereNotIn('id', $repossessedAgreementIds)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('imported_id')
            ->toArray()
    );

    $customerIds = array_merge(
        InCash::whereNotNull('customer_id')
            ->whereNotIn('id', $repossessedAgreementIds)
            ->pluck('customer_id')
            ->toArray(),
        HirePurchaseAgreement::whereNotNull('customer_id')
            ->whereNotIn('id', $repossessedAgreementIds)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('customer_id')
            ->toArray(),
        GentlemanAgreement::whereNotNull('customer_id')
            ->whereNotIn('id', $repossessedAgreementIds)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('customer_id')
            ->toArray()
    );

    // Get repossessed vehicles ready for resale (from both Hire Purchase and Gentleman Agreements)
    $repossessedVehicles = Repossession::with('agreement')
        ->whereIn('agreement_type', ['hire_purchase', 'gentleman_agreement'])
        ->where('status', 'repossessed')
        ->get()
        ->map(function($repo) {
            $agreement = null;
            
            // Get the correct agreement based on type
            if ($repo->agreement_type === 'hire_purchase') {
                $agreement = HirePurchaseAgreement::find($repo->agreement_id);
            } elseif ($repo->agreement_type === 'gentleman_agreement') {
                $agreement = GentlemanAgreement::find($repo->agreement_id);
            }
            
            if (!$agreement) return null;
            
            return [
                'type' => $agreement->car_type ?? 'import',
                'id' => $agreement->car_id ?? null,
                'is_repossessed' => true,
                'repossession_id' => $repo->id,
                'car_value' => $repo->car_value,
                'original_agreement_type' => $repo->agreement_type
            ];
        })
        ->filter(fn($item) => $item !== null && $item['id'] !== null);

    // Filter VehicleInspections for NEW cars not in active agreements
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
    $importCars = CarImport::whereIn('id', $gentlemanAgreements->where('car_type', 'import')->pluck('car_id'))
        ->get()
        ->keyBy('id');
    
    $customerCars = CustomerVehicle::whereIn('id', $gentlemanAgreements->where('car_type', 'customer')->pluck('car_id'))
        ->get()
        ->keyBy('id');

    return view('gentlemanagreement.index', compact(
        'cars', 
        'gentlemanAgreements', 
        'importCars', 
        'customerCars',
        'repossessedVehicles'
    ));
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
                'phone_number' => 'required|string|regex:/^254[17]\d{8}$/',
                'email' => 'required|email|max:100',
                'national_id' => 'required|string|max:20',
                'kra_pin' => 'nullable|string|max:20',
                'phone_numberalt' => 'nullable|string|regex:/^254[17]\d{8}$/',
                'emailalt' => 'nullable|string|max:20',
                'vehicle_id' => 'required|string',
                'vehicle_price' => 'required|numeric|min:1',
                'PaidAmount' => 'required|numeric|min:1',
                'TradeInnAmount' => 'nullable|numeric|min:1',
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
                'tradeinnamount' =>$validated['TradeInnAmount'],
                'totalpaidamount' => $depositAmount,
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
                    // Send SMS notification
            try {
                $message = "Dear {$agreement->client_name}, installment payment of KSh " . number_format($request->payment_amount, 2) . " has been received on " . date('M d, Y', strtotime($request->payment_date)) . ". Your remaining balance is KSh " . number_format(max(0, $newOutstanding), 2) . ". Thank you for your payment.";
                
                $smsSent = SmsService::send($agreement->phone_number, $message);
                
                if ($smsSent) {
                    Log::info('Payment confirmation SMS sent', [
                        'agreement_id' => $agreement->id,
                        'payment_id' => $paymentId,
                        'client' => $agreement->client_name,
                        'phone' => $agreement->phone_number,
                        'amount' => $request->payment_amount
                    ]);
                } else {
                    Log::warning('Payment confirmation SMS failed', [
                        'agreement_id' => $agreement->id,
                        'payment_id' => $paymentId,
                        'client' => $agreement->client_name
                    ]);
                }
                
            } catch (\Exception $smsException) {
                Log::error('SMS error during payment confirmation: ' . $smsException->getMessage());
                // Don't fail the payment process if SMS fails
            }

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
 * Update Payment Schedule when payment is made - FIXED VERSION
 */
private function updatePaymentSchedule($agreementId, $paymentAmount, $paymentDate)
{
    try {
        $remainingAmount = $paymentAmount;
        
        // Get unpaid installments in CHRONOLOGICAL ORDER by due date first
        // This ensures we always pay the earliest due installment first
        // regardless of whether it's partial, overdue, or pending
        $paymentSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date', 'asc') // PRIMARY: Chronological order (earliest due date first)
            ->orderBy('installment_number', 'asc') // SECONDARY: Installment sequence as tiebreaker
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

            // Calculate how much is still owed on this installment
            $currentPaid = $schedule->amount_paid ?? 0;
            $amountDue = $schedule->total_amount - $currentPaid;
            
            if ($amountDue <= 0) {
                continue; // Skip if already fully paid
            }

            Log::info('Processing installment ' . $schedule->installment_number . 
                      ' (Due: ' . $schedule->due_date . ', Status: ' . $schedule->status . 
                      ', Amount Due: ' . $amountDue . ', Already Paid: ' . $currentPaid . ')');

            if ($remainingAmount >= $amountDue) {
                // Fully pay this installment
                $schedule->update([
                    'amount_paid' => $schedule->total_amount,
                    'status' => 'paid',
                    'date_paid' => $paymentDate,
                    'days_overdue' => 0 // Reset overdue days when paid
                ]);
                
                $remainingAmount -= $amountDue;
                
                Log::info('Fully paid installment ' . $schedule->installment_number . 
                          ' for agreement ' . $agreementId . '. Remaining amount: ' . $remainingAmount);
                
            } else {
                // Partially pay this installment
                $newAmountPaid = $currentPaid + $remainingAmount;
                
                // Determine new status based on current status and due date
                $today = now()->toDateString();
                $newStatus = 'partial';
                
                // If installment is overdue (due date passed), keep it as overdue
                // even if it's partially paid
                if ($schedule->due_date < $today) {
                    $newStatus = 'overdue';
                }
                
                $updateData = [
                    'amount_paid' => $newAmountPaid,
                    'status' => $newStatus,
                    'date_paid' => $paymentDate
                ];
                
                // Calculate days overdue if applicable
                if ($newStatus === 'overdue') {
                    $dueDate = Carbon::parse($schedule->due_date);
                    $todayDate = Carbon::parse($today);
                    $updateData['days_overdue'] = $dueDate->diffInDays($todayDate);
                }
                
                $schedule->update($updateData);
                
                Log::info('Partially paid installment ' . $schedule->installment_number . 
                          ' for agreement ' . $agreementId . '. Amount: ' . $remainingAmount . 
                          ', New Status: ' . $newStatus);
                
                $remainingAmount = 0;
            }
        }

        // If there's still remaining amount after all installments are paid,
        // it might be an overpayment or early payment for future installments
        if ($remainingAmount > 0) {
            Log::info('Remaining amount of ' . $remainingAmount . ' for agreement ' . $agreementId);
            
            // Optional: Apply remaining amount to future pending installments
            $this->applyOverpaymentToFutureInstallments($agreementId, $remainingAmount, $paymentDate);
        }

        // Update overdue status for all installments
        $this->updateOverdueStatus($agreementId);

    } catch (\Exception $e) {
        Log::error('Payment schedule update failed: ' . $e->getMessage());
        throw $e;
    }
}
private function applyOverpaymentToFutureInstallments($agreementId, $remainingAmount, $paymentDate)
{
    try {
        if ($remainingAmount <= 0) {
            return;
        }

        // Get future pending installments in chronological order
        $futureInstallments = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'pending')
            ->where('due_date', '>', now()->toDateString())
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($futureInstallments as $installment) {
            if ($remainingAmount <= 0) {
                break;
            }

            $amountDue = $installment->total_amount - ($installment->amount_paid ?? 0);
            
            if ($remainingAmount >= $amountDue) {
                // Fully pay this future installment
                $installment->update([
                    'amount_paid' => $installment->total_amount,
                    'status' => 'paid',
                    'date_paid' => $paymentDate
                ]);
                
                $remainingAmount -= $amountDue;
                
                Log::info('Applied overpayment to fully pay future installment ' . 
                          $installment->installment_number . ' for agreement ' . $agreementId);
            } else {
                // Partially pay this future installment
                $newAmountPaid = ($installment->amount_paid ?? 0) + $remainingAmount;
                
                $installment->update([
                    'amount_paid' => $newAmountPaid,
                    'status' => 'partial',
                    'date_paid' => $paymentDate
                ]);
                
                Log::info('Applied overpayment to partially pay future installment ' . 
                          $installment->installment_number . ' for agreement ' . $agreementId . 
                          '. Amount: ' . $remainingAmount);
                
                $remainingAmount = 0;
            }
        }

        // If still remaining after all future installments
        if ($remainingAmount > 0) {
            Log::info('Excess overpayment of ' . $remainingAmount . 
                      ' for agreement ' . $agreementId . '. Consider storing as credit.');
        }

    } catch (\Exception $e) {
        Log::error('Future installments overpayment application failed: ' . $e->getMessage());
        // Don't throw as this is not critical to the main payment process
    }
}
/**
 * Update overdue status for payment schedules - IMPROVED VERSION
 */
private function updateOverdueStatus($agreementId)
{
    try {
        $today = now()->toDateString();
        
        // Update overdue installments (pending installments that are past due date)
        PaymentSchedule::where('agreement_id', $agreementId)
            ->where('due_date', '<', $today)
            ->where('status', 'pending')
            ->update([
                'status' => 'overdue',
                'days_overdue' => DB::raw("DATEDIFF('$today', due_date)")
            ]);

        // Update days overdue for already overdue installments (including partial overdue)
        PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'overdue')
            ->update([
                'days_overdue' => DB::raw("DATEDIFF('$today', due_date)")
            ]);

        // Update partial payments that have become overdue
        PaymentSchedule::where('agreement_id', $agreementId)
            ->where('due_date', '<', $today)
            ->where('status', 'partial')
            ->whereRaw('amount_paid < total_amount')
            ->update([
                'status' => 'overdue',
                'days_overdue' => DB::raw("DATEDIFF('$today', due_date)")
            ]);

    } catch (\Exception $e) {
        Log::error('Overdue status update failed: ' . $e->getMessage());
        // Don't throw here as it's not critical to the payment process
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
        'penalties', 
        'approvedBy'
    ])->findOrFail($id);
    
    $this->updateOverdueStatus($id);
    
    // Calculate penalties if overdue payments exist
    $penaltyService = app(\App\Services\PenaltyService::class);
    $penaltyService->calculatePenaltiesForAgreement('gentleman_agreement', $id);
    $penaltySummary = $penaltyService->getPenaltySummary('gentleman_agreement', $id);
    
    // Calculate accurate outstanding balance from payment schedule
    $totalScheduledAmount = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('total_amount') : 0;
    $totalPaidFromSchedule = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('amount_paid') : 0;
    $calculatedOutstanding = $totalScheduledAmount - $totalPaidFromSchedule;
    $actualOutstanding = $totalScheduledAmount > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
    
    // Calculate other metrics
    $totalAmountPaid = $agreement->deposit_amount + $agreement->amount_paid;
    $paymentProgress = $agreement->total_amount > 0 ? 
        (($totalAmountPaid) / $agreement->total_amount) * 100 : 0;
    
    // Enhanced Next Payment Due Calculation
    $overduePayments = collect();
    $nextDueInstallment = null;
    $totalAmountDue = 0;
    $overdueCount = 0;
    $paymentBreakdown = [];
    
    if ($agreement->paymentSchedule) {
        $today = \Carbon\Carbon::today();
        
        // Get all payments that are overdue or due today and not fully paid
        $overduePayments = $agreement->paymentSchedule->filter(function($schedule) use ($today) {
            $dueDate = \Carbon\Carbon::parse($schedule->due_date);
            $remainingAmount = ($schedule->total_amount ?? 0) - ($schedule->amount_paid ?? 0);
            
            return ($dueDate->lte($today) && $remainingAmount > 0) || 
                   in_array($schedule->status, ['overdue', 'partial']);
        })->sortBy('due_date');
        
        // Calculate total amount due and breakdown
        foreach ($overduePayments as $payment) {
            $remainingAmount = ($payment->total_amount ?? 0) - ($payment->amount_paid ?? 0);
            
            if ($remainingAmount > 0) {
                $totalAmountDue += $remainingAmount;
                $overdueCount++;
                
                $paymentBreakdown[] = [
                    'due_date' => $payment->due_date,
                    'original_amount' => $payment->total_amount,
                    'amount_paid' => $payment->amount_paid ?? 0,
                    'remaining_amount' => $remainingAmount,
                    'days_overdue' => \Carbon\Carbon::parse($payment->due_date)->isPast() ? 
                    \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($payment->due_date)) : 0,
                    'status' => $payment->status
                ];
            }
        }
        
        // If no overdue payments, find next upcoming payment
        if ($totalAmountDue == 0) {
            $nextDueInstallment = $agreement->paymentSchedule
                ->filter(function($schedule) use ($today) {
                    $dueDate = \Carbon\Carbon::parse($schedule->due_date);
                    $remainingAmount = ($schedule->total_amount ?? 0) - ($schedule->amount_paid ?? 0);
                    
                    return $dueDate->gt($today) && $remainingAmount > 0;
                })
                ->sortBy('due_date')
                ->first();
        } else {
            // Use the oldest overdue payment as the "next" payment for compatibility
            $nextDueInstallment = $overduePayments->first();
        }
    }
    
    // Calculate overdue amount (for backward compatibility)
    $overdueAmount = $agreement->paymentSchedule ? 
        $agreement->paymentSchedule->where('status', 'overdue')->sum('total_amount') : 0;
    
    return view('gentlemanagreement.loan-management', compact(
        'agreement',
        'actualOutstanding',
        'totalAmountPaid',
        'paymentProgress',
        'nextDueInstallment',
        'overdueAmount',
        'penaltySummary',
        'totalAmountDue',        // NEW
        'overdueCount',          // NEW
        'paymentBreakdown'       // NEW
    ));
}
public function getPenalties($agreementId)
{
    try {
        $penaltyService = app(PenaltyService::class);
        
        // Calculate cumulative penalties
        $result = $penaltyService->calculatePenaltiesForAgreement('gentleman_agreement', $agreementId);
        
        // Get penalties ordered by sequence
        $penalties = Penalty::forAgreement('gentleman_agreement', $agreementId)
            ->with('paymentSchedule')
            ->orderBy('penalty_sequence', 'asc')
            ->get();
            
        // Get summary
        $summary = $penaltyService->getPenaltySummary('gentleman_agreement', $agreementId);
        
        return response()->json([
            'success' => true,
            'penalties' => $penalties,
            'summary' => $summary,
            'calculation_result' => $result
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching cumulative penalties: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch penalties: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Calculate cumulative penalties
 */
public function calculatePenalties(Request $request, $agreementId)
{
    try {
        $penaltyService = app(PenaltyService::class);
        $result = $penaltyService->calculatePenaltiesForAgreement('gentleman_agreement', $agreementId);
        
        return response()->json([
            'success' => true,
            'message' => "Cumulative penalties calculated. Created: {$result['penalties_created']}, Updated: {$result['penalties_updated']}",
            'data' => $result
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error calculating penalties: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to calculate penalties: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Get penalty calculation breakdown for Gentleman Agreement
 */
public function getPenaltyBreakdown($agreementId)
{
    try {
        $penaltyService = app(PenaltyService::class);
        
        // Get current penalties to see calculation type
        $penalties = Penalty::forAgreement('gentleman_agreement', $agreementId)
            ->orderBy('penalty_sequence', 'asc')
            ->get();
            
        if ($penalties->isEmpty()) {
            return response()->json([
                'message' => 'No penalties found',
                'breakdown' => []
            ]);
        }

        // Detect calculation type
        $firstPenalty = $penalties->first();
        $overdueSchedules = PaymentSchedule::where('agreement_id', $agreementId)
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->get();

        $breakdown = [];
        $explanation = '';

        if ($overdueSchedules->count() == 1) {
            // Progressive monthly penalty explanation
            $schedule = $overdueSchedules->first();
            $unpaidAmount = $schedule->total_amount - ($schedule->amount_paid ?? 0);
            
            foreach ($penalties->groupBy('penalty_sequence') as $month => $monthPenalties) {
                $penalty = $monthPenalties->first();
                $breakdown[] = [
                    'sequence' => $month,
                    'installment_number' => $penalty->installment_number,
                    'month' => "Month {$month}",
                    'calculation' => "KSh " . number_format($unpaidAmount, 2) . " × {$month} × 10%",
                    'penalty_amount' => $penalty->penalty_amount,
                    'status' => $penalty->status
                ];
            }
            
            $explanation = 'Progressive Monthly Penalty: Each month multiplies the base unpaid amount by the month number × 10%';
            
        } else {
            // Cumulative penalty explanation
            $cumulativeAmount = 0;
            foreach ($overdueSchedules as $index => $schedule) {
                $unpaidAmount = $schedule->total_amount - ($schedule->amount_paid ?? 0);
                $cumulativeAmount += $unpaidAmount;
                $penalty = $penalties->where('payment_schedule_id', $schedule->id)->first();
                
                $breakdown[] = [
                    'sequence' => $index + 1,
                    'installment_number' => $schedule->installment_number,
                    'due_date' => $schedule->due_date->format('M d, Y'),
                    'unpaid_amount' => $unpaidAmount,
                    'cumulative_unpaid' => $cumulativeAmount,
                    'calculation' => "KSh " . number_format($cumulativeAmount, 2) . " × 10%",
                    'penalty_amount' => $penalty ? $penalty->penalty_amount : 0,
                    'status' => $penalty ? $penalty->status : 'pending'
                ];
            }
            
            $explanation = 'Cumulative Penalty: Each penalty includes all previous unpaid installments × 10%';
        }

        return response()->json([
            'success' => true,
            'breakdown' => $breakdown,
            'total_penalties' => $penalties->sum('penalty_amount'),
            'explanation' => $explanation,
            'calculation_type' => $overdueSchedules->count() == 1 ? 'progressive_monthly' : 'cumulative'
        ]);

    } catch (\Exception $e) {
        Log::error('Error generating penalty breakdown: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate breakdown: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Pay penalty (updated for cumulative system)
 */
public function payPenalty(Request $request, $penaltyId)
{
    $validated = $request->validate([
        'payment_amount' => 'required|numeric|min:0.01',
        'payment_date' => 'required|date',
        'payment_method' => 'required|string|in:cash,bank_transfer,mpesa,cheque,card',
        'payment_reference' => 'nullable|string|max:100',
        'notes' => 'nullable|string|max:500'
    ]);

    try {
        DB::beginTransaction();

        $penalty = Penalty::findOrFail($penaltyId);
        
        if ($penalty->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending penalties can be paid.'
            ], 422);
        }

        $outstanding = $penalty->penalty_amount - $penalty->amount_paid;
        
        if ($validated['payment_amount'] > $outstanding) {
            return response()->json([
                'success' => false,
                'message' => 'Payment exceeds outstanding penalty amount of KSh ' . number_format($outstanding, 2)
            ], 422);
        }

        // Record payment
        $penalty->recordPayment(
            $validated['payment_amount'],
            $validated['payment_date'],
            $validated['payment_reference']
        );

        // Update notes if provided
        if (!empty($validated['notes'])) {
            $penalty->update(['notes' => $validated['notes']]);
        }

        // Create audit trail
        DB::table('penalty_payments')->insert([
            'penalty_id' => $penalty->id,
            'amount' => $validated['payment_amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'],
            'notes' => $validated['notes'],
            'recorded_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Penalty payment recorded successfully!',
            'penalty' => $penalty->fresh(),
            'calculation_info' => $penalty->fresh()->calculation_explanation
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Penalty payment failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to process penalty payment: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Waive a penalty
 */
public function waivePenalty(Request $request, $penaltyId)
{
    $validated = $request->validate([
        'reason' => 'required|string|max:500'
    ]);

    try {
        DB::beginTransaction();

        $penalty = Penalty::findOrFail($penaltyId);
        
        if ($penalty->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending penalties can be waived.'
            ], 422);
        }

        $penalty->waive($validated['reason'], auth()->id());

        DB::commit();

        Log::info("Penalty {$penaltyId} waived", [
            'reason' => $validated['reason'],
            'waived_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penalty waived successfully!',
            'penalty' => $penalty->fresh()
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Penalty waiver failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to waive penalty: ' . $e->getMessage()
        ], 500);
    }
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
        // Send SMS notification
        try {
            $carDetails = $this->getCarDetails($agreement->car_type, $agreement->car_id);
            
            $message = "Dear {$agreement->client_name}, we confirm your Gentleman's Agreement for the {$carDetails} with a first payment of KSh " . number_format($agreement->deposit_amount, 2) . ", monthly installments of KSh " . number_format($agreement->monthly_payment, 2) . " and a balance of KSh " . number_format($agreement->loan_amount, 2) . ". Thank you for choosing House of Cars; we remain committed to providing you with excellent service.";
            
            $smsSent = SmsService::send($agreement->phone_number, $message);
            
            if ($smsSent) {
                Log::info('Gentleman Agreement approval SMS sent', [
                    'agreement_id' => $id,
                    'client' => $agreement->client_name,
                    'phone' => $agreement->phone_number
                ]);
                return response()->json(['success' => true, 'message' => 'Agreement approved and SMS notification sent successfully']);
            } else {
                Log::warning('Gentleman Agreement approval SMS failed', [
                    'agreement_id' => $id,
                    'client' => $agreement->client_name
                ]);
                return response()->json(['success' => true, 'message' => 'Agreement approved successfully, but SMS notification failed']);
            }
            
        } catch (\Exception $smsException) {
            Log::error('SMS error during gentleman agreement approval: ' . $smsException->getMessage());
            return response()->json(['success' => true, 'message' => 'Agreement approved successfully, but SMS notification failed']);
        }

        return response()->json(['success' => true, 'message' => 'Agreement approved successfully']);
    }
    /**
 * Get car details for SMS
 */
private function getCarDetails($car_type, $car_id)
{
    try {
        if ($car_type === 'import') {
            $car = CarImport::find($car_id);
            if ($car) {
                return "{$car->year} {$car->make} {$car->model}";
            }
        } else {
            $car = CustomerVehicle::find($car_id);
            if ($car) {
                return "{$car->model}";
            }
        }
        
        return "your selected vehicle";
        
    } catch (\Exception $e) {
        Log::error('Error getting car details: ' . $e->getMessage());
        return "your selected vehicle";
    }
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

    /**
 * Show repossession form
 */
public function showRepossessionForm($id)
{
    $agreement = GentlemanAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($id);
    
    // Check if already repossessed
    $existingRepossession = Repossession::where('agreement_id', $id)
        ->where('agreement_type', 'hire_purchase')
        ->whereIn('status', ['repossessed', 'pending_sale'])
        ->first();
    
    if ($existingRepossession) {
        return redirect()->back()->with('error', 'This vehicle has already been repossessed.');
    }
    
    // Calculate current outstanding balance
    $totalScheduled = $agreement->paymentSchedule->sum('total_amount');
    $totalPaid = $agreement->paymentSchedule->sum('amount_paid');
    $remainingBalance = $totalScheduled - $totalPaid;
    
    // Calculate total penalties
    $totalPenalties = $agreement->penalties()
        ->where('status', 'pending')
        ->sum('penalty_amount');
    
    return view('hirepurchase.repossession', compact(
        'agreement',
        'remainingBalance',
        'totalPenalties'
    ));
}

/**
 * Process repossession
 */
public function processRepossession(Request $request, $id)
{
    $validated = $request->validate([
        'repossession_date' => 'required|date|before_or_equal:today',
        'repossession_expenses' => 'required|numeric|min:0',
        'expected_sale_price' => 'nullable|numeric|min:0',
        'repossession_reason' => 'required|string|max:1000',
        'vehicle_condition' => 'required|string|max:1000',
        'storage_location' => 'nullable|string|max:255',
        'repossession_notes' => 'nullable|string|max:2000',
    ]);

    try {
        DB::beginTransaction();

        $agreement = GentlemanAgreement::with(['paymentSchedule', 'penalties'])->findOrFail($id);
        
        // Calculate financial details
        $totalScheduled = $agreement->paymentSchedule->sum('total_amount');
        $totalPaid = $agreement->paymentSchedule->sum('amount_paid');
        $remainingBalance = $totalScheduled - $totalPaid;
        
        $totalPenalties = $agreement->penalties()
            ->where('status', 'pending')
            ->sum('penalty_amount');
        
        // Calculate car value: remaining balance + penalties + repossession expenses
        $carValue = $remainingBalance + $totalPenalties + $validated['repossession_expenses'];
        
        // Create repossession record
        $repossession = Repossession::create([
            'agreement_id' => $agreement->id,
            'agreement_type' => 'hire_purchase',
            'repossession_date' => $validated['repossession_date'],
            'remaining_balance' => $remainingBalance,
            'total_penalties' => $totalPenalties,
            'repossession_expenses' => $validated['repossession_expenses'],
            'car_value' => $carValue,
            'expected_sale_price' => $validated['expected_sale_price'] ?? null,
            'status' => 'repossessed',
            'repossession_reason' => $validated['repossession_reason'],
            'vehicle_condition' => $validated['vehicle_condition'],
            'storage_location' => $validated['storage_location'] ?? null,
            'repossession_notes' => $validated['repossession_notes'] ?? null,
            'repossessed_by' => auth()->id(),
        ]);
        
        // Update agreement status
        $agreement->update([
            'status' => 'defaulted',
            'updated_at' => now()
        ]);
        
        DB::commit();
        
        // Send SMS notification
        try {
            $carDetails = $this->getCarDetails($agreement->car_type, $agreement->car_id);
            $message = "Dear {$agreement->client_name}, due to non-payment, your {$carDetails} has been repossessed. Outstanding amount: KSh " . number_format($carValue, 2) . ". Please contact us immediately to discuss resolution. - House of Cars";
            
            SmsService::send($agreement->phone_number, $message);
            
            Log::info('Repossession SMS sent', [
                'agreement_id' => $agreement->id,
                'repossession_id' => $repossession->id,
                'client' => $agreement->client_name
            ]);
        } catch (\Exception $smsException) {
            Log::error('SMS error during repossession: ' . $smsException->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Vehicle repossessed successfully',
            'repossession_id' => $repossession->id,
            'car_value' => $carValue
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Repossession failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to process repossession: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Record vehicle sale after repossession
 */
public function recordVehicleSale(Request $request, $repossessionId)
{
    $validated = $request->validate([
        'actual_sale_price' => 'required|numeric|min:0',
        'sale_date' => 'required|date|before_or_equal:today',
        'sale_notes' => 'nullable|string|max:1000',
    ]);

    try {
        DB::beginTransaction();

        $repossession = Repossession::findOrFail($repossessionId);
        
        if ($repossession->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle has already been marked as sold'
            ], 422);
        }
        
        $repossession->update([
            'actual_sale_price' => $validated['actual_sale_price'],
            'sale_date' => $validated['sale_date'],
            'status' => 'sold',
            'sold_by' => auth()->id(),
            'repossession_notes' => ($repossession->repossession_notes ?? '') . "\n\nSale Notes: " . ($validated['sale_notes'] ?? '')
        ]);
        
        $saleResult = $repossession->calculateSaleResult();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Vehicle sale recorded successfully',
            'sale_result' => $saleResult,
            'result_type' => $saleResult >= 0 ? 'profit' : 'loss'
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Vehicle sale recording failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to record vehicle sale: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * View repossessions list
 */
public function repossessionsList()
{
    $repossessions = Repossession::with(['agreement', 'repossessedBy', 'soldBy'])
        ->orderBy('repossession_date', 'desc')
        ->paginate(20);
    
    return view('hirepurchase.repossessions-list', compact('repossessions'));
}
public function getRepossessionData($agreementId)
{
    try {
        $agreement = GentlemanAgreement::with(['paymentSchedule', 'penalties'])
            ->findOrFail($agreementId);
        
        // Check if already repossessed
        $existingRepossession = Repossession::where('agreement_id', $agreementId)
            ->where('agreement_type', 'hire_purchase')
            ->whereIn('status', ['repossessed', 'pending_sale', 'sold'])
            ->first();
        
        if ($existingRepossession) {
            return response()->json([
                'success' => false,
                'message' => 'This vehicle has already been repossessed',
                'repossession' => $existingRepossession
            ], 422);
        }
        
        // Calculate financial details
        $totalScheduled = $agreement->paymentSchedule->sum('total_amount');
        $totalPaid = $agreement->paymentSchedule->sum('amount_paid');
        $remainingBalance = $totalScheduled - $totalPaid;
        
        // Get overdue installments count
        $overdueCount = $agreement->paymentSchedule()
            ->where('status', 'overdue')
            ->count();
        
        // Calculate total penalties
        $totalPenalties = $agreement->penalties()
            ->where('status', 'pending')
            ->sum('penalty_amount');
        
        // Get vehicle details
        $vehicleDetails = $this->getCarDetails($agreement->car_type, $agreement->car_id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'client_name' => $agreement->client_name,
                'phone_number' => $agreement->phone_number,
                'vehicle_details' => $vehicleDetails,
                'vehicle_make' => $agreement->vehicle_make,
                'vehicle_model' => $agreement->vehicle_model,
                'vehicle_year' => $agreement->vehicle_year,
                'remaining_balance' => $remainingBalance,
                'total_penalties' => $totalPenalties,
                'overdue_count' => $overdueCount,
                'monthly_payment' => $agreement->monthly_payment,
                'last_payment_date' => $agreement->last_payment_date,
                'agreement_date' => $agreement->agreement_date,
                'suggested_car_value' => $remainingBalance + $totalPenalties,
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching repossession data: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch repossession data: ' . $e->getMessage()
        ], 500);
    }
}
}