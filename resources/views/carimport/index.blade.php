<x-app-layout>
<div class="container-fluid">
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
<div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Bid Cars</h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                                                Create Bid
                                            </button>
                                            <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">Add Bid Details</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                       <!-- Tooltips -->
                                                    <div class="col-xl-12">
                                                        <div class="card">
                                                            <div class="card-body">
                                                            
                                                            <form id="carForm" class="row g-3" enctype="multipart/form-data">
                                                            @csrf
                                                        <!-- Add progress indicator here -->
                                                            <div id="createProgress" class="col-12" style="display: none;">
                                                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                    <div>Uploading car details and photos... Please wait.</div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Car Exporter</label>
                                                                <input type="text" class="form-control" id="bidder_name" name="bidder_name" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Make </label>
                                                                <input type="text" class="form-control" id="make" name="make" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">Model </label>
                                                                <input type="text" class="form-control" id="model" name="model" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">Year of Manufacture</label>
                                                                <input type="number" class="form-control" id="year" name="year" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">VIN (Vehicle Identification Number) </label>
                                                                <input type="text" class="form-control" id="vin" name="vin" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">Engine Type</label>
                                                                <input type="text" class="form-control" id="engine_type" name="engine_type" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Body Type </label>
                                                                <input type="text" class="form-control" id="body_type" name="body_type" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Mileage </label>
                                                                <input type="number" class="form-control" id="mileage" name="mileage" required>
                                                            </div>
                                                            
                                                            <div class="col-md-6">
                                                                <label class="form-label">Bid Amount (USD)</label>
                                                                <input type="number" class="form-control" id="bid_amount" name="bid_amount" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Bid Start Date</label>
                                                                <input type="date" class="form-control" id="bid_start_date" name="bid_start_date" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Bid End Date</label>
                                                                <input type="date" class="form-control" id="bid_end_date" name="bid_end_date" required>
                                                            </div>
                                                            <!-- <div class="col-md-6">
                                                                <label class="form-label">Deposit</label>
                                                                <input type="number" class="form-control" id="deposit" name="deposit" required>
                                                            </div>-->
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
                                                                <label class="form-label">Photos</label>
                                                                <input type="file" class="form-control" id="photos" name="photos[]" multiple required>
                                                            </div>

                                                             <div class="col-12">
                                                                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                                                                        <span class="btn-text">Submit</span>
                                                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                                                    </button>
                                                                </div>
                                                        </form>
                                                            </div> <!-- end card-body -->
                                                        </div> <!-- end card-->
                                                    </div> <!-- end col -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">

                                    <div class="card-body">
                                        
 <div class="table-responsive">  
    <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
        <thead>
            <tr>
                <th>Car's Photos</th>
                <th>Car Exporter</th>
                <th>Car Name / Model</th>
                <th>VIN (Vehicle ID)</th>
                <th>Bid Amount</th>
                <th>Deposit Amount</th>
                <th>Full Amount</th>
                <th>Bid Start Date</th>
                <th>Bid End Date</th><th>Mileage</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($carBids as $bid)
                <tr>
                    <td>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal{{$bid->id}}">
                            View
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="modal{{$bid->id}}" tabindex="-1" aria-labelledby="modalLabel{{$bid->id}}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="modalLabel{{$bid->id}}">{{ $bid->year }} {{ $bid->make }} {{ $bid->model }}</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                   
                                       @php
                                            // Decode the JSON string into an array of image paths
                                            $imagePaths = json_decode($bid->photos);
                                        @endphp

                                        @if ($imagePaths && is_array($imagePaths))
                                            @foreach ($imagePaths as $imagePath)
                                                @php
                                                    // Check if the image path is already a full S3 URL or just the S3 key
                                                    if (str_starts_with($imagePath, 'https://')) {
                                                        // Already a full URL
                                                        $imageUrl = $imagePath;
                                                    } else {
                                                        // Generate S3 URL from the key path
                                                        $bucket = config('filesystems.disks.s3.bucket');
                                                        $region = config('filesystems.disks.s3.region');
                                                        $imageUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$imagePath}";
                                                    }
                                                @endphp
                                                <div class="col-md-12 col-lg-6 mb-3">
                                                    <img src="{{ $imageUrl }}" 
                                                        alt="Car Image" 
                                                        width="100%" 
                                                        class="img-hover-zoom"
                                                        loading="lazy"
                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                    <div style="display: none; text-align: center; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6;">
                                                        <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                                        <p class="text-muted mb-0">Image failed to load</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="col-12">
                                                <div class="text-center py-4">
                                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">No images available for this car.</p>
                                                </div>
                                            </div>
                                        @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $bid->bidder_name }}</td>
                    <td>{{ $bid->year }} {{ $bid->make }} {{ $bid->model }}</td>
                    <td>{{ $bid->vin }}</td>
                    <td>{{ number_format($bid->bid_amount, 2) }}</td>
                     <td>{{ number_format($bid->deposit, 2) }}
                    </td>
                     <td>{{ number_format($bid->fullamount, 2) }}</td>
                    <td>{{ $bid->bid_start_date }}</td>
                    <td>{{ $bid->bid_end_date }}</td>
                    <td>{{ number_format($bid->mileage) }} Km</td>
                    <td>{{ $bid->created_at->format('Y-m-d') }}</td>
                    <td>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal{{$bid->id}}">
                                              Edit
                                            </button>
                                            <div class="modal fade" id="standard-modal{{$bid->id}}" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">  Edit Bid Info</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                      <!-- Tooltips -->
                                                    <div class="col-xl-12">
                                                        <div class="card">
                                                            <div class="card-body">
                                                            <form  class="row g-3" id="editcarForm" enctype="multipart/form-data">

                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $bid->id }}">
                                                            <!-- Add progress indicator here -->
                                                            <div id="editProgress{{$bid->id}}" class="col-12" style="display: none;">
                                                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                    <div>Updating bid information... Please wait.</div>
                                                                </div>
                                                            </div>
                                                             <!--complete this form follow above form for creation -->
                                                             <div class="col-md-6">
                                                                <label class="form-label">Car Exporter</label>
                                                                <input type="text" class="form-control" id="editbidder_name" value="{{ $bid->bidder_name }}" name="editbidder_name" required>
                                                            </div>


                                                            <div class="col-md-6">
                                                                <label class="form-label">Make </label>
                                                                <input type="text" class="form-control" id="editmake" value="{{ $bid->make }}" name="editmake" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">Model </label>
                                                                <input type="text" class="form-control" id="editmodel" value="{{ $bid->model }}" name="editmodel" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">Year of Manufacture</label>
                                                                <input type="number" class="form-control" id="edityear" value="{{ $bid->year }}" name="edityear" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">VIN (Vehicle Identification Number) </label>
                                                                <input type="text" class="form-control" id="editvin" value="{{ $bid->vin }}" name="editvin" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">Engine Type</label>
                                                                <input type="text" class="form-control" id="editengine_type"  value="{{ $bid-> engine_type}}"  name="editengine_type" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Body Type </label>
                                                                <input type="text" class="form-control" id="editbody_type" value="{{ $bid->body_type }}" name="editbody_type" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Mileage </label>
                                                                <input type="number" class="form-control" id="editmileage" value="{{ $bid-> mileage}}" name="editmileage" required>
                                                            </div>
                                                            
                                                            <div class="col-md-6">
                                                                <label class="form-label">Bid Amount</label>
                                                                <input type="number" class="form-control" id="editbid_amount" value="{{ $bid-> bid_amount}}" name="editbid_amount" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Bid Start Date</label>
                                                                <input type="date" class="form-control" id="editbid_start_date" value="{{ \Carbon\Carbon::parse($bid->bid_start_date)->format('Y-m-d') }}" name="editbid_start_date" required>

                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Bid End Date</label>
                                                                <input type="date" class="form-control" id="editbid_end_date" value="{{ \Carbon\Carbon::parse($bid->bid_end_date)->format('Y-m-d') }}" name="editbid_end_date" required>

                                                            </div>
                                                             <!-- <div class="col-md-6">
                                                                <label class="form-label">Deposit</label>
                                                                <input type="number" class="form-control" id="editdeposit" name="editdeposit" value="{{ $bid->deposit }}" required>
                                                            </div>-->
                                                              <div class="col-md-6">
                                                                    <label class="form-label">Engine Number</label>
                                                                    <input type="text" class="form-control" id="editengine_no" name="editengine_no" value="{{ $bid-> engine_no}}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Engine Capacity</label>
                                                                    <input type="text" class="form-control" id="editengine_capacity" name="editengine_capacity" value="{{ $bid-> engine_capacity}}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Transmission</label>
                                                                    <input type="text" class="form-control" id="edittransmission" name="edittransmission" value="{{ $bid-> transmission}}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Colour</label>
                                                                    <input type="text" class="form-control" id="editcolour" name="editcolour" value="{{ $bid-> colour}}" required>
                                                                </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Photos</label>
                                                                <input type="file" class="form-control" id="editphotos" name="editphotos[]" multiple>
                                                            </div>
                                                            <div class="col-12">
                                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
                                                        </form>
                                                            </div> <!-- end card-body -->
                                                        </div> <!-- end card-->
                                                    </div> <!-- end col -->

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <br>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#standard-modal1{{$bid->id}}">
                                             Confirm Partial Payment
                                            </button>
                                            <div class="modal fade" id="standard-modal1{{$bid->id}}" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">Partial Payment</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                      <!-- Tooltips -->
                                                    <div class="col-xl-12">
                                                        <div class="card">
                                                            <div class="card-body">
                                                            <form  class="row g-3" id="updatestatus">

                                                            @csrf
                                                            <input type="hidden" name="id" value="{{$bid->id }}">
                                                            <!-- Add progress indicator here -->
                                                            <div id="paymentProgress{{$bid->id}}" class="col-12" style="display: none;">
                                                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                    <div>Processing payment... Please wait.</div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label">Deposit Amount (USD)</label>
                                                                <input type="number" class="form-control" id="depositamount"
                                                                    value="{{ number_format($bid->deposit, 2, '.', '') }}"
                                                                    name="depositamount" required>
                                                            </div>
                                 
                                                            <div class="col-12">
                                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
                                                        </form>
                                                            </div> <!-- end card-body -->
                                                        </div> <!-- end card-->
                                                    </div> <!-- end col -->

                                                    </div>
                                                </div>
                                            </div>
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
                    <script>
// Create Bid Form
document.getElementById('carForm').addEventListener('submit', function(e) {
    document.getElementById('createProgress').style.display = 'block';
    document.getElementById('createSubmitBtn').disabled = true;
    document.querySelector('#createSubmitBtn .btn-text').textContent = 'Uploading...';
});

// Edit Bid Forms (multiple instances)
document.querySelectorAll('[id^="editcarForm"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const bidId = this.querySelector('input[name="id"]').value;
        const progressDiv = document.getElementById('editProgress' + bidId);
        if (progressDiv) {
            progressDiv.style.display = 'block';
        }
        this.querySelector('button[type="submit"]').disabled = true;
    });
});

// Partial Payment Forms (multiple instances)
document.querySelectorAll('[id="updatestatus"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const bidId = this.querySelector('input[name="id"]').value;
        const progressDiv = document.getElementById('paymentProgress' + bidId);
        if (progressDiv) {
            progressDiv.style.display = 'block';
        }
        this.querySelector('button[type="submit"]').disabled = true;
    });
});
</script>
                    <!-- container-fluid -->
</x-app-layout>
