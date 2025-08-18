<x-app-layout>
<div class="container-fluid">
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
<div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Sales</h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
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
                                                <th>Car Name / Model</th>
                                                <th>VIN (Vehicle ID)</th>
                                                <th>Client Name</th>
                                                <th>Phone Number</th>
                                                <th>Email</th>
                                                <th>Amount</th>
                                                <th>Installments</th>
                                                <th>Date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal1">
                                                View
                                            </button>
                                            <div class="modal fade" id="standard-modal1" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">2018 Land Rover</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                    <div class="row">
                                                    

                                                        <div class="col-md-12 col-lg-6">
                                                        <br>
                                                        <img src="{{asset('photos/Screenshot_1.png')}}" alt="" width="100%" class="img-hover-zoom" >
                                                        
                                                        </div>
                                                        <br>
                                                        <div class="col-md-12 col-lg-6">
                                                        <br>
                                                        <img src="{{asset('photos/Screenshot_2.png')}}" alt="" width="100%" class="img-hover-zoom">
                                                        
                                                        </div>
                                                        <br>
                                                        <div class="col-md-12 col-lg-6">
                                                        <br>
                                                        <img src="{{asset('photos/Screenshot_4.png')}}" alt="" width="100%" class="img-hover-zoom">
                                                        
                                                        </div>
                                                        <br>
                                                        <div class="col-md-12 col-lg-6">
                                                        <br>
                                                        <img src="{{asset('photos/Screenshot_5.png')}}" alt="" width="100%" class="img-hover-zoom">
                                                        
                                                        </div>
                                                        <br>
                                                        <div class="col-md-12 col-lg-6">
                                                        <br>
                                                        <img src="{{asset('photos/Screenshot_7.png')}}" alt="" width="100%" class="img-hover-zoom">
                                                        
                                                        </div>
                                                        
                                                        <div class="col-md-12 col-lg-6">
                                                        <br>
                                                        <img src="{{asset('photos/Screenshot_10.png')}}" alt="" width="100%" class="img-hover-zoom">
                                                        
                                                        </div>

                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                                </td>
                                                    <td>2018 Land Rover</td>
                                                    <td>1HGCM82633A004352</td>
                                                    <td>Daniel Kimani</td>
                                                    <td>+254703894372</td>
                                                    <td>kimdan2030@gmail.com</td>
                                                    <td>$55,000</td>
                                                    <td>Bank</td>
                                                    <td>2025-05-28</td>
                                                </tr>
                                                
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