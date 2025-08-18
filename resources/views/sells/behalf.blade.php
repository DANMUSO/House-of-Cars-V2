<x-app-layout>
<div class="container-fluid">
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
<div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Sell In Behalf</h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                                               Add Customer and Vehicle Information
                                            </button>
                                            <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">  Add Customer and Vehicle Information</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                                       
                                                    <form id="TradeInForm" class="row g-3" enctype="multipart/form-data">
                                                            @csrf
                                                            <input type="hidden" class="form-control" id="Status" name="Status" value="2">
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
                                                                <label class="form-label">Vehicle Make/Type</label>
                                                                <input type="text" class="form-control" id="Vehicle_Make" name="Vehicle_Make" required>
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
                                                                <label class="form-label">Minimum Price</label>
                                                                <input type="number" class="form-control" id="Minimum_Price" name="Minimum_Price" required>
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
                                        <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Customer Name</th>
                                                <th>Phone No</th>
                                                <th>Email</th>
                                                <th>Vehicle Make</th>
                                                <th>Chasis No</th>
                                                <th>Number Plate</th>
                                                <th>Minimum Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($vehicles as $vehicle)
                                                <tr>
                                                    <td>{{ $vehicle->customer_name }}</td>
                                                    <td>{{ $vehicle->phone_no }}</td>
                                                    <td>{{ $vehicle->email }}</td>
                                                    <td>{{ $vehicle->vehicle_make }}</td>
                                                    <td>{{ $vehicle->chasis_no }}</td>
                                                    <td>{{ $vehicle->number_plate }}</td>
                                                    <td>{{ number_format($vehicle->minimum_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
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