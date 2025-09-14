<x-app-layout>
<div class="container-fluid">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Users Management</h4>
        </div>
        <div class="flex-grow-1 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                <i class="fas fa-plus me-2"></i>Create User
            </button>
        </div>
    </div>

    <!-- Director Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary-subtle">
                                <span class="avatar-title rounded-circle bg-primary text-white">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $users->where('role', 'Managing-Director')->where('deleted_at', null)->count() }}</h5>
                            <p class="text-muted mb-0 small">Managing Directors</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success-subtle">
                                <span class="avatar-title rounded-circle bg-success text-white">
                                    <i class="fas fa-calculator"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $users->where('role', 'Accountant')->where('deleted_at', null)->count() }}</h5>
                            <p class="text-muted mb-0 small">Accountants</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning-subtle">
                                <span class="avatar-title rounded-circle bg-warning text-white">
                                    <i class="fas fa-store"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $users->where('role', 'Showroom-Manager')->where('deleted_at', null)->count() }}</h5>
                            <p class="text-muted mb-0 small">Showroom Managers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm">
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
                            <h5 class="mb-1">{{ $users->whereIn('role', ['Salesperson', 'Support-Staff'])->where('deleted_at', null)->count() }}</h5>
                            <p class="text-muted mb-0 small">Staff Members</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-purple-subtle" style="background-color: rgba(102, 16, 242, 0.1) !important;">
                                <span class="avatar-title rounded-circle text-white" style="background-color: #6610f2 !important;">
                                    <i class="fas fa-user-friends"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $users->whereIn('role', ['client', 'Client'])->where('deleted_at', null)->count() }}</h5>
                            <p class="text-muted mb-0 small">Clients</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-dark-subtle">
                                <span class="avatar-title rounded-circle bg-dark text-white">
                                    <i class="fas fa-chart-pie"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $users->where('deleted_at', null)->count() }}</h5>
                            <p class="text-muted mb-0 small">Total Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <h4 class="text-primary mb-1">{{ $users->where('deleted_at', null)->count() }}</h4>
                            <p class="text-muted mb-0">Total Active Users</p>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-danger mb-1">{{ $users->whereNotNull('deleted_at')->count() }}</h4>
                            <p class="text-muted mb-0">Deleted Users</p>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-success mb-1">{{ $users->count() }}</h4>
                            <p class="text-muted mb-0">Total Users</p>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-info mb-1">{{ $users->where('role', 'Managing-Director')->where('deleted_at', null)->count() + $users->where('role', 'Showroom-Manager')->where('deleted_at', null)->count() }}</h4>
                            <p class="text-muted mb-0">Leadership Team</p>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-purple mb-1" style="color: #6610f2 !important;">{{ $users->whereIn('role', ['client', 'Client'])->where('deleted_at', null)->count() }}</h4>
                            <p class="text-muted mb-0">Active Clients</p>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-warning mb-1">{{ $users->whereIn('role', ['Salesperson', 'Support-Staff', 'Accountant'])->where('deleted_at', null)->count() }}</h4>
                            <p class="text-muted mb-0">Staff & Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="standard-modalLabel">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card border-0">
                        <div class="card-body">
                            <form id="userForm" class="row g-3">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="number" class="form-control" id="phone" name="phone" required placeholder="254700000000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">National ID</label>
                                    <input type="number" class="form-control" id="national_id" name="national_id" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option disabled selected value="">Choose Role</option>
                                        <option value="Managing-Director">Managing Director</option>
                                        <option value="Accountant">Accountant</option>
                                        <option value="General-Manager">General Manager</option>
                                        <option value="Showroom-Manager">Showroom Manager</option>
                                        <option value="Salesperson">Salesperson</option>
                                        <option value="Support-Staff">Support Staff</option>
                                         <option value="HR">HR</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option disabled selected value="">Choose Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                 <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Create User
                                    </button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold" style="color: #000 !important;">
                        <i class="fas fa-users me-2 text-primary"></i>Users List
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive"> 
                        <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>National ID</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr class="{{ $user->deleted_at ? 'table-secondary' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span class="avatar-title rounded-circle bg-{{ $user->deleted_at ? 'secondary' : 'primary' }}-subtle text-{{ $user->deleted_at ? 'secondary' : 'primary' }}">
                                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                                </span>
                                            </div>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->national_id }}</td>
                                    <td>
                                      @php
    $badgeClass = match($user->role) {
        'Managing-Director' => 'primary',
        'Accountant' => 'info',
        'General-Manager' => 'success',
        'Showroom-Manager' => 'warning',
        'client', 'Client' => 'purple',
        default => 'info'
    };
    
    $customStyle = in_array($user->role, ['client', 'Client']) 
        ? 'background-color: #6610f2 !important;' 
        : '';
@endphp

<span class="badge bg-{{ $badgeClass }}" style="{{ $customStyle }}">
    {{ str_replace('-', ' ', $user->role) }}
</span>
                                    </td>
                                    <td>
                                        @if($user->deleted_at)
                                            <span class="badge bg-danger">Deleted</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->deleted_at)
                                            <button class="btn btn-success btn-sm restore-user" data-id="{{ $user->id }}">
                                                <i class="fas fa-undo me-1"></i>Restore
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#edit-modal-{{ $user->id }}">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm delete-user" data-id="{{ $user->id }}">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Edit User Modal for each user -->
                                @if(!$user->deleted_at)
                                <div class="modal fade" id="edit-modal-{{ $user->id }}" tabindex="-1" aria-labelledby="edit-modalLabel-{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="edit-modalLabel-{{ $user->id }}">
                                                    <i class="fas fa-user-edit me-2"></i>Edit User: {{ $user->first_name }} {{ $user->last_name }}
                                                </h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card border-0">
                                                    <div class="card-body">
                                                        <form class="row g-3" id="edituserForm-{{ $user->id }}" data-user-id="{{ $user->id }}">
                                                            @csrf
                                                            <input type="hidden" value="{{ $user->id }}" name="id" required>
                                                            
                                                            <div class="col-md-6">
                                                                <label class="form-label">First Name</label>
                                                                <input type="text" class="form-control" value="{{ $user->first_name }}" name="editfirst_name" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Last Name</label>
                                                                <input type="text" class="form-control" value="{{ $user->last_name }}" name="editlast_name" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" class="form-control" value="{{ $user->email }}" name="editemail" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Phone Number</label>
                                                                <input type="text" class="form-control" value="{{ $user->phone }}" name="editphone" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">National ID</label>
                                                                <input type="text" class="form-control" value="{{ $user->national_id }}" name="editnational_id" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Role</label>
                                                                <select class="form-select" name="editrole" required>
                                                                    <option disabled value="">Choose Role</option> General-Manager
                                                                    <option value="Managing-Director" {{ $user->role == 'Managing-Director' ? 'selected' : '' }}>Managing Director</option>
                                                                    <option value="General-Manager" {{ $user->role == 'General-Manager' ? 'selected' : '' }}>General-Manager</option>
                                                                    <option value="Accountant" {{ $user->role == 'Accountant' ? 'selected' : '' }}>Accountant</option>
                                                                    <option value="Showroom-Manager" {{ $user->role == 'Showroom-Manager' ? 'selected' : '' }}>Showroom Manager</option>
                                                                    <option value="Salesperson" {{ $user->role == 'Salesperson' ? 'selected' : '' }}>Salesperson</option>
                                                                    <option value="Support-Staff" {{ $user->role == 'Support-Staff' ? 'selected' : '' }}>Support Staff</option>
                                                                    <option value="HR" {{ $user->role == 'HR' ? 'selected' : '' }}>HR</option>
                                                                </select>
                                                            </div>
                                                            <br>
                                                            <div class="col-12">
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fas fa-save me-2"></i>Update User
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>