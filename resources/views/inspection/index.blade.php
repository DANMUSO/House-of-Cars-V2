<x-app-layout>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Inspect Imported Cars</h4>
            <p class="text-muted mb-0">Vehicle condition assessment and inspection reports</p>
        </div>
        <div class="flex-grow-1 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                <i class="fas fa-plus me-1"></i> Add Vehicle Condition and Accessories Checklist
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
                                    <i class="fas fa-clipboard-check"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $inspections->count() }}</h5>
                            <p class="text-muted mb-0 small">Total Inspections</p>
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
                            <h5 class="mb-1">{{ $inspections->where('overall_percent', '>=', 80)->count() }}</h5>
                            <p class="text-muted mb-0 small">Excellent (80%+)</p>
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
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $inspections->where('overall_percent', '<', 60)->count() }}</h5>
                            <p class="text-muted mb-0 small">Needs Repair</p>
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
                            <h5 class="mb-1">{{ $inspections->pluck('imported_id')->unique()->count() }}</h5>
                            <p class="text-muted mb-0 small">Unique Vehicles</p>
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
                            <h5 class="mb-1">{{ $inspections->where('created_at', '>=', now()->startOfMonth())->count() }}</h5>
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
                                    <i class="fas fa-tachometer-alt"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $inspections->count() > 0 ? round($inspections->avg('overall_percent'), 1) : 0 }}%</h5>
                            <p class="text-muted mb-0 small">Avg Score</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <h4 class="text-success mb-1">{{ $inspections->count() > 0 ? round($inspections->avg('overall_percent'), 1) : 0 }}%</h4>
                            <p class="text-muted mb-0">Overall Condition</p>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-primary mb-1">{{ $inspections->count() > 0 ? round($inspections->avg('exterior_percent'), 1) : 0 }}%</h4>
                            <p class="text-muted mb-0">Exterior Condition</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info mb-1">{{ $inspections->count() > 0 ? round($inspections->avg('interior_func_percent'), 1) : 0 }}%</h4>
                            <p class="text-muted mb-0">Interior Functional</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning mb-1">{{ $inspections->count() > 0 ? round($inspections->avg('interior_acc_percent'), 1) : 0 }}%</h4>
                            <p class="text-muted mb-0">Interior Accessories</p>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-secondary mb-1">{{ $inspections->count() > 0 ? round($inspections->avg('tools_percent'), 1) : 0 }}%</h4>
                            <p class="text-muted mb-0">Tools & Accessories</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

                                            <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">  Add Vehicle Condition and Accessories Checklist</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                    <form id="vehicleInspectionForm">
                                                    <h4>Overall Condition: <span id="overallPercent">0%</span></h4>
                                                    <input type="hidden" name="overall_percent" id="overall_percent_input">

                                                        <!-- Exterior Condition -->
                                                        <h6>Exterior Condition: <span id="exteriorPercent">0%</span></h6>
                                                        <input type="hidden" name="status" id="status" value="1">
                                                        <input type="hidden" name="exterior_percent" id="exterior_percent_input">
                                                        <table border="1">
                                                        <tr><td>Car's Details</td>
                                                        <td colspan="2"> 
                                                        <select class="form-select" id="imported_id" name="imported_id" required>
                                                            <option disabled selected value="">Choose</option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->id }}">
                                                                    {{ $customer->model }} - {{ $customer->vin }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        </td></tr>
                                                            <tr><th>Part</th><th>OK</th><th>Damaged</th></tr>
                                                            <tr><td>R/H Front Wing</td><td><input type="radio" name="rh_front_wing" value="ok"></td><td><input type="radio" name="rh_front_wing" value="damaged"></td></tr>
                                                            <tr><td>R/H Right Wing</td><td><input type="radio" name="rh_right_wing" value="ok"></td><td><input type="radio" name="rh_right_wing" value="damaged"></td></tr>
                                                            <tr><td>L/H Front Wing</td><td><input type="radio" name="lh_front_wing" value="ok"></td><td><input type="radio" name="lh_front_wing" value="damaged"></td></tr>
                                                            <tr><td>L/H Right Wing</td><td><input type="radio" name="lh_right_wing" value="ok"></td><td><input type="radio" name="lh_right_wing" value="damaged"></td></tr>
                                                            <tr><td>Bonnet</td><td><input type="radio" name="bonnet" value="ok"></td><td><input type="radio" name="bonnet" value="damaged"></td></tr>
                                                            <tr><td>R/H Front Door</td><td><input type="radio" name="rh_front_door" value="ok"></td><td><input type="radio" name="rh_front_door" value="damaged"></td></tr>
                                                            <tr><td>R/H Rear Door</td><td><input type="radio" name="rh_rear_door" value="ok"></td><td><input type="radio" name="rh_rear_door" value="damaged"></td></tr>
                                                            <tr><td>L/H Front Door</td><td><input type="radio" name="lh_front_door" value="ok"></td><td><input type="radio" name="lh_front_door" value="damaged"></td></tr>
                                                            <tr><td>L/H Rear Door</td><td><input type="radio" name="lh_rear_door" value="ok"></td><td><input type="radio" name="lh_rear_door" value="damaged"></td></tr>
                                                            <tr><td>Front Bumper</td><td><input type="radio" name="front_bumper" value="ok"></td><td><input type="radio" name="front_bumper" value="damaged"></td></tr>
                                                            <tr><td>Rear Bumper</td><td><input type="radio" name="rear_bumper" value="ok"></td><td><input type="radio" name="rear_bumper" value="damaged"></td></tr>
                                                            <tr><td>Head Lights</td><td><input type="radio" name="head_lights" value="ok"></td><td><input type="radio" name="head_lights" value="damaged"></td></tr>
                                                            <tr><td>Bumper Lights</td><td><input type="radio" name="bumper_lights" value="ok"></td><td><input type="radio" name="bumper_lights" value="damaged"></td></tr>
                                                            <tr><td>Corner Lights</td><td><input type="radio" name="corner_lights" value="ok"></td><td><input type="radio" name="corner_lights" value="damaged"></td></tr>
                                                            <tr><td>Rear Lights</td><td><input type="radio" name="rear_lights" value="ok"></td><td><input type="radio" name="rear_lights" value="damaged"></td></tr>
                                                        </table>

                                                        <!-- Interior Features -->
                                                        <div class="section-title">Interior Functional Items: <span id="interiorFuncPercent">0%</span></div>
                                                        <input type="hidden" name="interior_func_percent" id="interior_func_percent_input">
                                                        <table border="1">
                                                            <tr><th>Item</th><th>OK</th><th>Damaged</th></tr>
                                                            <tr><td>Radio Speakers</td><td><input type="radio" name="radio_speakers" value="ok"></td><td><input type="radio" name="radio_speakers" value="damaged"></td></tr>
                                                            <tr><td>Seat Belt</td><td><input type="radio" name="seat_belt" value="ok"></td><td><input type="radio" name="seat_belt" value="damaged"></td></tr>
                                                            <tr><td>Door Handles</td><td><input type="radio" name="door_handles" value="ok"></td><td><input type="radio" name="door_handles" value="damaged"></td></tr>
                                                        </table>

                                                        <div class="section-title">Interior Accessories: <span id="interiorAccPercent">0%</span></div>
                                                        <input type="hidden" name="interior_acc_percent" id="interior_acc_percent_input">
                                                        <table border="1">
                                                            <tr><th>Item</th><th>Present</th><th>Absent</th></tr>
                                                            <tr><td>Head Rest</td><td><input type="radio" name="head_rest" value="present"></td><td><input type="radio" name="head_rest" value="absent"></td></tr>
                                                            <tr><td>Floor Carpets</td><td><input type="radio" name="floor_carpets" value="present"></td><td><input type="radio" name="floor_carpets" value="absent"></td></tr>
                                                            <tr><td>Rubber Mats</td><td><input type="radio" name="rubber_mats" value="present"></td><td><input type="radio" name="rubber_mats" value="absent"></td></tr>
                                                            <tr><td>Cigar Lighter</td><td><input type="radio" name="cigar_lighter" value="present"></td><td><input type="radio" name="cigar_lighter" value="absent"></td></tr>
                                                            <tr><td>Boot Mats</td><td><input type="radio" name="boot_mats" value="present"></td><td><input type="radio" name="boot_mats" value="absent"></td></tr>
                                                            
                                                        </table>

                                                        <!-- Tools and Accessories -->
                                                        <div class="section-title">Tools & Accessories: <span id="toolsPercent">0%</span></div>
                                                        <input type="hidden" name="tools_percent" id="tools_percent_input">
                                                        <table border="1">
                                                            <tr><th>Item</th><th>Present</th><th>Absent</th></tr>
                                                            <tr><td>Jack/Handle</td><td><input type="radio" name="jack" value="present"></td><td><input type="radio" name="jack" value="absent"></td></tr>
                                                            <tr><td>Spare Wheel</td><td><input type="radio" name="spare_wheel" value="present"></td><td><input type="radio" name="spare_wheel" value="absent"></td></tr>
                                                            <tr><td>Compressor Kit</td><td><input type="radio" name="compressor" value="present"></td><td><input type="radio" name="compressor" value="absent"></td></tr>
                                                            <tr><td>Wheel Spanner</td><td><input type="radio" name="wheel_spanner" value="present"></td><td><input type="radio" name="wheel_spanner" value="absent"></td></tr>
                                                            <tr><td>Current Mileage</td>
                                                            <td colspan="2"><input type="text" name="current_mileage" placeholder="Enter current mileage"></td></tr>
                                                            <tr><td>Notes</td>
                                                            <td colspan="2"><textarea name="inspection_notes" > </textarea></td></tr>
                                                        </table>

                                                        <script>
                                                        document.addEventListener('DOMContentLoaded', function () {

                                                            function calculate() {
                                                                const exteriorItems = ['rh_front_wing','rh_right_wing','lh_front_wing','lh_right_wing','bonnet','rh_front_door','rh_rear_door','lh_front_door','lh_rear_door','front_bumper','rear_bumper','head_lights','bumper_lights','corner_lights','rear_lights'];
                                                                const interiorFunctional = ['radio_speakers','seat_belt','door_handles'];
                                                                const interiorAccessories = ['head_rest','floor_carpets','rubber_mats','cigar_lighter','boot_mats'];
                                                                const toolsAccessories = ['jack','spare_wheel','compressor','wheel_spanner'];

                                                                let extOk = exteriorItems.filter(name => document.querySelector(`input[name="${name}"][value="ok"]`)?.checked).length;
                                                                let intFuncOk = interiorFunctional.filter(name => document.querySelector(`input[name="${name}"][value="ok"]`)?.checked).length;
                                                                let intAccOk = interiorAccessories.filter(name => document.querySelector(`input[name="${name}"][value="present"]`)?.checked).length;
                                                                let toolsOk = toolsAccessories.filter(name => document.querySelector(`input[name="${name}"][value="present"]`)?.checked).length;

                                                                let totalOk = extOk + intFuncOk + intAccOk + toolsOk;
                                                                let totalItems = exteriorItems.length + interiorFunctional.length + interiorAccessories.length + toolsAccessories.length;

                                                                let extPercent = Math.round((extOk / exteriorItems.length) * 100);
                                                                let intFuncPercent = Math.round((intFuncOk / interiorFunctional.length) * 100);
                                                                let intAccPercent = Math.round((intAccOk / interiorAccessories.length) * 100);
                                                                let toolsPercent = Math.round((toolsOk / toolsAccessories.length) * 100);
                                                                let overallPercent = Math.round((totalOk / totalItems) * 100);
                                                                document.getElementById('exterior_percent_input').value = extPercent;
                                                                document.getElementById('interior_func_percent_input').value = intFuncPercent;
                                                                document.getElementById('interior_acc_percent_input').value = intAccPercent;
                                                                document.getElementById('tools_percent_input').value = toolsPercent;
                                                                document.getElementById('overall_percent_input').value = overallPercent;
                                                                document.getElementById('exteriorPercent').innerText = extPercent + '%';
                                                                document.getElementById('interiorFuncPercent').innerText = intFuncPercent + '%';
                                                                document.getElementById('interiorAccPercent').innerText = intAccPercent + '%';
                                                                document.getElementById('toolsPercent').innerText = toolsPercent + '%';
                                                                document.getElementById('overallPercent').innerText = overallPercent + '%';
                                                            }

                                                            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                                                                radio.addEventListener('change', calculate);
                                                            });

                                                        });
                                                        </script>

                                                        <button type="button" class="submit-btn" onclick="submitInspection()">Submit Inspection</button>
                                                    </form>

                                                                                
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
                                                                <table id="responsive-datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Current Mileage</th>
                                        <th>RH Front Wing</th>
                                        <th>RH Right Wing</th>
                                        <th>LH Front Wing</th>
                                        <th>LH Right Wing</th>
                                        <th>Bonnet</th>
                                        <th>Front Bumper</th>
                                        <th>Rear Bumper</th>
                                        <th>Head Lights</th>
                                        <th>Interior - Radio</th>
                                        <th>Seat Belt</th>
                                        <th>Head Rest</th>
                                        <th>Floor Carpets</th>
                                        <th>Rubber Mats</th>
                                        <th>Jack</th>
                                        <th>Spare Wheel</th>
                                        <th>Compressor</th>
                                        <th>Wheel Spanner</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inspections as $index => $inspection)
                                        <tr>
                                            <td>
                                                {{ $index + 1 }}
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{$inspection->id}}">
                                                    View
                                                </button>
                                                
                                                                                                    <!-- Updated View Modal with Photo Management -->
                                                    <div class="modal fade" id="viewModal{{$inspection->id}}" tabindex="-1" aria-labelledby="viewModalLabel{{$inspection->id}}" aria-hidden="true">
                                                        <div class="modal-dialog modal-xl"> <!-- Changed to modal-xl for more space -->
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title fs-5" id="viewModalLabel{{$inspection->id}}">
                                                                        Vehicle Inspection Summary -  {{ optional($inspection->carsImport)->make ?? 'N/A' }}  {{ optional($inspection->carsImport)->model ?? 'N/A' }}
                                                                       
                                                                    </h4>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <!-- Existing Inspection Data -->
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <ul class="list-group mb-3">
                                                                                <li class="list-group-item"><strong>Overall Condition:</strong> {{ $inspection->overall_percent }}%</li>
                                                                                <li class="list-group-item"><strong>Exterior Condition:</strong> {{ $inspection->exterior_percent }}%</li>
                                                                                <li class="list-group-item"><strong>Interior Functional Items:</strong> {{ $inspection->interior_func_percent }}%</li>
                                                                                <li class="list-group-item"><strong>Interior Accessories:</strong> {{ $inspection->interior_acc_percent }}%</li>
                                                                                <li class="list-group-item"><strong>Tools & Accessories:</strong> {{ $inspection->tools_percent }}%</li>
                                                                            </ul>
                                                                        </div>
                                                                        
                                                                        <!-- Photo Upload Section -->
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="photoUpload{{$inspection->id}}" class="form-label">
                                                                                    <strong>Upload Photos</strong>
                                                                                </label>
                                                                                <input class="form-control" type="file" id="photoUpload{{$inspection->id}}" 
                                                                                    multiple accept="image/*" data-inspection-id="{{$inspection->id}}">
                                                                                <div class="form-text">Select multiple images (JPEG, PNG, JPG, GIF - Max 2MB each)</div>
                                                                            </div>
                                                                            
                                                                            <button type="button" class="btn btn-primary btn-sm" id="uploadBtn{{$inspection->id}}">
                                                                                <i class="fas fa-upload"></i> Upload Photos
                                                                            </button>
                                                                            
                                                                            <div id="uploadProgress{{$inspection->id}}" class="progress mt-2" style="display: none;">
                                                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                                            </div>
                                                                            
                                                                            <div id="uploadMessage{{$inspection->id}}" class="mt-2"></div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Photo Gallery Section -->
                                                                    <div class="mt-4">
                                                                        <h5>Photos <span id="photoCount{{$inspection->id}}" class="badge bg-secondary">{{ $inspection->photos_count }}</span></h5>
                                                                        <div id="photoGallery{{$inspection->id}}" class="row g-2 mt-2">
                                                                            @if($inspection->photos)
                                                                                @foreach($inspection->getPhotosWithUrls() as $photo)
                                                                                <div class="col-md-3 col-sm-4 col-6 photo-item" data-photo-index="{{$photo['index']}}">
                                                                                    <div class="card">
                                                                                        <img src="{{ $photo['url'] }}" 
                                                                                            class="card-img-top photo-thumbnail" 
                                                                                            alt="{{$photo['name']}}"
                                                                                            style="height: 150px; object-fit: cover; cursor: pointer;"
                                                                                            onclick="showPhotoPreview('{{$inspection->id}}', '{{ $photo['url'] }}', '{{$photo['name']}}')">
                                                                                        <div class="card-body p-2">
                                                                                            <button type="button" class="btn btn-danger btn-sm mt-1 delete-photo-btn" 
                                                                                                    data-photo-index="{{$photo['index']}}"
                                                                                                    data-inspection-id="{{$inspection->id}}">
                                                                                                <i class="fas fa-trash"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                @endforeach
                                                                            @endif
                                                                        </div>
                                                                        
                                                                        <div id="emptyPhotosMessage{{$inspection->id}}" class="text-center text-muted mt-3" 
                                                                            style="{{ $inspection->photos_count > 0 ? 'display: none;' : '' }}">
                                                                            <i class="fas fa-images fa-3x mb-2"></i>
                                                                            <p>No photos uploaded yet. Use the upload button above to add photos.</p>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Add SweetAlert2 CDN before the closing body tag -->
                                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                                                    <div id="photoPreviewSection{{$inspection->id}}" class="mt-4" style="display: none;">
                                                                        <hr>
                                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                                            <h5 class="mb-0">Photo Preview</h5>
                                                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hidePhotoPreview('{{$inspection->id}}')">
                                                                                <i class="fas fa-times"></i> Close Preview
                                                                            </button>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <img id="photoPreviewImage{{$inspection->id}}" src="" class="img-fluid border rounded" 
                                                                                style="max-height: 400px; max-width: 100%;">
                                                                            <div class="mt-2">
                                                                                <small id="photoPreviewName{{$inspection->id}}" class="text-muted"></small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <script>
                                                    // Photo Management JavaScript
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        // Remove existing event listeners first to prevent duplicates
                                                        document.removeEventListener('click', handleUploadClick);
                                                        document.removeEventListener('click', handleDeleteClick);
                                                        
                                                        // Add event listeners
                                                        document.addEventListener('click', handleUploadClick);
                                                        document.addEventListener('click', handleDeleteClick);
                                                    });

                                                    // Separate named functions to prevent duplicate listeners
                                                    function handleUploadClick(e) {
                                                        if (e.target.id.startsWith('uploadBtn')) {
                                                            const inspectionId = e.target.id.replace('uploadBtn', '');
                                                            uploadPhotos(inspectionId);
                                                        }
                                                    }

                                                    function handleDeleteClick(e) {
                                                        if (e.target.classList.contains('delete-photo-btn') || e.target.parentElement.classList.contains('delete-photo-btn')) {
                                                            const btn = e.target.classList.contains('delete-photo-btn') ? e.target : e.target.parentElement;
                                                            const photoIndex = btn.getAttribute('data-photo-index');
                                                            const inspectionId = btn.getAttribute('data-inspection-id');
                                                            deletePhoto(photoIndex, inspectionId);
                                                        }
                                                    }

                                                    // Upload photos function
                                                    function uploadPhotos(inspectionId) {
                                                        const fileInput = document.getElementById('photoUpload' + inspectionId);
                                                        const files = fileInput.files;
                                                        
                                                        if (files.length === 0) {
                                                            Swal.fire(
                                                                'Warning!',
                                                                'Please select at least one photo to upload.',
                                                                'warning'
                                                            );
                                                            return;
                                                        }

                                                        const formData = new FormData();
                                                        for (let i = 0; i < files.length; i++) {
                                                            formData.append('photos[]', files[i]);
                                                        }
                                                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                                                        // Show progress bar
                                                        const progressBar = document.getElementById('uploadProgress' + inspectionId);
                                                        progressBar.style.display = 'block';
                                                        progressBar.querySelector('.progress-bar').style.width = '0%';

                                                        fetch(`/inspection/${inspectionId}/photos/upload`, {
                                                            method: 'POST',
                                                            body: formData,
                                                            headers: {
                                                                'X-Requested-With': 'XMLHttpRequest'
                                                            }
                                                        })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            progressBar.style.display = 'none';
                                                            
                                                            if (data.success) {
                                                                Swal.fire(
                                                                    'Success!',
                                                                    data.message,
                                                                    'success'
                                                                );
                                                                
                                                                // Clear existing gallery content to prevent duplicates
                                                                const gallery = document.getElementById('photoGallery' + inspectionId);
                                                                gallery.innerHTML = '';
                                                                
                                                                addPhotosToGallery(inspectionId, data.photos);
                                                                fileInput.value = ''; // Clear file input
                                                            } else {
                                                                Swal.fire(
                                                                    'Error!',
                                                                    'Upload failed. Please try again.',
                                                                    'error'
                                                                );
                                                            }
                                                        })
                                                        .catch(error => {
                                                            progressBar.style.display = 'none';
                                                            Swal.fire(
                                                                'Error!',
                                                                'Upload error: ' + error.message,
                                                                'error'
                                                            );
                                                        });
                                                    }

                                                    // Delete photo function with SweetAlert
                                                    function deletePhoto(photoIndex, inspectionId) {
                                                        Swal.fire({
                                                            title: 'Are you sure?',
                                                            text: "You won't be able to revert this!",
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#3085d6',
                                                            cancelButtonColor: '#d33',
                                                            confirmButtonText: 'Yes, delete it!'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                fetch(`/inspection/${inspectionId}/photos/${photoIndex}`, {
                                                                    method: 'DELETE',
                                                                    headers: {
                                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                                        'X-Requested-With': 'XMLHttpRequest'
                                                                    }
                                                                })
                                                                .then(response => response.json())
                                                                .then(data => {
                                                                    if (data.success) {
                                                                        // Refresh gallery to update indexes
                                                                        refreshPhotoGallery(inspectionId);
                                                                        
                                                                        Swal.fire(
                                                                            'Deleted!',
                                                                            data.message,
                                                                            'success'
                                                                        );
                                                                    } else {
                                                                        Swal.fire(
                                                                            'Error!',
                                                                            data.message || 'Delete failed. Please try again.',
                                                                            'error'
                                                                        );
                                                                    }
                                                                })
                                                                .catch(error => {
                                                                    Swal.fire(
                                                                        'Error!',
                                                                        'Delete error: ' + error.message,
                                                                        'error'
                                                                    );
                                                                });
                                                            }
                                                        });
                                                    }

                                                    // Add photos to gallery - FIXED
                                                    function addPhotosToGallery(inspectionId, photos) {
                                                        const gallery = document.getElementById('photoGallery' + inspectionId);
                                                        const emptyMessage = document.getElementById('emptyPhotosMessage' + inspectionId);
                                                        
                                                        photos.forEach(photo => {
                                                            const photoHtml = `
                                                                <div class="col-md-3 col-sm-4 col-6 photo-item" data-photo-index="${photo.index}">
                                                                    <div class="card">
                                                                        <img src="${photo.url}" 
                                                                            class="card-img-top photo-thumbnail" 
                                                                            alt="${photo.name}"
                                                                            style="height: 150px; object-fit: cover; cursor: pointer;"
                                                                            onclick="showPhotoPreview('${inspectionId}', '${photo.url}', '${photo.name}')">
                                                                        <div class="card-body p-2">
                                                                            <button type="button" class="btn btn-danger btn-sm mt-1 delete-photo-btn" 
                                                                                    data-photo-index="${photo.index}"
                                                                                    data-inspection-id="${inspectionId}">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            `;
                                                            gallery.insertAdjacentHTML('beforeend', photoHtml);
                                                        });
                                                        
                                                        emptyMessage.style.display = 'none';
                                                    }

                                                    // Refresh photo gallery after deletion (to update indexes) - ADDED
                                                    function refreshPhotoGallery(inspectionId) {
                                                        fetch(`/inspection/${inspectionId}/photos`, {
                                                            headers: {
                                                                'X-Requested-With': 'XMLHttpRequest'
                                                            }
                                                        })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                const gallery = document.getElementById('photoGallery' + inspectionId);
                                                                gallery.innerHTML = ''; // Clear gallery
                                                                
                                                                if (data.photos.length > 0) {
                                                                    addPhotosToGallery(inspectionId, data.photos);
                                                                }
                                                            }
                                                        })
                                                        .catch(error => {
                                                            console.error('Error refreshing gallery:', error);
                                                        });
                                                    }

                                                    // Show message
                                                    function showMessage(inspectionId, message, type) {
                                                        const messageDiv = document.getElementById('uploadMessage' + inspectionId);
                                                        messageDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">
                                                            ${message}
                                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                        </div>`;
                                                        
                                                        // Auto-hide success messages after 3 seconds
                                                        if (type === 'success') {
                                                            setTimeout(() => {
                                                                messageDiv.innerHTML = '';
                                                            }, 3000);
                                                        }
                                                    }

                                                    // Update photo count - FIXED
                                                    function updatePhotoCount(inspectionId, count = null) {
                                                        const gallery = document.getElementById('photoGallery' + inspectionId);
                                                        const actualCount = count !== null ? count : gallery.querySelectorAll('.photo-item').length;
                                                        
                                                        document.getElementById('photoCount' + inspectionId).textContent = actualCount;
                                                        
                                                        const emptyMessage = document.getElementById('emptyPhotosMessage' + inspectionId);
                                                        emptyMessage.style.display = actualCount === 0 ? 'block' : 'none';
                                                    }

                                                    // Show photo preview within the same modal
                                                    function showPhotoPreview(inspectionId, imageUrl, imageName) {
                                                        const previewSection = document.getElementById('photoPreviewSection' + inspectionId);
                                                        const previewImage = document.getElementById('photoPreviewImage' + inspectionId);
                                                        const previewName = document.getElementById('photoPreviewName' + inspectionId);
                                                        
                                                        previewImage.src = imageUrl;
                                                        previewName.textContent = imageName;
                                                        previewSection.style.display = 'block';
                                                        
                                                        // Scroll to preview section
                                                        previewSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                                    }

                                                    // Hide photo preview
                                                    function hidePhotoPreview(inspectionId) {
                                                        const previewSection = document.getElementById('photoPreviewSection' + inspectionId);
                                                        previewSection.style.display = 'none';
                                                    }
                                                    </script>

                                                    <style>
                                                    .photo-thumbnail {
                                                        transition: transform 0.2s;
                                                    }

                                                    .photo-thumbnail:hover {
                                                        transform: scale(1.05);
                                                    }

                                                    .photo-item .card {
                                                        transition: box-shadow 0.2s;
                                                    }

                                                    .photo-item .card:hover {
                                                        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                                                    }

                                                    .delete-photo-btn {
                                                        width: 100%;
                                                        font-size: 0.8rem;
                                                    }

                                                    #uploadProgress .progress-bar {
                                                        transition: width 0.3s ease;
                                                    }
                                                    </style>

                                            

                                            </td>
                                            <td>{{ $inspection->current_mileage }} KM</td>
                                          <!-- Exterior Items -->
                                            <td>
                                                @if ($inspection->rh_front_wing == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->rh_right_wing == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->lh_front_wing == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->lh_right_wing == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->bonnet == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->front_bumper == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->rear_bumper == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->head_lights == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>

                                            <!-- Interior Functional Items -->
                                            <td>
                                                @if ($inspection->radio_speakers == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->seat_belt == 'ok')
                                                    <span class="status-badge status-ok">OK</span>
                                                @else
                                                    <span class="status-badge status-damaged">Damaged</span>
                                                @endif
                                            </td>

                                            <!-- Interior Accessories -->
                                            <td>
                                                @if ($inspection->head_rest == 'present')
                                                    <span class="status-badge status-present">Present</span>
                                                @else
                                                    <span class="status-badge status-absent">Absent</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->floor_carpets == 'present')
                                                    <span class="status-badge status-present">Present</span>
                                                @else
                                                    <span class="status-badge status-absent">Absent</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->rubber_mats == 'present')
                                                    <span class="status-badge status-present">Present</span>
                                                @else
                                                    <span class="status-badge status-absent">Absent</span>
                                                @endif
                                            </td>

                                            <!-- Tools & Accessories -->
                                            <td>
                                                @if ($inspection->jack == 'present')
                                                    <span class="status-badge status-present">Present</span>
                                                @else
                                                    <span class="status-badge status-absent">Absent</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->spare_wheel == 'present')
                                                    <span class="status-badge status-present">Present</span>
                                                @else
                                                    <span class="status-badge status-absent">Absent</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->compressor == 'present')
                                                    <span class="status-badge status-present">Present</span>
                                                @else
                                                    <span class="status-badge status-absent">Absent</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inspection->wheel_spanner == 'present')
                                                    <span class="status-badge status-present">Present</span>
                                                @else
                                                    <span class="status-badge status-absent">Absent</span>
                                                @endif
                                            </td>
                                            <td>{{ $inspection->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#updateModal{{$inspection->id}}">
                                                    Update
                                                </button>
                                                <!-- Update Modal (New) -->
                                                <div class="modal fade" id="updateModal{{$inspection->id}}" tabindex="-1" aria-labelledby="updateModalLabel{{$inspection->id}}" aria-hidden="true">
                                                    <div class="modal-dialog modal-md">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title fs-5" id="standard-modalLabel">  Update Vehicle Condition and Accessories Checklist</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                        <div class="modal-content">
                                                        <form id="VehicleInspectionFormupdate{{ $inspection->id }}" data-inspection-id="{{ $inspection->id }}">
                                                                    @csrf
                                                                    <input type="hidden" name="_method" value="PUT">
                                                            <h4>Overall Condition: <span id="overallPercent_update{{ $inspection->id }}">{{ $inspection->overall_percent }}%</span></h4>
                                                            <input type="hidden" name="overall_percent" id="overall_percent_input_update{{ $inspection->id }}" value="{{ $inspection->overall_percent }}">

                                                            <h6>Exterior Condition: <span id="exteriorPercent_update{{ $inspection->id }}">{{ $inspection->exterior_percent }}%</span></h6>
                                                            <input type="hidden" name="exterior_percent" id="exterior_percent_input_update{{ $inspection->id }}" value="{{ $inspection->exterior_percent }}">

                                                            <table border="1">
                                                                <tr>
                                                                    <td>Car's Details</td>
                                                                    <td colspan="2">
                                                                        <select class="form-select" id="imported_id_update{{ $inspection->id }}" name="imported_id" required>
                                                                            <option disabled value="">Choose</option>
                                                                            @foreach ($customers as $customer)
                                                                                <option value="{{ $customer->id }}" {{ optional($inspection->carsImport)->id == $customer->id ? 'selected' : '' }}>
                                                                                {{ $customer->model }} - {{ $customer->vin }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>

                                                                <tr><th>Part</th><th>OK</th><th>Damaged</th></tr>
                                                                @foreach (['rh_front_wing', 'rh_right_wing', 'lh_front_wing', 'lh_right_wing', 'bonnet', 'rh_front_door', 'rh_rear_door', 'lh_front_door', 'lh_rear_door', 'front_bumper', 'rear_bumper', 'head_lights', 'bumper_lights', 'corner_lights', 'rear_lights'] as $part)
                                                                    <tr>
                                                                        <td>{{ ucwords(str_replace('_', ' ', $part)) }}</td>
                                                                        <td><input type="radio" name="{{ $part }}" value="ok" {{ $inspection->$part == 'ok' ? 'checked' : '' }}></td>
                                                                        <td><input type="radio" name="{{ $part }}" value="damaged" {{ $inspection->$part == 'damaged' ? 'checked' : '' }}></td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>

                                                            <div class="section-title">Interior Functional Items: <span id="interiorFuncPercent_update{{ $inspection->id }}">{{ $inspection->interior_func_percent }}%</span></div>
                                                            <input type="hidden" name="interior_func_percent" id="interior_func_percent_input_update{{ $inspection->id }}" value="{{ $inspection->interior_func_percent }}">
                                                            <table border="1">
                                                                <tr><th>Item</th><th>OK</th><th>Damaged</th></tr>
                                                                @foreach (['radio_speakers', 'seat_belt', 'door_handles'] as $item)
                                                                    <tr>
                                                                        <td>{{ ucwords(str_replace('_', ' ', $item)) }}</td>
                                                                        <td><input type="radio" name="{{ $item }}" value="ok" {{ $inspection->$item == 'ok' ? 'checked' : '' }}></td>
                                                                        <td><input type="radio" name="{{ $item }}" value="damaged" {{ $inspection->$item == 'damaged' ? 'checked' : '' }}></td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>

                                                            <div class="section-title">Interior Accessories: <span id="interiorAccPercent_update{{ $inspection->id }}">{{ $inspection->interior_acc_percent }}%</span></div>
                                                            <input type="hidden" name="interior_acc_percent" id="interior_acc_percent_input_update{{ $inspection->id }}" value="{{ $inspection->interior_acc_percent }}">
                                                            <table border="1">
                                                                <tr><th>Item</th><th>Present</th><th>Absent</th></tr>
                                                                @foreach (['head_rest', 'floor_carpets', 'rubber_mats', 'cigar_lighter', 'boot_mats'] as $item)
                                                                    <tr>
                                                                        <td>{{ ucwords(str_replace('_', ' ', $item)) }}</td>
                                                                        <td><input type="radio" name="{{ $item }}" value="present" {{ $inspection->$item == 'present' ? 'checked' : '' }}></td>
                                                                        <td><input type="radio" name="{{ $item }}" value="absent" {{ $inspection->$item == 'absent' ? 'checked' : '' }}></td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>

                                                            <div class="section-title">Tools & Accessories: <span id="toolsPercent_update{{ $inspection->id }}">{{ $inspection->tools_percent }}%</span></div>
                                                            <input type="hidden" name="tools_percent" id="tools_percent_input_update{{ $inspection->id }}" value="{{ $inspection->tools_percent }}">
                                                            <table border="1">
                                                                <tr><th>Item</th><th>Present</th><th>Absent</th></tr>
                                                                @foreach (['jack', 'spare_wheel', 'compressor', 'wheel_spanner'] as $item)
                                                                    <tr>
                                                                        <td>{{ ucwords(str_replace('_', ' ', $item)) }}</td>
                                                                        <td><input type="radio" name="{{ $item }}" value="present" {{ $inspection->$item == 'present' ? 'checked' : '' }}></td>
                                                                        <td><input type="radio" name="{{ $item }}" value="absent" {{ $inspection->$item == 'absent' ? 'checked' : '' }}></td>
                                                                    </tr>
                                                                @endforeach
                                                                <tr>
                                                                    <td>Current Mileage</td>
                                                                    <td colspan="2"><input type="text" name="current_mileage" value="{{ $inspection->current_mileage }}"></td>
                                                                </tr>
                                                                </tr>
                                                                <tr><td>Notes</td>
                                                                <td colspan="2"><textarea name="inspection_notes"> {{ $inspection->inspection_notes }}</textarea></td>
                                                            </tr>
                                                            </table>

                                                            <button type="submit" class="btn btn-success">Update Inspection</button>
                                                            <br>
                                                        </form>
                                                        <script>
                                                        document.addEventListener('DOMContentLoaded', function () {
                                                            function calculateUpdate{{ $inspection->id }}() {
                                                                const exteriorItems = ['rh_front_wing','rh_right_wing','lh_front_wing','lh_right_wing','bonnet','rh_front_door','rh_rear_door','lh_front_door','lh_rear_door','front_bumper','rear_bumper','head_lights','bumper_lights','corner_lights','rear_lights'];
                                                                const interiorFunctional = ['radio_speakers','seat_belt','door_handles'];
                                                                const interiorAccessories = ['head_rest','floor_carpets','rubber_mats','cigar_lighter','boot_mats'];
                                                                const toolsAccessories = ['jack','spare_wheel','compressor','wheel_spanner'];

                                                                let extOk = exteriorItems.filter(name => document.querySelector(`#VehicleInspectionFormupdate{{ $inspection->id }} input[name="${name}"][value="ok"]`)?.checked).length;
                                                                let intFuncOk = interiorFunctional.filter(name => document.querySelector(`#VehicleInspectionFormupdate{{ $inspection->id }} input[name="${name}"][value="ok"]`)?.checked).length;
                                                                let intAccOk = interiorAccessories.filter(name => document.querySelector(`#VehicleInspectionFormupdate{{ $inspection->id }} input[name="${name}"][value="present"]`)?.checked).length;
                                                                let toolsOk = toolsAccessories.filter(name => document.querySelector(`#VehicleInspectionFormupdate{{ $inspection->id }} input[name="${name}"][value="present"]`)?.checked).length;

                                                                let totalOk = extOk + intFuncOk + intAccOk + toolsOk;
                                                                let totalItems = exteriorItems.length + interiorFunctional.length + interiorAccessories.length + toolsAccessories.length;

                                                                let extPercent = Math.round((extOk / exteriorItems.length) * 100);
                                                                let intFuncPercent = Math.round((intFuncOk / interiorFunctional.length) * 100);
                                                                let intAccPercent = Math.round((intAccOk / interiorAccessories.length) * 100);
                                                                let toolsPercent = Math.round((toolsOk / toolsAccessories.length) * 100);
                                                                let overallPercent = Math.round((totalOk / totalItems) * 100);

                                                                document.getElementById('exterior_percent_input_update{{ $inspection->id }}').value = extPercent;
                                                                document.getElementById('interior_func_percent_input_update{{ $inspection->id }}').value = intFuncPercent;
                                                                document.getElementById('interior_acc_percent_input_update{{ $inspection->id }}').value = intAccPercent;
                                                                document.getElementById('tools_percent_input_update{{ $inspection->id }}').value = toolsPercent;
                                                                document.getElementById('overall_percent_input_update{{ $inspection->id }}').value = overallPercent;

                                                                document.getElementById('exteriorPercent_update{{ $inspection->id }}').innerText = extPercent + '%';
                                                                document.getElementById('interiorFuncPercent_update{{ $inspection->id }}').innerText = intFuncPercent + '%';
                                                                document.getElementById('interiorAccPercent_update{{ $inspection->id }}').innerText = intAccPercent + '%';
                                                                document.getElementById('toolsPercent_update{{ $inspection->id }}').innerText = toolsPercent + '%';
                                                                document.getElementById('overallPercent_update{{ $inspection->id }}').innerText = overallPercent + '%';
                                                            }

                                                            document.querySelectorAll('#VehicleInspectionFormupdate{{ $inspection->id }} input[type="radio"]').forEach(radio => {
                                                                radio.addEventListener('change', calculateUpdate{{ $inspection->id }});
                                                            });
                                                        });
                                                        </script>



                                                                </div>
                                                            </div>
                                                        </div>
                                                        <br>
                                                        <br>
                                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal1{{$inspection->id}}">
                                                                                        Report
                                                                                    </button>
                                                                                    
                                                                                    <!-- View Modal (Untouched) -->
                                                                                    <div class="modal fade" id="viewModal1{{$inspection->id}}" tabindex="-1" aria-labelledby="viewModalLabel1{{$inspection->id}}" aria-hidden="true">
                                                                                        <div class="modal-dialog modal-lg">
                                                                                            <div class="modal-content">
                                                                                                <div class="modal-header">
                                                                                                    <h4 class="modal-title fs-5" id="viewModalLabel1{{$inspection->id}}">VEHICLE INSPECTION REPORT -   
                                                                                                    <button onclick="generateInspectionPDF({{$inspection->id}})" class="pdf-button">
                                                <i class="fas fa-file-pdf"></i> Export Inspection Report
                                                </button>

                                                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
                                                <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

                                                <script>
                                                // Initialize jsPDF
                                                const { jsPDF } = window.jspdf;

                                                async function generateInspectionPDF(inspectionId) {
                                                const element = document.querySelector(`.inspection-container${inspectionId}`);
                                                const buttons = document.querySelectorAll(`button[onclick*="generateInspectionPDF(${inspectionId})"]`);
                                                
                                                if (!element) {
                                                    console.error('Inspection container not found');
                                                    alert('Report content not found. Please refresh and try again.');
                                                    return;
                                                }

                                                // Show loading state
                                                const originalButtonHTMLs = [];
                                                buttons.forEach(button => {
                                                    originalButtonHTMLs.push(button.innerHTML);
                                                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
                                                    button.disabled = true;
                                                });

                                                try {
                                                    // Clone element for capture
                                                    const elementClone = element.cloneNode(true);
                                                    elementClone.style.position = 'absolute';
                                                    elementClone.style.left = '-9999px';
                                                    elementClone.style.width = '210mm';
                                                    document.body.appendChild(elementClone);

                                                    // Create PDF
                                                    const pdf = new jsPDF({
                                                    orientation: 'portrait',
                                                    unit: 'mm',
                                                    format: 'a4',
                                                    hotfixes: ["px_scaling"]
                                                    });

                                                    // Capture with html2canvas
                                                    const canvas = await html2canvas(elementClone, {
                                                    scale: 2,
                                                    logging: false,
                                                    useCORS: true,
                                                    scrollY: 0,
                                                    windowHeight: elementClone.scrollHeight,
                                                    onclone: (clonedDoc) => {
                                                        clonedDoc.querySelectorAll('*').forEach(el => {
                                                        el.style.opacity = '1';
                                                        el.style.overflow = 'visible';
                                                        });
                                                    }
                                                    });
                                                    document.body.removeChild(elementClone);

                                                    // PDF generation
                                                    const imgData = canvas.toDataURL('image/png');
                                                    const pdfWidth = pdf.internal.pageSize.getWidth() - 20;
                                                    const imgHeight = (canvas.height * pdfWidth) / canvas.width;

                                                    // Multi-page support
                                                    let heightLeft = imgHeight;
                                                    let position = 10;
                                                    const pageHeight = pdf.internal.pageSize.getHeight() - 20;

                                                    while (heightLeft >= 0) {
                                                    pdf.addImage(imgData, 'PNG', 10, position, pdfWidth, imgHeight);
                                                    heightLeft -= pageHeight;
                                                    position -= pageHeight;
                                                    if (heightLeft > 0) pdf.addPage();
                                                    }

                                                    // DYNAMIC NAMING SOLUTION - FIXED
                                                    const customerNameElement = element.querySelector('.inventory-item label');
                                                    let customerName = 'Inspection_Report';
                                                    
                                                    if (customerNameElement) {
                                                    // Extract just the name part (after "Customer Name")
                                                    const text = customerNameElement.textContent || customerNameElement.innerText;
                                                    const nameMatch = text.match(/Customer Name\s*([^\n]+)/);
                                                    customerName = nameMatch ? nameMatch[1].trim() : 'Inspection_Report';
                                                    
                                                    // Clean the filename
                                                    customerName = customerName
                                                        .replace(/\s+/g, '_')
                                                        .replace(/[^a-zA-Z0-9_-]/g, '')
                                                        .substring(0, 50); // Limit length
                                                    }

                                                    // Final filename with timestamp
                                                    const timestamp = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                                                    pdf.save(`${customerName}_${inspectionId}_${timestamp}.pdf`);

                                                } catch (error) {
                                                    console.error("PDF generation error:", error);
                                                    alert("Failed to generate PDF. Please try again.");
                                                } finally {
                                                    buttons.forEach((button, index) => {
                                                    button.innerHTML = originalButtonHTMLs[index];
                                                    button.disabled = false;
                                                    });
                                                }
                                                }
                                                </script>
                                                
                                                                                                    </h4>
                                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                </div>
                                                                                                <div class="modal-body">
                                        <div class="inspection-container{{$inspection->id}}">
                                            <div class="header">
                                                <div class="company-info">
                                                    <div class="company-details" >
                                                        <h4>House of Cars </h4>
                                                        <h6 style="color:white">Jabavu Lane, Baridhara,<br>
                                                        P.O Box: 55230 (00100), Nairobi-Kenya<br>
                                                        Tel No: 0713400703<br>
                                                        Email: houseofcars@gmail.com</h6>
                                                    </div>
                                                    <div class="company-logo">
                                                        <div class="logo-placeholder">
                                                        
                                                    <div class="logo-box">
                                                        <a class='logo logo-light' href='#'>
                                                            <span class="logo-sm">
                                                                <img src="{{asset('dashboardv1/assets/images/hv1.png')}}" alt="" height="50">
                                                            </span>
                                                            <span class="logo-lg">
                                                                <img src="{{asset('dashboardv1/assets/images/hv1.png')}}" alt="" height="50">
                                                            </span>
                                                        </a>
                                                        <a class='logo logo-dark' href='#'>
                                                            <span class="logo-sm">
                                                                <img src="{{asset('dashboardv1/assets/images/houseofcars.png')}}" alt="" height="100">
                                                            </span>
                                                            <span class="logo-lg">
                                                                <img src="{{asset('dashboardv1/assets/images/houseofcars.png')}}" alt="" height="100">
                                                            </span>
                                                        </a>
                                                    </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="document-title">
                                                    <h4>VEHICLE INSPECTION REPORT</h4>
                                                    <p>Comprehensive Vehicle Condition Assessment</p>
                                                </div>
                                            </div>

                                            <div class="inventory-section">
                                                <div class="inventory-title">INVENTORY NOTE</div>
                                                <div class="inventory-grid">
                                                    <div class="inventory-item">
                                                        <label>Make <br>  {{ optional($inspection->carsImport)->make ?? 'N/A' }}</label>
                                                        
                                                    </div>
                                                    <div class="inventory-item">
                                                        <label>Model <br> 
                                                        {{ optional($inspection->carsImport)->model ?? 'N/A' }} </label>
                                                    </div>
                                                    <div class="inventory-item">
                                                        <label>VIN
                                                        <br> 
                                                        {{ optional($inspection->carsImport)->vin ?? 'N/A' }}
                                                        </label>
                                                    </div>
                                                    <div class="inventory-item">
                                                        <label>Year
                                                        <br> 
                                                        {{ optional($inspection->carsImport)->year ?? 'N/A' }}
                                                        </label>
                                                    </div>
                                                   
                                                    <div class="inventory-item">
                                                        <label>Current Mileage 
                                                        <br> 
                                                        {{ $inspection->current_mileage ?? 'N/A' }}
                                                        </label> 
                                                    </div>
                                                    <div class="inventory-item">
                                                        <label>Engine Type
                                                        <br> 
                                                        {{ optional($inspection->carsImport)->minimum_price ?? 'N/A' }}
                                                        </label>
                                                    </div>
                                                </div>
                                                </div>
                                                <div class="conditions-section">
                                                    <div class="conditions-title">VEHICLE CONDITIONS |  Overall Score:  <span class="status-badge status-ok"> {{ $inspection->overall_percent }}% </span></div>
                                                    
                                                    <div class="conditions-grid">
                                                        <!-- Left Column -->
                                                        <div class="conditions-column">
                                                            <table class="inspection-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="item-header">COMPONENT</th>
                                                                        <th class="status-header">STATUS</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <!-- Exterior Components -->
                                                                    <tr class="category-row">
                                                                        <td class="category-header" colspan="2">EXTERIOR COMPONENTS | Score:  <span class="status-badge status-ok"> {{ $inspection->exterior_percent }}% </span> </td>
                                                                    </tr>
                                                                    @foreach ([
                                                                        'rh_front_wing' => 'RH Front Wing',
                                                                        'rh_right_wing' => 'RH Right Wing',
                                                                        'lh_front_wing' => 'LH Front Wing',
                                                                        'lh_right_wing' => 'LH Right Wing',
                                                                        'bonnet' => 'Bonnet',
                                                                        'rh_front_door' => 'RH Front Door',
                                                                        'rh_rear_door' => 'RH Rear Door',
                                                                        'lh_front_door' => 'LH Front Door',
                                                                        'lh_rear_door' => 'LH Rear Door',
                                                                        'front_bumper' => 'Front Bumper',
                                                                        'rear_bumper' => 'Rear Bumper',
                                                                        'head_lights' => 'Head Lights',
                                                                        'bumper_lights' => 'Bumper Lights',
                                                                        'corner_lights' => 'Corner Lights',
                                                                        'rear_lights' => 'Rear Lights'
                                                                    ] as $field => $label)
                                                                        <tr>
                                                                            <td class="item-name">{{ $label }}</td>
                                                                            <td class="item-status">
                                                                                @if ($inspection->$field == 'ok')
                                                                                    <span class="status-badge status-ok">OK</span>
                                                                                @else
                                                                                    <span class="status-badge status-damaged">Damaged</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach

                                                                    
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <!-- Right Column -->
                                                        <div class="conditions-column">
                                                            <table class="inspection-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="item-header">COMPONENT</th>
                                                                        <th class="status-header">STATUS</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <!-- Functional Items -->
                                                                    <tr class="category-row">
                                                                        <td class="category-header" colspan="2">FUNCTIONAL ITEMS | Score:  <span class="status-badge status-ok">  {{ $inspection->interior_func_percent }}% </span> </td>
                                                                    </tr>
                                                                    @foreach ([
                                                                        'radio_speakers' => 'Radio Speakers',
                                                                        'seat_belt' => 'Seat Belt',
                                                                        'door_handles' => 'Door Handles'
                                                                    ] as $field => $label)
                                                                        <tr>
                                                                            <td class="item-name">{{ $label }}</td>
                                                                            <td class="item-status">
                                                                                @if ($inspection->$field == 'ok')
                                                                                    <span class="status-badge status-ok">OK</span>
                                                                                @else
                                                                                    <span class="status-badge status-damaged">Damaged</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                    <!-- Interior Accessories -->
                                                                    <tr class="category-row">
                                                                        <td class="category-header" colspan="2">INTERIOR ACCESSORIES | Score:  <span class="status-badge status-ok"> {{ $inspection->interior_acc_percent }}%</span></td>
                                                                    </tr>
                                                                    @foreach ([
                                                                        'head_rest' => 'Head Rest',
                                                                        'floor_carpets' => 'Floor Carpets',
                                                                        'rubber_mats' => 'Rubber Mats',
                                                                        'cigar_lighter' => 'Cigar Lighter',
                                                                        'boot_mats' => 'Boot Mats'
                                                                    ] as $field => $label)
                                                                        <tr>
                                                                            <td class="item-name">{{ $label }}</td>
                                                                            <td class="item-status">
                                                                                @if ($inspection->$field == 'present')
                                                                                    <span class="status-badge status-present">Present</span>
                                                                                @else
                                                                                    <span class="status-badge status-absent">Absent</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach

                                                                    <!-- Tools & Accessories -->
                                                                    <tr class="category-row">
                                                                        <td class="category-header" colspan="2">TOOLS & ACCESSORIES | Score:  <span class="status-badge status-ok"> {{ $inspection->tools_percent }}% </span></td>
                                                                    </tr>
                                                                    @foreach ([
                                                                        'jack' => 'Jack',
                                                                        'spare_wheel' => 'Spare Wheel',
                                                                        'compressor' => 'Compressor',
                                                                        'wheel_spanner' => 'Wheel Spanner'
                                                                    ] as $field => $label)
                                                                        <tr>
                                                                            <td class="item-name">{{ $label }}</td>
                                                                            <td class="item-status">
                                                                                @if ($inspection->$field == 'present')
                                                                                    <span class="status-badge status-present">Present</span>
                                                                                @else
                                                                                    <span class="status-badge status-absent">Absent</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="form-container">
                                                            <div class="dealer-info">INSPECTION NOTES</div>
                                                         <div class="conditions">
                                                            <div class="condition-item">
                                                                <span class="condition-number"></span>
                                                                {{ $inspection->inspection_notes }}
                                                            </div>
                                                         </div>

                                                            <div class="dealer-info">GENERAL CONDITIONS</div>
                                                        <div class="conditions">
                                                            <div class="condition-item text-justify" >
                                                                <span class="condition-number">1.</span> KELMER'S HOUSE OF CARS LTD will take precautionary security and safety measures to safeguard customers' property but KELMER'S HOUSE OF CARS LTD will not be responsible for damage or loss of any vehicle while in KELMER'S HOUSE OF CARS LTD premises.
                                                            </div>

                                                            <div class="condition-item text-justify">
                                                                <span class="condition-number">2.</span> KELMER'S HOUSE OF CARS LTD will receive all payments on behalf of customer and remit the same to customer.
                                                            </div>

                                                            <div class="condition-item text-justify">
                                                                <span class="condition-number">3.</span> KELMER'S HOUSE OF CARS LTD will acknowledge receipt of all vehicle and any extra such as tinted.
                                                            </div>

                                                            <div class="condition-item text-justify">
                                                                <span class="condition-number">4.</span> The Owner/Agent of the car indemnifies KELMER'S HOUSE OF CARS LTD from any legal process for the receipt of the vehicles at the showroom & confirm that all taxes have been paid.
                                                            </div>

                                                            <div class="condition-item text-justify">
                                                                <span class="condition-number">5.</span> KELMER'S HOUSE OF CARS LTD will advertise the vehicle in the media of their choice.
                                                            </div>

                                                            <div class="condition-item text-justify">
                                                                <span class="condition-number">6.</span> In the customer's vehicle with less or minor defects and in our history and if made difficult to avail vehicle, KELMER'S HOUSE OF CARS LTD reserves the right to rectify such minor defects and recover the same from the customer provided that he shall not exceed Ksh 10,000 (Ten Thousand shillings only).
                                                            </div>

                                                            <div class="condition-item text-justify">
                                                                <span class="condition-number">7.</span> If the vehicle, while in the process of selling within KELMER'S HOUSE OF CARS LTD receives a serious commitment from a potential buyer, the Customer (Car Owner/Agent) cannot withdraw the vehicle from KELMER'S HOUSE OF CARS LTD premises and the buyer will be given a preference. 
                                                                A penalty of Ksh 10,000 (£25) of proposed value whichever is higher will be charged for any breach of this condition.
                                                                
                                                                
                                                            </div>
                                                            <br>
                                                        <br>
                                                        <br>
                                                        <br>
                                                        <br>
                                                        <br>
                                                        </div>
                                                        <br>
                                                        <br>
                                                        <br>
                                                        <br>
                                                        
                                                        <div class="dealer-info">REMARKS</div>
                                                            <div class="authorization text-justify">
                                                                <strong>I do authorize the above mentioned Co. to sell the mentioned vehicle on my behalf.</strong><br>
                                                                <strong>N.B:</strong> Miscellaneous expenses will be catered for by the owner of the vehicle e.g. fuel for road test, Minor repair jobs e.t.c
                                                            </div>

                                                        <div class="signature-section">
                                                            <div class="signature-box">
                                                                <div class="label">Received by:</div>
                                                                <div class="signature-line"></div>
                                                                <div class="label">Sign:</div>
                                                                <div class="signature-line"></div>
                                                                <div class="dealer-info">Kelmer's House of Cars</div>
                                                            </div>

                                                            <div class="signature-box">
                                                                <div class="label">Date:</div>
                                                                <div class="signature-line"></div>
                                                                <div class="label">Sign:</div>
                                                                <div class="signature-line"></div>
                                                                <div class="dealer-info">MV Owner/ID No.</div>
                                                            </div>

                                                            <div class="dealer-info" style="color:red"> Vehicle(s) are sold at owner's risk</div>
                                                        
                                                        </div>
                                                        <div class="condition-item">
                                                           <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="row">
                                                                    <div class="dealer-info" style="color:#000"> Photos</div>
                                                                    @foreach($inspection->getPhotosWithUrls() as $photo)
                                                                        <div class="col-md-6 col-sm-6 col-6 mb-3">
                                                                            <div class="card">
                                                                                <img src="{{ $photo['url'] }}"
                                                                                    class="card-img-top photo-thumbnail"
                                                                                    alt="{{ $photo['name'] }}"
                                                                                    style="height: 220px; object-fit: cover; cursor: pointer;">
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>

                                                            </div>
                                                    
                                                    </div>
                                                    
                                                </div>

                                        </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="21">No inspections found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div> <!-- container-fluid -->
</x-app-layout>