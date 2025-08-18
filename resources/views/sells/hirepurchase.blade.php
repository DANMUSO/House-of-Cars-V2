<x-app-layout>
<div class="container-fluid">
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
<div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Hire Purchase</h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                                               Add  Details
                                            </button>
                                            <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">  Add Details</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                                                                                           
                                                    <form id="HirePurchaseForm" class="row g-3">
                                                            @csrf
                                                            <div class="col-md-6">
                                                                <label class="form-label">Client Name</label>
                                                                <input type="text" class="form-control" id="Client_Name" name="Client_Name" required>
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
                                                                <label class="form-label">KRA</label>
                                                                <input type="text" class="form-control" id="KRA" name="KRA" required>
                                                            </div>


                                                            <div class="col-md-6">
                                                                <label class="form-label">National ID</label>
                                                                <input type="number" class="form-control" id="National_ID" name="National_ID" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Total Amount</label>
                                                                <input type="number" class="form-control" id="Amount" name="Amount" required>
                                                            </div> 
                                                            <div class="col-md-6">
                                                                <label class="form-label">Deposit</label>
                                                                <input type="number" class="form-control" id="Deposit" name="Deposit" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Duration(months)</label>
                                                                <select name="duration" id="duration" class="form-select" required>
                                                                    <option value="">Choose Duration</option>
                                                                    @for ($i = 1; $i <= 72; $i++)
                                                                        <option value="{{ $i }}" {{ old('duration') == $i ? 'selected' : '' }}>
                                                                            {{ $i }} Month{{ $i > 1 ? 's' : '' }}
                                                                        </option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Car</label>
                                                                <select class="form-select" id="car_id" name="car_id" required>
                                                                <option disabled selected value="">Choose</option>

                                                                <optgroup label="Imported Cars">
                                                                    @foreach ($cars as $car)
                                                                        @if ($car->carsImport)
                                                                            <option value="import-{{ $car->carsImport->id }}">
                                                                                {{ $car->carsImport->make }} - {{ $car->carsImport->model }} - {{ $car->carsImport->year }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </optgroup>

                                                                <optgroup label="Trade Inn | Sell In Behalf">
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
                                                            <div class="col-md-6">
                                                                <label class="form-label">First Due Date</label>
                                                                <input type="date" class="form-control" id="first_due_date" name="first_due_date" required>
                                                            </div>
                                                            <div class="col-12">
                                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
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
                                    <table  id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Car Details</th>
                                                <th>Car Type</th>
                                                <th>Client Name</th>
                                                <th>Phone Number</th>
                                                <th>Email</th>
                                                <th>KRA</th>
                                                <th>National ID</th>
                                                <th>Amount (Ksh)</th>
                                                <th>Deposit (Ksh)</th>
                                                <th>Paid Percentage (%)</th>
                                                <th>Duration (Months)</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($HirePurchases as $index => $cash)
                                                <tr>
                                                    <td>{{ $index + 1 }}
                                                    <a href="{{ route('client_profile', $cash->id) }}">View</a>
                                                    </td>

                                                    <td>
                                                        @if ($cash->car_type == 'import')
                                                            @php $car = $importCars->get($cash->car_id); @endphp
                                                            {{ $car ? $car->make . ' - ' . $car->model . ' - ' . $car->year : 'N/A' }}
                                                        @elseif ($cash->car_type == 'customer')
                                                            @php $car = $customerCars->get($cash->car_id); @endphp
                                                            {{ $car ? $car->vehicle_make . ' - ' . $car->number_plate : 'N/A' }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <span class="badge {{ $cash->car_type == 'import' ? 'bg-primary' : 'bg-success' }}">
                                                            {{ ucfirst($cash->car_type) }}
                                                        </span>
                                                    </td>

                                                    <td>{{ $cash->Client_Name }}</td>
                                                    <td>{{ $cash->Phone_No }}</td>
                                                    <td>{{ $cash->email }}</td>
                                                    <td>{{ $cash->KRA }}</td>
                                                    <td>{{ $cash->National_ID }}</td>
                                                    <td>{{ number_format($cash->Amount, 2) }}</td>
                                                    <td>{{ number_format($cash->deposit, 2) }}</td>
                                                    <td>{{ number_format($cash->paid_percentage, 2) }} %</td>
                                                    <td>{{$cash->duration}}</td>
                                                    <td>{{ $cash->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                    @if ($cash->status != 1)
                                                    <button class="btn btn-sm btn-success approveHirePurchaseBtn" data-id="{{ $cash->id }}">
                                                        Approve
                                                    </button>
                                                    <br><br>
                                                    <button class="btn btn-sm btn-warning editBtnhire"
                                                        data-id="{{ $cash->id }}"
                                                        data-client="{{ $cash->Client_Name }}"
                                                        data-phone="{{ $cash->Phone_No }}"
                                                        data-email="{{ $cash->email }}"
                                                        data-kra="{{ $cash->KRA }}"
                                                        data-national="{{ $cash->National_ID }}"
                                                        data-amount="{{ $cash->Amount }}"
                                                        data-deposit="{{ $cash->deposit }}"
                                                        data-duration="{{ $cash->duration }}">
                                                        Edit
                                                    </button>
                                                    <br><br>
                                                    <button class="btn btn-sm btn-danger deletehireBtn" data-id="{{ $cash->id }}">
                                                        Delete
                                                    </button>
                                                    @else
                                                    <span class="badge bg-success">Approved</span>
                                                    @endif
                                                    
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    <!-- Modal for Editing -->
                                    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form id="updateHirePurchaseForm">
                                            @csrf
                                            @method('POST')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel">Update Details</h5>
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
                                                            <label for="amount" class="form-label">Amount (Ksh)</label>
                                                            <input type="number" class="form-control" name="Amount" id="amount" required>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                    <div class="col-md-6">
                                                                <label class="form-label">Deposit</label>
                                                                <input type="number" class="form-control" id="deposit" name="Deposit" required>
                                                            </div>
                                                            
                                                    </div>

                                                </div>
                                                
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Update Record</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>


                                    </table>
                                     </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div> <!-- container-fluid -->
</x-app-layout>
<script>

        </script>