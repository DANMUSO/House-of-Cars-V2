<x-app-layout>
<div class="container-fluid" data-fleet-id="{{ $fleet->id }}">
    <!-- Fleet Data for JavaScript -->
    <script>
        window.fleetData = {
            id: {{ $fleet->id }},
            status: '{{ $fleet->status }}',
            outstandingBalance: {{ $fleet->outstanding_balance }},
            monthlyInstallment: {{ $fleet->monthly_installment }},
            paidPercentage: {{ $paidPercentage ?? 0 }}
        };
    </script>

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('fleetacquisition') }}">Fleet Acquisition</a></li>
                    &nbsp&nbsp&nbsp <li class="" aria-current="page">Manage Fleet</li>
                </ol>
            </nav>
            <h4 class="fs-18 fw-semibold m-0">
                <i class="fa-solid fa-people-carry-box text-primary"></i> 
                {{ $fleet->vehicle_make }} {{ $fleet->vehicle_model }} ({{ $fleet->vehicle_year }})
            </h4>
            <small class="text-muted">Chassis: {{ $fleet->chassis_number }}</small>
        </div>
        <div class="flex-grow-1 text-end">
            <span class="badge 
                @if($fleet->status == 'pending') bg-warning
                @elseif($fleet->status == 'approved') bg-success
                @elseif($fleet->status == 'active') bg-primary
                @elseif($fleet->status == 'completed') bg-info
                @else bg-danger
                @endif fs-6 me-2">
                {{ ucfirst($fleet->status) }}
            </span>
            
            @if($fleet->status == 'pending')
                <button class="btn btn-success btn-sm me-2" onclick="approveFleet({{ $fleet->id }})">
                    <i class="fas fa-check"></i> Approve
                </button>
            @endif
            
            @if(in_array($fleet->status, ['approved', 'active']))
                <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-money-bill"></i> Record Payment
                </button>
            @endif
            
            <a href="{{ route('fleetacquisition') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Fleet Overview Cards -->
    <div class="row mb-8">
        <!-- Financial Summary Card -->
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie text-primary"></i> Financial Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Purchase Price</h6>
                                <h4 class="text-dark">KSh {{ number_format($fleet->purchase_price, 0) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Down Payment</h6>
                                <h4 class="text-success">KSh {{ number_format($fleet->down_payment, 0) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Amount Paid</h6>
                                <h4 class="text-info">KSh {{ number_format($fleet->amount_paid, 0) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Outstanding</h6>
                                <h4 class="text-danger" data-outstanding-balance="{{ $fleet->outstanding_balance }}">KSh {{ number_format($fleet->outstanding_balance, 0) }}</h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Payment Progress</span>
                            <span class="text-muted">{{ number_format($paidPercentage ?? 0, 1) }}% Complete</span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar bg-gradient" role="progressbar" 
                                 style="width: {{ $paidPercentage ?? 0 }}%"
                                 aria-valuenow="{{ $paidPercentage ?? 0 }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">{{ $fleet->payments_made ?? 0 }} of {{ $fleet->loan_duration_months }} payments made</small>
                            <small class="text-muted" data-monthly-installment="{{ $fleet->monthly_installment }}">KSh {{ number_format($fleet->monthly_installment, 0) }} monthly</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Different Sections -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="fleetTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                        <i class="fas fa-money-bill-wave"></i> Payment History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                        <i class="fas fa-calendar-alt"></i> Payment Schedule
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                        <i class="fas fa-info-circle"></i> Vehicle Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Legal & Compliance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fleet-documents-tab" data-bs-toggle="tab" 
                            data-bs-target="#fleet-documents" type="button" role="tab">
                        <i class="fas fa-file-pdf"></i> Documents
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="fleetTabsContent">
                <!-- Fleet Documents Tab -->
<div class="tab-pane fade" id="fleet-documents" role="tabpanel" aria-labelledby="fleet-documents-tab">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Legal Documents & Agreements</h5>
        <button class="btn btn-primary btn-sm" onclick="openUploadModal()">
            <i class="fas fa-upload"></i> Upload Documents
        </button>
    </div>
    
    <div id="documentsContainer">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading documents...</p>
        </div>
    </div>
    <!-- Document Viewer Modal -->
<div class="modal fade" id="docViewerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="docTitle">Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="docFrame" style="width:100%; height:100%; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadDocsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadDocsForm" enctype="multipart/form-data">
                    @csrf
                    <input type="file" class="form-control" name="documents[]" multiple 
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" required>
                    <small class="text-muted">Max 50MB per file. PDF, DOC, DOCX, JPG, PNG, GIF allowed.</small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="uploadDocuments()">Upload</button>
            </div>
        </div>
    </div>
</div>
                
                <!-- Payment History Tab -->
                <div class="tab-pane fade show active" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Payment History</h5>
                        @if(in_array($fleet->status, ['approved', 'active']))
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-plus"></i> Add Payment
                            </button>
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Balance Before</th>
                                    <th>Balance After</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments ?? [] as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_number ?? $loop->iteration }}</td>
                                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'N/A' }}</td>
                                        <td><strong class="text-success">KSh {{ number_format($payment->payment_amount ?? 0, 2) }}</strong></td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ str_replace('_', ' ', ucwords($payment->payment_method ?? 'N/A')) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->reference_number ?? '-' }}</td>
                                        <td>KSh {{ number_format($payment->balance_before ?? 0, 2) }}</td>
                                        <td>KSh {{ number_format($payment->balance_after ?? 0, 2) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if(($payment->status ?? 'pending') == 'confirmed') bg-success
                                                @elseif(($payment->status ?? 'pending') == 'pending') bg-warning
                                                @else bg-danger
                                                @endif">
                                                {{ ucfirst($payment->status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(($payment->status ?? 'pending') == 'pending')
                                                <button class="btn btn-sm btn-success" onclick="confirmPayment({{ $payment->id }})">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-outline-secondary" onclick="viewPayment({{ $payment->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-money-bill-wave fa-2x mb-2 opacity-50"></i>
                                            <p>No payments recorded yet</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Schedule Tab -->
                <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Payment Schedule</h5>
                        <small class="text-muted">{{ $fleet->loan_duration_months ?? 0 }} monthly installments</small>
                    </div>
                    
                    <style>
                        .schedule-card {
                            border-radius: 12px;
                            border: none;
                            margin-bottom: 1rem;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                            transition: transform 0.2s ease;
                        }
                        
                        .schedule-card:hover {
                            transform: translateY(-2px);
                        }
                        
                        .schedule-header {
                            border-radius: 12px 12px 0 0;
                            padding: 1rem;
                            color: white;
                            font-weight: 600;
                        }
                        
                        .schedule-header.paid {
                            background: linear-gradient(135deg, #28a745, #20c997);
                        }
                        
                        .schedule-header.pending {
                            background: linear-gradient(135deg, #fd7e14, #e74c3c);
                        }
                        
                        .schedule-header.overdue {
                            background: linear-gradient(135deg, #dc3545, #c82333);
                        }
                        
                        .schedule-body {
                            padding: 1.5rem;
                            background: #2c3e50;
                            color: white;
                            border-radius: 0 0 12px 12px;
                        }
                        
                        .schedule-confirm-btn {
                            background: #28a745;
                            border: none;
                            border-radius: 8px;
                            padding: 8px 16px;
                            color: white;
                            font-weight: 500;
                            transition: background 0.2s ease;
                        }
                        
                        .schedule-confirm-btn:hover {
                            background: #218838;
                        }
                        
                        .status-badge {
                            font-size: 0.8rem;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-weight: 600;
                        }
                        
                        .status-badge.paid {
                            background: #d4edda;
                            color: #155724;
                        }
                        
                        .status-badge.pending {
                            background: #fff3cd;
                            color: #856404;
                        }
                        
                        .status-badge.overdue {
                            background: #f8d7da;
                            color: #721c24;
                        }
                    </style>

                    <div class="row">
                        @php
                            // Generate payment schedule
                            $startDate = $fleet->first_payment_date ? $fleet->first_payment_date : now();
                            $monthlyAmount = $fleet->monthly_installment ?? 0;
                            $totalMonths = $fleet->loan_duration_months ?? 0;
                            $paidPayments = $payments ?? collect();
                            
                            $schedule = [];
                            for ($i = 1; $i <= $totalMonths; $i++) {
                                $dueDate = $startDate->copy()->addMonths($i - 1);
                                $payment = $paidPayments->where('payment_number', $i)->first();
                                
                                $status = 'pending';
                                if ($payment && $payment->status === 'confirmed') {
                                    $status = 'paid';
                                } elseif ($dueDate < now() && !$payment) {
                                    $status = 'overdue';
                                }
                                
                                $schedule[] = [
                                    'number' => $i,
                                    'due_date' => $dueDate,
                                    'amount' => $monthlyAmount,
                                    'status' => $status,
                                    'payment' => $payment
                                ];
                            }
                        @endphp

                        @foreach($schedule as $installment)
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                <div class="schedule-card">
                                    <div class="schedule-header {{ $installment['status'] }}">
                                        Installment #{{ $installment['number'] }} - {{ ucfirst($installment['status']) }}
                                    </div>
                                    <div class="schedule-body">
                                        <div class="mb-3">
                                            <strong>Due Date:</strong><br>
                                            {{ $installment['due_date']->format('M d, Y') }}
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Amount:</strong><br>
                                            KES {{ number_format($installment['amount'], 2) }}
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Status:</strong>
                                            <span class="status-badge {{ $installment['status'] }} ms-2">
                                                {{ ucfirst($installment['status']) }}
                                            </span>
                                        </div>
                                        
                                        @if($installment['status'] !== 'paid')
                                        @if(in_array(Auth::user()->role, ['Managing-Director', 'Accountant']))
                                            <button class="schedule-confirm-btn" onclick="recordScheduledPayment({{ $installment['number'] }}, {{ $installment['amount'] }}, '{{ $installment['due_date']->format('Y-m-d') }}')">
                                                Confirm
                                            </button>
                                        @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Vehicle Details Tab -->
                <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                    <div class="row">
                        <!-- Vehicle Photos Card -->
                        <div class="col-xl-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fas fa-camera text-primary"></i> Vehicle Photos</h5>
                                </div>
                                <div class="card-body">
                                    
@if($fleet->vehicle_photos && count($fleet->vehicle_photos) > 0)
    <div class="row g-2">
        @foreach(array_slice($fleet->vehicle_photos, 0, 10) as $index => $photo)
            @php
                // Check if the photo path is already a full S3 URL or just the S3 key
                if (str_starts_with($photo, 'https://')) {
                    // Already a full URL
                    $photoUrl = $photo;
                } else {
                    // Generate S3 URL from the key path
                    $bucket = config('filesystems.disks.s3.bucket');
                    $region = config('filesystems.disks.s3.region');
                    $photoUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$photo}";
                }
            @endphp
            <div class="col-6">
                <div class="position-relative">
                    <img src="{{ $photoUrl }}" 
                        alt="Vehicle Photo {{ $index + 1 }}" 
                        class="img-fluid rounded vehicle-photo-thumb" 
                        style="height: 80px; width: 100%; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
                        loading="lazy"
                        onclick="viewPhoto('{{ $photoUrl }}', {{ $index }})"
                        onmouseover="this.style.transform='scale(1.05)'"
                        onmouseout="this.style.transform='scale(1)'"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    
                    <!-- Error fallback -->
                    <div style="display: none; height: 80px; width: 100%; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; align-items: center; justify-content: center; flex-direction: column;">
                        <i class="fas fa-image text-muted mb-1"></i>
                        <small class="text-muted">Failed to load</small>
                    </div>
                    
                    <!-- Photo number indicator -->
                    <span class="position-absolute top-0 start-0 bg-dark text-white px-2 py-1 rounded-end" style="font-size: 0.75rem;">
                        {{ $index + 1 }}
                    </span>
                </div>
            </div>
        @endforeach
        
        @if(count($fleet->vehicle_photos) > 10)
            <div class="col-12 mt-2">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing 10 of {{ count($fleet->vehicle_photos) }} photos
                </small>
            </div>
        @endif
    </div>
@else
    <div class="text-center text-muted py-4">
        <i class="fas fa-camera fa-3x mb-2 opacity-50"></i>
        <p class="mb-0">No photos available</p>
        <small>Photos will appear here once uploaded</small>
    </div>
@endif

<!-- Photo Viewer Modal -->
<div class="modal fade" id="photoViewerModal" tabindex="-1" aria-labelledby="photoViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoViewerModalLabel">
                    <i class="fas fa-car me-2"></i>Vehicle Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center" style="background: #000;">
                <div id="photoContainer" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <span class="text-muted" id="photoCounter"></span>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary me-2" id="prevPhoto">
                        <i class="fas fa-chevron-left me-1"></i>Previous
                    </button>
                    <button type="button" class="btn btn-outline-secondary me-2" id="nextPhoto">
                        <i class="fas fa-chevron-right me-1"></i>Next
                    </button>
                    <a href="#" target="_blank" class="btn btn-primary me-2" id="openInNewTab">
                        <i class="fas fa-external-link-alt me-1"></i>Open in New Tab
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.vehicle-photo-thumb {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.vehicle-photo-thumb:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#photoViewerModal .modal-body img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

.photo-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.photo-navigation:hover {
    background: rgba(0,0,0,0.8);
    color: white;
}

.photo-navigation.prev {
    left: 20px;
}

.photo-navigation.next {
    right: 20px;
}
</style>

<script>
let currentPhotoIndex = 0;
let allPhotos = [];

// Initialize photos array from PHP
@if($fleet->vehicle_photos && count($fleet->vehicle_photos) > 0)
allPhotos = [
    @foreach($fleet->vehicle_photos as $photo)
        @php
            if (str_starts_with($photo, 'https://')) {
                $photoUrl = $photo;
            } else {
                $bucket = config('filesystems.disks.s3.bucket');
                $region = config('filesystems.disks.s3.region');
                $photoUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$photo}";
            }
        @endphp
        '{{ $photoUrl }}'@if(!$loop->last),@endif
    @endforeach
];
@endif

function viewPhoto(photoUrl, index = 0) {
    currentPhotoIndex = index;
    
    const modal = new bootstrap.Modal(document.getElementById('photoViewerModal'));
    const photoContainer = document.getElementById('photoContainer');
    const photoCounter = document.getElementById('photoCounter');
    const openInNewTab = document.getElementById('openInNewTab');
    
    // Show loading spinner
    photoContainer.innerHTML = `
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    
    // Load the image
    const img = new Image();
    img.onload = function() {
        photoContainer.innerHTML = '';
        img.className = 'img-fluid';
        img.style.maxHeight = '80vh';
        img.style.objectFit = 'contain';
        photoContainer.appendChild(img);
    };
    
    img.onerror = function() {
        photoContainer.innerHTML = `
            <div class="text-center text-light py-5">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <p>Failed to load image</p>
                <button class="btn btn-outline-light" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i>Retry
                </button>
            </div>
        `;
    };
    
    img.src = photoUrl;
    
    // Update counter and navigation
    photoCounter.textContent = `Photo ${index + 1} of ${allPhotos.length}`;
    openInNewTab.href = photoUrl;
    
    // Show/hide navigation buttons
    document.getElementById('prevPhoto').style.display = allPhotos.length > 1 ? 'inline-block' : 'none';
    document.getElementById('nextPhoto').style.display = allPhotos.length > 1 ? 'inline-block' : 'none';
    
    modal.show();
}

// Navigation functions
document.getElementById('prevPhoto').addEventListener('click', function() {
    if (currentPhotoIndex > 0) {
        viewPhoto(allPhotos[currentPhotoIndex - 1], currentPhotoIndex - 1);
    } else {
        // Loop to last photo
        viewPhoto(allPhotos[allPhotos.length - 1], allPhotos.length - 1);
    }
});

document.getElementById('nextPhoto').addEventListener('click', function() {
    if (currentPhotoIndex < allPhotos.length - 1) {
        viewPhoto(allPhotos[currentPhotoIndex + 1], currentPhotoIndex + 1);
    } else {
        // Loop to first photo
        viewPhoto(allPhotos[0], 0);
    }
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('photoViewerModal'));
    if (modal && modal._isShown) {
        if (e.key === 'ArrowLeft') {
            document.getElementById('prevPhoto').click();
        } else if (e.key === 'ArrowRight') {
            document.getElementById('nextPhoto').click();
        } else if (e.key === 'Escape') {
            modal.hide();
        }
    }
});

// Touch/swipe support for mobile
let touchStartX = 0;
let touchEndX = 0;

document.getElementById('photoContainer').addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
});

document.getElementById('photoContainer').addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swiped left - next photo
            document.getElementById('nextPhoto').click();
        } else {
            // Swiped right - previous photo
            document.getElementById('prevPhoto').click();
        }
    }
}
</script>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>Make & Model:</strong></td><td>{{ $fleet->vehicle_make }} {{ $fleet->vehicle_model }}</td></tr>
                                        <tr><td><strong>Year:</strong></td><td>{{ $fleet->vehicle_year }}</td></tr>
                                        <tr><td><strong>Engine Capacity:</strong></td><td>{{ $fleet->engine_capacity ?? 'N/A' }}</td></tr>
                                        <tr><td><strong>Category:</strong></td><td>{{ ucfirst($fleet->vehicle_category ?? 'N/A') }}</td></tr>
                                        <tr><td><strong>Chassis Number:</strong></td><td>{{ $fleet->chassis_number }}</td></tr>
                                        <tr><td><strong>Engine Number:</strong></td><td>{{ $fleet->engine_number ?? 'N/A' }}</td></tr>
                                        <tr><td><strong>Registration:</strong></td><td>{{ $fleet->registration_number ?: 'Not registered' }}</td></tr>
                                        <tr><td><strong>Market Value:</strong></td><td>KSh {{ number_format($fleet->market_value ?? 0, 2) }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Financier Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>Institution:</strong></td><td>{{ $fleet->financing_institution ?? 'N/A' }}</td></tr>
                                        <tr><td><strong>Contact Person:</strong></td><td>{{ $fleet->financier_contact_person ?: 'Not provided' }}</td></tr>
                                        <tr><td><strong>Phone:</strong></td><td>{{ $fleet->financier_phone ?: 'Not provided' }}</td></tr>
                                        <tr><td><strong>Email:</strong></td><td>{{ $fleet->financier_email ?: 'Not provided' }}</td></tr>
                                        <tr><td><strong>Agreement Ref:</strong></td><td>{{ $fleet->financier_agreement_ref ?: 'Not provided' }}</td></tr>
                                        <tr><td><strong>Interest Rate:</strong></td><td>{{ $fleet->interest_rate ?? 0 }}%</td></tr>
                                        <tr><td><strong>Loan Duration:</strong></td><td>{{ $fleet->loan_duration_months ?? 0 }} months</td></tr>
                                        <tr><td><strong>First Payment:</strong></td><td>{{ $fleet->first_payment_date ? $fleet->first_payment_date->format('d/m/Y') : 'N/A' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legal & Compliance Tab -->
                <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Legal Documents</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>HP Agreement:</strong></td><td>{{ $fleet->hp_agreement_number ?? 'N/A' }}</td></tr>
                                        <tr><td><strong>Logbook Custody:</strong></td><td>{{ ucfirst($fleet->logbook_custody ?? 'N/A') }}</td></tr>
                                        <tr><td><strong>Company KRA PIN:</strong></td><td>{{ $fleet->company_kra_pin ?? 'N/A' }}</td></tr>
                                        <tr><td><strong>Business Permit:</strong></td><td>{{ $fleet->business_permit_number ?: 'Not provided' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Insurance Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr><td><strong>Policy Number:</strong></td><td>{{ $fleet->insurance_policy_number ?: 'Not provided' }}</td></tr>
                                        <tr><td><strong>Insurance Company:</strong></td><td>{{ $fleet->insurance_company ?: 'Not provided' }}</td></tr>
                                        <tr><td><strong>Premium:</strong></td><td>KSh {{ number_format($fleet->insurance_premium ?: 0, 2) }}</td></tr>
                                        <tr><td><strong>Expiry Date:</strong></td><td>
                                            @if($fleet->insurance_expiry_date)
                                                {{ $fleet->insurance_expiry_date->format('d/m/Y') }}
                                                @if($fleet->insurance_expiry_date < now())
                                                    <span class="badge bg-danger ms-2">Expired</span>
                                                @elseif($fleet->insurance_expiry_date < now()->addDays(30))
                                                    <span class="badge bg-warning ms-2">Expiring Soon</span>
                                                @endif
                                            @else
                                                Not provided
                                            @endif
                                        </td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-fleet-id="{{ $fleet->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="recordPaymentForm" data-fleet-id="{{ $fleet->id }}">
                        @csrf
                        <input type="hidden" name="fleet_acquisition_id" value="{{ $fleet->id }}">
                        <input type="hidden" name="payment_number" id="schedulePaymentNumber">

                        <div class="alert alert-info">
                            <strong>Outstanding Balance:</strong> <span data-outstanding-balance="{{ $fleet->outstanding_balance }}">KSh {{ number_format($fleet->outstanding_balance, 2) }}</span><br>
                            <strong>Monthly Installment:</strong> <span data-monthly-installment="{{ $fleet->monthly_installment }}">KSh {{ number_format($fleet->monthly_installment, 2) }}</span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="paymentAmount" class="form-label">Payment Amount (KSh) *</label>
                                <input type="number" class="form-control" name="payment_amount" id="paymentAmount" 
                                       step="0.01" required min="1" max="{{ $fleet->outstanding_balance }}">
                            </div>
                            <div class="col-md-6">
                                <label for="paymentDate" class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" name="payment_date" id="paymentDate" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="paymentMethod" class="form-label">Payment Method *</label>
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

                        <div class="mb-3">
                            <label for="paymentNotes" class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="paymentNotes" rows="3"></textarea>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function recordScheduledPayment(paymentNumber, amount, dueDate) {
            // Pre-fill the payment modal with schedule data
            document.getElementById('schedulePaymentNumber').value = paymentNumber;
            document.getElementById('paymentAmount').value = amount;
            document.getElementById('paymentDate').value = dueDate;
            
            // Show the payment modal
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();
        }
    </script>

</div>

<script>
// Fleet Management JavaScript - Complete Version
$(document).ready(function() {
    // Get fleet ID with multiple fallback methods
    let fleetId = null;
    
    // Method 1: From global fleetData object
    if (typeof window.fleetData !== 'undefined' && window.fleetData.id) {
        fleetId = window.fleetData.id;
        console.log('Fleet ID from fleetData:', fleetId);
    }
    
    // Method 2: From hidden input
    if (!fleetId) {
        const hiddenInput = $('input[name="fleet_acquisition_id"]');
        if (hiddenInput.length && hiddenInput.val()) {
            fleetId = parseInt(hiddenInput.val());
            console.log('Fleet ID from hidden input:', fleetId);
        }
    }
    
    // Method 3: From data attributes
    if (!fleetId) {
        const containerElement = $('.container-fluid[data-fleet-id]');
        if (containerElement.length) {
            fleetId = parseInt(containerElement.data('fleet-id'));
            console.log('Fleet ID from container data:', fleetId);
        }
    }
    
    // Method 4: From meta tag
    if (!fleetId) {
        const metaFleetId = $('meta[name="fleet-id"]').attr('content');
        if (metaFleetId && metaFleetId !== '') {
            fleetId = parseInt(metaFleetId);
            console.log('Fleet ID from meta tag:', fleetId);
        }
    }
    
    // Method 5: From URL path
    if (!fleetId) {
        const pathParts = window.location.pathname.split('/');
        const manageIndex = pathParts.findIndex(part => part === 'manage');
        if (manageIndex > 0 && pathParts[manageIndex - 1]) {
            const urlId = parseInt(pathParts[manageIndex - 1]);
            if (!isNaN(urlId)) {
                fleetId = urlId;
                console.log('Fleet ID from URL:', fleetId);
            }
        }
    }
    
    // Final validation
    if (!fleetId || isNaN(fleetId) || fleetId <= 0) {
        console.error('Fleet ID validation failed. Current value:', fleetId);
        Swal.fire({
            icon: 'error',
            title: 'System Error!',
            text: 'Fleet ID could not be determined. Please refresh the page or contact support.',
            confirmButtonText: 'Refresh Page',
            allowOutsideClick: false
        }).then(() => {
            window.location.reload();
        });
        return;
    }
    
    console.log('Final Fleet ID confirmed:', fleetId);
    
    // Store globally for other functions
    window.currentFleetId = fleetId;
    
    // Initialize DataTable for payments
    if ($('#paymentsTable').length) {
        try {
            $('#paymentsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [-1] }
                ],
                language: {
                    emptyTable: "No payments recorded yet",
                    info: "Showing _START_ to _END_ of _TOTAL_ payments",
                    infoEmpty: "Showing 0 to 0 of 0 payments",
                    lengthMenu: "Show _MENU_ payments per page"
                }
            });
            console.log('DataTable initialized successfully');
        } catch (error) {
            console.error('DataTable initialization error:', error);
        }
    }

    // REPLACE THE AJAX CALL IN YOUR PAYMENT FORM HANDLER (around line 618)

// Record Payment Form Handler
$('#recordPaymentForm').on('submit', function(e) {
    e.preventDefault();
    console.log('Payment form submitted');
    
    // IMPROVED FLEET ID DETECTION
    let currentFleetId = null;
    
    // Method 1: Try from form data attribute
    currentFleetId = $(this).data('fleet-id');
    
    // Method 2: Try from hidden input in the same form
    if (!currentFleetId) {
        currentFleetId = $(this).find('input[name="fleet_acquisition_id"]').val();
    }
    
    // Method 3: Try from global variable
    if (!currentFleetId) {
        currentFleetId = window.currentFleetId;
    }
    
    // Method 4: Try from modal data attribute
    if (!currentFleetId) {
        currentFleetId = $('#paymentModal').data('fleet-id');
    }
    
    // Method 5: Try from container data attribute
    if (!currentFleetId) {
        currentFleetId = $('.container-fluid[data-fleet-id]').data('fleet-id');
    }
    
    // Method 6: Try from global fleetData
    if (!currentFleetId && window.fleetData && window.fleetData.id) {
        currentFleetId = window.fleetData.id;
    }
    
    // Convert to integer and validate
    currentFleetId = parseInt(currentFleetId);
    
    console.log('Detected Fleet ID:', currentFleetId);
    
    // STRICT VALIDATION
    if (!currentFleetId || isNaN(currentFleetId) || currentFleetId <= 0) {
        console.error('Fleet ID validation failed. Value:', currentFleetId);
        Swal.fire({
            icon: 'error',
            title: 'System Error!',
            text: 'Fleet ID is missing or invalid. Cannot record payment.',
            footer: 'Please refresh the page and try again.'
        });
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Recording...').prop('disabled', true);
    
    // ... (keep all your existing validation code) ...
    
    // Prepare form data
    const formData = new FormData(this);
    formData.set('fleet_acquisition_id', currentFleetId);
    
    console.log('Final Fleet ID for AJAX:', currentFleetId);
    console.log('Payment URL will be:', `/fleetacquisition/${currentFleetId}/payments`);
    
    // FIXED AJAX REQUEST
    $.ajax({
        url: `/fleetacquisition/${currentFleetId}/payments`,  // Now currentFleetId is guaranteed to be valid
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Payment success response:', response);
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Recorded Successfully!',
                    timer: 5000,
                    showConfirmButton: true,
                    confirmButtonText: 'Continue'
                }).then(() => {
                    $('#paymentModal').modal('hide');
                    setTimeout(() => {
                        window.location.reload(); // Reload to show updated data
                    }, 500);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Failed!',
                    text: response.message || 'Payment could not be recorded. Please try again.',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Payment AJAX error:', {xhr, status, error});
            
            let errorMessage = 'Unable to record payment. Please try again.';
            let errorDetails = '';
            
            try {
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'Please fix the following errors:';
                        errorDetails = '<ul class="text-start mt-2">';
                        for (let field in errors) {
                            if (errors[field] && Array.isArray(errors[field])) {
                                errors[field].forEach(err => {
                                    errorDetails += `<li>${err}</li>`;
                                });
                            }
                        }
                        errorDetails += '</ul>';
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                }
            } catch (parseError) {
                console.error('Error parsing response:', parseError);
                errorMessage = 'An unexpected error occurred. Please try again.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Payment Error!',
                html: errorMessage + errorDetails,
                confirmButtonText: 'OK'
            });
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// ALSO ADD THIS DEBUG FUNCTION TO CHECK FLEET ID ON PAGE LOAD
$(document).ready(function() {
    // Debug Fleet ID detection
    console.log('=== FLEET ID DEBUG ===');
    console.log('URL:', window.location.href);
    console.log('Form fleet-id data:', $('#recordPaymentForm').data('fleet-id'));
    console.log('Hidden input value:', $('input[name="fleet_acquisition_id"]').val());
    console.log('Modal fleet-id data:', $('#paymentModal').data('fleet-id'));
    console.log('Container fleet-id data:', $('.container-fluid[data-fleet-id]').data('fleet-id'));
    console.log('Global fleetData:', window.fleetData);
    console.log('======================');
    
    // Rest of your existing code...
});

    // Auto-fill monthly installment amount on focus
    $('#paymentAmount').on('focus', function() {
        if (!$(this).val() && window.fleetData && window.fleetData.monthlyInstallment) {
            $(this).val(window.fleetData.monthlyInstallment.toFixed(2));
        }
    });

    // Payment amount validation
    $('#paymentAmount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        const outstanding = window.fleetData ? window.fleetData.outstandingBalance : 0;
        const monthlyInstallment = window.fleetData ? window.fleetData.monthlyInstallment : 0;
        
        // Remove existing validation
        $(this).removeClass('is-invalid is-valid');
        $(this).siblings('.invalid-feedback, .valid-feedback').remove();
        $('.quick-amounts').remove();
        
        if (amount <= 0) {
            return; // Don't show validation for empty or zero amounts
        }
        
        if (outstanding > 0 && amount > outstanding) {
            $(this).addClass('is-invalid');
            $(this).after(`<div class="invalid-feedback">Amount cannot exceed outstanding balance of KSh ${outstanding.toLocaleString()}</div>`);
        } else {
            $(this).addClass('is-valid');
            $(this).after(`<div class="valid-feedback">Valid payment amount</div>`);
            
            // Show quick amount buttons
            if (outstanding > 0) {
                const quickAmountsHtml = `
                    <div class="quick-amounts mt-2">
                        <small class="text-muted d-block mb-1">Quick amounts:</small>
                        ${monthlyInstallment > 0 ? `
                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="setPaymentAmount(${monthlyInstallment})">
                            <i class="fas fa-calendar-alt"></i> Monthly (${monthlyInstallment.toLocaleString()})
                        </button>
                        ` : ''}
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="setPaymentAmount(${outstanding})">
                            <i class="fas fa-check-circle"></i> Full Balance (${outstanding.toLocaleString()})
                        </button>
                    </div>
                `;
                $(this).closest('.col-md-6').append(quickAmountsHtml);
            }
        }
    });

    // Form reset on modal close
    $('#paymentModal').on('hidden.bs.modal', function() {
        const form = $('#recordPaymentForm')[0];
        if (form) {
            form.reset();
        }
        $('#recordPaymentForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
        $('#recordPaymentForm').find('.invalid-feedback, .valid-feedback').remove();
        $('.quick-amounts').remove();
        console.log('Payment modal reset');
    });

    // Tab activation handler
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('data-bs-target');
        
        if (target === '#payments' && $.fn.DataTable.isDataTable('#paymentsTable')) {
            $('#paymentsTable').DataTable().columns.adjust().responsive.recalc();
        }
    });

    console.log('Fleet Management JavaScript initialized successfully');
});

// Approve Fleet Function
function approveFleet(id) {
    const fleetId = id || window.currentFleetId;
    
    if (!fleetId || isNaN(fleetId)) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Fleet ID not found. Cannot approve fleet.'
        });
        return;
    }
    
    Swal.fire({
        title: 'Approve Fleet Acquisition?',
        text: 'Are you sure you want to approve this fleet acquisition? This action will change the status to approved.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Yes, Approve!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Approving fleet acquisition',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            $.ajax({
                url: `/fleetacquisition/${fleetId}/approve`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Approval response:', response);
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Fleet Approved!',
                            text: response.message || 'Fleet acquisition has been approved successfully.',
                            timer: 3000,
                            showConfirmButton: true,
                            confirmButtonText: 'Continue'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Approval Failed!',
                            text: response.message || 'Unable to approve fleet acquisition. Please try again.'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Approval error:', xhr);
                    
                    let errorMessage = 'Unable to approve fleet acquisition.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Approval endpoint not found. Please check route configuration.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to approve this fleet.';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Approval Error!',
                        text: errorMessage
                    });
                }
            });
        }
    });
}

// Confirm Payment Function
function confirmPayment(paymentId) {
    if (!paymentId || isNaN(paymentId)) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Invalid payment ID.'
        });
        return;
    }
    
    Swal.fire({
        title: 'Confirm Payment?',
        text: 'Are you sure you want to confirm this payment? This action cannot be undone.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Yes, Confirm!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            $.ajax({
                url: `/fleet-payments/${paymentId}/confirm`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Confirmed!',
                            text: response.message || 'Payment has been confirmed successfully.',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Unable to confirm payment.'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Confirmation error:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Unable to confirm payment.'
                    });
                }
            });
        }
    });
}

// View Payment Details Function
function viewPayment(paymentId) {
    if (!paymentId || isNaN(paymentId)) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Invalid payment ID.'
        });
        return;
    }
    
    Swal.fire({
        title: 'Loading Payment Details...',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/fleet-payments/${paymentId}`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const payment = response.data;
                const paymentDate = payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-GB') : 'N/A';
                
                Swal.fire({
                    title: `Payment Details #${payment.payment_number || paymentId}`,
                    html: `
                        <div class="text-start">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr><td><strong>Amount:</strong></td><td class="text-end">KSh ${parseFloat(payment.payment_amount || 0).toLocaleString()}</td></tr>
                                    <tr><td><strong>Date:</strong></td><td class="text-end">${paymentDate}</td></tr>
                                    <tr><td><strong>Method:</strong></td><td class="text-end">${(payment.payment_method || 'N/A').replace('_', ' ').toUpperCase()}</td></tr>
                                    <tr><td><strong>Reference:</strong></td><td class="text-end">${payment.reference_number || 'N/A'}</td></tr>
                                    <tr><td><strong>Balance Before:</strong></td><td class="text-end">KSh ${parseFloat(payment.balance_before || 0).toLocaleString()}</td></tr>
                                    <tr><td><strong>Balance After:</strong></td><td class="text-end">KSh ${parseFloat(payment.balance_after || 0).toLocaleString()}</td></tr>
                                    <tr><td><strong>Status:</strong></td><td class="text-end"><span class="badge ${payment.status === 'confirmed' ? 'bg-success' : payment.status === 'pending' ? 'bg-warning' : 'bg-danger'}">${(payment.status || 'pending').toUpperCase()}</span></td></tr>
                                    ${payment.notes ? `<tr><td><strong>Notes:</strong></td><td class="text-end">${payment.notes}</td></tr>` : ''}
                                </table>
                            </div>
                        </div>
                    `,
                    confirmButtonText: '<i class="fas fa-times"></i> Close',
                    width: '600px'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Unable to load payment details.'
                });
            }
        },
        error: function(xhr) {
            console.error('View payment error:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: xhr.responseJSON?.message || 'Unable to load payment details.'
            });
        }
    });
}

// Set Payment Amount Helper Function
function setPaymentAmount(amount) {
    if (amount && !isNaN(amount)) {
        $('#paymentAmount').val(parseFloat(amount).toFixed(2)).trigger('input');
        $('.quick-amounts').remove();
    }
}

// Delete Payment Function
function deletePayment(paymentId) {
    if (!paymentId || isNaN(paymentId)) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Invalid payment ID.'
        });
        return;
    }
    
    Swal.fire({
        title: 'Delete Payment?',
        text: 'Are you sure you want to delete this payment? This action cannot be undone and will affect the payment history.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('_method', 'DELETE');
            
            $.ajax({
                url: `/fleet-payments/${paymentId}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Deleted!',
                            text: response.message || 'Payment has been deleted successfully.',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Unable to delete payment.'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Delete payment error:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Unable to delete payment.'
                    });
                }
            });
        }
    });
}

// Print Payment Receipt Function
function printPaymentReceipt(paymentId) {
    if (!paymentId || isNaN(paymentId)) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Invalid payment ID.'
        });
        return;
    }
    
    window.open(`/fleet-payments/${paymentId}/receipt`, '_blank');
}

// Export Payments Function
function exportPayments(fleetId) {
    const currentFleetId = fleetId || window.currentFleetId;
    if (!currentFleetId || isNaN(currentFleetId)) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Fleet ID not found. Cannot export payments.'
        });
        return;
    }
}

// Global AJAX setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Global error handler for AJAX requests
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    if (xhr.status === 419) {
        Swal.fire({
            icon: 'warning',
            title: 'Session Expired',
            text: 'Your session has expired. Please refresh the page and try again.',
            confirmButtonText: '<i class="fas fa-refresh"></i> Refresh Page',
            allowOutsideClick: false
        }).then(() => {
            window.location.reload();
        });
    }
});

// Initialize tooltips if Bootstrap is available
$(function () {
    if (typeof bootstrap !== 'undefined' && $('[data-bs-toggle="tooltip"]').length) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
});

// Page load complete
$(window).on('load', function() {
    console.log('Fleet Management page fully loaded');
    
    // Additional initialization if needed
    if (window.fleetData) {
        console.log('Fleet data available:', window.fleetData);
    }
});
</script>
<script>
// Auto-load documents on page ready
$(document).ready(function() {
    // Load documents if tab is already active on page load
    if ($('#fleet-documents').hasClass('active show')) {
        loadFleetDocuments();
    }
    
    // Load documents when tab is shown
    $('#fleet-documents-tab').on('shown.bs.tab', function() {
        loadFleetDocuments();
    });
});

function loadFleetDocuments() {
    const fleetId = {{ $fleet->id }};
    $('#documentsContainer').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Loading documents...</p></div>');
    
    $.ajax({
        url: `/fleetacquisition/${fleetId}/documents`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.documents && response.documents.length > 0) {
                let html = '<div class="row g-3">';
                response.documents.forEach(doc => {
                    html += `
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-${doc.type.toLowerCase() === 'pdf' ? 'pdf' : 'alt'} fa-3x text-danger mb-3"></i>
                                    <h6 class="text-truncate" title="${doc.name}">${doc.name}</h6>
                                    <small class="badge bg-secondary">${doc.type}</small>
                                    <div class="mt-3">
                                        <a href="#" onclick="viewDocument('${doc.url}', '${doc.name}'); return false;" class="btn btn-sm btn-primary me-1">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button onclick="deleteFleetDoc(${doc.index})" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                html += '</div>';
                $('#documentsContainer').html(html);
            } else {
                $('#documentsContainer').html('<div class="text-center py-5 text-muted"><i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i><p>No documents uploaded yet</p><small>Click "Upload Documents" to add files</small></div>');
            }
        },
        error: function(xhr) {
            console.error('Error loading documents:', xhr);
            $('#documentsContainer').html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Failed to load documents</p><button class="btn btn-sm btn-outline-primary" onclick="loadFleetDocuments()"><i class="fas fa-refresh"></i> Retry</button></div>');
        }
    });
}
function viewDocument(url, name) {
    $('#docTitle').text(name);
    $('#docFrame').attr('src', url);
    $('#docViewerModal').modal('show');
}
function openUploadModal() {
    $('#uploadDocsModal').modal('show');
}

function uploadDocuments() {
    const fleetId = {{ $fleet->id }};
    const formData = new FormData($('#uploadDocsForm')[0]);
    const uploadBtn = $('#uploadDocsModal .modal-footer button.btn-primary');
    const originalText = uploadBtn.html();
    
    $.ajax({
        url: `/fleetacquisition/${fleetId}/documents/upload`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    uploadBtn.html(`<i class="fas fa-spinner fa-spin"></i> ${percent}%`);
                }
            });
            return xhr;
        },
        beforeSend: function() {
            uploadBtn.html('<i class="fas fa-spinner fa-spin"></i> Uploading...').prop('disabled', true);
        },
        success: function(response) {
            $('#uploadDocsModal').modal('hide');
            Swal.fire('Success!', response.message, 'success');
            loadFleetDocuments();
            $('#uploadDocsForm')[0].reset();
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON?.message || 'Upload failed', 'error');
        },
        complete: function() {
            uploadBtn.html(originalText).prop('disabled', false);
        }
    });
}

function deleteFleetDoc(index) {
    Swal.fire({
        title: 'Delete Document?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/fleetacquisition/{{ $fleet->id }}/documents/${index}`,
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    Swal.fire('Deleted!', response.message, 'success');
                    loadFleetDocuments();
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Delete failed', 'error');
                }
            });
        }
    });
}
</script>
</x-app-layout>