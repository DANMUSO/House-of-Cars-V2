<x-app-layout>
<div class="container-fluid">
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
<div class="container-fluid">
                        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Profile</h4>
                            </div>
            
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="align-items-center">
                                            
                                       
                                            <div class="row align-items-center">
                                                <!-- Profile Section -->
                                                <div class="col-md-3 text-center">
                                                    <img src="{{ asset('photos/Screenshot_1.png') }}" class="rounded-circle img-fluid border border-3 border-light mb-3" alt="profile" style="width: 120px; height: 120px;">
                                                    <h5 class="mb-0">Daniel Kimani</h5>
                                                    <p class="text-muted">2018 Land Rover</p>
                                                </div>

                                                <!-- Loan Details -->
                                                <div class="col-md-9">
                                                    <div class="row">
                                                        <!-- Left Column -->
                                                        <div class="col-md-6 mb-3">
                                                            <p class="mb-1"><strong>Loan ID:</strong> LN-903245</p>
                                                            <p class="mb-1"><strong>Principal Amount:</strong> KES 5,200,000</p>
                                                            <p class="mb-1">
                                                                <strong>Duration:</strong> 3 Years 
                                                                <span class="badge bg-primary ms-2">Monthly Payment: KES 80,000</span>
                                                            </p>
                                                        </div>

                                                        <!-- Right Column -->
                                                        <div class="col-md-6 mb-3">
                                                            <p class="mb-1"><strong>Next Payment Due:</strong> <u>2025-05-01</u></p>
                                                            <p class="mb-1"><strong>Remaining Balance:</strong> KES 840,000</p>
                                                            <p class="mb-1"><strong>Status:</strong> <span class="text-success fw-semibold">Active / On Track</span></p>
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
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body pt-0">
                                        <ul class="nav nav-underline border-bottom pt-2" id="pills-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active p-2" id="profile_about_tab" data-bs-toggle="tab" href="#profile_about" role="tab">
                                                    <span class="d-block d-sm-none"><i class="mdi mdi-information"></i></span>
                                                    <span class="d-none d-sm-block">Loan Schedules</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link p-2" id="profile_experience_tab" data-bs-toggle="tab" href="#profile_experience" role="tab">
                                                    <span class="d-block d-sm-none"><i class="mdi mdi-sitemap-outline"></i></span>
                                                    <span class="d-none d-sm-block">Repayment History</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link p-2" id="setting_tab" data-bs-toggle="tab" href="#profile_setting" role="tab">
                                                    <span class="d-block d-sm-none"><i class="mdi mdi-information"></i></span>
                                                    <span class="d-none d-sm-block">Car</span>
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content text-muted">
                                            <div class="tab-pane active show pt-4" id="profile_about" role="tabpanel">
                                            <div class="container my-5">
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

    <!-- Card Component Start -->
    <div class="col">
      <div class="card text-white h-100 border-0 shadow-lg hover-shadow" style="background-color: #1e1e1e; border-radius: 15px;">
        <div class="card-header text-center fw-bold text-white" style="background-color:rgb(9, 152, 64); border-top-left-radius: 15px; border-top-right-radius: 15px;">
          Payment #1 <br><small class="fw-normal">Loan Installment Detail</small>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2"><span>📆 Due Date:</span><span>2025-04-01</span></div>
          <div class="d-flex justify-content-between mb-2"><span>💰 Total Installment:</span><span>KES 80,000</span></div>
          <hr class="border-secondary">
          <div class="d-flex justify-content-between mb-1"><strong>Status:</strong><span class="badge bg-success px-3">Paid</span></div>
         
        </div>
      </div>
    </div>
    <!-- Card Component End -->
    
    <div class="col">
      <div class="card text-white h-100 border-0 shadow-lg hover-shadow" style="background-color: #1e1e1e; border-radius: 15px;">
        <div class="card-header text-center fw-bold text-white" style="background-color: #0d6efd; border-top-left-radius: 15px; border-top-right-radius: 15px;">
          Payment #2 <br><small class="fw-normal">Loan Installment Detail</small>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2"><span>📆 Due Date:</span><span>2025-05-01</span></div>
          <div class="d-flex justify-content-between mb-2"><span>💰 Total Payment:</span><span>KES 80,000</span></div>
          <hr class="border-secondary">
          <div class="d-flex justify-content-between mb-1"><strong>Status:</strong><span class="badge bg-primary px-3">Due</span></div>
          
        </div>
      </div>
    </div>
    <!-- Repeat Cards -->
    <div class="col">
      <div class="card text-white h-100 border-0 shadow-lg hover-shadow" style="background-color: #1e1e1e; border-radius: 15px;">
        <div class="card-header text-center fw-bold text-dark" style="background-color: #ffc107; border-top-left-radius: 15px; border-top-right-radius: 15px;">
          Payment #3 <br><small class="fw-normal">Loan Installment Detail</small>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2"><span>📆 Due Date:</span><span>2025-06-01</span></div>
          <div class="d-flex justify-content-between mb-2"><span>💰 Total Installment:</span><span>KES 80,000</span></div>
          <hr class="border-secondary">
          <div class="d-flex justify-content-between mb-1"><strong>Status:</strong><span class="badge bg-warning text-dark px-3">Upcoming</span></div>

        </div>
      </div>
    </div>
    <!-- Repeat Cards -->
    <div class="col">
      <div class="card text-white h-100 border-0 shadow-lg hover-shadow" style="background-color: #1e1e1e; border-radius: 15px;">
        <div class="card-header text-center fw-bold text-dark" style="background-color:rgb(230, 10, 10); border-top-left-radius: 15px; border-top-right-radius: 15px;">
          Payment #4 <br><small class="fw-normal">Loan Installment Detail</small>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2"><span>📆 Due Date:</span><span>2025-06-01</span></div>
          <div class="d-flex justify-content-between mb-2"><span>💰 Total Installment:</span><span>KES 80,000</span></div>
          <hr class="border-secondary">
          <div class="d-flex justify-content-between mb-1"><strong>Status:</strong><span class="badge bg-danger text-dark px-3">Overdue</span></div>

        </div>
      </div>
    </div>


  </div>
</div>


                                           

                                            </div><!-- end Experience -->
                                            
                                            <div class="tab-pane pt-4" id="profile_experience" role="tabpanel">
                                                <div class="row">

                                                    <div class="col-md-12 col-sm-12 col-lg-12">
                                              
                                                    <div class="table-responsive"> 
                                        <table id="responsive-datatable" class="table table-bordered table-hover nowrap w-100">
                                            <thead>
                                            <tr>
                                                <th>Installment No</th>
                                                <th>Installment Amount</th>
                                                <th>Loan Balance</th>
                                                <th>Penalty</th>
                                                <th>Date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                  <td>#1</td>
                                                    <td>KES 80,000</td>
                                                    <td>KES 4,560,000</td>
                                                    <td>KES 00</td>
                                                    <td>2025-05-28</td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                     </div>
                                              
                                                    
                                                    </div>

                                                </div>
                                            </div> <!-- end Experience -->

                                            <div class="tab-pane pt-4" id="profile_setting" role="tabpanel">
                                                <div class="row">
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
                                            </div> <!-- end education -->

                                        </div> <!-- Tab panes -->
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> 
                            </div> 
                            </div> 
                    </div> <!-- container-fluid -->
</x-app-layout>
<script>

        </script>