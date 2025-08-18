<x-app-layout>
<div class="container-fluid">
    <!-- Header Section -->
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center">
                <a href="{{ route('gentlemanagreement.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <div>
                    <h4 class="fs-18 fw-semibold m-0">
                        <i class="fas fa-handshake me-2"></i>
                        @if($agreement->customerVehicle)
                            {{ $agreement->customerVehicle->vehicle_make }} ({{ $agreement->customerVehicle->year ?? 'N/A' }})
                        @elseif($agreement->carImport)
                            {{ $agreement->carImport->make }} {{ $agreement->carImport->model }} ({{ $agreement->carImport->year ?? 'N/A' }})
                        @else
                            Vehicle Details Not Available
                        @endif
                    </h4>
                    <small class="text-muted">
                        <span class="badge bg-success me-2">No Interest • No Fees • Gentleman's Agreement</span>
                        @if($agreement->customerVehicle)
                            @if($agreement->customerVehicle->chasis_no)
                                Chassis: {{ $agreement->customerVehicle->chasis_no }}
                            @elseif($agreement->customerVehicle->number_plate)
                                Plate: {{ $agreement->customerVehicle->number_plate }}
                            @else
                                Customer Vehicle ID: {{ $agreement->customerVehicle->id }}
                            @endif
                        @elseif($agreement->carImport)
                            @if($agreement->carImport->chassis_number)
                                Chassis: {{ $agreement->carImport->chassis_number }}
                            @elseif($agreement->carImport->plate_number)
                                Plate: {{ $agreement->carImport->plate_number }}
                            @else
                                Import ID: {{ $agreement->carImport->id }}
                            @endif
                        @else
                            Agreement ID: {{ $agreement->id }}
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2">
            @php
                $statusConfig = [
                    'pending' => ['class' => 'warning', 'text' => 'Pending'],
                    'active' => ['class' => 'success', 'text' => 'Active'],
                    'completed' => ['class' => 'primary', 'text' => 'Completed'],
                    'defaulted' => ['class' => 'danger', 'text' => 'Defaulted']
                ];
                $currentStatus = $statusConfig[strtolower($agreement->status)] ?? $statusConfig['pending'];
            @endphp
            <span class="badge bg-{{ $currentStatus['class'] }} fs-6 px-3 py-2">
                {{ $currentStatus['text'] }}
            </span>
            
            @if($agreement->status !== 'completed')
                <!-- Record Payment Button -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="fas fa-credit-card"></i> Record Payment 
                </button>
                
                <!-- Send Reminder Button -->
                <button type="button" class="btn btn-outline-info" onclick="sendPaymentReminder({{ $agreement->id }})">
                    <i class="fas fa-bell"></i> Send Reminder
                </button>
            @endif
        </div>
    </div>

    @php
        // Calculate accurate outstanding balance from payment schedule
        $totalScheduledAmount = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('total_amount') : 0;
        $totalPaidFromSchedule = $agreement->paymentSchedule ? $agreement->paymentSchedule->sum('amount_paid') : 0;
        $calculatedOutstanding = $totalScheduledAmount - $totalPaidFromSchedule;
        
        // Use the payment schedule calculation if it exists, otherwise use the agreement's outstanding balance
        $actualOutstanding = $totalScheduledAmount > 0 ? $calculatedOutstanding : $agreement->outstanding_balance;
        
        // Calculate total amount paid (including deposit)
        $totalAmountPaid = $agreement->deposit_amount + $agreement->amount_paid;
        
        // Calculate payment progress based on vehicle price (no interest)
        $paymentProgress = $agreement->vehicle_price > 0 ? 
            (($totalAmountPaid) / $agreement->vehicle_price) * 100 : 0;
        
        // Next payment due calculation
        $nextDueInstallment = $agreement->paymentSchedule ? 
            $agreement->paymentSchedule->whereIn('status', ['pending', 'overdue', 'partial'])->first() : null;
        
        // Overdue amount calculation (only principal, no interest)
        $overdueAmount = $agreement->paymentSchedule ? 
            $agreement->paymentSchedule->where('status', 'overdue')->sum('total_amount') : 0;
    @endphp

    <!-- Financial Overview Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-pie text-primary"></i> Financial Overview
                <span class="badge bg-success ms-2">Interest-Free Agreement</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-muted mb-1">Vehicle Price</h6>
                        <h4 class="mb-0 text-dark">KSh {{ number_format($agreement->vehicle_price, 0) }}</h4>
                        <small class="text-success">No Additional Costs</small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-soft-success rounded">
                        <h6 class="text-muted mb-1">Down Payment</h6>
                        <h4 class="mb-0 text-success">KSh {{ number_format($agreement->deposit_amount, 0) }}</h4>
                        <small class="text-muted">{{ number_format(($agreement->deposit_amount / $agreement->vehicle_price) * 100, 1) }}% of price</small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-soft-info rounded">
                        <h6 class="text-muted mb-1">Amount Paid</h6>
                        <h4 class="mb-0 text-info">KSh {{ number_format($totalAmountPaid, 0) }}</h4>
                        <small class="text-muted">Deposit + Payments</small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="text-center p-3 bg-soft-danger rounded">
                        <h6 class="text-muted mb-1">Outstanding</h6>
                        <h4 class="mb-0 text-danger">KSh {{ number_format($actualOutstanding, 0) }}</h4>
                        <small class="text-success">0% Interest</small>
                    </div>
                </div>
            </div>
            
            <!-- Payment Progress -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Payment Progress</h6>
                    <span class="text-end">
                        <strong>{{ number_format($paymentProgress, 1) }}% Complete</strong>
                    </span>
                </div>
                <div class="progress" style="height: 12px;">
                    @php
                        $progressClass = 'bg-danger';
                        if($paymentProgress >= 80) $progressClass = 'bg-success';
                        elseif($paymentProgress >= 50) $progressClass = 'bg-info';
                        elseif($paymentProgress >= 25) $progressClass = 'bg-warning';
                    @endphp
                    <div class="progress-bar {{ $progressClass }}" 
                         role="progressbar" 
                         style="width: {{ $paymentProgress }}%">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">{{ $agreement->payments_made }} of {{ $agreement->duration_months }} payments made</small>
                    <small class="text-muted">KSh {{ number_format($agreement->monthly_payment, 0) }} monthly</small>
                </div>
            </div>

            <!-- Next Payment Due Alert -->
            @if($nextDueInstallment)
                @php
                    $dueDate = \Carbon\Carbon::parse($nextDueInstallment->due_date);
                    $today = \Carbon\Carbon::today();
                    $daysUntilDue = $today->diffInDays($dueDate, false);
                @endphp
                <div class="alert alert-{{ $daysUntilDue < 0 ? 'danger' : ($daysUntilDue <= 7 ? 'warning' : 'info') }} mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="alert-heading mb-1">
                                <i class="fas fa-{{ $daysUntilDue < 0 ? 'exclamation-triangle' : 'calendar-alt' }}"></i>
                                Next Payment Due
                            </h6>
                            <p class="mb-0">
                                <strong>Amount:</strong> KSh {{ number_format($nextDueInstallment->total_amount, 0) }} | 
                                <strong>Due:</strong> {{ $dueDate->format('M d, Y') }}
                                @if($daysUntilDue < 0)
                                    <span class="badge bg-danger ms-2">{{ abs($daysUntilDue) }} days overdue</span>
                                @elseif($daysUntilDue <= 7)
                                    <span class="badge bg-warning ms-2">Due in {{ $daysUntilDue }} days</span>
                                @else
                                    <span class="badge bg-info ms-2">Due in {{ $daysUntilDue }} days</span>
                                @endif
                            </p>
                        </div>
                        @if($agreement->status !== 'completed')
                            <button class="btn btn-{{ $daysUntilDue < 0 ? 'danger' : 'primary' }}" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="loanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="payment-history-tab" data-bs-toggle="tab" 
                            data-bs-target="#payment-history" type="button" role="tab">
                        <i class="fas fa-history"></i> Payment History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payment-schedule-tab" data-bs-toggle="tab" 
                            data-bs-target="#payment-schedule" type="button" role="tab">
                        <i class="fas fa-calendar-alt"></i> Payment Schedule
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vehicle-details-tab" data-bs-toggle="tab" 
                            data-bs-target="#vehicle-details" type="button" role="tab">
                        <i class="fas fa-car"></i> Vehicle Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="agreement-document-tab" data-bs-toggle="tab" 
                            data-bs-target="#agreement-document" type="button" role="tab">
                        <i class="fas fa-file-contract"></i> Agreement Document
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="legal-compliance-tab" data-bs-toggle="tab" 
                            data-bs-target="#legal-compliance" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Legal & Compliance
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content" id="loanTabsContent">
                
                <!-- Payment History Tab -->
                <div class="tab-pane fade show active" id="payment-history" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Payment History</h5>
                        @if($agreement->status !== 'completed')
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                                <i class="fas fa-plus"></i> Add Payment
                            </button>
                        @endif
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Deposit Payment -->
                                <tr class="table-success">
                                    <td>{{ \Carbon\Carbon::parse($agreement->agreement_date)->format('M d, Y') }}</td>
                                    <td><strong>KSh {{ number_format($agreement->deposit_amount, 2) }}</strong></td>
                                    <td><span class="badge bg-success">Initial Deposit</span></td>
                                    <td>-</td>
                                    <td><span class="badge bg-success">Cleared</span></td>
                                    <td>
                                     <button class="btn btn-outline-primary btn-sm" 
                                    onclick="openReceiptModal('deposit', {{ $agreement->deposit_amount }}, 'INITIAL DEPOSIT', '{{ $agreement->vehicle_registration }}', '{{ $agreement->client_name }}', 'Initial Deposit', 'Cleared', {{ $agreement->loan_amount }}, '-', '{{ $agreement->id }}', '{{ $agreement->agreement_date }}')"
                                    data-bs-toggle="tooltip" 
                                    title="Print Receipt">
                                        <i class="fas fa-print me-1"></i>
                                    </button>
                                    </td>
                                </tr>
                                
                                @forelse($agreement->payments as $payment)
                                    <tr>
                                        <td>{{ isset($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : \Carbon\Carbon::parse($payment->created_at)->format('M d, Y') }}</td>
                                        <td><strong>KSh {{ number_format($payment->amount, 2) }}</strong></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Not Specified')) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->reference_number ?? $payment->payment_reference ?? '-' }}</td>
                                        <td>
                                            @if(isset($payment->is_verified) && $payment->is_verified)
                                                <span class="badge bg-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                   <button class="btn btn-outline-primary" 
                                            onclick="openReceiptModal('payment', {{ $payment->amount }}, 'MONTHLY PAYMENT', '{{ $agreement->vehicle_registration }}', '{{ $agreement->client_name }}', '{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Not Specified')) }}', '{{ isset($payment->is_verified) && $payment->is_verified ? 'Verified' : 'Pending' }}', {{ $payment->balance_after ?? 0 }}, '{{ $payment->reference_number ?? $payment->payment_reference ?? '-' }}', '{{ $agreement->id }}', '{{ isset($payment->payment_date) ? $payment->payment_date : $payment->created_at }}')"
                                            data-bs-toggle="tooltip" 
                                            title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                                @if(!isset($payment->is_verified) || !$payment->is_verified)
                                                    <button class="btn btn-outline-success" onclick="verifyPayment({{ $payment->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No payments recorded yet</h6>
                                            <p class="text-muted">Payment history will appear here once payments are made.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Schedule Tab -->
                <div class="tab-pane fade" id="payment-schedule" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Payment Schedule</h5>
                        <div>
                            <span class="badge bg-info me-2">Monthly: KSh {{ number_format($agreement->monthly_payment, 0) }}</span>
                            <span class="badge bg-secondary me-2">{{ $agreement->duration_months }} Months</span>
                            <span class="badge bg-success">0% Interest</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Payment Amount</th>
                                    <th>Balance After</th>
                                    <th>Status</th>
                                    <th>Amount Paid</th>
                                    <th>Days Overdue</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($agreement->paymentSchedule && $agreement->paymentSchedule->count() > 0)
                                @php
                                    $totalScheduled = 0;
                                    $totalPaid = 0;
                                    $totalPending = 0;
                                @endphp
                                @foreach($agreement->paymentSchedule as $schedule)
                                        @php
                                            $isOverdue = $schedule->status === 'overdue' || ($schedule->days_overdue > 0);
                                            $isPaid = $schedule->status === 'paid';
                                            $isPartial = $schedule->status === 'partial';
                                            
                                            // Accumulate totals (no interest, only principal)
                                            $totalScheduled += $schedule->total_amount;
                                            
                                            if ($isPaid) {
                                                $totalPaid += $schedule->total_amount;
                                            } elseif ($isPartial) {
                                                $totalPaid += $schedule->amount_paid;
                                                $totalPending += ($schedule->total_amount - $schedule->amount_paid);
                                            } else {
                                                $totalPending += $schedule->total_amount;
                                            }
                                        @endphp
                                        <tr class="{{ $isOverdue ? 'table-danger' : ($isPaid ? 'table-success' : ($isPartial ? 'table-warning' : '')) }}">
                                            <td>{{ $schedule->installment_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->due_date)->format('M d, Y') }}</td>
                                            <td><strong>KSh {{ number_format($schedule->total_amount, 2) }}</strong></td>
                                            <td>KSh {{ number_format($schedule->balance_after, 2) }}</td>
                                            <td>
                                                @switch($schedule->status)
                                                    @case('paid')
                                                        <span class="badge bg-success">Paid</span>
                                                        @break
                                                    @case('overdue')
                                                        <span class="badge bg-danger">Overdue</span>
                                                        @break
                                                    @case('partial')
                                                        <span class="badge bg-warning">Partial</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">Pending</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($schedule->amount_paid > 0)
                                                    KSh {{ number_format($schedule->amount_paid, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($schedule->days_overdue > 0)
                                                    <span class="badge bg-danger">{{ $schedule->days_overdue }} days</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$isPaid && $agreement->status !== 'completed')
                                                    <button class="btn btn-sm btn-primary" 
                                                            onclick="quickPayment({{ $schedule->total_amount }}, '{{ $schedule->due_date }}', {{ $schedule->installment_number }})"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#recordPaymentModal">
                                                        <i class="fas fa-credit-card"></i> Pay
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach 
                                    
                                    <!-- Summary Totals Row -->
                                    <tr class="table-active fw-bold">
                                        <td colspan="2" class="text-end">TOTALS:</td>
                                        <td>KSh {{ number_format($totalScheduled, 2) }}</td>
                                        <td>-</td>
                                        <td colspan="2">
                                            <span class="text-success">Paid: KSh {{ number_format($totalPaid, 2) }}</span><br>
                                            <span class="text-danger">Pending: KSh {{ number_format($totalPending, 2) }}</span>
                                        </td>
                                        <td colspan="2">-</td>
                                    </tr>

                                @else
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No payment schedule available</h6>
                                            <p class="text-muted">Payment schedule will be generated automatically.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Vehicle Details Tab -->
                <div class="tab-pane fade" id="vehicle-details" role="tabpanel">
                    <h5 class="mb-4">Vehicle Information</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Basic Details</h6>
                                </div>
                                <div class="card-body">
                                    @if($agreement->customerVehicle)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Make:</strong></td>
                                                <td>{{ $agreement->customerVehicle->vehicle_make ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Model:</strong></td>
                                                <td>{{ $agreement->customerVehicle->model ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Year:</strong></td>
                                                <td>{{ $agreement->customerVehicle->year ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Plate Number:</strong></td>
                                                <td>{{ $agreement->customerVehicle->number_plate ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Chassis Number:</strong></td>
                                                <td>{{ $agreement->customerVehicle->chasis_no ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Vehicle Type:</strong></td>
                                                <td>
                                                    <span class="badge bg-success">Customer Vehicle</span>
                                                </td>
                                            </tr>
                                        </table>
                                    @elseif($agreement->carImport)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Make:</strong></td>
                                                <td>{{ $agreement->carImport->make ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Model:</strong></td>
                                                <td>{{ $agreement->carImport->model ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Year:</strong></td>
                                                <td>{{ $agreement->carImport->year ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Plate Number:</strong></td>
                                                <td>{{ $agreement->carImport->plate_number ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Chassis Number:</strong></td>
                                                <td>{{ $agreement->carImport->chassis_number ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Vehicle Type:</strong></td>
                                                <td>
                                                    <span class="badge bg-primary">Imported Vehicle</span>
                                                </td>
                                            </tr>
                                        </table>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Vehicle details not available.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Financial Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Vehicle Price:</strong></td>
                                            <td>KSh {{ number_format($agreement->vehicle_price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Down Payment:</strong></td>
                                            <td>KSh {{ number_format($agreement->deposit_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Balance to Pay:</strong></td>
                                            <td>KSh {{ number_format($agreement->loan_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Interest Rate:</strong></td>
                                            <td><span class="badge bg-success">0% (Interest-Free)</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Monthly Payment:</strong></td>
                                            <td>KSh {{ number_format($agreement->monthly_payment, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Interest:</strong></td>
                                            <td><span class="text-success">KSh 0.00 (No Interest)</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Payable:</strong></td>
                                            <td>KSh {{ number_format($agreement->vehicle_price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Amount Paid:</strong></td>
                                            <td>KSh {{ number_format($totalAmountPaid, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Outstanding Balance:</strong></td>
                                            <td>KSh {{ number_format($actualOutstanding, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payments Remaining:</strong></td>
                                            <td>{{ $agreement->payments_remaining }} months</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agreement Document Tab -->
                <div class="tab-pane fade" id="agreement-document" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Gentleman's Agreement Document</h5>
                        <div>
                                         <button type="button" class="btn btn-primary btn-sm agreementBtn" 
                                                            data-cash-id="{{ $agreement->id }}"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#agreementModal{{ $agreement->id }}">
                                                        <i class="fas fa-file-contract me-1"></i> Agreement
                                                    </button>
           <!-- Professional Agreement Modal -->
<div class="modal fade" id="agreementModal{{ $agreement->id }}" tabindex="-1" aria-labelledby="agreementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="agreementModalLabel">
                    <i class="fas fa-file-contract me-2"></i>Gentleman Sales Agreement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Upload Section -->
                <div id="uploadSection{{ $agreement->id }}" class="mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Agreement PDF</h6>
                        </div>
                        <div class="card-body">
                            <form id="agreementUploadForm{{ $agreement->id }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                                <input type="hidden" name="agreement_type" value="Gentleman">
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <label for="agreement_file{{ $agreement->id }}" class="form-label">
                                            <i class="fas fa-file-pdf me-1"></i>Select PDF File
                                        </label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="agreement_file{{ $agreement->id }}" 
                                               name="agreement_file" 
                                               accept=".pdf" 
                                               required>
                                        <div class="form-text">Maximum file size: 1GB. Only PDF files are allowed.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" 
                                                class="btn btn-primary w-100" 
                                                id="uploadBtn{{ $agreement->id }}">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Progress Bar -->
                            <div class="progress mt-3 d-none" id="uploadProgress{{ $agreement->id }}">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 0%"></div>
                            </div>
                            
                            <!-- Upload Status -->
                            <div id="uploadStatus{{ $agreement->id }}" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Agreement Management Section (When PDF exists) -->
                <div id="agreementManagement{{ $agreement->id }}" class="mb-4" style="display: none;">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-file-check me-2"></i>Agreement Uploaded</h6>
                            <button type="button" 
                                    class="btn btn-outline-light btn-sm" 
                                    id="deleteAgreementBtn{{ $agreement->id }}"
                                    title="Delete Agreement">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p class="mb-0"><strong>Actions:</strong></p>
                                </div>
                                <div class="col-md-4">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openPDFNewTab{{ $agreement->id }}()">
                                            <i class="fas fa-external-link-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="downloadPDF{{ $agreement->id }}()">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="printPDF{{ $agreement->id }}()">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Display Section -->
                <div id="agreementContent{{ $agreement->id }}" style="min-height: 600px;">
                    <div class="text-center py-5" id="emptyState{{ $agreement->id }}">
                        <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No agreement uploaded yet. Please upload a PDF file above.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" id="replaceBtn{{ $agreement->id }}" style="display: none;" onclick="showUploadSection{{ $agreement->id }}()">
                    <i class="fas fa-sync-alt me-1"></i>Replace PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal{{ $agreement->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this agreement? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn{{ $agreement->id }}">
                    <i class="fas fa-trash-alt me-1"></i>Delete Agreement
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.pdf-viewer-container {
    width: 100%;
    height: 600px;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.pdf-embed {
    width: 100%;
    height: 100%;
    border: none;
    border-radius: 0.375rem;
}

.pdf-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
}

.pdf-error-state {
    height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.upload-dropzone {
    border: 2px dashed #0d6efd;
    border-radius: 0.375rem;
    padding: 2rem;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.upload-dropzone:hover {
    background: #e7f3ff;
    border-color: #0b5ed7;
}

.upload-dropzone.dragover {
    background: #cfe2ff;
    border-color: #0a58ca;
}
</style>

<script>
$(document).ready(function() {
    const agreementId = {{ $agreement->id }};
    let currentPdfUrl = null;
    
    // Check if agreement already exists when modal opens
    $('#agreementModal' + agreementId).on('shown.bs.modal', function() {
        checkExistingAgreement(agreementId);
    });
    
    // File upload form submission
    $('#agreementUploadForm' + agreementId).on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = $('#uploadBtn' + agreementId);
        const uploadProgress = $('#uploadProgress' + agreementId);
        const uploadStatus = $('#uploadStatus' + agreementId);
        
        // Reset status
        uploadStatus.empty();
        uploadProgress.removeClass('d-none');
        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Uploading...');
        
        $.ajax({
            url: '{{ route("agreement.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5 minutes timeout for large files
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        uploadProgress.find('.progress-bar').css('width', percentComplete + '%');
                        
                        // Show file size progress for large files
                        const loaded = (evt.loaded / (1024 * 1024 * 1024)).toFixed(2);
                        const total = (evt.total / (1024 * 1024 * 1024)).toFixed(2);
                        uploadProgress.find('.progress-bar').text(`${loaded}GB / ${total}GB (${percentComplete}%)`);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                uploadStatus.html(`
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Agreement uploaded successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                
                // Store PDF URL
                currentPdfUrl = response.pdfUrl;
                
                // Display the uploaded PDF
                displayPDF(currentPdfUrl, agreementId);
                
                // Show management section and hide upload section
                showAgreementManagement(agreementId);
                
                // Reset form
                $('#agreementUploadForm' + agreementId)[0].reset();
            },
            error: function(xhr) {
                let errorMessage = 'Upload failed. Please try again.';
                
                if (xhr.status === 413) {
                    errorMessage = 'File is too large. Maximum allowed size is 1GB.';
                } else if (xhr.status === 408 || xhr.statusText === 'timeout') {
                    errorMessage = 'Upload timed out. Please try again with a smaller file or check your internet connection.';
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                uploadStatus.html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            },
            complete: function() {
                uploadProgress.addClass('d-none');
                uploadBtn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Upload');
            }
        });
    });
    
    // Delete agreement functionality
    $('#deleteAgreementBtn' + agreementId).on('click', function() {
        $('#deleteConfirmModal' + agreementId).modal('show');
    });
    
    $('#confirmDeleteBtn' + agreementId).on('click', function() {
        deleteAgreement(agreementId);
    });
    
    // File input validation
    $('#agreement_file' + agreementId).on('change', function() {
        const file = this.files[0];
        const uploadStatus = $('#uploadStatus' + agreementId);
        
        if (file) {
            if (file.type !== 'application/pdf') {
                uploadStatus.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Please select a PDF file only.
                    </div>
                `);
                this.value = '';
                return;
            }
            
            // Check for 1GB limit (1073741824 bytes)
            if (file.size > 1073741824) {
                const fileSize = (file.size / (1024 * 1024 * 1024)).toFixed(2);
                uploadStatus.html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>File size (${fileSize}GB) exceeds the maximum limit of 1GB.
                    </div>
                `);
                this.value = '';
                return;
            }
            
            // Show file size info for large files
            const fileSize = file.size / (1024 * 1024);
            if (fileSize > 100) { // Show size info for files larger than 100MB
                const sizeText = fileSize > 1024 ? 
                    `${(fileSize / 1024).toFixed(2)}GB` : 
                    `${fileSize.toFixed(2)}MB`;
                
                uploadStatus.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Large file selected (${sizeText}). Upload may take several minutes.
                    </div>
                `);
            } else {
                uploadStatus.empty();
            }
        }
    });
});

// Check if agreement already exists
 function checkExistingAgreement(agreementId) {
    const agreementType = 'HirePurchase'; // or get this dynamically
    
    $.get('{{ url("/agreements") }}/' + agreementId + '/' + agreementType)
        .done(function(data, status, xhr) {
            const pdfUrl = '{{ url("/agreements") }}/' + agreementId + '/' + agreementType;
            currentPdfUrl = pdfUrl;
            displayPDF(pdfUrl, agreementId);
            showAgreementManagement(agreementId);
        })
        .fail(function() {
            showUploadSection(agreementId);
        });
}

// Display PDF with multiple fallback methods
function displayPDF(pdfUrl, agreementId) {
    $('#emptyState' + agreementId).hide();
    
    const content = `
        <div class="text-center mb-3">
            <h6 class="text-primary">
                <i class="fas fa-file-pdf me-2"></i>Agreement Document
            </h6>
        </div>
        <div class="pdf-viewer-container" id="pdfContainer${agreementId}">
            <div class="pdf-loading-overlay" id="pdfLoading${agreementId}">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading PDF...</p>
                    <p class="small text-muted">Large files may take longer to load</p>
                </div>
            </div>
        </div>
    `;
    
    $('#agreementContent' + agreementId).html(content);
    
    // Try different methods to display PDF
    tryDisplayMethods(pdfUrl, agreementId);
}

function tryDisplayMethods(pdfUrl, agreementId) {
    const container = $('#pdfContainer' + agreementId);
    const loading = $('#pdfLoading' + agreementId);
    
    // Method 1: Try direct embed
    const embed = `<embed src="${pdfUrl}#view=FitH" type="application/pdf" class="pdf-embed" id="pdfEmbed${agreementId}">`;
    container.append(embed);
    
    // Check if embed loaded after 5 seconds (longer for large files)
    setTimeout(() => {
        const embedElement = $('#pdfEmbed' + agreementId)[0];
        
        if (!embedElement || embedElement.clientHeight === 0) {
            // Method 2: Try Google Docs Viewer
            container.html(`
                <iframe src="https://docs.google.com/viewer?url=${encodeURIComponent(pdfUrl)}&embedded=true" 
                        class="pdf-embed" 
                        id="pdfIframe${agreementId}">
                </iframe>
            `);
            
            // Check if Google Docs Viewer loaded
            setTimeout(() => {
                const iframe = $('#pdfIframe' + agreementId)[0];
                if (!iframe || iframe.clientHeight === 0) {
                    // Method 3: Fallback with manual controls
                    showPDFError(pdfUrl, agreementId);
                } else {
                    loading.hide();
                }
            }, 5000);
        } else {
            loading.hide();
        }
    }, 5000);
}

function showPDFError(pdfUrl, agreementId) {
    const container = $('#pdfContainer' + agreementId);
    container.html(`
        <div class="pdf-error-state">
            <div class="text-center">
                <i class="fas fa-file-pdf fa-4x text-muted mb-3"></i>
                <h5>PDF Preview Not Available</h5>
                <p class="text-muted mb-2">Your browser doesn't support embedded PDF viewing.</p>
                <p class="text-muted mb-4 small">Large PDF files may not display properly in the browser.</p>
                <div class="btn-group" role="group">
                    <a href="${pdfUrl}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Open in New Tab
                    </a>
                    <a href="${pdfUrl}" download class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
    `);
}

// Show/hide sections
function showAgreementManagement(agreementId) {
    $('#uploadSection' + agreementId).slideUp();
    $('#agreementManagement' + agreementId).slideDown();
    $('#replaceBtn' + agreementId).show();
}

function showUploadSection(agreementId) {
    $('#agreementManagement' + agreementId).slideUp();
    $('#uploadSection' + agreementId).slideDown();
    $('#replaceBtn' + agreementId).hide();
    $('#agreementContent' + agreementId).html(`
        <div class="text-center py-5" id="emptyState${agreementId}">
            <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
            <p class="text-muted">No agreement uploaded yet. Please upload a PDF file above.</p>
        </div>
    `);
}

// Delete agreement
function deleteAgreement(agreementId) {
    const deleteBtn = $('#confirmDeleteBtn' + agreementId);
    deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Deleting...');
    
    $.ajax({
        url: '{{ url("/agreements") }}/' + agreementId,
        type: 'DELETE',
        data: {
            '_token': '{{ csrf_token() }}'
        },
        success: function(response) {
            $('#deleteConfirmModal' + agreementId).modal('hide');
            showUploadSection(agreementId);
            
            // Show success message
            $('#uploadStatus' + agreementId).html(`
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Agreement deleted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        },
        error: function(xhr) {
            $('#deleteConfirmModal' + agreementId).modal('hide');
            
            $('#uploadStatus' + agreementId).html(`
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>Failed to delete agreement. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        },
        complete: function() {
            deleteBtn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i>Delete Agreement');
        }
    });
}

// PDF action functions
window['openPDFNewTab' + {{ $agreement->id }}] = function() {
    if (currentPdfUrl) {
        window.open(currentPdfUrl, '_blank');
    }
};

window['downloadPDF' + {{ $agreement->id }}] = function() {
    if (currentPdfUrl) {
        const link = document.createElement('a');
        link.href = currentPdfUrl;
        link.download = 'agreement-{{ $agreement->id }}.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

window['printPDF' + {{ $agreement->id }}] = function() {
    if (currentPdfUrl) {
        const printWindow = window.open(currentPdfUrl, '_blank');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        });
    }
};

window['showUploadSection' + {{ $agreement->id }}] = function() {
    showUploadSection({{ $agreement->id }});
};
</script>
                        </div>
                    </div>
                  
                </div>

                <!-- Legal & Compliance Tab -->
                <div class="tab-pane fade" id="legal-compliance" role="tabpanel">
                    <h5 class="mb-4">Legal & Compliance Information</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Client Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Full Name:</strong></td>
                                            <td>{{ $agreement->client_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $agreement->phone_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $agreement->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>National ID:</strong></td>
                                            <td>{{ $agreement->national_id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>KRA PIN:</strong></td>
                                            <td>{{ $agreement->kra_pin ?? 'N/A' }}</td>
                                        </tr>
                                        @if($agreement->address)
                                            <tr>
                                                <td><strong>Address:</strong></td>
                                                <td>{{ $agreement->address }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Agreement Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Agreement ID:</strong></td>
                                            <td>GA-{{ str_pad($agreement->id, 6, '0', STR_PAD_LEFT) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Agreement Date:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($agreement->agreement_date)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>First Due Date:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($agreement->first_due_date)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Expected Completion:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($agreement->expected_completion_date)->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $currentStatus['class'] }}">
                                                    {{ $currentStatus['text'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Agreement Type:</strong></td>
                                            <td><span class="badge bg-success">Gentleman's Agreement</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Interest Rate:</strong></td>
                                            <td><span class="badge bg-success">0% (Interest-Free)</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-4">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-primary" onclick="printAgreement()">
                                <i class="fas fa-print"></i> Print Agreement
                            </button>
                            <button class="btn btn-outline-info" onclick="downloadPDF()">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </button>
                            <button class="btn btn-outline-secondary" onclick="sendCopy()">
                                <i class="fas fa-envelope"></i> Email Copy
                            </button>
                            @if($agreement->status === 'pending')
                                <button class="btn btn-success" onclick="approveAgreement({{ $agreement->id }})">
                                    <i class="fas fa-check"></i> Approve Agreement
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <!-- Receipt Download Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="receiptModalLabel">
                    <i class="fas fa-receipt me-2"></i>Payment Receipt
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="receiptContent">
                <!-- Receipt Content -->
                <div class="receipt-container" style="background: white; padding: 25px; font-family: 'Arial', sans-serif; width: 100%; max-width: 580px; margin: 0 auto; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <!-- Header Section -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #007bff;">
                        <!-- Company Details -->
                        <div style="flex: 1; padding-right: 20px;">
                            <h2 style="font-size: 18px; font-weight: bold; margin: 0 0 8px 0; color: #2c3e50;">Kelmer's House of Cars LTD</h2>
                            <div style="font-size: 12px; line-height: 1.4; color: #555;">
                                <div style="margin-bottom: 2px;">Jabavu Lane, Huringham</div>
                                <div style="margin-bottom: 2px;">P.O Box 9215 - 00100, Nairobi - Kenya</div>
                                <div style="margin: 4px 0 2px 0;"><strong>Email:</strong> info@kelmercars.co.ke</div>
                                <div><strong>Phone:</strong> +254 700 000 000</div>
                            </div>
                        </div>
                        
                        <!-- Logo -->
                        <div style="flex: 0 0 auto; text-align: center;">
                            <img src="{{ asset('dashboardv1/assets/images/houseofcars.png') }}" alt="Kelmer's House of Cars" style="height: 70px; width: auto;">
                        </div>
                    </div>

                    <!-- Receipt Title and Number -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="font-size: 32px; font-weight: bold; margin: 0 0 10px 0; letter-spacing: 4px; color: #2c3e50;">RECEIPT</h1>
                        <div style="background: #f8f9fa; padding: 6px 15px; border-radius: 4px; display: inline-block; border: 1px solid #dee2e6;">
                            <span style="font-size: 14px; font-weight: bold; color: #495057;">Receipt No: </span>
                            <span id="receiptNumber" style="font-size: 16px; font-weight: bold; color: #007bff;"></span>
                        </div>
                    </div>

                    <!-- Date and Customer Info -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 18px; border-left: 4px solid #007bff;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px;">
                            <div>
                                <span style="font-weight: bold; color: #495057;">Date: </span>
                                <span id="receiptDate" style="color: #6c757d;"></span>
                            </div>
                            <div>
                                <span style="font-weight: bold; color: #495057;">Time: </span>
                                <span id="receiptTime" style="color: #6c757d;"></span>
                            </div>
                        </div>
                        
                        <div style="font-size: 14px;">
                            <span style="font-weight: bold; color: #495057;">Received from: </span>
                            <span id="customerName" style="font-weight: bold; text-transform: uppercase; color: #2c3e50;"></span>
                        </div>
                    </div>

                    <!-- Payment Details Section -->
                    <div style="border: 2px solid #007bff; padding: 18px; margin-bottom: 18px; border-radius: 6px; background: #fff;">
                        <h3 style="margin: 0 0 15px 0; color: #007bff; font-size: 16px; text-align: center; font-weight: bold;">PAYMENT DETAILS</h3>
                        
                        <div style="margin-bottom: 15px; font-size: 13px;">
                            <span style="font-weight: bold; color: #495057;">Being payment of: </span>
                            <span id="paymentDescription" style="font-weight: bold; color: #28a745;"></span>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                            <div style="margin-bottom: 10px; font-size: 13px;">
                                <span style="font-weight: bold; color: #495057;">Amount (Digital): </span>
                                <span id="paymentAmount" style="font-size: 18px; font-weight: bold; color: #007bff;"></span>
                            </div>
                            <div style="font-size: 12px;">
                                <span style="font-weight: bold; color: #495057;">Amount (In Words): </span>
                                <span id="paymentAmountWords" style="font-style: italic; color: #6c757d; text-transform: capitalize;"></span>
                            </div>
                        </div>
                        
                        <div style="font-size: 13px;">
                            <span style="font-weight: bold; color: #495057;">Vehicle Registration: </span>
                            <span id="vehicleReg" style="font-weight: bold; color: #2c3e50; background: #fff3cd; padding: 3px 6px; border-radius: 3px;"></span>
                        </div>
                    </div>

                    <!-- Payment Method and Reference -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 18px;">
                        <div style="background: #e3f2fd; padding: 12px; border-radius: 6px; border-left: 3px solid #2196f3;">
                            <div style="font-weight: bold; color: #1976d2; margin-bottom: 4px; font-size: 12px;">Payment Method</div>
                            <div id="paymentMethod" style="font-size: 14px; font-weight: bold; color: #2c3e50;"></div>
                        </div>
                        <div style="background: #f3e5f5; padding: 12px; border-radius: 6px; border-left: 3px solid #9c27b0;">
                            <div style="font-weight: bold; color: #7b1fa2; margin-bottom: 4px; font-size: 12px;">Reference Number</div>
                            <div id="referenceNumber" style="font-size: 12px; font-weight: bold; color: #2c3e50; font-family: 'Courier New', monospace;"></div>
                        </div>
                    </div>

                    <!-- Balance Information -->
                    <div style="background: #e8f5e8; border: 2px solid #28a745; padding: 15px; border-radius: 6px; margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0; color: #155724; font-weight: bold; font-size: 14px;">Outstanding Balance</h4>
                                <p style="margin: 3px 0 0 0; color: #155724; font-size: 11px;">Remaining amount after this payment</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 20px; font-weight: bold; color: #155724;">
                                    KSh <span> {{ number_format($actualOutstanding, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thank You Section -->
                    <div style="text-align: center; margin-bottom: 18px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px;">
                        <h3 style="margin: 0 0 6px 0; color: #155724; font-weight: bold; font-size: 16px;">WITH THANKS</h3>
                        <p style="margin: 0; color: #155724; font-size: 12px;">We appreciate your business and prompt payment</p>
                    </div>

                    <!-- Terms Section -->
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
                        <div style="font-weight: bold; color: #856404; font-size: 12px;">
                            "Money once received is not refundable but transferable"
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
                        <div style="text-align: center;">
                            <div style="height: 50px; border-bottom: 2px solid #495057; margin-bottom: 8px;"></div>
                            <div style="font-size: 12px; font-weight: bold; color: #495057;">Customer Signature</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="height: 50px; border-bottom: 2px solid #495057; margin-bottom: 8px;"></div>
                            <div style="font-size: 12px; font-weight: bold; color: #495057;">For Kelmer's House of Cars</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="text-align: center; padding: 12px; background: #f8f9fa; border-radius: 6px; border-top: 2px solid #007bff;">
                        <div style="font-size: 11px; color: #6c757d; margin-bottom: 3px;" id="generatedDateTime"></div>
                        <div style="font-size: 10px; color: #6c757d; font-style: italic;">
                            Official Receipt from Kelmer's House of Cars Limited
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-success" onclick="downloadReceipt()">
                    <i class="fas fa-download me-2"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    @csrf
                    <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                    
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <span><strong>Suggested Payment:</strong></span>
                            <span><strong>KSh {{ number_format($agreement->monthly_payment, 2) }}</strong></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Outstanding Balance:</span>
                            <span><strong>KSh {{ number_format($actualOutstanding, 2) }}</strong></span>
                        </div>
                        @if($nextDueInstallment)
                            <div class="mt-2 pt-2 border-top">
                                <small><strong>Next Due:</strong> {{ \Carbon\Carbon::parse($nextDueInstallment->due_date)->format('M d, Y') }} 
                                - KSh {{ number_format($nextDueInstallment->total_amount, 2) }}</small>
                            </div>
                        @endif
                        <div class="mt-2">
                            <span class="badge bg-success">No Interest • No Additional Fees</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Amount (KSh) *</label>
                        <input type="number" 
                               class="form-control" 
                               name="payment_amount" 
                               id="paymentAmount"
                               value="{{ $agreement->monthly_payment }}" 
                               required 
                               min="1" 
                               max="{{ $actualOutstanding }}"
                               step="0.01">
                        <small class="text-muted">Maximum: KSh {{ number_format($actualOutstanding, 2) }}</small>
                        
                        <!-- Quick Amount Buttons -->
                        <div class="mt-2">
                            <small class="text-muted d-block mb-1">Quick amounts:</small>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="setQuickAmount({{ $agreement->monthly_payment }})">
                                    Monthly ({{ number_format($agreement->monthly_payment, 0) }})
                                </button>
                                @if($actualOutstanding > $agreement->monthly_payment * 2)
                                <button type="button" class="btn btn-outline-info" onclick="setQuickAmount({{ $agreement->monthly_payment * 2 }})">
                                    2 Months ({{ number_format($agreement->monthly_payment * 2, 0) }})
                                </button>
                                @endif
                                @if($actualOutstanding > $agreement->monthly_payment * 3)
                                <button type="button" class="btn btn-outline-warning" onclick="setQuickAmount({{ $agreement->monthly_payment * 3 }})">
                                    3 Months ({{ number_format($agreement->monthly_payment * 3, 0) }})
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline-success" onclick="setQuickAmount({{ $actualOutstanding }})">
                                    Full Payment
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method *</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card Payment</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" class="form-control" name="payment_reference" placeholder="Transaction/Receipt Number">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="payment_notes" rows="2" placeholder="Additional notes about this payment"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save"></i> Record Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadReceiptDirect() {
    try {
        const receiptContent = document.getElementById('receiptContent');
        
        if (!receiptContent) {
            alert('Receipt content not found!');
            return;
        }

        // Get customer name for filename
        const customerNameElement = document.getElementById('customerName');
        const customerName = customerNameElement ? customerNameElement.textContent.trim() : 'Customer';
        const fileName = customerName.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') || 'Receipt';

        // Create HTML content with existing receipt
        const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <title>${fileName} Receipt</title>
    <meta charset="UTF-8">
    <style>
        body { 
            margin: 20px; 
            font-family: Arial, sans-serif; 
            background: white;
        }
        @media print {
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    ${receiptContent.innerHTML}
</body>
</html>`;

        // Create and download as HTML file
        const blob = new Blob([htmlContent], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        
        const downloadLink = document.createElement('a');
        downloadLink.href = url;
        downloadLink.download = `${fileName}_Receipt.html`;
        downloadLink.style.display = 'none';
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        URL.revokeObjectURL(url);
        
    } catch (error) {
        console.error('Error downloading receipt:', error);
        alert('Error downloading receipt. Please try again.');
    }
}
// Function to convert numbers to words
function numberToWords(num) {
    if (num === 0) return "zero";
    
    const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
    const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    const teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    
    function convertHundreds(n) {
        let result = '';
        
        if (n >= 100) {
            result += ones[Math.floor(n / 100)] + ' hundred';
            n %= 100;
            if (n > 0) result += ' and ';
        }
        
        if (n >= 20) {
            result += tens[Math.floor(n / 10)];
            n %= 10;
            if (n > 0) result += '-' + ones[n];
        } else if (n >= 10) {
            result += teens[n - 10];
        } else if (n > 0) {
            result += ones[n];
        }
        
        return result;
    }
    
    if (num < 0) {
        return 'negative ' + numberToWords(-num);
    }
    
    if (num < 1000) {
        return convertHundreds(num);
    }
    
    if (num < 1000000) {
        const thousands = Math.floor(num / 1000);
        const remainder = num % 1000;
        let result = convertHundreds(thousands) + ' thousand';
        if (remainder > 0) {
            result += ' ' + convertHundreds(remainder);
        }
        return result;
    }
    
    if (num < 1000000000) {
        const millions = Math.floor(num / 1000000);
        const remainder = num % 1000000;
        let result = convertHundreds(millions) + ' million';
        if (remainder > 0) {
            result += ' ' + numberToWords(remainder);
        }
        return result;
    }
    
    if (num < 1000000000000) {
        const billions = Math.floor(num / 1000000000);
        const remainder = num % 1000000000;
        let result = convertHundreds(billions) + ' billion';
        if (remainder > 0) {
            result += ' ' + numberToWords(remainder);
        }
        return result;
    }
    
    return 'number too large';
}

// Function to convert currency amount to words
function amountToWords(amount) {
    // Handle decimal amounts (cents)
    const parts = amount.toString().split('.');
    const wholePart = parseInt(parts[0]) || 0;
    const decimalPart = parts[1] ? parseInt(parts[1].padEnd(2, '0').slice(0, 2)) : 0;
    
    let result = '';
    
    if (wholePart > 0) {
        result += numberToWords(wholePart);
        result += wholePart === 1 ? ' shilling' : ' shillings';
    }
    
    if (decimalPart > 0) {
        if (wholePart > 0) result += ' and ';
        result += numberToWords(decimalPart);
        result += decimalPart === 1 ? ' cent' : ' cents';
    }
    
    if (wholePart === 0 && decimalPart === 0) {
        result = 'zero shillings';
    }
    
    return result + ' only';
}

function openReceiptModal(type, amount, description, vehicleReg, customerName, paymentMethod, status, reference, agreementId, paymentDate) {
    try {
        console.log('Opening receipt modal with data:', {type, amount, description, vehicleReg, customerName});
        
        // Update modal content with dynamic data
        document.getElementById('paymentAmount').textContent = new Intl.NumberFormat().format(amount);
        
        // Convert amount to words and update the words field
        const amountInWords = amountToWords(amount);
        document.getElementById('paymentAmountWords').textContent = amountInWords;
        
        document.getElementById('paymentDescription').textContent = description;
        document.getElementById('vehicleReg').textContent = vehicleReg;
        document.getElementById('customerName').textContent = customerName;
        document.getElementById('paymentMethod').textContent = paymentMethod;
        document.getElementById('referenceNumber').textContent = reference;
        
        // Set date and time - use payment date if provided, otherwise current date
        const receiptDate = paymentDate ? new Date(paymentDate) : new Date();
        document.getElementById('receiptDate').textContent = receiptDate.toLocaleDateString('en-GB');
        
        // Update generated date time
        const currentDateTime = new Date();
        document.getElementById('generatedDateTime').textContent = 
            `Generated on ${currentDateTime.toLocaleDateString('en-GB')} at ${currentDateTime.toLocaleTimeString('en-GB')} | Thank you for your business!`;
        
        // Generate dynamic receipt number based on agreement ID and timestamp
        const receiptNumber = `${Math.floor(Math.random() * 900) + 100}`;
        document.getElementById('receiptNumber').textContent = receiptNumber;
        
        // Show modal using jQuery if Bootstrap 4, or Bootstrap 5 method
        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            $('#receiptModal').modal('show');
        } else if (typeof bootstrap !== 'undefined') {
            var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
            receiptModal.show();
        } else {
            // Fallback - show modal manually
            document.getElementById('receiptModal').style.display = 'block';
            document.getElementById('receiptModal').classList.add('show');
        }
        
    } catch (error) {
        console.error('Error opening receipt modal:', error);
        alert('Error opening receipt. Please try again.');
    }
}

function downloadReceipt() {
    // Get the customer name from the receipt
    var customerNameElement = document.getElementById('customerName');
    var customerName = customerNameElement ? customerNameElement.textContent.trim() : 'Receipt';
    
    // Clean the customer name for filename (remove special characters)
    var fileName = customerName.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') || 'Receipt';
    
    // Try multiple selectors to find the receipt content
    var receiptContainer = document.querySelector('.receipt-container') || 
                          document.querySelector('#receiptContent') ||
                          document.querySelector('.modal-body');
    
    if (!receiptContainer) {
        alert('Receipt content not found');
        return;
    }
    
    var printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${fileName}_Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                @page { size: A4; margin: 1cm; }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            ${receiptContainer.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
}

function verifyPayment(paymentId) {
    if (confirm('Are you sure you want to verify this payment?')) {
        // Add your verification logic here
        // You can make an AJAX call to verify the payment
        console.log('Verifying payment ID:', paymentId);
        
        // Example AJAX call (uncomment and modify as needed):
        /*
        fetch(`/payments/${paymentId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated status
            }
        });
        */
    }
}

// Initialize Bootstrap tooltips and modals when document loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing components...');
    
    // Initialize Bootstrap tooltips
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        console.log('Bootstrap 5 tooltips initialized');
    } else if (typeof jQuery !== 'undefined' && jQuery.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
        console.log('Bootstrap 4/jQuery tooltips initialized');
    }
    
    // Test modal availability
    const modal = document.getElementById('receiptModal');
    if (modal) {
        console.log('Receipt modal found in DOM');
    } else {
        console.error('Receipt modal not found in DOM');
    }
});
</script>
<script>
// CSRF token setup for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Set quick payment amounts
function setQuickAmount(amount) {
    document.getElementById('paymentAmount').value = amount;
}

// Quick payment function for payment schedule
function quickPayment(amount, dueDate, installmentNumber) {
    document.getElementById('paymentAmount').value = amount;
    document.querySelector('input[name="payment_notes"]').value = `Payment for installment #${installmentNumber} due on ${dueDate}`;
}

function printReceipt(paymentId) {
    if (paymentId === 'deposit') {
        console.log('Printing deposit receipt...');
        // Add your print receipt logic here
    } else {
        console.log('Printing payment receipt for payment ID:', paymentId);
        // Add your print receipt logic here
    }
}

function verifyPayment(paymentId) {
    Swal.fire({
        title: 'Verify Payment',
        text: 'Are you sure you want to verify this payment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, verify it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Verifying Payment...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/gentlemanagreement/payments/${paymentId}/verify`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Payment has been verified successfully.',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while verifying the payment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

function sendPaymentReminder(agreementId) {
    Swal.fire({
        title: 'Send Payment Reminder',
        text: 'Send a payment reminder to the client via SMS and Email?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Send Reminder',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sending Reminder...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/gentlemanagreement/${agreementId}/reminder`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Reminder Sent!',
                        text: 'Payment reminder has been sent to the client.',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to send reminder. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

function printAgreement() {
    window.print();
}

function downloadAgreementPDF() {
    window.open(`/gentlemanagreement/{{ $agreement->id }}/print`, '_blank');
}

function sendCopy() {
    Swal.fire({
        title: 'Send Agreement Copy',
        text: 'Send a copy of this agreement to the client\'s email?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Send Email',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Email Sent!', 'Agreement copy has been sent to the client.', 'success');
        }
    });
}

function approveAgreement(agreementId) {
    Swal.fire({
        title: 'Approve Agreement',
        text: 'Are you sure you want to approve this gentleman\'s agreement?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Approving Agreement...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/gentlemanagreement/${agreementId}/approve`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Agreement has been approved successfully.',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while approving the agreement.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
}

// Enhanced payment form submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const amount = parseFloat(formData.get('payment_amount'));
    const maxAmount = {{ $actualOutstanding }};
    const paymentDate = formData.get('payment_date');
    const paymentMethod = formData.get('payment_method');
    
    // Enhanced validation
    if (!amount || amount <= 0) {
        Swal.fire({
            title: 'Invalid Amount',
            text: 'Please enter a valid payment amount.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    if (amount > maxAmount) {
        Swal.fire({
            title: 'Amount Too High',
            text: `Payment amount cannot exceed outstanding balance of KSh ${maxAmount.toLocaleString()}`,
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    if (!paymentDate) {
        Swal.fire({
            title: 'Missing Date',
            text: 'Please select a payment date.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    if (!paymentMethod) {
        Swal.fire({
            title: 'Missing Payment Method',
            text: 'Please select a payment method.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    // Show remaining balance after payment
    const remainingBalance = maxAmount - amount;
    const isFullPayment = remainingBalance <= 0;
    
    // Confirmation dialog
    Swal.fire({
        title: 'Confirm Payment',
        html: `
            <div class="text-start">
                <p><strong>Amount:</strong> KSh ${amount.toLocaleString()}</p>
                <p><strong>Date:</strong> ${paymentDate}</p>
                <p><strong>Method:</strong> ${paymentMethod.replace('_', ' ').toUpperCase()}</p>
                <p><strong>Remaining Balance:</strong> ${isFullPayment ? '<span class="text-success">KSh 0 (FULLY PAID)</span>' : 'KSh ' + remainingBalance.toLocaleString()}</p>
                ${isFullPayment ? '<div class="alert alert-success mt-2"><i class="fas fa-trophy"></i> This payment will complete the agreement!</div>' : ''}
            </div>
        `,
        icon: isFullPayment ? 'success' : 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: isFullPayment ? 'Complete Payment' : 'Record Payment',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Recording Payment...',
                text: 'Please wait while we process your payment.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '/gentlemanagreement/payment',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#recordPaymentModal').modal('hide');
                    form.reset();
                    
                    const isCompleted = response.new_balance <= 0;
                    
                    Swal.fire({
                        title: isCompleted ? 'Agreement Completed!' : 'Payment Recorded!',
                        html: isCompleted ? 
                            '<div class="text-center"><i class="fas fa-trophy fa-3x text-success mb-3"></i><p>Congratulations! The agreement has been fully completed.</p></div>' :
                            'Payment has been successfully recorded.',
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        timer: isCompleted ? 5000 : 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while recording the payment.';
                    let errorDetails = '';
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'Please check the following errors:';
                        errorDetails = '<ul class="text-start mt-2">';
                        for (const field in errors) {
                            errors[field].forEach(error => {
                                errorDetails += `<li>${error}</li>`;
                            });
                        }
                        errorDetails += '</ul>';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        html: errorMessage + errorDetails,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });
});

// Auto-fill reference number based on payment method
document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
    const referenceInput = document.querySelector('input[name="payment_reference"]');
    const method = this.value;
    
    switch(method) {
        case 'mpesa':
            referenceInput.placeholder = 'M-Pesa Transaction Code (e.g., QC12345678)';
            break;
        case 'bank_transfer':
            referenceInput.placeholder = 'Bank Transaction Reference';
            break;
        case 'cheque':
            referenceInput.placeholder = 'Cheque Number';
            break;
        case 'card':
            referenceInput.placeholder = 'Card Transaction Reference';
            break;
        case 'cash':
            referenceInput.placeholder = 'Receipt Number (if any)';
            break;
        default:
            referenceInput.placeholder = 'Transaction/Receipt Number';
    }
});

// Input validation for payment amount
document.querySelector('input[name="payment_amount"]').addEventListener('input', function() {
    const maxAmount = {{ $actualOutstanding }};
    const enteredAmount = parseFloat(this.value);
    
    if (enteredAmount > maxAmount) {
        this.setCustomValidity(`Amount cannot exceed KSh ${maxAmount.toLocaleString()}`);
    } else {
        this.setCustomValidity('');
    }
});

// Reset modal form when modal is closed
$('#recordPaymentModal').on('hidden.bs.modal', function () {
    document.getElementById('paymentForm').reset();
    document.querySelector('input[name="payment_amount"]').setCustomValidity('');
    // Reset to default monthly payment amount
    document.getElementById('paymentAmount').value = {{ $agreement->monthly_payment }};
});

// Print specific sections
function printSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Gentleman's Agreement - {{ $agreement->client_name }}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            .no-print { display: none !important; }
                            body { font-size: 12px; }
                            .table { font-size: 11px; }
                            .card { break-inside: avoid; }
                        }
                        .letterhead-logo { max-height: 60px; }
                        .company-name { color: #0d6efd !important; }
                    </style>
                </head>
                <body class="p-3">
                    ${section.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

// Show success/error messages
$(document).ready(function() {
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: '{{ session("success") }}',
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true
        });
    @endif
    
    @if(session('error'))
        Swal.fire({
            title: 'Error!',
            text: '{{ session("error") }}',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    @endif
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Update payment progress dynamically
function updatePaymentProgress() {
    const totalPaid = {{ $totalAmountPaid }};
    const vehiclePrice = {{ $agreement->vehicle_price }};
    const progress = (totalPaid / vehiclePrice) * 100;
    
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
    }
}

// Utility functions
function formatCurrency(amount) {
    return 'KSh ' + amount.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function calculateRemainingMonths() {
    const remainingBalance = {{ $actualOutstanding }};
    const monthlyPayment = {{ $agreement->monthly_payment }};
    return Math.ceil(remainingBalance / monthlyPayment);
}

// Display remaining months calculation
const remainingMonths = calculateRemainingMonths();
if (remainingMonths > 0) {
    console.log(`Estimated ${remainingMonths} payments remaining based on current monthly amount.`);
}
</script>

<!-- Additional Styling -->
<style>
/* Custom styles for Gentleman's Agreement */
.bg-soft-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-soft-info {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-soft-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.professional-agreement {
    font-family: 'Times New Roman', serif;
    line-height: 1.6;
}

.professional-agreement h1, .professional-agreement h2, .professional-agreement h3 {
    color: #2c3e50;
}

.professional-agreement .company-name {
    color: #0d6efd;
    font-weight: bold;
}

/* Badge styling */
.badge {
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Card hover effects */
.card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

/* Progress bar styling */
.progress {
    border-radius: 10px;
    background-color: #f8f9fa;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.6s ease;
}

/* Alert styling */
.alert {
    border-radius: 10px;
    border: none;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #f0f9f0 100%);
    color: #155724;
}

.alert-info {
    background: linear-gradient(135deg, #cce7ff 0%, #e6f3ff 100%);
    color: #0c5460;
}

/* Table styling */
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Button group styling */
.btn-group-sm .btn {
    border-radius: 6px;
    margin-right: 2px;
}

/* Form styling */
.form-control:focus,
.form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Modal styling */
.modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px 15px 0 0;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .btn-group-vertical .btn {
        margin-bottom: 2px;
    }
    
    .financial-overview .col-xl-3 {
        margin-bottom: 1rem;
    }
}

/* Print styling */
@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        break-inside: avoid;
    }
    
    .badge {
        background-color: #6c757d !important;
        color: white !important;
    }
    
    .text-primary {
        color: #0d6efd !important;
    }
    
    .text-success {
        color: #198754 !important;
    }
}
</style>

</x-app-layout>