<x-app-layout>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Facilitation Requests</h4>
                <p class="text-muted mb-0">Manage and track facilitation requests</p>
            </div>
            <div class="flex-grow-1 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                    <i class="fas fa-plus me-1"></i> Send Request
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
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
                                <h5 class="mb-1">{{ $facilitations->where('status', 1)->count() }}</h5>
                                <p class="text-muted mb-0 small">Pending</p>
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
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $facilitations->where('status', 2)->count() }}</h5>
                                <p class="text-muted mb-0 small">Approved</p>
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
                                        <i class="fas fa-times"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $facilitations->where('status', 3)->count() }}</h5>
                                <p class="text-muted mb-0 small">Rejected</p>
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
                                <div class="avatar-sm rounded-circle bg-primary-subtle">
                                    <span class="avatar-title rounded-circle bg-primary text-white">
                                        <i class="fas fa-list"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $facilitations->count() }}</h5>
                                <p class="text-muted mb-0 small">Total Requests</p>
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
                                        <i class="fas fa-users"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $facilitations->pluck('user_id')->unique()->count() }}</h5>
                                <p class="text-muted mb-0 small">Unique Requesters</p>
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
                                <div class="avatar-sm rounded-circle bg-dark-subtle">
                                    <span class="avatar-title rounded-circle bg-dark text-white">
                                        <i class="fas fa-chart-line"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $facilitations->where('status', 2)->count() > 0 ? number_format(($facilitations->where('status', 2)->count() / $facilitations->count()) * 100, 1) : 0 }}%</h5>
                                <p class="text-muted mb-0 small">Approval Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Statistics Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-success mb-1">KES {{ number_format($facilitations->sum('amount'), 2) }}</h4>
                                <p class="text-muted mb-0">Total Requested</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-primary mb-1">KES {{ number_format($facilitations->where('status', 2)->sum('amount'), 2) }}</h4>
                                <p class="text-muted mb-0">Total Approved</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning mb-1">KES {{ number_format($facilitations->where('status', 1)->sum('amount'), 2) }}</h4>
                                <p class="text-muted mb-0">Pending Amount</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info mb-1">KES {{ $facilitations->count() > 0 ? number_format($facilitations->avg('amount'), 2) : '0.00' }}</h4>
                                <p class="text-muted mb-0">Average Request</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Type Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0 fw-bold" style="color: #000 !important;">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>Request Types Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $requestTypes = [
                                    'Fuel' => ['icon' => '🚗', 'color' => 'primary'],
                                    'Transport' => ['icon' => '🚌', 'color' => 'success'], 
                                    'Repairs' => ['icon' => '🔧', 'color' => 'warning'],
                                    'Amount' => ['icon' => '💰', 'color' => 'info'],
                                    'Allowance' => ['icon' => '💳', 'color' => 'secondary'],
                                    'Airtime' => ['icon' => '📱', 'color' => 'danger'],
                                    'Advance' => ['icon' => '💵', 'color' => 'dark']
                                ];
                            @endphp
                            
                            @foreach($requestTypes as $type => $config)
                                @php
                                    $typeCount = $facilitations->where('request', $type)->count();
                                    $typeAmount = $facilitations->where('request', $type)->sum('amount');
                                @endphp
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="flex-shrink-0">
                                            <span class="fs-4">{{ $config['icon'] }}</span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">{{ $type }}</h6>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">{{ $typeCount }} requests</small>
                                                <small class="text-{{ $config['color'] }} fw-bold">KES {{ number_format($typeAmount, 0) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Request Modal -->
        <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="standard-modalLabel">
                            <i class="fas fa-paper-plane me-2"></i>New Facilitation Request
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-xl-12">
                            <div class="card border-0">
                                <div class="card-body">
                                    <form id="FrequestForm" class="row g-3">
                                        @csrf
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Request Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="frequest" name="frequest" required>
                                                <option disabled selected value="">Choose Request Type</option>
                                                <option value="Fuel">🚗 Fuel</option>
                                                <option value="Transport">🚌 Transport</option>
                                                <option value="Repairs">🔧 Repairs</option>
                                                <option value="Amount">💰 Amount</option>
                                                <option value="Allowance">💳 Allowance</option>
                                                <option value="Airtime">📱 Airtime</option>
                                                <option value="Advance">💵 Advance</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Amount (KES) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">KES</span>
                                                <input type="text" class="form-control" id="famount" name="famount" 
                                                       placeholder="0.00" required>
                                            </div>
                                            <small class="text-muted">Maximum: KES 999,999.99</small>
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i> Submit Request
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
                </div>
            </div>
        </div>

        <!-- Status Filter Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group" role="group" aria-label="Status filters">
                        <button type="button" class="btn btn-outline-primary status-filter active" data-status="all">
                            <i class="fas fa-list me-1"></i> All Requests
                        </button>
                    </div>
                    
                    <div class="text-muted">
                        <small>Total: {{ $facilitations->count() }} requests</small>
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
                            <i class="fas fa-table me-2"></i>Facilitation Requests
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="table-responsive"> 
                  <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
    <thead class="table-dark">
        <tr>
            <th><i class="fas fa-user me-1"></i>Requester</th>
            <th><i class="fas fa-tag me-1"></i>Request Type</th>
            <th><i class="fas fa-money-bill me-1"></i>Amount</th>
            <th><i class="fas fa-info-circle me-1"></i>Status</th>
            <th><i class="fas fa-calendar me-1"></i>Date</th>
            @if(in_array(Auth::user()->role, ['Managing-Director', 'Accountant']))
                <th><i class="fas fa-cog me-1"></i>Actions</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse($facilitations as $facilitation)
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <span class="avatar-title rounded-circle bg-primary text-white">
                                {{ substr($facilitation->requester->first_name, 0, 1) }}{{ substr($facilitation->requester->last_name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $facilitation->requester->first_name }} {{ $facilitation->requester->last_name }}</h6>
                            <small class="text-muted">{{ $facilitation->requester->email }}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-info fs-6">
                        @switch($facilitation->request)
                            @case('Fuel')
                                🚗 {{ $facilitation->request }}
                                @break
                            @case('Transport')
                                🚌 {{ $facilitation->request }}
                                @break
                            @case('Repairs')
                                🔧 {{ $facilitation->request }}
                                @break
                            @case('Amount')
                                💰 {{ $facilitation->request }}
                                @break
                            @case('Allowance')
                                💳 {{ $facilitation->request }}
                                @break
                            @case('Airtime')
                                📱 {{ $facilitation->request }}
                                @break
                            @case('Advance')
                                💵 {{ $facilitation->request }}
                                @break
                            @default
                                {{ $facilitation->request }}
                        @endswitch
                    </span>
                </td>
                <td>
                    <strong class="text-success">KES {{ number_format($facilitation->amount, 2) }}</strong>
                </td>
                <td>
                    @if($facilitation->status == '1')
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-clock me-1"></i>Pending
                        </span>
                    @elseif($facilitation->status == '2')
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Approved
                        </span>
                    @elseif($facilitation->status == '3')
                        <span class="badge bg-danger">
                            <i class="fas fa-times me-1"></i>Rejected
                        </span>
                    @else
                        <span class="badge bg-secondary">Unknown</span>
                    @endif
                </td>
                <td>
                    <strong>{{ $facilitation->created_at->format('M d, Y') }}</strong>
                    <br><small class="text-muted">{{ $facilitation->created_at->diffForHumans() }}</small>
                    <br><small class="text-muted">{{ $facilitation->created_at->format('Y-m-d H:i:s') }}</small>
                </td>
                
                {{-- Actions column only for Managing Director and Accountant --}}
                @if(in_array(Auth::user()->role, ['Managing-Director', 'Accountant']))
                <td>
                    @if($facilitation->status == '1')
                    <div class="btn-group-vertical" role="group">
                        <button class="btn btn-success btn-sm approve-frequest mb-1" data-id="{{ $facilitation->id }}">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm reject-frequest" data-id="{{ $facilitation->id }}">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                    </div>
                    @else
                        <span class="text-muted">
                            <i class="fas fa-ban me-1"></i>No actions needed
                        </span>
                    @endif
                </td>
                @endif
            </tr>
        @empty
        <tr>
            <td colspan="@if(in_array(Auth::user()->role, ['Managing-Director', 'Accountant'])) 6 @else 5 @endif" class="text-center py-4">
                <div class="text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h5>No facilitation requests found</h5>
                    <p>Click "Send Request" to create your first facilitation request.</p>
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

        <!-- Edit Modals for each facilitation -->
        @foreach($facilitations as $facilitation)
            @if($facilitation->user_id == Auth::id() && $facilitation->status == 1)
            <div class="modal fade" id="edit-modal-{{ $facilitation->id }}" tabindex="-1" 
                 aria-labelledby="edit-modalLabel-{{ $facilitation->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="edit-modalLabel-{{ $facilitation->id }}">
                                <i class="fas fa-edit me-2"></i>Edit Request #{{ $facilitation->id }}
                            </h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="col-xl-12">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <form class="row g-3" id="editfacilitationForm-{{ $facilitation->id }}">
                                            @csrf
                                            <input type="hidden" value="{{ $facilitation->id }}" 
                                                   class="form-control" id="id-{{ $facilitation->id }}" name="id" required>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Request Type <span class="text-danger">*</span></label>
                                                <select class="form-select" id="editrequest-{{ $facilitation->id }}" name="editrequest" required>
                                                    <option disabled value="">Choose Request Type</option>
                                                    <option value="Fuel" @if($facilitation->request == 'Fuel') selected @endif>🚗 Fuel</option>
                                                    <option value="Transport" @if($facilitation->request == 'Transport') selected @endif>🚌 Transport</option>
                                                    <option value="Repairs" @if($facilitation->request == 'Repairs') selected @endif>🔧 Repairs</option>
                                                    <option value="Amount" @if($facilitation->request == 'Amount') selected @endif>💰 Amount</option>
                                                    <option value="Allowance" @if($facilitation->request == 'Allowance') selected @endif>💳 Allowance</option>
                                                    <option value="Airtime" @if($facilitation->request == 'Airtime') selected @endif>📱 Airtime</option>
                                                    <option value="Advance" @if($facilitation->request == 'Advance') selected @endif>💵 Advance</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Amount (KES) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">KES</span>
                                                    <input type="text" class="form-control" value="{{ $facilitation->amount }}" 
                                                           id="editamount-{{ $facilitation->id }}" name="editamount" required>
                                                </div>
                                                <small class="text-muted">Maximum: KES 999,999.99</small>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-1"></i> Update Request
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
                    </div>
                </div>
            </div>
            @endif
        @endforeach

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
        
        /* Table Responsive */
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        /* Form Styles */
        .form-select:focus, .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
        }
        .invalid-feedback {
            display: block;
        }
        .is-invalid {
            border-color: var(--bs-danger);
        }
        
        /* Input Group Styles */
        .input-group-text {
            background-color: var(--bs-light);
            border-color: var(--bs-border-color);
            font-weight: 600;
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        /* Empty State */
        .table tbody tr td .fa-inbox {
            color: var(--bs-gray-400);
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
        }
    </style>
    
</x-app-layout>