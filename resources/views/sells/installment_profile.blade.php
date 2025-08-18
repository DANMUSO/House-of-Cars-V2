
<x-app-layout>
<div class="container-fluid">
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
<div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Hire Purchase Loan Management</h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                        </div>


<!-- Two Column Layout -->
<div class="row g-4">

    <!-- Left Column: Profile -->
    <div class="col-md-6">
        <div class="card rounded-4 h-100">
        <div class="card-header bg-primary text-white fw-bold rounded-top-4">
             KYC
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>Client Name:</strong> {{ $hirePurchase->Client_Name }}</div>
                    <div class="col-md-6 mb-2"><strong>Phone Number:</strong> {{ $hirePurchase->Phone_No }}</div>

                    <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $hirePurchase->email }}</div>
                    <div class="col-md-6 mb-2"><strong>National ID:</strong> {{ $hirePurchase->National_ID }}</div>

                    <div class="col-md-6 mb-2"><strong>KRA:</strong> {{ $hirePurchase->KRA }}</div>
                    <div class="col-md-6 mb-2"><strong>Date of Purchase:</strong>{{ $hirePurchase->created_at }}</div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Loan Details -->
    <div class="col-md-6">
        <div class="card rounded-4 h-100">
            <div class="card-header bg-primary text-white fw-bold rounded-top-4">
                Loan Details
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>Loan Type:</strong> Hire Purchase</div>
                    <div class="col-md-6 mb-2"><strong>Purchasing Price:</strong> KES {{ number_format($hirePurchase->Amount, 2) }}</div>

                    <div class="col-md-6 mb-2"><strong>Deposit:</strong> KES {{ number_format($hirePurchase->deposit, 2) }}</div>
                    <div class="col-md-6 mb-2"><strong>Balance:</strong> KES {{ number_format($hirePurchase->Amount - $hirePurchase->deposit - $hirePurchase->payments->sum('amount'), 2) }}</div>

                    <div class="col-md-6 mb-2"><strong>Duration:</strong> {{ $hirePurchase->duration }} months</div>
                    <div class="col-md-6 mb-2"><strong>Due Dates:</strong> {{ $hirePurchase->first_due_date }} - {{ $hirePurchase->last_due_date }}</div>

                    <div class="col-md-12 mb-2"><strong>Car:</strong>
                        @if($hirePurchase->car_type === 'import' && $hirePurchase->carImport)
                            {{ $hirePurchase->carImport->make }} {{ $hirePurchase->carImport->model }} ({{ $hirePurchase->carImport->year }})
                        @elseif($hirePurchase->car_type === 'customer' && $hirePurchase->customerVehicle)
                            {{ $hirePurchase->customerVehicle->vehicle_make }} {{ $hirePurchase->customerVehicle->number_plate }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal">
                            View
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="modalLabel">
                                        @if($hirePurchase->car_type === 'import' && $hirePurchase->carImport)
                                            {{ $hirePurchase->carImport->make }} {{ $hirePurchase->carImport->model }} ({{ $hirePurchase->carImport->year }})
                                        @elseif($hirePurchase->car_type === 'customer' && $hirePurchase->customerVehicle)
                                            {{ $hirePurchase->customerVehicle->vehicle_make }} {{ $hirePurchase->customerVehicle->number_plate }}
                                        @else
                                            N/A
                                        @endif
                                        </h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                    <div class="row">

                                    @php
    $imagePaths = [];

    if ($hirePurchase->car_type === 'import' && $hirePurchase->carImport && !empty($hirePurchase->carImport->photos)) {
        $imagePaths = json_decode($hirePurchase->carImport->photos, true) ?? [];
    } elseif ($hirePurchase->car_type === 'customer' && $hirePurchase->customerVehicle && !empty($hirePurchase->customerVehicle->photos)) {
        $imagePaths = json_decode($hirePurchase->customerVehicle->photos, true) ?? [];
    }
@endphp

@if (!empty($imagePaths) && is_array($imagePaths))
    @foreach ($imagePaths as $imagePath)
        <div class="col-md-12 col-lg-6 mb-3">
            <img 
                src="{{ Str::startsWith($imagePath, '/storage/') ? asset($imagePath) : asset('storage/' . $imagePath) }}" 
                alt="Car Image" 
                class="img-fluid rounded shadow-sm w-100" 
                style="object-fit: cover; max-height: 300px;">
        </div>
    @endforeach
@else
    <div class="col-12">
        <p class="text-muted">No images available for this car.</p>
    </div>
@endif


                                    </div>
                                </div>

                                </div>
                            </div>
                        </div>
                        @php
                    $percentage = (($hirePurchase->deposit + $hirePurchase->payments->sum('amount')) / $hirePurchase->Amount) * 100;
                @endphp

                <!-- Progress Bar with fixed color and dynamic width -->
                <div class="position-relative mt-3 rounded-pill" style="height: 22px;">
                    <div class="progress rounded-pill" style="height: 22px;">
                        <div class="progress-bar bg-success" style="width: {{ $percentage }}%;"></div>
                    </div>
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center fw-bold text-white">
                        {{ number_format($percentage, 2) }}% Paid
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
<br>
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3 rounded-4 overflow-hidden" style="background-color: #822c57;">
        <li class="nav-item">
            <a class="nav-link active text-white bg-secondary" data-bs-toggle="tab" href="#installments">Installment Schedule</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" data-bs-toggle="tab" href="#payments">Payments</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Installments Tab -->
        <div id="installments" class="tab-pane fade show active">
            <div class="card shadow-sm mb-4 rounded-4">
                <div class="card-body">
                    @if ($hirePurchase->installments && $hirePurchase->installments->count() > 0)
                        <div class="row">
                            @foreach ($hirePurchase->installments as $index => $installment)
                                @php
                                    $status = strtolower($installment->status);
                                    $headerClass = match($status) {
                                        'paid' => 'bg-success text-white',
                                        'due' => 'bg-danger text-white',
                                        'pending' => 'bg-warning text-dark',
                                        default => 'bg-light'
                                    };
                                    $borderClass = match($status) {
                                        'paid' => 'border-success',
                                        'due' => 'border-danger',
                                        'pending' => 'border-warning',
                                        default => 'border-secondary'
                                    };
                                @endphp

                                <div class="col-md-4 mb-4">
                                    <div class="card shadow h-100 {{ $borderClass }} rounded-4" style="border-width: 2px;">
                                        <div class="card-header {{ $headerClass }} rounded-top-4">
                                            <h6 class="mb-0">Installment #{{ $index + 1 }} - {{ ucfirst($status) }}</h6>
                                        </div>
                                        <div class="card-body rounded-bottom-4">
                                            <p><strong>Due Date:</strong><br>{{ \Carbon\Carbon::parse($installment->due_date)->format('M d, Y') }}</p>
                                            <p><strong>Amount:</strong><br>KES {{ number_format($installment->amount, 2) }}</p>
                                            <p><strong>Status:</strong>
                                                <span class="badge 
                                                    {{ $status == 'pending' ? 'bg-warning text-dark' : 
                                                       ($status == 'paid' ? 'bg-success' : 
                                                       ($status == 'due' ? 'bg-danger' : 'bg-secondary')) }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            </p>
                                            <br>
                                            <button class="btn btn-sm btn-success ConfirmBtn" data-id="{{ $installment->id }}">
                                                        Confirm
                                                    </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No installments recorded for this loan.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payments Tab -->
        <div id="payments" class="tab-pane fade">
            <div class="card shadow-sm rounded-4">
                <div class="card-body text-muted">
                <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">In Cash</h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0"></h4>
                            </div>
                            <div class="flex-grow-1">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standard-modal">
                                               Add  Payments
                                            </button>
                                            <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="standard-modalLabel">  Add Payments</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                                                                                           
                                                    <form id="PaymentForm" class="row g-3">
                                                            @csrf
                                                            <input type="hidden" class="form-control" id="hire_id" name="hire_id" value="{{ number_format($hirePurchase->id, 2) }}" required>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Paid Amount</label>
                                                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" required>
                                                            </div>
                                                    

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
                                                <th>#ID</th>
                                                <th>Paid Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody> 
                                        @foreach ($hirePurchase->payments as $index => $payment)
                                            <tr>
                                                <td>{{ $payment->id }}</td>
                                                <td>KES {{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->status }}</td>
                                                <td>{{ $payment->created_at }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="1"><strong>Total</strong></td>
                                            <td colspan="3">
                                                <strong>
                                                KES  {{ number_format($hirePurchase->payments->sum('amount'), 2) }}
                                                </strong>
                                            </td>
                                        </tr>
                                    </tfoot>

                                   


                                    </table>

                                     </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
                       
                      
                    </div> <!-- container-fluid -->
</x-app-layout>