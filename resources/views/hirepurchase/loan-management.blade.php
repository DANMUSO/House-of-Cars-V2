<x-app-layout>
<div class="container-fluid">
    <!-- Header Section -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center">
                <a href="{{ route('hire-purchase.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <div>
                    <h4 class="fs-18 fw-semibold m-0">
                        <i class="fas fa-car me-2"></i>
                        @if($agreement->customerVehicle)
                            {{ $agreement->customerVehicle->vehicle_make }} ({{ $agreement->customerVehicle->year ?? 'N/A' }})
                        @elseif($agreement->carImport)
                            {{ $agreement->carImport->make }} {{ $agreement->carImport->model }} ({{ $agreement->carImport->year ?? 'N/A' }})
                        @else
                            Vehicle Details Not Available
                        @endif
                    </h4>
                    <small class="text-muted">
                        @if($agreement->customerVehicle)
                            @if($agreement->customerVehicle->chasis_no)
                                Chassis: {{ $agreement->customerVehicle->chasis_no }}
                            @elseif($agreement->customerVehicle->number_plate)
                                Plate: {{ $agreement->customerVehicle->number_plate }}
                            @else
                                Customer Vehicle ID: {{ $agreement->customerVehicle->id }}
                            @endif
                        @elseif($agreement->carImport)
                            @if($agreement->carImport->chassis_number)
                                Chassis: {{ $agreement->carImport->chassis_number }}
                            @elseif($agreement->carImport->plate_number)
                                Plate: {{ $agreement->carImport->plate_number }}
                            @else
                                Import ID: {{ $agreement->carImport->id }}
                            @endif
                        @else
                            Agreement ID: {{ $agreement->id }}
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <!-- 1. First, add this CSS in your <head> section or main CSS file -->
<style>
/* Lump Sum Payment Button Styling */
.lump-sum-btn {
    background: linear-gradient(45deg, #28a745, #20c997) !important;
    border: none !important;
    color: white !important;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
}

.lump-sum-btn:hover {
    background: linear-gradient(45deg, #218838, #1bb185) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    color: white !important;
}

.lump-sum-btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    color: white !important;
}

.lump-sum-btn:active {
    background: linear-gradient(45deg, #1e7e34, #17a2b8) !important;
    color: white !important;
}

/* Enhanced Lump Sum Modal Styles */
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.step-indicator {
    position: relative;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.step-item {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.step-item.active .step-circle {
    background: #28a745;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

.step-item.active .step-label {
    color: #28a745;
}

/* Rescheduling Options Enhanced Styling */
.rescheduling-option {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-bottom: 15px;
    background: #fff;
}

.rescheduling-option:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.1);
    transform: translateY(-2px);
}

.rescheduling-option.selected {
    border-color: #28a745;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(40, 167, 69, 0.02) 100%);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.15);
}

.rescheduling-option .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.option-details {
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Payment breakdown styling */
#paymentBreakdown {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #17a2b8;
}

/* Quick amount buttons */
.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    margin-right: 4px;
}

/* Modal enhancements */
.modal-lg {
    max-width: 900px;
}

.form-control-lg {
    font-size: 1.1rem;
    font-weight: 500;
}

/* Loading states */
.calculating-pulse {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}

/* Input focus states */
.form-control:focus,
.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Animation for step transitions */
.step-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>

<!-- 2. Replace your existing button section with this corrected version -->
<div class="d-flex gap-2">
    @php
        $statusConfig = [
            'pending' => ['class' => 'warning', 'text' => 'Pending'],
            'approved' => ['class' => 'success', 'text' => 'Active'],
            'completed' => ['class' => 'primary', 'text' => 'Completed'],
            'defaulted' => ['class' => 'danger', 'text' => 'Defaulted']
        ];
        $currentStatus = $statusConfig[strtolower($agreement->status)] ?? $statusConfig['pending'];
    @endphp
    <span class="badge bg-{{ $currentStatus['class'] }} fs-6 px-3 py-2">
        {{ $currentStatus['text'] }}
    </span>
    
    @if($agreement->status !== 'completed')
        <!-- Regular Payment Button -->
          @if(in_array(Auth::user()->role, ['Accountant','Managing-Director']))
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
            <i class="fas fa-credit-card"></i> Record Payment 
        </button>
        
        <!-- Lump Sum Payment Button - CORRECTED -->
        <button type="button" class="btn lump-sum-btn"  id="lumpSumPaymentBtn" onclick="openLumpSumModal()">
            <i class="fas fa-money-bill-wave"></i> Lump Sum & Reschedule
        </button>
        @endif
    @endif
</div>

<!-- 3. Fixed and Complete Lump Sum Payment Modal -->
<div class="modal fade" id="lumpSumPaymentModal" data-bs-backdrop="false" tabindex="-1" aria-labelledby="lumpSumPaymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title" id="lumpSumPaymentModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>Lump Sum Payment & Loan Rescheduling
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step Indicator -->
                <div class="step-indicator mb-4">
                    <div class="d-flex justify-content-between">
                        <div class="step-item active" data-step="1">
                            <div class="step-circle">1</div>
                            <div class="step-label">Payment Details</div>
                        </div>
                        <div class="step-item" data-step="2">
                            <div class="step-circle">2</div>
                            <div class="step-label">Rescheduling Options</div>
                        </div>
                        <div class="step-item" data-step="3">
                            <div class="step-circle">3</div>
                            <div class="step-label">Review & Confirm</div>
                        </div>
                    </div>
                </div>

                <form id="lumpSumPaymentForm">
                    @csrf
                    <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                    
                    <!-- Step 1: Payment Details -->
                    <div class="step-content" id="step-1">
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Current Loan Status</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Outstanding Balance:</strong> KSh {{ number_format($actualOutstanding, 2) }}</p>
                                    <p class="mb-1"><strong>Monthly Payment:</strong> KSh {{ number_format($agreement->monthly_payment, 2) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Remaining Months:</strong> {{ $agreement->payments_remaining ?? 0 }}</p>
                                    @if($nextDueInstallment)
                                        <p class="mb-0"><strong>Next Due:</strong> {{ \Carbon\Carbon::parse($nextDueInstallment->due_date)->format('M d, Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lump Sum Amount (KSh) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">KSh</span>
                                        <input type="number" 
                                               class="form-control form-control-lg" 
                                               name="payment_amount" 
                                               id="lumpSumAmount"
                                               required 
                                               min="100" 
                                               max="{{ $actualOutstanding }}"
                                               step="0.01"
                                               placeholder="Enter amount">
                                    </div>
                                    <small class="text-muted">Maximum: KSh {{ number_format($actualOutstanding, 2) }}</small>
                                    
                                    <!-- Quick Amount Buttons -->
                                    <div class="mt-2">
                                        <small class="text-muted d-block mb-1">Quick amounts:</small>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="setLumpSumAmount({{ $agreement->monthly_payment }})">
                                                1 Month
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="setLumpSumAmount({{ $agreement->monthly_payment * 3 }})">
                                                3 Months
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="setLumpSumAmount({{ $agreement->monthly_payment * 6 }})">
                                                6 Months
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="setLumpSumAmount({{ $actualOutstanding }})">
                                                Full Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="mpesa">M-Pesa</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="card">Card Payment</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Reference Number</label>
                                    <input type="text" class="form-control" name="payment_reference" placeholder="Transaction/Receipt Number">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="payment_notes" rows="2" placeholder="Additional notes about this payment"></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="proceedToRescheduling()" id="nextToOptionsBtn">
                                Next: Choose Rescheduling Option <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Rescheduling Options -->
                    <div class="step-content d-none" id="step-2">
                        <div class="alert alert-warning mb-4">
                            <h6 class="alert-heading"><i class="fas fa-calculator me-2"></i>Payment Application Preview</h6>
                            <div id="paymentBreakdown" class="calculating-pulse">
                                <p>Enter lump sum amount to see payment breakdown...</p>
                            </div>
                        </div>

                        <h6 class="mb-3">Choose Your Rescheduling Option:</h6>
                         <!-- FIXED: Option 1: Reduce Duration -->
<div class="rescheduling-option" onclick="selectReschedulingOption('reduce_duration')">
    <div class="form-check">
        <input class="form-check-input" type="radio" name="reschedule_option" id="reduceDuration" value="reduce_duration">
        <label class="form-check-label w-100" for="reduceDuration">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-2 text-primary">
                        <i class="fas fa-clock me-2"></i>Reduce Loan Duration
                    </h6>
                    <p class="mb-2 text-muted">Keep your current monthly payment and finish the loan earlier</p>
                    <div id="durationOption" class="option-details text-success">
                        <small>Loading calculation...</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary">Faster Completion</span>
                </div>
            </div>
        </label>
    </div>
</div>

<!-- FIXED: Option 2: Reduce Monthly Payment -->
<div class="rescheduling-option" onclick="selectReschedulingOption('reduce_installment')">
    <div class="form-check">
        <input class="form-check-input" type="radio" name="reschedule_option" id="reducePayment" value="reduce_installment">
        <label class="form-check-label w-100" for="reducePayment">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-2 text-success">
                        <i class="fas fa-money-bill-wave me-2"></i>Reduce Monthly Payment
                    </h6>
                    <p class="mb-2 text-muted">Lower your monthly payments while keeping the same loan duration</p>
                    <div id="paymentOption" class="option-details text-success">
                        <small>Loading calculation...</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-success">Lower Payments</span>
                </div>
            </div>
        </label>
    </div>
</div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="goToStep(1)">
                                <i class="fas fa-arrow-left me-2"></i>Back to Payment Details
                            </button>
                            <button type="button" class="btn btn-primary" onclick="proceedToReview()" disabled id="proceedToReviewBtn">
                                Next: Review & Confirm <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Review & Confirm -->
                    <div class="step-content d-none" id="step-3">
                        <div class="alert alert-success mb-4">
                            <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Review Your Rescheduling</h6>
                            <p class="mb-0">Please review the details below before confirming your lump sum payment and loan rescheduling.</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="card-title mb-0">Payment Summary</h6>
                                    </div>
                                    <div class="card-body" id="paymentSummary">
                                        <!-- Payment details will be populated here -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success bg-opacity-10">
                                        <h6 class="card-title mb-0">New Loan Terms</h6>
                                    </div>
                                    <div class="card-body" id="newLoanTerms">
                                        <!-- New loan terms will be populated here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-warning mt-3">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Important Notice
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>This rescheduling will take effect immediately upon confirmation</li>
                                    <li>Your payment schedule will be updated automatically</li>
                                    <li>A new payment schedule will be generated</li>
                                    <li>This action cannot be undone once processed</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="confirmRescheduling" required>
                            <label class="form-check-label" for="confirmRescheduling">
                                I understand and agree to the new loan terms and confirm this rescheduling
                            </label>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="goToStep(2)">
                                <i class="fas fa-arrow-left me-2"></i>Back to Options
                            </button>
                            <button type="submit" class="btn btn-success btn-lg" id="confirmReschedulingBtn">
                                <i class="fas fa-check me-2"></i>Confirm Payment & Rescheduling
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 4. Fixed JavaScript - Place this at the bottom of your page before </body> -->
<script>
    const modal = document.getElementById('lumpSumPaymentModal');

modal.addEventListener('shown.bs.modal', function () {
  document.body.classList.remove('modal-open');
  document.body.style.overflow = 'auto'; // enable scrolling
});
// Make sure jQuery and Bootstrap are loaded first
document.addEventListener('DOMContentLoaded', function() {
    // Check if required dependencies are loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is required for lump sum functionality');
        return;
    }
    
    if (typeof bootstrap === 'undefined' && typeof $.fn.modal === 'undefined') {
        console.error('Bootstrap is required for modal functionality');
        return;
    }

    // Global variables for lump sum modal
    window.currentStep = 1;
    window.reschedulingOptions = null;
    window.selectedOption = null;

    // Initialize the modal functionality
    initializeLumpSumModal();
});

// Function to open lump sum modal
function openLumpSumModal() {
    console.log('Opening lump sum modal');
    
    // Reset modal state
    resetLumpSumModal();
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('lumpSumPaymentModal'));
    modal.show();
    // Remove Bootstrap's scroll lock
document.body.classList.remove('modal-open');
document.body.style.overflow = 'auto';
}

// Initialize lump sum modal functionality
function initializeLumpSumModal() {
    console.log('Initializing lump sum modal functionality');
    
    // Ensure the button is clickable
    const lumpSumBtn = document.getElementById('lumpSumPaymentBtn');
    if (lumpSumBtn) {
        lumpSumBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Lump sum button clicked');
            openLumpSumModal();
        });
    }

    // Form submission handler
    const form = document.getElementById('lumpSumPaymentForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmission);
    }

    // Reset modal when closed
    const modal = document.getElementById('lumpSumPaymentModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', resetLumpSumModal);
    }

    // Auto-calculate when amount changes in step 2
    const lumpSumInput = document.getElementById('lumpSumAmount');
    if (lumpSumInput) {
        lumpSumInput.addEventListener('input', function() {
            if (window.currentStep === 2) {
                clearTimeout(this.calculateTimeout);
                this.calculateTimeout = setTimeout(calculateReschedulingOptions, 500);
            }
        });
    }
}

// Set lump sum amount from quick buttons
function setLumpSumAmount(amount) {
    const maxAmount = {{ $actualOutstanding }};
    const finalAmount = Math.min(amount, maxAmount);
    const input = document.getElementById('lumpSumAmount');
    if (input) {
        input.value = finalAmount;
        
        // Trigger calculation if we're on step 2
        if (window.currentStep === 2) {
            calculateReschedulingOptions();
        }
    }
}

// Proceed to rescheduling options
function proceedToRescheduling() {
    const amount = parseFloat(document.getElementById('lumpSumAmount').value);
    const paymentDate = document.querySelector('input[name="payment_date"]').value;
    const paymentMethod = document.querySelector('select[name="payment_method"]').value;
    
    console.log('Proceeding to rescheduling with:', { amount, paymentDate, paymentMethod });
    
    // Validate required fields
    if (!amount || amount <= 0) {
        showError('Please enter a valid lump sum amount');
        return;
    }
    
    if (amount < 100) {
        showError('Minimum lump sum amount is KSh 100');
        return;
    }
    
    if (!paymentDate) {
        showError('Please select a payment date');
        return;
    }
    
    if (!paymentMethod) {
        showError('Please select a payment method');
        return;
    }
    
    if (amount > {{ $actualOutstanding }}) {
        showError(`Amount cannot exceed outstanding balance of KSh {{ number_format($actualOutstanding, 2) }}`);
        return;
    }
    
    // Proceed to step 2
    goToStep(2);
    calculateReschedulingOptions();
}

function calculateReschedulingOptions() {
    const amount = parseFloat(document.getElementById('lumpSumAmount').value);
    
    if (!amount || amount <= 0) {
        return;
    }
    
    console.log('Calculating rescheduling options for amount:', amount);
    
    // Show loading state
    document.getElementById('paymentBreakdown').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm me-2"></div>Calculating options...</div>';
    document.getElementById('durationOption').innerHTML = '<small class="text-muted">Calculating...</small>';
    document.getElementById('paymentOption').innerHTML = '<small class="text-muted">Calculating...</small>';
    
    // Make AJAX call to get rescheduling options
    $.ajax({
        url: '/hire-purchase/rescheduling-options',
        method: 'GET',
        data: {
            agreement_id: {{ $agreement->id }},
            lump_sum_amount: amount
        },
        success: function(response) {
            console.log('Rescheduling options received:', response);
            window.reschedulingOptions = response;
            
            if (response.completion) {
                // Full payment scenario
                document.getElementById('paymentBreakdown').innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-trophy me-2"></i><strong>Congratulations!</strong> 
                        This payment will fully complete your loan.
                    </div>
                `;
                document.getElementById('durationOption').innerHTML = '<small class="text-success">Loan will be completed</small>';
                document.getElementById('paymentOption').innerHTML = '<small class="text-success">Loan will be completed</small>';
                
                // Auto-select completion and enable proceed button
                window.selectedOption = 'complete';
                document.getElementById('proceedToReviewBtn').disabled = false;
                
            } else {
                // Show payment breakdown
                document.getElementById('paymentBreakdown').innerHTML = `
                    <div class="row">
                        <div class="col-6">
                            <strong>Current Outstanding:</strong><br>
                            <span class="text-primary">KSh ${response.current_outstanding.toLocaleString()}</span>
                        </div>
                        <div class="col-6">
                            <strong>New Outstanding:</strong><br>
                            <span class="text-success">KSh ${response.new_outstanding.toLocaleString()}</span>
                        </div>
                    </div>
                `;
                
                // FIXED: Show option 1 details (reduce duration)
                if (response.option_1) {
                    document.getElementById('durationOption').innerHTML = `
                        <strong>Duration: ${response.option_1.current_duration} → ${response.option_1.new_duration} months</strong><br>
                        <small class="text-success">Save ${response.option_1.duration_reduction} months</small><br>
                        <small class="text-muted">Monthly payment stays KSh ${response.option_1.monthly_payment.toLocaleString()}</small>
                    `;
                } else {
                    document.getElementById('durationOption').innerHTML = '<small class="text-warning">Calculation error</small>';
                }
                
                // FIXED: Show option 2 details (reduce payment)
                if (response.option_2) {
                        console.log('Option 2 received:', response.option_2); // Debug log
                        
                        // Try multiple field names to ensure we get the right values
                        const currentPayment = response.option_2.current_payment || 
                                            response.option_2.original_monthly_payment || 
                                            response.option_2.monthly_payment;
                        
                        const newPayment = response.option_2.new_payment || 
                                        response.option_2.new_monthly_payment;
                        
                        const paymentReduction = response.option_2.payment_reduction || 0;
                        
                        const duration = response.option_2.duration || 
                                        response.option_2.remaining_duration || 
                                        response.option_2.new_duration;
                        
                        console.log('Parsed values:', { currentPayment, newPayment, paymentReduction, duration }); // Debug log
                        
                        if (currentPayment && newPayment && paymentReduction > 0) {
                            document.getElementById('paymentOption').innerHTML = `
                                <strong>Payment: KSh ${currentPayment.toLocaleString()} → KSh ${newPayment.toLocaleString()}</strong><br>
                                <small class="text-success">Save KSh ${paymentReduction.toLocaleString()} monthly</small><br>
                                <small class="text-muted">Duration stays ${duration} months</small>
                            `;
                        } else {
                            console.error('Missing option 2 values:', response.option_2);
                            document.getElementById('paymentOption').innerHTML = '<small class="text-warning">Calculation incomplete - missing values</small>';
                        }
                    } else {
                        console.error('No option_2 in response');
                        document.getElementById('paymentOption').innerHTML = '<small class="text-warning">Option 2 not available</small>';
                    }
            }
        },
        error: function(xhr) {
            console.error('Failed to calculate rescheduling options:', xhr);
            let errorMessage = 'Failed to calculate rescheduling options. Please try again.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 500) {
                errorMessage = 'Server error during calculation. Please check the server logs.';
            }
            
            // Show error in UI
            document.getElementById('paymentBreakdown').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}
                </div>
            `;
            document.getElementById('durationOption').innerHTML = '<small class="text-danger">Error</small>';
            document.getElementById('paymentOption').innerHTML = '<small class="text-danger">Error</small>';
            
            showError(errorMessage);
        }
    });
}


function selectReschedulingOption(option) {
    console.log('Selecting rescheduling option:', option);
    window.selectedOption = option;
    
    // Remove previous selections from all options
    document.querySelectorAll('.rescheduling-option').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Remove checked state from all radio buttons
    document.querySelectorAll('input[name="reschedule_option"]').forEach(radio => {
        radio.checked = false;
    });
    
    // Add selection to the clicked option
    const clickedOption = event.currentTarget;
    clickedOption.classList.add('selected');
    
    // FIXED: Properly set the radio button based on option value
    let radioId;
    if (option === 'reduce_duration') {
        radioId = 'reduceDuration';
    } else if (option === 'reduce_installment') {
        radioId = 'reducePayment';
    } else {
        console.error('Unknown option:', option);
        return;
    }
    
    console.log('Setting radio button:', radioId, 'for option:', option);
    const radioElement = document.getElementById(radioId);
    if (radioElement) {
        radioElement.checked = true;
        console.log('Radio button checked successfully:', radioElement.value);
        
        // Verify the radio is actually checked
        setTimeout(() => {
            const checkedRadio = document.querySelector('input[name="reschedule_option"]:checked');
            console.log('Verified checked radio:', checkedRadio ? checkedRadio.value : 'none');
        }, 100);
    } else {
        console.error('Radio button not found:', radioId);
    }
    
    // Enable proceed button
    const proceedBtn = document.getElementById('proceedToReviewBtn');
    if (proceedBtn) {
        proceedBtn.disabled = false;
        console.log('Proceed button enabled');
    }
}
// FIXED: Proceed to review with better validation
function proceedToReview() {
    console.log('Proceeding to review...');
    console.log('Selected option:', window.selectedOption);
    console.log('Rescheduling options:', window.reschedulingOptions);
    
    // Check if it's a completion scenario
    if (window.reschedulingOptions?.completion) {
        console.log('Loan completion scenario detected');
        populateReviewSection();
        goToStep(3);
        return;
    }
    
    // Validate selection for regular rescheduling
    if (!window.selectedOption) {
        showError('Please select a rescheduling option');
        return;
    }
    
    // Double-check that a radio button is actually selected
    const checkedRadio = document.querySelector('input[name="reschedule_option"]:checked');
    if (!checkedRadio) {
        console.error('No radio button is checked despite selectedOption being set');
        showError('Please select a rescheduling option');
        return;
    }
    
    console.log('Proceeding to review with option:', window.selectedOption);
    console.log('Checked radio value:', checkedRadio.value);
    
    // Ensure the selectedOption matches the radio value
    if (window.selectedOption !== checkedRadio.value) {
        console.warn('Selected option mismatch, correcting...');
        window.selectedOption = checkedRadio.value;
    }
    
    // Populate review section and proceed
    populateReviewSection();
    goToStep(3);
}

// Populate review section
function populateReviewSection() {
    const amount = parseFloat(document.getElementById('lumpSumAmount').value);
    const paymentDate = document.querySelector('input[name="payment_date"]').value;
    const paymentMethod = document.querySelector('select[name="payment_method"]').value;
    const reference = document.querySelector('input[name="payment_reference"]').value;
    
    console.log('Populating review section');
    
    // Payment summary
    document.getElementById('paymentSummary').innerHTML = `
        <table class="table table-sm table-borderless">
            <tr>
                <td><strong>Amount:</strong></td>
                <td>KSh ${amount.toLocaleString()}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td>${new Date(paymentDate).toLocaleDateString()}</td>
            </tr>
            <tr>
                <td><strong>Method:</strong></td>
                <td>${paymentMethod.replace('_', ' ').toUpperCase()}</td>
            </tr>
            ${reference ? `<tr><td><strong>Reference:</strong></td><td>${reference}</td></tr>` : ''}
        </table>
    `;
    
    // New loan terms
    if (window.reschedulingOptions?.completion) {
        document.getElementById('newLoanTerms').innerHTML = `
            <div class="text-center">
                <i class="fas fa-trophy fa-2x text-success mb-2"></i>
                <h6 class="text-success">Loan Completed!</h6>
                <p class="mb-0">This payment will fully settle your loan.</p>
            </div>
        `;
    } else if (window.selectedOption === 'reduce_duration') {
        const option = window.reschedulingOptions.option_1;
        document.getElementById('newLoanTerms').innerHTML = `
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>New Duration:</strong></td>
                    <td>${option.new_duration} months</td>
                </tr>
                <tr>
                    <td><strong>Monthly Payment:</strong></td>
                    <td>KSh ${option.monthly_payment.toLocaleString()}</td>
                </tr>
                <tr>
                    <td><strong>Time Saved:</strong></td>
                    <td class="text-success">${option.duration_reduction} months</td>
                </tr>
                <tr>
                    <td><strong>New Balance:</strong></td>
                    <td>KSh ${window.reschedulingOptions.new_outstanding.toLocaleString()}</td>
                </tr>
            </table>
        `;
    } else {
        const option = window.reschedulingOptions.option_2;
        const newPayment = option.new_payment || option.new_monthly_payment;
        const duration = option.duration || option.remaining_duration;
        const paymentReduction = option.payment_reduction;
        
        document.getElementById('newLoanTerms').innerHTML = `
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>New Monthly Payment:</strong></td>
                    <td>KSh ${newPayment.toLocaleString()}</td>
                </tr>
                <tr>
                    <td><strong>Duration:</strong></td>
                    <td>${duration} months</td>
                </tr>
                <tr>
                    <td><strong>Monthly Savings:</strong></td>
                    <td class="text-success">KSh ${paymentReduction.toLocaleString()}</td>
                </tr>
                <tr>
                    <td><strong>New Balance:</strong></td>
                    <td>KSh ${window.reschedulingOptions.new_outstanding.toLocaleString()}</td>
                </tr>
            </table>
        `;
    }
}

// Navigate to step
function goToStep(step) {
    console.log('Going to step:', step);
    
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(el => {
        el.classList.add('d-none');
    });
    
    // Show target step
    const targetStep = document.getElementById(`step-${step}`);
    if (targetStep) {
        targetStep.classList.remove('d-none');
    }
    
    // Update step indicator
    document.querySelectorAll('.step-item').forEach((el, index) => {
        if (index + 1 <= step) {
            el.classList.add('active');
        } else {
            el.classList.remove('active');
        }
    });
    
    window.currentStep = step;
}

// Handle form submission
function handleFormSubmission(e) {
    e.preventDefault();
    
    console.log('Handling form submission');
    
    const confirmCheckbox = document.getElementById('confirmRescheduling');
    if (!confirmCheckbox.checked) {
        showError('Please confirm that you agree to the new loan terms');
        return;
    }
    
    const formData = new FormData(e.target);
    
    // Add the selected reschedule option
    if (window.selectedOption) {
        formData.set('reschedule_option', window.selectedOption);
    }
    
    // Show loading state
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Processing Payment...',
            text: 'Please wait while we process your lump sum payment and reschedule your loan.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    } else {
        // Fallback if SweetAlert is not available
        const submitBtn = document.getElementById('confirmReschedulingBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    }
    
    // Submit the form
    $.ajax({
        url: '/hire-purchase/lump-sum-payment',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Lump sum payment successful:', response);
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('lumpSumPaymentModal'));
            if (modal) {
                modal.hide();
            }
            
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Success!',
                    html: `
                        <div class="text-start">
                            <p><strong>Payment recorded successfully!</strong></p>
                            <p><strong>New Balance:</strong> KSh ${response.new_balance ? response.new_balance.toLocaleString() : 'Updated'}</p>
                            <p>${response.rescheduling_details ? response.rescheduling_details.savings_message : 'Loan rescheduled successfully'}</p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    timer: 5000,
                    timerProgressBar: true
                }).then(() => {
                    location.reload();
                });
            } else {
                alert('Payment recorded successfully! Page will reload.');
                location.reload();
            }
        },
        error: function(xhr) {
            console.error('Lump sum payment failed:', xhr);
            
            let errorMessage = 'An error occurred while processing the payment.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            } else {
                alert('Error: ' + errorMessage);
                // Re-enable submit button
                const submitBtn = document.getElementById('confirmReschedulingBtn');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Payment & Rescheduling';
            }
        }
    });
}

// Reset modal state
function resetLumpSumModal() {
    console.log('Resetting lump sum modal');
    
    // Reset form
    const form = document.getElementById('lumpSumPaymentForm');
    if (form) {
        form.reset();
    }
    
    // Reset step to 1
    goToStep(1);
    
    // Reset global variables
    window.selectedOption = null;
    window.reschedulingOptions = null;
    
    // Reset UI elements
    const proceedBtn = document.getElementById('proceedToReviewBtn');
    if (proceedBtn) {
        proceedBtn.disabled = true;
    }
    
    // Clear selections
    document.querySelectorAll('.rescheduling-option').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Reset content areas
    const paymentBreakdown = document.getElementById('paymentBreakdown');
    if (paymentBreakdown) {
        paymentBreakdown.innerHTML = '<p>Enter lump sum amount to see payment breakdown...</p>';
    }
    
    const durationOption = document.getElementById('durationOption');
    if (durationOption) {
        durationOption.innerHTML = '<small>Loading calculation...</small>';
    }
    
    const paymentOption = document.getElementById('paymentOption');
    if (paymentOption) {
        paymentOption.innerHTML = '<small>Loading calculation...</small>';
    }
}

// Helper function to show errors
function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    } else {
        alert('Error: ' + message);
    }
}

// Helper function to show success messages
function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Success',
            text: message,
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        alert('Success: ' + message);
    }
}

// Debugging function - remove in production
function debugLumpSumModal() {
    console.log('=== Lump Sum Modal Debug Info ===');
    console.log('Current Step:', window.currentStep);
    console.log('Selected Option:', window.selectedOption);
    console.log('Rescheduling Options:', window.reschedulingOptions);
    console.log('Modal Element:', document.getElementById('lumpSumPaymentModal'));
    console.log('Button Element:', document.getElementById('lumpSumPaymentBtn'));
    console.log('Form Element:', document.getElementById('lumpSumPaymentForm'));
    console.log('jQuery Available:', typeof $ !== 'undefined');
    console.log('Bootstrap Available:', typeof bootstrap !== 'undefined');
    console.log('SweetAlert Available:', typeof Swal !== 'undefined');
    console.log('==================================');
}

// Add debugging to window for testing
window.debugLumpSumModal = debugLumpSumModal;

// Test function to manually trigger modal - remove in production
window.testLumpSumModal = function() {
    console.log('Testing lump sum modal manually');
    openLumpSumModal();
};
</script>
    </div>

    @php
        // Calculate accurate outstanding balance from payment schedule
        $totalScheduledAmount = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('total_amount') : 0;
        $totalPaidFromSchedule = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('amount_paid') : 0;
        $calculatedOutstanding = $totalScheduledAmount - $totalPaidFromSchedule;
        
        // Use the payment schedule calculation if it exists, otherwise use the agreement's outstanding balance
        $actualOutstanding = $totalScheduledAmount > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
        
        // Calculate total amount paid (including deposit)
        $totalAmountPaid = $agreement->deposit_amount + $agreement->amount_paid;
        
        // Calculate payment progress based on total amount
        $paymentProgress = $agreement->total_amount > 0 ? 
            (($totalAmountPaid) / $agreement->total_amount) * 100 : 0;
        
        // Next payment due calculation
        $nextDueInstallment = $agreement->paymentSchedule ? 
            $agreement->paymentSchedule->whereIn('status', ['pending', 'overdue', 'partial'])->first() : null;
        
        // Overdue amount calculation
        $overdueAmount = $agreement->paymentSchedule ? 
            $agreement->paymentSchedule->where('status', 'overdue')->sum('total_amount') : 0;
    @endphp

    <!-- Financial Overview Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-pie text-primary"></i> Financial Overview
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-muted mb-1">Purchase Price</h6>
                        <h4 class="mb-0 text-dark">KSh {{ number_format($agreement->vehicle_price, 0) }}</h4>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-soft-success rounded">
                        <h6 class="text-muted mb-1">Down Payment</h6>
                        <h4 class="mb-0 text-success">KSh {{ number_format($agreement->deposit_amount, 0) }}</h4>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-soft-info rounded">
                        <h6 class="text-muted mb-1">Amount Paid</h6>
                        <h4 class="mb-0 text-info">KSh {{ number_format($totalAmountPaid, 0) }}</h4>
                        <small class="text-muted">Deposit + Payments</small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-soft-danger rounded">
                        <h6 class="text-muted mb-1">Outstanding</h6>
                        <h4 class="mb-0 text-danger">KSh {{ number_format($actualOutstanding, 0) }}</h4>
                        @if($actualOutstanding != $agreement->outstanding_balance)
                            
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Payment Progress -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Payment Progress</h6>
                    <span class="text-end">
                        <strong>{{ number_format($paymentProgress, 1) }}% Complete</strong>
                    </span>
                </div>
                <div class="progress" style="height: 12px;">
                    @php
                        $progressClass = 'bg-danger';
                        if($paymentProgress >= 80) $progressClass = 'bg-success';
                        elseif($paymentProgress >= 50) $progressClass = 'bg-info';
                        elseif($paymentProgress >= 25) $progressClass = 'bg-warning';
                    @endphp
                    <div class="progress-bar {{ $progressClass }}" 
                         role="progressbar" 
                         style="width: {{ $paymentProgress }}%">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">{{ $agreement->payments_made }} of {{ $agreement->duration_months }} payments made</small>
                    <small class="text-muted">KSh {{ number_format($agreement->monthly_payment, 0) }} monthly</small>
                </div>
            </div>
        </div>
    </div>
    @if($agreement->is_rescheduled ?? false)
<div class="card mb-4 border-info">
    <div class="card-header bg-info bg-opacity-10">
        <h6 class="card-title mb-0 text-info">
            <i class="fas fa-sync-alt me-2"></i>Loan Rescheduling Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="text-center p-2">
                    <h6 class="text-muted mb-1">Rescheduled</h6>
                    <h5 class="mb-0 text-info">{{ $agreement->rescheduling_count ?? 0 }} times</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-2">
                    <h6 class="text-muted mb-1">Interest Saved</h6>
                    <h5 class="mb-0 text-primary">KSh {{ number_format($agreement->total_interest_savings ?? 0, 0) }}</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-2">
                    <h6 class="text-muted mb-1">Last Rescheduled</h6>
                    <h5 class="mb-0 text-muted">{{ $agreement->latest_rescheduling?->rescheduling_date?->format('M Y') ?? 'N/A' }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
    <!-- Navigation Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="loanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="payment-history-tab" data-bs-toggle="tab" 
                            data-bs-target="#payment-history" type="button" role="tab">
                        <i class="fas fa-history"></i> Payment History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payment-schedule-tab" data-bs-toggle="tab" 
                            data-bs-target="#payment-schedule" type="button" role="tab">
                        <i class="fas fa-calendar-alt"></i> Payment Schedule
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vehicle-details-tab" data-bs-toggle="tab" 
                            data-bs-target="#vehicle-details" type="button" role="tab">
                        <i class="fas fa-car"></i> Vehicle Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rescheduling-history-tab" data-bs-toggle="tab" 
                            data-bs-target="#rescheduling-history" type="button" role="tab">
                        <i class="fas fa-sync-alt"></i> Rescheduling History
                        @if($agreement->rescheduling_count ?? 0 > 0)
                            <span class="badge bg-info ms-1">{{ $agreement->rescheduling_count }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="legal-compliance-tab" data-bs-toggle="tab" 
                            data-bs-target="#legal-compliance" type="button" role="tab">
                        <i class="fas fa-file-contract"></i> Legal & Compliance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="agreement-document-tab" data-bs-toggle="tab" 
                            data-bs-target="#agreement-document" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Agreement Document
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content" id="loanTabsContent">
                
                <!-- Payment History Tab -->
                <div class="tab-pane fade show active" id="payment-history" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Payment History</h5>
                        @if($agreement->status !== 'completed')
                         @if(in_array(Auth::user()->role, ['Accountant','Managing-Director']))
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                                <i class="fas fa-plus"></i> Add Payment
                            </button>
                         @endif
                        @endif
                    </div>
 <!-- Receipt Download Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="receiptModalLabel">
                    <i class="fas fa-receipt me-2"></i>Payment Receipt
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="receiptContent">
                <!-- Receipt Content -->
                <div class="receipt-container" style="background: white; padding: 25px; font-family: 'Arial', sans-serif; width: 100%; max-width: 580px; margin: 0 auto; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <!-- Header Section -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #007bff;">
                        <!-- Company Details -->
                        <div style="flex: 1; padding-right: 20px;">
                            <h2 style="font-size: 18px; font-weight: bold; margin: 0 0 8px 0; color: #2c3e50;">Kelmer's House of Cars LTD</h2>
                            <div style="font-size: 12px; line-height: 1.4; color: #555;">
                                <div style="margin-bottom: 2px;">Jabavu Lane, Hurlingham</div>
                                <div style="margin-bottom: 2px;">P.O Box 9215 - 00100, Nairobi - Kenya</div>
                                <div style="margin: 4px 0 2px 0;"><strong>Email:</strong> info@kelmercars.co.ke</div>
                                <div><strong>Phone:</strong> +254 700 000 000</div>
                            </div>
                        </div>
                        
                        <!-- Logo -->
                        <div style="flex: 0 0 auto; text-align: center;">
                            <img src="{{ asset('dashboardv1/assets/images/houseofcars.png') }}" alt="Kelmer's House of Cars" style="height: 70px; width: auto;">
                        </div>
                    </div>

                    <!-- Receipt Title and Number -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="font-size: 32px; font-weight: bold; margin: 0 0 10px 0; letter-spacing: 4px; color: #2c3e50;">RECEIPT</h1>
                        <div style="background: #f8f9fa; padding: 6px 15px; border-radius: 4px; display: inline-block; border: 1px solid #dee2e6;">
                            <span style="font-size: 14px; font-weight: bold; color: #495057;">Receipt No: </span>
                            <span id="receiptNumber" style="font-size: 16px; font-weight: bold; color: #007bff;"></span>
                        </div>
                    </div>

                    <!-- Date and Customer Info -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 18px; border-left: 4px solid #007bff;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px;">
                            <div>
                                <span style="font-weight: bold; color: #495057;">Date: </span>
                                <span id="receiptDate" style="color: #6c757d;"></span>
                            </div>
                            <div>
                                <span style="font-weight: bold; color: #495057;">Time: </span>
                                <span id="receiptTime" style="color: #6c757d;"></span>
                            </div>
                        </div>
                        
                        <div style="font-size: 14px;">
                            <span style="font-weight: bold; color: #495057;">Received from: </span>
                            <span id="customerName" style="font-weight: bold; text-transform: uppercase; color: #2c3e50;"></span>
                        </div>
                    </div>

                    <!-- Payment Details Section -->
                    <div style="border: 2px solid #007bff; padding: 18px; margin-bottom: 18px; border-radius: 6px; background: #fff;">
                        <h3 style="margin: 0 0 15px 0; color: #007bff; font-size: 16px; text-align: center; font-weight: bold;">PAYMENT DETAILS</h3>
                        
                        <div style="margin-bottom: 15px; font-size: 13px;">
                            <span style="font-weight: bold; color: #495057;">Being payment of: </span>
                            <span id="paymentDescription" style="font-weight: bold; color: #28a745;"></span>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                            <div style="margin-bottom: 10px; font-size: 13px;">
                                <span style="font-weight: bold; color: #495057;">Amount (Digital): </span>
                                <span id="paymentAmount" style="font-size: 18px; font-weight: bold; color: #007bff;"></span>
                            </div>
                            <div style="font-size: 12px;">
                                <span style="font-weight: bold; color: #495057;">Amount (In Words): </span>
                                <span id="paymentAmountWords" style="font-style: italic; color: #6c757d; text-transform: capitalize;"></span>
                            </div>
                        </div>
                        
                        <div style="font-size: 13px;">
                            <span style="font-weight: bold; color: #495057;">Vehicle Registration: </span>
                            <span id="vehicleReg" style="font-weight: bold; color: #2c3e50; background: #fff3cd; padding: 3px 6px; border-radius: 3px;"></span>
                        </div>
                    </div>

                    <!-- Payment Method and Reference -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 18px;">
                        <div style="background: #e3f2fd; padding: 12px; border-radius: 6px; border-left: 3px solid #2196f3;">
                            <div style="font-weight: bold; color: #1976d2; margin-bottom: 4px; font-size: 12px;">Payment Method</div>
                            <div id="paymentMethod" style="font-size: 14px; font-weight: bold; color: #2c3e50;"></div>
                        </div>
                        <div style="background: #f3e5f5; padding: 12px; border-radius: 6px; border-left: 3px solid #9c27b0;">
                            <div style="font-weight: bold; color: #7b1fa2; margin-bottom: 4px; font-size: 12px;">Reference Number</div>
                            <div id="referenceNumber" style="font-size: 12px; font-weight: bold; color: #2c3e50; font-family: 'Courier New', monospace;"></div>
                        </div>
                    </div>

                    <!-- Balance Information -->
                    <div style="background: #e8f5e8; border: 2px solid #28a745; padding: 15px; border-radius: 6px; margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0; color: #155724; font-weight: bold; font-size: 14px;">Outstanding Balance</h4>
                                <p style="margin: 3px 0 0 0; color: #155724; font-size: 11px;">Remaining amount after this payment</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 20px; font-weight: bold; color: #155724;">
                                    KSh <span> {{ number_format($actualOutstanding, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thank You Section -->
                    <div style="text-align: center; margin-bottom: 18px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px;">
                        <h3 style="margin: 0 0 6px 0; color: #155724; font-weight: bold; font-size: 16px;">WITH THANKS</h3>
                        <p style="margin: 0; color: #155724; font-size: 12px;">We appreciate your business and prompt payment</p>
                    </div>

                    <!-- Terms Section -->
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
                        <div style="font-weight: bold; color: #856404; font-size: 12px;">
                            "Money once received is not refundable but transferable"
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
                        <div style="text-align: center;">
                            <div style="height: 50px; border-bottom: 2px solid #495057; margin-bottom: 8px;"></div>
                            <div style="font-size: 12px; font-weight: bold; color: #495057;">Customer Signature</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="height: 50px; border-bottom: 2px solid #495057; margin-bottom: 8px;"></div>
                            <div style="font-size: 12px; font-weight: bold; color: #495057;">For Kelmer's House of Cars</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="text-align: center; padding: 12px; background: #f8f9fa; border-radius: 6px; border-top: 2px solid #007bff;">
                        <div style="font-size: 11px; color: #6c757d; margin-bottom: 3px;" id="generatedDateTime"></div>
                        <div style="font-size: 10px; color: #6c757d; font-style: italic;">
                            Official Receipt from Kelmer's House of Cars Limited
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-success" onclick="downloadReceipt()">
                    <i class="fas fa-download me-2"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Deposit Payment -->
                                <tr class="table-success">
                                    <td>{{ \Carbon\Carbon::parse($agreement->agreement_date)->format('M d, Y') }}</td>
                                    <td><strong>KSh {{ number_format($agreement->deposit_amount, 2) }}</strong></td>
                                    <td><span class="badge bg-success">Initial Deposit</span></td>
                                    <td>-</td>
                                    <td><span class="badge bg-success">Cleared</span></td>
                                    <td>
                                    <button class="btn btn-outline-primary btn-sm" 
                                    onclick="openReceiptModal('deposit', {{ $agreement->deposit_amount }}, 'INITIAL DEPOSIT', '{{ $agreement->vehicle_registration }}', '{{ $agreement->client_name }}', 'Initial Deposit', 'Cleared', {{ $agreement->loan_amount }}, '-', '{{ $agreement->id }}', '{{ $agreement->agreement_date }}')"
                                    data-bs-toggle="tooltip" 
                                    title="Print Receipt">
                                <i class="fas fa-print me-1"></i>
                            </button>
                                    </td>
                                </tr>
                                
                                @forelse($agreement->payments as $payment)
                                    <tr>
                                        <td>{{ isset($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : \Carbon\Carbon::parse($payment->created_at)->format('M d, Y') }}</td>
                                        <td><strong>KSh {{ number_format($payment->amount, 2) }}</strong></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Not Specified')) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->reference_number ?? $payment->payment_reference ?? '-' }}</td>
                                        <td>
                                            @if(isset($payment->is_verified) && $payment->is_verified)
                                                <span class="badge bg-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                 <button class="btn btn-outline-primary" 
                                                        onclick="openReceiptModal('payment', {{ $payment->amount }}, 'MONTHLY PAYMENT', '{{ $agreement->vehicle_registration }}', '{{ $agreement->client_name }}', '{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Not Specified')) }}', '{{ isset($payment->is_verified) && $payment->is_verified ? 'Verified' : 'Pending' }}', {{ $payment->balance_after ?? 0 }}, '{{ $payment->reference_number ?? $payment->payment_reference ?? '-' }}', '{{ $agreement->id }}', '{{ isset($payment->payment_date) ? $payment->payment_date : $payment->created_at }}')"
                                                        data-bs-toggle="tooltip" 
                                                        title="Print Receipt">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                @if(!isset($payment->is_verified) || !$payment->is_verified)
                                                 @if(in_array(Auth::user()->role, ['Accountant','Managing-Director']))
                                                    <button class="btn btn-outline-success" onclick="verifyPayment({{ $payment->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No payments recorded yet</h6>
                                            <p class="text-muted">Payment history will appear here once payments are made.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Schedule Tab -->
                <div class="tab-pane fade" id="payment-schedule" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Payment Schedule</h5>
                        <div>
                            <span class="badge bg-info me-2">Monthly: KSh {{ number_format($agreement->monthly_payment, 0) }}</span>
                            <span class="badge bg-secondary">{{ $agreement->duration_months }} Months</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Total Payment</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Amount Paid</th>
                                    <th>Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($agreement->paymentSchedule && $agreement->paymentSchedule->count() > 0)
                                @php
                                    $totalPrincipal = 0;
                                    $totalInterest = 0;
                                    $totalPaid = 0;
                                    $totalPending = 0;
                                @endphp
                                @foreach($agreement->paymentSchedule->sortBy('due_date') as $schedule)
                                        @php
                                            $isOverdue = $schedule->status === 'overdue' || ($schedule->days_overdue > 0);
                                            $isPaid = $schedule->status === 'paid' || ($schedule->amount_paid > 0);
                                            $isPartial = $schedule->status === 'partial';
                                            
                                            // Accumulate totals
                                            $totalPrincipal += $schedule->principal_amount;
                                            $totalInterest += $schedule->interest_amount;
                                            
                                            if ($isPaid) {
                                                $totalPaid += $schedule->total_amount;
                                            } elseif ($isPartial) {
                                                $totalPaid += $schedule->amount_paid;
                                                $totalPending += ($schedule->total_amount - $schedule->amount_paid);
                                            } else {
                                                $totalPending += $schedule->total_amount;
                                            }
                                        @endphp
                                        <tr class="{{ $isOverdue ? 'table-danger' : ($isPaid ? 'table-success' : ($isPartial ? 'table-warning' : '')) }}">
                                            <td>{{ $schedule->installment_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->due_date)->format('M d, Y') }}</td>
                                            <td>KSh {{ number_format($schedule->principal_amount, 2) }}</td>
                                            <td>KSh {{ number_format($schedule->interest_amount, 2) }}</td>
                                            <td><strong>KSh {{ number_format($schedule->total_amount, 2) }}</strong></td>
                                            <td>KSh {{ number_format($schedule->balance_after, 2) }}</td>
                                            <td>
                                                @switch($schedule->status)
                                                    @case('paid')
                                                        <span class="badge bg-success">Paid</span>
                                                        @break
                                                    @case('overdue')
                                                        <span class="badge bg-danger">Overdue</span>
                                                        @break
                                                    @case('partial')
                                                        <span class="badge bg-warning">Partial</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">Pending</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($schedule->amount_paid > 0)
                                                    KSh {{ number_format($schedule->amount_paid, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($schedule->days_overdue > 0)
                                                    <span class="badge bg-danger">{{ $schedule->days_overdue }} days</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach 
                                    
                                    <!-- Summary Totals Row -->
                                    <tr class="table-active fw-bold">
                                        <td colspan="2" class="text-end">TOTALS:</td>
                                        <td>KSh {{ number_format($totalPrincipal, 2) }}</td>
                                        <td>KSh {{ number_format($totalInterest, 2) }}</td>
                                        <td>KSh {{ number_format($totalPrincipal + $totalInterest, 2) }}</td>
                                        <td>-</td>
                                        <td colspan="2">
                                            <span class="text-success">Paid: KSh {{ number_format($totalPaid, 2) }}</span><br>
                                            <span class="text-danger">Pending: KSh {{ number_format($totalPending, 2) }}</span>
                                        </td>
                                        <td>-</td>
                                    </tr>

                                @else
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No payment schedule available</h6>
                                            <p class="text-muted">Payment schedule will be generated automatically.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

<!-- Agreement Document Tab -->
<div class="tab-pane fade" id="agreement-document" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Hire Purchase Agreement</h5>
        <div>
             <button type="button" class="btn btn-primary btn-sm agreementBtn" 
                                                            data-cash-id="{{ $agreement->id }}"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#agreementModal{{ $agreement->id }}">
                                                        <i class="fas fa-file-contract me-1"></i> Agreement
                                                    </button>
           <!-- Professional Agreement Modal -->
<div class="modal fade" id="agreementModal{{ $agreement->id }}" tabindex="-1" aria-labelledby="agreementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="agreementModalLabel">
                    <i class="fas fa-file-contract me-2"></i>Hire Purchase Sales Agreement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Upload Section -->
                <div id="uploadSection{{ $agreement->id }}" class="mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Agreement PDF</h6>
                        </div>
                        <div class="card-body">
                            <form id="agreementUploadForm{{ $agreement->id }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                                <input type="hidden" name="agreement_type" value="HirePurchase">
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <label for="agreement_file{{ $agreement->id }}" class="form-label">
                                            <i class="fas fa-file-pdf me-1"></i>Select PDF File
                                        </label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="agreement_file{{ $agreement->id }}" 
                                               name="agreement_file" 
                                               accept=".pdf" 
                                               required>
                                        <div class="form-text">Maximum file size: 1GB. Only PDF files are allowed.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" 
                                                class="btn btn-primary w-100" 
                                                id="uploadBtn{{ $agreement->id }}">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Progress Bar -->
                            <div class="progress mt-3 d-none" id="uploadProgress{{ $agreement->id }}">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 0%"></div>
                            </div>
                            
                            <!-- Upload Status -->
                            <div id="uploadStatus{{ $agreement->id }}" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Agreement Management Section (When PDF exists) -->
                <div id="agreementManagement{{ $agreement->id }}" class="mb-4" style="display: none;">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-file-check me-2"></i>Agreement Uploaded</h6>
                            <button type="button" 
                                    class="btn btn-outline-light btn-sm" 
                                    id="deleteAgreementBtn{{ $agreement->id }}"
                                    title="Delete Agreement">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p class="mb-0"><strong>Actions:</strong></p>
                                </div>
                                <div class="col-md-4">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openPDFNewTab{{ $agreement->id }}()">
                                            <i class="fas fa-external-link-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="downloadPDF{{ $agreement->id }}()">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="printPDF{{ $agreement->id }}()">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Display Section -->
                <div id="agreementContent{{ $agreement->id }}" style="min-height: 600px;">
                    <div class="text-center py-5" id="emptyState{{ $agreement->id }}">
                        <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No agreement uploaded yet. Please upload a PDF file above.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" id="replaceBtn{{ $agreement->id }}" style="display: none;" onclick="showUploadSection{{ $agreement->id }}()">
                    <i class="fas fa-sync-alt me-1"></i>Replace PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal{{ $agreement->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this agreement? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn{{ $agreement->id }}">
                    <i class="fas fa-trash-alt me-1"></i>Delete Agreement
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.pdf-viewer-container {
    width: 100%;
    height: 600px;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.pdf-embed {
    width: 100%;
    height: 100%;
    border: none;
    border-radius: 0.375rem;
}

.pdf-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
}

.pdf-error-state {
    height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.upload-dropzone {
    border: 2px dashed #0d6efd;
    border-radius: 0.375rem;
    padding: 2rem;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.upload-dropzone:hover {
    background: #e7f3ff;
    border-color: #0b5ed7;
}

.upload-dropzone.dragover {
    background: #cfe2ff;
    border-color: #0a58ca;
}
</style>

<script>
$(document).ready(function() {
    const agreementId = {{ $agreement->id }};
    let currentPdfUrl = null;
    
    // Check if agreement already exists when modal opens
    $('#agreementModal' + agreementId).on('shown.bs.modal', function() {
        checkExistingAgreement(agreementId);
    });
    
    // File upload form submission
    $('#agreementUploadForm' + agreementId).on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = $('#uploadBtn' + agreementId);
        const uploadProgress = $('#uploadProgress' + agreementId);
        const uploadStatus = $('#uploadStatus' + agreementId);
        
        // Reset status
        uploadStatus.empty();
        uploadProgress.removeClass('d-none');
        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Uploading...');
        
        $.ajax({
            url: '{{ route("agreement.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5 minutes timeout for large files
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        uploadProgress.find('.progress-bar').css('width', percentComplete + '%');
                        
                        // Show file size progress for large files
                        const loaded = (evt.loaded / (1024 * 1024 * 1024)).toFixed(2);
                        const total = (evt.total / (1024 * 1024 * 1024)).toFixed(2);
                        uploadProgress.find('.progress-bar').text(`${loaded}GB / ${total}GB (${percentComplete}%)`);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                uploadStatus.html(`
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Agreement uploaded successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                
                // Store PDF URL
                currentPdfUrl = response.pdfUrl;
                
                // Display the uploaded PDF
                displayPDF(currentPdfUrl, agreementId);
                
                // Show management section and hide upload section
                showAgreementManagement(agreementId);
                
                // Reset form
                $('#agreementUploadForm' + agreementId)[0].reset();
            },
            error: function(xhr) {
                let errorMessage = 'Upload failed. Please try again.';
                
                if (xhr.status === 413) {
                    errorMessage = 'File is too large. Maximum allowed size is 1GB.';
                } else if (xhr.status === 408 || xhr.statusText === 'timeout') {
                    errorMessage = 'Upload timed out. Please try again with a smaller file or check your internet connection.';
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                uploadStatus.html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            },
            complete: function() {
                uploadProgress.addClass('d-none');
                uploadBtn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Upload');
            }
        });
    });
    
    // Delete agreement functionality
    $('#deleteAgreementBtn' + agreementId).on('click', function() {
        $('#deleteConfirmModal' + agreementId).modal('show');
    });
    
    $('#confirmDeleteBtn' + agreementId).on('click', function() {
        deleteAgreement(agreementId);
    });
    
    // File input validation
    $('#agreement_file' + agreementId).on('change', function() {
        const file = this.files[0];
        const uploadStatus = $('#uploadStatus' + agreementId);
        
        if (file) {
            if (file.type !== 'application/pdf') {
                uploadStatus.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Please select a PDF file only.
                    </div>
                `);
                this.value = '';
                return;
            }
            
            // Check for 1GB limit (1073741824 bytes)
            if (file.size > 1073741824) {
                const fileSize = (file.size / (1024 * 1024 * 1024)).toFixed(2);
                uploadStatus.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>File size (${fileSize}GB) exceeds the maximum limit of 1GB.
                    </div>
                `);
                this.value = '';
                return;
            }
            
            // Show file size info for large files
            const fileSize = file.size / (1024 * 1024);
            if (fileSize > 100) { // Show size info for files larger than 100MB
                const sizeText = fileSize > 1024 ? 
                    `${(fileSize / 1024).toFixed(2)}GB` : 
                    `${fileSize.toFixed(2)}MB`;
                
                uploadStatus.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Large file selected (${sizeText}). Upload may take several minutes.
                    </div>
                `);
            } else {
                uploadStatus.empty();
            }
        }
    });
});

// Check if agreement already exists
function checkExistingAgreement(agreementId) {
    const agreementType = 'HirePurchase';
    
    $.ajax({
        url: '/agreements/' + agreementId + '/' + agreementType,
        type: 'HEAD', // Use HEAD request to check if file exists without downloading
        success: function(data, status, xhr) {
            // If successful, the agreement exists
            const pdfUrl = '/agreements/' + agreementId + '/' + agreementType;
            currentPdfUrl = pdfUrl;
            displayPDF(pdfUrl, agreementId);
            showAgreementManagement(agreementId);
        },
        error: function(xhr) {
            // If 404 or any error, assume no agreement exists
            showUploadSection(agreementId);
        }
    });
}
// Display PDF with multiple fallback methods
function displayPDF(pdfUrl, agreementId) {
    $('#emptyState' + agreementId).hide();
    
    const content = `
        <div class="text-center mb-3">
            <h6 class="text-primary">
                <i class="fas fa-file-pdf me-2"></i>Agreement Document
            </h6>
        </div>
        <div class="pdf-viewer-container" id="pdfContainer${agreementId}">
            <div class="pdf-loading-overlay" id="pdfLoading${agreementId}">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading PDF...</p>
                    <p class="small text-muted">Large files may take longer to load</p>
                </div>
            </div>
        </div>
    `;
    
    $('#agreementContent' + agreementId).html(content);
    
    // Try different methods to display PDF
    tryDisplayMethods(pdfUrl, agreementId);
}

function tryDisplayMethods(pdfUrl, agreementId) {
    const container = $('#pdfContainer' + agreementId);
    const loading = $('#pdfLoading' + agreementId);
    
    // Method 1: Try direct embed
    const embed = `<embed src="${pdfUrl}#view=FitH" type="application/pdf" class="pdf-embed" id="pdfEmbed${agreementId}">`;
    container.append(embed);
    
    // Check if embed loaded after 5 seconds (longer for large files)
    setTimeout(() => {
        const embedElement = $('#pdfEmbed' + agreementId)[0];
        
        if (!embedElement || embedElement.clientHeight === 0) {
            // Method 2: Try Google Docs Viewer
            container.html(`
                <iframe src="https://docs.google.com/viewer?url=${encodeURIComponent(pdfUrl)}&embedded=true" 
                        class="pdf-embed" 
                        id="pdfIframe${agreementId}">
                </iframe>
            `);
            
            // Check if Google Docs Viewer loaded
            setTimeout(() => {
                const iframe = $('#pdfIframe' + agreementId)[0];
                if (!iframe || iframe.clientHeight === 0) {
                    // Method 3: Fallback with manual controls
                    showPDFError(pdfUrl, agreementId);
                } else {
                    loading.hide();
                }
            }, 5000);
        } else {
            loading.hide();
        }
    }, 5000);
}

function showPDFError(pdfUrl, agreementId) {
    const container = $('#pdfContainer' + agreementId);
    container.html(`
        <div class="pdf-error-state">
            <div class="text-center">
                <i class="fas fa-file-pdf fa-4x text-muted mb-3"></i>
                <h5>PDF Preview Not Available</h5>
                <p class="text-muted mb-2">Your browser doesn't support embedded PDF viewing.</p>
                <p class="text-muted mb-4 small">Large PDF files may not display properly in the browser.</p>
                <div class="btn-group" role="group">
                    <a href="${pdfUrl}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Open in New Tab
                    </a>
                    <a href="${pdfUrl}" download class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
    `);
}

// Show/hide sections
function showAgreementManagement(agreementId) {
    $('#uploadSection' + agreementId).slideUp();
    $('#agreementManagement' + agreementId).slideDown();
    $('#replaceBtn' + agreementId).show();
}

function showUploadSection(agreementId) {
    $('#agreementManagement' + agreementId).slideUp();
    $('#uploadSection' + agreementId).slideDown();
    $('#replaceBtn' + agreementId).hide();
    $('#agreementContent' + agreementId).html(`
        <div class="text-center py-5" id="emptyState${agreementId}">
            <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
            <p class="text-muted">No agreement uploaded yet. Please upload a PDF file above.</p>
        </div>
    `);
}

// Delete agreement
function deleteAgreement(agreementId) {
    const deleteBtn = $('#confirmDeleteBtn' + agreementId);
    deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Deleting...');
    
    $.ajax({
        url: '{{ url("/agreements") }}/' + agreementId,
        type: 'DELETE',
        data: {
            '_token': '{{ csrf_token() }}'
        },
        success: function(response) {
            $('#deleteConfirmModal' + agreementId).modal('hide');
            showUploadSection(agreementId);
            
            // Show success message
            $('#uploadStatus' + agreementId).html(`
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Agreement deleted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        },
        error: function(xhr) {
            $('#deleteConfirmModal' + agreementId).modal('hide');
            
            $('#uploadStatus' + agreementId).html(`
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Failed to delete agreement. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        },
        complete: function() {
            deleteBtn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i>Delete Agreement');
        }
    });
}

// PDF action functions
window['openPDFNewTab' + {{ $agreement->id }}] = function() {
    if (currentPdfUrl) {
        window.open(currentPdfUrl, '_blank');
    }
};

window['downloadPDF' + {{ $agreement->id }}] = function() {
    if (currentPdfUrl) {
        const link = document.createElement('a');
        link.href = currentPdfUrl;
        link.download = 'agreement-{{ $agreement->id }}.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

window['printPDF' + {{ $agreement->id }}] = function() {
    if (currentPdfUrl) {
        const printWindow = window.open(currentPdfUrl, '_blank');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        });
    }
};

window['showUploadSection' + {{ $agreement->id }}] = function() {
    showUploadSection({{ $agreement->id }});
};
</script>
        </div>
    </div>
</div>


                <!-- Vehicle Details Tab -->
                <div class="tab-pane fade" id="vehicle-details" role="tabpanel">
                    <h5 class="mb-4">Vehicle Information</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Basic Details</h6>
                                </div>
                                <div class="card-body">
                                    @if($agreement->customerVehicle)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Make:</strong></td>
                                                <td>{{ $agreement->customerVehicle->vehicle_make ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Model:</strong></td>
                                                <td>{{ $agreement->customerVehicle->model ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Year:</strong></td>
                                                <td>{{ $agreement->customerVehicle->year ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Plate Number:</strong></td>
                                                <td>{{ $agreement->customerVehicle->number_plate ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Chassis Number:</strong></td>
                                                <td>{{ $agreement->customerVehicle->chasis_no ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Vehicle Type:</strong></td>
                                                <td>
                                                    <span class="badge bg-success">Customer Vehicle</span>
                                                </td>
                                            </tr>
                                        </table>
                                    @elseif($agreement->carImport)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Make:</strong></td>
                                                <td>{{ $agreement->carImport->make ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Model:</strong></td>
                                                <td>{{ $agreement->carImport->model ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Year:</strong></td>
                                                <td>{{ $agreement->carImport->year ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Plate Number:</strong></td>
                                                <td>{{ $agreement->carImport->plate_number ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Chassis Number:</strong></td>
                                                <td>{{ $agreement->carImport->chassis_number ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Vehicle Type:</strong></td>
                                                <td>
                                                    <span class="badge bg-primary">Imported Vehicle</span>
                                                </td>
                                            </tr>
                                        </table>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Vehicle details not available.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Financial Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Purchase Price:</strong></td>
                                            <td>KSh {{ number_format($agreement->vehicle_price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Down Payment:</strong></td>
                                            <td>KSh {{ number_format($agreement->deposit_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Loan Amount:</strong></td>
                                            <td>KSh {{ number_format($agreement->loan_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Interest Rate:</strong></td>
                                            <td>{{ $agreement->interest_rate }}% per month</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Monthly Payment:</strong></td>
                                            <td>KSh {{ number_format($agreement->monthly_payment, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Interest:</strong></td>
                                            <td>KSh {{ number_format($agreement->total_interest, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Amount:</strong></td>
                                            <td>KSh {{ number_format($agreement->total_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Amount Paid:</strong></td>
                                            <td>KSh {{ number_format($totalAmountPaid, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Outstanding Balance:</strong></td>
                                            <td>KSh {{ number_format($actualOutstanding, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payments Remaining:</strong></td>
                                            <td>{{ $agreement->payments_remaining }} months</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Extended Vehicle Details -->
                    @if($agreement->customerVehicle)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Customer Vehicle Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Customer Name:</strong> {{ $agreement->customerVehicle->customer_name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Phone Number:</strong> {{ $agreement->customerVehicle->phone_no ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Email:</strong> {{ $agreement->customerVehicle->email ?? 'N/A' }}
                                    </div>
                                </div>
                                
                                @if(isset($agreement->customerVehicle->photos) && !empty($agreement->customerVehicle->photos))
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <strong>Vehicle Photos:</strong>
                                            <div class="d-flex gap-2 mt-2">
                                                @php
                                                    $photos = is_string($agreement->customerVehicle->photos) ? 
                                                             json_decode($agreement->customerVehicle->photos, true) : 
                                                             $agreement->customerVehicle->photos;
                                                @endphp
                                                @if(is_array($photos))
                                                    @foreach($photos as $photo)
                                                        <img src="https://houseofcars.s3.eu-central-1.amazonaws.com/{{$photo}}" alt="Vehicle Photo" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($agreement->carImport)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Import Vehicle Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Engine:</strong> {{ $agreement->carImport->engine ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Transmission:</strong> {{ $agreement->carImport->transmission ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Fuel Type:</strong> {{ $agreement->carImport->fuel_type ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Mileage:</strong> {{ $agreement->carImport->mileage ?? 'N/A' }}
                                    </div>
                                </div>
                                
                                @if(isset($agreement->carImport->photos) && !empty($agreement->carImport->photos))
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <strong>Vehicle Photos:</strong>
                                            <div class="row mt-2">
                                                @php
                                                    $photos = is_string($agreement->carImport->photos) ? 
                                                             json_decode($agreement->carImport->photos, true) : 
                                                             $agreement->carImport->photos;
                                                @endphp
                                                @if(is_array($photos))
                                                    @foreach($photos as $photo)
                                                        <div class="col-md-4 mb-3">
                                                            <div class="card">
                                                                <img src="https://houseofcars.s3.eu-central-1.amazonaws.com/{{$photo}}" 
                                                                     alt="Vehicle Photo" 
                                                                     class="card-img-top img-fluid" 
                                                                     style="object-fit: cover; height: 200px; width: 100%;">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Legal & Compliance Tab -->
                <div class="tab-pane fade" id="legal-compliance" role="tabpanel">
                    <h5 class="mb-4">Legal & Compliance Information</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Client Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Full Name:</strong></td>
                                            <td>{{ $agreement->client_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $agreement->phone_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $agreement->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>National ID:</strong></td>
                                            <td>{{ $agreement->national_id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>KRA PIN:</strong></td>
                                            <td>{{ $agreement->kra_pin ?? 'N/A' }}</td>
                                        </tr>
                                        @if($agreement->address)
                                            <tr>
                                                <td><strong>Address:</strong></td>
                                                <td>{{ $agreement->address }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Agreement Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Agreement ID:</strong></td>
                                            <td>{{ $agreement->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Agreement Date:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($agreement->agreement_date)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>First Due Date:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($agreement->first_due_date)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Expected Completion:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($agreement->expected_completion_date)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $currentStatus['class'] }}">
                                                    {{ $currentStatus['text'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Is Overdue:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $agreement->is_overdue ? 'danger' : 'success' }}">
                                                    {{ $agreement->is_overdue ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($agreement->overdue_days > 0)
                                            <tr>
                                                <td><strong>Overdue Days:</strong></td>
                                                <td>
                                                    <span class="badge bg-danger">{{ $agreement->overdue_days }} days</span>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-4">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-primary" onclick="printAgreement()">
                                <i class="fas fa-print"></i> Print Agreement
                            </button>
                            <button class="btn btn-outline-info" onclick="downloadPDF()">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </button>
                            <button class="btn btn-outline-secondary" onclick="sendCopy()">
                                <i class="fas fa-envelope"></i> Email Copy
                            </button>
                            @if($agreement->status === 'pending')
                                <button class="btn btn-success" onclick="approveAgreement({{ $agreement->id }})">
                                    <i class="fas fa-check"></i> Approve Agreement
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="rescheduling-history" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Loan Rescheduling History</h5>
        @if($agreement->status !== 'completed' && ($agreement->rescheduling_count ?? 0) < 3)
           \
        @endif
    </div>
    
    @if(isset($agreement->reschedulingHistory) && $agreement->reschedulingHistory->count() > 0)
        <div class="timeline">
            @foreach($agreement->reschedulingHistory as $rescheduling)
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <i class="fas fa-{{ $rescheduling->reschedule_type === 'reduce_duration' ? 'clock' : 'money-bill-wave' }}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="card border-{{ $rescheduling->reschedule_type === 'reduce_duration' ? 'primary' : 'success' }}">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0">
                                        @if($rescheduling->reschedule_type === 'reduce_duration')
                                            <i class="fas fa-clock text-primary"></i> Duration Reduced
                                        @else
                                            <i class="fas fa-money-bill-wave text-success"></i> Payment Reduced
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $rescheduling->rescheduling_date->format('M d, Y') }}</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Lump Sum Payment:</strong> KSh {{ number_format($rescheduling->lump_sum_amount, 2) }}</p>
                                        <p class="mb-1"><strong>Outstanding Before:</strong> KSh {{ number_format($rescheduling->outstanding_before, 2) }}</p>
                                        <p class="mb-1"><strong>Outstanding After:</strong> KSh {{ number_format($rescheduling->outstanding_after, 2) }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        @if($rescheduling->reschedule_type === 'reduce_duration')
                                            <p class="mb-1"><strong>Duration Reduced:</strong> {{ $rescheduling->duration_change_months }} months</p>
                                            <p class="mb-1"><strong>New Duration:</strong> {{ $rescheduling->new_duration_months }} months</p>
                                        @else
                                            <p class="mb-1"><strong>Payment Reduced:</strong> KSh {{ number_format($rescheduling->payment_change_amount, 2) }}</p>
                                            <p class="mb-1"><strong>New Monthly Payment:</strong> KSh {{ number_format($rescheduling->new_monthly_payment, 2) }}</p>
                                        @endif
                                        @if($rescheduling->total_interest_savings > 0)
                                            <p class="mb-1"><strong>Interest Saved:</strong> KSh {{ number_format($rescheduling->total_interest_savings, 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($rescheduling->notes)
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted">{{ $rescheduling->notes }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-sync-alt fa-3x text-muted mb-3"></i>
            <h6 class="text-muted">No Rescheduling History</h6>
            <p class="text-muted">This loan has not been rescheduled yet. Make a lump sum payment to access rescheduling options.</p>
            @if($agreement->status !== 'completed')
               
            @endif
        </div>
    @endif
</div>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    @csrf
                    <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                    
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <span><strong>Suggested Payment:</strong></span>
                            <span><strong>KSh {{ number_format($agreement->monthly_payment, 2) }}</strong></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Outstanding Balance:</span>
                            <span><strong>KSh {{ number_format($actualOutstanding, 2) }}</strong></span>
                        </div>
                        @if($actualOutstanding != $agreement->outstanding_balance)
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="fas fa-info-circle"></i> 
                                    Amount calculated from payment schedule
                                </small>
                            </div>
                        @endif
                        @if($nextDueInstallment)
                            <div class="mt-2 pt-2 border-top">
                                <small><strong>Next Due:</strong> {{ \Carbon\Carbon::parse($nextDueInstallment->due_date)->format('M d, Y') }} 
                                - KSh {{ number_format($nextDueInstallment->total_amount, 2) }}</small>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Amount (KSh) *</label>
                        <input type="number" 
                               class="form-control" 
                               name="payment_amount" 
                               value="{{ $agreement->monthly_payment }}" 
                               required 
                               min="1" 
                               max="{{ $actualOutstanding }}"
                               step="0.01">
                        <small class="text-muted">Maximum: KSh {{ number_format($actualOutstanding, 2) }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method *</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card Payment</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" class="form-control" name="payment_reference" placeholder="Transaction/Receipt Number">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="payment_notes" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save"></i> Record Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div> 

<script>
    function downloadReceiptDirect() {
    try {
        const receiptContent = document.getElementById('receiptContent');
        
        if (!receiptContent) {
            alert('Receipt content not found!');
            return;
        }

        // Get customer name for filename
        const customerNameElement = document.getElementById('customerName');
        const customerName = customerNameElement ? customerNameElement.textContent.trim() : 'Customer';
        const fileName = customerName.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') || 'Receipt';

        // Create HTML content with existing receipt
        const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <title>${fileName} Receipt</title>
    <meta charset="UTF-8">
    <style>
        body { 
            margin: 20px; 
            font-family: Arial, sans-serif; 
            background: white;
        }
        @media print {
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    ${receiptContent.innerHTML}
</body>
</html>`;

        // Create and download as HTML file
        const blob = new Blob([htmlContent], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        
        const downloadLink = document.createElement('a');
        downloadLink.href = url;
        downloadLink.download = `${fileName}_Receipt.html`;
        downloadLink.style.display = 'none';
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        URL.revokeObjectURL(url);
        
    } catch (error) {
        console.error('Error downloading receipt:', error);
        alert('Error downloading receipt. Please try again.');
    }
}
// Function to convert numbers to words
function numberToWords(num) {
    if (num === 0) return "zero";
    
    const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
    const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    const teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    
    function convertHundreds(n) {
        let result = '';
        
        if (n >= 100) {
            result += ones[Math.floor(n / 100)] + ' hundred';
            n %= 100;
            if (n > 0) result += ' and ';
        }
        
        if (n >= 20) {
            result += tens[Math.floor(n / 10)];
            n %= 10;
            if (n > 0) result += '-' + ones[n];
        } else if (n >= 10) {
            result += teens[n - 10];
        } else if (n > 0) {
            result += ones[n];
        }
        
        return result;
    }
    
    if (num < 0) {
        return 'negative ' + numberToWords(-num);
    }
    
    if (num < 1000) {
        return convertHundreds(num);
    }
    
    if (num < 1000000) {
        const thousands = Math.floor(num / 1000);
        const remainder = num % 1000;
        let result = convertHundreds(thousands) + ' thousand';
        if (remainder > 0) {
            result += ' ' + convertHundreds(remainder);
        }
        return result;
    }
    
    if (num < 1000000000) {
        const millions = Math.floor(num / 1000000);
        const remainder = num % 1000000;
        let result = convertHundreds(millions) + ' million';
        if (remainder > 0) {
            result += ' ' + numberToWords(remainder);
        }
        return result;
    }
    
    if (num < 1000000000000) {
        const billions = Math.floor(num / 1000000000);
        const remainder = num % 1000000000;
        let result = convertHundreds(billions) + ' billion';
        if (remainder > 0) {
            result += ' ' + numberToWords(remainder);
        }
        return result;
    }
    
    return 'number too large';
}

// Function to convert currency amount to words
function amountToWords(amount) {
    // Handle decimal amounts (cents)
    const parts = amount.toString().split('.');
    const wholePart = parseInt(parts[0]) || 0;
    const decimalPart = parts[1] ? parseInt(parts[1].padEnd(2, '0').slice(0, 2)) : 0;
    
    let result = '';
    
    if (wholePart > 0) {
        result += numberToWords(wholePart);
        result += wholePart === 1 ? ' shilling' : ' shillings';
    }
    
    if (decimalPart > 0) {
        if (wholePart > 0) result += ' and ';
        result += numberToWords(decimalPart);
        result += decimalPart === 1 ? ' cent' : ' cents';
    }
    
    if (wholePart === 0 && decimalPart === 0) {
        result = 'zero shillings';
    }
    
    return result + ' only';
}

function openReceiptModal(type, amount, description, vehicleReg, customerName, paymentMethod, status, reference, agreementId, paymentDate) {
    try {
        console.log('Opening receipt modal with data:', {type, amount, description, vehicleReg, customerName});
        
        // Update modal content with dynamic data
        document.getElementById('paymentAmount').textContent = new Intl.NumberFormat().format(amount);
        
        // Convert amount to words and update the words field
        const amountInWords = amountToWords(amount);
        document.getElementById('paymentAmountWords').textContent = amountInWords;
        
        document.getElementById('paymentDescription').textContent = description;
        document.getElementById('vehicleReg').textContent = vehicleReg;
        document.getElementById('customerName').textContent = customerName;
        document.getElementById('paymentMethod').textContent = paymentMethod;
        document.getElementById('referenceNumber').textContent = reference;
        
        // Set date and time - use payment date if provided, otherwise current date
        const receiptDate = paymentDate ? new Date(paymentDate) : new Date();
        document.getElementById('receiptDate').textContent = receiptDate.toLocaleDateString('en-GB');
        
        // Update generated date time
        const currentDateTime = new Date();
        document.getElementById('generatedDateTime').textContent = 
            `Generated on ${currentDateTime.toLocaleDateString('en-GB')} at ${currentDateTime.toLocaleTimeString('en-GB')} | Thank you for your business!`;
        
        // Generate dynamic receipt number based on agreement ID and timestamp
        const receiptNumber = `${Math.floor(Math.random() * 900) + 100}`;
        document.getElementById('receiptNumber').textContent = receiptNumber;
        
        // Show modal using jQuery if Bootstrap 4, or Bootstrap 5 method
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            $('#receiptModal').modal('show');
        } else if (typeof bootstrap !== 'undefined') {
            var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
            receiptModal.show();
        } else {
            // Fallback - show modal manually
            document.getElementById('receiptModal').style.display = 'block';
            document.getElementById('receiptModal').classList.add('show');
        }
        
    } catch (error) {
        console.error('Error opening receipt modal:', error);
        alert('Error opening receipt. Please try again.');
    }
}

function downloadReceipt() {
    // Get the customer name from the receipt
    var customerNameElement = document.getElementById('customerName');
    var customerName = customerNameElement ? customerNameElement.textContent.trim() : 'Receipt';
    
    // Clean the customer name for filename (remove special characters)
    var fileName = customerName.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') || 'Receipt';
    
    // Try multiple selectors to find the receipt content
    var receiptContainer = document.querySelector('.receipt-container') || 
                          document.querySelector('#receiptContent') ||
                          document.querySelector('.modal-body');
    
    if (!receiptContainer) {
        alert('Receipt content not found');
        return;
    }
    
    var printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${fileName}_Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                @page { size: A4; margin: 1cm; }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            ${receiptContainer.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
}

function verifyPayment(paymentId) {
    if (confirm('Are you sure you want to verify this payment?')) {
        // Add your verification logic here
        // You can make an AJAX call to verify the payment
        console.log('Verifying payment ID:', paymentId);
        
        // Example AJAX call (uncomment and modify as needed):
        /*
        fetch(`/payments/${paymentId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated status
            }
        });
        */
    }
}

// Initialize Bootstrap tooltips and modals when document loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing components...');
    
    // Initialize Bootstrap tooltips
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        console.log('Bootstrap 5 tooltips initialized');
    } else if (typeof jQuery !== 'undefined' && jQuery.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
        console.log('Bootstrap 4/jQuery tooltips initialized');
    }
    
    // Test modal availability
    const modal = document.getElementById('receiptModal');
    if (modal) {
        console.log('Receipt modal found in DOM');
    } else {
        console.error('Receipt modal not found in DOM');
    }
});
</script>
<script>



// Download Hire Purchase Agreement PDF
document.addEventListener('DOMContentLoaded', function() {
   const downloadBtn = document.getElementById('downloadHirePurchasePDF');
   if (downloadBtn) {
       downloadBtn.addEventListener('click', function() {
           console.log('Download Hire Purchase PDF clicked');
           
           const agreementElement = document.getElementById('hirePurchaseAgreementContent');
           if (!agreementElement) {
               alert('Agreement content not found!');
               return;
           }

           // Show loading state
           const originalText = this.innerHTML;
           this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating PDF...';
           this.disabled = true;

           // Configure html2canvas options for better quality
           const options = {
               scale: 2,
               useCORS: true,
               allowTaint: true,
               backgroundColor: '#ffffff',
               width: agreementElement.scrollWidth,
               height: agreementElement.scrollHeight,
               scrollX: 0,
               scrollY: 0
           };

           // Generate PDF
           html2canvas(agreementElement, options).then(canvas => {
               try {
                   const { jsPDF } = window.jspdf;
                   
                   // Create PDF in A4 format
                   const pdf = new jsPDF('p', 'mm', 'a4');
                   const imgData = canvas.toDataURL('image/png');
                   
                   // Calculate dimensions
                   const pdfWidth = 210; // A4 width in mm
                   const pdfHeight = 297; // A4 height in mm
                   const imgWidth = pdfWidth - 20; // Add margins
                   const imgHeight = (canvas.height * imgWidth) / canvas.width;
                   
                   let heightLeft = imgHeight;
                   let position = 10; // Top margin

                   // Add first page
                   pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                   heightLeft -= (pdfHeight - 20); // Subtract margins

                   // Add additional pages if needed
                   while (heightLeft >= 0) {
                       position = heightLeft - imgHeight + 10;
                       pdf.addPage();
                       pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                       heightLeft -= (pdfHeight - 20);
                   }

                   // Download the PDF
                   const clientName = '{{ $agreement->client_name }}'.replace(/\s+/g, '_');
                   const filename = `Hire_Purchase_Agreement_${clientName}_${new Date().toISOString().slice(0, 10)}.pdf`;
                   pdf.save(filename);

                   console.log('Hire Purchase PDF generated successfully:', filename);
                   
               } catch (error) {
                   console.error('Error generating PDF:', error);
                   alert('Error generating PDF. Please try again.');
               }
               
               // Restore button state
               this.innerHTML = originalText;
               this.disabled = false;
               
           }).catch(error => {
               console.error('Error capturing content:', error);
               alert('Error capturing content. Please try again.');
               
               // Restore button state
               this.innerHTML = originalText;
               this.disabled = false;
           });
       });
   }

   // Print Hire Purchase Agreement
   const printBtn = document.getElementById('printHirePurchaseAgreement');
   if (printBtn) {
       printBtn.addEventListener('click', function() {
           console.log('Print Hire Purchase Agreement clicked');
           
           const agreementContent = document.getElementById('hirePurchaseAgreementContent');
           if (!agreementContent) {
               alert('Agreement content not found!');
               return;
           }

           // Create a new window for printing
           const printWindow = window.open('', '_blank', 'width=800,height=600');
           
           // Get the agreement HTML
           const agreementHTML = agreementContent.innerHTML;
           
           // Create the print document
           printWindow.document.write(`
               <!DOCTYPE html>
               <html>
               <head>
                   <title>Hire Purchase Agreement</title>
                   <style>
                       @page {
                           margin: 15mm;
                           size: A4;
                       }
                       
                       body {
                           font-family: 'Times New Roman', serif;
                           line-height: 1.6;
                           color: #000;
                           background: white;
                           margin: 0;
                           padding: 0;
                           font-size: 12px;
                       }
                       
                       * {
                           box-sizing: border-box;
                           -webkit-print-color-adjust: exact !important;
                           color-adjust: exact !important;
                           print-color-adjust: exact !important;
                       }
                       
                       .agreement-document {
                           background: white !important;
                           color: #000 !important;
                       }
                       
                       h1, h2, h3 {
                           color: #000 !important;
                           page-break-after: avoid;
                       }
                       
                       table {
                           page-break-inside: avoid;
                           width: 100%;
                           border-collapse: collapse;
                       }
                       
                       tr {
                           page-break-inside: avoid;
                       }
                       
                       div[style*="border-bottom: 2px solid"],
                       div[style*="border-bottom: 3px solid"] {
                           border-bottom: 2px solid #000 !important;
                       }
                       
                       div[style*="page-break-before: always"] {
                           page-break-before: always;
                       }
                   </style>
               </head>
               <body>
                   ${agreementHTML}
               </body>
               </html>
           `);
           
           printWindow.document.close();
           
           // Wait for content to load, then print
           printWindow.onload = function() {
               setTimeout(() => {
                   printWindow.focus();
                   printWindow.print();
                   
                   // Close the print window after printing
                   setTimeout(() => {
                       printWindow.close();
                   }, 1000);
               }, 500);
           };
       });
   }
});

// Enhanced Agreement Document Tab Functions
function downloadAgreementPDF() {
   // Generate and download the hire purchase agreement
   generateHirePurchaseAgreement();
   
   setTimeout(() => {
       document.getElementById('downloadHirePurchasePDF').click();
   }, 500);
}

function printSection(sectionId) {
   if (sectionId === 'agreement-document-content') {
       // For hire purchase, open the modal and print
       openHirePurchaseModal();
       
       setTimeout(() => {
           document.getElementById('printHirePurchaseAgreement').click();
       }, 1000);
   } else {
       // Regular section printing
       const section = document.getElementById(sectionId);
       if (section) {
           const printWindow = window.open('', '_blank');
           printWindow.document.write(`
               <html>
                   <head>
                       <title>Print - ${sectionId}</title>
                       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                       <style>
                           @media print {
                               .no-print { display: none !important; }
                               body { font-size: 12px; }
                               .table { font-size: 11px; }
                           }
                       </style>
                   </head>
                   <body class="p-3">
                       ${section.innerHTML}
                   </body>
               </html>
           `);
           printWindow.document.close();
           printWindow.print();
       }
   }
}

// Number to words function (reuse from previous implementation)
function numberToWords(num) {
   if (num === 0) return 'zero';
   
   const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
   const teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
   const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
   
   function convertHundreds(n) {
       let result = '';
       if (n >= 100) {
           result += ones[Math.floor(n / 100)] + ' hundred ';
           n %= 100;
       }
       if (n >= 20) {
           result += tens[Math.floor(n / 10)] + ' ';
           n %= 10;
       }
       if (n >= 10) {
           result += teens[n - 10] + ' ';
       } else if (n > 0) {
           result += ones[n] + ' ';
       }
       return result.trim();
   }
   
   let result = '';
   let thousandIndex = 0;
   const thousands = ['', 'thousand', 'million', 'billion'];
   
   while (num > 0) {
       if (num % 1000 !== 0) {
           result = convertHundreds(num % 1000) + ' ' + thousands[thousandIndex] + ' ' + result;
       }
       num = Math.floor(num / 1000);
       thousandIndex++;
   }
   
   return result.trim();
}
    </script>
<script>
// CSRF token setup for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function printReceipt(paymentId) {
    if (paymentId === 'deposit') {
        console.log('Printing deposit receipt...');
        // Add your print receipt logic here
    } else {
        console.log('Printing payment receipt for payment ID:', paymentId);
        // Add your print receipt logic here
    }
}

function verifyPayment(paymentId) {
    Swal.fire({
        title: 'Verify Payment',
        text: 'Are you sure you want to verify this payment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, verify it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Verifying Payment...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/hire-purchase/payments/${paymentId}/verify`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Payment has been verified successfully.',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while verifying the payment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

function printAgreement() {
    window.print();
}

function downloadPDF() {
    console.log('Downloading PDF...');
    // Add your PDF download logic here
}

function sendCopy() {
    console.log('Sending email copy...');
    // Add your email logic here
}

function approveAgreement(agreementId) {
    Swal.fire({
        title: 'Approve Agreement',
        text: 'Are you sure you want to approve this agreement?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Approving Agreement...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/hire-purchase/${agreementId}/approve`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Agreement has been approved successfully.',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while approving the agreement.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

// Enhanced payment form submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const amount = parseFloat(formData.get('payment_amount'));
    const maxAmount = {{ $actualOutstanding }};
    const paymentDate = formData.get('payment_date');
    const paymentMethod = formData.get('payment_method');
    
    // Enhanced validation
    if (!amount || amount <= 0) {
        Swal.fire({
            title: 'Invalid Amount',
            text: 'Please enter a valid payment amount.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    if (amount > maxAmount) {
        Swal.fire({
            title: 'Amount Too High',
            text: `Payment amount cannot exceed outstanding balance of KSh ${maxAmount.toLocaleString()}`,
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    if (!paymentDate) {
        Swal.fire({
            title: 'Missing Date',
            text: 'Please select a payment date.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    if (!paymentMethod) {
        Swal.fire({
            title: 'Missing Payment Method',
            text: 'Please select a payment method.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    // Confirmation dialog
    Swal.fire({
        title: 'Confirm Payment',
        html: `
            <div class="text-left">
                <p><strong>Amount:</strong> KSh ${amount.toLocaleString()}</p>
                <p><strong>Date:</strong> ${paymentDate}</p>
                <p><strong>Method:</strong> ${paymentMethod.replace('_', ' ').toUpperCase()}</p>
                <p><strong>Remaining Balance:</strong> KSh ${(maxAmount - amount).toLocaleString()}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Record Payment',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Recording Payment...',
                text: 'Please wait while we process your payment.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '/hire-purchase/payments/store',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#recordPaymentModal').modal('hide');
                    form.reset();
                    
                    Swal.fire({
                        title: 'Payment Recorded!',
                        text: 'Payment has been successfully recorded.',
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while recording the payment.';
                    let errorDetails = '';
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'Please check the following errors:';
                        errorDetails = '<ul class="text-left mt-2">';
                        for (const field in errors) {
                            errors[field].forEach(error => {
                                errorDetails += `<li>${error}</li>`;
                            });
                        }
                        errorDetails += '</ul>';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        html: errorMessage + errorDetails,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
});

// Auto-fill reference number based on payment method
document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
    const referenceInput = document.querySelector('input[name="payment_reference"]');
    const method = this.value;
    
    switch(method) {
        case 'mpesa':
            referenceInput.placeholder = 'M-Pesa Transaction Code (e.g., QC12345678)';
            break;
        case 'bank_transfer':
            referenceInput.placeholder = 'Bank Transaction Reference';
            break;
        case 'cheque':
            referenceInput.placeholder = 'Cheque Number';
            break;
        case 'card':
            referenceInput.placeholder = 'Card Transaction Reference';
            break;
        case 'cash':
            referenceInput.placeholder = 'Receipt Number (if any)';
            break;
        default:
            referenceInput.placeholder = 'Transaction/Receipt Number';
    }
});

// Input validation for payment amount
document.querySelector('input[name="payment_amount"]').addEventListener('input', function() {
    const maxAmount = {{ $actualOutstanding }};
    const enteredAmount = parseFloat(this.value);
    
    if (enteredAmount > maxAmount) {
        this.setCustomValidity(`Amount cannot exceed KSh ${maxAmount.toLocaleString()}`);
    } else {
        this.setCustomValidity('');
    }
});

// Reset modal form when modal is closed
$('#recordPaymentModal').on('hidden.bs.modal', function () {
    document.getElementById('paymentForm').reset();
    document.querySelector('input[name="payment_amount"]').setCustomValidity('');
});

// Quick amount buttons
function setQuickAmount(amount) {
    document.querySelector('input[name="payment_amount"]').value = amount;
}

// Show success/error messages
$(document).ready(function() {
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: '{{ session("success") }}',
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
        });
    @endif
    
    @if(session('error'))
        Swal.fire({
            title: 'Error!',
            text: '{{ session("error") }}',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    @endif
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount);
}

// Print specific sections
function printSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print - ${sectionId}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            .no-print { display: none !important; }
                            body { font-size: 12px; }
                            .table { font-size: 11px; }
                        }
                    </style>
                </head>
                <body class="p-3">
                    ${section.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

// Export data functions
function exportToCSV() {
    // Add CSV export logic here
    console.log('Exporting to CSV...');
}

function exportToExcel() {
    // Add Excel export logic here
    console.log('Exporting to Excel...');
}

// Advanced search functionality
function searchPayments() {
    // Add search logic here
    console.log('Searching payments...');
}

// Bulk operations
function selectAllPayments() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="payment_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllPayments() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="payment_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

// Dynamic interest calculation (if needed)
function calculateInterest(principal, rate, months) {
    const monthlyRate = rate / 100;
    const monthlyPayment = (principal * monthlyRate * Math.pow(1 + monthlyRate, months)) / 
                          (Math.pow(1 + monthlyRate, months) - 1);
    return monthlyPayment;
}

// Payment reminder functionality
function sendPaymentReminder(agreementId) {
    Swal.fire({
        title: 'Send Payment Reminder',
        text: 'Send a payment reminder to the client?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Send Reminder',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Add reminder logic here
            Swal.fire('Sent!', 'Payment reminder has been sent.', 'success');
        }
    });
}

// Auto-refresh data every 5 minutes (optional)
setInterval(function() {
    // Add auto-refresh logic if needed
    console.log('Auto-refresh check...');
}, 300000); // 5 minutes

// Enhanced error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // Optionally log to server or show user-friendly message
});

// Performance monitoring
window.addEventListener('load', function() {
    const loadTime = performance.now();
    console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
});
</script>
<style>
/* Lump Sum Payment Button Styling */
.lump-sum-btn {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
}

.lump-sum-btn:hover {
    background: linear-gradient(45deg, #218838, #1bb185);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    color: white;
}

/* Rescheduling Option Cards */
.rescheduling-option {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-bottom: 15px;
}

.rescheduling-option:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.rescheduling-option.selected {
    border-color: #28a745;
    background-color: rgba(40, 167, 69, 0.05);
}

.rescheduling-option .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

/* Timeline Styling */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #0d6efd, #28a745);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 10px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(45deg, #0d6efd, #28a745);
    border: 3px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.timeline-content {
    margin-left: 20px;
}

/* Enhanced Card Hover Effects */
.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

/* Loading Animation */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.calculating-pulse {
    animation: pulse 1.5s infinite;
}

/* Success/Info Backgrounds */
.bg-soft-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-soft-info {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-soft-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

/* Responsive Improvements */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -17px;
        width: 25px;
        height: 25px;
        font-size: 10px;
    }
    
    .lump-sum-btn {
        font-size: 0.875rem;
    }
}

/* Modal Enhancements */
.modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px 15px 0 0;
}

/* Form Improvements */
.form-control:focus,
.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Button Group Enhancements */
.btn-group-sm .btn {
    border-radius: 6px;
    margin-right: 2px;
}

/* Alert Enhancements */
.alert {
    border-radius: 10px;
    border: none;
}

.alert-info {
    background: linear-gradient(135deg, #cce7ff 0%, #e6f3ff 100%);
    color: #0c5460;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #f0f9f0 100%);
    color: #155724;
}

/* Badge Improvements */
.badge {
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Table Enhancements */
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Progress Bar Enhancements */
.progress {
    border-radius: 10px;
    background-color: #f8f9fa;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.6s ease;
}
</style>
<!-- DEBUGGING AND FIXES FOR THE NON-CLICKABLE BUTTON -->


</x-app-layout>