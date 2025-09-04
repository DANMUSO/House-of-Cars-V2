<x-app-layout>
<div class="container-fluid">
    <!-- Header Section -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Gentleman's Agreement Management</h4>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#calculatorModal">
                <i class="fas fa-calculator"></i> Payment Calculator
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAgreementModal">
                <i class="fas fa-plus"></i> Add New Agreement
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-18">
                                    <i class="fas fa-handshake"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Agreements</p>
                            <h5 class="mb-0">{{ $gentlemanAgreements->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-soft-success text-success rounded-circle fs-18">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Portfolio</p>
                            <h5 class="mb-0">KSh {{ number_format($gentlemanAgreements->sum('total_amount'), 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-soft-info text-info rounded-circle fs-18">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Outstanding Balance</p>
                            <h5 class="mb-0">KSh {{ number_format($gentlemanAgreements->sum('outstanding_balance'), 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-soft-warning text-warning rounded-circle fs-18">
                                    <i class="fas fa-percentage"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Avg. Completion</p>
                            <h5 class="mb-0">{{ number_format($gentlemanAgreements->avg('payment_progress'), 1) }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Calculator Modal -->
    <div class="modal fade" id="calculatorModal" tabindex="-1" aria-labelledby="calculatorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calculatorModalLabel">Gentleman's Agreement Calculator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">Simple Payment Calculator</h6>
                            <small>No interest charges - Gentleman's agreement basis</small>
                        </div>
                        <div class="card-body">
                            <form id="paymentCalculator">
                                <div class="mb-3">
                                    <label class="form-label">Vehicle Price (KSh)</label>
                                    <input type="number" class="form-control" id="calc_vehicle_price" placeholder="e.g., 2,500,000">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deposit Amount (KSh)</label>
                                    <input type="number" class="form-control" id="calc_deposit" placeholder="Any amount (recommended minimum 20%)">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Period (Months)</label>
                                    <select class="form-select" id="calc_duration">
                                        <option value="">Select Duration</option>
                                        @for ($i = 1; $i <= 60; $i += 1)
                                            <option value="{{ $i }}">{{ $i }} Months</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary w-100" onclick="calculatePayment()">
                                    <i class="fas fa-calculator"></i> Calculate Monthly Payment
                                </button>
                            </form>
                            <div id="calculatorResults" class="mt-3" style="display: none;">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-handshake"></i> Gentleman's Agreement Results:</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Balance to Pay:</strong><br>KSh <span id="calc_balance"></span></p>
                                            <p><strong>Monthly Payment:</strong><br>KSh <span id="calc_monthly_payment"></span></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Deposit Percentage:</strong><br><span id="calc_deposit_percentage"></span>%</p>
                                            <p><strong>Payment Duration:</strong><br><span id="calc_payment_period"></span> months</p>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mb-0">
                                        <small><i class="fas fa-info-circle"></i> <strong>Note:</strong> This is a gentleman's agreement with no interest charges or additional fees.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Agreement Modal -->
    <div class="modal fade" id="addAgreementModal" tabindex="-1" aria-labelledby="addAgreementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAgreementModalLabel">Create New Gentleman's Agreement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="gentlemanAgreementForm" class="row g-3">
                        @csrf
                        
                        <!-- Client Information Section -->
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user"></i> Client Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="client_name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" name="phone_number" required>
                        </div>
                         <div class="col-md-6">
                            <label class="form-label">Alternative Phone Number (Optional)</label>
                            <input type="tel" class="form-control" name="phone_numberalt">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alternative Email Address (Optional)</label>
                            <input type="email" class="form-control" name="emailalt">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">National ID *</label>
                            <input type="text" class="form-control" name="national_id" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">KRA PIN</label>
                            <input type="text" class="form-control" name="kra_pin">
                        </div>

                        <!-- Vehicle Information Section -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-car"></i> Vehicle Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Selection *</label>
                            <select class="form-select" name="vehicle_id" id="vehicle_select" required>
                                <option value="">Choose Vehicle</option>
                                
                                @if($cars->whereNotNull('carsImport')->count() > 0)
                                    <optgroup label="Imported Vehicles ({{ $cars->whereNotNull('carsImport')->count() }})">
                                        @foreach ($cars->whereNotNull('carsImport') as $inspection)
                                            @if($inspection->carsImport)
                                                @php
                                                    $import = $inspection->carsImport;
                                                    $price = $import->selling_price ?? $import->purchase_price ?? $import->price ?? 0;
                                                @endphp
                                                <option value="import-{{ $import->id }}" 
                                                        data-price="{{ $price }}"
                                                        data-make="{{ $import->make ?? 'Unknown' }}"
                                                        data-model="{{ $import->model ?? 'Unknown' }}"
                                                        data-year="{{ $import->year ?? '' }}">
                                                    {{ $import->make ?? 'Unknown' }} {{ $import->model ?? 'Unknown' }} 
                                                    @if($import->year) ({{ $import->year }}) @endif
                                                    @if($price > 0) - KSh {{ number_format($price) }} @endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endif
                                
                                @if($cars->whereNotNull('customerVehicle')->count() > 0)
                                    <optgroup label="Trade-In Vehicles ({{ $cars->whereNotNull('customerVehicle')->count() }})">
                                        @foreach ($cars->whereNotNull('customerVehicle') as $inspection)
                                            @if($inspection->customerVehicle)
                                                @php
                                                    $customer = $inspection->customerVehicle;
                                                    $price = $customer->agreed_selling_price ?? $customer->selling_price ?? $customer->evaluated_price ?? $customer->price ?? 0;
                                                @endphp
                                                  <option value="customer-{{ $customer->id }}" 
                                                        data-price="{{ $price }}"
                                                        data-make="{{ $customer->vehicle_make ?? 'Unknown' }}"
                                                        data-model="{{ $customer->vehicle_model ?? '' }}"
                                                        data-plate="{{ $customer->number_plate ?? '' }}"
                                                        data-variant="{{ $customer->model ?? '' }}">
                                                    {{ $customer->vehicle_make ?? 'Unknown' }}
                                                    @if($customer->vehicle_model) - {{ $customer->vehicle_model }} @endif
                                                    @if($customer->model) - {{ $customer->model }} @endif
                                                    @if($customer->number_plate) ({{ $customer->number_plate }}) @endif
                                                    @if($price > 0) - KSh {{ number_format($price) }} @endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endif
                                
                                @if($cars->count() == 0)
                                    <option disabled>No vehicles available</option>
                                @elseif($cars->whereNotNull('carsImport')->count() == 0 && $cars->whereNotNull('customerVehicle')->count() == 0)
                                    <option disabled>No vehicles with proper relationships found</option>
                                @endif
                            </select>
                            
                            <small class="text-muted">
                                Total: {{ $cars->count() }} | 
                                Imports: {{ $cars->whereNotNull('carsImport')->count() }} | 
                                Trade-ins: {{ $cars->whereNotNull('customerVehicle')->count() }}
                            </small>
                        </div>

                        <!-- Financial Information Section -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-money-bill"></i> Payment Details
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Price (KSh) *</label>
                            <input type="number" class="form-control" name="vehicle_price" id="vehicle_price" required>
                            <small class="text-muted">Price will auto-fill when you select a vehicle</small>
                        </div>
                        <div class="col-md-6">
                                <label class="form-label">Deposit Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="PaidAmount" name="PaidAmount" placeholder="0.00" required>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <label class="form-label">Trade Inn Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="TradeInnAmount" name="TradeInnAmount" placeholder="0.00">
                                </div>
                            </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Paid Amount (KSh) *</label>
                            <input type="number" class="form-control" name="deposit_amount" id="deposit_amount" required>
                            <small class="form-text text-muted">Any amount (recommended minimum 20%)</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Payment Duration *</label>
                            <select name="duration_months" id="duration_months" class="form-select" required>
                                <option value="">Choose Duration</option>
                                @for ($i = 1; $i <= 60; $i += 1)
                                    <option value="{{ $i }}">{{ $i }} Months</option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Monthly Payment (KSh)</label>
                            <input type="number" class="form-control" id="monthly_payment_display" readonly>
                            <small class="form-text text-muted">Auto-calculated: (Price - Deposit) ÷ Duration</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">First Payment Due Date *</label>
                            <input type="date" class="form-control" name="first_due_date" required>
                        </div>

                        <!-- Agreement Summary -->
                        <div class="col-12 mt-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-handshake"></i> Gentleman's Agreement Summary
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Balance to Pay</small>
                                            <div class="fw-bold" id="summary_balance">KSh 0</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Monthly Payment</small>
                                            <div class="fw-bold" id="summary_monthly">KSh 0</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Total Payable</small>
                                            <div class="fw-bold" id="summary_total_payable">KSh 0</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Deposit Percentage</small>
                                            <div class="fw-bold" id="summary_deposit_percentage">0%</div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-2 mb-0">
                                        <small><i class="fas fa-info-circle"></i> <strong>Note:</strong> No interest or additional fees - this is a gentleman's agreement based on trust.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-handshake"></i> Create Agreement
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-handshake"></i> Gentleman's Agreements
                    </h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="defaulted">Defaulted</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="gentlemanAgreementTable" class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Client Details</th>
                                    <th width="15%">Payment Summary</th>
                                    <th width="10%">Status</th>
                                    <th width="25%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gentlemanAgreements as $index => $agreement)
                                    <tr data-status="{{ strtolower($agreement->status) }}">
                                        <td>
                                            <div class="fw-bold">{{ $index + 1 }}</div>
                                            <small class="text-muted">ID: {{ $agreement->id }}</small>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                        {{ substr($agreement->client_name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $agreement->client_name }}</div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone"></i> {{ $agreement->phone_number }}<br>
                                                        <i class="fas fa-envelope"></i> {{ $agreement->email }}
                                                    </small><br>
                                                     <small class="text-muted">
                                                       {{ $agreement->phone_numberalt }}<br>
                                                      {{ $agreement->emailalt }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="small">
                                                <div class="mb-1">
                                                    <span class="text-muted">Price:</span> 
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->vehicle_price ?? 0) }}</span>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="text-muted">Deposit:</span> 
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->deposit_amount ?? 0) }}</span>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="text-muted">Balance:</span> 
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->outstanding_balance ?? 0) }}</span>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="text-muted">Monthly:</span> 
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->monthly_payment ?? 0) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Duration:</span> 
                                                    <span class="fw-semibold">{{ $agreement->duration_months ?? 0 }}m</span>
                                                </div>
                                                <div class="mt-1">
                                                    <span class="badge bg-success">No Interest</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['class' => 'bg-warning', 'icon' => 'fas fa-clock', 'text' => 'Pending'],
                                                    'active' => ['class' => 'bg-success', 'icon' => 'fas fa-check-circle', 'text' => 'Active'],
                                                    'completed' => ['class' => 'bg-primary', 'icon' => 'fas fa-flag-checkered', 'text' => 'Completed'],
                                                    'defaulted' => ['class' => 'bg-danger', 'icon' => 'fas fa-exclamation-triangle', 'text' => 'Defaulted']
                                                ];
                                                $status = $statusConfig[strtolower($agreement->status)] ?? $statusConfig['pending'];
                                            @endphp
                                            <span class="badge {{ $status['class'] }}">
                                                <i class="{{ $status['icon'] }}"></i> {{ $status['text'] }}
                                            </span>
                                            <div class="mt-1">
                                                <small class="text-muted">{{ $agreement->created_at->format('M d, Y') }}</small>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <a href="{{ route('gentlemanagreement.show', $agreement->id) }}" 
                                                   class="btn btn-outline-primary mb-1">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                
                                                @if(strtolower($agreement->status) === 'pending')
                                                    <button class="btn btn-outline-success mb-1" 
                                                            onclick="activateAgreement({{ $agreement->id }})">
                                                        <i class="fas fa-check"></i> Activate
                                                    </button>
                                                    
                                                    <button class="btn btn-outline-danger mb-1" 
                                                            onclick="deleteAgreement({{ $agreement->id }})">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                @endif
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

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        @csrf
                        <input type="hidden" id="payment_agreement_id" name="agreement_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Amount (KSh) *</label>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" required>
                            <small class="form-text text-muted">Suggested monthly payment: KSh <span id="suggested_payment">0</span></small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
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
                            <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="Transaction/Receipt Number">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Record Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Agreement Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Agreement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAgreementForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_agreement_id" name="agreement_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Client Name</label>
                                <input type="text" class="form-control" id="edit_client_name" name="client_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="edit_phone_number" name="phone_number" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">National ID</label>
                                <input type="text" class="form-control" id="edit_national_id" name="national_id" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Deposit Amount (KSh)</label>
                                <input type="number" class="form-control" id="edit_deposit_amount" name="deposit_amount" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duration (Months)</label>
                                <select class="form-select" id="edit_duration_months" name="duration_months" required>
                                    @for ($i = 3; $i <= 60; $i += 3)
                                        <option value="{{ $i }}">{{ $i }} Months</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Agreement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div> <!-- container-fluid -->
</x-app-layout>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Calculator functionality
function calculatePayment() {
    const vehiclePrice = parseFloat(document.getElementById('calc_vehicle_price').value) || 0;
    const deposit = parseFloat(document.getElementById('calc_deposit').value) || 0;
    const duration = parseInt(document.getElementById('calc_duration').value) || 0;
    
    if (vehiclePrice <= 0 || deposit < 0 || duration <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Please enter valid values for all fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    if (deposit >= vehiclePrice) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Deposit',
            text: 'Deposit cannot be equal to or greater than vehicle price',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    const balance = vehiclePrice - deposit;
    const monthlyPayment = balance / duration;
    const depositPercentage = (deposit / vehiclePrice) * 100;
    
    // Display results
    document.getElementById('calc_balance').textContent = balance.toLocaleString();
    document.getElementById('calc_monthly_payment').textContent = monthlyPayment.toLocaleString();
    document.getElementById('calc_deposit_percentage').textContent = depositPercentage.toFixed(1);
    document.getElementById('calc_payment_period').textContent = duration;
    
    document.getElementById('calculatorResults').style.display = 'block';
}

// Form calculation functionality
function calculateFormValues() {
    const vehiclePrice = parseFloat(document.getElementById('vehicle_price').value) || 0;
    const deposit = parseFloat(document.getElementById('deposit_amount').value) || 0;
    const duration = parseInt(document.getElementById('duration_months').value) || 0;
    
    if (vehiclePrice > 0 && deposit >= 0 && duration > 0) {
        const balance = vehiclePrice - deposit;
        const monthlyPayment = balance / duration;
        const depositPercentage = vehiclePrice > 0 ? (deposit / vehiclePrice) * 100 : 0;
        
        // Update form fields
        document.getElementById('monthly_payment_display').value = monthlyPayment.toFixed(2);
        
        // Update summary
        document.getElementById('summary_balance').textContent = 'KSh ' + balance.toLocaleString();
        document.getElementById('summary_monthly').textContent = 'KSh ' + monthlyPayment.toLocaleString();
        document.getElementById('summary_total_payable').textContent = 'KSh ' + vehiclePrice.toLocaleString();
        document.getElementById('summary_deposit_percentage').textContent = depositPercentage.toFixed(1) + '%';
    }
}

// Validation function to check required fields
function validateForm(formData) {
    const errors = [];
    
    // Required fields
    const requiredFields = {
        'client_name': 'Client Name',
        'phone_number': 'Phone Number',
        'email': 'Email Address',
        'national_id': 'National ID',
        'vehicle_id': 'Vehicle Selection',
        'vehicle_price': 'Vehicle Price',
        'deposit_amount': 'Deposit Amount',
        'duration_months': 'Payment Duration',
        'first_due_date': 'First Payment Due Date'
    };
    
    for (const [field, label] of Object.entries(requiredFields)) {
        const value = formData.get(field);
        if (!value || value.trim() === '') {
            errors.push(label + ' is required');
        }
    }
    
    // Additional validations
    const vehiclePrice = parseFloat(formData.get('vehicle_price')) || 0;
    const deposit = parseFloat(formData.get('deposit_amount')) || 0;
    
    if (vehiclePrice <= 0) {
        errors.push('Vehicle price must be greater than 0');
    }
    
    if (deposit <= 0) {
        errors.push('Deposit amount must be greater than 0');
    }
    
    if (deposit >= vehiclePrice) {
        errors.push('Deposit cannot be equal to or greater than vehicle price');
    }
    
    const depositPercentage = vehiclePrice > 0 ? (deposit / vehiclePrice) * 100 : 0;
    if (depositPercentage < 1) {
        errors.push('Minimum recommended deposit is 5% of vehicle price');
    }
    
    return errors;
}

// Event listeners for form fields
document.addEventListener('DOMContentLoaded', function() {
    // Vehicle selection handler
    document.getElementById('vehicle_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        if (price) {
            document.getElementById('vehicle_price').value = price;
            calculateFormValues();
        }
    });
    
    // Form field listeners
    ['vehicle_price', 'deposit_amount', 'duration_months'].forEach(id => {
        document.getElementById(id).addEventListener('input', calculateFormValues);
    });
    
    // Status filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        const filterValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#gentlemanAgreementTable tbody tr');
        
        rows.forEach(row => {
            if (filterValue === '' || row.getAttribute('data-status') === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Agreement management functions with SweetAlert
function activateAgreement(agreementId) {
    Swal.fire({
        title: 'Activate Agreement?',
        text: 'Are you sure you want to activate this gentleman\'s agreement?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Activate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/gentlemanagreement/${agreementId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Activated!',
                        text: 'Agreement has been activated successfully.',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error activating agreement: ' + data.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while activating the agreement',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function deleteAgreement(agreementId) {
    Swal.fire({
        title: 'Delete Agreement?',
        text: 'Are you sure you want to delete this agreement? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/gentlemanagreement/${agreementId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Agreement has been deleted successfully.',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error deleting agreement: ' + data.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while deleting the agreement',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function recordPayment(agreementId, suggestedAmount) {
    document.getElementById('payment_agreement_id').value = agreementId;
    document.getElementById('suggested_payment').textContent = suggestedAmount.toLocaleString();
    document.getElementById('payment_amount').value = suggestedAmount;
    
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();
}

// Enhanced form submission with better error handling
document.getElementById('gentlemanAgreementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Validate form
    const errors = validateForm(formData);
    
    if (errors.length > 0) {
        let errorMessage = 'Please fix the following issues:\n\n';
        errors.forEach((error, index) => {
            errorMessage += `${index + 1}. ${error}\n`;
        });
        
        Swal.fire({
            icon: 'error',
            title: 'Form Validation Failed',
            text: errorMessage,
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    // Show loading
    Swal.fire({
        title: 'Creating Agreement...',
        text: 'Please wait while we process your request',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('/gentlemanagreement', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Gentleman\'s agreement created successfully!',
                confirmButtonColor: '#28a745'
            }).then(() => {
                location.reload();
            });
        } else {
            // Enhanced error handling
            handleFormErrors(data);
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to connect to the server. Please check your internet connection and try again.',
            confirmButtonColor: '#dc3545'
        });
    });
});
// Auto-calculate Total Paid Amount and make it readonly
function initializeTotalPaidCalculation() {
    const depositInput = document.getElementById('PaidAmount');
    const tradeInnInput = document.getElementById('TradeInnAmount');
    const totalPaidInput = document.getElementById('deposit_amount');
    
    // Make total paid amount readonly
    if (totalPaidInput) {
        totalPaidInput.readOnly = true;
        totalPaidInput.classList.add('bg-light');
    }
    
    // Calculate total paid amount
    function calculateTotalPaid() {
        const deposit = parseFloat(depositInput?.value) || 0;
        const tradeInn = parseFloat(tradeInnInput?.value) || 0;
        const total = deposit + tradeInn;
        
        if (totalPaidInput) {
            totalPaidInput.value = total.toFixed(2);
        }
        
        // Trigger the existing agreement summary calculation
        if (typeof calculateAgreementSummary === 'function') {
            calculateAgreementSummary();
        }
        
        console.log('Total Paid Amount calculated:', { deposit, tradeInn, total });
    }
    
    // Add event listeners
    if (depositInput) {
        depositInput.addEventListener('input', calculateTotalPaid);
    }
    
    if (tradeInnInput) {
        tradeInnInput.addEventListener('input', calculateTotalPaid);
    }
    
    // Initial calculation
    calculateTotalPaid();
}

// Add this to your existing DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
    // ... your existing code ...
    initializeTotalPaidCalculation();
});
// Enhanced error handling function that works with your controller's validation responses
function handleFormErrors(data) {
    let errorTitle = 'Error Creating Agreement';
    let errorMessage = '';
    let errorIcon = 'error';
    
    // Handle controller validation errors (422 status)
    if (data.errors) {
        errorTitle = 'Form Validation Failed';
        errorMessage = 'Please fix the following issues:\n\n';
        let errorCount = 1;
        
        Object.entries(data.errors).forEach(([field, fieldErrors]) => {
            fieldErrors.forEach(error => {
                errorMessage += `${errorCount}. ${error}\n`;
                errorCount++;
            });
        });
        errorIcon = 'warning';
        
    // Handle specific controller validation responses
    } else if (data.message) {
        // Check for specific validation messages from your controller
        if (data.message.includes('Minimum recommended deposit is 5%')) {
            errorTitle = 'Insufficient Deposit';
            errorMessage = 'The deposit amount is too low. Please ensure the deposit is at least 5% of the vehicle price.';
            errorIcon = 'warning';
            
        } else if (data.message.includes('Selected vehicle not found')) {
            errorTitle = 'Invalid Vehicle Selection';
            errorMessage = 'The selected vehicle could not be found. Please choose a different vehicle from the list.';
            errorIcon = 'warning';
            
        } else if (data.message.includes('Please check the form data')) {
            errorTitle = 'Form Data Error';
            errorMessage = 'There are issues with the form data. Please review all fields and try again.';
            errorIcon = 'warning';
            
        } else if (data.message.includes('An unexpected error occurred')) {
            errorTitle = 'System Error';
            errorMessage = 'A system error occurred while processing your request. Please try again or contact support if the problem persists.';
            
        } else {
            errorMessage = data.message;
        }
    }
    
    // Handle database constraint violations (like duplicate entries)
    if (data.error_details) {
        if (data.error_details.includes('Duplicate entry')) {
            if (data.error_details.includes('national_id_unique') || data.error_details.includes('national_id')) {
                errorTitle = 'Duplicate National ID';
                errorMessage = 'This National ID is already registered in the system. Please check if an agreement already exists for this client or verify the National ID number.';
                errorIcon = 'warning';
                
            } else if (data.error_details.includes('email_unique') || data.error_details.includes('email')) {
                errorTitle = 'Duplicate Email Address';
                errorMessage = 'This email address is already registered. Please use a different email address or check if an agreement already exists for this client.';
                errorIcon = 'warning';
                
            } else if (data.error_details.includes('phone_number_unique') || data.error_details.includes('phone_number')) {
                errorTitle = 'Duplicate Phone Number';
                errorMessage = 'This phone number is already registered. Please use a different phone number or check if an agreement already exists for this client.';
                errorIcon = 'warning';
                
            } else {
                errorTitle = 'Duplicate Information';
                errorMessage = 'Some of the information provided already exists in the system. Please check your entries for duplicates.';
                errorIcon = 'warning';
            }
        } else if (data.error_details.includes('SQLSTATE') || data.error_details.includes('SQL')) {
            errorTitle = 'Database Error';
            errorMessage = 'A database error occurred. This might be due to invalid data or system constraints. Please verify your information and try again.';
        }
    }
    
    // Fallback for empty messages
    if (!errorMessage) {
        errorMessage = 'An unexpected error occurred while creating the agreement. Please try again or contact support.';
    }
    
    // Show the error with appropriate styling
    Swal.fire({
        icon: errorIcon,
        title: errorTitle,
        text: errorMessage,
        confirmButtonColor: errorIcon === 'warning' ? '#f39c12' : '#dc3545',
        confirmButtonText: 'Understood',
        allowOutsideClick: false,
        footer: data.error_details && data.error_details.includes('SQLSTATE') ? 
            '<small class="text-muted">Technical Details: Database constraint violation detected</small>' : null
    });
}

// Enhanced payment form error handling
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Basic validation for payment form
    const paymentAmount = formData.get('payment_amount');
    const paymentMethod = formData.get('payment_method');
    
    if (!paymentAmount || paymentAmount <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Payment Amount',
            text: 'Please enter a valid payment amount greater than 0',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    if (!paymentMethod) {
        Swal.fire({
            icon: 'error',
            title: 'Payment Method Required',
            text: 'Please select a payment method',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    // Show loading
    Swal.fire({
        title: 'Recording Payment...',
        text: 'Please wait while we process your payment',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('/gentlemanagreement/payment', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Payment Recorded!',
                text: 'Payment has been recorded successfully',
                confirmButtonColor: '#28a745'
            }).then(() => {
                // Close the modal and reload the page
                const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                paymentModal.hide();
                location.reload();
            });
        } else {
            // Handle payment errors with the same enhanced error handling
            handlePaymentErrors(data);
        }
    })
    .catch(error => {
        console.error('Payment submission error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to connect to the server. Please check your internet connection and try again.',
            confirmButtonColor: '#dc3545'
        });
    });
});

// Enhanced payment error handling function that works with your controller
function handlePaymentErrors(data) {
    let errorTitle = 'Payment Error';
    let errorMessage = '';
    let errorIcon = 'error';
    
    // Handle controller validation errors first
    if (data.errors) {
        errorTitle = 'Payment Validation Failed';
        errorMessage = 'Please fix the following issues:\n\n';
        let errorCount = 1;
        
        Object.entries(data.errors).forEach(([field, fieldErrors]) => {
            fieldErrors.forEach(error => {
                errorMessage += `${errorCount}. ${error}\n`;
                errorCount++;
            });
        });
        errorIcon = 'warning';
        
    // Handle specific payment error messages from your controller
    } else if (data.message) {
        if (data.message.includes('Cannot add payment to a completed agreement')) {
            errorTitle = 'Agreement Already Completed';
            errorMessage = 'This agreement has already been completed. No additional payments can be recorded.';
            errorIcon = 'info';
            
        } else if (data.message.includes('Payment amount cannot exceed outstanding balance')) {
            errorTitle = 'Payment Amount Too High';
            errorMessage = data.message; // This already contains the outstanding balance info
            errorIcon = 'warning';
            
        } else if (data.message.includes('Please check your input')) {
            errorTitle = 'Invalid Payment Data';
            errorMessage = 'There are issues with the payment information. Please verify all fields and try again.';
            errorIcon = 'warning';
            
        } else if (data.message.includes('An error occurred:')) {
            errorTitle = 'Payment Processing Error';
            errorMessage = data.message;
            
        } else {
            errorMessage = data.message;
        }
    } else {
        errorMessage = 'An unexpected error occurred while recording the payment. Please try again.';
    }
    
    Swal.fire({
        icon: errorIcon,
        title: errorTitle,
        text: errorMessage,
        confirmButtonColor: errorIcon === 'warning' ? '#f39c12' : (errorIcon === 'info' ? '#3085d6' : '#dc3545'),
        confirmButtonText: 'Understood'
    });
}

// Enhanced validation function that matches your controller's validation rules
function validateForm(formData) {
    const errors = [];
    
    // Required fields validation (matches your controller's validation)
    const requiredFields = {
        'client_name': 'Client name',
        'phone_number': 'Phone number', 
        'email': 'Email address',
        'national_id': 'National ID',
        'vehicle_id': 'Vehicle selection',
        'vehicle_price': 'Vehicle price',
        'deposit_amount': 'Deposit amount',
        'duration_months': 'Payment duration',
        'first_due_date': 'First payment due date'
    };
    
    // Check required fields
    for (const [field, label] of Object.entries(requiredFields)) {
        const value = formData.get(field);
        if (!value || value.toString().trim() === '') {
            errors.push(`${label} is required`);
        }
    }
    
    // Numeric validations that match your controller
    const vehiclePrice = parseFloat(formData.get('vehicle_price')) || 0;
    const deposit = parseFloat(formData.get('deposit_amount')) || 0;
    const duration = parseInt(formData.get('duration_months')) || 0;
    
    // Vehicle price validation (min:1 in controller)
    if (vehiclePrice <= 0) {
        errors.push('Vehicle price must be greater than 0');
    }
    
    // Deposit validation (min:1 in controller)
    if (deposit <= 0) {
        errors.push('Deposit amount must be greater than 0');
    }
    
    // Prevent deposit >= vehicle price (logical validation)
    if (deposit >= vehiclePrice && vehiclePrice > 0) {
        errors.push('Deposit cannot be equal to or greater than vehicle price');
    }
    
    
    // Deposit percentage check (matches controller's 5% minimum recommendation)
    const depositPercentage = vehiclePrice > 0 ? (deposit / vehiclePrice) * 100 : 0;
    if (depositPercentage < 1 && vehiclePrice > 0 && deposit > 0) {
        errors.push('Minimum recommended deposit is 5% of vehicle price');
    }
    
    // Email format validation (matches controller's email validation)
    const email = formData.get('email');
    if (email && !isValidEmail(email)) {
        errors.push('Please enter a valid email address');
    }
    
    // National ID validation (matches controller's max:20)
    const nationalId = formData.get('national_id');
    if (nationalId && nationalId.length > 20) {
        errors.push('National ID cannot exceed 20 characters');
    }
    
    // Phone number validation (matches controller's max:20)
    const phoneNumber = formData.get('phone_number');
    if (phoneNumber && phoneNumber.length > 20) {
        errors.push('Phone number cannot exceed 20 characters');
    }
    
    // Client name validation (matches controller's max:100)
    const clientName = formData.get('client_name');
    if (clientName && clientName.length > 100) {
        errors.push('Client name cannot exceed 100 characters');
    }
    
    // KRA PIN validation (matches controller's max:20)
    const kraPin = formData.get('kra_pin');
    if (kraPin && kraPin.length > 20) {
        errors.push('KRA PIN cannot exceed 20 characters');
    }
    
    return errors;
}

// Helper function for email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Helper function for phone number validation (Kenyan format)
function isValidPhoneNumber(phone) {
    const phoneRegex = /^(\+254|0)(7|1)\d{8}$/;
    return phoneRegex.test(phone.replace(/\s+/g, ''));
}
</script>