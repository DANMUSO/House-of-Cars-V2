<x-app-layout>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">In Cash Management</h4>
                <p class="text-muted mb-0">Manage and track cash transactions for vehicle sales</p>
            </div>
            <div class="flex-grow-1 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                    <i class="fas fa-plus me-1"></i> Add Transaction
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
                                <h5 class="mb-1">{{ $pendingApproval ?? 0 }}</h5>
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
                                <h5 class="mb-1">{{ ($totalTransactions ?? 0) - ($pendingApproval ?? 0) }}</h5>
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
                                <div class="avatar-sm rounded-circle bg-primary-subtle">
                                    <span class="avatar-title rounded-circle bg-primary text-white">
                                        <i class="fas fa-list"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $totalTransactions ?? 0 }}</h5>
                                <p class="text-muted mb-0 small">Total Sales</p>
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
                                        <i class="fas fa-car"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $importedCarsCount ?? 0 }}</h5>
                                <p class="text-muted mb-0 small">Imported Cars</p>
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
                                        <i class="fas fa-exchange-alt"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $customerCarsCount ?? 0 }}</h5>
                                <p class="text-muted mb-0 small">Trade-In</p>
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
                                <h5 class="mb-1">
                                    @if(($totalTransactions ?? 0) > 0)
                                        {{ number_format(((($totalTransactions ?? 0) - ($pendingApproval ?? 0)) / ($totalTransactions ?? 1)) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </h5>
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
                                <h4 class="text-success mb-1">KES {{ number_format($totalAmount ?? 0, 2) }}</h4>
                                <p class="text-muted mb-0">Total Revenue</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-primary mb-1">KES {{ number_format($thisMonthAmount ?? 0, 2) }}</h4>
                                <p class="text-muted mb-0">This Month</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning mb-1">KES {{ number_format($lastMonthAmount ?? 0, 2) }}</h4>
                                <p class="text-muted mb-0">Last Month</p>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info mb-1">KES {{ number_format($averageSale ?? 0, 2) }}</h4>
                                <p class="text-muted mb-0">Average Sale</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Type Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0 fw-bold" style="color: #000 !important;">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>Car Types Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 mb-3">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="flex-shrink-0">
                                        <span class="fs-4">🚗</span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Imported Cars</h6>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ $importedCarsCount ?? 0 }} sales</small>
                                            <small class="text-primary fw-bold">{{ $importedCarsPercentage ?? 0 }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 col-md-6 mb-3">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="flex-shrink-0">
                                        <span class="fs-4">🔄</span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Trade-In / Sell on Behalf</h6>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ $customerCarsCount ?? 0 }} sales</small>
                                            <small class="text-success fw-bold">{{ $customerCarsPercentage ?? 0 }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Performance Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0 fw-bold" style="color: #000 !important;">
                            <i class="fas fa-chart-line me-2 text-primary"></i>Monthly Sales Performance
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Details Modal -->
        <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h1 class="modal-title fs-5" id="standard-modalLabel">
                            <i class="fas fa-plus me-2"></i>Add Cash Transaction
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="InCashForm" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Client_Name" name="Client_Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone No <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="Phone_No" name="Phone_No" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">KRA PIN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="KRA" name="KRA" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">National ID <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="National_ID" name="National_ID" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="Amount" name="Amount" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Paid Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" class="form-control" id="PaidAmount" name="PaidAmount" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Select Vehicle <span class="text-danger">*</span></label>
                                <select class="form-select" id="car_id" name="car_id" required>
                                    <option disabled selected value="">Choose Vehicle</option>
                                    <optgroup label="🚗 Imported Cars">
                                        @foreach ($cars as $car)
                                            @if ($car->carsImport)
                                                <option value="import-{{ $car->carsImport->id }}">
                                                    {{ $car->carsImport->make }} - {{ $car->carsImport->model }} - {{ $car->carsImport->year }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="🔄 Trade Inn | Sell In Behalf">
                                        @foreach ($cars as $car)
                                            @if ($car->customerVehicle)
                                                <option value="customer-{{ $car->customerVehicle->id }}">
                                                    {{ $car->customerVehicle->vehicle_make }} - {{ $car->customerVehicle->number_plate }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Submit Transaction
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
                        <button type="button" class="btn btn-outline-primary status-filter active" data-status="all">
                            <i class="fas fa-list me-1"></i> All Transactions
                        </button>
                        <button type="button" class="btn btn-outline-warning status-filter" data-status="pending">
                            <i class="fas fa-clock me-1"></i> Pending
                        </button>
                        <button type="button" class="btn btn-outline-success status-filter" data-status="approved">
                            <i class="fas fa-check me-1"></i> Approved
                        </button>
                    </div>
                    
                    <div class="text-muted">
                        <small>Total: {{ ($inCashes ?? collect())->count() }} transactions</small>
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
                            <i class="fas fa-table me-2"></i>Cash Transactions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>#
                                       
                                        </th>
                                        <th><i class="fas fa-car me-1"></i>Car Details</th>
                                        <th><i class="fas fa-tag me-1"></i>Car Type</th>
                                        <th><i class="fas fa-user me-1"></i>Client Name</th>
                                        <th><i class="fas fa-phone me-1"></i>Phone Number</th>
                                        <th><i class="fas fa-envelope me-1"></i>Email</th>
                                        <th><i class="fas fa-id-card me-1"></i>KRA</th>
                                        <th><i class="fas fa-id-badge me-1"></i>National ID</th>
                                        <th><i class="fas fa-money-bill me-1"></i>Total Amount (Ksh)</th>
                                        <th><i class="fas fa-money-bill me-1"></i>Paid Amount (Ksh)</th>
                                        <th><i class="fas fa-calendar me-1"></i>Date</th>
                                        <th><i class="fas fa-cog me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inCashes as $index => $cash)
                                        <tr>
                                            <td>{{ $index + 1 }}
                                                <div class="container mt-5">
  <!-- Button to Open Modal -->
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#VehicleModal{{$cash->id}}">
     <span class="me-2">View 🚗</span>
  </button>
</div>


<!-- Professional Vehicle Details Modal -->
<div class="modal fade" id="VehicleModal{{$cash->id}}" tabindex="-1" aria-labelledby="VehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-primary text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="modal-icon me-3">
                        <i class="fas fa-car fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="VehicleModalLabel">Vehicle Details</h5>
                        <small class="opacity-75">Complete vehicle information and gallery</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0">
                @if ($cash->car_type == 'import')
                    @php $car = $importCars->get($cash->car_id); @endphp
                    @if($car)
                        <div class="row g-0">
                            <!-- Vehicle Information Panel -->
                            <div class="col-lg-7 p-4">
                                <!-- Header Section -->
                                <div class="vehicle-header mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="vehicle-type-badge">
                                            <span class="badge bg-primary fs-6 px-3 py-2">
                                                <i class="fas fa-ship me-2"></i>Import Vehicle
                                            </span>
                                        </div>
                                    </div>
                                    <h3 class="text-dark mb-2">{{ $car->make }} {{ $car->model }}</h3>
                                    <p class="text-muted mb-0">{{ $car->year }} • {{ $car->body_type }}</p>
                                </div>

                                <!-- Vehicle Specifications -->
                                <div class="specs-section mb-4">
                                    <h6 class="section-title text-uppercase fw-bold text-muted mb-3">
                                        <i class="fas fa-cogs me-2"></i>Vehicle Specifications
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">VIN Number</div>
                                                <div class="spec-value fw-semibold">{{ $car->vin }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">Engine Type</div>
                                                <div class="spec-value fw-semibold">{{ $car->engine_type }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">Mileage</div>
                                                <div class="spec-value fw-semibold">
                                                    <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                                                    {{ number_format($car->mileage) }} km
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">Body Type</div>
                                                <div class="spec-value fw-semibold">{{ $car->body_type }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Photos Panel -->
                            <div class="col-lg-5 bg-light border-start">
                                <div class="photo-section h-100 p-4">
                                    <h6 class="section-title text-uppercase fw-bold text-muted mb-3">
                                        <i class="fas fa-images me-2"></i>Vehicle Gallery
                                    </h6>
                                    @if($car->photos)
                                        @php
                                            $photos = json_decode($car->photos, true);
                                            if (is_string($photos)) {
                                                $photos = json_decode($photos, true);
                                            }
                                        @endphp
                                        @if(is_array($photos) && count($photos) > 0)
                                            <div class="gallery-container">
                                                <div id="carouselImport{{$cash->id}}" class="carousel slide" data-bs-ride="carousel">
                                                    <div class="carousel-inner rounded-3 shadow-sm">
                                                        @foreach($photos as $index => $photo)
                                                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                                <img src="{{ asset('storage/' . $photo) }}" 
                                                                     class="d-block w-100" 
                                                                     style="height: 320px; object-fit: cover;" 
                                                                     alt="Vehicle Photo {{ $index + 1 }}"
                                                                     loading="lazy">
                                                                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                                                                    <small>Photo {{ $index + 1 }} of {{ count($photos) }}</small>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @if(count($photos) > 1)
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselImport{{$cash->id}}" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Previous</span>
                                                        </button>
                                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselImport{{$cash->id}}" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Next</span>
                                                        </button>
                                                    @endif
                                                </div>
                                                
                                                <!-- Photo Thumbnails -->
                                                @if(count($photos) > 1)
                                                    <div class="photo-thumbnails mt-3">
                                                        <div class="row g-2">
                                                            @foreach(array_slice($photos, 0, 4) as $index => $photo)
                                                                <div class="col-3">
                                                                    <img src="{{ asset('storage/' . $photo) }}" 
                                                                         class="img-thumbnail cursor-pointer thumbnail-img" 
                                                                         style="height: 60px; object-fit: cover; width: 100%;"
                                                                         data-bs-target="#carouselImport{{$cash->id}}" 
                                                                         data-bs-slide-to="{{ $index }}"
                                                                         alt="Thumbnail {{ $index + 1 }}">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @if(count($photos) > 4)
                                                            <div class="text-center mt-2">
                                                                <small class="text-muted">+{{ count($photos) - 4 }} more photos</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="no-photos text-center py-5">
                                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No Photos Available</h6>
                                                <p class="text-muted small mb-0">Vehicle images have not been uploaded yet</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="no-photos text-center py-5">
                                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No Photos Available</h6>
                                            <p class="text-muted small mb-0">Vehicle images have not been uploaded yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="error-state text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5 class="text-muted">Vehicle Not Found</h5>
                            <p class="text-muted">The import vehicle information could not be retrieved.</p>
                        </div>
                    @endif

                @elseif ($cash->car_type == 'customer')
                    @php $car = $customerCars->get($cash->car_id); @endphp
                    @if($car)
                        <div class="row g-0">
                            <!-- Vehicle Information Panel -->
                            <div class="col-lg-7 p-4">
                                <!-- Header Section -->
                                <div class="vehicle-header mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="vehicle-type-badge">
                                            <span class="badge bg-info fs-6 px-3 py-2">
                                                <i class="fas fa-user-friends me-2"></i>Customer Vehicle
                                            </span>
                                        </div>
                                    </div>
                                    <h3 class="text-dark mb-2">{{ $car->vehicle_make }}</h3>
                                    <p class="text-muted mb-0">{{ $car->number_plate }} • Listed {{ date('M d, Y', strtotime($car->created_at)) }}</p>
                                </div>

                                <!-- Owner Information -->
                                <div class="owner-section mb-4">
                                    <h6 class="section-title text-uppercase fw-bold text-muted mb-3">
                                        <i class="fas fa-user me-2"></i>Owner Information
                                    </h6>
                                    <div class="owner-card border rounded p-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="owner-detail">
                                                    <div class="detail-label text-muted small mb-1">Full Name</div>
                                                    <div class="detail-value fw-semibold">
                                                        <i class="fas fa-user-circle me-2 text-primary"></i>
                                                        {{ $car->customer_name }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="owner-detail">
                                                    <div class="detail-label text-muted small mb-1">Phone Number</div>
                                                    <div class="detail-value fw-semibold">
                                                        <i class="fas fa-phone me-2 text-success"></i>
                                                        <a href="tel:{{ $car->phone_no }}" class="text-decoration-none">{{ $car->phone_no }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($car->email)
                                                <div class="col-12">
                                                    <div class="owner-detail">
                                                        <div class="detail-label text-muted small mb-1">Email Address</div>
                                                        <div class="detail-value fw-semibold">
                                                            <i class="fas fa-envelope me-2 text-info"></i>
                                                            <a href="mailto:{{ $car->email }}" class="text-decoration-none">{{ $car->email }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Details -->
                                <div class="specs-section mb-4">
                                    <h6 class="section-title text-uppercase fw-bold text-muted mb-3">
                                        <i class="fas fa-car me-2"></i>Vehicle Details
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">Vehicle Make</div>
                                                <div class="spec-value fw-semibold">
                                                    <i class="fas fa-industry me-2 text-primary"></i>
                                                    {{ $car->vehicle_make }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">Number Plate</div>
                                                <div class="spec-value fw-semibold">
                                                    <i class="fas fa-id-card me-2 text-success"></i>
                                                    {{ $car->number_plate }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">Chassis Number</div>
                                                <div class="spec-value fw-semibold">
                                                    <i class="fas fa-barcode me-2 text-info"></i>
                                                    {{ $car->chasis_no }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="spec-item p-3 bg-light rounded">
                                                <div class="spec-label text-muted small mb-1">Vehicle ID</div>
                                                <div class="spec-value fw-semibold">
                                                    <i class="fas fa-hashtag me-2 text-warning"></i>
                                                    #{{ $car->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing Information -->
                                <div class="pricing-section">
                                    <h6 class="section-title text-uppercase fw-bold text-muted mb-3">
                                        <i class="fas fa-tag me-2"></i>Pricing & Sale Information
                                    </h6>
                                    <div class="pricing-card border rounded p-3">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-6">
                                                <div class="price-info text-center">
                                                    <div class="price-amount text-success fw-bold fs-3">
                                                        KSh {{ number_format($car->minimum_price, 2) }}
                                                    </div>
                                                    <small class="text-muted">Minimum Price</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="sale-type text-center">
                                                    <span class="badge {{ $car->sell_type == '1' ? 'bg-primary' : 'bg-secondary' }} fs-6 px-3 py-2">
                                                        <i class="fas {{ $car->sell_type == '1' ? 'fa-gavel' : 'fa-handshake' }} me-2"></i>
                                                        {{ $car->sell_type == '1' ? 'Direct Sale' : 'Direct Sale' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Photos Panel -->
                            <div class="col-lg-5 bg-light border-start">
                                <div class="photo-section h-100 p-4">
                                    <h6 class="section-title text-uppercase fw-bold text-muted mb-3">
                                        <i class="fas fa-images me-2"></i>Vehicle Gallery
                                    </h6>
                                    @if($car->photos)
                                        @php
                                            $photos = json_decode($car->photos, true);
                                        @endphp
                                        @if(is_array($photos) && count($photos) > 0)
                                            <div class="gallery-container">
                                                <div id="carouselCustomer{{$cash->id}}" class="carousel slide" data-bs-ride="carousel">
                                                    <div class="carousel-inner rounded-3 shadow-sm">
                                                        @foreach($photos as $index => $photo)
                                                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                                <img src="{{ asset($photo) }}" 
                                                                     class="d-block w-100" 
                                                                     style="height: 320px; object-fit: cover;" 
                                                                     alt="Vehicle Photo {{ $index + 1 }}"
                                                                     loading="lazy">
                                                                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                                                                    <small>Photo {{ $index + 1 }} of {{ count($photos) }}</small>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @if(count($photos) > 1)
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselCustomer{{$cash->id}}" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Previous</span>
                                                        </button>
                                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselCustomer{{$cash->id}}" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Next</span>
                                                        </button>
                                                    @endif
                                                </div>
                                                
                                                <!-- Photo Thumbnails -->
                                                @if(count($photos) > 1)
                                                    <div class="photo-thumbnails mt-3">
                                                        <div class="row g-2">
                                                            @foreach(array_slice($photos, 0, 4) as $index => $photo)
                                                                <div class="col-3">
                                                                    <img src="{{ asset($photo) }}" 
                                                                         class="img-thumbnail cursor-pointer thumbnail-img" 
                                                                         style="height: 60px; object-fit: cover; width: 100%;"
                                                                         data-bs-target="#carouselCustomer{{$cash->id}}" 
                                                                         data-bs-slide-to="{{ $index }}"
                                                                         alt="Thumbnail {{ $index + 1 }}">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @if(count($photos) > 4)
                                                            <div class="text-center mt-2">
                                                                <small class="text-muted">+{{ count($photos) - 4 }} more photos</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="no-photos text-center py-5">
                                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No Photos Available</h6>
                                                <p class="text-muted small mb-0">Vehicle images have not been uploaded yet</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="no-photos text-center py-5">
                                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No Photos Available</h6>
                                            <p class="text-muted small mb-0">Vehicle images have not been uploaded yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="error-state text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5 class="text-muted">Vehicle Not Found</h5>
                            <p class="text-muted">The customer vehicle information could not be retrieved.</p>
                        </div>
                    @endif

                @else
                    <div class="error-state text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                        <h5 class="text-muted">No Vehicle Data</h5>
                        <p class="text-muted">No vehicle information is available for this record.</p>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-0 py-3">
                <div class="w-100 d-flex justify-content-between align-items-center">
                    <div class="modal-info">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Last updated: {{ now()->format('M d, Y g:i A') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                                            </td>
                                            <td>
                                                @if ($cash->car_type == 'import')
                                                    @php $car = $importCars->get($cash->car_id); @endphp
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2">🚗</span>
                                                        <div>
                                                            <strong>{{ $car ? $car->make . ' - ' . $car->model : 'N/A' }}</strong>
                                                            @if($car)<br><small class="text-muted">{{ $car->year }}</small>@endif
                                                        </div>
                                                    </div>
                                                @elseif ($cash->car_type == 'customer')
                                                    @php $car = $customerCars->get($cash->car_id); @endphp
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2">🔄</span>
                                                        <div>
                                                            <strong>{{ $car ? $car->vehicle_make : 'N/A' }}</strong>
                                                            @if($car)<br><small class="text-muted">{{ $car->number_plate }}</small>@endif
                                                        </div>
                                                    </div>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $cash->car_type == 'import' ? 'bg-primary' : 'bg-success' }} fs-6">
                                                    {{ $cash->car_type == 'import' ? '🚗 Import' : '🔄 Trade-In' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <span class="avatar-title rounded-circle bg-primary text-white">
                                                            {{ substr($cash->Client_Name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $cash->Client_Name }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $cash->Phone_No }}</td>
                                            <td>{{ $cash->email }}</td>
                                            <td>{{ $cash->KRA }}</td>
                                            <td>{{ $cash->National_ID }}</td>
                                            <td><strong class="text-success">Ksh {{ number_format($cash->Amount, 2) }}</strong></td>
                                            <td><strong class="text-success">Ksh {{ number_format($cash->paid_amount, 2) }}</strong></td>
                                            <td>
                                                <strong>{{ $cash->created_at->format('Y-m-d') }}</strong>
                                                <br><small class="text-muted">{{ $cash->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if ($cash->status != 1)
                                                    <div class="btn-group-vertical" role="group">
                                                        <button class="btn btn-success btn-sm approveBtn mb-1" data-id="{{ $cash->id }}">
                                                            <i class="fas fa-check me-1"></i> Approve
                                                        </button>
                                                      
                                                        <button class="btn btn-danger btn-sm deleteBtn" data-id="{{ $cash->id }}">
                                                            <i class="fas fa-trash me-1"></i> Delete
                                                        </button>
                                                    </div>
                                                @else
                                                <!-- Button to trigger modal -->
                                                
                                               
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Approved
                                                    </span>
                                                @endif
                                                 <br><br>
                                                <div class="text-center">
                                                   <button type="button" class="btn btn-primary btn-sm agreementBtn" 
                                                            data-cash-id="{{ $cash->id }}"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#agreementModal{{ $cash->id }}">
                                                        <i class="fas fa-file-contract me-1"></i> Agreement
                                                    </button>
                                                </div>
<!-- Professional Agreement Modal -->
<div class="modal fade" id="agreementModal{{ $cash->id }}" tabindex="-1" aria-labelledby="agreementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="agreementModalLabel">
                    <i class="fas fa-file-contract me-2"></i>In Cash Sales Agreement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Upload Section -->
                <div id="uploadSection{{ $cash->id }}" class="mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Agreement PDF</h6>
                        </div>
                        <div class="card-body">
                            <form id="agreementUploadForm{{ $cash->id }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="agreement_id" value="{{ $cash->id }}">
                                <input type="hidden" name="agreement_type" value="InCash">
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <label for="agreement_file{{ $cash->id }}" class="form-label">
                                            <i class="fas fa-file-pdf me-1"></i>Select PDF File
                                        </label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="agreement_file{{ $cash->id }}" 
                                               name="agreement_file" 
                                               accept=".pdf" 
                                               required>
                                        <div class="form-text">Maximum file size: 1GB. Only PDF files are allowed.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" 
                                                class="btn btn-primary w-100" 
                                                id="uploadBtn{{ $cash->id }}">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Progress Bar -->
                            <div class="progress mt-3 d-none" id="uploadProgress{{ $cash->id }}">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 0%"></div>
                            </div>
                            
                            <!-- Upload Status -->
                            <div id="uploadStatus{{ $cash->id }}" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Agreement Management Section (When PDF exists) -->
                <div id="agreementManagement{{ $cash->id }}" class="mb-4" style="display: none;">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-file-check me-2"></i>Agreement Uploaded</h6>
                            <button type="button" 
                                    class="btn btn-outline-light btn-sm" 
                                    id="deleteAgreementBtn{{ $cash->id }}"
                                    title="Delete Agreement">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p class="mb-0"><strong>Actions:</strong></p>
                                </div>
                                <div class="col-md-4">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openPDFNewTab{{ $cash->id }}()">
                                            <i class="fas fa-external-link-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="downloadPDF{{ $cash->id }}()">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="printPDF{{ $cash->id }}()">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Display Section -->
                <div id="agreementContent{{ $cash->id }}" style="min-height: 600px;">
                    <div class="text-center py-5" id="emptyState{{ $cash->id }}">
                        <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No agreement uploaded yet. Please upload a PDF file above.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" id="replaceBtn{{ $cash->id }}" style="display: none;" onclick="showUploadSection{{ $cash->id }}()">
                    <i class="fas fa-sync-alt me-1"></i>Replace PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal{{ $cash->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this agreement? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn{{ $cash->id }}">
                    <i class="fas fa-trash-alt me-1"></i>Delete Agreement
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.pdf-viewer-container {
    width: 100%;
    height: 600px;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.pdf-embed {
    width: 100%;
    height: 100%;
    border: none;
    border-radius: 0.375rem;
}

.pdf-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
}

.pdf-error-state {
    height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.upload-dropzone {
    border: 2px dashed #0d6efd;
    border-radius: 0.375rem;
    padding: 2rem;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.upload-dropzone:hover {
    background: #e7f3ff;
    border-color: #0b5ed7;
}

.upload-dropzone.dragover {
    background: #cfe2ff;
    border-color: #0a58ca;
}
</style>

<script>
$(document).ready(function() {
    const agreementId = {{ $cash->id }};
    let currentPdfUrl = null;
    
    // Check if agreement already exists when modal opens
    $('#agreementModal' + agreementId).on('shown.bs.modal', function() {
        checkExistingAgreement(agreementId);
    });
    
    // File upload form submission
    $('#agreementUploadForm' + agreementId).on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = $('#uploadBtn' + agreementId);
        const uploadProgress = $('#uploadProgress' + agreementId);
        const uploadStatus = $('#uploadStatus' + agreementId);
        
        // Reset status
        uploadStatus.empty();
        uploadProgress.removeClass('d-none');
        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Uploading...');
        
        $.ajax({
            url: '{{ route("agreement.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5 minutes timeout for large files
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        uploadProgress.find('.progress-bar').css('width', percentComplete + '%');
                        
                        // Show file size progress for large files
                        const loaded = (evt.loaded / (1024 * 1024 * 1024)).toFixed(2);
                        const total = (evt.total / (1024 * 1024 * 1024)).toFixed(2);
                        uploadProgress.find('.progress-bar').text(`${loaded}GB / ${total}GB (${percentComplete}%)`);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                uploadStatus.html(`
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Agreement uploaded successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                
                // Store PDF URL
                currentPdfUrl = response.pdfUrl;
                
                // Display the uploaded PDF
                displayPDF(currentPdfUrl, agreementId);
                
                // Show management section and hide upload section
                showAgreementManagement(agreementId);
                
                // Reset form
                $('#agreementUploadForm' + agreementId)[0].reset();
            },
            error: function(xhr) {
                let errorMessage = 'Upload failed. Please try again.';
                
                if (xhr.status === 413) {
                    errorMessage = 'File is too large. Maximum allowed size is 1GB.';
                } else if (xhr.status === 408 || xhr.statusText === 'timeout') {
                    errorMessage = 'Upload timed out. Please try again with a smaller file or check your internet connection.';
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                uploadStatus.html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            },
            complete: function() {
                uploadProgress.addClass('d-none');
                uploadBtn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Upload');
            }
        });
    });
    
    // Delete agreement functionality
    $('#deleteAgreementBtn' + agreementId).on('click', function() {
        $('#deleteConfirmModal' + agreementId).modal('show');
    });
    
    $('#confirmDeleteBtn' + agreementId).on('click', function() {
        deleteAgreement(agreementId);
    });
    
    // File input validation
    $('#agreement_file' + agreementId).on('change', function() {
        const file = this.files[0];
        const uploadStatus = $('#uploadStatus' + agreementId);
        
        if (file) {
            if (file.type !== 'application/pdf') {
                uploadStatus.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Please select a PDF file only.
                    </div>
                `);
                this.value = '';
                return;
            }
            
            // Check for 1GB limit (1073741824 bytes)
            if (file.size > 1073741824) {
                const fileSize = (file.size / (1024 * 1024 * 1024)).toFixed(2);
                uploadStatus.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>File size (${fileSize}GB) exceeds the maximum limit of 1GB.
                    </div>
                `);
                this.value = '';
                return;
            }
            
            // Show file size info for large files
            const fileSize = file.size / (1024 * 1024);
            if (fileSize > 100) { // Show size info for files larger than 100MB
                const sizeText = fileSize > 1024 ? 
                    `${(fileSize / 1024).toFixed(2)}GB` : 
                    `${fileSize.toFixed(2)}MB`;
                
                uploadStatus.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Large file selected (${sizeText}). Upload may take several minutes.
                    </div>
                `);
            } else {
                uploadStatus.empty();
            }
        }
    });
});

// Check if agreement already exists
// Alternative fix - replace the checkExistingAgreement function:
function checkExistingAgreement(agreementId) {
    const agreementType = 'InCash';
    
    $.ajax({
        url: '/agreements/' + agreementId + '/' + agreementType,
        type: 'HEAD', // Use HEAD request to check if file exists without downloading
        success: function(data, status, xhr) {
            // If successful, the agreement exists
            const pdfUrl = '/agreements/' + agreementId + '/' + agreementType;
            currentPdfUrl = pdfUrl;
            displayPDF(pdfUrl, agreementId);
            showAgreementManagement(agreementId);
        },
        error: function(xhr) {
            // If 404 or any error, assume no agreement exists
            showUploadSection(agreementId);
        }
    });
}

// Display PDF with multiple fallback methods
function displayPDF(pdfUrl, agreementId) {
    $('#emptyState' + agreementId).hide();
    
    const content = `
        <div class="text-center mb-3">
            <h6 class="text-primary">
                <i class="fas fa-file-pdf me-2"></i>Agreement Document
            </h6>
        </div>
        <div class="pdf-viewer-container" id="pdfContainer${agreementId}">
            <div class="pdf-loading-overlay" id="pdfLoading${agreementId}">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading PDF...</p>
                    <p class="small text-muted">Large files may take longer to load</p>
                </div>
            </div>
        </div>
    `;
    
    $('#agreementContent' + agreementId).html(content);
    
    // Try different methods to display PDF
    tryDisplayMethods(pdfUrl, agreementId);
}

function tryDisplayMethods(pdfUrl, agreementId) {
    const container = $('#pdfContainer' + agreementId);
    const loading = $('#pdfLoading' + agreementId);
    
    // Method 1: Try direct embed
    const embed = `<embed src="${pdfUrl}#view=FitH" type="application/pdf" class="pdf-embed" id="pdfEmbed${agreementId}">`;
    container.append(embed);
    
    // Check if embed loaded after 5 seconds (longer for large files)
    setTimeout(() => {
        const embedElement = $('#pdfEmbed' + agreementId)[0];
        
        if (!embedElement || embedElement.clientHeight === 0) {
            // Method 2: Try Google Docs Viewer
            container.html(`
                <iframe src="https://docs.google.com/viewer?url=${encodeURIComponent(pdfUrl)}&embedded=true" 
                        class="pdf-embed" 
                        id="pdfIframe${agreementId}">
                </iframe>
            `);
            
            // Check if Google Docs Viewer loaded
            setTimeout(() => {
                const iframe = $('#pdfIframe' + agreementId)[0];
                if (!iframe || iframe.clientHeight === 0) {
                    // Method 3: Fallback with manual controls
                    showPDFError(pdfUrl, agreementId);
                } else {
                    loading.hide();
                }
            }, 5000);
        } else {
            loading.hide();
        }
    }, 5000);
}

function showPDFError(pdfUrl, agreementId) {
    const container = $('#pdfContainer' + agreementId);
    container.html(`
        <div class="pdf-error-state">
            <div class="text-center">
                <i class="fas fa-file-pdf fa-4x text-muted mb-3"></i>
                <h5>PDF Preview Not Available</h5>
                <p class="text-muted mb-2">Your browser doesn't support embedded PDF viewing.</p>
                <p class="text-muted mb-4 small">Large PDF files may not display properly in the browser.</p>
                <div class="btn-group" role="group">
                    <a href="${pdfUrl}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Open in New Tab
                    </a>
                    <a href="${pdfUrl}" download class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
    `);
}

// Show/hide sections
function showAgreementManagement(agreementId) {
    $('#uploadSection' + agreementId).slideUp();
    $('#agreementManagement' + agreementId).slideDown();
    $('#replaceBtn' + agreementId).show();
}

function showUploadSection(agreementId) {
    $('#agreementManagement' + agreementId).slideUp();
    $('#uploadSection' + agreementId).slideDown();
    $('#replaceBtn' + agreementId).hide();
    $('#agreementContent' + agreementId).html(`
        <div class="text-center py-5" id="emptyState${agreementId}">
            <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
            <p class="text-muted">No agreement uploaded yet. Please upload a PDF file above.</p>
        </div>
    `);
}

// Delete agreement
function deleteAgreement(agreementId) {
    const deleteBtn = $('#confirmDeleteBtn' + agreementId);
    deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Deleting...');
    
    $.ajax({
        url: '{{ url("/agreements") }}/' + agreementId,
        type: 'DELETE',
        data: {
            '_token': '{{ csrf_token() }}'
        },
        success: function(response) {
            $('#deleteConfirmModal' + agreementId).modal('hide');
            showUploadSection(agreementId);
            
            // Show success message
            $('#uploadStatus' + agreementId).html(`
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Agreement deleted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        },
        error: function(xhr) {
            $('#deleteConfirmModal' + agreementId).modal('hide');
            
            $('#uploadStatus' + agreementId).html(`
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Failed to delete agreement. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        },
        complete: function() {
            deleteBtn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i>Delete Agreement');
        }
    });
}

// PDF action functions
window['openPDFNewTab' + {{ $cash->id }}] = function() {
    if (currentPdfUrl) {
        window.open(currentPdfUrl, '_blank');
    }
};

window['downloadPDF' + {{ $cash->id }}] = function() {
    if (currentPdfUrl) {
        const link = document.createElement('a');
        link.href = currentPdfUrl;
        link.download = 'agreement-{{ $cash->id }}.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

window['printPDF' + {{ $cash->id }}] = function() {
    if (currentPdfUrl) {
        const printWindow = window.open(currentPdfUrl, '_blank');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        });
    }
};

window['showUploadSection' + {{ $cash->id }}] = function() {
    showUploadSection({{ $cash->id }});
};
</script>
                                                <br>
                                                 <button class="btn btn-outline-primary btn-sm"
                                                    onclick="openReceiptModal('deposit', 
                                                                            {{ $cash->Amount }}, 
                                                                            {{ $cash->paid_amount }}, 
                                                                            'INITIAL DEPOSIT', 
                                                                            '{{ $cash->Client_Name }}', 
                                                                            'Initial Deposit', 
                                                                            'Cleared', 
                                                                            '{{ $cash->id }}', 
                                                                            '{{ $cash->created_at }}')"
                                                    data-bs-toggle="tooltip"
                                                    title="Print Receipt">
                                                <i class="fas fa-print me-1"></i>
                                            </button>
                                            <!-- Receipt Download Modal -->
<!-- Receipt Download Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="receiptModalLabel">
                    <i class="fas fa-receipt me-2"></i>Payment Receipt
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="receiptContent">
                <!-- Receipt Content -->
                <div class="receipt-container" style="background: white; padding: 25px; font-family: 'Arial', sans-serif; width: 100%; max-width: 580px; margin: 0 auto; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <!-- Header Section -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #007bff;">
                        <!-- Company Details -->
                        <div style="flex: 1; padding-right: 20px;">
                            <h2 style="font-size: 18px; font-weight: bold; margin: 0 0 8px 0; color: #2c3e50;">Kelmer's House of Cars LTD</h2>
                            <div style="font-size: 12px; line-height: 1.4; color: #555;">
                                <div style="margin-bottom: 2px;">Jabavu Lane, Huringham</div>
                                <div style="margin-bottom: 2px;">P.O Box 9215 - 00100, Nairobi - Kenya</div>
                                <div style="margin: 4px 0 2px 0;"><strong>Email:</strong> info@kelmercars.co.ke</div>
                                <div><strong>Phone:</strong> +254 700 000 000</div>
                            </div>
                        </div>
                        
                        <!-- Logo -->
                        <div style="flex: 0 0 auto; text-align: center;">
                            <img src="{{ asset('dashboardv1/assets/images/houseofcars.png') }}" alt="Kelmer's House of Cars" style="height: 70px; width: auto;">
                        </div>
                    </div>

                    <!-- Receipt Title and Number -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="font-size: 32px; font-weight: bold; margin: 0 0 10px 0; letter-spacing: 4px; color: #2c3e50;">RECEIPT</h1>
                        <div style="background: #f8f9fa; padding: 6px 15px; border-radius: 4px; display: inline-block; border: 1px solid #dee2e6;">
                            <span style="font-size: 14px; font-weight: bold; color: #495057;">Receipt No: </span>
                            <span id="receiptNumber" style="font-size: 16px; font-weight: bold; color: #007bff;"></span>
                        </div>
                    </div>

                    <!-- Date and Customer Info -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 18px; border-left: 4px solid #007bff;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px;">
                            <div>
                                <span style="font-weight: bold; color: #495057;">Date: </span>
                                <span id="receiptDate" style="color: #6c757d;"></span>
                            </div>
                        </div>
                        
                        <div style="font-size: 14px;">
                            <span style="font-weight: bold; color: #495057;">Received from: </span>
                            <span id="customerName" style="font-weight: bold; text-transform: uppercase; color: #2c3e50;"></span>
                        </div>
                    </div>

                    <!-- Payment Details Section -->
                    <div style="border: 2px solid #007bff; padding: 18px; margin-bottom: 18px; border-radius: 6px; background: #fff;">
                        <h3 style="margin: 0 0 15px 0; color: #007bff; font-size: 16px; text-align: center; font-weight: bold;">PAYMENT DETAILS</h3>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                            <div style="margin-bottom: 10px; font-size: 13px;">
                                <span style="font-weight: bold; color: #495057;">Total Amount: </span>
                                <span style="font-size: 16px; font-weight: bold; color: #6c757d;">KSh <span id="totalAmount"></span></span>
                            </div>
                            <div style="margin-bottom: 10px; font-size: 13px;">
                                <span style="font-weight: bold; color: #495057;">Paid Amount: </span>
                                <span style="font-size: 18px; font-weight: bold; color: #007bff;">KSh <span id="paymentAmount"></span></span>
                            </div>
                            <div style="font-size: 12px;">
                                <span style="font-weight: bold; color: #495057;">Amount (In Words): </span>
                                <span id="paymentAmountWords" style="font-style: italic; color: #6c757d; text-transform: capitalize;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Thank You Section -->
                    <div style="text-align: center; margin-bottom: 18px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px;">
                        <h3 style="margin: 0 0 6px 0; color: #155724; font-weight: bold; font-size: 16px;">WITH THANKS</h3>
                        <p style="margin: 0; color: #155724; font-size: 12px;">We appreciate your business and prompt payment</p>
                    </div>

                    <!-- Terms Section -->
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
                        <div style="font-weight: bold; color: #856404; font-size: 12px;">
                            "Money once received is not refundable but transferable"
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
                        <div style="text-align: center;">
                            <div style="height: 50px; border-bottom: 2px solid #495057; margin-bottom: 8px;"></div>
                            <div style="font-size: 12px; font-weight: bold; color: #495057;">Customer Signature</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="height: 50px; border-bottom: 2px solid #495057; margin-bottom: 8px;"></div>
                            <div style="font-size: 12px; font-weight: bold; color: #495057;">For Kelmer's House of Cars</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="text-align: center; padding: 12px; background: #f8f9fa; border-radius: 6px; border-top: 2px solid #007bff;">
                        <div style="font-size: 11px; color: #6c757d; margin-bottom: 3px;" id="generatedDateTime"></div>
                        <div style="font-size: 10px; color: #6c757d; font-style: italic;">
                            Official Receipt from Kelmer's House of Cars Limited
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-success" onclick="downloadReceipt()">
                    <i class="fas fa-download me-2"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div><br> 
@if(in_array(Auth::user()->role, ['Managing-Director', 'Accountant']))
<br>  <button class="btn btn-warning btn-sm editBtn mb-1"
                                                            data-id="{{ $cash->id }}"
                                                            data-client="{{ $cash->Client_Name }}"
                                                            data-phone="{{ $cash->Phone_No }}"
                                                            data-email="{{ $cash->email }}"
                                                            data-kra="{{ $cash->KRA }}"
                                                            data-national="{{ $cash->National_ID }}"
                                                            data-amount="{{ $cash->Amount }}"
                                                            data-paid_amount="{{ $cash->paid_amount }}">
                                                            <i class="fas fa-edit me-1"></i> Edit
                                                        </button>
                                                         @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <h5>No cash transactions found</h5>
                                                    <p>Click "Add Transaction" to create your first cash transaction.</p>
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
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="CashupdateForm">
                    @csrf
                    @method('POST')
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="editModalLabel">
                                <i class="fas fa-edit me-2"></i>Update Transaction Details
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="recordId">
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="clientName" class="form-label">Client Name</label>
                                    <input type="text" class="form-control" name="Client_Name" id="clientName" required>
                                </div>
                                <div class="col">
                                    <label for="phoneNo" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="Phone_No" id="phoneNo" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="emailAddress" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="emailAddress">
                                </div>
                                <div class="col">
                                    <label for="kra" class="form-label">KRA</label>
                                    <input type="text" class="form-control" name="KRA" id="kra">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="nationalId" class="form-label">National ID</label>
                                    <input type="number" class="form-control" name="National_ID" id="nationalId">
                                </div>
                                <div class="col">
                                    <label for="amount" class="form-label">Total Amount (Ksh)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" class="form-control" name="Amount" id="amount" required>
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="amount" class="form-label">Paid Amount (Ksh)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" class="form-control" name="PaidAmount" id="paid_amount" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Record
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> <!-- container-fluid -->
<script>
    function downloadReceiptDirect() {
    try {
        const receiptContent = document.getElementById('receiptContent');
        
        if (!receiptContent) {
            alert('Receipt content not found!');
            return;
        }

        // Get customer name for filename
        const customerNameElement = document.getElementById('customerName');
        const customerName = customerNameElement ? customerNameElement.textContent.trim() : 'Customer';
        const fileName = customerName.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') || 'Receipt';

        // Create HTML content with existing receipt
        const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <title>${fileName} Receipt</title>
    <meta charset="UTF-8">
    <style>
        body { 
            margin: 20px; 
            font-family: Arial, sans-serif; 
            background: white;
        }
        @media print {
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    ${receiptContent.innerHTML}
</body>
</html>`;

        // Create and download as HTML file
        const blob = new Blob([htmlContent], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        
        const downloadLink = document.createElement('a');
        downloadLink.href = url;
        downloadLink.download = `${fileName}_Receipt.html`;
        downloadLink.style.display = 'none';
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        URL.revokeObjectURL(url);
        
    } catch (error) {
        console.error('Error downloading receipt:', error);
        alert('Error downloading receipt. Please try again.');
    }
}
// Function to convert numbers to words
function numberToWords(num) {
    if (num === 0) return "zero";
    
    const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
    const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    const teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    
    function convertHundreds(n) {
        let result = '';
        
        if (n >= 100) {
            result += ones[Math.floor(n / 100)] + ' hundred';
            n %= 100;
            if (n > 0) result += ' and ';
        }
        
        if (n >= 20) {
            result += tens[Math.floor(n / 10)];
            n %= 10;
            if (n > 0) result += '-' + ones[n];
        } else if (n >= 10) {
            result += teens[n - 10];
        } else if (n > 0) {
            result += ones[n];
        }
        
        return result;
    }
    
    if (num < 0) {
        return 'negative ' + numberToWords(-num);
    }
    
    if (num < 1000) {
        return convertHundreds(num);
    }
    
    if (num < 1000000) {
        const thousands = Math.floor(num / 1000);
        const remainder = num % 1000;
        let result = convertHundreds(thousands) + ' thousand';
        if (remainder > 0) {
            result += ' ' + convertHundreds(remainder);
        }
        return result;
    }
    
    if (num < 1000000000) {
        const millions = Math.floor(num / 1000000);
        const remainder = num % 1000000;
        let result = convertHundreds(millions) + ' million';
        if (remainder > 0) {
            result += ' ' + numberToWords(remainder);
        }
        return result;
    }
    
    if (num < 1000000000000) {
        const billions = Math.floor(num / 1000000000);
        const remainder = num % 1000000000;
        let result = convertHundreds(billions) + ' billion';
        if (remainder > 0) {
            result += ' ' + numberToWords(remainder);
        }
        return result;
    }
    
    return 'number too large';
}

// Function to convert currency amount to words
function amountToWords(amount) {
    // Handle decimal amounts (cents)
    const parts = amount.toString().split('.');
    const wholePart = parseInt(parts[0]) || 0;
    const decimalPart = parts[1] ? parseInt(parts[1].padEnd(2, '0').slice(0, 2)) : 0;
    
    let result = '';
    
    if (wholePart > 0) {
        result += numberToWords(wholePart);
        result += wholePart === 1 ? ' shilling' : ' shillings';
    }
    
    if (decimalPart > 0) {
        if (wholePart > 0) result += ' and ';
        result += numberToWords(decimalPart);
        result += decimalPart === 1 ? ' cent' : ' cents';
    }
    
    if (wholePart === 0 && decimalPart === 0) {
        result = 'zero shillings';
    }
    
    return result + ' only';
}

// Updated function with corrected parameters - removed vehicle registration, phone, email, KRA, and National ID
function openReceiptModal(type, totalAmount, paidAmount, description, customerName, paymentMethod, status, reference, paymentDate) {
    try {
        console.log('Opening receipt modal with data:', {type, totalAmount, paidAmount, description, customerName});
        
        // Helper function to safely set element content
        function safeSetContent(elementId, content) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = content;
            } else {
                console.warn(`Element with ID '${elementId}' not found`);
            }
        }
        
        // Update modal content with dynamic data using safe method
        safeSetContent('paymentAmount', new Intl.NumberFormat().format(paidAmount));
        safeSetContent('totalAmount', new Intl.NumberFormat().format(totalAmount));
        
        // Convert paid amount to words and update the words field
        const amountInWords = amountToWords(paidAmount);
        safeSetContent('paymentAmountWords', amountInWords);
        
        safeSetContent('paymentDescription', description);
        safeSetContent('customerName', customerName);
        safeSetContent('paymentMethod', paymentMethod);
        safeSetContent('paymentStatus', status);
        safeSetContent('referenceNumber', reference);
        
        // Set date and time - use payment date if provided, otherwise current date
        const receiptDate = paymentDate ? new Date(paymentDate) : new Date();
        safeSetContent('receiptDate', receiptDate.toLocaleDateString('en-GB'));
        
        // Update generated date time
        const currentDateTime = new Date();
        safeSetContent('generatedDateTime', 
            `Generated on ${currentDateTime.toLocaleDateString('en-GB')} at ${currentDateTime.toLocaleTimeString('en-GB')} | Thank you for your business!`);
        
        // Generate dynamic receipt number based on reference and timestamp
        const receiptNumber = `RCP-${reference}-${Math.floor(Math.random() * 900) + 100}`;
        safeSetContent('receiptNumber', receiptNumber);
        
        // Show modal using jQuery if Bootstrap 4, or Bootstrap 5 method
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            $('#receiptModal').modal('show');
        } else if (typeof bootstrap !== 'undefined') {
            var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
            receiptModal.show();
        } else {
            // Fallback - show modal manually
            const modal = document.getElementById('receiptModal');
            if (modal) {
                modal.style.display = 'block';
                modal.classList.add('show');
            } else {
                console.error('Receipt modal not found in DOM');
                alert('Receipt modal not found. Please check your HTML structure.');
                return;
            }
        }
        
    } catch (error) {
        console.error('Error opening receipt modal:', error);
        alert('Error opening receipt. Please try again.');
    }
}

function downloadReceipt() {
    // Get the customer name from the receipt
    var customerNameElement = document.getElementById('customerName');
    var customerName = customerNameElement ? customerNameElement.textContent.trim() : 'Receipt';
    
    // Clean the customer name for filename (remove special characters)
    var fileName = customerName.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') || 'Receipt';
    
    // Try multiple selectors to find the receipt content
    var receiptContainer = document.querySelector('.receipt-container') || 
                          document.querySelector('#receiptContent') ||
                          document.querySelector('.modal-body');
    
    if (!receiptContainer) {
        alert('Receipt content not found');
        return;
    }
    
    var printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${fileName}_Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                @page { size: A4; margin: 1cm; }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            ${receiptContainer.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
}


function verifyPayment(paymentId) {
    if (confirm('Are you sure you want to verify this payment?')) {
        // Add your verification logic here
        // You can make an AJAX call to verify the payment
        console.log('Verifying payment ID:', paymentId);
        
        // Example AJAX call (uncomment and modify as needed):
        /*
        fetch(`/payments/${paymentId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated status
            }
        });
        */
    }
}

// Initialize Bootstrap tooltips and modals when document loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing components...');
    
    // Initialize Bootstrap tooltips
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        console.log('Bootstrap 5 tooltips initialized');
    } else if (typeof jQuery !== 'undefined' && jQuery.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
        console.log('Bootstrap 4/jQuery tooltips initialized');
    }
    
    // Test modal availability
    const modal = document.getElementById('receiptModal');
    if (modal) {
        console.log('Receipt modal found in DOM');
    } else {
        console.error('Receipt modal not found in DOM');
    }
});
</script>
    <!-- Custom CSS -->
     <!-- Additional CSS for enhanced styling -->
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.cursor-pointer {
    cursor: pointer;
}

.thumbnail-img:hover {
    opacity: 0.8;
    transform: scale(1.05);
    transition: all 0.2s ease-in-out;
}

.spec-item:hover,
.owner-card:hover,
.pricing-card:hover,
.bid-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s ease-in-out;
}

.section-title {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
    letter-spacing: 0.5px;
}

.carousel-inner {
    border: 3px solid #fff;
}

.modal-content {
    border-radius: 12px;
    overflow: hidden;
}

.vehicle-header h3 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 600;
}

.price-amount {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 700;
}

.no-photos {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
}

.error-state {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff3cd 100%);
    border-radius: 8px;
    margin: 2rem;
}

@media (max-width: 768px) {
    .modal-xl {
        margin: 0.5rem;
    }
    
    .carousel-inner {
        height: 250px !important;
    }
    
    .col-lg-7, .col-lg-5 {
        padding: 1rem !important;
    }
}
</style>
    <style>
        /* Print-specific styles for modal content */
@media print {
    .modal-header, 
    .modal-footer, 
    .btn, 
    .navbar, 
    .sidebar,
    .no-print {
        display: none !important;
    }
    
    .modal {
        position: static !important;
        display: block !important;
        width: auto !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }
    
    .modal-dialog {
        width: auto !important;
        max-width: none !important;
        margin: 0 !important;
        background: white !important;
    }
    
    .modal-content {
        border: none !important;
        box-shadow: none !important;
        background: white !important;
    }
    
    .modal-body {
        padding: 0 !important;
        background: white !important;
    }
    
  
}



.header-content {
    position: relative;
    z-index: 2;
}

.company-logo {
    margin-bottom: 25px;
}

.company-logo img {
    max-height: 140px;
    width: auto;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
}

.company-name {
    font-size: 36px;
    font-weight: 800;
    margin: 20px 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 3px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    font-family: 'Arial Black', sans-serif;
}

.company-tagline {
    font-size: 18px;
    color: rgba(255,255,255,0.95);
    font-style: italic;
    margin-bottom: 20px;
    font-weight: 300;
    letter-spacing: 1px;
}

.company-details {
    font-size: 16px;
    color: rgba(255,255,255,0.9);
    line-height: 2;
    max-width: 600px;
    margin: 0 auto;
}


.document-body {
    padding: 40px;
    background: #ffffff;
}

.section-box {
    margin: 35px 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 1px solid #e8ecef;
}

.section-header {
    background: linear-gradient(135deg, #34495e, #2c3e50);
    color: white;
    padding: 20px 25px;
    font-weight: 700;
    font-size: 18px;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
}

.section-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3498db, #2980b9);
}

.section-content {
    padding: 30px 25px;
    background: #ffffff;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin: 20px 0;
}

.info-card {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    padding: 20px;
    border-left: 5px solid #3498db;
    border-radius: 0 10px 10px 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s ease;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.info-label {
    font-weight: 700;
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 5px;
}

.info-value {
    color: #34495e;
    font-size: 16px;
    line-height: 1.6;
    font-weight: 500;
}

.price-section {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    padding: 30px;
    border-radius: 15px;
    border: 3px solid #f39c12;
    margin: 30px 0;
    text-align: center;
    box-shadow: 0 8px 25px rgba(243,156,18,0.2);
}

.price-amount {
    font-size: 36px;
    font-weight: 900;
    color: #d68910;
    margin: 15px 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-family: 'Arial Black', sans-serif;
}

.price-words {
    font-size: 16px;
    font-style: italic;
    color: #856404;
    margin-bottom: 20px;
    font-weight: 600;
}

.payment-status {
    padding: 25px;
    margin: 25px 0;
    border-radius: 12px;
    text-align: center;
    font-size: 20px;
    font-weight: 700;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.payment-status.paid-full {
    background: linear-gradient(135deg, #d5f4e6, #a8e6cf);
    color: #27ae60;
    border: 3px solid #27ae60;
}

.payment-status.balance-due {
    background: linear-gradient(135deg, #fadbd8, #f1948a);
    color: #c0392b;
    border: 3px solid #e74c3c;
}

.payment-status .status-icon {
    font-size: 24px;
    margin-right: 10px;
}

.terms-list {
    list-style: none;
    padding: 0;
}

.terms-list li {
    background: #f8f9fa;
    margin: 15px 0;
    padding: 18px;
    border-left: 4px solid #3498db;
    border-radius: 0 8px 8px 0;
    font-size: 15px;
    line-height: 1.7;
}

.terms-list li strong {
    color: #2c3e50;
    font-weight: 700;
}

.signature-section {
    margin-top: 60px;
    padding: 40px;
    background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
    border-radius: 15px;
    border: 2px solid #95a5a6;
}

.signature-title {
    text-align: center;
    font-size: 24px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 40px;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-bottom: 3px solid #3498db;
    padding-bottom: 10px;
}

.signature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    margin: 40px 0;
}

.signature-box {
    background: white;
    padding: 30px;
    border: 2px solid #bdc3c7;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.signature-role {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    padding: 12px 20px;
    border-radius: 25px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 20px;
    font-size: 14px;
    letter-spacing: 1px;
}

.signature-line {
    border-bottom: 3px solid #2c3e50;
    height: 60px;
    margin: 25px 0 15px 0;
    position: relative;
}

.signature-line::after {
    content: 'Signature';
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    color: #7f8c8d;
    font-style: italic;
}

.signature-details {
    font-size: 14px;
    color: #2c3e50;
    line-height: 1.8;
    font-weight: 500;
}

.bank-details {
    background: linear-gradient(135deg, #e8f4fd, #d6eaf8);
    padding: 35px;
    border: 3px solid #3498db;
    border-radius: 15px;
    margin: 40px 0;
    box-shadow: 0 8px 25px rgba(52,152,219,0.2);
}

.bank-title {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 18px 25px;
    margin: -35px -35px 25px -35px;
    border-radius: 12px 12px 0 0;
    font-weight: 800;
    text-align: center;
    font-size: 20px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.bank-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.bank-table td {
    padding: 16px 20px;
    border: 1px solid #ecf0f1;
    font-size: 15px;
    font-weight: 500;
}

.bank-table td:first-child {
    background: linear-gradient(135deg, #f8f9fa, #ecf0f1);
    font-weight: 700;
    color: #2c3e50;
    width: 35%;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

.bank-table td:last-child {
    color: #34495e;
    font-weight: 600;
}

.document-footer {
    text-align: center;
    margin-top: 50px;
    padding: 25px;
    background: linear-gradient(135deg, #f8f9fa, #ecf0f1);
    border-radius: 10px;
    border-top: 3px solid #3498db;
    font-size: 14px;
    color: #7f8c8d;
    line-height: 1.8;
}

.document-footer .generation-info {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.document-footer .copyright {
    font-size: 12px;
    color: #95a5a6;
}




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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Add this right after your Chart.js code and before the existing functions

// Debug: Log that script is loading
console.log('Agreement script loading...');

// Store transaction data - with debugging
const cashTransactions = @json($inCashes ?? []);
const importCars = @json($importCars ?? []);
const customerCars = @json($customerCars ?? []);

console.log('Cash transactions loaded:', cashTransactions);
console.log('Import cars loaded:', importCars);
console.log('Customer cars loaded:', customerCars);

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up agreement buttons...');
    
    // Agreement button functionality with debugging
    const agreementButtons = document.querySelectorAll('.agreementBtn');
    console.log('Found agreement buttons:', agreementButtons.length);
    
    agreementButtons.forEach((button, index) => {
        console.log(`Setting up button ${index}:`, button);
        
        button.addEventListener('click', function() {
            console.log('Agreement button clicked!');
            const cashId = this.getAttribute('data-cash-id');
            console.log('Cash ID:', cashId);
            generateProfessionalAgreement(cashId);
        });
    });

});


        // Sales Performance Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                datasets: [{
                    label: 'Monthly Sales (Ksh)',
                    data: {!! json_encode($monthlyData ?? [0, 0, 0, 0, 0, 0]) !!},
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KES ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Status filter functionality
        document.querySelectorAll('.status-filter').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.status-filter').forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                const status = this.getAttribute('data-status');
                const table = document.getElementById('responsive-datatable');
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    if (status === 'all') {
                        row.style.display = '';
                    } else if (status === 'pending') {
                        const actionCell = row.querySelector('td:last-child');
                        if (actionCell && actionCell.textContent.includes('Approve')) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    } else if (status === 'approved') {
                        const actionCell = row.querySelector('td:last-child');
                        if (actionCell && actionCell.textContent.includes('Approved')) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });

         // Edit button functionality with debugging
        document.querySelectorAll('.editBtn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const client = this.getAttribute('data-client');
                const phone = this.getAttribute('data-phone');
                const email = this.getAttribute('data-email');
                const kra = this.getAttribute('data-kra');
                const national = this.getAttribute('data-national');
                const amount = this.getAttribute('data-amount');
                const paid_amount = this.getAttribute('data-paid_amount');

                // Debug: Log all values to console
                console.log('Debug values:');
                console.log('ID:', id);
                console.log('Client:', client);
                console.log('Phone:', phone);
                console.log('Email:', email);
                console.log('KRA:', kra);
                console.log('National:', national);
                console.log('Amount:', amount);
                console.log('Paid Amount:', paid_amount);

                // Check if paid_amount field exists
                const paidAmountField = document.getElementById('paid_amount');
                console.log('Paid amount field found:', paidAmountField);

                // Populate edit modal
                document.getElementById('recordId').value = id || '';
                document.getElementById('clientName').value = client || '';
                document.getElementById('phoneNo').value = phone || '';
                document.getElementById('emailAddress').value = email || '';
                document.getElementById('kra').value = kra || '';
                document.getElementById('nationalId').value = national || '';
                document.getElementById('amount').value = amount || '';
                
                // Set paid amount with extra debugging
                if (paidAmountField) {
                    paidAmountField.value = paid_amount || '';
                    console.log('Set paid amount to:', paid_amount);
                    console.log('Field value after setting:', paidAmountField.value);
                } else {
                    console.error('Paid amount field not found!');
                }

                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            });
        });

        // Form validation and formatting
        document.getElementById('Amount').addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            if (value) {
                this.value = parseFloat(value).toFixed(2);
            }
        });

        document.getElementById('amount').addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            if (value) {
                this.value = parseFloat(value).toFixed(2);
            }
        });

        // Add loading states to buttons
        document.querySelectorAll('.approveBtn, .deleteBtn').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
                this.disabled = true;
                
                // Re-enable after 3 seconds (adjust based on your actual request time)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            });
        });

        // Real-time search functionality
        function addSearchFunctionality() {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'form-control mb-3';
            searchInput.placeholder = 'Search transactions by client name, email, or car details...';
            
            const tableCard = document.querySelector('.card .card-body');
            tableCard.insertBefore(searchInput, tableCard.querySelector('.table-responsive'));
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#responsive-datatable tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Initialize search functionality
        document.addEventListener('DOMContentLoaded', function() {
            addSearchFunctionality();
        });
    </script>
    
</x-app-layout>