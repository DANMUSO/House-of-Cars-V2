<x-app-layout>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Trade Inn | Sell In Behalf</h4>
            <p class="text-muted mb-0">Manage customer vehicles for trade-in and consignment sales</p>
        </div>
        <div class="flex-grow-1 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                <i class="fas fa-plus me-1"></i> Add Customer and Vehicle Information
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
                            <div class="avatar-sm rounded-circle bg-primary-subtle">
                                <span class="avatar-title rounded-circle bg-primary text-white">
                                    <i class="fas fa-car"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $vehicles->count() }}</h5>
                            <p class="text-muted mb-0 small">Total Vehicles</p>
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
                                    <i class="fas fa-exchange-alt"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $vehicles->where('sell_type', 1)->count() }}</h5>
                            <p class="text-muted mb-0 small">Trade Inn</p>
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
                                    <i class="fas fa-handshake"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $vehicles->where('sell_type', 2)->count() }}</h5>
                            <p class="text-muted mb-0 small">Sell in Behalf</p>
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
                                    <i class="fas fa-users"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $vehicles->pluck('customer_name')->unique()->count() }}</h5>
                            <p class="text-muted mb-0 small">Unique Customers</p>
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
                                    <i class="fas fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $vehicles->where('created_at', '>=', now()->startOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0 small">This Month</p>
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
                                    <i class="fas fa-tags"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $vehicles->pluck('vehicle_make')->unique()->count() }}</h5>
                            <p class="text-muted mb-0 small">Vehicle Makes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Price Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-success mb-1">KES {{ number_format($vehicles->sum('minimum_price'), 2) }}</h4>
                            <p class="text-muted mb-0">Total Inventory Value</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-primary mb-1">KES {{ $vehicles->count() > 0 ? number_format($vehicles->avg('minimum_price'), 2) : '0.00' }}</h4>
                            <p class="text-muted mb-0">Average Vehicle Price</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning mb-1">KES {{ $vehicles->count() > 0 ? number_format($vehicles->max('minimum_price'), 2) : '0.00' }}</h4>
                            <p class="text-muted mb-0">Highest Price</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info mb-1">KES {{ $vehicles->count() > 0 ? number_format($vehicles->min('minimum_price'), 2) : '0.00' }}</h4>
                            <p class="text-muted mb-0">Lowest Price</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Distribution -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Top Vehicle Makes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $vehicleMakes = $vehicles->groupBy('vehicle_make')->map(function($group) {
                                return [
                                    'count' => $group->count(),
                                    'total_value' => $group->sum('minimum_price')
                                ];
                            })->sortByDesc('count')->take(6);
                        @endphp
                        
                        @forelse($vehicleMakes as $make => $data)
                            <div class="col-lg-6 col-md-12 mb-3">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="flex-shrink-0">
                                        <span class="fs-4">🚗</span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $make }}</h6>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ $data['count'] }} vehicles</small>
                                            <small class="text-primary fw-bold">KES {{ number_format($data['total_value'], 0) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-car fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No vehicle data available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="fas fa-chart-pie me-2 text-success"></i>Sell Type Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 200px; width: 100%;">
                        <canvas id="sellTypeChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-circle text-success"></i> Trade Inn</span>
                            <span>{{ $vehicles->where('sell_type', 1)->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-info"></i> Sell in Behalf</span>
                            <span>{{ $vehicles->where('sell_type', 2)->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ORIGINAL ADD MODAL - UNCHANGED CRUD -->
    <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="standard-modalLabel">Add Customer and Vehicle Information</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- EXACTLY ORIGINAL FORM - NO CHANGES -->
                    <form id="TradeInForm" class="row g-3" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" class="form-control" id="Status" name="Status" value="1">
                        <div class="col-md-6">
                            <label class="form-label">Sell Type</label>
                            <select class="form-select" id="Sell_Type" name="Sell_Type" required>
                                <option disabled selected value="">Choose</option>
                                <option value="1">Trade Inn</option>
                                <option value="2">Sell in Behalf</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="Customer_Name" name="Customer_Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone No</label>
                            <input type="number" class="form-control" id="Phone_No" name="Phone_No" required>
                        </div>
                         
                        <div class="col-md-6">
                            <label class="form-label">Email Address </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                         <div class="col-md-6">
                            <label class="form-label">National ID</label>
                            <input type="number" class="form-control" id="national_id" name="national_id" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Make/Type</label>
                            <input type="text" class="form-control" id="Vehicle_Make" name="Vehicle_Make" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chasis No:</label>
                            <input type="text" class="form-control" id="Chasis_No" name="Chasis_No" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number Plate</label>
                            <input type="text" class="form-control" id="Number_Plate" name="Number_Plate" required>
                        </div>
                          <div class="col-md-6">
                            <label class="form-label">Engine Number</label>
                            <input type="text" class="form-control" id="engine_no" name="engine_no" required>
                        </div>
                          <div class="col-md-6">
                            <label class="form-label">Engine Capacity</label>
                            <input type="text" class="form-control" id="engine_capacity" name="engine_capacity" required>
                        </div>
                          <div class="col-md-6">
                            <label class="form-label">Transmission</label>
                            <input type="text" class="form-control" id="transmission" name="transmission" required>
                        </div>
                          <div class="col-md-6">
                            <label class="form-label">Colour</label>
                            <input type="text" class="form-control" id="colour" name="colour" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Minimum Price</label>
                            <input type="number" class="form-control" id="Minimum_Price" name="Minimum_Price" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Photos</label>
                            <input type="file" class="form-control" id="photos" name="photos[]" multiple required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">
                        <i class="fas fa-list me-1"></i> All Vehicles
                    </button>
                    <button type="button" class="btn btn-outline-success filter-btn" data-filter="1" style="border-color: #20c997; color: #20c997;">
                        <i class="fas fa-exchange-alt me-1"></i> Trade Inn
                    </button>
                    <button type="button" class="btn btn-outline-info filter-btn" data-filter="2" style="border-color: #0dcaf0; color: #0dcaf0;">
                        <i class="fas fa-handshake me-1"></i> Sell in Behalf
                    </button>
                </div>
                <div class="text-muted">
                    <small>Total: {{ $vehicles->count() }} vehicles</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>Customer Vehicles
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="mb-3">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by customer name, vehicle make, or number plate...">
                    </div>
                    
                    <div class="table-responsive">
                        <table id="responsive-datatable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th>Vehicle Make</th>
                                    <th>Model</th>
                                    <th>Chasis No</th>
                                    <th>Number Plate</th>
                                    <th>Minimum Price</th>
                                    <th>Sell Type</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vehicles as $vehicle)
                                <tr data-sell-type="{{ $vehicle->sell_type }}" class="{{ $vehicle->trashed() ? 'table-danger bg-danger bg-opacity-10' : '' }}">
                                    <td>
                                        {{ $vehicle->customer_name }}
                                        @if($vehicle->trashed())
                                            <span class="badge bg-danger ms-2">DELETED</span>
                                        @endif
                                    </td>
                                    <td>{{ $vehicle->phone_no }}</td>
                                    <td>{{ $vehicle->email }}</td>
                                    <td>{{ $vehicle->vehicle_make }}</td>
                                    <td>{{ $vehicle->model }}</td>
                                    <td>{{ $vehicle->chasis_no }}</td>
                                    <td>{{ $vehicle->number_plate }}</td>
                                    <td>{{ number_format($vehicle->minimum_price, 2) }}</td>
                                    <td>
                                    @if($vehicle->sell_type == 1)
                                        <span class="badge bg-success fs-6" style="background-color: #20c997 !important;">
                                            <i class="fas fa-exchange-alt me-1"></i>Trade Inn
                                        </span>
                                    @else
                                        <span class="badge bg-info fs-6" style="background-color: #0dcaf0 !important;">
                                            <i class="fas fa-handshake me-1"></i>Sell in Behalf
                                        </span>
                                    @endif
                                    </td>
                                    <td>
                                        {{ $vehicle->created_at }}
                                        @if($vehicle->trashed())
                                            <br><small class="text-danger">Deleted: {{ $vehicle->deleted_at->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($vehicle->trashed())
                                            <button class="btn btn-sm btn-success restore-vehicle" data-id="{{ $vehicle->id }}">
                                                <i class="fas fa-undo me-1"></i>Restore
                                            </button>
                                        @else
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editVehicleModal{{ $vehicle->id }}">
                                            Edit
                                        </button>

                                        <br><br>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal{{$vehicle->id}}">
                                            View
                                        </button>
                                        <br> <br> <br>
                                         <button class="btn btn-outline-danger btn-sm delete-vehicle" data-id="{{ $vehicle->id }}">
                                                <i class="fas fa-trash me-1"></i>Deactivate
                                            </button>
                                        <!-- ORIGINAL VIEW MODAL - UNCHANGED -->
                                        <div class="modal fade" id="modal{{$vehicle->id}}" tabindex="-1" aria-labelledby="modalLabel{{$vehicle->id}}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="modalLabel{{$vehicle->id}}">{{ $vehicle->vehicle_make }}  |  {{ $vehicle->number_plate }} | {{ $vehicle->chasis_no }}</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                        @php
                                                            $imagePaths = json_decode($vehicle->photos);
                                                        @endphp

                                                        @if ($imagePaths && is_array($imagePaths))
                                                            @foreach ($imagePaths as $imagePath)
                                                                @php
                                                                    // Check if the photo path is already a full URL (legacy data) or S3 key
                                                                    if (str_starts_with($imagePath, 'http')) {
                                                                        // Legacy full URL - use as is
                                                                        $imageUrl = $imagePath;
                                                                    } else {
                                                                        // S3 key path - generate S3 URL
                                                                        $bucket = config('filesystems.disks.s3.bucket');
                                                                        $region = config('filesystems.disks.s3.region');
                                                                        $imageUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$imagePath}";
                                                                    }
                                                                @endphp
                                                                <div class="col-md-12 col-lg-6 mb-3">
                                                                    <img src="{{ $imageUrl }}" 
                                                                        alt="Car Image" 
                                                                        width="100%" 
                                                                        class="img-hover-zoom rounded shadow"
                                                                        loading="lazy"
                                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                                    <div style="display: none; text-align: center; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem;"
                                                                        class="rounded shadow">
                                                                        <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                                                        <p class="text-muted mb-0">Failed to load image</p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p>No images available for this car.</p>
                                                        @endif
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                </tr>

                                <!-- ORIGINAL EDIT MODAL - UNCHANGED -->
                                <div class="modal fade" id="editVehicleModal{{ $vehicle->id }}" tabindex="-1" aria-labelledby="editVehicleModalLabel{{ $vehicle->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                        <form id="TradeInFormupdate{{ $vehicle->id }}" data-vehicle-id="{{ $vehicle->id }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editVehicleModalLabel{{ $vehicle->id }}">Edit Customer and Vehicle Information</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="form-label">Sell Type</label>
                                                            <select class="form-select" id="Sell_Typev1" name="sell_typev1" required>
                                                                <option disabled {{ $vehicle->sell_type == null ? 'selected' : '' }} value="">Choose</option>
                                                                <option value="1" {{ $vehicle->sell_type == 1 ? 'selected' : '' }}>Trade Inn</option>
                                                                <option value="2" {{ $vehicle->sell_type == 2 ? 'selected' : '' }}>Sell in Behalf</option>
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label class="form-label">Customer Name</label>
                                                            <input type="text" class="form-control" name="customer_namev1" value="{{ $vehicle->customer_name }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="form-label">Phone Number</label>
                                                            <input type="number" class="form-control" name="phone_nov1" value="{{ $vehicle->phone_no }}" required>
                                                        </div>
                                                        <div class="col">
                                                            <label class="form-label">National ID</label>
                                                            <input type="number" class="form-control" name="national_idv1" value="{{ $vehicle->national_id }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" class="form-control" name="emailv1" value="{{ $vehicle->email }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="form-label">Vehicle Make</label>
                                                            <input type="text" class="form-control" name="vehicle_makev1" value="{{ $vehicle->vehicle_make }}" required>
                                                        </div>
                                                         <div class="col">
                                                            <label class="form-label">Model</label>
                                                            <input type="text" class="form-control" name="modelv1" value="{{ $vehicle->model }}" required>
                                                        </div>
                                                        <div class="col">
                                                            <label class="form-label">Chasis No</label>
                                                            <input type="text" class="form-control" name="chasis_nov1" value="{{ $vehicle->chasis_no }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="form-label">Number Plate</label>
                                                            <input type="text" class="form-control" name="number_platev1" value="{{ $vehicle->number_plate }}" required>
                                                        </div>
                                                          <div class="col-md-6">
                                                            <label class="form-label">Engine Number</label>
                                                            <input type="text" class="form-control" id="engine_nov1" name="engine_nov1" value="{{ $vehicle->engine_no }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Engine Capacity</label>
                                                            <input type="text" class="form-control" id="engine_capacityv1" name="engine_capacityv1" value="{{ $vehicle->engine_capacity }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Transmission</label>
                                                            <input type="text" class="form-control" id="transmissionv1" name="transmissionv1" value="{{ $vehicle->transmission }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Colour</label>
                                                            <input type="text" class="form-control" id="colourv1" name="colourv1" value="{{ $vehicle->colour }}" required>
                                                        </div>
                                                        <div class="col">
                                                        <label class="form-label">Minimum Price</label>
                                                        <input type="number" step="0.01" class="form-control" name="minimum_pricev1" value="{{ $vehicle->minimum_price }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Photos</label>
                                                        <input type="file" class="form-control" id="photosv1" name="photosv1[]" multiple>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success">Update</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- container-fluid -->

<!-- Custom CSS -->
<style>
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
    
    .filter-btn.active {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
    }
    
    .filter-btn[data-filter="1"].active {
        background-color: #20c997 !important;
        border-color: #20c997 !important;
        color: white !important;
    }
    
    .filter-btn[data-filter="2"].active {
        background-color: #0dcaf0 !important;
        border-color: #0dcaf0 !important;
        color: white !important;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .img-hover-zoom:hover {
        transform: scale(1.05);
    }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ONLY ESSENTIAL CHART FUNCTIONALITY - NO OTHER JAVASCRIPT
    document.addEventListener('DOMContentLoaded', function() {
        // Chart initialization ONLY
        const sellTypeCanvas = document.getElementById('sellTypeChart');
        if (sellTypeCanvas) {
            const sellTypeCtx = sellTypeCanvas.getContext('2d');
            const tradeInnCount = {{ $vehicles->where('sell_type', 1)->count() ?? 0 }};
            const sellInBehalfCount = {{ $vehicles->where('sell_type', 2)->count() ?? 0 }};
            
            if (tradeInnCount > 0 || sellInBehalfCount > 0) {
                new Chart(sellTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Trade Inn', 'Sell in Behalf'],
                        datasets: [{
                            data: [tradeInnCount, sellInBehalfCount],
                            backgroundColor: ['#28a745', '#17a2b8'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }
        
        // SIMPLE filter functionality - no modal interference
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const rows = document.querySelectorAll('#responsive-datatable tbody tr[data-sell-type]');
                
                rows.forEach(row => {
                    const rowType = row.getAttribute('data-sell-type');
                    row.style.display = (filter === 'all' || rowType === filter) ? '' : 'none';
                });
            });
        });

        // SIMPLE search functionality - no modal interference
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#responsive-datatable tbody tr[data-sell-type]');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
</script>
</x-app-layout>