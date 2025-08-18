<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FleetAcquisition;
use App\Models\FleetPayment; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FleetAcquisitionController extends Controller
{
    public function index()
    {
        $fleetAcquisitions = FleetAcquisition::orderBy('created_at', 'desc')->get();
        
        return view('fleetacquisition.index', compact('fleetAcquisitions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Vehicle Information
            'vehicle_make' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'engine_capacity' => 'required|string|max:50',
            'chassis_number' => 'required|string|unique:fleet_acquisitions,chassis_number|max:255',
            'engine_number' => 'required|string|unique:fleet_acquisitions,engine_number|max:255',
            'registration_number' => 'nullable|string|max:20',
            'vehicle_category' => 'required|in:commercial,passenger,utility,special_purpose',
            'purchase_price' => 'required|numeric|min:0',
            'market_value' => 'required|numeric|min:0',
            'vehicle_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            
            // Financial Details
            'down_payment' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'loan_duration_months' => 'required|integer|min:1|max:120',
            'first_payment_date' => 'required|date',
            'insurance_premium' => 'nullable|numeric|min:0',
            
            // Legal & Compliance
            'hp_agreement_number' => 'required|string|unique:fleet_acquisitions,hp_agreement_number|max:255',
            'logbook_custody' => 'required|in:financier,company',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date',
            'company_kra_pin' => 'required|string|max:20',
            'business_permit_number' => 'nullable|string|max:255',
            
            // Vendor/Financier Information
            'financing_institution' => 'required|string|max:255',
            'financier_contact_person' => 'nullable|string|max:255',
            'financier_phone' => 'nullable|string|max:20',
            'financier_email' => 'nullable|email|max:255',
            'financier_agreement_ref' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Handle photo uploads
            $photosPaths = [];
            if ($request->hasFile('vehicle_photos')) {
                foreach ($request->file('vehicle_photos') as $photo) {
                    $path = $photo->store('fleet_photos', 'public');
                    $photosPaths[] = $path;
                }
            }

            // Calculate financial details
            $purchasePrice = $request->purchase_price;
            $downPayment = $request->down_payment;
            $interestRate = $request->interest_rate / 100;
            $months = $request->loan_duration_months;
            
            $principalAmount = $purchasePrice - $downPayment;
            $totalInterest = $principalAmount * $interestRate * ($months / 12);
            $totalAmountPayable = $principalAmount + $totalInterest;
            $monthlyInstallment = $totalAmountPayable / $months;
            
            $fleetAcquisition = FleetAcquisition::create(array_merge($request->all(), [
                'vehicle_photos' => $photosPaths,
                'monthly_installment' => $monthlyInstallment,
                'total_interest' => $totalInterest,
                'total_amount_payable' => $totalAmountPayable,
                'outstanding_balance' => $totalAmountPayable,
                'amount_paid' => 0,
                'payments_made' => 0,
                'status' => 'pending'
            ]));

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Fleet acquisition record created successfully',
                'data' => $fleetAcquisition
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating fleet acquisition record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $fleetAcquisition
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fleet acquisition not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $fleetAcquisition = FleetAcquisition::findOrFail($id);
        
        $request->validate([
            // Vehicle Information
            'vehicle_make' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'engine_capacity' => 'required|string|max:50',
            'chassis_number' => 'required|string|max:255|unique:fleet_acquisitions,chassis_number,' . $id,
            'engine_number' => 'required|string|max:255|unique:fleet_acquisitions,engine_number,' . $id,
            'registration_number' => 'nullable|string|max:20',
            'vehicle_category' => 'required|in:commercial,passenger,utility,special_purpose',
            'purchase_price' => 'required|numeric|min:0',
            'market_value' => 'required|numeric|min:0',
            'vehicle_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            
            // Financial Details
            'down_payment' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'loan_duration_months' => 'required|integer|min:1|max:120',
            'first_payment_date' => 'required|date',
            'insurance_premium' => 'nullable|numeric|min:0',
            
            // Other fields validation...
            'hp_agreement_number' => 'required|string|max:255|unique:fleet_acquisitions,hp_agreement_number,' . $id,
            'financing_institution' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Handle photo uploads
            $photosPaths = $fleetAcquisition->vehicle_photos ?? [];
            
            if ($request->hasFile('vehicle_photos')) {
                // Delete old photos if replacing
                if ($request->has('replace_photos') && $request->replace_photos == '1') {
                    foreach ($photosPaths as $oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    }
                    $photosPaths = [];
                }
                
                // Add new photos
                foreach ($request->file('vehicle_photos') as $photo) {
                    $path = $photo->store('fleet_photos', 'public');
                    $photosPaths[] = $path;
                }
            }
            
            // Recalculate financial details
            $purchasePrice = $request->purchase_price;
            $downPayment = $request->down_payment;
            $interestRate = $request->interest_rate / 100;
            $months = $request->loan_duration_months;
            
            $principalAmount = $purchasePrice - $downPayment;
            $totalInterest = $principalAmount * $interestRate * ($months / 12);
            $totalAmountPayable = $principalAmount + $totalInterest;
            $monthlyInstallment = $totalAmountPayable / $months;
            
            $fleetAcquisition->update(array_merge($request->all(), [
                'vehicle_photos' => $photosPaths,
                'monthly_installment' => $monthlyInstallment,
                'total_interest' => $totalInterest,
                'total_amount_payable' => $totalAmountPayable,
                'outstanding_balance' => $totalAmountPayable - $fleetAcquisition->amount_paid,
            ]));

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Fleet acquisition record updated successfully',
                'data' => $fleetAcquisition
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating fleet acquisition record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            
            // Delete associated photos
            if ($fleetAcquisition->vehicle_photos) {
                foreach ($fleetAcquisition->vehicle_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            
            $fleetAcquisition->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Fleet acquisition record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting fleet acquisition record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            $fleetAcquisition->update(['status' => 'approved']);
            
            return response()->json([
                'success' => true,
                'message' => 'Fleet acquisition approved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving fleet acquisition: ' . $e->getMessage()
            ], 500);
        }
    }

    public function manage($id)
    {
        try {
            $fleet = FleetAcquisition::with('payments')->findOrFail($id);
            $payments = $fleet->payments()->orderBy('payment_date', 'desc')->get();
            
            // ===== ACCURATE CALCULATIONS =====
            
            // 1. Calculate the loan amount (what needs to be paid after down payment)
            $loanAmount = $fleet->purchase_price - $fleet->down_payment;
            
            // 2. Get only CONFIRMED payments (exclude pending)
            $confirmedPayments = $fleet->payments()
                ->where('status', 'confirmed')
                ->sum('payment_amount');
            
            // 3. Calculate accurate outstanding balance
            $accurateOutstanding = max(0, $loanAmount - $confirmedPayments);
            
            // 4. Calculate accurate paid percentage
            $paidPercentage = 0;
            if ($loanAmount > 0) {
                $paidPercentage = min(100, ($confirmedPayments / $loanAmount) * 100);
            }
            
            // 5. Count confirmed payments for progress display
            $confirmedPaymentsCount = $fleet->payments()
                ->where('status', 'confirmed')
                ->count();
            
            // ===== UPDATE FLEET OBJECT WITH ACCURATE VALUES =====
            
            // Update the fleet object with calculated values (for display)
            $fleet->amount_paid = $confirmedPayments;
            $fleet->outstanding_balance = $accurateOutstanding;
            $fleet->payments_made = $confirmedPaymentsCount;
            
            // ===== RECALCULATE PAYMENT BALANCES =====
            
            // Get all confirmed payments in chronological order
            $confirmedPaymentsList = $fleet->payments()
                ->where('status', 'confirmed')
                ->orderBy('payment_date')
                ->orderBy('created_at')
                ->get();
            
            // Recalculate running balances for each payment
            $runningBalance = $loanAmount; // Start with total loan amount
            
            foreach ($confirmedPaymentsList as $payment) {
                // Set balance before this payment
                $payment->balance_before = $runningBalance;
                
                // Subtract payment amount
                $runningBalance -= $payment->payment_amount;
                
                // Set balance after this payment (never go below 0)
                $payment->balance_after = max(0, $runningBalance);
                
                // Save the updated balances to database
                $payment->save();
            }
            
            // ===== PERSIST ACCURATE VALUES TO DATABASE =====
            
            // Update the fleet record with accurate values
            $fleet->update([
                'amount_paid' => $confirmedPayments,
                'outstanding_balance' => $accurateOutstanding
            ]);
            
            // ===== AUTO-UPDATE STATUS BASED ON PAYMENT PROGRESS =====
            
            if ($accurateOutstanding <= 0 && $confirmedPayments > 0) {
                // Loan is fully paid
                if ($fleet->status !== 'completed') {
                    $fleet->update(['status' => 'completed']);
                    $fleet->status = 'completed'; // Update the object too
                }
            } elseif ($confirmedPayments > 0 && $fleet->status === 'approved') {
                // First payment made, change to active
                $fleet->update(['status' => 'active']);
                $fleet->status = 'active'; // Update the object too
            }
            
            // ===== ADDITIONAL DATA FOR VIEW =====
            
            // Add computed properties for JavaScript
            $fleet->loan_amount = $loanAmount;
            $fleet->confirmed_payments_total = $confirmedPayments;
            $fleet->confirmed_payments_count = $confirmedPaymentsCount;
            
            // Debug information (remove in production)
            $debugInfo = [
                'purchase_price' => $fleet->purchase_price,
                'down_payment' => $fleet->down_payment,
                'loan_amount' => $loanAmount,
                'confirmed_payments' => $confirmedPayments,
                'outstanding_calculated' => $accurateOutstanding,
                'paid_percentage' => round($paidPercentage, 2),
                'total_payments_count' => $payments->count(),
                'confirmed_payments_count' => $confirmedPaymentsCount,
                'pending_payments_count' => $payments->where('status', 'pending')->count(),
            ];
            
            // Log for debugging (remove in production)
            \Log::info("Fleet {$id} accurate calculations:", $debugInfo);
            
            return view('fleetacquisition.manage-fleet', compact('fleet', 'payments', 'paidPercentage'));
            
        } catch (\Exception $e) {
            \Log::error("Error in manage fleet {$id}: " . $e->getMessage());
            return redirect()->route('fleetacquisition')->with('error', 'Fleet acquisition not found.');
        }
    }

    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cheque,cash,mobile_money',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            $fleet = FleetAcquisition::findOrFail($id);
            
            // Validate payment amount doesn't exceed outstanding balance
            if ($request->payment_amount > $fleet->outstanding_balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed outstanding balance.'
                ], 400);
            }
            
            // Get current balance
            $balanceBefore = $fleet->outstanding_balance;
            $balanceAfter = $balanceBefore - $request->payment_amount;
            
            // Create payment record
            $payment = FleetPayment::create([
                'fleet_acquisition_id' => $fleet->id,
                'payment_amount' => $request->payment_amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_number' => $fleet->payments()->count() + 1,
                'notes' => $request->notes,
                'status' => 'confirmed',
                'processed_by' => auth()->user()->name ?? 'System'
            ]);
            
            // Update fleet acquisition
            $fleet->amount_paid += $request->payment_amount;
            $fleet->outstanding_balance = $balanceAfter;
            $fleet->payments_made += 1;
            
            // Update status if needed
            if ($fleet->status == 'approved') {
                $fleet->status = 'active';
            }
            
            if ($fleet->outstanding_balance <= 0) {
                $fleet->status = 'completed';
                $fleet->completion_date = now();
            }
            
            $fleet->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment' => $payment,
                    'new_balance' => $fleet->outstanding_balance,
                    'paid_percentage' => ($fleet->amount_paid / $fleet->total_amount_payable) * 100
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePhoto(Request $request, $id)
    {
        try {
            $fleetAcquisition = FleetAcquisition::findOrFail($id);
            $photoIndex = $request->photo_index;
            
            if (isset($fleetAcquisition->vehicle_photos[$photoIndex])) {
                $photoPath = $fleetAcquisition->vehicle_photos[$photoIndex];
                Storage::disk('public')->delete($photoPath);
                
                $photos = $fleetAcquisition->vehicle_photos;
                unset($photos[$photoIndex]);
                $photos = array_values($photos); // Re-index array
                
                $fleetAcquisition->update(['vehicle_photos' => $photos]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Photo deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Photo not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting photo: ' . $e->getMessage()
            ], 500);
        }
    }
}