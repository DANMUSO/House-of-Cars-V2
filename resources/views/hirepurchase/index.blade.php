<x-app-layout>
<div class="container-fluid">
    <!-- Header Section -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Hire Purchase Management</h4>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#calculatorModal">
                <i class="fas fa-calculator"></i> Loan Calculator
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
                            <h5 class="mb-0">{{ $hirePurchases->count() }}</h5>
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
                            <h5 class="mb-0">KSh {{ number_format($hirePurchases->sum('total_amount'), 2) }}</h5>
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
                            <h5 class="mb-0">KSh {{ number_format($hirePurchases->sum('outstanding_balance'), 2) }}</h5>
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
                            <h5 class="mb-0">{{ number_format($hirePurchases->avg('payment_progress'), 1) }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Calculator Modal -->
    <div class="modal fade" id="calculatorModal" tabindex="-1" aria-labelledby="calculatorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calculatorModalLabel">Hire Purchase Calculator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Standard Calculator (Above 50% Deposit) -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="card-title mb-0">Standard Rate (Deposit ≥ 50%)</h6>
                                    <small>Lower interest rates for higher deposits</small>
                                </div>
                                <div class="card-body">
                                    <form id="standardCalculator">
                                        <div class="mb-3">
                                            <label class="form-label">Vehicle Price (KSh)</label>
                                            <input type="number" class="form-control" id="std_vehicle_price" placeholder="e.g., 2,500,000">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Deposit Amount (KSh)</label>
                                            <input type="number" class="form-control" id="std_deposit" placeholder="Minimum 50% of vehicle price">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Interest Rate (% per month)</label>
                                            <input type="number" class="form-control" id="std_interest_rate" step="0.01" placeholder="e.g., 4.29" required>
                                            <small class="form-text text-muted">Current standard rate: 4.29%</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Duration (Months)</label>
                                            <select class="form-select" id="std_duration">
                                                <option value="">Select Duration</option>
                                                @for ($i = 1; $i <= 60; $i += 1)
                                                    <option value="{{ $i }}">{{ $i }} Months</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-success w-100" onclick="calculateStandard()">
                                            <i class="fas fa-calculator"></i> Calculate Standard Rate
                                        </button>
                                    </form>
                                    <div id="standardResults" class="mt-3" style="display: none;">
                                        <div class="alert alert-success">
                                            <h6><i class="fas fa-chart-line"></i> Standard Rate Results:</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <p><strong>Loan Amount:</strong><br>KSh <span id="std_loan_amount"></span></p>
                                                </div>
                                                <div class="col-6">
                                                    <p><strong>Monthly Payment:</strong><br>KSh <span id="std_monthly_payment"></span></p>
                                                    <p><strong>Total Interest:</strong><br>KSh <span id="std_total_interest"></span></p>
                                                </div>
                                            </div>
                                            <p class="mb-0"><strong>Total Amount Payable:</strong> KSh <span id="std_total_amount"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Below 50% Deposit Calculator -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="card-title mb-0">Higher Rate (Deposit < 50%)</h6>
                                    <small>Higher interest rates for lower deposits</small>
                                </div>
                                <div class="card-body">
                                    <form id="belowCalculator">
                                        <div class="mb-3">
                                            <label class="form-label">Vehicle Price (KSh)</label>
                                            <input type="number" class="form-control" id="below_vehicle_price" placeholder="e.g., 2,500,000">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Deposit Amount (KSh)</label>
                                            <input type="number" class="form-control" id="below_deposit" placeholder="Minimum 30% of vehicle price">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Interest Rate (% per month)</label>
                                            <input type="number" class="form-control" id="below_interest_rate" step="0.01" placeholder="e.g., 4.50" required>
                                            <small class="form-text text-muted">Current higher rate: 4.50%</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Duration (Months)</label>
                                            <select class="form-select" id="below_duration">
                                                <option value="">Select Duration</option>
                                                @for ($i = 1; $i <= 90; $i += 1)
                                                    <option value="{{ $i }}">{{ $i }} Months</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-warning w-100" onclick="calculateBelow()">
                                            <i class="fas fa-calculator"></i> Calculate Higher Rate
                                        </button>
                                    </form>
                                    <div id="belowResults" class="mt-3" style="display: none;">
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-exclamation-triangle"></i> Higher Rate Results:</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <p><strong>Loan Amount:</strong><br>KSh <span id="below_loan_amount"></span></p>
                                                </div>
                                                <div class="col-6">
                                                    <p><strong>Monthly Payment:</strong><br>KSh <span id="below_monthly_payment"></span></p>
                                                    <p><strong>Total Interest:</strong><br>KSh <span id="below_total_interest"></span></p>
                                                </div>
                                            </div>
                                            <p class="mb-0"><strong>Total Amount Payable:</strong> KSh <span id="below_total_amount"></span></p>
                                            <small class="text-danger"><strong>Note:</strong> Higher interest rate applied for deposits below 50%</small>
                                        </div>
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
        <div class="modal-dialog modal-l">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAgreementModalLabel">Create New Hire Purchase Agreement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="hirePurchaseForm" class="row g-3">
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
                            <label class="form-label">Alternatice Phone Number (Optional)</label>
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
                                
                                {{-- Debug info --}}
                                {{-- Total available cars: {{ $cars->count() }} --}}
                                
                                @if($cars->whereNotNull('carsImport')->count() > 0)
                                    <optgroup label="Imported Vehicles ({{ $cars->whereNotNull('carsImport')->count() }})">
                                        @foreach ($cars->whereNotNull('carsImport') as $inspection)
                                            @if($inspection->carsImport)
                                                @php
                                                    $import = $inspection->carsImport;
                                                    // Try different price fields that might exist
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
                                                    // Try different price fields that might exist
                                                    $price = $customer->agreed_selling_price ?? $customer->selling_price ?? $customer->evaluated_price ?? $customer->price ?? 0;
                                                @endphp
                                                <option value="customer-{{ $customer->id }}" 
                                                        data-price="{{ $price }}"
                                                        data-make="{{ $customer->vehicle_make ?? 'Unknown' }}"
                                                        data-model="{{ $customer->vehicle_model ?? '' }}"
                                                        data-plate="{{ $customer->number_plate ?? '' }}">
                                                    {{ $customer->vehicle_make ?? 'Unknown' }}
                                                    @if($customer->vehicle_model) {{ $customer->vehicle_model }} @endif
                                                    @if($customer->number_plate) ({{ $customer->number_plate }}) @endif
                                                    @if($price > 0) - KSh {{ number_format($price) }} @endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endif
                                
                                {{-- If no vehicles found, show message --}}
                                @if($cars->count() == 0)
                                    <option disabled>No vehicles available for hire purchase</option>
                                @elseif($cars->whereNotNull('carsImport')->count() == 0 && $cars->whereNotNull('customerVehicle')->count() == 0)
                                    <option disabled>No vehicles with proper relationships found</option>
                                @endif
                            </select>
                            
                            {{-- Debug information (remove in production) --}}
                            <small class="text-muted">
                                Total: {{ $cars->count() }} | 
                                Imports: {{ $cars->whereNotNull('carsImport')->count() }} | 
                                Trade-ins: {{ $cars->whereNotNull('customerVehicle')->count() }}
                            </small>
                        </div>

                        <!-- Financial Information Section -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-money-bill"></i> Financial Details
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Price (KSh) *</label>
                            <input type="number" class="form-control" name="vehicle_price" id="vehicle_price" required>
                            <small class="text-muted">Price will auto-fill when you select a vehicle</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Deposit Amount (KSh) *</label>
                            <input type="number" class="form-control" name="deposit_amount" id="deposit_amount" required>
                            <small class="form-text text-muted">Minimum 30% of vehicle price</small>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <label class="form-label">Loan Duration *</label>
                            <select name="duration_months" id="duration_months" class="form-select" required>
                                <option value="">Choose Duration</option>
                                @for ($i = 1; $i <= 72; $i += 1)
                                    <option value="{{ $i }}">{{ $i }} Months</option>
                                @endfor
                            </select>
                        </div>
                         <div class="col-md-6">
                            <label class="form-label">Tracking Fees (KSh) *</label>
                            <input type="number" class="form-control" name="tracking_fees" id="tracking_fees" readonly>
                            
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Interest Rate (% per month)</label>
                            <input type="number" class="form-control" id="interest_rate" name="interest_rate" step="0.01" placeholder="e.g., 4.29">
                            <small class="form-text text-muted">Enter custom rate or leave blank for auto-calculation based on deposit</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">First Payment Due Date *</label>
                            <input type="date" class="form-control" name="first_due_date" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Monthly Payment (KSh)</label>
                            <input type="number" class="form-control" id="monthly_payment_display" readonly>
                        </div>

                        <!-- Agreement Summary -->
                        <div class="col-12 mt-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-file-contract"></i> Agreement Summary
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Loan Amount</small>
                                            <div class="fw-bold" id="summary_loan_amount">KSh 0</div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Total Interest</small>
                                            <div class="fw-bold" id="summary_total_interest">KSh 0</div>
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
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Create Agreement
                            </button>
                            <button type="submit" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
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
                        <i class="fas fa-table"></i> Hire Purchase Agreements
                    </h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="completed">Completed</option>
                            <option value="defaulted">Defaulted</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="hirePurchaseTable" class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Client Details</th>
                                    <th width="15%">Financial Summary</th>
                                    
                                    <th width="10%">Status</th>
                                    <th width="25%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hirePurchases as $index => $agreement)
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
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->vehicle_price) }}</span>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="text-muted">Deposit:</span> 
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->deposit_amount) }}</span>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="text-muted">Balance:</span> 
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->outstanding_balance) }}</span>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="text-muted">Monthly:</span> 
                                                    <span class="fw-semibold">KSh {{ number_format($agreement->monthly_payment) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Duration:</span> 
                                                    <span class="fw-semibold">{{ $agreement->duration_months }}m</span>
                                                </div>
                                                 <div>
                                                    <span class="text-muted">Duration:</span> 
                                                    <span class="fw-semibold">{{ $agreement->interest_rate }}%</span>
                                                </div>
                                            </div>
                                        </td>

                                       

                                        <td class="text-center">
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['class' => 'bg-warning', 'icon' => 'fas fa-clock', 'text' => 'Pending'],
                                                    'approved' => ['class' => 'bg-success', 'icon' => 'fas fa-check-circle', 'text' => 'Approved'],
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
                                                <a href="{{ route('hire-purchase.show', $agreement->id) }}" 
                                                   class="btn btn-outline-primary mb-1">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                
                                                
                                                @if(strtolower($agreement->status) === 'pending')
                                                    <button class="btn btn-outline-success mb-1" 
                                                            onclick="approveAgreement({{ $agreement->id }})">
                                                        <i class="fas fa-check"></i> Approve
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
                                    @for ($i = 6; $i <= 72; $i += 6)
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

<script>
// Updated Hire Purchase Management JavaScript - Auto-Calculated Tracking Fees
document.addEventListener('DOMContentLoaded', function() {
    console.log('Hire Purchase Management initialized');
    
    // Initialize all event listeners
    initializeVehicleSelection();
    initializeFormCalculations();
    initializeFormSubmission();
    initializeOtherHandlers();
    initializeDataTable();
});

// Constants
const COMMISSION_HOC = 10000; // KES 10,000
const COMMISSION_SALES = 15000; // KES 15,000

// CORRECTED: Interest rate calculations based on Excel
function getInterestRateByDeposit(depositPercentage, manualRate = null) {
    console.log(`Getting interest rate for deposit percentage: ${depositPercentage.toFixed(2)}%`);
    
    // If manual rate is provided and valid, use it
    if (manualRate !== null && manualRate > 0) {
        console.log(`Using manual rate: ${manualRate}%`);
        return manualRate;
    }
    
    // Default rates (fallback)
    if (depositPercentage >= 50) {
        console.log('Using standard rate: 4.29%');
        return 4.29;
    } else {
        console.log('Using higher rate: 4.50%');
        return 4.50;
    }
}

// NEW: Auto-calculate tracking fee based on loan amount and deposit percentage
function calculateTrackingFee(baseLoanAmount, depositPercentage) {
    let trackingFeeRate;
    
    if (depositPercentage >= 50) {
        trackingFeeRate = 0.05; // 5% for 50%+ deposit
    } else {
        trackingFeeRate = 0.04; // 4% for deposits below 50%
    }
    
    const trackingFee = baseLoanAmount * trackingFeeRate;
    console.log(`Auto-calculated tracking fee: ${trackingFee} (${trackingFeeRate * 100}% of ${baseLoanAmount})`);
    return trackingFee;
}

// UPDATED: Get tracking fee - now auto-calculated or from input
function getTrackingFee(baseLoanAmount, depositPercentage) {
    // Check if we should use auto-calculation or manual input
    const trackingFeeInput = document.getElementById('tracking_fees');
    const autoCalculate = document.getElementById('auto_calculate_tracking')?.checked !== false; // Default to true
    
    if (autoCalculate || !trackingFeeInput || trackingFeeInput.readOnly) {
        // Auto-calculate based on loan amount and deposit percentage
        const autoFee = calculateTrackingFee(baseLoanAmount, depositPercentage);
        
        // Update the input field to show the calculated value
        if (trackingFeeInput) {
            trackingFeeInput.value = autoFee.toFixed(2);
        }
        
        return autoFee;
    } else {
        // Use manual input
        const manualFee = parseFloat(trackingFeeInput.value) || 0;
        console.log('Using manual tracking fee:', manualFee);
        return manualFee;
    }
}

// UPDATED: Agreement summary calculation using auto-calculated tracking fee
function calculateAgreementSummary() {
    const vehiclePrice = parseFloat(document.getElementById('vehicle_price').value) || 0;
    const deposit = parseFloat(document.getElementById('deposit_amount').value) || 0;
    const duration = parseInt(document.getElementById('duration_months').value) || 0;
    const manualInterestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
    
    console.log('Calculating agreement summary:', { vehiclePrice, deposit, duration, manualInterestRate });
    
    if (vehiclePrice > 0 && deposit > 0 && duration > 0) {
        const depositPercentage = (deposit / vehiclePrice) * 100;
        
        // Base loan amount (vehicle price - deposit)
        const baseLoanAmount = vehiclePrice - deposit;
        
        // Auto-calculate tracking fee based on loan and deposit percentage
        const trackingFee = getTrackingFee(baseLoanAmount, depositPercentage);
        
        // Total loan amount includes auto-calculated tracking fee
        const totalLoanAmount = baseLoanAmount + trackingFee;
        
        // UPDATED: Use manual rate if provided, otherwise use default based on deposit
        const interestRate = manualInterestRate > 0 ? manualInterestRate : getInterestRateByDeposit(depositPercentage);
        
        // Calculate using Excel's reducing balance formula
        const monthlyRateDecimal = interestRate / 100;
        
        let monthlyPayment, totalInterest;
        
        if (monthlyRateDecimal === 0) {
            // If somehow interest rate is 0
            monthlyPayment = totalLoanAmount / duration;
            totalInterest = 0;
        } else {
            // Excel's PMT formula: PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
            const numerator = totalLoanAmount * (monthlyRateDecimal * Math.pow(1 + monthlyRateDecimal, duration));
            const denominator = Math.pow(1 + monthlyRateDecimal, duration) - 1;
            monthlyPayment = numerator / denominator;
            
            const totalPayment = monthlyPayment * duration;
            totalInterest = totalPayment - totalLoanAmount;
        }
        
        const totalPayable = vehiclePrice + trackingFee + totalInterest;
        
        console.log('Calculation results with dynamic interest rate:', {
            baseLoanAmount,
            totalLoanAmount,
            trackingFee,
            trackingFeeRate: depositPercentage >= 50 ? '5%' : '4%',
            interestRate,
            manualInterestRate,
            monthlyRateDecimal,
            monthlyPayment: monthlyPayment.toFixed(2),
            totalInterest: totalInterest.toFixed(2),
            totalPayable: totalPayable.toFixed(2),
            depositPercentage: depositPercentage.toFixed(1) + '%'
        });
        
        // Update form fields
        const interestRateInput = document.getElementById('interest_rate');
        const monthlyPaymentInput = document.getElementById('monthly_payment_display');
        
        // Only auto-fill interest rate if user hasn't entered a manual rate
        if (interestRateInput && manualInterestRate === 0) {
            interestRateInput.value = interestRate.toFixed(2);
        }
        if (monthlyPaymentInput) monthlyPaymentInput.value = monthlyPayment.toFixed(2);
        
        // Update summary
        updateSummaryDisplay(totalLoanAmount, totalInterest, totalPayable, depositPercentage, trackingFee);
        
    } else {
        resetSummaryDisplay();
        // Clear tracking fee when inputs are invalid
        const trackingFeeInput = document.getElementById('tracking_fees');
        if (trackingFeeInput) trackingFeeInput.value = '';
    }
}

// UPDATED: Calculator functions for modals using auto-calculated tracking fees
function calculateStandard() {
    const vehiclePrice = parseFloat(document.getElementById('std_vehicle_price').value) || 0;
    const deposit = parseFloat(document.getElementById('std_deposit').value) || 0;
    const duration = parseInt(document.getElementById('std_duration').value) || 0;
    const manualInterestRate = parseFloat(document.getElementById('std_interest_rate').value) || 0;
    
    if (vehiclePrice <= 0 || deposit <= 0 || duration <= 0 || manualInterestRate <= 0) {
        showAlert('error', 'Please fill in all fields with valid values');
        return;
    }
    
    const depositPercentage = (deposit / vehiclePrice) * 100;
    
    if (depositPercentage < 50) {
        showAlert('error', 'For standard calculation, deposit must be at least 50% of vehicle price');
        return;
    }
    
    const baseLoanAmount = vehiclePrice - deposit;
    const trackingFee = calculateTrackingFee(baseLoanAmount, depositPercentage);
    const totalLoanAmount = baseLoanAmount + trackingFee;
    const interestRate = manualInterestRate;
    
    // Rest of the calculation remains the same...
    const monthlyRateDecimal = interestRate / 100;
    const numerator = totalLoanAmount * (monthlyRateDecimal * Math.pow(1 + monthlyRateDecimal, duration));
    const denominator = Math.pow(1 + monthlyRateDecimal, duration) - 1;
    const monthlyPayment = numerator / denominator;
    
    const totalPayment = monthlyPayment * duration;
    const totalInterest = totalPayment - totalLoanAmount;
    const totalAmount = vehiclePrice + trackingFee + totalInterest;
    
    console.log('Standard calculation with dynamic interest rate:', {
        vehiclePrice, deposit, depositPercentage: depositPercentage.toFixed(1) + '%',
        baseLoanAmount, totalLoanAmount, trackingFee, 
        trackingFeeRate: '5%', interestRate: manualInterestRate, // UPDATED LOG
        monthlyPayment: monthlyPayment.toFixed(2),
        totalInterest: totalInterest.toFixed(2),
        totalAmount: totalAmount.toFixed(2)
    });
    
    // Display results (same as before)
    document.getElementById('std_loan_amount').innerHTML = 
        `${totalLoanAmount.toLocaleString()}<br><small class="text-muted">(Vehicle: ${baseLoanAmount.toLocaleString()} + Tracking: ${trackingFee.toLocaleString()} @5%)</small>`;
    document.getElementById('std_interest_rate').textContent = interestRate.toFixed(2);
    document.getElementById('std_monthly_payment').textContent = monthlyPayment.toLocaleString('en-US', { maximumFractionDigits: 0 });
    document.getElementById('std_total_interest').textContent = totalInterest.toLocaleString('en-US', { maximumFractionDigits: 0 });
    document.getElementById('std_total_amount').textContent = totalAmount.toLocaleString('en-US', { maximumFractionDigits: 0 });
    document.getElementById('standardResults').style.display = 'block';
}


function calculateBelow() {
    const vehiclePrice = parseFloat(document.getElementById('below_vehicle_price').value) || 0;
    const deposit = parseFloat(document.getElementById('below_deposit').value) || 0;
    const duration = parseInt(document.getElementById('below_duration').value) || 0;
    const manualInterestRate = parseFloat(document.getElementById('below_interest_rate').value) || 0;
    
    if (vehiclePrice <= 0 || deposit <= 0 || duration <= 0 || manualInterestRate <= 0) {
        showAlert('error', 'Please fill in all fields with valid values');
        return;
    }
    
    const depositPercentage = (deposit / vehiclePrice) * 100;
    
    if (depositPercentage < 30) {
        showAlert('error', 'Minimum deposit is 30% of vehicle price');
        return;
    }
    
    if (depositPercentage >= 50) {
        showAlert('error', 'For deposits 50% and above, use the Standard Rate calculator');
        return;
    }
    
    const baseLoanAmount = vehiclePrice - deposit;
    const trackingFee = calculateTrackingFee(baseLoanAmount, depositPercentage);
    const totalLoanAmount = baseLoanAmount + trackingFee;
    const interestRate = manualInterestRate; // Use the manual input directly
    
    // Calculate using Excel's reducing balance method
    const monthlyRateDecimal = interestRate / 100;
    const numerator = totalLoanAmount * (monthlyRateDecimal * Math.pow(1 + monthlyRateDecimal, duration));
    const denominator = Math.pow(1 + monthlyRateDecimal, duration) - 1;
    const monthlyPayment = numerator / denominator;
    
    const totalPayment = monthlyPayment * duration;
    const totalInterest = totalPayment - totalLoanAmount;
    const totalAmount = vehiclePrice + trackingFee + totalInterest;
    
    // Display results
    document.getElementById('below_loan_amount').innerHTML = 
        `${totalLoanAmount.toLocaleString()}<br><small class="text-muted">(Vehicle: ${baseLoanAmount.toLocaleString()} + Tracking: ${trackingFee.toLocaleString()} @4%)</small>`;
    document.getElementById('below_interest_rate').textContent = interestRate.toFixed(2);
    document.getElementById('below_monthly_payment').textContent = monthlyPayment.toLocaleString('en-US', { maximumFractionDigits: 0 });
    document.getElementById('below_total_interest').textContent = totalInterest.toLocaleString('en-US', { maximumFractionDigits: 0 });
    document.getElementById('below_total_amount').textContent = totalAmount.toLocaleString('en-US', { maximumFractionDigits: 0 });
    document.getElementById('belowResults').style.display = 'block';
}

// Vehicle selection initialization
function initializeVehicleSelection() {
    const vehicleSelect = document.getElementById('vehicle_select');
    const vehiclePriceInput = document.getElementById('vehicle_price');
    
    if (vehicleSelect && vehiclePriceInput) {
        vehicleSelect.addEventListener('change', function() {
            console.log('Vehicle selection changed:', this.value);
            
            if (this.value === '') {
                vehiclePriceInput.value = '';
                calculateAgreementSummary();
                return;
            }
            
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            
            console.log('Vehicle price:', price);
            
            // Set the vehicle price
            vehiclePriceInput.value = price;
            
            // Trigger calculation
            calculateAgreementSummary();
        });
    }
}

// UPDATED: Form calculations initialization to include auto-calculation toggle
function initializeFormCalculations() {
    const depositInput = document.getElementById('deposit_amount');
    const durationSelect = document.getElementById('duration_months');
    const vehiclePriceInput = document.getElementById('vehicle_price');
    const trackingFeesInput = document.getElementById('tracking_fees');
    const autoCalculateToggle = document.getElementById('auto_calculate_tracking');
    // NEW: Interest rate input listener
    const interestRateInput = document.getElementById('interest_rate');
    if (interestRateInput) {
        interestRateInput.addEventListener('input', function() {
            console.log('Interest rate manually changed:', this.value);
            calculateAgreementSummary();
        });
    }
    const stdInterestInput = document.getElementById('std_interest_rate');
    const belowInterestInput = document.getElementById('below_interest_rate');
    
    if (stdInterestInput) {
        stdInterestInput.addEventListener('input', function() {
            document.getElementById('standardResults').style.display = 'none';
        });
    }
    
    if (belowInterestInput) {
        belowInterestInput.addEventListener('input', function() {
            document.getElementById('belowResults').style.display = 'none';
        });
    }
    
    if (depositInput) {
        depositInput.addEventListener('input', function() {
            console.log('Deposit changed:', this.value);
            calculateAgreementSummary();
        });
    }
    
    if (durationSelect) {
        durationSelect.addEventListener('change', function() {
            console.log('Duration changed:', this.value);
            calculateAgreementSummary();
        });
    }
    
    if (vehiclePriceInput) {
        vehiclePriceInput.addEventListener('input', function() {
            console.log('Vehicle price manually changed:', this.value);
            calculateAgreementSummary();
        });
    }
    
    // Auto-calculate toggle handler
    if (autoCalculateToggle) {
        autoCalculateToggle.addEventListener('change', function() {
            if (trackingFeesInput) {
                trackingFeesInput.readOnly = this.checked;
                if (this.checked) {
                    trackingFeesInput.placeholder = 'Auto-calculated based on loan amount';
                    trackingFeesInput.classList.add('bg-light');
                } else {
                    trackingFeesInput.placeholder = 'Enter tracking fees manually';
                    trackingFeesInput.classList.remove('bg-light');
                }
            }
            calculateAgreementSummary();
        });
        
        // Set initial state
        if (trackingFeesInput) {
            trackingFeesInput.readOnly = autoCalculateToggle.checked;
            if (autoCalculateToggle.checked) {
                trackingFeesInput.placeholder = 'Auto-calculated based on loan amount';
                trackingFeesInput.classList.add('bg-light');
            }
        }
    }
    
    // Manual tracking fees input listener (only when not auto-calculating)
    if (trackingFeesInput) {
        trackingFeesInput.addEventListener('input', function() {
            if (!autoCalculateToggle?.checked) {
                console.log('Manual tracking fees changed:', this.value);
                calculateAgreementSummary();
            }
        });
    }
    
    // Modal calculator input listeners
    const stdInputs = ['std_vehicle_price', 'std_deposit', 'std_duration'];
    const belowInputs = ['below_vehicle_price', 'below_deposit', 'below_duration'];
    
    stdInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                // Clear previous results when inputs change
                document.getElementById('standardResults').style.display = 'none';
            });
        }
    });
    
    belowInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                // Clear previous results when inputs change
                document.getElementById('belowResults').style.display = 'none';
            });
        }
    });
}

// UPDATED: Update summary display with auto-calculated tracking fee breakdown
function updateSummaryDisplay(totalLoanAmount, totalInterest, totalPayable, depositPercentage, trackingFee) {
    const elements = {
        loan_amount: document.getElementById('summary_loan_amount'),
        total_interest: document.getElementById('summary_total_interest'),
        total_payable: document.getElementById('summary_total_payable'),
        deposit_percentage: document.getElementById('summary_deposit_percentage')
    };
    
    if (elements.loan_amount) {
        const baseLoan = totalLoanAmount - trackingFee;
        const trackingRate = depositPercentage >= 50 ? '5%' : '4%';
        elements.loan_amount.innerHTML = `
            KSh ${totalLoanAmount.toLocaleString('en-US', { maximumFractionDigits: 0 })}
            <small class="d-block text-muted mt-1">
                Vehicle Loan: ${baseLoan.toLocaleString()}<br>
                Tracking Fee: ${trackingFee.toLocaleString()} (${trackingRate} of loan)
            </small>
        `;
    }
    if (elements.total_interest) {
        elements.total_interest.textContent = 'KSh ' + totalInterest.toLocaleString('en-US', { maximumFractionDigits: 0 });
    }
    if (elements.total_payable) {
        elements.total_payable.textContent = 'KSh ' + totalPayable.toLocaleString('en-US', { maximumFractionDigits: 0 });
    }
    if (elements.deposit_percentage) {
        elements.deposit_percentage.textContent = depositPercentage.toFixed(1) + '%';
        
        // Color code based on deposit percentage
        if (depositPercentage >= 50) {
            elements.deposit_percentage.className = 'fw-bold text-success';
        } else if (depositPercentage >= 30) {
            elements.deposit_percentage.className = 'fw-bold text-warning';
        } else {
            elements.deposit_percentage.className = 'fw-bold text-danger';
        }
    }
}

// Reset summary display
function resetSummaryDisplay() {
    const summaryElements = [
        'summary_loan_amount',
        'summary_total_interest', 
        'summary_total_payable',
        'summary_deposit_percentage'
    ];
    
    summaryElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            if (id === 'summary_deposit_percentage') {
                element.textContent = '0%';
                element.className = 'fw-bold';
            } else {
                element.textContent = 'KSh 0';
                if (id === 'summary_loan_amount') {
                    element.innerHTML = 'KSh 0';
                }
            }
        }
    });
}

// Enhanced alert function
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show custom-alert`;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
            <div style="white-space: pre-line;">${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds for success, 10 seconds for error
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, type === 'error' ? 10000 : 5000);
}

// Form submission handlers
function initializeFormSubmission() {
    const hirePurchaseForm = document.getElementById('hirePurchaseForm');
    if (hirePurchaseForm) {
        hirePurchaseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Disable submit button and show loading
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Agreement...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    // Close modal and redirect after delay
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addAgreementModal'));
                        if (modal) modal.hide();
                        if (data.data && data.data.redirect_url) {
                            window.location.href = data.data.redirect_url;
                        } else {
                            window.location.reload();
                        }
                    }, 2000);
                } else {
                    showAlert('error', data.message || 'An error occurred while creating the agreement.');
                    if (data.errors) {
                        // Show validation errors
                        Object.keys(data.errors).forEach(field => {
                            const input = document.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = data.errors[field][0];
                                input.parentNode.appendChild(errorDiv);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An unexpected error occurred. Please try again.');
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
}

// Other handlers including missing functions
function initializeOtherHandlers() {
    // Status filter functionality
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterTableByStatus(this.value);
        });
    }
    
    console.log('Other handlers initialized');
}

// Initialize DataTable
function initializeDataTable() {
    const table = document.getElementById('hirePurchaseTable');
    if (table && typeof DataTable !== 'undefined') {
        new DataTable(table, {
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [-1] } // Disable sorting on actions column
            ]
        });
    }
    console.log('DataTable initialized');
}

// Filter table by status
function filterTableByStatus(status) {
    const tableRows = document.querySelectorAll('#hirePurchaseTable tbody tr');
    tableRows.forEach(row => {
        if (status === '' || row.dataset.status === status.toLowerCase()) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Approve Agreement Function
function approveAgreement(agreementId) {
    Swal.fire({
        title: 'Approve Agreement?',
        text: 'Are you sure you want to approve this agreement?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we approve the agreement',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/hire-purchase/${agreementId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                // Check if response is ok first
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                // Close loading dialog
                Swal.close();
                
                // Check if backend returned success (either data.success === true OR just data.message exists)
                if (data.success === true || (data.message && !data.error)) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Agreement approved successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Failed!',
                        text: data.message || data.error || 'Failed to approve agreement',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error approving agreement:', error);
                
                // Close loading dialog
                Swal.close();
                
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while approving the agreement: ' + error.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

// Delete Agreement Function
function deleteAgreement(agreementId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You want to delete this agreement? This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Show loading state
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the agreement.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/hire-purchase/${agreementId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        // Remove the row from table or reload page
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to delete agreement',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                }
            })
            .catch(error => {
                console.error('Error deleting agreement:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while deleting the agreement',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            });
        }
    });
}

// Edit Agreement Function
function editAgreement(agreementId) {
    // You can implement this to open an edit modal
    // For now, just show a message
    showAlert('info', 'Edit functionality will be implemented soon');
}

// Record Payment Function
function recordPayment(agreementId) {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        // Set the agreement ID in the hidden input
        const agreementInput = document.getElementById('payment_agreement_id');
        if (agreementInput) {
            agreementInput.value = agreementId;
        }
        
        // Show the modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

// Export for testing (updated to include auto-calculated tracking fee functions)
window.HirePurchaseManager = {
    calculateAgreementSummary,
    getInterestRateByDeposit,
    calculateTrackingFee,
    getTrackingFee,
    calculateStandard,
    calculateBelow
};
</script>

<style>
.avatar-sm {
    height: 2.5rem;
    width: 2.5rem;
}

.avatar-title {
    align-items: center;
    background-color: var(--bs-primary);
    color: #fff;
    display: flex;
    font-size: 1rem;
    font-weight: 500;
    height: 100%;
    justify-content: center;
    width: 100%;
}

.bg-soft-primary {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

.bg-soft-success {
    background-color: rgba(var(--bs-success-rgb), 0.1);
}

.bg-soft-info {
    background-color: rgba(var(--bs-info-rgb), 0.1);
}

.bg-soft-warning {
    background-color: rgba(var(--bs-warning-rgb), 0.1);
}

.progress {
    border-radius: 0.375rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.btn-group-vertical .btn {
    border-radius: 0.375rem !important;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-xl {
    max-width: 90%;
}

@media (max-width: 768px) {
    .modal-xl {
        max-width: 95%;
    }
    
    .btn-group-vertical .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
.card-header .card-title {
    color: #2c3e50 !important;
    font-weight: 600 !important;
    font-size: 1.1rem !important;
}

.nav-tabs .nav-link {
    color: #495057 !important;
    font-weight: 500 !important;
}

.nav-tabs .nav-link.active {
    color: #0d6efd !important;
    font-weight: 600 !important;
}
</style>