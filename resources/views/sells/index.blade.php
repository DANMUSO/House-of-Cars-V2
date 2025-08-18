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
        <div class="col-md-6 col-lg-4 gate-pass-item" 
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
                <!-- Compact Header -->
                <div class="gate-pass-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 text-white fw-bold">Gate Pass</h6>
                            <small class="text-white-50">ID: GP-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</small>
                        </div>
                        <div class="text-end">
                            @if($sale->record_type == 'incash')
                                <span class="badge bg-success">CASH</span>
                            @elseif($sale->record_type == 'hire_purchase')
                                <span class="badge bg-info">H.P.</span>
                            @else
                                <span class="badge bg-warning">G.A.</span>
                            @endif
                            <div class="small text-white-50">{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Compact Body -->
                <div class="gate-pass-body p-4">
                    <!-- Vehicle Section -->
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-car"></i>
                            <span>VEHICLE DETAILS</span>
                        </div>
                        <div class="section-content">
                            <div class="primary-info">
                                @if($sale->record_type == 'incash')
                                    @if($sale->car_type == 'import' && $sale->carImport)
                                        {{ $sale->carImport->make }} {{ $sale->carImport->model }} ({{ $sale->carImport->year }})
                                    @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                                        {{ $sale->customerVehicle->vehicle_make }}
                                    @else
                                        N/A
                                    @endif
                                @elseif($sale->record_type == 'hire_purchase')
                                    @if($sale->carImport)
                                        {{ $sale->carImport->make }} {{ $sale->carImport->model }} ({{ $sale->carImport->year }})
                                    @elseif($sale->customerVehicle)
                                        {{ $sale->customerVehicle->vehicle_make }}
                                    @else
                                        N/A
                                    @endif
                                @else
                                    @if($sale->car_type == 'import' && $sale->carImport)
                                        {{ $sale->carImport->make }} {{ $sale->carImport->model }} ({{ $sale->carImport->year }})
                                    @elseif($sale->car_type == 'customer' && $sale->customerVehicle)
                                        {{ $sale->customerVehicle->vehicle_make }}
                                    @else
                                        {{ $sale->vehicle_make ?? 'N/A' }} {{ $sale->vehicle_model ?? '' }}
                                    @endif
                                @endif
                            </div>
                            <div class="secondary-info">
                                Chassis: 
                                @if($sale->record_type == 'incash')
                                    {{ $sale->carImport->vin ?? $sale->customerVehicle->chasis_no ?? 'N/A' }}
                                @elseif($sale->record_type == 'hire_purchase')
                                    {{ $sale->carImport->vin ?? $sale->carImport->chassis_number ?? $sale->customerVehicle->chasis_no ?? 'N/A' }}
                                @else
                                    {{ $sale->carImport->chassis_number ?? $sale->carImport->vin ?? $sale->customerVehicle->chasis_no ?? 'N/A' }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Customer Section -->
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-user"></i>
                            <span>CUSTOMER DETAILS</span>
                        </div>
                        <div class="section-content">
                            <div class="customer-layout">
                                <div class="customer-main">
                                    <div class="primary-info">
                                        @if($sale->record_type == 'incash')
                                            {{ $sale->Client_Name ?? 'N/A' }}
                                        @elseif($sale->record_type == 'hire_purchase')
                                            {{ $sale->customer_name ?? $sale->client_name ?? 'N/A' }}
                                        @else
                                            {{ $sale->client_name ?? 'N/A' }}
                                        @endif
                                    </div>
                                    <div class="secondary-info">
                                        Phone: 
                                        @if($sale->record_type == 'incash')
                                            {{ $sale->Phone_No ?? 'N/A' }}
                                        @elseif($sale->record_type == 'hire_purchase')
                                            {{ $sale->customer_phone ?? $sale->phone_number ?? 'N/A' }}
                                        @else
                                            {{ $sale->phone_number ?? 'N/A' }}
                                        @endif
                                    </div>
                                </div>
                                <div class="customer-id">
                                    <div class="id-label">ID Number</div>
                                    <div class="id-number">
                                        @if($sale->record_type == 'incash')
                                            {{ $sale->ID_Number ?? 'N/A' }}
                                        @elseif($sale->record_type == 'hire_purchase')
                                            {{ $sale->customer_id_number ?? $sale->national_id ?? 'N/A' }}
                                        @else
                                            {{ $sale->national_id ?? 'N/A' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Authorization Section -->
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-clipboard-check"></i>
                            <span>AUTHORIZATION</span>
                        </div>
                        <div class="section-content">
                            <div class="auth-grid">
                                <div class="auth-item">
                                    <span class="auth-label">Purpose:</span>
                                    <span class="auth-value">Vehicle delivery</span>
                                </div>
                                <div class="auth-item">
                                    <span class="auth-label">Date:</span>
                                    <span class="auth-value">{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}</span>
                                </div>
                                <div class="auth-item">
                                    <span class="auth-label">Time:</span>
                                    <span class="auth-value">{{ \Carbon\Carbon::parse($sale->created_at)->format('H:i') }}</span>
                                </div>
                                <div class="auth-item">
                                    <span class="auth-label">By:</span>
                                    <span class="auth-value">{{ $sale->authorized_by ?? 'Showroom Manager' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Alert -->
                    <div class="security-verification">
                        <div class="verification-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="verification-content">
                            <div class="verification-title">VERIFY ID:</div>
                            <div class="verification-text">Security must check customer ID before vehicle release</div>
                        </div>
                    </div>
                </div>

                <!-- Compact Actions -->
                <div class="card-footer bg-light p-2">
                    <button class="btn btn-primary btn-sm w-100" onclick="downloadCard({{ $sale->id }})">
                        <i class="fas fa-download"></i> Download Gate Pass
                    </button>
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

<!-- JavaScript for Instant Filtering -->
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
        // Add event listeners for all filter inputs
        Object.values(this.filters).forEach(filter => {
            if (filter.type === 'text') {
                filter.addEventListener('input', () => this.applyFilters());
            } else {
                filter.addEventListener('change', () => this.applyFilters());
            }
        });

        // Clear filters button
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
        // Sale type filter
        if (filterValues.saleType && !itemData.saleType.includes(filterValues.saleType)) {
            return false;
        }

        // Pass ID filter (supports both full ID and number only)
        if (filterValues.passId) {
            const passIdMatch = itemData.passId.includes(filterValues.passId) ||
                               itemData.passId.replace('gp-', '').includes(filterValues.passId);
            if (!passIdMatch) return false;
        }

        // ID Number filter
        if (filterValues.idNumber && !itemData.idNumber.includes(filterValues.idNumber)) {
            return false;
        }

        // Customer filter
        if (filterValues.customer && !itemData.customer.includes(filterValues.customer)) {
            return false;
        }

        // Vehicle filter
        if (filterValues.vehicle && !itemData.vehicle.includes(filterValues.vehicle)) {
            return false;
        }

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

// Enhanced Download Function
function downloadCard(saleId) {
    // Get the original card data
    const cardElement = document.getElementById(`gate-pass-${saleId}`);
    const gatePassItem = cardElement.closest('.gate-pass-item');
    
    // Extract data from the card
    const passId = gatePassItem.dataset.passId;
    const saleType = gatePassItem.dataset.saleType;
    const customer = gatePassItem.dataset.customer;
    const idNumber = gatePassItem.dataset.idNumber;
    const vehicle = gatePassItem.dataset.vehicle;
    
    // Get vehicle and chassis info from the card
    const vehicleText = cardElement.querySelector('.text-primary strong')?.textContent || vehicle;
    const chassisText = cardElement.querySelector('.small.text-muted')?.textContent || 'N/A';
    const phoneText = cardElement.querySelectorAll('.small.text-muted')[1]?.textContent || 'N/A';
    
    // Determine badge type and color
    let badgeText = '', badgeColor = '', badgeTextColor = '#fff';
    if (saleType === 'incash') {
        badgeText = 'CASH';
        badgeColor = '#28a745';
    } else if (saleType === 'hire_purchase') {
        badgeText = 'H.P.';
        badgeColor = '#17a2b8';
    } else {
        badgeText = 'G.A.';
        badgeColor = '#ffc107';
        badgeTextColor = '#000';
    }
    
    // Get current date
    const currentDate = new Date();
    const formattedDate = currentDate.toLocaleDateString('en-US', { 
        month: 'short', 
        day: '2-digit', 
        year: 'numeric' 
    });
    
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Gate Pass - ${passId}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        background: white;
                        padding: 20px;
                        color: #333;
                    }
                    .gate-pass-container {
                        max-width: 600px;
                        margin: 0 auto;
                        border: 3px solid #000;
                        border-radius: 8px;
                        overflow: hidden;
                        background: white;
                    }
                    .gate-pass-header {
                        background: linear-gradient(135deg, #007bff, #0056b3);
                        color: white;
                        padding: 20px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .header-left h2 {
                        margin: 0 0 5px 0;
                        font-size: 1.5rem;
                        font-weight: bold;
                    }
                    .header-left .id {
                        opacity: 0.8;
                        font-size: 0.9rem;
                    }
                    .header-right {
                        text-align: right;
                    }
                    .badge {
                        background: ${badgeColor};
                        color: ${badgeTextColor};
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-weight: bold;
                        font-size: 0.8rem;
                        margin-bottom: 8px;
                        display: inline-block;
                    }
                    .date {
                        opacity: 0.8;
                        font-size: 0.9rem;
                    }
                    .gate-pass-body {
                        padding: 25px;
                    }
                    .section {
                        margin-bottom: 20px;
                        padding-bottom: 15px;
                        border-bottom: 1px solid #eee;
                    }
                    .section:last-of-type {
                        border-bottom: none;
                        margin-bottom: 0;
                    }
                    .vehicle-info h3 {
                        color: #007bff;
                        font-size: 1.2rem;
                        margin-bottom: 5px;
                        font-weight: bold;
                    }
                    .chassis {
                        color: #666;
                        font-size: 0.9rem;
                    }
                    .customer-section {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                    }
                    .customer-info h3 {
                        font-size: 1.1rem;
                        margin-bottom: 5px;
                        color: #333;
                    }
                    .phone {
                        color: #666;
                        font-size: 0.9rem;
                    }
                    .customer-id {
                        text-align: right;
                        color: #007bff;
                        font-weight: bold;
                        font-size: 0.9rem;
                    }
                    .auth-section {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                    }
                    .auth-details {
                        color: #666;
                        font-size: 0.9rem;
                        line-height: 1.5;
                    }
                    .status-badge {
                        background: ${saleType === 'incash' ? '#d1e7dd' : '#fff3cd'};
                        color: ${saleType === 'incash' ? '#0f5132' : '#664d03'};
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-size: 0.85rem;
                        font-weight: 600;
                    }
                    .security-alert {
                        background: #f8d7da;
                        border: 1px solid #f5c6cb;
                        color: #721c24;
                        padding: 15px;
                        border-radius: 4px;
                        margin-top: 20px;
                        text-align: center;
                        font-size: 0.9rem;
                    }
                    .security-alert strong {
                        font-weight: bold;
                    }
                    @media print {
                        body { padding: 0; }
                        .gate-pass-container { border: 2px solid #000; }
                    }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                <div class="gate-pass-container">
                    <div class="gate-pass-header">
                        <div class="header-left">
                            <h2>Gate Pass</h2>
                            <div class="id">ID: ${passId}</div>
                        </div>
                        <div class="header-right">
                            <div class="badge">${badgeText}</div>
                            <div class="date">${formattedDate}</div>
                        </div>
                    </div>
                    
                    <div class="gate-pass-body">
                        <div class="section vehicle-info">
                            <h3>${vehicleText}</h3>
                            <div class="chassis">${chassisText}</div>
                        </div>
                        
                        <div class="section">
                            <div class="customer-section">
                                <div class="customer-info">
                                    <h3>${customer || 'N/A'}</h3>
                                    <div class="phone">${phoneText}</div>
                                </div>
                                <div class="customer-id">
                                    ID: ${idNumber || 'N/A'}
                                </div>
                            </div>
                        </div>
                        
                        <div class="section">
                            <div class="auth-section">
                                <div class="auth-details">
                                    <div>Purpose: Vehicle delivery to client</div>
                                    <div>Date: ${currentDate.toISOString().split('T')[0]}</div>
                                    <div>Time: ${currentDate.toTimeString().slice(0,5)}</div>
                                    <div>Authorized By: Showroom Manager</div>
                                </div>
                                <div class="status-badge">
                                    ${saleType === 'incash' ? 'PAID' : 'FINANCED'}
                                </div>
                            </div>
                        </div>
                        
                        <div class="security-alert">
                            <strong>⚠️ VERIFY ID:</strong> Security must check customer ID before vehicle release
                        </div>
                    </div>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Initialize filter system when page loads
document.addEventListener('DOMContentLoaded', function() {
    new GatePassFilter();
    
    // Show success/error messages
    @if(session('success'))
        showNotification('{{ session("success") }}', 'success');
    @endif
    
    @if(session('error'))
        showNotification('{{ session("error") }}', 'error');
    @endif
});

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>

<!-- Precise CSS Styles -->
<style>
.gate-pass-card {
    border: 1px solid #000;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: #fff;
    overflow: hidden;
}

.gate-pass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.gate-pass-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 11px 11px 0 0;
    margin: -1px -1px 0 -1px;
    padding: 16px 20px;
}

.gate-pass-body {
    font-size: 0.9rem;
    background: #fff;
}

/* Enhanced Section Styling */
.info-section {
    background: #fff;
    margin: 0 0 12px 0;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    overflow: hidden;
}

.info-section:last-of-type {
    margin-bottom: 0;
}

.section-header {
    background: #f8f9fa;
    padding: 10px 16px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    letter-spacing: 0.5px;
}

.section-header i {
    font-size: 0.85rem;
    color: #6c757d;
    width: 16px;
    text-align: center;
}

.section-content {
    padding: 16px;
    background: #fff;
}

/* Typography */
.primary-info {
    font-size: 1.1rem;
    font-weight: 600;
    color: #007bff;
    margin-bottom: 6px;
    line-height: 1.3;
}

.secondary-info {
    font-size: 0.85rem;
    color: #6c757d;
    line-height: 1.4;
}

/* Customer Layout */
.customer-layout {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.customer-main {
    flex: 1;
}

.customer-main .primary-info {
    color: #495057;
    font-size: 1.05rem;
}

.customer-id {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px 12px;
    text-align: center;
    min-width: 120px;
}

.id-label {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.id-number {
    font-weight: 700;
    color: #007bff;
    font-size: 0.9rem;
    font-family: 'Courier New', monospace;
}

/* Status Badge */
.status-badge-container {
    margin-left: auto;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.paid {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
}

.status-badge.financed {
    background: #fff3cd;
    color: #664d03;
    border: 1px solid #ffecb5;
}

/* Authorization Grid */
.auth-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 16px;
}

.auth-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.auth-label {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.auth-value {
    font-size: 0.85rem;
    color: #495057;
    font-weight: 500;
}

/* Security Verification */
.security-verification {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 6px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
}

.verification-icon {
    color: #721c24;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.verification-title {
    color: #721c24;
    font-size: 0.8rem;
    font-weight: 700;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.verification-text {
    color: #721c24;
    font-size: 0.8rem;
    line-height: 1.3;
}

/* Form Controls */
.form-control-sm, .form-select-sm {
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: all 0.2s ease;
}

.form-control-sm:focus, .form-select-sm:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

/* Buttons */
.btn-sm {
    padding: 8px 12px;
    font-size: 0.85rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 12px 16px;
}

.card-footer .btn {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Header Badges */
.badge {
    font-size: 0.75rem;
    padding: 6px 10px;
    font-weight: 600;
    border-radius: 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .col-md-2 {
        margin-bottom: 10px;
    }
    
    .gate-pass-card {
        margin-bottom: 20px;
    }
    
    .customer-layout {
        flex-direction: column;
        gap: 12px;
    }
    
    .customer-id {
        align-self: flex-start;
        min-width: auto;
    }
    
    .auth-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .auth-item {
        flex-direction: row;
        align-items: center;
        gap: 8px;
    }
    
    .auth-label {
        min-width: 70px;
    }
}

#no-results {
    background: #f8f9fa;
    border-radius: 8px;
    margin: 20px 0;
}

@media print {
    .card-footer, .btn {
        display: none !important;
    }
    
    .gate-pass-card {
        break-inside: avoid;
        border: 2px solid #000 !important;
        margin-bottom: 20px;
    }
}
</style>
</x-app-layout>