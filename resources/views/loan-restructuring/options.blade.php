<x-app-layout>
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Header Section -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center">
                <a href="{{ route('hire-purchase.show', $agreement->id) }}" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Back to Agreement
                </a>
                <div>
                    <h4 class="fs-18 fw-semibold m-0">
                        <i class="fas fa-sync-alt me-2"></i>
                        Loan Restructuring Options
                    </h4>
                    <small >
                        Client: {{ $agreement->client_name }} | 
                        Vehicle: {{ $agreement->vehicle_make }} {{ $agreement->vehicle_model }}
                    </small>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-info fs-6 px-3 py-2">
                Current Status: {{ ucfirst($agreement->status) }}
            </span>
        </div>
    </div>

    <!-- Current Financial Summary -->
    <div class="card mb-4">
        <div class="card-header bg-info bg-opacity-10">
            <h5 class="card-title mb-0 text-info">
                <i class="fas fa-calculator me-2"></i>Current Financial Position
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                        <h6 class=" mb-1">Due Payments</h6>
                        <h4 class="mb-0 text-warning">KSh {{ number_format($financialSummary['due_payments'], 2) }}</h4>
                        <small >{{ $financialSummary['breakdown']['overdue_count'] }} overdue</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                        <h6 class=" mb-1">Principal Balance</h6>
                        <h4 class="mb-0 text-primary">KSh {{ number_format($financialSummary['principal_balance'], 2) }}</h4>
                        <small >{{ $financialSummary['breakdown']['unpaid_count'] }} installments</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                        <h6 class=" mb-1">Total Penalties</h6>
                        <h4 class="mb-0 text-danger"> KES {{ number_format($agreement->penalties->sum('penalty_amount'), 2) }}</h4>
                      
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                        <h6 class=" mb-1">Total Outstanding</h6>
                        <h4 class="mb-0 text-success">KSh {{ number_format($financialSummary['total_outstanding'], 2) }}</h4>
                        <small >Base for restructuring</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Configuration Section -->
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="card-title mb-0 text-warning">
                <i class="fas fa-cog me-2"></i>Admin Configuration
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Custom Payment Duration (months)</label>
                        <input type="number" class="form-control" id="adminPaymentDuration" 
                               min="1" max="120" placeholder="Enter desired duration">
                        <small >
                            Current remaining: <strong>{{ $currentRemainingMonths }} months</strong><br>
                            • Enter <strong>less than {{ $currentRemainingMonths }}</strong> to auto-select "Reduce Duration"<br>
                            • Enter <strong>more than {{ $currentRemainingMonths }}</strong> to auto-select "Increase Duration"
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Restructuring Fee Rate (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="adminFeeRate" 
                                   value="3" min="0" max="10" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                        <small >Default: 3% of outstanding balance</small>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mb-0">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Smart Selection:</strong> The system will automatically recommend the appropriate restructuring option based on your duration input and highlight it for quick processing.
            </div>
        </div>
    </div>

    <!-- Loan Restructuring Configuration -->
    <div class="card">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-cogs me-2"></i>Loan Restructuring Configuration
            </h5>
        </div>
        <div class="card-body">
            
            <!-- Restructuring Fee Notice & Loan Calculation -->
            <div class="alert alert-info mb-4">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>Restructuring Fee Notice & Loan Calculation
                </h6>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded border">
                            <h6 class="fw-semibold mb-2 text-primary">Current Loan Status</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small >Outstanding Balance:</small><br>
                                    <strong>KSh {{ number_format($financialSummary['total_outstanding'], 2) }}</strong>
                                </div>
                                <div class="col-6">
                                    <small >Remaining Months:</small><br>
                                    <strong>{{ $currentRemainingMonths }} months</strong>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small >Current Payment:</small><br>
                                    <strong>KSh {{ number_format($agreement->monthly_payment, 2) }}</strong>
                                </div>
                                <div class="col-6">
                                    <small >Interest Rate:</small><br>
                                    <strong id="displayInterestRate">Loading...</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded border">
                            <h6 class="fw-semibold mb-2 text-success">After Restructuring</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small >Restructuring Fee (<span id="displayFeeRate">3</span>%):</small><br>
                                    <strong class="text-warning" id="restructuringFee">Calculating...</strong>
                                </div>
                                <div class="col-6">
                                    <small >New Loan Amount:</small><br>
                                    <strong class="text-success" id="newLoanAmount">Calculating...</strong>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small >New Monthly Payment:</small><br>
                                    <strong class="text-primary" id="newMonthlyPayment">Based on selection</strong>
                                </div>
                                <div class="col-6">
                                    <small >New Duration:</small><br>
                                    <strong class="text-info" id="newDuration">Based on selection</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-3 bg-light rounded">
                    <h6 class="fw-semibold mb-2" style="color:#000">
                        <i class="fas fa-calculator me-2"></i>Calculation Breakdown
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                             <div class="text-center p-2 border rounded bg-white">
                            <small class="d-block" style="color:#000">
                                Step 1: Add Restructuring Fee
                            </small>
                            <div class="fw-semibold">
                                KSh {{ number_format($financialSummary['total_outstanding'], 2) }}
                                + {{ number_format($agreement->penalties->sum('penalty_amount'), 2) }}
                                + <span id="feeAmountDisplay">Fee</span>
                            </div>
                            <small class="text-success">
                                = <span id="totalNewLoan">New Loan Amount</span>
                            </small>
                        </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-2 border rounded bg-white">
                                <small class=" d-block" style="color:#000">Step 2: Apply Interest Rate</small>
                                <div class="fw-semibold">
                                    New Loan × <span id="monthlyRate">Rate</span>%
                                </div>
                                <small class="text-info">Monthly interest component</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-2 border rounded bg-white">
                                <small class=" d-block" style="color:#000">Step 3: Calculate Payment</small>
                                <div class="fw-semibold">
                                    Based on <span id="selectedDuration">Duration</span> months
                                </div>
                                <small class="text-primary">Final monthly payment</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-arrow-up text-warning me-2"></i>
                                <span class="fw-semibold">Payment Change:</span>
                                <span class="ms-2" id="paymentChange">Configure above to see change</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-info me-2"></i>
                                <span class="fw-semibold">Duration Change:</span>
                                <span class="ms-2" id="durationChange">Configure above to see change</span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-coins text-success me-2"></i>
                                <span class="fw-semibold">Interest Impact:</span>
                                <span class="ms-2" id="interestImpact">Configure above to see impact</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-line text-primary me-2"></i>
                                <span class="fw-semibold">Total Cost:</span>
                                <span class="ms-2" id="totalCost">Configure above to see total</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form id="restructuringForm">
                @csrf
                <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                <input type="hidden" name="restructuring_date" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="admin_payment_duration" id="hiddenAdminDuration">
                <input type="hidden" name="admin_fee_rate" id="hiddenAdminFeeRate" value="3">
                <input type="hidden" name="selected_fee_rate" id="hiddenSelectedFeeRate">
                <input type="hidden" name="selected_duration" id="hiddenSelectedDuration">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Restructuring Type</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="restructuring_type" 
                                           id="reduceDuration" value="reduce_duration">
                                    <label class="form-check-label" for="reduceDuration">
                                        <i class="fas fa-tachometer-alt me-1"></i>Reduce Duration
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="restructuring_type" 
                                           id="increaseDuration" value="increase_duration">
                                    <label class="form-check-label" for="increaseDuration">
                                        <i class="fas fa-calendar-plus me-1"></i>Increase Duration
                                    </label>
                                </div>
                            </div>
                            <small class="text-info" id="autoSelectionMessage">Auto-selected based on admin configuration</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Restructuring Fee Rate (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-light" id="displayFeeRateInput" 
                                       value="3" readonly style="color:#000">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-info">Set by admin configuration above</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Custom Duration (months)</label>
                            <input type="number" class="form-control" id="displayDurationInput" 
                                   readonly placeholder="Set by admin configuration" style="color:#000">
                            <small class="text-info">
                                Current: {{ $currentRemainingMonths }} months | Set by admin above
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Calculation Result</label>
                            <div class="form-control bg-light" id="calculationResult">
                                Configure admin options above to see calculation
                            </div>
                            <small class="text-info">Live calculation based on admin inputs</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Additional Notes (Optional)</label>
                    <textarea class="form-control" name="notes" rows="3" 
                              placeholder="Any specific requirements or comments about this restructuring..."></textarea>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitRestructuring" disabled>
                        <i class="fas fa-sync-alt me-1"></i>Process Restructuring
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Important Information -->
    <div class="card mt-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h6 class="card-title mb-0 text-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>Important Information
            </h6>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>This restructuring will create a new payment schedule that replaces your current one</li>
                <li>A restructuring fee will be added to your outstanding balance based on the admin-configured rate</li>
                <li>The original interest rate will be applied to the new loan amount</li>
                <li>This action cannot be undone once processed</li>
                <li>You can restructure a maximum of 3 times during the loan term</li>
                <li>A waiting period of 6 months applies between restructuring requests</li>
            </ul>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmRestructuringModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Loan Restructuring
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Please review the details carefully before confirming:</strong>
                </div>
                <div id="confirmationDetails"></div>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmCheckbox" required>
                    <label class="form-check-label" for="confirmCheckbox">
                        I understand and agree to the restructuring terms and conditions
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="finalConfirmBtn" disabled>
                    <i class="fas fa-sync-alt me-1"></i>Confirm Restructuring
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const agreementId = {{ $agreement->id }};
    const totalOutstanding = {{ $financialSummary['total_outstanding'] }};
    const currentRemainingMonths = {{ $currentRemainingMonths }};
    const currentPayment = {{ $agreement->monthly_payment }};
    const agreementData = @json($agreement);
    const penalties = {{ $agreement->penalties->sum('penalty_amount') }};
    
    const adminDurationInput = document.getElementById('adminPaymentDuration');
    const adminFeeRateInput = document.getElementById('adminFeeRate');
    const reduceRadio = document.getElementById('reduceDuration');
    const increaseRadio = document.getElementById('increaseDuration');
    const displayFeeRateInput = document.getElementById('displayFeeRateInput');
    const displayDurationInput = document.getElementById('displayDurationInput');
    const calculationResult = document.getElementById('calculationResult');
    const submitButton = document.getElementById('submitRestructuring');
    const autoSelectionMessage = document.getElementById('autoSelectionMessage');
    
    const hiddenAdminDuration = document.getElementById('hiddenAdminDuration');
    const hiddenAdminFeeRate = document.getElementById('hiddenAdminFeeRate');
    const hiddenSelectedFeeRate = document.getElementById('hiddenSelectedFeeRate');
    const hiddenSelectedDuration = document.getElementById('hiddenSelectedDuration');
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    initializeForm();
    
    function initializeForm() {
        updateEnhancedFeeNotice();
        setupEventListeners();
    }
    
    function setupEventListeners() {
        adminDurationInput?.addEventListener('input', handleAdminDurationChange);
        adminFeeRateInput?.addEventListener('input', handleAdminFeeChange);
        
        const form = document.getElementById('restructuringForm');
        form?.addEventListener('submit', handleFormSubmission);
        
        const confirmCheckbox = document.getElementById('confirmCheckbox');
        confirmCheckbox?.addEventListener('change', function() {
            const finalConfirmBtn = document.getElementById('finalConfirmBtn');
            if (finalConfirmBtn) finalConfirmBtn.disabled = !this.checked;
        });
        
        const finalConfirmBtn = document.getElementById('finalConfirmBtn');
        finalConfirmBtn?.addEventListener('click', processRestructuring);
    }
    
    function handleAdminDurationChange() {
        const duration = parseInt(adminDurationInput.value);
        
        displayDurationInput.value = duration || '';
        hiddenAdminDuration.value = adminDurationInput.value;
        hiddenSelectedDuration.value = adminDurationInput.value;
        
        if (duration && duration > 0) {
            if (duration < currentRemainingMonths) {
                selectRestructuringType('reduce', duration);
            } else if (duration > currentRemainingMonths) {
                selectRestructuringType('increase', duration);
            } else {
                autoSelectionMessage.textContent = 'Duration same as current - select type manually';
                clearTypeSelection();
            }
            
            calculateAndDisplay();
            enableSubmit();
        } else {
            clearTypeSelection();
            calculationResult.textContent = 'Enter duration in admin configuration to see calculation';
            submitButton.disabled = true;
        }
    }
    
    function handleAdminFeeChange() {
        const feeRate = parseFloat(adminFeeRateInput.value) || 3;
        
        displayFeeRateInput.value = feeRate;
        hiddenAdminFeeRate.value = feeRate;
        hiddenSelectedFeeRate.value = feeRate;
        
        updateEnhancedFeeNotice(null, feeRate);
        
        if (getSelectedType()) {
            calculateAndDisplay();
        }
    }
    
    function selectRestructuringType(type, duration) {
        reduceRadio.checked = false;
        increaseRadio.checked = false;
        
        if (type === 'reduce') {
            reduceRadio.checked = true;
            const monthsSaved = currentRemainingMonths - duration;
            autoSelectionMessage.innerHTML = `<span class="text-primary"><i class="fas fa-magic me-1"></i>Auto-selected: Reduce Duration (${monthsSaved} months faster)</span>`;
        } else {
            increaseRadio.checked = true;
            const monthsAdded = duration - currentRemainingMonths;
            autoSelectionMessage.innerHTML = `<span class="text-success"><i class="fas fa-magic me-1"></i>Auto-selected: Increase Duration (+${monthsAdded} months longer)</span>`;
        }
    }
    
    function clearTypeSelection() {
        reduceRadio.checked = false;
        increaseRadio.checked = false;
        autoSelectionMessage.textContent = 'Configure duration above to auto-select type';
    }
    
    function getSelectedType() {
        if (reduceRadio?.checked) return 'reduce_duration';
        if (increaseRadio?.checked) return 'increase_duration';
        return null;
    }
    
    function enableSubmit() {
        submitButton.disabled = !getSelectedType();
    }
    
    function calculateAndDisplay() {
        const restructuringType = getSelectedType();
        const feeRate = parseFloat(adminFeeRateInput.value) || 3;
        const customDuration = adminDurationInput.value;
        
        if (!restructuringType) {
            calculationResult.textContent = 'Configure admin settings above';
            return;
        }
        
        calculationResult.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Calculating...';
        
        const params = new URLSearchParams({
            agreement_id: agreementId,
            admin_fee_rate: feeRate,
            restructuring_type: restructuringType
        });
        
        if (customDuration) {
            params.append('new_duration', customDuration);  
        }
        
        fetch(`{{ route('loan-restructuring.calculate-options') }}?${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const option = data.options[restructuringType];
                if (option && !option.error) {
                    displayCalculationResult(option);
                    updateEnhancedFeeNotice(option, feeRate);
                } else {
                    calculationResult.innerHTML = '<span class="text-danger">Calculation error: ' + (option?.error || 'Unknown error') + '</span>';
                }
            } else {
                calculationResult.innerHTML = '<span class="text-danger">Error: ' + (data.error || 'Calculation failed') + '</span>';
            }
        })
        .catch(error => {
            console.error('Calculation error:', error);
            calculationResult.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
        });
    }
    
    function displayCalculationResult(option) {
        const newPayment = parseFloat(option.new_payment) || 0;
        const newDuration = parseInt(option.new_duration) || 0;
        const restructuringType = getSelectedType();
        
        let resultHtml = '';
        
        if (restructuringType === 'reduce_duration') {
            const paymentIncrease = parseFloat(option.payment_increase) || 0;
            const durationReduction = parseInt(option.duration_reduction) || 0;
            const interestSaved = parseFloat(option.total_interest_saved) || 0;
            
            resultHtml = `
                <div class="row">
                    <div class="col-6">
                        <strong>New Payment:</strong><br>
                        <span class="text-primary">KSh ${newPayment.toLocaleString()}</span>
                        <small class="text-success d-block">+KSh ${paymentIncrease.toLocaleString()}</small>
                    </div>
                    <div class="col-6">
                        <strong>Duration:</strong><br>
                        <span class="text-info">${newDuration} months</span>
                        <small class="text-success d-block">${durationReduction} months saved</small>
                    </div>
                </div>
                <div class="mt-2">
                    <small><strong>Interest Savings:</strong> <span class="text-success">KSh ${interestSaved.toLocaleString()}</span></small>
                </div>
            `;
        } else {
            const paymentReduction = parseFloat(option.payment_reduction) || 0;
            const durationIncrease = parseInt(option.duration_increase) || 0;
            const additionalInterest = parseFloat(option.additional_interest) || 0;
            
            resultHtml = `
                <div class="row">
                    <div class="col-6">
                        <strong>New Payment:</strong><br>
                        <span class="text-success">KSh ${newPayment.toLocaleString()}</span>
                        <small class="text-success d-block">-KSh ${paymentReduction.toLocaleString()}</small>
                    </div>
                    <div class="col-6">
                        <strong>Duration:</strong><br>
                        <span class="text-info">${newDuration} months</span>
                        <small class="text-warning d-block">+${durationIncrease} months</small>
                    </div>
                </div>
                <div class="mt-2">
                    <small><strong>Additional Interest:</strong> <span class="text-warning">KSh ${additionalInterest.toLocaleString()}</span></small>
                </div>
            `;
        }
        
        calculationResult.innerHTML = resultHtml;
    }
    
    function updateEnhancedFeeNotice(optionData = null, feeRate = null) {
        const currentFeeRate = feeRate || parseFloat(adminFeeRateInput?.value) || 3;
        const originalInterestRate = {{ $originalMonthlyRate }};
        
        const restructuringFee = (totalOutstanding * currentFeeRate) / 100;
        const newLoanAmount = totalOutstanding + penalties + restructuringFee;
        
        updateElement('displayFeeRate', currentFeeRate);
        updateElement('restructuringFee', 'KSh ' + restructuringFee.toLocaleString());
        updateElement('newLoanAmount', 'KSh ' + newLoanAmount.toLocaleString());
        updateElement('feeAmountDisplay', 'KSh ' + restructuringFee.toLocaleString());
        updateElement('totalNewLoan', 'KSh ' + newLoanAmount.toLocaleString());
        updateElement('monthlyRate', originalInterestRate.toFixed(2));
        updateElement('displayInterestRate', originalInterestRate.toFixed(2) + '%');
        
        if (optionData) {
            updateOptionSpecificData(optionData);
        } else {
            updateElement('newMonthlyPayment', 'Configure admin settings above');
            updateElement('newDuration', 'Configure admin settings above');
            updateElement('selectedDuration', 'Selected');
            updateElement('paymentChange', 'Configure admin settings above');
            updateElement('durationChange', 'Configure admin settings above');
            updateElement('interestImpact', 'Configure admin settings above');
            updateElement('totalCost', 'Configure admin settings above');
        }
    }
    
    function updateOptionSpecificData(optionData) {
        const newPayment = parseFloat(optionData.new_payment) || 0;
        const newDuration = parseInt(optionData.new_duration) || 0;
        
        updateElement('newMonthlyPayment', 'KSh ' + newPayment.toLocaleString());
        updateElement('newDuration', newDuration + ' months');
        updateElement('selectedDuration', newDuration);
        
        const paymentChange = newPayment - currentPayment;
        const durationChange = newDuration - currentRemainingMonths;
        
        if (paymentChange > 0) {
            updateElementHTML('paymentChange', `<span class="text-danger">+KSh ${paymentChange.toLocaleString()}</span> (Higher)`);
        } else if (paymentChange < 0) {
            updateElementHTML('paymentChange', `<span class="text-success">-KSh ${Math.abs(paymentChange).toLocaleString()}</span> (Lower)`);
        } else {
            updateElementHTML('paymentChange', '<span>No change</span>');
        }
        
        if (durationChange > 0) {
            updateElementHTML('durationChange', `<span class="text-warning">+${durationChange} months</span> (Longer)`);
        } else if (durationChange < 0) {
            updateElementHTML('durationChange', `<span class="text-success">${durationChange} months</span> (Shorter)`);
        } else {
            updateElementHTML('durationChange', '<span>No change</span>');
        }
        
        if (optionData.total_interest_saved && optionData.total_interest_saved > 0) {
            updateElementHTML('interestImpact', `<span class="text-success">Save KSh ${parseFloat(optionData.total_interest_saved).toLocaleString()}</span>`);
        } else if (optionData.additional_interest && optionData.additional_interest > 0) {
            updateElementHTML('interestImpact', `<span class="text-warning">+KSh ${parseFloat(optionData.additional_interest).toLocaleString()}</span> extra`);
        } else {
            updateElement('interestImpact', 'Calculating...');
        }
        
        const totalCost = newPayment * newDuration;
        const currentTotalCost = currentPayment * currentRemainingMonths;
        const costDifference = totalCost - currentTotalCost;
        
        if (costDifference > 0) {
            updateElementHTML('totalCost', `KSh ${totalCost.toLocaleString()} <span class="text-warning">(+KSh ${costDifference.toLocaleString()})</span>`);
        } else if (costDifference < 0) {
            updateElementHTML('totalCost', `KSh ${totalCost.toLocaleString()} <span class="text-success">(Save KSh ${Math.abs(costDifference).toLocaleString()})</span>`);
        } else {
            updateElementHTML('totalCost', `KSh ${totalCost.toLocaleString()} <span>Same</span>`);
        }
    }
    
    function updateElement(id, text) {
        const element = document.getElementById(id);
        if (element) element.textContent = text;
    }
    
    function updateElementHTML(id, html) {
        const element = document.getElementById(id);
        if (element) element.innerHTML = html;
    }
    
    function getOriginalInterestRate() {
        if (agreementData.monthly_interest_rate && agreementData.monthly_interest_rate > 0) {
            return agreementData.monthly_interest_rate;
        }
        
        if (agreementData.interest_rate > 0 && agreementData.interest_rate <= 10) {
            return agreementData.interest_rate;
        }
        
        if (agreementData.interest_rate > 10) {
            return agreementData.interest_rate / 12;
        }
        
        const depositPercentage = (agreementData.deposit_amount / agreementData.vehicle_price) * 100;
        return depositPercentage >= 50 ? 4.29 : 4.50;
    }
    
    function handleFormSubmission(e) {
        e.preventDefault();
        showConfirmationModal();
    }
    
    // ✨ UPDATED: SweetAlert Implementation
    function showConfirmationModal() {
        const restructuringType = getSelectedType();
        
        if (!restructuringType) {
            // SweetAlert for configuration required
            Swal.fire({
                icon: 'warning',
                title: 'Configuration Required',
                text: 'Please configure admin settings to enable restructuring',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ffc107',
                customClass: {
                    popup: 'swal2-warning'
                }
            });
            return;
        }
        
        const selectedFeeRate = parseFloat(adminFeeRateInput.value) || 3;
        const restructuringFee = (totalOutstanding * selectedFeeRate) / 100;
        const newLoanAmount = totalOutstanding + penalties + restructuringFee;
        
        // Create detailed HTML content for SweetAlert
        const confirmationContent = `
            <div class="text-start">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-sync-alt me-2"></i>
                            ${restructuringType === 'reduce_duration' ? 'Reduce Duration' : 'Increase Duration'} Restructuring
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            ${calculationResult.innerHTML}
                        </div>
                        <div class="bg-light p-3 rounded border">
                            <h6 class="fw-bold mb-2" style="color:#000">
                                <i class="fas fa-cog me-2"></i>Admin Configuration:
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <small style="color:#000"><strong>Fee Rate:</strong> ${selectedFeeRate}%</small><br>
                                    <small style="color:#000"><strong>Fee Amount:</strong> KSh ${restructuringFee.toLocaleString()}</small>
                                </div>
                                <div class="col-6">
                                    <small style="color:#000"><strong>New Loan Amount:</strong> KSh ${newLoanAmount.toLocaleString()}</small><br>
                                    ${adminDurationInput.value ? `<small style="color:#000"><strong>Duration:</strong> ${adminDurationInput.value} months</small>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please review carefully:</strong> This action cannot be undone once processed.
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Show SweetAlert confirmation
        Swal.fire({
            title: '<i class="fas fa-exclamation-triangle text-warning"></i> Confirm Loan Restructuring',
            html: confirmationContent,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sync-alt me-1"></i> Confirm Restructuring',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            buttonsStyling: true,
            customClass: {
                popup: 'swal2-large',
                htmlContainer: 'text-start p-0',
                actions: 'gap-3'
            },
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return processRestructuringPromise();
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // Success handled in processRestructuringPromise
            }
        });
    }
    
    // ✨ NEW: Promise-based restructuring for SweetAlert
    function processRestructuringPromise() {
        const form = document.getElementById('restructuringForm');
        if (!form) {
            throw new Error('Form not found');
        }
        
        const formData = new FormData(form);
        
        return fetch('{{ route("loan-restructuring.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: '<i class="fas fa-check-circle text-success"></i> Success!',
                    html: `
                        <div class="text-center">
                            <h5 class="text-success mb-3">Loan Restructured Successfully!</h5>
                            <p class="mb-3">Your loan has been restructured with the new terms.</p>
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle me-2"></i>
                                You will be redirected to the agreement details page.
                            </div>
                        </div>
                    `,
                    confirmButtonText: '<i class="fas fa-arrow-right me-1"></i> Continue to Agreement',
                    confirmButtonColor: '#28a745',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'swal2-success-custom'
                    }
                }).then(() => {
                    window.location.href = '{{ route("hire-purchase.show", $agreement->id) }}';
                });
                return data;
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error in SweetAlert
            Swal.fire({
                icon: 'error',
                title: '<i class="fas fa-exclamation-circle text-danger"></i> Error',
                html: `
                    <div class="text-center">
                        <h5 class="text-danger mb-3">Restructuring Failed</h5>
                        <p class="mb-3">${error.message || 'Network error occurred. Please try again.'}</p>
                        <div class="alert alert-danger">
                            <i class="fas fa-info-circle me-2"></i>
                            Please check your connection and try again.
                        </div>
                    </div>
                `,
                confirmButtonText: '<i class="fas fa-redo me-1"></i> Try Again',
                confirmButtonColor: '#dc3545',
                customClass: {
                    popup: 'swal2-error-custom'
                }
            });
            throw error;
        });
    }
    
    // ✨ LEGACY: Keep original function for modal-based approach (if needed)
    function processRestructuring() {
        const form = document.getElementById('restructuringForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const finalConfirmBtn = document.getElementById('finalConfirmBtn');
        
        if (finalConfirmBtn) {
            finalConfirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            finalConfirmBtn.disabled = true;
        }
        
        fetch('{{ route("loan-restructuring.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // SweetAlert for success
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: `
                        <div class="text-center">
                            <h5 class="text-success mb-3">Loan Restructured Successfully!</h5>
                            <p>Your loan has been restructured with the new terms.</p>
                        </div>
                    `,
                    confirmButtonText: 'Continue to Agreement',
                    confirmButtonColor: '#28a745',
                    customClass: {
                        popup: 'swal2-success'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("hire-purchase.show", $agreement->id) }}';
                    }
                });
            } else {
                // SweetAlert for error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Unknown error occurred',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // SweetAlert for network error
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Network error occurred. Please try again.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        })
        .finally(() => {
            if (finalConfirmBtn) {
                finalConfirmBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Confirm Restructuring';
                finalConfirmBtn.disabled = false;
            }
        });
    }
});
</script>

<style>
.form-check-input:disabled + .form-check-label {
    opacity: 0.6;
}

.bg-light {
    background-color: #f8f9fa !important;
}

input[readonly] {
    background-color: #e9ecef;
    opacity: 1;
}

.card {
    transition: all 0.3s ease;
}

.badge {
    font-size: 0.75rem;
}
</style>

</x-app-layout>