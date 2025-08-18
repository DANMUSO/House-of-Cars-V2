<x-app-layout>
<div class="container-fluid">
    {{-- Dashboard Header --}}
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Dashboard Overview</h4>
            <p class="text-muted mb-0">Last updated: {{ now()->format('M d, Y h:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                <i class="mdi mdi-refresh"></i> Refresh
            </button>
            @if(Route::has('dashboard.export'))
                <a href="{{ route('dashboard.export') }}" class="btn btn-outline-success btn-sm">
                    <i class="mdi mdi-download"></i> Export
                </a>
            @endif
        </div>
    </div>

    {{-- Alerts Section --}}
    @if(isset($alerts) && count($alerts) > 0)
        <div class="row mb-4">
            <div class="col-12">
                @foreach($alerts as $alert)
                    <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        {{ $alert['message'] }}
                        @if(isset($alert['action_url']) && $alert['action_url'] !== '#')
                            <a href="{{ $alert['action_url'] }}" class="alert-link ms-2">Take Action</a>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Performance Metrics Row --}}
    <div class="row mb-4">
        {{-- Collections Forecast --}}
        <div class="col-md-3">
            <div class="card border-start border-start-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Next 30 Days Collections</h6>
                    <h4 class="mb-0 text-success">KES {{ number_format($collectionsForecast['next_30_days'] ?? 0) }}</h4>
                    <small class="text-muted">Expected revenue</small>
                </div>
            </div>
        </div>
        <!-- Next 60 Days Collections Card -->
<div class="col-md-3">
    <div class="card border-start border-start-4 border-warning">
        <div class="card-body">
            <h6 class="text-muted mb-1">Next 60 Days Collections</h6>
            <h4 class="mb-0 text-warning">KES {{ number_format($collectionsForecast['next_60_days'] ?? 0) }}</h4>
            <small class="text-muted">Mid-term revenue</small>
        </div>
    </div>
</div>

<!-- Total 90-Day Forecast Card -->
<div class="col-md-3">
    <div class="card border-start border-start-4 border-primary">
        <div class="card-body">
            <h6 class="text-muted mb-1">90-Day Total Forecast</h6>
            <h4 class="mb-0 text-primary">KES {{ number_format($collectionsForecast['total_forecast'] ?? 0) }}</h4>
            <small class="text-muted">Complete outlook</small>
        </div>
    </div>
</div>

<!-- Overdue Collections Card -->
<div class="col-md-3">
    <div class="card border-start border-start-4 border-danger">
        <div class="card-body">
            <h6 class="text-muted mb-1">Overdue Collections</h6>
            <h4 class="mb-0 text-danger">KES {{ number_format($overdueCollections ?? 0) }}</h4>
            <small class="text-muted">Requires attention</small>
        </div>
    </div>
</div>
    </div>

    {{-- NEW STATISTICS ROW --}}
    <div class="row mb-4">
        {{-- Inspected Cars --}}
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-info border-opacity-10 bg-info-subtle rounded-2 me-2">
                                <div class="bg-info rounded-circle widget-size text-center">
                                    <i class="mdi mdi-clipboard-check text-white fs-20"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-14">Inspected Cars</p>
                        </div>
                        <h4 class="mb-0 fs-20 text-dark">{{ number_format($inspectedCarsCount ?? 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cars in Showroom (Updated) --}}
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-secondary border-opacity-10 bg-secondary-subtle rounded-2 me-2">
                                <div class="bg-secondary rounded-circle widget-size text-center">
                                    <i class="mdi mdi-home-variant text-white fs-20"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-14">Cars in Showroom</p>
                        </div>
                        <h4 class="mb-0 fs-20 text-dark">{{ number_format($carsInShowroom ?? 0) }}</h4>
                       
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Sold Cars --}}
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-success border-opacity-10 bg-success-subtle rounded-2 me-2">
                                <div class="bg-success rounded-circle widget-size text-center">
                                    <i class="mdi mdi-car text-white fs-20"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-14">Total Sold</p>
                        </div>
                        <h4 class="mb-0 fs-20 text-dark">{{ number_format($totalSoldCarsPurchase ?? 0) }}</h4>
                        
                    </div>
                </div>
            </div>
        </div>

        {{-- Fleet Acquisitions --}}
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-primary border-opacity-10 bg-primary-subtle rounded-2 me-2">
                                <div class="bg-primary rounded-circle widget-size text-center">
                                    <i class="mdi mdi-truck text-white fs-20"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-14">Fleet Cars</p>
                        </div>
                        <h4 class="mb-0 fs-20 text-dark">{{ number_format($fleetAcquisitionCount ?? 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Users --}}
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-warning border-opacity-10 bg-warning-subtle rounded-2 me-2">
                                <div class="bg-warning rounded-circle widget-size text-center">
                                    <i class="mdi mdi-account-group text-white fs-20"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-14">Active Users</p>
                        </div>
                        <h4 class="mb-0 fs-20 text-dark">{{ number_format($activeUsersCount ?? 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 border border-success border-opacity-10 bg-success-subtle rounded-2 me-2">
                                <div class="bg-success rounded-circle widget-size text-center">
                                    <i class="mdi mdi-currency-usd text-white fs-20"></i>
                                </div>
                            </div>
                            <p class="mb-0 text-dark fs-14">Total Revenue</p>
                        </div>
                        <h4 class="mb-0 fs-20 text-dark">KES {{ number_format($totalRevenue ?? 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales by Payment Method Section (Updated with new counts) --}}
    <div class="row mt-4">
        {{-- Hire Purchase Sales Count --}}
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="p-2 border border-warning border-opacity-10 bg-warning-subtle rounded-2 me-2">
                            <div class="bg-warning rounded-circle widget-size text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                    <path fill="#ffffff" d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a1 1 0 0 0-1 1v11a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8a1 1 0 0 0-1-1ZM10 6a2 2 0 0 1 4 0v1h-4V6Zm8 13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V9h2v1a1 1 0 0 0 2 0V9h4v1a1 1 0 0 0 2 0V9h2v10Z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Hire Purchase</h6>
                            <h4 class="mb-0 text-warning">{{ $soldThroughHirePurchase ?? 0 }}</h4>
                            <small class="text-muted">cars sold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cash Sales Count --}}
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="p-2 border border-success border-opacity-10 bg-success-subtle rounded-2 me-2">
                            <div class="bg-success rounded-circle widget-size text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                    <path fill="#ffffff" d="M7 15h2c0 1.08 1.37 2 3 2s3-.92 3-2c0-1.1-1.04-1.5-3.24-2.03C9.64 12.44 7 11.78 7 9c0-1.79 1.47-3.31 3.5-3.82V3h3v2.18C15.53 5.69 17 7.21 17 9h-2c0-1.08-1.37-2-3-2s-3 .92-3 2c0 1.1 1.04 1.5 3.24 2.03C14.36 11.56 17 12.22 17 15c0 1.79-1.47 3.31-3.5 3.82V21h-3v-2.18C8.47 18.31 7 16.79 7 15"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Cash Sales</h6>
                            <h4 class="mb-0 text-success">{{ $soldThroughCash ?? 0 }}</h4>
                            <small class="text-muted">cars sold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gentleman Sales Count --}}
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="p-2 border border-info border-opacity-10 bg-info-subtle rounded-2 me-2">
                            <div class="bg-info rounded-circle widget-size text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                    <path fill="#ffffff" d="M12 4c4.411 0 8 3.589 8 8s-3.589 8-8 8s-8-3.589-8-8s3.589-8 8-8m0-2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2zm1 13h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Gentleman Sales</h6>
                            <h4 class="mb-0 text-info">{{ $soldThroughGentleman ?? 0 }}</h4>
                            <small class="text-muted">cars sold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Leads & Conversion Rate --}}
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="p-2 border border-primary border-opacity-10 bg-primary-subtle rounded-2 me-2">
                            <div class="bg-primary rounded-circle widget-size text-center">
                                <i class="mdi mdi-trending-up text-white fs-20"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Active Leads</h6>
                            <h4 class="mb-0 text-primary">{{ $activeLeads ?? 0 }}</h4>
                            <small class="text-muted">{{ $conversionRate ?? 0 }}% conversion</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row mt-4">
        {{-- Monthly Financial Analysis Chart --}}
        <div class="col-md-12 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Monthly Financial Analysis</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="chartType" id="lineChart" checked>
                            <label class="btn btn-outline-primary" for="lineChart">Line</label>
                            <input type="radio" class="btn-check" name="chartType" id="barChart">
                            <label class="btn btn-outline-primary" for="barChart">Bar</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="monthly-sales" class="apex-charts"></div>
                </div>
            </div>
        </div>

        {{-- Top Car Makes Chart --}}
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Top Car Makes</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="pieType" id="donutChart" checked>
                            <label class="btn btn-outline-secondary" for="donutChart">Donut</label>
                            <input type="radio" class="btn-check" name="pieType" id="pieChart">
                            <label class="btn btn-outline-secondary" for="pieChart">Pie</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="top-session" class="apex-charts"></div>
                    <div class="row mt-2">
                        @if(isset($topSellingCars) && $topSellingCars->count() > 0)
                            @foreach($topSellingCars->chunk(2) as $chunk)
                                <div class="col">
                                    @foreach($chunk as $car)
                                        <div class="d-flex justify-content-between align-items-center p-1">
                                            <div>
                                                <i class="mdi mdi-circle fs-12 align-middle me-1" style="color: {{ ['#287F71', '#522c8f', '#E77636', '#01D4FF', '#FF6B6B', '#4ECDC4'][$loop->parent->index * 2 + $loop->index] ?? '#6c757d' }}"></i>
                                                <span class="align-middle fw-semibold">{{ $car->make }}</span>
                                            </div>
                                            <span class="fw-medium text-muted float-end">{{ $car->count }} units</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center">
                                <p class="text-muted mb-0">No sales data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions Table (Updated to show recent 5) --}}
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Recent 5 Transactions</h5>
                    </div>
                </div>
                <div class="card-body mt-0">
                    <div class="table-responsive table-card mt-0">
                        <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col">Vehicle</th>
                                    <th scope="col">Client Name</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Payment Mode</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                                    @foreach($recentTransactions->take(5) as $transaction)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-light rounded me-3 d-flex align-items-center justify-content-center">
                                                        <i class="mdi mdi-car text-primary"></i>
                                                    </div>
                                                    {{ $transaction->make ?? 'N/A' }} {{ $transaction->model ?? '' }}
                                                </div>
                                            </td>
                                            <td>{{ $transaction->client_name ?? 'N/A' }}</td>
                                            <td>{{ $transaction->phone ?? 'N/A' }}</td>
                                            <td>KES {{ number_format($transaction->amount ?? 0) }}</td>
                                            <td>
                                                <span class="badge bg-{{ ($transaction->payment_mode ?? 'In Cash') == 'Cash Sale' ? 'success' : (($transaction->payment_mode ?? 'In Cash') == 'Hire Purchase' ? 'warning' : 'info') }}-subtle text-{{ ($transaction->payment_mode ?? 'In Cash') == 'Cash Sale' ? 'success' : (($transaction->payment_mode ?? 'In Cash') == 'Hire Purchase' ? 'warning' : 'info') }} fw-semibold">
                                                    {{ $transaction->payment_mode ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No recent transactions found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NEW DETAILED STATISTICS SECTION --}}
    <div class="row mt-4">
        {{-- This Month Sales --}}
        <div class="col-md-3">
            <div class="card border-start border-start-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">This Month Sales</h6>
                            <h4 class="mb-0">KES {{ number_format($performanceMetrics['current_month_sales'] ?? 0) }}</h4>
                        </div>
                        <div class="text-{{ ($performanceMetrics['growth_direction'] ?? 'up') == 'up' ? 'success' : 'danger' }}">
                            <i class="mdi mdi-arrow-{{ $performanceMetrics['growth_direction'] ?? 'up' }} fs-20"></i>
                            <span class="fs-14">{{ abs($performanceMetrics['sales_growth'] ?? 0) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Overdue Accounts --}}
        <div class="col-md-3">
            <div class="card border-start border-start-4 border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Overdue Accounts</h6>
                    <h4 class="mb-0 text-warning">{{ $overduePayments['overdue_count'] ?? 0 }}</h4>
                    <small class="text-muted">KES {{ number_format($overduePayments['total_overdue_amount'] ?? 0) }}</small>
                </div>
            </div>
        </div>

        {{-- Inventory Health --}}
        <div class="col-md-3">
            <div class="card border-start border-start-4 border-info">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Inventory Health</h6>
                    <h4 class="mb-0 text-info">{{ $inventoryAnalysis['inventory_health'] ?? 100 }}%</h4>
                    <small class="text-muted">{{ $inventoryAnalysis['stale_inventory'] ?? 0 }} stale items</small>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detailed Sales Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="border-end pe-3">
                                <h6 class="text-muted mb-3">Sales Breakdown</h6>
                                <div class="mb-2">
                                    <span class="text-dark fw-semibold">Hire Purchase:</span>
                                    <span class="float-end text-warning fw-bold">{{ $soldThroughHirePurchase ?? 0 }} cars</span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-dark fw-semibold">Cash Sales:</span>
                                    <span class="float-end text-success fw-bold">{{ $soldThroughCash ?? 0 }} cars</span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-dark fw-semibold">Gentleman Agreement:</span>
                                    <span class="float-end text-info fw-bold">{{ $soldThroughGentleman ?? 0 }} cars</span>
                                </div>
                                <hr>
                                <div class="mb-0">
                                    <span class="text-dark fw-semibold">Total Sold:</span>
                                    <span class="float-end text-primary fw-bold">{{ $totalSoldCarsPurchase ?? 0 }} cars</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end pe-3">
                                <h6 class="text-muted mb-3">Inventory Status</h6>
                                <div class="mb-2">
                                    <span class="text-dark fw-semibold">Cars in Showroom:</span>
                                    <span class="float-end text-secondary fw-bold">{{ $carsInShowroom ?? 0 }} cars</span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-dark fw-semibold">Inspected Cars:</span>
                                    <span class="float-end text-info fw-bold">{{ $inspectedCarsCount ?? 0 }} cars</span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-dark fw-semibold">Fleet Acquisitions:</span>
                                    <span class="float-end text-primary fw-bold">{{ $fleetAcquisitionCount ?? 0 }} cars</span>
                                </div>
                                <hr>
                                <div class="mb-0">
                                    <span class="text-dark fw-semibold">Inventory Health:</span>
                                    <span class="float-end text-{{ ($inventoryAnalysis['inventory_health'] ?? 100) >= 70 ? 'success' : (($inventoryAnalysis['inventory_health'] ?? 100) >= 50 ? 'warning' : 'danger') }} fw-bold">{{ $inventoryAnalysis['inventory_health'] ?? 100 }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3">System Overview</h6>
                            <div class="mb-2">
                                <span class="text-dark fw-semibold">Active Users:</span>
                                <span class="float-end text-warning fw-bold">{{ $activeUsersCount ?? 0 }} users</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-dark fw-semibold">Active Leads:</span>
                                <span class="float-end text-primary fw-bold">{{ $activeLeads ?? 0 }} leads</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-dark fw-semibold">Conversion Rate:</span>
                                <span class="float-end text-{{ ($conversionRate ?? 0) >= 20 ? 'success' : (($conversionRate ?? 0) >= 10 ? 'warning' : 'danger') }} fw-bold">{{ $conversionRate ?? 0 }}%</span>
                            </div>
                            <hr>
                            <div class="mb-0">
                                <span class="text-dark fw-semibold">Total Revenue:</span>
                                <span class="float-end text-success fw-bold">KES {{ number_format($totalRevenue ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript Section --}}
<script>
// Global chart variables
let monthlyChart;
let topCarChart;

// Dashboard refresh function
function refreshDashboard() {
    @if(Route::has('dashboard.api'))
        fetch('{{ route('dashboard.api') }}')
            .then(response => response.json())
            .then(data => {
                location.reload();
            })
            .catch(error => {
                console.error('Error refreshing dashboard:', error);
                alert('Error refreshing dashboard. Please try again.');
            });
    @else
        location.reload();
    @endif
}

// Prepare chart data
@if(isset($topSellingCars) && $topSellingCars->count() > 0)
    var topCarData = @json($topSellingCars->pluck('count')->toArray());
    var topCarLabels = @json($topSellingCars->pluck('make')->toArray());
@else
    var topCarData = [1];
    var topCarLabels = ['No Data'];
@endif

@if(isset($monthlyData))
    var monthlyData = @json($monthlyData);
@else
    var monthlyData = {
        months: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        income: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        expenses: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        revenue: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
    };
@endif

// Top Car Makes Chart (Donut/Pie)
function renderTopCarChart(type = 'donut') {
    if (topCarChart) {
        topCarChart.destroy();
    }
    
    var options = {
        series: topCarData,
        labels: topCarLabels,
        chart: {
            type: type === 'donut' ? 'donut' : 'pie',
            height: 280
        },
        plotOptions: {
            pie: {
                size: 100,
                donut: {
                    size: type === 'donut' ? "75%" : "0%"
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Math.round(val) + "%"
            }
        },
        legend: {
            show: false
        },
        stroke: {
            width: 2
        },
        colors: ["#287F71", "#522c8f", "#E77636", "#01D4FF", "#FF6B6B", "#4ECDC4"],
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " units"
                }
            }
        }
    };

    topCarChart = new ApexCharts(document.querySelector("#top-session"), options);
    topCarChart.render();
}

// Monthly Financial Chart (Line/Bar)
function renderMonthlyChart(type = 'line') {
    if (monthlyChart) {
        monthlyChart.destroy();
    }
    
    var options = {
        series: [
            { name: "Total Income", type: type === 'line' ? "bar" : "bar", data: monthlyData.income },
            { name: "Total Expense", type: type === 'line' ? "bar" : "bar", data: monthlyData.expenses },
            { name: "Net Revenue", type: type === 'line' ? "line" : "bar", data: monthlyData.revenue }
        ],
        chart: { 
            height: 340, 
            type: type, 
            toolbar: { show: false } 
        },
        stroke: { 
            dashArray: [0, 0, type === 'line' ? 0 : 0], 
            width: [0, 0, type === 'line' ? 3 : 0], 
            curve: "smooth" 
        },
        fill: { 
            opacity: [1, 1, type === 'line' ? 0.1 : 1],
            type: ["solid", "solid", type === 'line' ? "gradient" : "solid"],
            gradient: { 
                type: "vertical", 
                opacityFrom: 0.5, 
                opacityTo: 0 
            } 
        },
        markers: { 
            size: [0, 0, type === 'line' ? 5 : 0], 
            strokeWidth: 2, 
            hover: { size: 4 } 
        },
        xaxis: { 
            categories: monthlyData.months,
            axisTicks: { show: false }, 
            axisBorder: { show: false } 
        },
        yaxis: { 
            min: 0, 
            axisBorder: { show: false },
            labels: {
                formatter: function(val) {
                    return val + "K";
                }
            }
        },
        grid: { 
            show: true, 
            strokeDashArray: 3,
            padding: { top: 0, right: -2, bottom: 0, left: 10 } 
        },
        legend: { 
            show: true, 
            horizontalAlign: "center", 
            offsetY: 5,
            markers: { width: 9, height: 9, radius: 6 },
            itemMargin: { horizontal: 10, vertical: 0 } 
        },
        plotOptions: { 
            bar: { 
                columnWidth: "50%", 
                borderRadius: 3 
            } 
        },
        colors: ["#28a745", "#dc3545", "#007bff"],
        tooltip: {
            shared: true,
            y: [
                { formatter: e => (e !== undefined ? `KES ${e.toFixed(1)}K` : e) },
                { formatter: e => (e !== undefined ? `KES ${e.toFixed(1)}K` : e) },
                { formatter: e => (e !== undefined ? `KES ${e.toFixed(1)}K` : e) }
            ]
        }
    };

    monthlyChart = new ApexCharts(document.querySelector("#monthly-sales"), options);
    monthlyChart.render();
}

// Initialize all charts
document.addEventListener('DOMContentLoaded', function() {
    renderTopCarChart('donut');
    renderMonthlyChart('line');

    // Chart type toggles
    document.addEventListener('change', function(e) {
        if (e.target.name === 'chartType') {
            renderMonthlyChart(e.target.id === 'lineChart' ? 'line' : 'bar');
        }
        if (e.target.name === 'pieType') {
            renderTopCarChart(e.target.id === 'donutChart' ? 'donut' : 'pie');
        }
    });
});
</script>

{{-- Custom Styles --}}
<style>
.widget-size {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.border-start-4 {
    border-left-width: 4px !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    font-weight: 600;
    color: #495057;
}

.apex-charts {
    min-height: 200px;
}
</style>
</x-app-layout>