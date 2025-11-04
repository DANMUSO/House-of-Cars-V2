<x-app-layout>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Leads Management</h4>
                <p class="text-muted mb-0">Manage and track potential vehicle sales leads</p>
            </div>
            <div class="flex-grow-1 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                    <i class="fas fa-plus me-1"></i> Add Lead
                </button>
            </div>
        </div>
        <!-- REPLACE THE EXISTING STATUS FILTER BUTTONS SECTION WITH THIS ENHANCED FILTER SECTION -->

<!-- Enhanced Filters Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>Filter Leads
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Search by name, phone, email...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                            <option value="unsuccessful">Unsuccessful</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Purchase Type</label>
                        <select class="form-select" id="purchaseTypeFilter">
                            <option value="">All Types</option>
                            <option value="cash">Cash</option>
                            <option value="finance">Finance</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Salesperson</label>
                        <select class="form-select" id="salespersonFilter">
                            <option value="">All Salespeople</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" id="dateFromFilter">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" id="dateToFilter">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Follow Up</label>
                        <select class="form-select" id="followUpFilter">
                            <option value="">All</option>
                            <option value="1">Required</option>
                            <option value="0">Not Required</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                            <i class="fas fa-times me-1"></i> Clear
                        </button>
                    </div>
                </div>
                
                <!-- Quick Date Filters -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            <small class="text-muted align-self-center me-2">Quick date filters:</small>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-date-filter" data-range="today">
                                <i class="fas fa-calendar-day me-1"></i> Today
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-date-filter" data-range="week">
                                <i class="fas fa-calendar-week me-1"></i> Last 7 Days
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-date-filter" data-range="month">
                                <i class="fas fa-calendar-alt me-1"></i> This Month
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-date-filter" data-range="quarter">
                                <i class="fas fa-calendar me-1"></i> This Quarter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Info -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                <span id="filterResults">Showing all leads</span>
            </div>
            <div class="text-muted">
                <small>Total: <span id="totalCount">{{ $leads->total() }}</span> leads</small>
            </div>
        </div>
    </div>
</div>
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-primary-subtle">
                                    <span class="avatar-title rounded-circle bg-primary text-white">
                                        <i class="fas fa-user-plus"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ isset($statistics) ? ($statistics['active'] ?? 0) : 0 }}</h5>
                                <p class="text-muted mb-0 small">Active</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-success-subtle">
                                    <span class="avatar-title rounded-circle bg-success text-white">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ isset($statistics) ? ($statistics['closed'] ?? 0) : 0 }}</h5>
                                <p class="text-muted mb-0 small">Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-danger-subtle">
                                    <span class="avatar-title rounded-circle bg-danger text-white">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ isset($statistics) ? ($statistics['unsuccessful'] ?? 0) : 0 }}</h5>
                                <p class="text-muted mb-0 small">Unsuccessful</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-warning-subtle">
                                    <span class="avatar-title rounded-circle bg-warning text-white">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ isset($statistics) ? ($statistics['follow_up'] ?? 0) : 0 }}</h5>
                                <p class="text-muted mb-0 small">Follow Up</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-info-subtle">
                                    <span class="avatar-title rounded-circle bg-info text-white">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ isset($statistics) ? ($statistics['finance'] ?? 0) : 0 }}</h5>
                                <p class="text-muted mb-0 small">Finance</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-secondary-subtle">
                                    <span class="avatar-title rounded-circle bg-secondary text-white">
                                        <i class="fas fa-coins"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ isset($statistics) ? ($statistics['cash'] ?? 0) : 0 }}</h5>
                                <p class="text-muted mb-0 small">Cash</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Budget Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-success mb-1">{{ $leads->total() }}</h4>
                                <p class="text-muted mb-0">Total Leads</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-primary mb-1">KES {{ number_format(isset($statistics) ? ($statistics['avg_budget'] ?? 0) : 0, 2) }}</h4>
                                <p class="text-muted mb-0">Average Budget</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning mb-1">KES {{ number_format(isset($statistics) ? ($statistics['total_budget'] ?? 0) : 0, 2) }}</h4>
                                <p class="text-muted mb-0">Total Budget Pool</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info mb-1">
                                    @php
                                        $active = isset($statistics) ? ($statistics['active'] ?? 0) : 0;
                                        $closed = isset($statistics) ? ($statistics['closed'] ?? 0) : 0;
                                        $unsuccessful = isset($statistics) ? ($statistics['unsuccessful'] ?? 0) : 0;
                                        $total = $active + $closed + $unsuccessful;
                                    @endphp
                                    @if($total > 0)
                                        {{ number_format(($closed / $total) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </h4>
                                <p class="text-muted mb-0">Conversion Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Lead Modal -->
        <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h1 class="modal-title fs-5" id="standard-modalLabel">
                            <i class="fas fa-plus me-2"></i>Add New Lead
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="LeadForm" class="row g-3" action="{{ route('leads.store') }}" method="POST">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="client_name" name="client_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Client Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="client_phone" name="client_phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Client Email</label>
                                <input type="email" class="form-control" id="client_email" name="client_email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Car Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="car_model" name="car_model" placeholder="e.g., Toyota Camry 2020" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Purchase Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="purchase_type" name="purchase_type" required>
                                    <option value="">Select Purchase Type</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Finance">Finance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Client Budget <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="client_budget" name="client_budget" placeholder="0.00" required>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <label class="form-label">Commitment Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="commitment_amount" name="commitment_amount" placeholder="0.00" required>
                                </div>
                            </div>
                            <!--
                            <div class="col-md-6">
                                <label class="form-label">Salesperson <span class="text-danger"></span></label>
                                <select class="form-select" id="salesperson_id" name="salesperson_id">
                                    <option value="1">Select Salesperson</option>
                                    @foreach($salespeople as $salesperson)
                                        <option value="{{ $salesperson->id }}">{{ $salesperson->first_name }} {{ $salesperson->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            -->
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Closed">Closed</option>
                                    <option value="Unsuccessful">Unsuccessful</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="follow_up_required" name="follow_up_required" value="1">
                                    <label class="form-check-label" for="follow_up_required">
                                        Follow up required
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the lead..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Lead
                                </button>
                                <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Status Filter Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group" role="group" aria-label="Status filters">
                        <button type="button" class="btn btn-outline-primary status-filter {{ !request('status') ? 'active' : '' }}" data-status="">
                            <i class="fas fa-list me-1"></i> All Leads
                        </button>
                    </div>
                    
                    <div class="text-muted">
                        <small>Showing {{ $leads->firstItem() }} to {{ $leads->lastItem() }} of {{ $leads->total() }} leads</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Sales Leads
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" id="leadsTableContainer">
                            <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                                <thead class="table-dark">
    <tr>
        <th data-priority="4"><i class="fas fa-hashtag me-1"></i>#</th>
        <th data-priority="2"><i class="fas fa-user me-1"></i>Client Details</th>
        <th data-priority="5"><i class="fas fa-car me-1"></i>Car Model</th>
        <th data-priority="6"><i class="fas fa-credit-card me-1"></i>Purchase Type</th>
        <th data-priority="7"><i class="fas fa-money-bill me-1"></i>Budget (KES)</th>
        <th data-priority="8"><i class="fas fa-money-bill me-1"></i>Commitment Amount (KES)</th>
        <th data-priority="9"><i class="fas fa-user-tie me-1"></i>Salesperson</th>
        <th data-priority="3"><i class="fas fa-flag me-1"></i>Status</th>
        <th data-priority="10"><i class="fas fa-clock me-1"></i>Follow Up</th>
        <th data-priority="11"><i class="fas fa-calendar me-1"></i>Created</th>
        <th data-priority="1"><i class="fas fa-cog me-1"></i>Actions</th>
    </tr>
</thead>
                                <tbody>
                                    @forelse ($leads as $index => $lead)
                                        <tr>
                                            <td>{{ $leads->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <span class="avatar-title rounded-circle bg-primary text-white">
                                                            {{ substr($lead->client_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $lead->client_name }}</strong>
                                                        <br><small class="text-muted">{{ $lead->client_phone }}</small>
                                                        @if($lead->client_email)
                                                            <br><small class="text-muted">{{ $lead->client_email }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $lead->car_model }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge {{ $lead->purchase_type == 'cash' ? 'bg-success' : 'bg-info' }} fs-6">
                                                    <i class="fas {{ $lead->purchase_type == 'cash' ? 'fa-coins' : 'fa-credit-card' }} me-1"></i>
                                                    {{ ucfirst($lead->purchase_type) }}
                                                </span>
                                            </td>
                                            <td><strong class="text-success">KES {{ number_format($lead->client_budget, 2) }}</strong></td>
                                            <td><strong class="text-success">KES {{ number_format($lead->commitment_amount, 2) }}</strong></td>
                                            <td>
                                                @if($lead->users)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <span class="avatar-title rounded-circle bg-secondary text-white">
                                                              
                                                            </span>
                                                        </div>
                                                        <span>  {{ $lead->users->first_name }}</span>
                                                    </div>
                                                @else
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'active' => ['bg-primary', 'fa-user-plus'],
                                                        'closed' => ['bg-success', 'fa-check-circle'],
                                                        'unsuccessful' => ['bg-danger', 'fa-times-circle']
                                                    ];
                                                    $config = $statusConfig[$lead->status] ?? ['bg-secondary', 'fa-question'];
                                                @endphp
                                                <span class="badge {{ $config[0] }}">
                                                    <i class="fas {{ $config[1] }} me-1"></i>{{ ucfirst($lead->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($lead->follow_up_required)
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Required
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-check me-1"></i>None
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $lead->created_at->format('Y-m-d') }}</strong>
                                                <br><small class="text-muted">{{ $lead->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical" role="group">
                                                    <button class="btn btn-warning btn-sm editBtn mb-1"
                                                        data-id="{{ $lead->id }}"
                                                        data-client-name="{{ $lead->client_name }}"
                                                        data-client-phone="{{ $lead->client_phone }}"
                                                        data-client-email="{{ $lead->client_email }}"
                                                        data-car-model="{{ $lead->car_model }}"
                                                        data-purchase-type="{{ $lead->purchase_type }}"
                                                        data-client-budget="{{ $lead->client_budget }}"
                                                        data-salesperson-id="{{ $lead->salesperson_id }}"
                                                        data-status="{{ $lead->status }}"
                                                        data-follow-up="{{ $lead->follow_up_required }}"
                                                        data-commitment-amount="{{ $lead->commitment_amount }}"
                                                        data-notes="{{ $lead->notes }}">
                                                        <i class="fas fa-edit me-1"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger btn-sm deleteBtn mb-1" data-id="{{ $lead->id }}">
                                                        <i class="fas fa-trash me-1"></i> Delete
                                                    </button>
                                                    @if($lead->notes)
                                                        <button class="btn btn-info btn-sm notesBtn" data-notes="{{ $lead->notes }}">
                                                            <i class="fas fa-sticky-note me-1"></i> Notes
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-user-times fa-3x mb-3"></i>
                                                    <h5>No leads found</h5>
                                                    <p>Click "Add Lead" to create your first sales lead.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1"  data-bs-backdrop="false" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="updateLeadsForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="recordId">
                    <input type="hidden" name="salesperson_id" id="editSalespersonId">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="editModalLabel">
                                <i class="fas fa-edit me-2"></i>Update Lead
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="recordId">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="editClientName" class="form-label">Client Name</label>
                                    <input type="text" class="form-control" name="client_name" id="editClientName" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editClientPhone" class="form-label">Client Phone</label>
                                    <input type="tel" class="form-control" name="client_phone" id="editClientPhone" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="editClientEmail" class="form-label">Client Email</label>
                                    <input type="email" class="form-control" name="client_email" id="editClientEmail">
                                </div>
                                <div class="col-md-6">
                                    <label for="editCarModel" class="form-label">Car Model</label>
                                    <input type="text" class="form-control" name="car_model" id="editCarModel" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="editPurchaseType" class="form-label">Purchase Type</label>
                                    <select class="form-select" name="purchase_type" id="editPurchaseType" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Finance">Finance</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editClientBudget" class="form-label">Client Budget</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" class="form-control" name="client_budget" id="editClientBudget" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label">Commitment Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="editCommitmentAmount" name="commitment_amount" placeholder="0.00" required>
                                </div>
                            </div>
                            </div>
                            <div class="row mb-3">
                                <!--
                                <div class="col-md-6">
                                    <label for="editSalesperson" class="form-label">Salesperson</label>
                                    <select class="form-select" name="salesperson_id" id="editSalesperson" required>
                                        @foreach($salespeople as $salesperson)
                                            <option value="{{ $salesperson->id }}">{{ $salesperson->first_name }} {{ $salesperson->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>-->
                                <div class="col-md-6">
                                    <label for="editStatus" class="form-label">Status</label>
                                    <select class="form-select" name="status" id="editStatus" required>
                                        <option value="Active">Active</option>
                                        <option value="Closed">Closed</option>
                                        <option value="Unsuccessful">Unsuccessful</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editFollowUpRequired" name="follow_up_required" value="1">
                                        <label class="form-check-label" for="editFollowUpRequired">
                                            Follow up required
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="editNotes" class="form-label">Notes</label>
                                    <textarea class="form-control" name="notes" id="editNotes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Lead
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notes Modal -->
        <div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="notesModalLabel">
                            <i class="fas fa-sticky-note me-2"></i>Lead Notes
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="notesContent"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- container-fluid -->

    <!-- Custom CSS -->
    <style>
        /* Avatar Styles */
        .avatar-sm {
            width: 40px;
            height: 40px;
        }
        .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Status Filter Styles */
        .status-filter.active {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: white;
        }
        
        /* Badge Styles */
        .badge {
            font-size: 0.75em;
        }
        .badge.fs-6 {
            font-size: 0.85rem !important;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        /* Button Styles */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .btn-group-vertical .btn {
            margin-bottom: 2px;
        }
        .btn-group-vertical .btn:last-child {
            margin-bottom: 0;
        }
        
        /* Modal Styles */
        .modal-header {
            background-color: var(--bs-light);
            border-bottom: 1px solid var(--bs-border-color);
        }
        
        /* Card Styles */
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .card-header {
            font-weight: 600;
        }

        /* Input Group Styles */
        .input-group-text {
            background-color: var(--bs-light);
            border-color: var(--bs-border-color);
            font-weight: 600;
        }
        
        /* Empty State */
        .table tbody tr td .fa-user-times {
            color: var(--bs-gray-400);
        }
        
        /* Filter Form Styles */
        .card-body .row.g-3 {
            align-items: end;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .btn-group-vertical {
                display: flex;
                flex-direction: column;
                width: 100%;
            }
            .btn-group-vertical .btn {
                margin-bottom: 5px;
                width: 100%;
            }
            
            .statistics-row .col-lg-2 {
                margin-bottom: 1rem;
            }
        }

        /* Loading States */
        .btn.loading {
            pointer-events: none;
            opacity: 0.6;
        }

        /* Search highlight */
        .table tbody tr.highlight {
            background-color: rgba(255, 193, 7, 0.1);
            animation: highlight 2s ease-out;
        }

        @keyframes highlight {
            0% { background-color: rgba(255, 193, 7, 0.3); }
            100% { background-color: transparent; }
        }
    </style>

    <script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#responsive-datatable').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        pageLength: 25,
        order: [[9, 'desc']], // Sort by created date column
        columnDefs: [
            { 
                orderable: false, 
                targets: [10],
                responsivePriority: 1 // Always show Actions column
            },
            {
                responsivePriority: 2,
                targets: [1] // Client Details
            },
            {
                responsivePriority: 3,
                targets: [7] // Status
            }
        ]
    });

    // Store original data for reference
    let originalTableData = [];
    
    // Custom filter function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var searchFilter = $('#searchInput').val().toLowerCase();
            var statusFilter = $('#statusFilter').val().toLowerCase();
            var purchaseTypeFilter = $('#purchaseTypeFilter').val().toLowerCase();
            var salespersonFilter = $('#salespersonFilter').val().toLowerCase();
            var followUpFilter = $('#followUpFilter').val();
            var dateFrom = $('#dateFromFilter').val();
            var dateTo = $('#dateToFilter').val();

            // Get row element
            var $row = $(table.row(dataIndex).node());

            // Search filter - check multiple columns
            if (searchFilter !== '') {
                var clientName = data[1].toLowerCase(); // Client Details column
                var carModel = data[2].toLowerCase(); // Car Model column
                
                if (clientName.indexOf(searchFilter) === -1 && 
                    carModel.indexOf(searchFilter) === -1) {
                    return false;
                }
            }

            // Status filter
            if (statusFilter !== '') {
                var rowStatus = $row.find('td:eq(7) .badge').text().toLowerCase().trim();
                if (rowStatus.indexOf(statusFilter) === -1) {
                    return false;
                }
            }

            // Purchase Type filter
            if (purchaseTypeFilter !== '') {
                var rowPurchaseType = $row.find('td:eq(3) .badge').text().toLowerCase().trim();
                if (rowPurchaseType.indexOf(purchaseTypeFilter) === -1) {
                    return false;
                }
            }

            // Salesperson filter
            if (salespersonFilter !== '') {
                var rowSalesperson = $row.find('td:eq(6) span').text().toLowerCase().trim();
                if (rowSalesperson !== salespersonFilter) {
                    return false;
                }
            }

            // Follow Up filter
            if (followUpFilter !== '') {
                var hasFollowUp = $row.find('td:eq(8) .badge').text().includes('Required');
                var wantsFollowUp = (followUpFilter === '1');
                if (hasFollowUp !== wantsFollowUp) {
                    return false;
                }
            }

            // Date range filter
            if (dateFrom || dateTo) {
                var rowDateText = $row.find('td:eq(9) strong').text().trim();
                var rowDate = new Date(rowDateText);
                
                if (dateFrom) {
                    var fromDate = new Date(dateFrom);
                    if (rowDate < fromDate) {
                        return false;
                    }
                }
                
                if (dateTo) {
                    var toDate = new Date(dateTo);
                    toDate.setHours(23, 59, 59, 999);
                    if (rowDate > toDate) {
                        return false;
                    }
                }
            }

            return true;
        }
    );

    // Populate salesperson dropdown from existing data
    function populateSalespersonDropdown() {
        var salespeople = new Set();
        
        table.rows().every(function() {
            var $row = $(this.node());
            var salesperson = $row.find('td:eq(6) span').text().trim();
            if (salesperson && salesperson !== '') {
                salespeople.add(salesperson.toLowerCase());
            }
        });
        
        var dropdown = $('#salespersonFilter');
        dropdown.find('option:not(:first)').remove();
        
        Array.from(salespeople).sort().forEach(function(salesperson) {
            var displayName = salesperson.charAt(0).toUpperCase() + salesperson.slice(1);
            dropdown.append(`<option value="${salesperson}">${displayName}</option>`);
        });
    }

    // Initialize salesperson dropdown
    populateSalespersonDropdown();

    // Function to update filter summary
    function updateFilterSummary() {
        var filters = [];
        
        var searchVal = $('#searchInput').val();
        var statusVal = $('#statusFilter').val();
        var purchaseTypeVal = $('#purchaseTypeFilter').val();
        var salespersonVal = $('#salespersonFilter').val();
        var followUpVal = $('#followUpFilter').val();
        var dateFromVal = $('#dateFromFilter').val();
        var dateToVal = $('#dateToFilter').val();

        if (searchVal) filters.push('Search: "' + searchVal + '"');
        if (statusVal) filters.push('Status: ' + statusVal);
        if (purchaseTypeVal) filters.push('Type: ' + purchaseTypeVal);
        if (salespersonVal) filters.push('Salesperson: ' + salespersonVal);
        if (followUpVal) filters.push('Follow-up: ' + (followUpVal === '1' ? 'Required' : 'Not Required'));
        if (dateFromVal) filters.push('From: ' + new Date(dateFromVal).toLocaleDateString());
        if (dateToVal) filters.push('To: ' + new Date(dateToVal).toLocaleDateString());

        var resultText = 'Showing ';
        if (filters.length > 0) {
            resultText += 'filtered leads (' + filters.join(', ') + ')';
        } else {
            resultText += 'all leads';
        }
        
        $('#filterResults').text(resultText);
    }

    // Function to update statistics based on filtered data
    function updateFilteredStatistics() {
        var stats = {
            active: 0,
            closed: 0,
            unsuccessful: 0,
            followUp: 0,
            finance: 0,
            cash: 0,
            totalBudget: 0,
            count: 0
        };

        table.rows({ search: 'applied' }).every(function() {
            var $row = $(this.node());
            stats.count++;

            // Count by status
            var status = $row.find('td:eq(7) .badge').text().toLowerCase().trim();
            if (status.includes('active')) stats.active++;
            else if (status.includes('closed')) stats.closed++;
            else if (status.includes('unsuccessful')) stats.unsuccessful++;

            // Count by purchase type
            var purchaseType = $row.find('td:eq(3) .badge').text().toLowerCase().trim();
            if (purchaseType.includes('finance')) stats.finance++;
            else if (purchaseType.includes('cash')) stats.cash++;

            // Count follow-ups
            if ($row.find('td:eq(8) .badge').text().includes('Required')) {
                stats.followUp++;
            }

            // Sum budget
            var budgetText = $row.find('td:eq(4)').text().replace(/[^\d.]/g, '');
            stats.totalBudget += parseFloat(budgetText) || 0;
        });

        // Update statistics cards
        $('.row.mb-4 .col-lg-2:eq(0) .card-body h5').text(stats.active);
        $('.row.mb-4 .col-lg-2:eq(1) .card-body h5').text(stats.closed);
        $('.row.mb-4 .col-lg-2:eq(2) .card-body h5').text(stats.unsuccessful);
        $('.row.mb-4 .col-lg-2:eq(3) .card-body h5').text(stats.followUp);
        $('.row.mb-4 .col-lg-2:eq(4) .card-body h5').text(stats.finance);
        $('.row.mb-4 .col-lg-2:eq(5) .card-body h5').text(stats.cash);

        // Update total count
        $('#totalCount').text(stats.count);

        // Update average budget and conversion rate
        var avgBudget = stats.count > 0 ? stats.totalBudget / stats.count : 0;
        var totalProcessed = stats.active + stats.closed + stats.unsuccessful;
        var conversionRate = totalProcessed > 0 ? (stats.closed / totalProcessed * 100).toFixed(1) : 0;

        $('.card.bg-light .text-success').text(stats.count);
        $('.card.bg-light .text-primary').text('KES ' + avgBudget.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('.card.bg-light .text-warning').text('KES ' + stats.totalBudget.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('.card.bg-light .text-info').text(conversionRate + '%');
    }

    // Apply filters with debounce for search
    var searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.draw();
            updateFilterSummary();
            updateFilteredStatistics();
        }, 300);
    });

    // Apply filters on dropdown change
    $('#statusFilter, #purchaseTypeFilter, #salespersonFilter, #followUpFilter').on('change', function() {
        table.draw();
        updateFilterSummary();
        updateFilteredStatistics();
        
        // Show notification
        var visibleRows = table.rows({ search: 'applied' }).count();
        var totalRows = table.rows().count();
        
        toastr.info(`Showing ${visibleRows} of ${totalRows} leads`, 'Filter Applied', {
            timeOut: 2000,
            progressBar: true
        });
    });

    // Apply filters on date change
    $('#dateFromFilter, #dateToFilter').on('change', function() {
        validateDateRange();
        table.draw();
        updateFilterSummary();
        updateFilteredStatistics();
    });

    // Date validation
    function validateDateRange() {
        var fromDate = $('#dateFromFilter').val();
        var toDate = $('#dateToFilter').val();
        
        if (fromDate && toDate && fromDate > toDate) {
            $('#dateToFilter').val(fromDate);
        }
        
        if (fromDate) {
            $('#dateToFilter').attr('min', fromDate);
        }
        if (toDate) {
            $('#dateFromFilter').attr('max', toDate);
        }
    }

    // Quick date filters
    $('.quick-date-filter').on('click', function() {
        var range = $(this).data('range');
        var today = new Date();
        var fromDate, toDate;
        
        switch(range) {
            case 'today':
                fromDate = toDate = today.toISOString().split('T')[0];
                break;
            case 'week':
                fromDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'month':
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'quarter':
                var quarter = Math.floor(today.getMonth() / 3);
                fromDate = new Date(today.getFullYear(), quarter * 3, 1).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
        }
        
        $('#dateFromFilter').val(fromDate);
        $('#dateToFilter').val(toDate);
        
        table.draw();
        updateFilterSummary();
        updateFilteredStatistics();
    });

    // Clear all filters
    $('#clearFilters').on('click', function() {
        $('#searchInput').val('');
        $('#statusFilter').val('');
        $('#purchaseTypeFilter').val('');
        $('#salespersonFilter').val('');
        $('#followUpFilter').val('');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        
        // Remove date restrictions
        $('#dateFromFilter').removeAttr('max');
        $('#dateToFilter').removeAttr('min');
        
        table.draw();
        updateFilterSummary();
        updateFilteredStatistics();
        
        toastr.success('All filters cleared', 'Filters Reset', {
            timeOut: 2000,
            progressBar: true
        });
    });

    // Initial filter summary and statistics
    updateFilterSummary();
    updateFilteredStatistics();

    // Rest of your existing code (edit, delete, notes, forms, etc.)
    // ... (keep all your existing event handlers below)
    
    // CSRF token setup for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Edit modal handler
    $(document).on('click', '.editBtn', function() {
        const data = $(this).data();
        
        $('#recordId').val(data.id);
        $('#editClientName').val(data.clientName);
        $('#editClientPhone').val(data.clientPhone);
        $('#editClientEmail').val(data.clientEmail);
        $('#editCarModel').val(data.carModel);
        $('#editPurchaseType').val(data.purchaseType); 
        $('#editCommitmentAmount').val(data.commitmentAmount);
        $('#editClientBudget').val(data.clientBudget);
        $('#editSalespersonId').val(data.salespersonId);
        $('#editStatus').val(data.status);
        $('#editFollowUpRequired').prop('checked', data.followUp == 1);
        $('#editNotes').val(data.notes);
        
        $('#updateLeadsForm').attr('action', `/leads/${data.id}`);
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });

    // Update form handler
    $('#updateLeadsForm').on('submit', function(e) {
        e.preventDefault();
        
        const button = $(this).find('button[type="submit"]');
        const originalText = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');
        button.prop('disabled', true);
        
        const actionUrl = $(this).attr('action');
        
        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                if (modal) modal.hide();
                
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                }, 300);
                
                Swal.fire('Success!', 'Lead updated successfully!', 'success')
                    .then(() => location.reload());
            },
            error: function(xhr) {
                button.html(originalText);
                button.prop('disabled', false);
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorMessages = [];
                    Object.values(xhr.responseJSON.errors).forEach(error => {
                        errorMessages.push('• ' + error[0]);
                    });
                    
                    Swal.fire({
                        title: 'Validation Errors',
                        html: errorMessages.join('<br>'),
                        icon: 'error'
                    });
                } else {
                    Swal.fire('Error!', 'Error updating lead. Please try again.', 'error');
                }
            }
        });
    });

    // Delete handler
    $(document).on('click', '.deleteBtn', function() {
        const leadId = $(this).data('id');
        const leadRow = $(this).closest('tr');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/leads/${leadId}`,
                    type: 'DELETE',
                    success: function(response) {
                        // Remove row from DataTable
                        table.row(leadRow).remove().draw();
                        
                        // Update statistics
                        updateFilteredStatistics();
                        
                        Swal.fire('Deleted!', 'Lead has been deleted.', 'success');
                    },
                    error: function() {
                        Swal.fire('Error!', 'Error deleting lead. Please try again.', 'error');
                    }
                });
            }
        });
    });

    // Notes modal handler
    $(document).on('click', '.notesBtn', function() {
        const notes = $(this).data('notes');
        $('#notesContent').html(notes.replace(/\n/g, '<br>'));
        new bootstrap.Modal(document.getElementById('notesModal')).show();
    });

    // Add lead form handler
    $('#LeadForm').on('submit', function(e) {
        e.preventDefault();
        
        const button = $(this).find('button[type="submit"]');
        const originalText = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        button.prop('disabled', true);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('standard-modal'));
                if (modal) modal.hide();
                
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                }, 300);
                
                Swal.fire('Success!', 'Lead created successfully!', 'success');
                location.reload();
            },
            error: function(xhr) {
                button.html(originalText);
                button.prop('disabled', false);
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorMessages = [];
                    Object.values(xhr.responseJSON.errors).forEach(error => {
                        errorMessages.push('• ' + error[0]);
                    });
                    
                    Swal.fire({
                        title: 'Validation Errors',
                        html: errorMessages.join('<br>'),
                        icon: 'error'
                    });
                } else {
                    Swal.fire('Error!', 'Error creating lead. Please try again.', 'error');
                }
            }
        });
    });

    // Modal cleanup
    document.getElementById('editModal').addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = 'auto';
    });

    // Budget formatting
    $('#client_budget, #editClientBudget').on('input', function() {
        let value = $(this).val().replace(/[^\d.]/g, '');
        if (value && !isNaN(value)) {
            $(this).val(parseFloat(value).toFixed(2));
        }
    });

    // Phone number formatting
    $('#client_phone, #editClientPhone').on('input', function() {
        let value = $(this).val().replace(/[^\d+\-\(\)\s]/g, '');
        $(this).val(value);
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
    </script>
</x-app-layout>