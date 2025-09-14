@extends('layouts.app')

@section('title', 'Gentleman Agreement Restructuring')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Loan Restructuring - Gentleman Agreement</h1>
                    <p class="text-muted">No Interest • Simple Payment Plan • Formula: Outstanding Balance × Fee Rate + Penalties ÷ Duration</p>
                </div>
                <div>
                    <a href="{{ route('gentlemanagreement.show', $agreement->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Agreement
                    </a>
                </div>
            </div>

            <!-- Agreement Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-handshake me-2"></i>Agreement Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Client:</strong><br>
                            {{ $agreement->client_name }}<br>
                            <small class="text-muted">{{ $agreement->phone_number }}</small>
                        </div>
                        <div class="col-md-3">
                            <strong>Vehicle:</strong><br>
                            {{ $agreement->vehicle_make }} {{ $agreement->vehicle_model }}
                            @if($agreement->vehicle_year)
                                <br><small class="text-muted">{{ $agreement->vehicle_year }}</small>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Current Payment:</strong><br>
                            KSh {{ number_format($agreement->monthly_payment, 2) }}<br>
                            <small class="text-muted">No Interest</small>
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            <span class="badge badge-{{ $agreement->status === 'active' ? 'success' : ($agreement->status === 'completed' ? 'primary' : 'warning') }}">
                                {{ ucfirst($agreement->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Current Financial Position
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">KSh {{ number_format($financialSummary['outstanding_balance'], 2) }}</h4>
                                <small class="text-muted">Outstanding Balance</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">KSh {{ number_format($financialSummary['total_penalties'], 2) }}</h4>
                                <small class="text-muted">Penalties</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger">KSh {{ number_format($financialSummary['total_outstanding'], 2) }}</h4>
                                <small class="text-muted">Total Outstanding</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">{{ $currentRemainingMonths }}</h4>
                                <small class="text-muted">Remaining Payments</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formula Display -->
                    <div class="row mt-3 pt-3 border-top">
                        <div class="col-md-12">
                            <div class="alert alert-light">
                                <h6><i class="fas fa-calculator me-2"></i>Restructuring Formula (No Interest):</h6>
                                <p class="mb-1"><strong>Outstanding Balance × Fee Rate = Restructuring Fee</strong></p>
                                <p class="mb-1"><strong>New Loan Amount = Outstanding Balance + Restructuring Fee + Penalties</strong></p>
                                <p class="mb-0"><strong>New Monthly Payment = New Loan Amount ÷ Admin-Selected Duration</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Restructuring Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Restructuring Options
                    </h5>
                </div>
                <div class="card-body">
                    <form id="restructuringForm">
                        @csrf
                        <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">New Duration (months) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="new_duration" min="3" max="120" required>
                                    <small class="text-muted">Enter the new payment duration in months</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Restructuring Fee Rate (%)</label>
                                    <input type="number" class="form-control" name="fee_rate" step="0.1" min="0" max="20" value="3.0">
                                    <small class="text-muted">Default: 3.0%</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Restructuring Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="restructuring_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Reason for restructuring..."></textarea>
                        </div>
                        
                        <!-- Calculation Preview -->
                        <div id="calculationPreview" class="alert alert-light d-none">
                            <h6>Calculation Preview:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Outstanding Balance:</strong> KSh <span id="previewOutstanding">{{ number_format($financialSummary['outstanding_balance'], 2) }}</span></p>
                                    <p><strong>Restructuring Fee:</strong> KSh <span id="previewFee">-</span></p>
                                    <p><strong>Penalties:</strong> KSh <span id="previewPenalties">{{ number_format($financialSummary['total_penalties'], 2) }}</span></p>
                                    <p><strong>New Loan Amount:</strong> KSh <span id="previewNewAmount">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>New Duration:</strong> <span id="previewDuration">-</span> months</p>
                                    <p><strong>New Monthly Payment:</strong> KSh <span id="previewPayment">-</span></p>
                                    <p><strong>Payment Change:</strong> <span id="previewChange">-</span></p>
                                    <p><strong>Interest Rate:</strong> <span class="text-success">0% (Gentleman Agreement)</span></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="calculatePreview()">
                                <i class="fas fa-calculator me-2"></i>Calculate Preview
                            </button>
                            <button type="submit" class="btn btn-primary" disabled>
                                <i class="fas fa-check me-2"></i>Apply Restructuring
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Important Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-heart text-success me-2"></i>Gentleman Agreement Benefits</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>No interest charges</li>
                                <li><i class="fas fa-check text-success me-2"></i>Simple payment calculations</li>
                                <li><i class="fas fa-check text-success me-2"></i>Flexible restructuring options</li>
                                <li><i class="fas fa-check text-success me-2"></i>Fair and transparent terms</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>Restructuring Terms</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-arrow-right text-muted me-2"></i>A restructuring fee will be applied</li>
                                <li><i class="fas fa-arrow-right text-muted me-2"></i>All outstanding amounts will be included</li>
                                <li><i class="fas fa-arrow-right text-muted me-2"></i>Penalties will be added to the balance</li>
                                <li><i class="fas fa-arrow-right text-muted me-2"></i>New payment schedule starts immediately</li>
                                <li><i class="fas fa-arrow-right text-muted me-2"></i>SMS confirmation will be sent</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Processing restructuring...</p>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Restructuring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> This action will permanently modify the loan terms and cannot be undone.
                </div>
                
                <div id="confirmationDetails">
                    <!-- Details will be populated by JavaScript -->
                </div>
                
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmCheck">
                    <label class="form-check-label" for="confirmCheck">
                        I understand and confirm this restructuring
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmRestructuring" disabled>
                    <i class="fas fa-check me-2"></i>Confirm Restructuring
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Global variables
    let currentCalculation = null;
    
    // Agreement data from server
    const agreementData = {
        id: {{ $agreement->id }},
        currentPayment: {{ $agreement->monthly_payment }},
        remainingMonths: {{ $currentRemainingMonths }},
        outstandingBalance: {{ $financialSummary['outstanding_balance'] }},
        penalties: {{ $financialSummary['total_penalties'] }},
        totalOutstanding: {{ $financialSummary['total_outstanding'] }},
        noInterest: true
    };
    
    console.log('Gentleman Agreement Data:', agreementData);
    
    // Calculate preview
    window.calculatePreview = function() {
        const formData = new FormData(document.querySelector('#restructuringForm'));
        
        const newDuration = parseInt(formData.get('new_duration'));
        const feeRate = parseFloat(formData.get('fee_rate') || 3.0);
        
        if (!newDuration || newDuration < 3) {
            showError('Please enter a valid duration (minimum 3 months)');
            return;
        }
        
        // Apply exact formula: Outstanding Balance × Fee Rate = Restructuring Fee
        const outstandingBalance = agreementData.outstandingBalance;
        const restructuringFee = (outstandingBalance * feeRate) / 100;
        
        // New Loan Amount = Outstanding Balance + Restructuring Fee + Penalties
        const newLoanAmount = outstandingBalance + restructuringFee + agreementData.penalties;
        
        // New Monthly Payment = New Loan Amount ÷ Admin-Selected Duration
        const newMonthlyPayment = newLoanAmount / newDuration;
        
        const paymentChange = newMonthlyPayment - agreementData.currentPayment;
        
        // Update preview
        $('#previewFee').text(number_format(restructuringFee, 2));
        $('#previewNewAmount').text(number_format(newLoanAmount, 2));
        $('#previewDuration').text(newDuration);
        $('#previewPayment').text(number_format(newMonthlyPayment, 2));
        
        const changeText = paymentChange >= 0 ? 
            '+KSh ' + number_format(Math.abs(paymentChange), 2) + ' (increase)' :
            '-KSh ' + number_format(Math.abs(paymentChange), 2) + ' (decrease)';
        $('#previewChange').text(changeText);
        
        $('#calculationPreview').removeClass('d-none');
        $('#restructuringForm button[type="submit"]').prop('disabled', false);
        
        currentCalculation = {
            newLoanAmount: newLoanAmount,
            newMonthlyPayment: newMonthlyPayment,
            newDuration: newDuration,
            restructuringFee: restructuringFee,
            feeRate: feeRate,
            paymentChange: paymentChange
        };
    };
    
    // Form submission
    $('#restructuringForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentCalculation) {
            showError('Please calculate the preview first');
            return;
        }
        
        showConfirmationModal();
    });
    
    // Show confirmation modal
    function showConfirmationModal() {
        const details = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Current Payment:</strong></td>
                        <td>KSh ${number_format(agreementData.currentPayment, 2)}</td>
                    </tr>
                    <tr>
                        <td><strong>New Payment:</strong></td>
                        <td>KSh ${number_format(currentCalculation.newMonthlyPayment, 2)}</td>
                    </tr>
                    <tr>
                        <td><strong>Current Duration:</strong></td>
                        <td>${agreementData.remainingMonths} months</td>
                    </tr>
                    <tr>
                        <td><strong>New Duration:</strong></td>
                        <td>${currentCalculation.newDuration} months</td>
                    </tr>
                    <tr>
                        <td><strong>Outstanding Balance:</strong></td>
                        <td>KSh ${number_format(agreementData.outstandingBalance, 2)}</td>
                    </tr>
                    <tr>
                        <td><strong>Restructuring Fee (${currentCalculation.feeRate}%):</strong></td>
                        <td>KSh ${number_format(currentCalculation.restructuringFee, 2)}</td>
                    </tr>
                    <tr>
                        <td><strong>Penalties:</strong></td>
                        <td>KSh ${number_format(agreementData.penalties, 2)}</td>
                    </tr>
                    <tr class="table-info">
                        <td><strong>New Loan Amount:</strong></td>
                        <td>KSh ${number_format(currentCalculation.newLoanAmount, 2)}</td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Agreement Type:</strong></td>
                        <td>Gentleman Agreement (No Interest)</td>
                    </tr>
                </table>
            </div>
        `;
        
        $('#confirmationDetails').html(details);
        $('#confirmationModal').modal('show');
    }
    
    // Confirmation checkbox
    $('#confirmCheck').on('change', function() {
        $('#confirmRestructuring').prop('disabled', !this.checked);
    });
    
    // Confirm restructuring
    $('#confirmRestructuring').on('click', function() {
        processRestructuring();
    });
    
    // Process restructuring
    function processRestructuring() {
        const formData = new FormData(document.querySelector('#restructuringForm'));
        
        // Show loading
        $('#confirmationModal').modal('hide');
        $('#loadingModal').modal('show');
        
        $.ajax({
            url: '{{ route("gentleman-loan-restructuring.process") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                if (response.success) {
                    showSuccessMessage(response.message, response.restructuring_details);
                    
                    // Redirect after 3 seconds
                    setTimeout(function() {
                        window.location.href = '{{ route("gentlemanagreement.show", $agreement->id) }}';
                    }, 3000);
                } else {
                    showError(response.message || 'Restructuring failed');
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                console.error('Restructuring error:', xhr);
                
                const error = xhr.responseJSON?.message || 'Failed to process restructuring';
                showError(error);
            }
        });
    }
    
    // Success message
    function showSuccessMessage(message, details) {
        const successHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> ${message}
                <br><br>
                <small><strong>New Payment:</strong> KSh ${number_format(details.new_payment, 2)} | 
                <strong>Duration:</strong> ${details.new_duration} months | 
                <strong>Type:</strong> Gentleman Agreement (No Interest)</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').prepend(successHtml);
        $('html, body').animate({ scrollTop: 0 }, 500);
    }
    
    // Error message
    function showError(message) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').prepend(errorHtml);
        $('html, body').animate({ scrollTop: 0 }, 500);
    }
    
    // Utility function for number formatting
    function number_format(number, decimals) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }
    
    // Reset confirmation modal when closed
    $('#confirmationModal').on('hidden.bs.modal', function() {
        $('#confirmCheck').prop('checked', false);
        $('#confirmRestructuring').prop('disabled', true);
    });
    
    // Auto-calculate when duration or fee rate changes
    $('input[name="new_duration"], input[name="fee_rate"]').on('change', function() {
        if ($('input[name="new_duration"]').val()) {
            calculatePreview();
        }
    });
});
</script>
@endsection