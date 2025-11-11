<x-app-layout>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Bulk SMS</h4>
                <p class="text-muted mb-0">Send and manage bulk SMS messages</p>
            </div>
            <div class="flex-grow-1 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#send-sms-modal">
                    <i class="fas fa-sms me-1"></i> Send Bulk SMS
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
                                <h5 class="mb-1">{{ $messages->where('status', 'pending')->count() }}</h5>
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
                                <div class="avatar-sm rounded-circle bg-info-subtle">
                                    <span class="avatar-title rounded-circle bg-info text-white">
                                        <i class="fas fa-spinner"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $messages->where('status', 'processing')->count() }}</h5>
                                <p class="text-muted mb-0 small">Processing</p>
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
                                <h5 class="mb-1">{{ $messages->where('status', 'completed')->count() }}</h5>
                                <p class="text-muted mb-0 small">Completed</p>
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
                                <h5 class="mb-1">{{ $messages->where('status', 'failed')->count() }}</h5>
                                <p class="text-muted mb-0 small">Failed</p>
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
                                <h5 class="mb-1">{{ $messages->count() }}</h5>
                                <p class="text-muted mb-0 small">Total Messages</p>
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
                                <h5 class="mb-1">{{ $messages->sum('total_sent') }}</h5>
                                <p class="text-muted mb-0 small">Total Sent</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Statistics Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-success mb-1">{{ $messages->sum('total_sent') }}</h4>
                                <p class="text-muted mb-0">Successfully Sent</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-danger mb-1">{{ $messages->sum('total_failed') }}</h4>
                                <p class="text-muted mb-0">Failed Messages</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-primary mb-1">{{ $messages->sum(function($msg) { return count($msg->recipients); }) }}</h4>
                                <p class="text-muted mb-0">Total Recipients</p>
                            </div>
                            <div class="col-md-3">
                                @php
                                    $totalAttempts = $messages->sum('total_sent') + $messages->sum('total_failed');
                                    $successRate = $totalAttempts > 0 ? number_format(($messages->sum('total_sent') / $totalAttempts) * 100, 1) : 0;
                                @endphp
                                <h4 class="text-info mb-1">{{ $successRate }}%</h4>
                                <p class="text-muted mb-0">Success Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation and Content -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs card-header-tabs" id="sms-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="target-groups-tab" data-bs-toggle="tab" data-bs-target="#target-groups-pane" type="button" role="tab">
                                    <i class="fas fa-users me-2"></i>Target Groups Overview
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sender-grouping-tab" data-bs-toggle="tab" data-bs-target="#sender-grouping-pane" type="button" role="tab">
                                    <i class="fas fa-user-tie me-2"></i>Sender Statistics
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <!-- Tab Content -->
                        <div class="tab-content" id="sms-tab-content">
                            <!-- Target Groups Tab -->
                            <div class="tab-pane fade show active" id="target-groups-pane" role="tabpanel">
                                <div class="row">
                                    @php
                                        $targetGroups = [
                                            'all' => ['icon' => '🌐', 'color' => 'dark', 'label' => 'All Clients'],
                                            'leads' => ['icon' => '👥', 'color' => 'primary', 'label' => 'Leads'],
                                            'hire_purchase' => ['icon' => '🚗', 'color' => 'success', 'label' => 'Hire Purchase'],
                                            'gentleman' => ['icon' => '🤝', 'color' => 'info', 'label' => 'Gentleman Agreement']
                                        ];
                                    @endphp
                                    
                                    @foreach($targetGroups as $group => $config)
                                        @php
                                            $groupMessages = $messages->where('target_group', $group);
                                            $groupCount = $groupMessages->count();
                                            $groupSent = $groupMessages->sum('total_sent');
                                            $groupFailed = $groupMessages->sum('total_failed');
                                        @endphp
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="d-flex align-items-center p-3 border rounded">
                                                <div class="flex-shrink-0">
                                                    <span class="fs-4">{{ $config['icon'] }}</span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">{{ $config['label'] }}</h6>
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">{{ $groupCount }} campaigns</small>
                                                        <small class="text-{{ $config['color'] }} fw-bold">{{ $groupSent }} sent</small>
                                                    </div>
                                                    @if($groupFailed > 0)
                                                        <small class="text-danger d-block">{{ $groupFailed }} failed</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Sender Statistics Tab -->
                            <div class="tab-pane fade" id="sender-grouping-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="mb-3 text-muted">
                                            <i class="fas fa-info-circle me-2"></i>Total messages sent by each user
                                        </h6>
                                        
                                        <!-- Sender Summary Cards -->
                                        <div class="row">
                                            @php
                                                $senderData = $messages->groupBy('sent_by')->map(function ($userMessages) {
                                                    $user = $userMessages->first()->user;
                                                    
                                                    return [
                                                        'user' => $user,
                                                        'total_campaigns' => $userMessages->count(),
                                                        'total_sent' => $userMessages->sum('total_sent'),
                                                        'total_failed' => $userMessages->sum('total_failed'),
                                                        'completed' => $userMessages->where('status', 'completed')->count(),
                                                        'pending' => $userMessages->where('status', 'pending')->count(),
                                                        'processing' => $userMessages->where('status', 'processing')->count(),
                                                        'failed' => $userMessages->where('status', 'failed')->count()
                                                    ];
                                                })->sortByDesc('total_sent');
                                            @endphp
                                            
                                            @if($senderData->count() > 0)
                                                @foreach($senderData as $data)
                                                    <div class="col-lg-6 col-md-6 mb-4">
                                                        <div class="card h-100 border-0 shadow-sm">
                                                            <div class="card-body">
                                                                <!-- User Header -->
                                                                <div class="d-flex align-items-center mb-3">
                                                                    <div class="avatar-sm me-3">
                                                                        <span class="avatar-title rounded-circle bg-primary text-white">
                                                                            {{ substr($data['user']->first_name ?? 'U', 0, 1) }}{{ substr($data['user']->last_name ?? 'N', 0, 1) }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-0">{{ $data['user']->first_name ?? 'Unknown' }} {{ $data['user']->last_name ?? 'User' }}</h6>
                                                                        <small class="text-muted">{{ $data['user']->email }}</small>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- SMS Summary -->
                                                                <div class="row g-2 mb-3">
                                                                    <div class="col-6">
                                                                        <div class="text-center p-2 bg-light rounded">
                                                                            <h6 class="text-success mb-0">{{ $data['total_sent'] }}</h6>
                                                                            <small class="text-muted">Messages Sent</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="text-center p-2 bg-light rounded">
                                                                            <h6 class="text-danger mb-0">{{ $data['total_failed'] }}</h6>
                                                                            <small class="text-muted">Failed</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Campaign Count Summary -->
                                                                <div class="row g-2 mb-3">
                                                                    <div class="col-3">
                                                                        <div class="text-center">
                                                                            <span class="badge bg-primary">{{ $data['total_campaigns'] }}</span>
                                                                            <small class="d-block text-muted mt-1">Total</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <div class="text-center">
                                                                            <span class="badge bg-success">{{ $data['completed'] }}</span>
                                                                            <small class="d-block text-muted mt-1">Done</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <div class="text-center">
                                                                            <span class="badge bg-info">{{ $data['processing'] }}</span>
                                                                            <small class="d-block text-muted mt-1">Running</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <div class="text-center">
                                                                            <span class="badge bg-warning">{{ $data['pending'] }}</span>
                                                                            <small class="d-block text-muted mt-1">Pending</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Success Rate Progress Bar -->
                                                                @php
                                                                    $totalAttempts = $data['total_sent'] + $data['total_failed'];
                                                                    $successRate = $totalAttempts > 0 ? ($data['total_sent'] / $totalAttempts) * 100 : 0;
                                                                @endphp
                                                                <div class="mb-2">
                                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                                        <small class="text-muted">Success Rate</small>
                                                                        <small class="text-muted">{{ number_format($successRate, 1) }}%</small>
                                                                    </div>
                                                                    <div class="progress" style="height: 6px;">
                                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                                             style="width: {{ $successRate }}%" 
                                                                             aria-valuenow="{{ $successRate }}" 
                                                                             aria-valuemin="0" aria-valuemax="100">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="col-12">
                                                    <div class="text-center py-5">
                                                        <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                                        <h5 class="text-muted">No senders found</h5>
                                                        <p class="text-muted">No bulk SMS campaigns have been sent yet.</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send SMS Modal -->
        <div class="modal fade" id="send-sms-modal" tabindex="-1" aria-labelledby="send-sms-modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="send-sms-modalLabel">
                            <i class="fas fa-sms me-2"></i>Send Bulk SMS
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-xl-12">
                            <div class="card border-0">
                                <div class="card-body">
                                    <form id="BulkSmsForm" class="row g-3">
                                        @csrf
                                        
                                        <div class="col-md-12">
                                            <label class="form-label">Target Group <span class="text-danger">*</span></label>
                                            <select class="form-select" id="target_group" name="target_group" required>
                                                <option disabled selected value="">Choose Target Group</option>
                                                <option value="all">🌐 All Clients</option>
                                                <option value="leads">👥 Leads</option>
                                                <option value="hire_purchase">🚗 Hire Purchase Clients</option>
                                                <option value="gentleman">🤝 Gentleman Agreement Clients</option>
                                            </select>
                                            <small class="text-muted" id="recipient-count"></small>
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <label class="form-label">Message <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="message" name="message" rows="5" 
                                                      placeholder="Enter your SMS message..." required maxlength="500"></textarea>
                                            <div class="d-flex justify-content-between mt-1">
                                                <small class="text-muted">Maximum 500 characters</small>
                                                <small class="text-muted"><span id="char-count">0</span>/500</small>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i> Send Bulk SMS
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
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-filter me-2"></i>Filter Messages
                        </h6>
                        
                        <div class="row g-3">
                            <!-- Status Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select class="form-select form-select-sm" id="filter-status">
                                    <option value="">All Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Processing">Processing</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Failed">Failed</option>
                                </select>
                            </div>
                            
                            <!-- Target Group Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label small text-muted mb-1">Target Group</label>
                                <select class="form-select form-select-sm" id="filter-target-group">
                                    <option value="">All Groups</option>
                                    <option value="Leads">Leads</option>
                                    <option value="Hire Purchase">Hire Purchase</option>
                                    <option value="Gentleman Agreement">Gentleman Agreement</option>
                                </select>
                            </div>
                            
                            <!-- Sender Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label small text-muted mb-1">Sender</label>
                                <select class="form-select form-select-sm" id="filter-sender">
                                    <option value="">All Senders</option>
                                    <!-- Will be populated dynamically -->
                                </select>
                            </div>
                            
                            
                            <!-- Action Buttons -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="reset-filters">
                                        <i class="fas fa-redo me-1"></i>Reset All Filters
                                    </button>
                                    <div class="text-muted">
                                        <small>
                                            Showing <strong id="showing-count">0</strong> of 
                                            <strong id="total-count">0</strong> messages
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <i class="fas fa-table me-2"></i>Bulk SMS History
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive"> 
                            <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                        <th><i class="fas fa-user me-1"></i>Sender</th>
                                        <th><i class="fas fa-users me-1"></i>Target Group</th>
                                        <th><i class="fas fa-comment me-1"></i>Message</th>
                                        <th><i class="fas fa-paper-plane me-1"></i>Sent/Failed</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                        <th><i class="fas fa-calendar me-1"></i>Date</th>
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($messages as $sms)
                                        <tr>
                                            <td><strong>{{ $sms->id }}</strong></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <span class="avatar-title rounded-circle bg-primary text-white">
                                                            {{ substr($sms->user->first_name ?? 'U', 0, 1) }}{{ substr($sms->user->last_name ?? 'N', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $sms->user->first_name ?? 'Unknown' }} {{ $sms->user->last_name ?? 'User' }}</h6>
                                                        <small class="text-muted">{{ $sms->user->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($sms->target_group)
                                                    @case('all')
                                                        <span class="badge bg-dark fs-6">🌐 All Clients</span>
                                                        @break
                                                    @case('leads')
                                                        <span class="badge bg-primary fs-6">👥 Leads</span>
                                                        @break
                                                    @case('hire_purchase')
                                                        <span class="badge bg-success fs-6">🚗 Hire Purchase</span>
                                                        @break
                                                    @case('gentleman')
                                                        <span class="badge bg-info fs-6">🤝 Gentleman Agreement</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $sms->target_group }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <div style="max-width: 300px;">
                                                    {{ Str::limit($sms->message, 80) }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold">{{ $sms->total_sent }}</span> / 
                                                <span class="text-danger fw-bold">{{ $sms->total_failed }}</span>
                                                <br>
                                                <small class="text-muted">Total: {{ count($sms->recipients) }}</small>
                                            </td>
                                            <td>
                                                @if($sms->status == 'pending')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @elseif($sms->status == 'processing')
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-spinner me-1"></i>Processing
                                                    </span>
                                                @elseif($sms->status == 'completed')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Completed
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i>Failed
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $sms->created_at->format('M d, Y') }}</strong>
                                                <br><small class="text-muted">{{ $sms->created_at->diffForHumans() }}</small>
                                                <br><small class="text-muted">{{ $sms->created_at->format('h:i A') }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <h5>No bulk SMS sent yet</h5>
                                                <p>Click "Send Bulk SMS" to create your first campaign.</p>
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

        <!-- View SMS Modal -->
        <div class="modal fade" id="view-sms-modal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-eye me-2"></i>SMS Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="sms-details-body">
                        <!-- Details will be loaded here -->
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
        
        /* Character Counter */
        #char-count {
            font-weight: bold;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .btn-group-vertical {
                display: flex;
                flex-direction: column;
                width: 100%;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
$(document).ready(function() {
    
    // Character counter for message textarea
    $('#message').on('input', function() {
        const length = $(this).val().length;
        $('#char-count').text(length);
        
        if (length > 450) {
            $('#char-count').addClass('text-danger').removeClass('text-muted');
        } else {
            $('#char-count').addClass('text-muted').removeClass('text-danger');
        }
    });
    
    // Update recipient count when target group changes
    $('#target_group').on('change', function() {
        const group = $(this).val();
        
        // Make AJAX call to get recipient count
        $.ajax({
            url: '{{ route("bulksms.recipient-count") }}',
            type: 'GET',
            data: { group: group },
            success: function(response) {
                if (response.count !== undefined) {
                    $('#recipient-count').text(`This will send to ${response.count} recipient(s)`);
                }
            },
            error: function() {
                $('#recipient-count').text('');
            }
        });
    });
    
    // Handle bulk SMS form submission
    $('#BulkSmsForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show processing state
        submitBtn.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin me-1"></i>Sending...');
        
        $.ajax({
            url: '{{ route("bulksms.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Bulk SMS queued successfully!',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    $('#BulkSmsForm')[0].reset();
                    $('#send-sms-modal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'An error occurred.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg,
                    confirmButtonColor: '#d33'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // View SMS details
    $(document).on('click', '.view-sms-btn', function() {
        const smsId = $(this).data('id');
        
        $.ajax({
            url: `/sms/${smsId}`,
            type: 'GET',
            success: function(response) {
                let html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Sender:</label>
                            <p>${response.sender_name}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Target Group:</label>
                            <p>${response.target_group_label}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Total Recipients:</label>
                            <p>${response.total_recipients}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Status:</label>
                            <p>${response.status_badge}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Sent:</label>
                            <p class="text-success">${response.total_sent}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Failed:</label>
                            <p class="text-danger">${response.total_failed}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="fw-bold">Message:</label>
                            <div class="p-3 bg-light rounded">
                                ${response.message}
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold">Sent At:</label>
                            <p>${response.created_at}</p>
                        </div>
                    </div>
                `;
                
                $('#sms-details-body').html(html);
                $('#view-sms-modal').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load SMS details',
                    confirmButtonColor: '#d33'
                });
            }
        });
    });
    
    // Reset modal when closed
    $('#send-sms-modal').on('hidden.bs.modal', function() {
        $('#BulkSmsForm')[0].reset();
        $('#char-count').text('0');
        $('#recipient-count').text('');
    });
});
</script>

<script>
$(document).ready(function() {
    console.log('=== SMS FILTER INITIALIZATION STARTED ===');
    
    let table;
    let filterSearchFunction = null;
    
    // Initialize filters
    function initializeFilters() {
        if ($.fn.DataTable.isDataTable('#responsive-datatable')) {
            table = $('#responsive-datatable').DataTable();
            console.log('DataTable found, setting up filters...');
            setupFilters();
        } else {
            console.log('DataTable not ready, waiting...');
            setTimeout(initializeFilters, 100);
        }
    }
    
    function setupFilters() {
        // Populate sender dropdown
        populateSenderFilter();
        updateCounts();
        
        // Remove any existing search functions first
        if (filterSearchFunction !== null) {
            const index = $.fn.dataTable.ext.search.indexOf(filterSearchFunction);
            if (index > -1) {
                $.fn.dataTable.ext.search.splice(index, 1);
            }
        }
        
        // Define the filter function
        filterSearchFunction = function(settings, data, dataIndex) {
            const statusFilter = $('#filter-status').val();
            const groupFilter = $('#filter-target-group').val();
            const senderFilter = $('#filter-sender').val();
            const dateRangeFilter = $('#filter-date-range').val();
            
            const row = table.row(dataIndex).node();
            const $row = $(row);
            
            // Get sender name
            const senderName = $row.find('td:eq(1) h6').text().trim();
            
            // Get target group
            const groupBadge = $row.find('td:eq(2) .badge').text().trim();
            const targetGroup = groupBadge.replace(/[^\w\s-]/g, '').trim();
            
            // Get status
            const statusBadge = $row.find('td:eq(5) .badge');
            let status = '';
            if (statusBadge.length > 0) {
                const badgeText = statusBadge.text().trim();
                if (badgeText.includes('Pending')) status = 'Pending';
                else if (badgeText.includes('Processing')) status = 'Processing';
                else if (badgeText.includes('Completed')) status = 'Completed';
                else if (badgeText.includes('Failed')) status = 'Failed';
            }
            
            // Get date
            const dateText = $row.find('td:eq(6) small:last').text().trim();
            
            // Status filter
            if (statusFilter && status !== statusFilter) {
                return false;
            }
            
            // Target group filter
            if (groupFilter && targetGroup !== groupFilter) {
                return false;
            }
            
            // Sender filter
            if (senderFilter && senderName !== senderFilter) {
                return false;
            }
            
            // Date range filter
            if (dateRangeFilter && dateRangeFilter !== '') {
                const rowDate = new Date(dateText);
                
                if (isNaN(rowDate.getTime())) {
                    return false;
                }
                
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                let passDateFilter = true;
                
                switch(dateRangeFilter) {
                    case 'today':
                        passDateFilter = rowDate >= today;
                        break;
                    case 'yesterday':
                        const yesterday = new Date(today);
                        yesterday.setDate(yesterday.getDate() - 1);
                        passDateFilter = (rowDate >= yesterday && rowDate < today);
                        break;
                    case 'last7days':
                        const last7 = new Date(today);
                        last7.setDate(last7.getDate() - 7);
                        passDateFilter = rowDate >= last7;
                        break;
                    case 'last30days':
                        const last30 = new Date(today);
                        last30.setDate(last30.getDate() - 30);
                        passDateFilter = rowDate >= last30;
                        break;
                    case 'thismonth':
                        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                        passDateFilter = rowDate >= monthStart;
                        break;
                    case 'lastmonth':
                        const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 1);
                        passDateFilter = (rowDate >= lastMonthStart && rowDate < lastMonthEnd);
                        break;
                }
                
                if (!passDateFilter) {
                    return false;
                }
            }
            
            return true;
        };
        
        // Add the filter function
        $.fn.dataTable.ext.search.push(filterSearchFunction);
        
        // Apply filters on change
        $('#filter-status, #filter-target-group, #filter-sender, #filter-date-range').on('change', function() {
            table.draw();
        });
        
        // Reset all filters
        $('#reset-filters').on('click', function() {
            $('#filter-status').val('');
            $('#filter-target-group').val('');
            $('#filter-sender').val('');
            $('#filter-date-range').val('');
            table.draw();
        });
        
        // Update counts after draw
        table.on('draw', function() {
            updateCounts();
        });
        
        console.log('✅ Filters setup complete');
    }
    
    // Populate sender dropdown
    function populateSenderFilter() {
        const senders = new Set();
        
        table.rows().every(function() {
            const row = this.node();
            const $row = $(row);
            const senderName = $row.find('td:eq(1) h6').text().trim();
            if (senderName && senderName !== '') {
                senders.add(senderName);
            }
        });
        
        const $select = $('#filter-sender');
        $select.find('option:not(:first)').remove();
        
        const sortedSenders = Array.from(senders).sort();
        sortedSenders.forEach(sender => {
            $select.append(`<option value="${sender}">${sender}</option>`);
        });
        
        console.log('📋 Populated sender dropdown with', senders.size, 'senders');
    }
    
    // Update count display
    function updateCounts() {
        if (table) {
            const info = table.page.info();
            const showing = info.recordsDisplay;
            const total = info.recordsTotal;
            
            $('#showing-count').text(showing);
            $('#total-count').text(total);
        }
    }
    
    // Start initialization
    initializeFilters();
});
</script>
</x-app-layout>