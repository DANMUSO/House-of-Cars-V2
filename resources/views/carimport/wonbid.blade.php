<x-app-layout>
<div class="container-fluid">
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
<div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Won Bid</h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
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
                    <td>{{ number_format($bid->bid_amount *0.3, 2) }}
                          <br>
                        {{ number_format($bid->deposit, 2) }}
                    </td>
                     <td>{{ number_format($bid->fullamount, 2) }}</td>
                    <td>{{ $bid->bid_start_date }}</td>
                    <td>{{ $bid->bid_end_date }}</td>
                    <td>{{ number_format($bid->mileage) }} km</td>
                    
                    <td>{{ $bid->updated_at->format('Y-m-d') }}</td>
                    <td>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#standard-modal1{{$bid->id}}">
                                            Confirm Full Payment
                                            </button>
                                            <div class="modal fade" id="standard-modal1{{$bid->id}}" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">Confirm Full Payment</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                      <!-- Tooltips -->
                                                    <div class="col-xl-12">
                                                        <div class="card">
                                                            <div class="card-body">
                                                            <form  class="row g-3" id="fullpayment">

                                                            @csrf
                                                            <input type="hidden" name="id" value="{{$bid->id }}">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Full Amount (USD)</label>
                                                                <input type="number" class="form-control" id="fullamount" name="fullamount" required>
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

                    </div> <!-- container-fluid -->
</x-app-layout>
<script>

        </script>