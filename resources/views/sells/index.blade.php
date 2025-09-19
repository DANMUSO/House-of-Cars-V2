<!-- Updated Blade Template with External Download Button -->
<x-app-layout>
<div class="container-fluid">
    <!-- Compact Header -->
    <div class="py-2 d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fs-18 fw-semibold m-0">Gate Pass Cards</h4>
            <small class="text-muted">Vehicle Exit Authorization</small>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success" id="cash-count">Cash: {{ $combined->where('record_type', 'incash')->count() }}</span>
            <span class="badge bg-info" id="hp-count">HP: {{ $combined->where('record_type', 'hire_purchase')->count() }}</span>
            <span class="badge bg-warning" id="ga-count">GA: {{ $combined->where('record_type', 'gentleman_agreement')->count() }}</span>
            <span class="badge bg-secondary" id="total-count">Total: {{ $combined->count() }}</span>
        </div>
    </div>

    <!-- Compact Filter Bar -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Sale Type</label>
                    <select id="filter-sale-type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="incash">Cash Sale</option>
                        <option value="hire_purchase">Hire Purchase</option>
                        <option value="gentleman_agreement">Gentleman's Agreement</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Pass ID</label>
                    <input type="text" id="filter-pass-id" class="form-control form-control-sm" placeholder="GP-000001 or 1">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">ID Number</label>
                    <input type="text" id="filter-id-number" class="form-control form-control-sm" placeholder="Customer ID">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Customer</label>
                    <input type="text" id="filter-customer" class="form-control form-control-sm" placeholder="Customer name">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Vehicle</label>
                    <input type="text" id="filter-vehicle" class="form-control form-control-sm" placeholder="Make/Model">
                </div>
                <div class="col-md-2">
                    <button type="button" id="clear-filters" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <small class="text-muted" id="filter-status">Showing all {{ $combined->count() }} records</small>
            </div>
        </div>
    </div>

    <!-- Compact Gate Pass Cards -->
    <div class="row" id="gate-pass-container">
        
        @foreach($combined as $sale)
        <div class="col-md-6 col-lg-6 gate-pass-item" 
             data-sale-type="{{ $sale->record_type }}"
             data-pass-id="GP-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}"
             data-id-number="{{ $sale->ID_Number ?? $sale->customer_id_number ?? $sale->national_id ?? '' }}"
             data-customer="{{ $sale->Client_Name ?? $sale->customer_name ?? $sale->client_name ?? '' }}"
             data-vehicle="{{ 
                ($sale->carImport->make ?? '') . ' ' . 
                ($sale->carImport->model ?? '') . ' ' . 
                ($sale->customerVehicle->vehicle_make ?? '') . ' ' .
                ($sale->vehicle_make ?? '')
             }}">
            
            <div class="card gate-pass-card mb-3" id="gate-pass-{{ $sale->id }}">
                <div class="row">
    <div class="col-md-4">
        <div class="company-logo">
            <img src="{{asset('dashboardv1/assets/images/houseofcars.png')}}" alt="House of Cars" class="logo-img" style="height:160px; width:auto">
        </div>
    </div>
    <div class="col-md-8">
        <div class="company-details">
            <br>
            <div style="color:#000">
                <span>📞 +254 715 400 709 | +254735 400 709</span><br>
                <span>📧 Email: houseofcarske@gmail.com</span><br>
                <span>🌐 houseofcars.co.ke</span><br>
                <span>📍 Jabavu Lane, Hurlingham<br>
                    P.O Box 9215 - 00100, Nairobi - Kenya
                </span>
            </div>
        </div>
    </div>
     <div class="text-center mb-3" style="border-bottom: 3px solid #007bff; padding-bottom: 10px;">
            <h5 style="font-weight: bold; color: #000; margin: 0;">GATEPASS/DELIVERY</h5>
        </div>
</div>

<div class="col-md-12">
    <!-- Vehicle Section -->
    <div class="info-section">
        <div class="section-header">
            <i class="fas fa-car" style="background-color: #f8f9fa !important; color: black !important;"></i>
            <span style="background-color: #f8f9fa !important; color: black !important;">VEHICLE DETAILS</span>
        </div>
        <div class="section-content" class="text-center" >
            <div class="row">
                <!-- Registration/Number Plate -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Registration:</strong>
                        @if($sale->record_type == 'incash')
                            @if($sale->car_type == 'import' && $sale->carImport)
                                {{ $sale->carImport->vin ?? 'N/A' }}
                            @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                                {{ $sale->customerVehicle->number_plate ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        @elseif($sale->record_type == 'hire_purchase')
                            @if($sale->carImport)
                                {{ $sale->carImport->vin ?? 'N/A' }}
                            @elseif($sale->customerVehicle)
                                {{ $sale->customerVehicle->number_plate ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        @else
                            @if($sale->car_type == 'import' && $sale->carImport)
                                {{ $sale->carImport->vin ?? 'N/A' }}
                            @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                                {{ $sale->customerVehicle->number_plate ?? 'N/A' }}
                            @else
                                {{ $sale->vehicle_plate ?? 'N/A' }}
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Stock No -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Stock No:</strong>
                        @if($sale->car_type == 'import')
                            {{ $sale->carImport->id ?? $sale->imported_id ?? 'N/A' }}
                        @else
                            {{ $sale->customerVehicle->id ?? $sale->car_id ?? 'N/A' }}
                        @endif
                    </div>
                </div>

                <!-- Year of Manufacture -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Year:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->year ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            @php
                                $model = $sale->customerVehicle->model ?? '';
                                preg_match('/(\d{4})/', $model, $matches);
                                echo $matches[1] ?? 'N/A';
                            @endphp
                        @else
                            {{ $sale->vehicle_year ?? 'N/A' }}
                        @endif
                    </div>
                </div>

                <!-- Make -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Make:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->make ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            {{ $sale->customerVehicle->vehicle_make ?? 'N/A' }}
                        @else
                            {{ $sale->vehicle_make ?? 'N/A' }}
                        @endif
                    </div>
                </div>

                <!-- Model -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Model:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->model ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            {{ $sale->customerVehicle->model ?? 'N/A' }}
                        @else
                            {{ $sale->vehicle_model ?? 'N/A' }}
                        @endif
                    </div>
                </div>

                <!-- Chassis No / VIN -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Chassis/VIN:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->vin ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            {{ $sale->customerVehicle->chasis_no ?? 'N/A' }}
                        @else
                            {{ $sale->chassis_number ?? 'N/A' }}
                        @endif
                    </div>
                </div>

                <!-- Color -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Color:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->colour ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            {{ $sale->customerVehicle->colour ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <!-- Engine No -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Engine No:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->engine_no ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            {{ $sale->customerVehicle->engine_no ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <!-- Engine Capacity/CC -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Engine CC:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->engine_capacity ?? $sale->carImport->engine_type ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            {{ $sale->customerVehicle->engine_capacity ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <!-- Transmission -->
                <div class="col-md-6">
                    <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                        <strong>Transmission:</strong>
                        @if($sale->car_type == 'import' && $sale->carImport)
                            {{ $sale->carImport->transmission ?? 'N/A' }}
                        @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                            {{ $sale->customerVehicle->transmission ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>
        </div>
         <!-- Vehicle Inspection Checklist -->
    <div class="info-section mt-4">
        <div class="section-header">
            <i class="fas fa-clipboard-check" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;"></i>
            <span class="text-center" style=" background-color: #f8f9fa !important; color: black !important;">VEHICLE INSPECTION CHECKLIST</span>
        </div>
        <div class="section-content">
            <div class="inspection-notice mb-3">
                <div class="alert alert-info">
                    <strong>Notice:</strong> I/We have received the above mentioned motor vehicle and have confirmed that it is in good order and condition as per the inventory form.
                </div>
            </div>
            
            <div class="inspection-table">
                <table class="table table-bordered" style="background-color: white !important; color: black !important;">
                    <thead style="background-color: #f8f9fa !important; color: black !important;">
                        <tr>
                            <th style="background-color: #f8f9fa !important; color: black !important;">Item</th>
                            <th class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">Present</th>
                            <th class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">Absent</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: white !important; color: black !important;">
                        <tr >
                            <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Spare Wheel</strong></td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="spare_wheel_present" id="spare_wheel_present">
                                </div>
                            </td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="spare_wheel_absent" id="spare_wheel_absent">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Wheel Spanner</strong></td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wheel_spanner_present" id="wheel_spanner_present">
                                </div>
                            </td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wheel_spanner_absent" id="wheel_spanner_absent">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Jack</strong></td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jack_present" id="jack_present">
                                </div>
                            </td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jack_absent" id="jack_absent">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Life Saver</strong></td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="life_saver_present" id="life_saver_present">
                                </div>
                            </td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="life_saver_absent" id="life_saver_absent">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td  class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>First Aid Kit</strong></td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="first_aid_kit_present" id="first_aid_kit_present">
                                </div>
                            </td>
                            <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="first_aid_kit_absent" id="first_aid_kit_absent">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Comments Section -->
            <div class="comments-section mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <label for="comments" class="form-label" style="font-weight: bold; color: #dc3545; border-bottom: 2px solid #dc3545; padding-bottom: 2px;">
                            Comments:
                        </label>
                        <textarea 
                            class="form-control mt-2" 
                            id="comments" 
                            name="comments" 
                            rows="2" 
                            placeholder="Enter any additional comments or observations about the vehicle condition..."
                            style="background-color: white !important; color: black !important; border: 1px solid #dee2e6;">
                        </textarea>
                    </div>
                </div>
                
            </div>
            <!-- Comments Section -->
            <div class="comments-section mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="notice-box" style="background-color: #dc3545; color: white; padding: 20px; display: flex; align-items: center; justify-content: center; text-align: center; font-weight: bold; border-radius: 8px;">
                            I/We have received the motor vehicle above in good order and condition
                        </div>
                    </div>
                    <div class="col-md-6">
                    <div class="signature-box delivered-by-section" style="padding: 20px;  background-color: white;">
    <div style="margin-bottom: 15px; font-weight: bold;  color: black !important;" >
        Vehicle delivered by:
    </div>
    <div class="form-group mb-3">
        <div style="margin-bottom: 5px; font-size: 14px; color: black !important;">Name:</div>
        <div class="delivered-by-name" style="height: 25px; border-bottom: 1px solid #000; margin-bottom: 10px;"></div>
    </div>
    <div class="form-group mb-3">
        <div style="margin-bottom: 5px; font-size: 14px; color: black !important;">Sign:</div>
        <div class="delivered-by-email" style="height: 35px; border-bottom: 1px solid #000; margin-bottom: 10px;"></div>
    </div>
    <div class="form-group">
        <div style="margin-bottom: 5px; font-size: 14px; color: black !important;">Date:</div>
        <div class="delivered-by-date" style="height: 25px; border-bottom: 1px solid #000; width: 120px;"></div>
    </div>
</div>
                    </div>
                    <div class="col-md-6">
                         <div class="signature-box" style="padding: 20px; height: 200px; background-color: white;">
                            <div style="margin-bottom: 15px; font-weight: bold;   color: black !important;">
                                Vehicle received by:
                            </div>
                            <div class="form-group mb-3">
                                <div style="margin-bottom: 5px; font-size: 14px;   color: black !important;">Name: 
                                   
                                    </div>
                                <div style="height: 25px; border-bottom: 1px solid #000; margin-bottom: 10px; color: black !important;">   @if($sale->record_type == 'incash')
                                        {{ $sale->Client_Name ?? 'N/A' }}
                                    @elseif($sale->record_type == 'hire_purchase')
                                        {{ $sale->client_name ?? 'N/A' }}
                                    @elseif($sale->record_type == 'gentleman_agreement')
                                        {{ $sale->client_name ?? 'N/A' }}
                                    @else
                                        {{ $sale->client_name ?? $sale->Client_Name ?? 'N/A' }}
                                    @endif</div>
                            </div>
                            <div class="form-group mb-3">
                                <div style="margin-bottom: 5px; font-size: 14px;   color: black !important;">Sign | ID: 
                                   
                                </div>
                                <div style="height: 35px; border-bottom: 1px solid #000; margin-bottom: 10px; color: black !important;"> 
                                     @if($sale->record_type == 'incash')
                                        {{ $sale->National_ID ?? 'N/A' }}
                                    @elseif($sale->record_type == 'hire_purchase')
                                        {{ $sale->national_id ?? 'N/A' }}
                                    @elseif($sale->record_type == 'gentleman_agreement')
                                        {{ $sale->national_id ?? 'N/A' }}
                                    @else
                                        {{ $sale->national_id ?? $sale->National_ID ?? 'N/A' }}
                                    @endif</div>
                            </div>
                             <div class="form-group">
                                <div style="margin-bottom: 5px; font-size: 14px;   color: black !important;">Date:</div>
                                <div style="height: 25px; border-bottom: 1px solid #000; width: 120px;"></div>
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
            <div class="row">
             <div class="col-md-12">
                <!-- Download Button OUTSIDE Card -->
            <div class="download-actions mb-3">
                <button class="btn btn-primary btn-sm w-100" onclick="downloadCard({{ $sale->id }})">
                    <i class="fas fa-download"></i> Download Gate Pass
                </button>
            </div>
            </div>
            </div>
            
        </div>
        @endforeach

        @foreach($vehicles as $vehicle)
<div class="col-md-6 col-lg-6 gate-pass-item" 
     data-pass-id="GP-{{ str_pad($vehicle->id, 6, '0', STR_PAD_LEFT) }}"
     data-customer="{{ $vehicle->customer_name ?? '' }}"
     data-vehicle="{{ $vehicle->vehicle_make ?? '' }} {{ $vehicle->model ?? '' }}">
    
    <div class="card gate-pass-card mb-3" id="gate-pass-{{ $vehicle->id }}">
        <div class="row">
            <div class="col-md-4">
                <div class="company-logo">
                    <img src="{{asset('dashboardv1/assets/images/houseofcars.png')}}" alt="House of Cars" class="logo-img" style="height:160px; width:auto">
                </div>
            </div>
            <div class="col-md-8">
                <div class="company-details">
                    <br>
                    <div style="color:#000">
                        <span>📞 +254 715 400 709 | +254735 400 709</span><br>
                        <span>📧 Email: info@kelmercars.co.ke</span><br>
                        <span>🌐 houseofcars.co.ke</span><br>
                        <span>📍 Jabavu Lane, Hurlingham<br>
                            P.O Box 9215 - 00100, Nairobi - Kenya
                        </span>
                    </div>
                </div>
            </div>
            <div class="text-center mb-3" style="border-bottom: 3px solid #007bff; padding-bottom: 10px;">
            <h5 style="font-weight: bold; color: #000; margin: 0;">GATEPASS/DELIVERY</h5>
        </div>
        </div>

        <div class="col-md-12">
            <!-- Vehicle Section -->
            <div class="info-section">
                <div class="section-header">
                    <i class="fas fa-car" style="background-color: #f8f9fa !important; color: black !important;"></i>
                    <span style="background-color: #f8f9fa !important; color: black !important;">VEHICLE DETAILS</span>
                </div>
                <div class="section-content" class="text-center" >
                    <div class="row">
                        <!-- Registration/Number Plate -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Registration:</strong>
                                {{ $vehicle->number_plate ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Stock No -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Stock No:</strong>
                                {{ $vehicle->id ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Year of Manufacture -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Year:</strong>
                                @php
                                    $model = $vehicle->model ?? '';
                                    preg_match('/(\d{4})/', $model, $matches);
                                    echo $matches[1] ?? 'N/A';
                                @endphp
                            </div>
                        </div>

                        <!-- Make -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Make:</strong>
                                {{ $vehicle->vehicle_make ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Model -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Model:</strong>
                                {{ $vehicle->model ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Chassis No / VIN -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Chassis/VIN:</strong>
                                {{ $vehicle->chasis_no ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Color -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Color:</strong>
                                {{ $vehicle->colour ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Engine No -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Engine No:</strong>
                                {{ $vehicle->engine_no ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Engine Capacity/CC -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Engine CC:</strong>
                                {{ $vehicle->engine_capacity ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Transmission -->
                        <div class="col-md-6">
                            <div class="secondary-info" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;">
                                <strong>Transmission:</strong>
                                {{ $vehicle->transmission ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Vehicle Inspection Checklist -->
                <div class="info-section mt-4">
                    <div class="section-header">
                        <i class="fas fa-clipboard-check" style="background-color: #f8f9fa !important; color: black !important; #dee2e6 !important;"></i>
                        <span class="text-center" style=" background-color: #f8f9fa !important; color: black !important;">VEHICLE INSPECTION CHECKLIST</span>
                    </div>
                    <div class="section-content">
                        <div class="inspection-notice mb-3">
                            <div class="alert alert-info">
                                <strong>Notice:</strong> I/We have received the above mentioned motor vehicle and have confirmed that it is in good order and condition as per the inventory form.
                            </div>
                        </div>
                        
                        <div class="inspection-table">
                            <table class="table table-bordered" style="background-color: white !important; color: black !important;">
                                <thead style="background-color: #f8f9fa !important; color: black !important;">
                                    <tr>
                                        <th style="background-color: #f8f9fa !important; color: black !important;">Item</th>
                                        <th class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">Present</th>
                                        <th class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">Absent</th>
                                    </tr>
                                </thead>
                                <tbody style="background-color: white !important; color: black !important;">
                                    <tr >
                                        <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Spare Wheel</strong></td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="spare_wheel_present" id="spare_wheel_present">
                                            </div>
                                        </td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="spare_wheel_absent" id="spare_wheel_absent">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Wheel Spanner</strong></td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="wheel_spanner_present" id="wheel_spanner_present">
                                            </div>
                                        </td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="wheel_spanner_absent" id="wheel_spanner_absent">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Jack</strong></td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="jack_present" id="jack_present">
                                            </div>
                                        </td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="jack_absent" id="jack_absent">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>Life Saver</strong></td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="life_saver_present" id="life_saver_present">
                                            </div>
                                        </td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="life_saver_absent" id="life_saver_absent">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td  class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;"><strong>First Aid Kit</strong></td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="first_aid_kit_present" id="first_aid_kit_present">
                                            </div>
                                        </td>
                                        <td class="text-center" class="text-center" style="width: 100px; background-color: #f8f9fa !important; color: black !important;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="first_aid_kit_absent" id="first_aid_kit_absent">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Comments Section -->
                        <div class="comments-section mt-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="comments" class="form-label" style="font-weight: bold; color: #dc3545; border-bottom: 2px solid #dc3545; padding-bottom: 2px;">
                                        Comments:
                                    </label>
                                    <textarea 
                                        class="form-control mt-2" 
                                        id="comments" 
                                        name="comments" 
                                        rows="2" 
                                        placeholder="Enter any additional comments or observations about the vehicle condition..."
                                        style="background-color: white !important; color: black !important; border: 1px solid #dee2e6;">
                                    </textarea>
                                </div>
                            </div>
                            
                        </div>
                        <!-- Comments Section -->
                        <div class="comments-section mt-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="notice-box" style="background-color: #dc3545; color: white; padding: 20px; display: flex; align-items: center; justify-content: center; text-align: center; font-weight: bold; border-radius: 8px;">
                                        I/We have received the motor vehicle above in good order and condition
                                    </div>
                                </div>
                                <div class="col-md-5">
                                <div class="signature-box delivered-by-section" style="padding: 20px;  background-color: white;">
                                <div style="margin-bottom: 15px; font-weight: bold;  color: black !important;" >
                                    Vehicle delivered by:
                                </div>
                                <div class="form-group mb-3">
                                    <div style="margin-bottom: 5px; font-size: 14px; color: black !important;">Name:</div>
                                    <div class="delivered-by-name" style="height: 25px; border-bottom: 1px solid #000; margin-bottom: 10px;"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <div style="margin-bottom: 5px; font-size: 14px; color: black !important;">Sign:</div>
                                    <div class="delivered-by-email" style="height: 35px; border-bottom: 1px solid #000; margin-bottom: 10px;"></div>
                                </div>
                                <div class="form-group">
                                    <div style="margin-bottom: 5px; font-size: 14px; color: black !important;">Date:</div>
                                    <div class="delivered-by-date" style="height: 25px; border-bottom: 1px solid #000; width: 120px;"></div>
                                </div>
                            </div>
                                </div>
                                <div class="col-md-7">
                                     <div class="signature-box" style="padding: 20px; height: 200px; background-color: white;">
                                        <div style="margin-bottom: 15px; font-weight: bold;   color: black !important;">
                                            Vehicle received by:
                                        </div>
                                        <div class="form-group mb-3">
                                            <div style="margin-bottom: 5px; font-size: 14px;   color: black !important;">Name : 
                                               
                                                </div>
                                            <div style="height: 25px; border-bottom: 1px solid #000; margin-bottom: 10px; color: black !important;">{{ $vehicle->customer_name ?? 'N/A' }} </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <div style="margin-bottom: 5px; font-size: 14px;   color: black !important;">Sign  | ID: 
                                               
                                            </div>
                                            <div style="height: 35px; border-bottom: 1px solid #000; margin-bottom: 10px; color: black !important;">| {{ $vehicle->national_id ?? 'N/A' }}</div>
                                        </div>
                                        <div class="form-group">
                                            <div style="margin-bottom: 5px; font-size: 14px;   color: black !important;">Date:</div>
                                            <div style="height: 25px; border-bottom: 1px solid #000; width: 120px;"></div>
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
    
   
     <div class="row">
             <div class="col-md-12">
                <!-- Download Button OUTSIDE Card -->
            <div class="download-actions mb-3">
                <button class="btn btn-danger btn-sm w-100" onclick="downloadCard({{ $vehicle->id }})">
                    <i class="fas fa-download"></i> Download Gate Pass (Deleted)
                </button>
            </div>
              </div>
            </div>
</div>
@endforeach

    </div>

    <!-- No Results Message -->
    <div class="text-center py-5 d-none" id="no-results">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No matching records found</h5>
        <p class="text-muted">Try adjusting your search criteria</p>
    </div>

    <!-- Empty State -->
    @if($combined->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-car fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Gate Pass Records</h5>
            <p class="text-muted">Records will appear here once vehicles are sold</p>
        </div>
    @endif
</div>

<!-- JavaScript -->
<script>
class GatePassFilter {
    constructor() {
        this.items = document.querySelectorAll('.gate-pass-item');
        this.totalItems = this.items.length;
        this.initializeFilters();
        this.setupEventListeners();
    }

    initializeFilters() {
        this.filters = {
            saleType: document.getElementById('filter-sale-type'),
            passId: document.getElementById('filter-pass-id'),
            idNumber: document.getElementById('filter-id-number'),
            customer: document.getElementById('filter-customer'),
            vehicle: document.getElementById('filter-vehicle')
        };
    }

    setupEventListeners() {
        Object.values(this.filters).forEach(filter => {
            if (filter.type === 'text') {
                filter.addEventListener('input', () => this.applyFilters());
            } else {
                filter.addEventListener('change', () => this.applyFilters());
            }
        });

        document.getElementById('clear-filters').addEventListener('click', () => {
            this.clearAllFilters();
        });
    }

    applyFilters() {
        const filterValues = {
            saleType: this.filters.saleType.value.toLowerCase(),
            passId: this.filters.passId.value.toLowerCase(),
            idNumber: this.filters.idNumber.value.toLowerCase(),
            customer: this.filters.customer.value.toLowerCase(),
            vehicle: this.filters.vehicle.value.toLowerCase()
        };

        let visibleCount = 0;
        let typeCounts = { incash: 0, hire_purchase: 0, gentleman_agreement: 0 };

        this.items.forEach(item => {
            const itemData = {
                saleType: item.dataset.saleType.toLowerCase(),
                passId: item.dataset.passId.toLowerCase(),
                idNumber: item.dataset.idNumber.toLowerCase(),
                customer: item.dataset.customer.toLowerCase(),
                vehicle: item.dataset.vehicle.toLowerCase()
            };

            const matches = this.checkMatches(filterValues, itemData);

            if (matches) {
                item.style.display = 'block';
                item.classList.remove('d-none');
                visibleCount++;
                typeCounts[item.dataset.saleType]++;
            } else {
                item.style.display = 'none';
                item.classList.add('d-none');
            }
        });

        this.updateCounters(visibleCount, typeCounts);
        this.toggleNoResults(visibleCount === 0);
        this.updateFilterStatus(filterValues, visibleCount);
    }

    checkMatches(filterValues, itemData) {
        if (filterValues.saleType && !itemData.saleType.includes(filterValues.saleType)) return false;
        if (filterValues.passId) {
            const passIdMatch = itemData.passId.includes(filterValues.passId) ||
                               itemData.passId.replace('gp-', '').includes(filterValues.passId);
            if (!passIdMatch) return false;
        }
        if (filterValues.idNumber && !itemData.idNumber.includes(filterValues.idNumber)) return false;
        if (filterValues.customer && !itemData.customer.includes(filterValues.customer)) return false;
        if (filterValues.vehicle && !itemData.vehicle.includes(filterValues.vehicle)) return false;
        return true;
    }

    updateCounters(visibleCount, typeCounts) {
        document.getElementById('cash-count').textContent = `Cash: ${typeCounts.incash}`;
        document.getElementById('hp-count').textContent = `HP: ${typeCounts.hire_purchase}`;
        document.getElementById('ga-count').textContent = `GA: ${typeCounts.gentleman_agreement}`;
        document.getElementById('total-count').textContent = `Total: ${visibleCount}`;
    }

    toggleNoResults(show) {
        const noResults = document.getElementById('no-results');
        const container = document.getElementById('gate-pass-container');
        
        if (show && this.totalItems > 0) {
            noResults.classList.remove('d-none');
            container.style.display = 'none';
        } else {
            noResults.classList.add('d-none');
            container.style.display = 'flex';
        }
    }

    updateFilterStatus(filterValues, visibleCount) {
        const activeFilters = Object.entries(filterValues)
            .filter(([key, value]) => value)
            .map(([key, value]) => `${key}: "${value}"`)
            .join(', ');

        const status = activeFilters 
            ? `Showing ${visibleCount} of ${this.totalItems} records (filtered by: ${activeFilters})`
            : `Showing all ${visibleCount} records`;

        document.getElementById('filter-status').textContent = status;
    }

    clearAllFilters() {
        Object.values(this.filters).forEach(filter => {
            filter.value = '';
        });
        this.applyFilters();
    }
}

// CLEAN Download Function - Clones existing card and adds signatures
function downloadCard(saleId) {
    const cardElement = document.getElementById(`gate-pass-${saleId}`);
    const gatePassItem = cardElement.closest('.gate-pass-item');
    
    // Extract customer data for signature section
    const customerName = gatePassItem.dataset.customer;
    const idNumber = gatePassItem.dataset.idNumber;
    
    // Clone the existing card HTML
    const cardClone = cardElement.cloneNode(true);
    
    // Add signature sections HTML to the cloned card
    const signatureHTML = `
        <!-- Signature Sections -->
        <div class="signatures-section">
            <div class="signatures-header">Authorization Signatures</div>
            <div class="signatures-grid">
                <!-- Buyer Signature -->
                <div class="signature-box">
                    <div class="signature-label">Vehicle Buyer</div>
                    <div class="signature-area"></div>
                    <div class="signature-details">
                        <strong>Name:</strong> ${customerName || 'N/A'}<br>
                        <strong>ID Number:</strong> ${idNumber || 'N/A'}
                    </div>
                    <div class="signature-date">
                        <strong>Date:</strong> ________________
                    </div>
                </div>
                
                <!-- Seller Representative Signature -->
                <div class="signature-box">
                    <div class="signature-label">Seller Representative</div>
                    <div class="signature-area"></div>
                    <div class="signature-details">
                        <strong>Title:</strong> Sales Manager<br>
                        <strong>Department:</strong> Vehicle Sales
                    </div>
                    <div class="signature-date">
                        <strong>Date:</strong> ________________
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add signature sections to the card body
    const cardBody = cardClone.querySelector('.gate-pass-body');
    cardBody.insertAdjacentHTML('beforeend', signatureHTML);
    
    // Create print window with the cloned card
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Gate Pass - ${gatePassItem.dataset.passId}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        background: white; padding: 20px; color: #333; line-height: 1.5;
                    }
                    
                    /* Include all existing card styles */
                    .gate-pass-card {
                        border: 2px solid #000; border-radius: 12px; background: #fff; overflow: hidden;
                        max-width: 600px; margin: 0 auto;
                    }
                    .gate-pass-header {
                        background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 16px 20px;
                    }
                    .gate-pass-body { font-size: 0.9rem; background: #f8f9fa; padding: 24px; }
                    .info-section {
                        background: #fff; margin: 0 0 12px 0; border-radius: 6px; 
                        border: 1px solid #e9ecef; overflow: hidden;
                    }
                    .section-header {
                        background: #f8f9fa; padding: 10px 16px; border-bottom: 1px solid #e9ecef;
                        display: flex; align-items: center; gap: 10px; font-weight: 600; 
                        font-size: 0.8rem; color: #495057; letter-spacing: 0.5px;
                    }
                    .section-content { padding: 16px; background: #fff; }
                    .primary-info {
                        font-size: 1.1rem; font-weight: 600; color: #007bff; 
                        margin-bottom: 6px; line-height: 1.3;
                    }
                    .secondary-info { font-size: 0.85rem; color: #6c757d; line-height: 1.4; }
                    .customer-layout {
                        display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
                    }
                    .customer-main { flex: 1; }
                    .customer-main .primary-info { color: #495057; font-size: 1.05rem; }
                    .customer-id {
                        background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px;
                        padding: 10px 12px; text-align: center; min-width: 120px;
                    }
                    .id-label {
                        font-size: 0.7rem; color: #6c757d; font-weight: 500; margin-bottom: 4px;
                        text-transform: uppercase; letter-spacing: 0.3px;
                    }
                    .id-number {
                        font-weight: 700; color: #007bff; font-size: 0.9rem; font-family: 'Courier New', monospace;
                    }
                    .auth-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
                    .auth-item { display: flex; flex-direction: column; gap: 2px; }
                    .auth-label {
                        font-size: 0.75rem; color: #6c757d; font-weight: 500;
                        text-transform: uppercase; letter-spacing: 0.3px;
                    }
                    .auth-value { font-size: 0.85rem; color: #495057; font-weight: 500; }
                    .security-verification {
                        background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px;
                        padding: 12px 16px; display: flex; align-items: center; gap: 12px; margin-top: 12px;
                    }
                    .verification-icon { color: #721c24; font-size: 1.1rem; flex-shrink: 0; }
                    .verification-title {
                        color: #721c24; font-size: 0.8rem; font-weight: 700; margin-bottom: 2px;
                        text-transform: uppercase; letter-spacing: 0.5px;
                    }
                    .verification-text { color: #721c24; font-size: 0.8rem; line-height: 1.3; }
                    
                    /* Signature Styles */
                    .signatures-section {
                        background: white; border: 1px solid #e9ecef; border-radius: 8px;
                        padding: 20px; margin-top: 20px;
                    }
                    .signatures-header {
                        text-align: center; margin-bottom: 20px; color: #333; font-weight: 700;
                        font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px;
                        border-bottom: 2px solid #007bff; padding-bottom: 8px;
                    }
                    .signatures-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
                    .signature-box {
                        text-align: center; border: 1px solid #dee2e6; border-radius: 6px;
                        padding: 16px; background: #fafafa;
                    }
                    .signature-area {
                        height: 60px; border-bottom: 2px solid #333; margin-bottom: 12px; position: relative;
                    }
                    .signature-area::after {
                        content: 'Signature'; position: absolute; right: 0; bottom: -18px;
                        font-size: 0.7rem; color: #999; font-style: italic;
                    }
                    .signature-label {
                        font-weight: 700; color: #333; font-size: 0.9rem; margin-bottom: 6px;
                        text-transform: uppercase; letter-spacing: 0.3px;
                    }
                    .signature-details { font-size: 0.8rem; color: #6c757d; line-height: 1.3; }
                    .signature-date {
                        margin-top: 12px; padding-top: 8px; border-top: 1px solid #dee2e6;
                        font-size: 0.75rem; color: #6c757d;
                    }
                    .signature-date strong { color: #333; }
                    
                    /* Hide download buttons and actions */
                    .download-actions, .card-footer, button { display: none !important; }
                    
                    @media print {
                        body { padding: 0; }
                        .download-actions, .card-footer, button { display: none !important; }
                    }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                ${cardClone.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    new GatePassFilter();
});
</script>
<script>
    // SOLUTION 1: Update JavaScript to use correct route paths

class GatePassInspection {
    constructor() {
        this.initializeEventListeners();
        this.loadSavedData();
    }

    async saveInspectionData(gatePassId) {
        const inspectionData = this.collectInspectionData(gatePassId);
        if (!inspectionData) return;

        // Add user ID explicitly
        inspectionData.submitted_by = window.authUserId;

        try {
            // Use the correct route path that matches your Laravel routes
            const response = await fetch('/gate-pass-inspection/save', {  // Updated path
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(inspectionData)
            });

            if (!response.ok) {
                // Handle different HTTP status codes
                if (response.status === 401) {
                    throw new Error('Authentication required - please log in');
                } else if (response.status === 403) {
                    throw new Error('Access denied - insufficient permissions');
                } else if (response.status === 419) {
                    throw new Error('CSRF token mismatch - please refresh the page');
                } else {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSaveIndicator(gatePassId, 'saved');
                if (result.user_data) {
                    this.updateDeliveredBySection(gatePassId, result.user_data);
                }
            } else {
                console.error('Save failed:', result);
                this.showSaveIndicator(gatePassId, 'error');
                
                // Show user-friendly error message
                if (result.message) {
                    this.showErrorMessage(gatePassId, result.message);
                }
            }

        } catch (error) {
            console.error('Error saving inspection data:', error);
            this.showSaveIndicator(gatePassId, 'error');
            this.showErrorMessage(gatePassId, error.message);
        }
    }

    async loadSavedData() {
        const gatePassCards = document.querySelectorAll('.gate-pass-card');
        const gatePassIds = Array.from(gatePassCards).map(card => this.extractGatePassId(card));

        if (gatePassIds.length === 0) return;

        try {
            // Use the correct route path
            const response = await fetch('/gate-pass-inspection/load', {  // Updated path
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ gate_pass_ids: gatePassIds })
            });

            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    console.warn('Authentication/authorization failed for loading data');
                    return; // Silently fail for loading data
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.success && result.data) {
                result.data.forEach(inspectionData => {
                    this.populateInspectionData(inspectionData);
                });
            }

        } catch (error) {
            console.error('Error loading inspection data:', error);
        }
    }

    // Add error message display method
    showErrorMessage(gatePassId, message) {
        const gatePassCard = document.getElementById(`gate-pass-${gatePassId}`);
        if (!gatePassCard) return;

        // Remove existing error messages
        const existingError = gatePassCard.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message alert alert-danger';
        errorDiv.style.cssText = `
            position: absolute;
            top: 50px;
            right: 10px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            max-width: 300px;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        `;
        errorDiv.textContent = message;

        gatePassCard.style.position = 'relative';
        gatePassCard.appendChild(errorDiv);

        // Remove error message after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }

    // Rest of your existing methods...
    extractGatePassId(gatePassCard) {
        const cardId = gatePassCard.id;
        return cardId.replace('gate-pass-', '');
    }

    collectInspectionData(gatePassId) {
        const gatePassCard = document.getElementById(`gate-pass-${gatePassId}`);
        if (!gatePassCard) return null;

        const inspectionData = {
            gate_pass_id: gatePassId,
            spare_wheel_present: gatePassCard.querySelector('input[name="spare_wheel_present"]')?.checked || false,
            spare_wheel_absent: gatePassCard.querySelector('input[name="spare_wheel_absent"]')?.checked || false,
            wheel_spanner_present: gatePassCard.querySelector('input[name="wheel_spanner_present"]')?.checked || false,
            wheel_spanner_absent: gatePassCard.querySelector('input[name="wheel_spanner_absent"]')?.checked || false,
            jack_present: gatePassCard.querySelector('input[name="jack_present"]')?.checked || false,
            jack_absent: gatePassCard.querySelector('input[name="jack_absent"]')?.checked || false,
            life_saver_present: gatePassCard.querySelector('input[name="life_saver_present"]')?.checked || false,
            life_saver_absent: gatePassCard.querySelector('input[name="life_saver_absent"]')?.checked || false,
            first_aid_kit_present: gatePassCard.querySelector('input[name="first_aid_kit_present"]')?.checked || false,
            first_aid_kit_absent: gatePassCard.querySelector('input[name="first_aid_kit_absent"]')?.checked || false,
            comments: gatePassCard.querySelector('#comments')?.value || '',
            submitted_by: window.authUserId || null,
            submitted_at: new Date().toISOString()
        };

        return inspectionData;
    }

    handleCheckboxChange(checkbox) {
        const gatePassCard = checkbox.closest('.gate-pass-card');
        const gatePassId = this.extractGatePassId(gatePassCard);
        
        // Handle mutual exclusivity
        if (checkbox.name.endsWith('_present')) {
            const absentCheckbox = gatePassCard.querySelector(`input[name="${checkbox.name.replace('_present', '_absent')}"]`);
            if (checkbox.checked && absentCheckbox) {
                absentCheckbox.checked = false;
            }
        } else if (checkbox.name.endsWith('_absent')) {
            const presentCheckbox = gatePassCard.querySelector(`input[name="${checkbox.name.replace('_absent', '_present')}"]`);
            if (checkbox.checked && presentCheckbox) {
                presentCheckbox.checked = false;
            }
        }

        this.saveInspectionData(gatePassId);
    }

    showSaveIndicator(gatePassId, status) {
        const gatePassCard = document.getElementById(`gate-pass-${gatePassId}`);
        if (!gatePassCard) return;

        const existingIndicator = gatePassCard.querySelector('.save-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }

        const indicator = document.createElement('div');
        indicator.className = 'save-indicator';
        indicator.style.cssText = `
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 1000;
            transition: opacity 0.3s ease;
        `;

        if (status === 'saved') {
            indicator.textContent = 'Saved';
            indicator.style.backgroundColor = '#d4edda';
            indicator.style.color = '#155724';
            indicator.style.border = '1px solid #c3e6cb';
        } else if (status === 'error') {
            indicator.textContent = 'Save Failed';
            indicator.style.backgroundColor = '#f8d7da';
            indicator.style.color = '#721c24';
            indicator.style.border = '1px solid #f5c6cb';
        }

        gatePassCard.style.position = 'relative';
        gatePassCard.appendChild(indicator);

        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.style.opacity = '0';
                setTimeout(() => {
                    if (indicator.parentNode) {
                        indicator.remove();
                    }
                }, 300);
            }
        }, 3000);
    }

    populateInspectionData(inspectionData) {
        const gatePassCard = document.getElementById(`gate-pass-${inspectionData.gate_pass_id}`);
        if (!gatePassCard) return;

        // Populate checkboxes
        const checkboxMapping = {
            'spare_wheel_present': 'input[name="spare_wheel_present"]',
            'spare_wheel_absent': 'input[name="spare_wheel_absent"]',
            'wheel_spanner_present': 'input[name="wheel_spanner_present"]',
            'wheel_spanner_absent': 'input[name="wheel_spanner_absent"]',
            'jack_present': 'input[name="jack_present"]',
            'jack_absent': 'input[name="jack_absent"]',
            'life_saver_present': 'input[name="life_saver_present"]',
            'life_saver_absent': 'input[name="life_saver_absent"]',
            'first_aid_kit_present': 'input[name="first_aid_kit_present"]',
            'first_aid_kit_absent': 'input[name="first_aid_kit_absent"]'
        };

        Object.entries(checkboxMapping).forEach(([dataKey, selector]) => {
            const checkbox = gatePassCard.querySelector(selector);
            if (checkbox && inspectionData[dataKey]) {
                checkbox.checked = true;
            }
        });

        // Populate comments
        const commentsTextarea = gatePassCard.querySelector('#comments');
        if (commentsTextarea && inspectionData.comments) {
            commentsTextarea.value = inspectionData.comments;
        }

        // Update delivered by section if user data exists
        if (inspectionData.user_data) {
            this.updateDeliveredBySection(inspectionData.gate_pass_id, inspectionData.user_data);
        }
    }

    updateDeliveredBySection(gatePassId, userData) {
    const gatePassCard = document.getElementById(`gate-pass-${gatePassId}`);
    if (!gatePassCard || !userData) return;

    // Find the "Vehicle delivered by" section
    const deliveredBySection = gatePassCard.querySelector('.delivered-by-section');
    if (!deliveredBySection) return;

    // Update delivered by name field with full name (first_name + last_name)
    const deliveredNameDiv = deliveredBySection.querySelector('.delivered-by-name');
    if (deliveredNameDiv) {
        deliveredNameDiv.textContent = userData.name || ''; // This now contains "first_name last_name"
        deliveredNameDiv.style.color = '#000';
    }

    // Update delivered by email field  
    const deliveredEmailDiv = deliveredBySection.querySelector('.delivered-by-email');
    if (deliveredEmailDiv) {
        deliveredEmailDiv.textContent = userData.email || '';
        deliveredEmailDiv.style.color = '#000';
    }

    // Update delivered by date with submission date
    const deliveredDateDiv = deliveredBySection.querySelector('.delivered-by-date');
    if (deliveredDateDiv && userData.submitted_at) {
        const submitDate = new Date(userData.submitted_at);
        deliveredDateDiv.textContent = submitDate.toLocaleDateString();
        deliveredDateDiv.style.color = '#000';
    }

    // Update the "Vehicle received by" date section with same submission date
    const allSignatureBoxes = gatePassCard.querySelectorAll('.signature-box');
    const receivedBySection = Array.from(allSignatureBoxes).find(box => 
        box.textContent.includes('Vehicle received by:') && 
        !box.classList.contains('delivered-by-section')
    );

    if (receivedBySection && userData.submitted_at) {
        const receivedDateFields = receivedBySection.querySelectorAll('div[style*="border-bottom"]');
        const receivedDateDiv = receivedDateFields[receivedDateFields.length - 1];
        
        if (receivedDateDiv) {
            const submitDate = new Date(userData.submitted_at);
            receivedDateDiv.textContent = submitDate.toLocaleDateString();
            receivedDateDiv.style.color = '#000';
        }
    }
}

    initializeEventListeners() {
        document.addEventListener('change', (e) => {
            if (e.target.type === 'checkbox' && e.target.closest('.gate-pass-card')) {
                this.handleCheckboxChange(e.target);
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.id === 'comments' && e.target.closest('.gate-pass-card')) {
                const gatePassCard = e.target.closest('.gate-pass-card');
                const gatePassId = this.extractGatePassId(gatePassCard);
                
                clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    this.saveInspectionData(gatePassId);
                }, 1000);
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    if (!window.authUserId) {
        console.warn('User not authenticated - inspection features may not work');
    }
    
    window.gatePassInspection = new GatePassInspection();
});

// Debugging function to test authentication
function testAuth() {
    fetch('/gate-pass-inspection/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
        body: JSON.stringify({ gate_pass_id: 'test' })
    })
    .then(response => response.json())
    .then(data => console.log('Auth test result:', data))
    .catch(error => console.error('Auth test error:', error));
}
    </script>
<!-- Enhanced CSS -->
<style>
.gate-pass-card {
    border: 1px solid #000; border-radius: 12px; transition: all 0.3s ease;
    background: #fff; overflow: hidden;
}
.gate-pass-card:hover {
    transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.gate-pass-header {
    background: linear-gradient(135deg, #007bff, #0056b3); border-radius: 11px 11px 0 0;
    margin: -1px -1px 0 -1px; padding: 16px 20px;
}
.gate-pass-body { font-size: 0.9rem; background: #fff; }
.info-section {
    background: #fff; margin: 0 0 12px 0; border-radius: 6px;
    border: 1px solid #e9ecef; overflow: hidden;
}
.info-section:last-of-type { margin-bottom: 0; }
.section-header {
    background: #f8f9fa; padding: 10px 16px; border-bottom: 1px solid #e9ecef;
    display: flex; align-items: center; gap: 10px; font-weight: 600;
    font-size: 0.8rem; color: #495057; letter-spacing: 0.5px;
}
.section-header i { font-size: 0.85rem; color: #6c757d; width: 16px; text-align: center; }
.section-content { padding: 16px; background: #fff; }
.primary-info {
    font-size: 1.1rem; font-weight: 600; color: #007bff;
    margin-bottom: 6px; line-height: 1.3;
}
.secondary-info { font-size: 0.85rem; color: #6c757d; line-height: 1.4; }
.customer-layout {
    display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
}
.customer-main { flex: 1; }
.customer-main .primary-info { color: #495057; font-size: 1.05rem; }
.customer-id {
    background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px;
    padding: 10px 12px; text-align: center; min-width: 120px;
}
.id-label {
    font-size: 0.7rem; color: #6c757d; font-weight: 500; margin-bottom: 4px;
    text-transform: uppercase; letter-spacing: 0.3px;
}
.id-number {
    font-weight: 700; color: #007bff; font-size: 0.9rem; font-family: 'Courier New', monospace;
}
.auth-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
.auth-item { display: flex; flex-direction: column; gap: 2px; }
.auth-label {
    font-size: 0.75rem; color: #6c757d; font-weight: 500;
    text-transform: uppercase; letter-spacing: 0.3px;
}
.auth-value { font-size: 0.85rem; color: #495057; font-weight: 500; }
.security-verification {
    background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px;
    padding: 12px 16px; display: flex; align-items: center; gap: 12px; margin-top: 12px;
}
.verification-icon { color: #721c24; font-size: 1.1rem; flex-shrink: 0; }
.verification-title {
    color: #721c24; font-size: 0.8rem; font-weight: 700; margin-bottom: 2px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.verification-text { color: #721c24; font-size: 0.8rem; line-height: 1.3; }

/* Download Actions Styling */
.download-actions { padding: 0 15px; }
.download-actions .btn {
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;
}

/* Header Badges */
.badge { font-size: 0.75rem; padding: 6px 10px; font-weight: 600; border-radius: 4px; }

/* Responsive */
@media (max-width: 768px) {
    .col-md-2 { margin-bottom: 10px; }
    .gate-pass-card { margin-bottom: 20px; }
    .customer-layout { flex-direction: column; gap: 12px; }
    .customer-id { align-self: flex-start; min-width: auto; }
    .auth-grid { grid-template-columns: 1fr; gap: 8px; }
    .auth-item { flex-direction: row; align-items: center; gap: 8px; }
    .auth-label { min-width: 70px; }
}

#no-results { background: #f8f9fa; border-radius: 8px; margin: 20px 0; }

/* Hide download buttons in print */
@media print {
    .download-actions, .card-footer, .btn { display: none !important; }
    .gate-pass-card { break-inside: avoid; border: 2px solid #000 !important; margin-bottom: 20px; }
}
/* Professional Signature Section */
.signatures-section {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 24px;
    margin-top: 16px;
}

.signatures-header {
    text-align: center;
    margin-bottom: 24px;
    color: #495057;
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 12px;
}

.signatures-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
}

.signature-box {
    border: none;
    padding: 0;
    background: transparent;
}

.signature-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.8rem;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
}

.signature-area {
    height: 2px;
    border-bottom: 1px solid #495057;
    margin-bottom: 8px;
    background: transparent;
    position: relative;
}

.signature-area::after {
    content: '';
    display: none;
}

.signature-details {
    font-size: 0.82rem;
    color: #6c757d;
    line-height: 1.6;
    margin-top: 12px;
}

.signature-details strong {
    color: #495057;
    font-weight: 600;
}

.signature-date {
    margin-top: 16px;
    font-size: 0.82rem;
    color: #6c757d;
}

.signature-date strong {
    color: #495057;
    font-weight: 600;
}
</style>
<script>
    @auth
        window.authUserId = {{ Auth::id() }};
        window.authUserName = @json(Auth::user()->name);
        window.authUserEmail = @json(Auth::user()->email);
    @else
        window.authUserId = null;
        window.authUserName = '';
        window.authUserEmail = '';
    @endauth
</script>
</x-app-layout>