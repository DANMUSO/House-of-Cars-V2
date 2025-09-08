<x-app-layout>
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
                    <small class="text-muted">
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

    <!-- Eligibility Check -->
    @if(!$eligibility['eligible'])
        <div class="alert alert-danger border-danger">
            <h6 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-2"></i>Restructuring Not Available
            </h6>
            <ul class="mb-0">
                @foreach($eligibility['errors'] as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @else

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
                        <h6 class="text-muted mb-1">Due Payments</h6>
                        <h4 class="mb-0 text-warning">KSh {{ number_format($financialSummary['due_payments'], 2) }}</h4>
                        <small class="text-muted">{{ $financialSummary['breakdown']['overdue_count'] }} overdue</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                        <h6 class="text-muted mb-1">Principal Balance</h6>
                        <h4 class="mb-0 text-primary">KSh {{ number_format($financialSummary['principal_balance'], 2) }}</h4>
                        <small class="text-muted">{{ $financialSummary['breakdown']['unpaid_count'] }} installments</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                        <h6 class="text-muted mb-1">Total Penalties</h6>
                        <h4 class="mb-0 text-danger">KSh {{ number_format($financialSummary['total_penalties'], 2) }}</h4>
                        <small class="text-muted">{{ $financialSummary['breakdown']['penalty_count'] }} penalties</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                        <h6 class="text-muted mb-1">Total Outstanding</h6>
                        <h4 class="mb-0 text-success">KSh {{ number_format($financialSummary['total_outstanding'], 2) }}</h4>
                        <small class="text-muted">Base for restructuring</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Restructuring Options -->
    <div class="card">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-cogs me-2"></i>Choose Your Restructuring Option
            </h5>
        </div>
        <div class="card-body">
            
            <!-- Restructuring Fee Notice -->
            <div class="alert alert-info mb-4">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>Restructuring Fee Notice
                </h6>
                <p class="mb-2">
                    A restructuring fee of <strong>3%</strong> will be applied to your total outstanding amount.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <small><strong>Current Outstanding:</strong> KSh {{ number_format($financialSummary['total_outstanding'], 2) }}</small>
                    </div>
                    <div class="col-md-6">
                        <small><strong>Restructuring Fee (3%):</strong> <span id="restructuringFee">Calculating...</span></small>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top">
                    <small><strong>New Loan Amount:</strong> <span id="newLoanAmount">Calculating...</span></small>
                </div>
            </div>

            <form id="restructuringForm">
                @csrf
                <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                <input type="hidden" name="restructuring_date" value="{{ date('Y-m-d') }}">

                <!-- Option Selection -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary h-100" id="reduceDurationCard">
                            <div class="card-header bg-primary bg-opacity-10">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="restructuring_type" 
                                           id="reduceDuration" value="reduce_duration">
                                    <label class="form-check-label w-100" for="reduceDuration">
                                        <h6 class="mb-1 text-primary">
                                            <i class="fas fa-tachometer-alt me-2"></i>Reduce Duration (Pay Faster)
                                        </h6>
                                    </label>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Increase your monthly payment to finish the loan faster and save on interest.</p>
                                <div id="reduceDurationDetails" class="option-details">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Calculating options...</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="badge bg-primary">Faster Completion</span>
                                    <span class="badge bg-success">Interest Savings</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-success h-100" id="increaseDurationCard">
                            <div class="card-header bg-success bg-opacity-10">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="restructuring_type" 
                                           id="increaseDuration" value="increase_duration">
                                    <label class="form-check-label w-100" for="increaseDuration">
                                        <h6 class="mb-1 text-success">
                                            <i class="fas fa-calendar-plus me-2"></i>Increase Duration (Lower Payments)
                                        </h6>
                                    </label>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Reduce your monthly payment by extending the loan duration.</p>
                                
                                <!-- Custom Duration Input -->
                                <div class="mb-3" id="customDurationSection" style="display: none;">
                                    <label class="form-label">Preferred Duration (months)</label>
                                    <input type="number" class="form-control" name="new_duration" 
                                           id="newDurationInput" min="1" max="84" 
                                           placeholder="Enter desired duration">
                                    <small class="text-muted">Leave blank for automatic calculation</small>
                                </div>

                                <div id="increaseDurationDetails" class="option-details">
                                    <div class="text-center">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Calculating options...</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="badge bg-success">Lower Payments</span>
                                    <span class="badge bg-info">Extended Term</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="mt-4">
                    <div class="mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Any specific requirements or comments about this restructuring..."></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
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

    @endif

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
                <li>A 3% restructuring fee will be added to your outstanding balance</li>
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
    const restructuringFeeRate = 3; // 3%
    const restructuringFee = (totalOutstanding * restructuringFeeRate) / 100;
    const newLoanAmount = totalOutstanding + restructuringFee;
    
    // Update fee display
    document.getElementById('restructuringFee').textContent = 'KSh ' + restructuringFee.toLocaleString();
    document.getElementById('newLoanAmount').textContent = 'KSh ' + newLoanAmount.toLocaleString();
    
    // Option selection handlers
    const reduceRadio = document.getElementById('reduceDuration');
    const increaseRadio = document.getElementById('increaseDuration');
    const submitButton = document.getElementById('submitRestructuring');
    const customDurationSection = document.getElementById('customDurationSection');
    const newDurationInput = document.getElementById('newDurationInput');
    
    // Load initial calculations
    loadRestructuringOptions();
    
    // Event listeners
    reduceRadio.addEventListener('change', function() {
        if (this.checked) {
            customDurationSection.style.display = 'none';
            submitButton.disabled = false;
            highlightSelectedOption('reduce');
        }
    });
    
    increaseRadio.addEventListener('change', function() {
        if (this.checked) {
            customDurationSection.style.display = 'block';
            submitButton.disabled = false;
            highlightSelectedOption('increase');
        }
    });
    
    // Custom duration input
    newDurationInput.addEventListener('input', function() {
        if (increaseRadio.checked) {
            loadRestructuringOptions();
        }
    });
    
    // Form submission
    document.getElementById('restructuringForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showConfirmationModal();
    });
    
    // Confirmation checkbox
    document.getElementById('confirmCheckbox').addEventListener('change', function() {
        document.getElementById('finalConfirmBtn').disabled = !this.checked;
    });
    
    // Final confirmation
    document.getElementById('finalConfirmBtn').addEventListener('click', function() {
        processRestructuring();
    });
    
    function loadRestructuringOptions() {
        const params = new URLSearchParams({
            agreement_id: agreementId
        });
        
        if (increaseRadio.checked && newDurationInput.value) {
            params.append('restructuring_type', 'increase_duration');
            params.append('new_duration', newDurationInput.value);
        }
        
        fetch(`{{ route('loan-restructuring.calculate-options') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateOptionDetails(data.options);
                } else {
                    console.error('Error loading options:', data.error);
                }
            })
            .catch(error => {
                console.error('Network error:', error);
            });
    }
    
    function updateOptionDetails(options) {
        // Update reduce duration option
        if (options.reduce_duration) {
            const option = options.reduce_duration;
            document.getElementById('reduceDurationDetails').innerHTML = `
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="text-primary mb-1">New Payment</h6>
                            <h5 class="mb-0">KSh ${option.new_payment.toLocaleString()}</h5>
                            <small class="text-success">+KSh ${option.payment_increase.toLocaleString()}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="text-primary mb-1">Duration Saved</h6>
                        <h5 class="mb-0">${option.duration_reduction} months</h5>
                        <small class="text-muted">${option.new_duration} months total</small>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <p class="mb-1"><strong>Interest Saved:</strong> 
                       <span class="text-success">KSh ${option.total_interest_saved.toLocaleString()}</span></p>
                    <small class="text-muted">${option.description}</small>
                </div>
            `;
        }
        
        // Update increase duration option
        if (options.increase_duration) {
            const option = options.increase_duration;
            document.getElementById('increaseDurationDetails').innerHTML = `
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="text-success mb-1">New Payment</h6>
                            <h5 class="mb-0">KSh ${option.new_payment.toLocaleString()}</h5>
                            <small class="text-success">-KSh ${option.payment_reduction.toLocaleString()}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="text-success mb-1">Extended By</h6>
                        <h5 class="mb-0">${option.duration_increase} months</h5>
                        <small class="text-muted">${option.new_duration} months total</small>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <p class="mb-1"><strong>Additional Interest:</strong> 
                       <span class="text-warning">KSh ${option.additional_interest.toLocaleString()}</span></p>
                    <small class="text-muted">${option.description}</small>
                </div>
            `;
        }
    }
    
    function highlightSelectedOption(type) {
        const reduceCard = document.getElementById('reduceDurationCard');
        const increaseCard = document.getElementById('increaseDurationCard');
        
        if (type === 'reduce') {
            reduceCard.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            increaseCard.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
        } else {
            increaseCard.classList.add('border-success', 'bg-success', 'bg-opacity-10');
            reduceCard.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        }
    }
    
    function showConfirmationModal() {
        const formData = new FormData(document.getElementById('restructuringForm'));
        const restructuringType = formData.get('restructuring_type');
        
        if (!restructuringType) {
            alert('Please select a restructuring option');
            return;
        }
        
        // Get current option details
        let details = '';
        if (restructuringType === 'reduce_duration') {
            details = document.getElementById('reduceDurationDetails').innerHTML;
        } else {
            details = document.getElementById('increaseDurationDetails').innerHTML;
        }
        
        document.getElementById('confirmationDetails').innerHTML = `
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">${restructuringType === 'reduce_duration' ? 'Reduce Duration' : 'Increase Duration'} Option</h6>
                </div>
                <div class="card-body">
                    ${details}
                </div>
            </div>
            <div class="mt-3">
                <p><strong>Restructuring Fee:</strong> KSh ${restructuringFee.toLocaleString()} (3%)</p>
                <p><strong>New Loan Amount:</strong> KSh ${newLoanAmount.toLocaleString()}</p>
            </div>
        `;
        
        new bootstrap.Modal(document.getElementById('confirmRestructuringModal')).show();
    }
    
    function processRestructuring() {
        const formData = new FormData(document.getElementById('restructuringForm'));
        
        // Show loading
        document.getElementById('finalConfirmBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
        document.getElementById('finalConfirmBtn').disabled = true;
        
        fetch('{{ route("loan-restructuring.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Loan restructured successfully!');
                window.location.href = '{{ route("hire-purchase.show", $agreement->id) }}';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred. Please try again.');
        })
        .finally(() => {
            document.getElementById('finalConfirmBtn').innerHTML = '<i class="fas fa-sync-alt me-1"></i>Confirm Restructuring';
            document.getElementById('finalConfirmBtn').disabled = false;
        });
    }
});
</script>

<style>
.option-details {
    min-height: 150px;
}

.card.border-primary.bg-primary.bg-opacity-10 {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
    transition: all 0.3s ease;
}

.card.border-success.bg-success.bg-opacity-10 {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(25, 135, 84, 0.2);
    transition: all 0.3s ease;
}

.card {
    transition: all 0.3s ease;
}

.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.badge {
    font-size: 0.75rem;
}
</style>

</x-app-layout>