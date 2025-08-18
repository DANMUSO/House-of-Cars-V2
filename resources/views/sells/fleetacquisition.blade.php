<x-app-layout>
<div class="container-fluid">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Fleet Acquisition</h4>
        </div>
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0"></h4>
        </div>
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0"></h4>
        </div>
        <div class="flex-grow-1">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                Add Fleet Acquisition
            </button>

            <!-- Add Modal -->
            <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="standard-modalLabel">Add Fleet Acquisition Details</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="FleetAcquisitionForm" class="row g-3">
                                @csrf
                                
                                <!-- Vehicle Information Section -->
                                <div class="col-12">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Vehicle Information</h6>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Vehicle Make *</label>
                                    <input type="text" class="form-control" name="vehicle_make" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Vehicle Model *</label>
                                    <input type="text" class="form-control" name="vehicle_model" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Year *</label>
                                    <input type="number" class="form-control" name="vehicle_year" min="1900" max="{{ date('Y')+1 }}" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Engine Capacity *</label>
                                    <input type="text" class="form-control" name="engine_capacity" placeholder="e.g., 2.0L" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Chassis Number *</label>
                                    <input type="text" class="form-control" name="chassis_number" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Engine Number *</label>
                                    <input type="text" class="form-control" name="engine_number" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" class="form-control" name="registration_number" placeholder="KXX XXXZ">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Vehicle Category *</label>
                                    <select class="form-select" name="vehicle_category" required>
                                        <option value="">Choose Category</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="passenger">Passenger</option>
                                        <option value="utility">Utility</option>
                                        <option value="special_purpose">Special Purpose</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Purchase Price (Ksh) *</label>
                                    <input type="number" class="form-control" name="purchase_price" step="0.01" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Market Value (Ksh) *</label>
                                    <input type="number" class="form-control" name="market_value" step="0.01" required>
                                </div>

                                <!-- Financial Details Section -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Financial Details</h6>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Down Payment (Ksh) *</label>
                                    <input type="number" class="form-control" name="down_payment" step="0.01" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Interest Rate (%) *</label>
                                    <input type="number" class="form-control" name="interest_rate" step="0.01" min="0" max="100" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Loan Duration (Months) *</label>
                                    <select class="form-select" name="loan_duration_months" required>
                                        <option value="">Choose Duration</option>
                                        @for ($i = 6; $i <= 120; $i += 6)
                                            <option value="{{ $i }}">{{ $i }} Months</option>
                                        @endfor
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">First Payment Date *</label>
                                    <input type="date" class="form-control" name="first_payment_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Insurance Premium (Ksh)</label>
                                    <input type="number" class="form-control" name="insurance_premium" step="0.01">
                                </div>

                                <!-- Legal & Compliance Section -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Legal & Compliance</h6>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">HP Agreement Number *</label>
                                    <input type="text" class="form-control" name="hp_agreement_number" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Logbook Custody *</label>
                                    <select class="form-select" name="logbook_custody" required>
                                        <option value="">Choose</option>
                                        <option value="financier">Financier</option>
                                        <option value="company">Company</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Company KRA PIN *</label>
                                    <input type="text" class="form-control" name="company_kra_pin" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Insurance Policy Number</label>
                                    <input type="text" class="form-control" name="insurance_policy_number">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Insurance Company</label>
                                    <input type="text" class="form-control" name="insurance_company">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Insurance Expiry Date</label>
                                    <input type="date" class="form-control" name="insurance_expiry_date">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Business Permit Number</label>
                                    <input type="text" class="form-control" name="business_permit_number">
                                </div>

                                <!-- Vendor/Financier Information Section -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Financier Information</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Financing Institution *</label>
                                    <input type="text" class="form-control" name="financing_institution" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Financier Contact Person</label>
                                    <input type="text" class="form-control" name="financier_contact_person">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Financier Phone</label>
                                    <input type="text" class="form-control" name="financier_phone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Financier Email</label>
                                    <input type="email" class="form-control" name="financier_email">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Financier Agreement Ref</label>
                                    <input type="text" class="form-control" name="financier_agreement_ref">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive"> 
                        <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vehicle Details</th>
                                    <th>Chassis Number</th>
                                    <th>Purchase Price (Ksh)</th>
                                    <th>Down Payment (Ksh)</th>
                                    <th>Monthly Installment (Ksh)</th>
                                    <th>Outstanding Balance (Ksh)</th>
                                    <th>Paid Percentage (%)</th>
                                    <th>Duration (Months)</th>
                                    <th>Financing Institution</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fleetAcquisitions as $index => $fleet)
                                    <tr>
                                        <td>{{ $index + 1 }}
                                            <a href="{{ route('fleet_acquisition.show', $fleet->id) }}">View</a>
                                        </td>
                                        <td>{{ $fleet->vehicle_full_name }}</td>
                                        <td>{{ $fleet->chassis_number }}</td>
                                        <td>{{ number_format($fleet->purchase_price, 2) }}</td>
                                        <td>{{ number_format($fleet->down_payment, 2) }}</td>
                                        <td>{{ number_format($fleet->monthly_installment, 2) }}</td>
                                        <td>{{ number_format($fleet->outstanding_balance, 2) }}</td>
                                        <td>{{ number_format($fleet->paid_percentage, 2) }}%</td>
                                        <td>{{ $fleet->loan_duration_months }}</td>
                                        <td>{{ $fleet->financing_institution }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($fleet->status == 'pending') bg-warning
                                                @elseif($fleet->status == 'approved') bg-success
                                                @elseif($fleet->status == 'active') bg-primary
                                                @elseif($fleet->status == 'completed') bg-info
                                                @else bg-danger
                                                @endif">
                                                {{ ucfirst($fleet->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $fleet->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            @if ($fleet->status == 'pending')
                                                <button class="btn btn-sm btn-success approveFleetBtn" data-id="{{ $fleet->id }}">
                                                    Approve
                                                </button>
                                                <br><br>
                                            @endif
                                            
                                            <button class="btn btn-sm btn-warning editFleetBtn"
                                                data-id="{{ $fleet->id }}">
                                                Edit
                                            </button>
                                            <br><br>
                                            
                                            @if($fleet->status == 'active' || $fleet->status == 'approved')
                                                <button class="btn btn-sm btn-info recordPaymentBtn" data-id="{{ $fleet->id }}">
                                                    Record Payment
                                                </button>
                                                <br><br>
                                            @endif
                                            
                                            <button class="btn btn-sm btn-danger deleteFleetBtn" data-id="{{ $fleet->id }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="updateFleetForm">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Update Fleet Acquisition Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editVehicleMake" class="form-label">Vehicle Make</label>
                                <input type="text" class="form-control" name="vehicle_make" id="editVehicleMake" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editVehicleModel" class="form-label">Vehicle Model</label>
                                <input type="text" class="form-control" name="vehicle_model" id="editVehicleModel" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editPurchasePrice" class="form-label">Purchase Price (Ksh)</label>
                                <input type="number" class="form-control" name="purchase_price" id="editPurchasePrice" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editDownPayment" class="form-label">Down Payment (Ksh)</label>
                                <input type="number" class="form-control" name="down_payment" id="editDownPayment" step="0.01" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editInterestRate" class="form-label">Interest Rate (%)</label>
                                <input type="number" class="form-control" name="interest_rate" id="editInterestRate" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editFinancingInstitution" class="form-label">Financing Institution</label>
                                <input type="text" class="form-control" name="financing_institution" id="editFinancingInstitution" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Record</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="recordPaymentForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <input type="hidden" name="fleet_id" id="paymentFleetId">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="paymentAmount" class="form-label">Payment Amount (Ksh)</label>
                                <input type="number" class="form-control" name="payment_amount" id="paymentAmount" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="paymentDate" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" id="paymentDate" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" id="paymentMethod" required>
                                    <option value="">Choose Method</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="referenceNumber" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" name="reference_number" id="referenceNumber">
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div> <!-- container-fluid -->
</x-app-layout>

<script>
// JavaScript for handling CRUD operations
$(document).ready(function() {
    // Submit Fleet Acquisition Form
    $('#FleetAcquisitionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('fleet_acquisition.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    alert('Fleet acquisition added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = 'Validation errors:\n';
                for (let field in errors) {
                    errorMessage += '- ' + errors[field][0] + '\n';
                }
                alert(errorMessage);
            }
        });
    });

    // Approve Fleet Acquisition
    $('.approveFleetBtn').on('click', function() {
        let id = $(this).data('id');
        
        if(confirm('Are you sure you want to approve this fleet acquisition?')) {
            $.ajax({
                url: `/fleet-acquisition/${id}/approve`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        alert('Fleet acquisition approved successfully!');
                        location.reload();
                    }
                }
            });
        }
    });

    // Record Payment
    $('.recordPaymentBtn').on('click', function() {
        let id = $(this).data('id');
        $('#paymentFleetId').val(id);
        $('#paymentModal').modal('show');
    });

    $('#recordPaymentForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#paymentFleetId').val();
        
        $.ajax({
            url: `/fleet-acquisition/${id}/payment`,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    alert('Payment recorded successfully!');
                    $('#paymentModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error recording payment: ' + xhr.responseJSON.message);
            }
        });
    });

    // Delete Fleet Acquisition
    $('.deleteFleetBtn').on('click', function() {
        let id = $(this).data('id');
        
        if(confirm('Are you sure you want to delete this fleet acquisition record?')) {
            $.ajax({
                url: `/fleet-acquisition/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        alert('Fleet acquisition deleted successfully!');
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>