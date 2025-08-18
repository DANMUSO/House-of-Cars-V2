<x-app-layout>
<div class="container-fluid">
    <!-- SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Fleet Acquisition
            </button>

            <!-- Add Modal -->
            <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="addModalLabel">Add Fleet Acquisition Details</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="FleetAcquisitionForm" enctype="multipart/form-data">
                                @csrf
                                
                                <!-- Vehicle Information Section -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-car"></i> Vehicle Information
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-4">
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
                                    
                                    <div class="col-md-8">
                                        <label class="form-label">Vehicle Photos</label>
                                        <input type="file" class="form-control" name="vehicle_photos[]" multiple accept="image/*">
                                        <small class="text-muted">You can select multiple images. Max 2MB each.</small>
                                    </div>
                                </div>

                                <!-- Financial Details Section -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-money-bill-wave"></i> Financial Details
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-4">
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
                                </div>

                                <!-- Legal & Compliance Section -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-gavel"></i> Legal & Compliance
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-4">
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
                                </div>

                                <!-- Financier Information Section -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-university"></i> Financier Information
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mb-4">
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
                                </div>

                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Submit
                                        </button>
                                    </div>
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
                        <table id="fleetTable" class="table table-bordered table-hover nowrap w-100">
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
                                            <a href="{{ route('fleetacquisition.manage', $fleet->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-cog"></i> Manage
                                            </a>
                                        </td>
                                        <td>
                                            <strong>{{ $fleet->vehicle_make }} {{ $fleet->vehicle_model }}</strong><br>
                                            <small class="text-muted">{{ $fleet->vehicle_year }} | {{ $fleet->engine_capacity }}</small><br>
                                            <small class="text-info">{{ $fleet->chassis_number }}</small>
                                        </td>
                                        <td>{{ $fleet->chassis_number }}</td>
                                        <td>{{ number_format($fleet->purchase_price, 2) }}</td>
                                        <td>{{ number_format($fleet->down_payment, 2) }}</td>
                                        <td>{{ number_format($fleet->monthly_installment, 2) }}</td>
                                        <td>{{ number_format($fleet->outstanding_balance, 2) }}</td>
                                        <td>
                                            @php
                                                $paidPercentage = 0;
                                                if ($fleet->total_amount_payable > 0) {
                                                    $paidPercentage = ($fleet->amount_paid / $fleet->total_amount_payable) * 100;
                                                }
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $paidPercentage }}%"
                                                     aria-valuenow="{{ $paidPercentage }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    {{ number_format($paidPercentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
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
                                            <div class="btn-group-vertical" role="group">
                                                @if ($fleet->status == 'pending')
                                                    <button class="btn btn-sm btn-success approve-fleet-btn mb-1" 
                                                            data-id="{{ $fleet->id }}">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                       
                                                <button class="btn btn-sm btn-danger delete-fleet-btn" 
                                                        data-id="{{ $fleet->id }}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                                @endif
                                                
                                                <button class="btn btn-sm btn-warning edit-fleet-btn mb-1"
                                                        data-id="{{ $fleet->id }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                
                                             
                                            </div>
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

    <!-- Photo View Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoModalLabel">Vehicle Photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="photoCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="carouselInner">
                            <!-- Photos will be loaded here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#photoCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#photoCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Update Fleet Acquisition Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <form id="updateFleetForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="editRecordId">

                        <!-- Vehicle Information Section -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-car"></i> Vehicle Information
                                </h6>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="editVehicleMake" class="form-label">Vehicle Make</label>
                                <input type="text" class="form-control" name="vehicle_make" id="editVehicleMake" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editVehicleModel" class="form-label">Vehicle Model</label>
                                <input type="text" class="form-control" name="vehicle_model" id="editVehicleModel" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editVehicleYear" class="form-label">Year</label>
                                <input type="number" class="form-control" name="vehicle_year" id="editVehicleYear" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editEngineCapacity" class="form-label">Engine Capacity</label>
                                <input type="text" class="form-control" name="engine_capacity" id="editEngineCapacity" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editVehicleCategory" class="form-label">Category</label>
                                <select class="form-select" name="vehicle_category" id="editVehicleCategory" required>
                                    <option value="commercial">Commercial</option>
                                    <option value="passenger">Passenger</option>
                                    <option value="utility">Utility</option>
                                    <option value="special_purpose">Special Purpose</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editChassisNumber" class="form-label">Chassis Number</label>
                                <input type="text" class="form-control" name="chassis_number" id="editChassisNumber" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editEngineNumber" class="form-label">Engine Number</label>
                                <input type="text" class="form-control" name="engine_number" id="editEngineNumber" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editRegistrationNumber" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" name="registration_number" id="editRegistrationNumber">
                            </div>
                            <div class="col-md-4">
                                <label for="editPurchasePrice" class="form-label">Purchase Price (Ksh)</label>
                                <input type="number" class="form-control" name="purchase_price" id="editPurchasePrice" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editMarketValue" class="form-label">Market Value (Ksh)</label>
                                <input type="number" class="form-control" name="market_value" id="editMarketValue" step="0.01" required>
                            </div>
                        </div>

                        <!-- Current Photos Display -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Current Photos</label>
                                <div id="currentPhotos" class="d-flex flex-wrap gap-2 mb-2">
                                    <!-- Current photos will be displayed here -->
                                </div>
                            </div>
                        </div>

                        <!-- New Photos Upload -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="form-label">Add New Photos</label>
                                <input type="file" class="form-control" name="vehicle_photos[]" multiple accept="image/*">
                                <small class="text-muted">Select new images to add. Max 2MB each.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="replace_photos" value="1" id="replacePhotos">
                                    <label class="form-check-label" for="replacePhotos">
                                        Replace all existing photos
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Legal & Compliance Section -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-gavel"></i> Legal & Compliance
                                </h6>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="editHpAgreementNumber" class="form-label">HP Agreement Number</label>
                                <input type="text" class="form-control" name="hp_agreement_number" id="editHpAgreementNumber" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editLogbookCustody" class="form-label">Logbook Custody</label>
                                <select class="form-select" name="logbook_custody" id="editLogbookCustody" required>
                                    <option value="financier">Financier</option>
                                    <option value="company">Company</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editCompanyKraPin" class="form-label">Company KRA PIN</label>
                                <input type="text" class="form-control" name="company_kra_pin" id="editCompanyKraPin" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editInsurancePolicyNumber" class="form-label">Insurance Policy Number</label>
                                <input type="text" class="form-control" name="insurance_policy_number" id="editInsurancePolicyNumber">
                            </div>
                            <div class="col-md-4">
                                <label for="editInsuranceCompany" class="form-label">Insurance Company</label>
                                <input type="text" class="form-control" name="insurance_company" id="editInsuranceCompany">
                            </div>
                            <div class="col-md-4">
                                <label for="editInsuranceExpiryDate" class="form-label">Insurance Expiry Date</label>
                                <input type="date" class="form-control" name="insurance_expiry_date" id="editInsuranceExpiryDate">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editBusinessPermitNumber" class="form-label">Business Permit Number</label>
                                <input type="text" class="form-control" name="business_permit_number" id="editBusinessPermitNumber">
                            </div>
                            <div class="col-md-6">
                                <label for="editInsurancePremium" class="form-label">Insurance Premium (Ksh)</label>
                                <input type="number" class="form-control" name="insurance_premium" id="editInsurancePremium" step="0.01">
                            </div>
                        </div>

                        <!-- Financier Information Section -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-university"></i> Financier Information
                                </h6>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="editFinancingInstitution" class="form-label">Financing Institution</label>
                                <input type="text" class="form-control" name="financing_institution" id="editFinancingInstitution" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editFinancierContactPerson" class="form-label">Financier Contact Person</label>
                                <input type="text" class="form-control" name="financier_contact_person" id="editFinancierContactPerson">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editFinancierPhone" class="form-label">Financier Phone</label>
                                <input type="text" class="form-control" name="financier_phone" id="editFinancierPhone">
                            </div>
                            <div class="col-md-4">
                                <label for="editFinancierEmail" class="form-label">Financier Email</label>
                                <input type="email" class="form-control" name="financier_email" id="editFinancierEmail">
                            </div>
                            <div class="col-md-4">
                                <label for="editFinancierAgreementRef" class="form-label">Financier Agreement Ref</label>
                                <input type="text" class="form-control" name="financier_agreement_ref" id="editFinancierAgreementRef">
                            </div>
                        </div>

                        <!-- Financial Details Section -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-money-bill-wave"></i> Financial Details
                                </h6>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="editDownPayment" class="form-label">Down Payment (Ksh)</label>
                                <input type="number" class="form-control" name="down_payment" id="editDownPayment" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editInterestRate" class="form-label">Interest Rate (%)</label>
                                <input type="number" class="form-control" name="interest_rate" id="editInterestRate" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editLoanDuration" class="form-label">Loan Duration (Months)</label>
                                <select class="form-select" name="loan_duration_months" id="editLoanDuration" required>
                                    @for ($i = 6; $i <= 120; $i += 6)
                                        <option value="{{ $i }}">{{ $i }} Months</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editFirstPaymentDate" class="form-label">First Payment Date</label>
                                <input type="date" class="form-control" name="first_payment_date" id="editFirstPaymentDate" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Record
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <form id="recordPaymentForm">
                        @csrf
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

                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Record Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Fleet Acquisition Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Vehicle Information Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fa-solid fa-car"></i> Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>Make & Model:</strong></td><td id="viewVehicleMakeModel"></td></tr>
                                        <tr><td><strong>Year:</strong></td><td id="viewVehicleYear"></td></tr>
                                        <tr><td><strong>Engine Capacity:</strong></td><td id="viewEngineCapacity"></td></tr>
                                        <tr><td><strong>Category:</strong></td><td id="viewVehicleCategory"></td></tr>
                                        <tr><td><strong>Chassis Number:</strong></td><td id="viewChassisNumber"></td></tr>
                                        <tr><td><strong>Engine Number:</strong></td><td id="viewEngineNumber"></td></tr>
                                        <tr><td><strong>Registration:</strong></td><td id="viewRegistrationNumber"></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fa-solid fa-money-bill-wave"></i> Financial Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>Purchase Price:</strong></td><td id="viewPurchasePrice"></td></tr>
                                        <tr><td><strong>Down Payment:</strong></td><td id="viewDownPayment"></td></tr>
                                        <tr><td><strong>Monthly Installment:</strong></td><td id="viewMonthlyInstallment"></td></tr>
                                        <tr><td><strong>Total Amount:</strong></td><td id="viewTotalAmount"></td></tr>
                                        <tr><td><strong>Amount Paid:</strong></td><td id="viewAmountPaid"></td></tr>
                                        <tr><td><strong>Outstanding Balance:</strong></td><td id="viewOutstandingBalance"></td></tr>
                                        <tr><td><strong>Interest Rate:</strong></td><td id="viewInterestRate"></td></tr>
                                        <tr><td><strong>Duration:</strong></td><td id="viewDuration"></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Progress -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fa-solid fa-chart-line"></i> Status & Progress</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Status:</strong> <span id="viewStatus" class="badge"></span></p>
                                    <p><strong>Payment Progress:</strong></p>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div id="viewProgressBar" class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small id="viewPaymentsMade" class="text-muted"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Legal & Compliance -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fa-solid fa-gavel"></i> Legal & Compliance</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>HP Agreement:</strong></td><td id="viewHpAgreement"></td></tr>
                                        <tr><td><strong>Logbook Custody:</strong></td><td id="viewLogbookCustody"></td></tr>
                                        <tr><td><strong>Company KRA PIN:</strong></td><td id="viewCompanyKraPin"></td></tr>
                                        <tr><td><strong>Insurance Policy:</strong></td><td id="viewInsurancePolicy"></td></tr>
                                        <tr><td><strong>Insurance Company:</strong></td><td id="viewInsuranceCompany"></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Financier Information -->
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fa-solid fa-university"></i> Financier Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tr><td><strong>Institution:</strong></td><td id="viewFinancingInstitution"></td></tr>
                                                <tr><td><strong>Contact Person:</strong></td><td id="viewFinancierContact"></td></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tr><td><strong>Phone:</strong></td><td id="viewFinancierPhone"></td></tr>
                                                <tr><td><strong>Email:</strong></td><td id="viewFinancierEmail"></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Photos -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fa-solid fa-camera"></i> Vehicle Photos</h6>
                                </div>
                                <div class="card-body">
                                    <div id="viewVehiclePhotos" class="row">
                                        <!-- Photos will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> <!-- container-fluid -->
</x-app-layout>