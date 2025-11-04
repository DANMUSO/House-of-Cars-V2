<x-app-layout>
    <div class="container-fluid">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Leave Management</h4>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLeaveModal">
                    <i class="fas fa-plus me-1"></i> Create Leave Request
                </button>
            </div>
        </div>

        <!-- Leave Balance Cards -->
        @if(isset($userLeaveBalances) && $userLeaveBalances->count() > 0)
        <div class="row mb-4">
            @foreach($userLeaveBalances as $leaveType => $balance)
            @php
                // Format leave type display name for cards
                $cardDisplayName = str_replace(['_', 'Leave'], [' ', ''], $leaveType);
                
                // Special formatting for Maternity/Paternity Leave
                if ($leaveType === 'Maternity/Paternity Leave') {
                    if ($balance->total_days == 14) {
                        $cardDisplayName = 'Paternity';
                    } elseif ($balance->total_days == 90) {
                        $cardDisplayName = 'Maternity';
                    } else {
                        $cardDisplayName = 'Maternity/Paternity';
                    }
                }
            @endphp
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title text-capitalize mb-2">{{ $cardDisplayName }}</h6>
                        <h3 class="text-primary mb-1">{{ $balance->remaining_days }}<small class="text-muted">/{{ $balance->total_days }}</small></h3>
                        <small class="text-muted">Days Remaining/Total</small>
                        <div class="progress mt-2" style="height: 6px;">
                            @php
                                $percentage = $balance->total_days > 0 ? ($balance->remaining_days / $balance->total_days) * 100 : 0;
                                $progressClass = $percentage > 50 ? 'bg-success' : ($percentage > 25 ? 'bg-warning' : 'bg-danger');
                            @endphp
                            <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                 style="width: {{ $percentage }}%"
                                 aria-valuenow="{{ $balance->remaining_days }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $balance->total_days }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Leave Balances Loading...</strong> 
                    Your leave balances are being set up. If this message persists, please contact HR.
                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="location.reload()">
                        <i class="fas fa-refresh me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
        @endif
<!-- Add this right before <div class="row"> containing the table -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Applications</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchFilter" placeholder="Search employee, email...">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="statusFilterSelect">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="leaveTypeFilterSelect">
                            <option value="">All Types</option>
                            <option value="Annual">Annual</option>
                            <option value="Sick">Sick</option>
                            <option value="Maternity">Maternity/Paternity</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="dateFromFilter" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="dateToFilter" placeholder="To">
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-secondary w-100" id="clearFiltersBtn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-2 d-flex justify-content-between">
                    <small class="text-muted" id="filterInfo">Showing all applications</small>
                    <button class="btn btn-success btn-sm" id="exportBtn">
                        <i class="fas fa-file-excel me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
        <!-- Leave Applications Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Leave Applications</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm active" onclick="filterTable('all')">All</button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="filterTable('Pending')">Pending</button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="filterTable('Approved')">Approved</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="filterTable('Rejected')">Rejected</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="filterTable('Cancelled')">Cancelled</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                           <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Total Days</th>
            <th>Handover Person</th>
            <th>Status</th>
            <th>Applied Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leaveApplications as $application)
        <tr data-status="{{ $application->status }}" id="application-row-{{ $application->id }}">
            <td>{{ $application->id }}</td>
            <td>
                <div class="fw-semibold">{{ $application->user->first_name }} {{ $application->user->last_name }}</div>
                <small class="text-muted">{{ $application->user->email }}</small>
            </td>
            <td>
                <span class="badge bg-info text-capitalize">
                    {{ str_replace('_', ' ', $application->leave_type) }}
                </span>
            </td>
            <td>{{ \Carbon\Carbon::parse($application->start_date)->format('M d, Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($application->end_date)->format('M d, Y') }}</td>
            <td>
                <span class="fw-semibold">{{ $application->total_days }}</span> 
                <small class="text-muted">working days</small>
            </td>
            <td>{{ $application->handover_person ?? 'N/A' }}</td>
            <td>
                <span id="status-badge-{{ $application->id }}">
                    @switch($application->status)
                        @case('Pending')
                            <span class="badge bg-warning">Pending</span>
                            @break
                        @case('Approved')
                            <span class="badge bg-success">Approved</span>
                            @break
                        @case('Rejected')
                            <span class="badge bg-danger">Rejected</span>
                            @break
                        @case('Cancelled')
                            <span class="badge bg-secondary">Cancelled</span>
                            @break
                    @endswitch
                </span>
            </td>
            <td>{{ $application->applied_date->format('M d, Y') }}</td>
            
            {{-- Actions column for all users --}}
            <td>
                <div class="btn-group-vertical" id="actions-{{ $application->id }}">
                    @if($application->status === 'Pending')
                        {{-- Approve/Reject buttons only for Managing Director and HR, but not for their own applications --}}
                        @if(in_array(Auth::user()->role, ['Managing-Director','General-Manager', 'HR']) && $application->user_id !== auth()->id())
                            <button class="btn btn-sm btn-success mb-1" onclick="approveApplication({{ $application->id }})" id="approve-btn-{{ $application->id }}">
                                <i class="fas fa-check me-1"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger mb-1" onclick="rejectApplication({{ $application->id }})" id="reject-btn-{{ $application->id }}">
                                <i class="fas fa-times me-1"></i> Reject
                            </button>
                        @endif
                        
                        {{-- Cancel button only for own PENDING applications --}}
                        @if($application->user_id === auth()->id())
                            <button class="btn btn-sm btn-warning" onclick="cancelApplication({{ $application->id }})" id="cancel-btn-{{ $application->id }}">
                                <i class="fas fa-ban me-1"></i> Cancel
                            </button>
                        @endif
                        
                        {{-- Show "No actions" if not their application and not Managing Director/HR, OR if it's HR/MD's own application --}}
                        @if(($application->user_id !== auth()->id() && !in_array(Auth::user()->role, ['Managing-Director','General-Manager', 'HR'])) || 
                            (in_array(Auth::user()->role, ['Managing-Director','General-Manager', 'HR']) && $application->user_id === auth()->id()))
                            <span class="text-muted">
                                <i class="fas fa-ban me-1"></i>@if($application->user_id === auth()->id() && in_array(Auth::user()->role, ['Managing-Director','General-Manager', 'HR']))Cannot approve own request @else No actions available @endif
                            </span>
                        @endif
                    @else
                        {{-- No actions for approved, rejected, or cancelled applications --}}
                        <span class="text-muted">
                            <i class="fas fa-ban me-1"></i>No actions needed
                        </span>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <br>No leave applications found
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
    </div>

    <!-- Create Leave Application Modal -->
    <div class="modal fade" id="createLeaveModal" tabindex="-1" aria-labelledby="createLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createLeaveModalLabel">Create Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createLeaveForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leave_type" class="form-label">Leave Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="leave_type" name="leave_type" required>
                                        <option value="">Select Leave Type</option>
                                        @if($userLeaveBalances && $userLeaveBalances->count() > 0)
                                            @foreach($userLeaveBalances as $leaveType => $balance)
                                                @php
                                                    // Format leave type display name
                                                    $displayName = str_replace(['_', 'Leave'], [' ', ''], $leaveType);
                                                    
                                                    // Special formatting for Maternity/Paternity Leave
                                                    if ($leaveType === 'Maternity/Paternity Leave') {
                                                        if ($balance->total_days == 14) {
                                                            $displayName = 'Paternity Leave';
                                                        } elseif ($balance->total_days == 90) {
                                                            $displayName = 'Maternity Leave';
                                                        } else {
                                                            $displayName = 'Maternity/Paternity Leave';
                                                        }
                                                    }
                                                @endphp
                                                <option value="{{ $leaveType }}" data-remaining="{{ $balance->remaining_days }}" data-total="{{ $balance->total_days }}">
                                                    {{ $displayName }} ({{ $balance->remaining_days }}/{{ $balance->total_days }} days)
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="Annual Leave">Annual Leave (Loading...)</option>
                                            <option value="Sick Leave">Sick Leave (Loading...)</option>
                                            <option value="Maternity/Paternity Leave">Maternity/Paternity Leave (Loading...)</option>
                                            <option value="Emergency Leave">Emergency Leave (Loading...)</option>
                                        @endif
                                    </select>
                                    <div class="form-text">Select the type of leave you want to apply for</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="handover_person" class="form-label">Handover Person <span class="text-danger">*</span></label>
                                    <select class="form-select" id="handover_person" name="handover_person" required>
                                        <option value="">Select Handover Person</option>
                                        @if(isset($handoverSuggestions) && $handoverSuggestions->count() > 0)
                                            @php
                                                // Since controller now returns only one level, we can group by role for display
                                                $roleGroups = $handoverSuggestions->groupBy('role');
                                                
                                                // Get the suggestion level (all will be the same now)
                                                $currentLevel = $handoverSuggestions->first()->suggestion_level ?? 'primary';
                                                
                                                // Level labels for user info
                                                $levelLabels = [
                                                    'primary' => 'Same Role',
                                                    'secondary' => 'Related Roles', 
                                                    'tertiary' => 'Other Roles',
                                                    'fallback' => 'Available Users'
                                                ];
                                            @endphp
                                            
                                            @foreach($roleGroups as $role => $users)
                                                @if($roleGroups->count() > 1)
                                                    {{-- Show role optgroup only if multiple roles in this level --}}
                                                    <optgroup label="{{ str_replace('-', ' ', $role) }}">
                                                        @foreach($users as $suggestion)
                                                            <option value="{{ $suggestion->name }}" data-role="{{ $suggestion->role }}" data-email="{{ $suggestion->email }}">
                                                                {{ $suggestion->name }} ({{ $suggestion->email }})
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @else
                                                    {{-- Single role, no optgroup needed --}}
                                                    @foreach($users as $suggestion)
                                                        <option value="{{ $suggestion->name }}" data-role="{{ $suggestion->role }}" data-email="{{ $suggestion->email }}">
                                                            {{ $suggestion->name }} ({{ $suggestion->email }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @else
                                            <option value="" disabled>No handover suggestions available</option>
                                        @endif
                                    </select>
                                    
                                    <div class="form-text" id="handover_suggestion_text">
                                        @if(isset($handoverSuggestions) && $handoverSuggestions->count() > 0)
                                            @php
                                                $currentLevel = $handoverSuggestions->first()->suggestion_level ?? 'primary';
                                                $userRole = auth()->user()->role ?? 'Support-Staff';
                                            @endphp
                                            <i class="fas fa-info-circle me-1"></i>
                                            @if($currentLevel === 'primary')
                                                Showing colleagues with your same role ({{ str_replace('-', ' ', $userRole) }})
                                            @elseif($currentLevel === 'secondary') 
                                                No same-role colleagues available. Showing related roles
                                            @elseif($currentLevel === 'tertiary')
                                                No primary/secondary colleagues available. Showing other roles
                                            @else
                                                Showing all available users
                                            @endif
                                        @else
                                            <i class="fas fa-info-circle me-1"></i>Select the person who will handle your duties
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Leave Balance Display (will be populated by JavaScript) -->
                        <div id="balance_display_container"></div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required min="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Calculated Working Days:</strong> <span id="calculated_days" class="fw-bold text-primary">0</span>
                                <small class="d-block mt-1 text-muted">Sundays are automatically excluded from the calculation</small>
                            </div>
                        </div>
                        
                        <!-- Balance Warning Container -->
                        <div id="balance_warning_container"></div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Duties/Tasks handed over <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Duties/Tasks handed over" required maxlength="1000"></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitLeaveBtn">Submit Request</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Application Modal -->
    <div class="modal fade" id="viewApplicationModal" tabindex="-1" aria-labelledby="viewApplicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewApplicationModalLabel">Leave Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewApplicationContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .badge {
            font-size: 0.75em;
        }
        
        .btn-group-vertical .btn {
            border-radius: 0.375rem !important;
            margin-bottom: 0.25rem;
        }
        
        .btn-group-vertical .btn:last-child {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0,0,0,.02);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }
        
        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-right: 0.25rem;
        }
        
        .btn-group .btn.active {
            transform: scale(0.98);
            box-shadow: inset 0 2px 4px rgba(0,0,0,.1);
        }
        
        .modal-lg {
            max-width: 900px;
        }
        
        .text-capitalize {
            text-transform: capitalize;
        }
        
        #calculated_days {
            color: #0d6efd !important;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .balance-warning {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .progress-bar {
            transition: width 0.3s ease;
        }
        
        .custom-alert {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Action buttons loading state */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Improve mobile responsiveness */
        @media (max-width: 768px) {
            .modal-lg {
                max-width: 95%;
                margin: 1rem auto;
            }
            
            .btn-group {
                flex-wrap: wrap;
            }
            
            .btn-group .btn {
                margin-bottom: 0.25rem;
            }
            
            .btn-group-vertical .btn {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
        }
        
        /* Loading states */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        /* Enhanced form styling */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            transform: translateY(-1px);
        }
        
        /* Progress bar animations */
        .progress {
            background-color: #e9ecef;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .progress-bar {
            border-radius: 0.5rem;
            transition: width 0.6s ease;
        }
    </style>
    @endpush

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Global functions - declared in global scope
        window.submitLeaveApplication = function() {
            const form = document.getElementById('createLeaveForm');
            const submitBtn = document.getElementById('submitLeaveBtn');
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Check if requesting more days than available
            const leaveTypeSelect = document.getElementById('leave_type');
            const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
            const requestedDays = parseInt(document.getElementById('calculated_days').textContent);
            
            if (selectedOption.dataset.remaining !== undefined) {
                const remaining = parseInt(selectedOption.dataset.remaining);
                if (requestedDays > remaining) {
                    Swal.fire({
                        title: 'Insufficient Leave Balance',
                        text: `You are requesting ${requestedDays} days but only have ${remaining} days remaining.`,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
            }
            
            // Show confirmation dialog
            Swal.fire({
                title: 'Submit Leave Application?',
                html: `
                    <div class="text-start">
                        <p><strong>Leave Type:</strong> ${document.getElementById('leave_type').options[document.getElementById('leave_type').selectedIndex].text.split('(')[0].trim()}</p>
                        <p><strong>Duration:</strong> ${document.getElementById('start_date').value} to ${document.getElementById('end_date').value}</p>
                        <p><strong>Working Days:</strong> ${requestedDays} days</p>
                        <p><strong>Handover Person:</strong> ${document.getElementById('handover_person').options[document.getElementById('handover_person').selectedIndex].text}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    performSubmission();
                }
            });
        };

        function performSubmission() {
            const form = document.getElementById('createLeaveForm');
            const submitBtn = document.getElementById('submitLeaveBtn');
            const formData = new FormData(form);
            
            // Disable submit button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            
            // Add calculated days to form data
            formData.append('total_days', document.getElementById('calculated_days').textContent);
            
            fetch('/leave-applications', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your leave application has been submitted successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        // Close modal and refresh page
                        bootstrap.Modal.getInstance(document.getElementById('createLeaveModal')).hide();
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Something went wrong');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to submit leave application. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Request';
            });
        }

        // Other global functions
        window.filterTable = function(status) {
            const table = document.getElementById('leaveTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            const buttons = document.querySelectorAll('.btn-group .btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter rows
            for (let row of rows) {
                if (status === 'all') {
                    row.style.display = '';
                } else {
                    const rowStatus = row.getAttribute('data-status');
                    row.style.display = rowStatus === status ? '' : 'none';
                }
            }
        };

        window.viewApplication = function(applicationId) {
            const modal = new bootstrap.Modal(document.getElementById('viewApplicationModal'));
            const content = document.getElementById('viewApplicationContent');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading application details...</p>
                </div>
            `;
            
            modal.show();
            
            fetch(`/leave-applications/${applicationId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const app = data.application;
                    content.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Employee</label>
                                    <p class="form-control-plaintext">${app.user.name} (${app.user.email})</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Leave Type</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-info">${app.leave_type.replace('_', ' ')}</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Duration</label>
                                    <p class="form-control-plaintext">${app.start_date} to ${app.end_date}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Total Days</label>
                                    <p class="form-control-plaintext">${app.total_days} working days</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Handover Person</label>
                                    <p class="form-control-plaintext">${app.handover_person || 'N/A'}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-${getStatusColor(app.status)}">${app.status}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Reason</label>
                                    <p class="form-control-plaintext border rounded p-3 bg-light">${app.reason}</p>
                                </div>
                                ${app.comments ? `
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Comments</label>
                                    <p class="form-control-plaintext border rounded p-3 bg-light">${app.comments}</p>
                                </div>
                                ` : ''}
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Applied: ${app.applied_date}</small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">Updated: ${app.updated_at}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    throw new Error(data.message || 'Failed to load application details');
                }
            })
            .catch(error => {
                content.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                        <p class="text-danger">Failed to load application details</p>
                        <button class="btn btn-outline-secondary" onclick="viewApplication(${applicationId})">Retry</button>
                    </div>
                `;
            });
        };

        function getStatusColor(status) {
            switch(status) {
                case 'Pending': return 'warning';
                case 'Approved': return 'success';
                case 'Rejected': return 'danger';
                case 'Cancelled': return 'secondary';
                default: return 'secondary';
            }
        }

        // Direct action functions without modals
        window.approveApplication = function(applicationId) {
            const button = document.getElementById(`approve-btn-${applicationId}`);
            const rejectButton = document.getElementById(`reject-btn-${applicationId}`);
            
            // Show confirmation
            Swal.fire({
                title: 'Approve Leave Application?',
                text: 'Are you sure you want to approve this leave application?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable buttons and show loading
                    button.disabled = true;
                    button.classList.add('btn-loading');
                    if (rejectButton) {
                        rejectButton.disabled = true;
                    }
                    
                    fetch(`/leave-applications/${applicationId}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update status badge
                            const statusBadge = document.getElementById(`status-badge-${applicationId}`);
                            statusBadge.innerHTML = '<span class="badge bg-success">Approved</span>';
                            
                            // Update row data attribute
                            const row = document.getElementById(`application-row-${applicationId}`);
                            row.setAttribute('data-status', 'Approved');
                            
                            // Remove action buttons except view
                            const actionsContainer = document.getElementById(`actions-${applicationId}`);
                            actionsContainer.innerHTML = `
                                <button class="btn btn-sm btn-outline-primary" onclick="viewApplication(${applicationId})">
                                    <i class="fas fa-ban me-1"></i>No actions needed
                                </button>
                            `;
                            
                            // Show success message
                            showToast('Leave application approved successfully!', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to approve application');
                        }
                    })
                    .catch(error => {
                        // Re-enable buttons
                        button.disabled = false;
                        button.classList.remove('btn-loading');
                        if (rejectButton) {
                            rejectButton.disabled = false;
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to approve leave application. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        };

        window.rejectApplication = function(applicationId) {
            const button = document.getElementById(`reject-btn-${applicationId}`);
            const approveButton = document.getElementById(`approve-btn-${applicationId}`);
            
            // Show confirmation with reason input
            Swal.fire({
                title: 'Reject Leave Application?',
                html: `
                    <p class="mb-3">Are you sure you want to reject this leave application?</p>
                    <textarea id="rejection-reason" class="form-control" placeholder="Please provide a reason for rejection (optional)" rows="3"></textarea>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                preConfirm: () => {
                    return document.getElementById('rejection-reason').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable buttons and show loading
                    button.disabled = true;
                    button.classList.add('btn-loading');
                    if (approveButton) {
                        approveButton.disabled = true;
                    }
                    
                    fetch(`/leave-applications/${applicationId}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            comments: result.value || ''
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update status badge
                            const statusBadge = document.getElementById(`status-badge-${applicationId}`);
                            statusBadge.innerHTML = '<span class="badge bg-danger">Rejected</span>';
                            
                            // Update row data attribute
                            const row = document.getElementById(`application-row-${applicationId}`);
                            row.setAttribute('data-status', 'Rejected');
                            
                            // Remove action buttons except view
                            const actionsContainer = document.getElementById(`actions-${applicationId}`);
                            actionsContainer.innerHTML = `
                                <button class="btn btn-sm btn-outline-primary" onclick="viewApplication(${applicationId})">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                            `;
                            
                            // Show success message
                            showToast('Leave application rejected successfully!', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to reject application');
                        }
                    })
                    .catch(error => {
                        // Re-enable buttons
                        button.disabled = false;
                        button.classList.remove('btn-loading');
                        if (approveButton) {
                            approveButton.disabled = false;
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to reject leave application. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        };

        window.cancelApplication = function(applicationId) {
            const button = document.getElementById(`cancel-btn-${applicationId}`);
            
            // Show confirmation
            Swal.fire({
                title: 'Cancel Leave Application?',
                text: 'Are you sure you want to cancel this leave application?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No, Keep It',
                confirmButtonColor: '#f0ad4e',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading
                    button.disabled = true;
                    button.classList.add('btn-loading');
                    
                    fetch(`/leave-applications/${applicationId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update status badge
                            const statusBadge = document.getElementById(`status-badge-${applicationId}`);
                            statusBadge.innerHTML = '<span class="badge bg-secondary">Cancelled</span>';
                            
                            // Update row data attribute
                            const row = document.getElementById(`application-row-${applicationId}`);
                            row.setAttribute('data-status', 'Cancelled');
                            
                            // Remove action buttons except view
                            const actionsContainer = document.getElementById(`actions-${applicationId}`);
                            actionsContainer.innerHTML = `
                                <button class="btn btn-sm btn-outline-primary" onclick="viewApplication(${applicationId})">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                            `;
                            
                            // Show success message
                            showToast('Leave application cancelled successfully!', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to cancel application');
                        }
                    })
                    .catch(error => {
                        // Re-enable button
                        button.disabled = false;
                        button.classList.remove('btn-loading');
                        
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to cancel leave application. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        };

        // DOM ready functions
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form event listeners
            initializeFormHandlers();
            initializeDateCalculation();
            
            // Add event listener to submit button
            document.getElementById('submitLeaveBtn').addEventListener('click', function(e) {
                e.preventDefault();
                submitLeaveApplication();
            });
        });

        function initializeFormHandlers() {
            // Handle leave type change to show balance
            document.getElementById('leave_type').addEventListener('change', function() {
                updateLeaveBalance();
            });
            
            // Handle date changes for calculation
            document.getElementById('start_date').addEventListener('change', calculateWorkingDays);
            document.getElementById('end_date').addEventListener('change', calculateWorkingDays);
        }

        function initializeDateCalculation() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').setAttribute('min', today);
            
            // Update end date minimum when start date changes
            document.getElementById('start_date').addEventListener('change', function() {
                const startDate = this.value;
                document.getElementById('end_date').setAttribute('min', startDate);
                calculateWorkingDays();
            });
        }

        function updateLeaveBalance() {
            const leaveTypeSelect = document.getElementById('leave_type');
            const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
            const balanceContainer = document.getElementById('balance_display_container');
            
            if (selectedOption.value && selectedOption.dataset.remaining !== undefined) {
                const remaining = selectedOption.dataset.remaining;
                const total = selectedOption.dataset.total;
                const leaveTypeName = selectedOption.text.split('(')[0].trim();
                
                balanceContainer.innerHTML = `
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <div>
                                <strong>${leaveTypeName} Balance:</strong> 
                                <span class="text-primary fw-bold">${remaining}/${total} days remaining</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                balanceContainer.innerHTML = '';
            }
            
            // Recalculate if dates are selected
            calculateWorkingDays();
        }

        function calculateWorkingDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const calculatedDaysSpan = document.getElementById('calculated_days');
            const warningContainer = document.getElementById('balance_warning_container');
            
            if (startDate && endDate) {
                if (new Date(endDate) < new Date(startDate)) {
                    calculatedDaysSpan.textContent = '0';
                    showBalanceWarning('End date cannot be earlier than start date', 'danger');
                    return;
                }
                
                const workingDays = getWorkingDaysBetween(new Date(startDate), new Date(endDate));
                calculatedDaysSpan.textContent = workingDays;
                
                // Check balance
                checkLeaveBalance(workingDays);
            } else {
                calculatedDaysSpan.textContent = '0';
                warningContainer.innerHTML = '';
            }
        }

        function getWorkingDaysBetween(startDate, endDate) {
    let count = 0;
    let currentDate = new Date(startDate);
    
    while (currentDate <= endDate) {
        const dayOfWeek = currentDate.getDay();
        // Only exclude Sunday (0) - Saturday (6) is now a working day
        if (dayOfWeek !== 0) {
            count++;
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    return count;
}

        function checkLeaveBalance(requestedDays) {
            const leaveTypeSelect = document.getElementById('leave_type');
            const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
            
            if (selectedOption.value && selectedOption.dataset.remaining !== undefined) {
                const remaining = parseInt(selectedOption.dataset.remaining);
                
                if (requestedDays > remaining) {
                    showBalanceWarning(
                        `Insufficient balance! You are requesting ${requestedDays} days but only have ${remaining} days remaining.`,
                        'danger'
                    );
                } else if (requestedDays === remaining) {
                    showBalanceWarning(
                        `You are using all your remaining ${remaining} days for this leave type.`,
                        'warning'
                    );
                } else {
                    showBalanceWarning(
                        `After this leave, you will have ${remaining - requestedDays} days remaining.`,
                        'success'
                    );
                }
            }
        }

        function showBalanceWarning(message, type) {
            const warningContainer = document.getElementById('balance_warning_container');
            const alertClass = type === 'danger' ? 'alert-danger' : (type === 'warning' ? 'alert-warning' : 'alert-success');
            const iconClass = type === 'danger' ? 'fa-exclamation-triangle' : (type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle');
            
            warningContainer.innerHTML = `
                <div class="alert ${alertClass} balance-warning mb-3">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
                </div>
            `;
        }

        // Reset form when modal is hidden
        document.getElementById('createLeaveModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('createLeaveForm').reset();
            document.getElementById('balance_display_container').innerHTML = '';
            document.getElementById('balance_warning_container').innerHTML = '';
            document.getElementById('calculated_days').textContent = '0';
        });

        // Handle form validation feedback
        document.getElementById('createLeaveForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitLeaveApplication();
        });

        // Utility function for toast notifications
        function showToast(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
        // Add this after the table in your blade file (before existing scripts)

// HTML Filters Section - Add before the table
const filtersHTML = `
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Applications</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchFilter" placeholder="Search employee, email...">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="statusFilterSelect">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="leaveTypeFilterSelect">
                            <option value="">All Types</option>
                            <option value="Annual Leave">Annual</option>
                            <option value="Sick Leave">Sick</option>
                            <option value="Maternity/Paternity Leave">Maternity/Paternity</option>
                            <option value="Emergency Leave">Emergency</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="dateFromFilter">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="dateToFilter">
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-secondary w-100" id="clearFiltersBtn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="text-muted" id="filterInfo">Showing all applications</small>
                        <button class="btn btn-success btn-sm float-end" id="exportBtn">
                            <i class="fas fa-file-excel me-1"></i>Export to Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>`;

// Initialize DataTable with filters
$(document).ready(function() {
    var table = $('#responsive-datatable').DataTable({
        responsive: false,
        scrollX: true,
        pageLength: 25,
        order: [[8, 'desc']],
        columnDefs: [
            { orderable: false, targets: [9] },
            { responsivePriority: 1, targets: [1, 7, 9] }
        ]
    });

    // Custom filter function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var searchVal = $('#searchFilter').val().toLowerCase();
        var statusVal = $('#statusFilterSelect').val().toLowerCase();
        var typeVal = $('#leaveTypeFilterSelect').val();
        var dateFrom = $('#dateFromFilter').val();
        var dateTo = $('#dateToFilter').val();

        var $row = $(table.row(dataIndex).node());
        
        // Search filter
        if (searchVal && !data[1].toLowerCase().includes(searchVal)) {
            return false;
        }

        // Status filter
        if (statusVal) {
            var rowStatus = $row.find('td:eq(7) .badge').text().toLowerCase().trim();
            if (!rowStatus.includes(statusVal)) return false;
        }

        // Leave type filter
        if (typeVal) {
            var rowType = $row.find('td:eq(2) .badge').text().trim();
            if (!rowType.includes(typeVal)) return false;
        }

        // Date range filter
        if (dateFrom || dateTo) {
            var startDate = new Date(data[3]);
            if (dateFrom && startDate < new Date(dateFrom)) return false;
            if (dateTo && startDate > new Date(dateTo)) return false;
        }

        return true;
    });

    // Apply filters
    function applyFilters() {
        table.draw();
        updateFilterInfo();
    }

    function updateFilterInfo() {
        var visible = table.rows({ search: 'applied' }).count();
        var total = table.rows().count();
        $('#filterInfo').text(`Showing ${visible} of ${total} applications`);
    }

    // Event handlers
    $('#searchFilter').on('input', debounce(applyFilters, 300));
    $('#statusFilterSelect, #leaveTypeFilterSelect, #dateFromFilter, #dateToFilter').on('change', applyFilters);
    
    $('#clearFiltersBtn').on('click', function() {
        $('#searchFilter, #dateFromFilter, #dateToFilter').val('');
        $('#statusFilterSelect, #leaveTypeFilterSelect').val('');
        applyFilters();
    });

    // Export function
    $('#exportBtn').on('click', function() {
        var data = [];
        var headers = ['ID', 'Employee', 'Email', 'Leave Type', 'Start Date', 'End Date', 
                      'Days', 'Handover Person', 'Status', 'Applied Date'];
        
        data.push(headers);
        
        table.rows({ search: 'applied' }).every(function() {
            var $row = $(this.node());
            var rowData = [
                $row.find('td:eq(0)').text(),
                $row.find('td:eq(1) .fw-semibold').text(),
                $row.find('td:eq(1) small').text(),
                $row.find('td:eq(2) .badge').text().trim(),
                $row.find('td:eq(3)').text(),
                $row.find('td:eq(4)').text(),
                $row.find('td:eq(5) .fw-semibold').text(),
                $row.find('td:eq(6)').text(),
                $row.find('td:eq(7) .badge').text().trim(),
                $row.find('td:eq(8)').text()
            ];
            data.push(rowData);
        });

        // Create CSV
        var csv = data.map(row => row.join(',')).join('\n');
        var blob = new Blob([csv], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = `leave_applications_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        
        toastr.success('Export completed successfully!');
    });

    function debounce(func, wait) {
        var timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(func, wait);
        };
    }

    updateFilterInfo();
});

// Add this CSS
const styles = `
<style>
    #responsive-datatable_wrapper .row:first-child {
        margin-bottom: 1rem;
    }
    .dataTables_filter {
        display: none;
    }
</style>`;
    </script>
</x-app-layout>