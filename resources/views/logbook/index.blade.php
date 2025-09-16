<x-app-layout>
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0">Car Docs Management</h4>
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <i class="fas fa-plus me-1"></i> Add Document
            </button>
        </div>

        <!-- Summary Cards -->
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Total Documents</h6>
                        <h3 class="text-primary mb-1">{{ $logbooks->count() }}</h3>
                        <small class="text-muted">All Documents</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Active</h6>
                        <h3 class="text-success mb-1">{{ $logbooks->where('status', 'active')->count() }}</h3>
                        <small class="text-muted">Active Documents</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Expiring Soon</h6>
                        <h3 class="text-warning mb-1">{{ $logbooks->filter(function($logbook) { return $logbook->expiry_status === 'expiring_soon'; })->count() }}</h3>
                        <small class="text-muted">Within 30 Days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Expired</h6>
                        <h3 class="text-danger mb-1">{{ $logbooks->filter(function($logbook) { return $logbook->expiry_status === 'expired'; })->count() }}</h3>
                        <small class="text-muted">Needs Renewal</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logbooks Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Documents</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="responsive-datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Car</th>
                                <th>Document Type</th>
                                <th>Title</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Documents</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logbooks as $logbook)
                            <tr id="logbook-{{ $logbook->id }}">
                                <td>
                                    <div class="fw-semibold">{{ $logbook->car_details ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ ucfirst($logbook->car_type ?? 'unknown') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $logbook->document_type)) }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $logbook->title }}</div>
                                    @if($logbook->reference_number)
                                        <small class="text-muted">{{ $logbook->reference_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($logbook->expiry_date)
                                        {{ $logbook->expiry_date->format('M d, Y') }}
                                        <span class="badge bg-warning d-block mt-1">
                                            {{ ucfirst(str_replace('_', ' ', $logbook->expiry_status ?? 'active')) }}
                                        </span>
                                    @else
                                        <span class="text-muted">No Expiry</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $logbook->status === 'active' ? 'success' : ($logbook->status === 'archived' ? 'secondary' : 'warning') }}">
                                        {{ ucfirst($logbook->status) }}
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-file-alt me-1"></i> {{ $logbook->document_count ?? 0 }}
                                </td>
                                <td>
                                    <div class="btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewLogbook({{ $logbook->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-info btn-sm" onclick="manageDocuments({{ $logbook->id }})">
                                            <i class="fas fa-folder"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="editLogbook({{ $logbook->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteLogbook({{ $logbook->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                                    <br>No Documents found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="logbookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="logbookForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="logbookId" name="id">
                    <input type="hidden" id="method" name="_method" value="POST">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Car <span class="text-danger">*</span></label>
                                    <select class="form-select" id="carSelection" required>
                                        <option value="">Choose a car...</option>
                                        <optgroup label="Imported Cars">
                                            @foreach($importedCars as $car)
                                                <option value="imported_{{ $car->id }}">
                                                    {{ $car->make }} {{ $car->model }} ({{ $car->year }}) - {{ $car->chassis_number }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Trade-in Cars">
                                            @foreach($tradeInCars as $car)
                                                <option value="tradein_{{ $car->id }}">
                                                    {{ $car->make }} {{ $car->model }} ({{ $car->year }}) - {{ $car->registration_number }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="documentType" name="document_type" required>
                                        <option value="">Select Type</option>
                                        <option value="logbook">Logbook</option>
                                        <option value="registration">Registration Certificate</option>
                                        <option value="insurance">Insurance Document</option>
                                        <option value="service_record">Service Record</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Reference Number</label>
                                    <input type="text" class="form-control" id="referenceNumber" name="reference_number">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Document Date</label>
                                    <input type="date" class="form-control" id="documentDate" name="document_date">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" id="expiryDate" name="expiry_date">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="archived">Archived</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Issued By</label>
                            <input type="text" class="form-control" id="issuedBy" name="issued_by">
                        </div>
                        
                        <div class="mb-3" id="documentsSection">
                            <label class="form-label">Upload Documents</label>
                            <input type="file" class="form-control" id="documents" name="documents[]" multiple 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                            <div class="form-text">PDF, DOC, DOCX, JPG, PNG, GIF files only. Max 50MB per file.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>

                        <!-- Hidden fields for car IDs -->
                        <input type="hidden" id="customerId" name="customer_id">
                        <input type="hidden" id="importedId" name="imported_id">
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewContent">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Modal -->
    <div class="modal fade" id="documentsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 id="docCarDetails" class="mb-0"></h6>
                            <small class="text-muted" id="docLogbookTitle"></small>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="openUploadModal()">
                            <i class="fas fa-upload me-1"></i> Upload More
                        </button>
                    </div>
                    <div id="documentsContainer">
                        <!-- Documents loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Documents Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Files</label>
                            <input type="file" class="form-control" name="documents[]" multiple required 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let currentLogbookId = null;
        const csrfToken = '{{ csrf_token() }}';

        // Toast notification
        function showToast(message, type = 'success') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        }

        // Open create modal
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Add New Logbook';
            document.getElementById('method').value = 'POST';
            document.getElementById('logbookForm').reset();
            document.getElementById('logbookId').value = '';
            document.getElementById('customerId').value = '';
            document.getElementById('importedId').value = '';
            document.getElementById('documentsSection').style.display = 'block';
            document.getElementById('documents').setAttribute('required', 'required');
            new bootstrap.Modal(document.getElementById('logbookModal')).show();
        }

        // View logbook
        function viewLogbook(id) {
            fetch(`/logbooks/${id}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const logbook = data.logbook;
                    document.getElementById('viewContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Car:</strong> ${logbook.car_details}</p>
                                <p><strong>Type:</strong> <span class="badge bg-info">${logbook.formatted_document_type}</span></p>
                                <p><strong>Title:</strong> ${logbook.title}</p>
                                <p><strong>Reference:</strong> ${logbook.reference_number || 'N/A'}</p>
                                <p><strong>Issued By:</strong> ${logbook.issued_by || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Document Date:</strong> ${logbook.document_date || 'N/A'}</p>
                                <p><strong>Expiry Date:</strong> ${logbook.expiry_date || 'No Expiry'}</p>
                                <p><strong>Status:</strong> <span class="badge bg-success">${logbook.status}</span></p>
                                <p><strong>Documents:</strong> ${logbook.document_count} files</p>
                                <p><strong>Created:</strong> ${logbook.created_at}</p>
                            </div>
                        </div>
                        ${logbook.description ? `<div class="row"><div class="col-12"><p><strong>Description:</strong></p><div class="border rounded p-3 bg-light">${logbook.description}</div></div></div>` : ''}
                        ${logbook.notes ? `<div class="row"><div class="col-12"><p><strong>Notes:</strong></p><div class="border rounded p-3 bg-light">${logbook.notes}</div></div></div>` : ''}
                    `;
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                }
            })
            .catch(error => {
                showToast('Failed to load logbook details', 'error');
            });
        }

        // Edit logbook
        function editLogbook(id) {
            fetch(`/logbooks/${id}/edit`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const logbook = data.logbook;
                    
                    document.getElementById('modalTitle').textContent = 'Edit Logbook';
                    document.getElementById('method').value = 'PUT';
                    document.getElementById('logbookId').value = logbook.id;
                    
                    // Populate form
                    document.getElementById('title').value = logbook.title || '';
                    document.getElementById('description').value = logbook.description || '';
                    document.getElementById('documentType').value = logbook.document_type || '';
                    document.getElementById('referenceNumber').value = logbook.reference_number || '';
                    document.getElementById('documentDate').value = logbook.document_date || '';
                    document.getElementById('expiryDate').value = logbook.expiry_date || '';
                    document.getElementById('issuedBy').value = logbook.issued_by || '';
                    document.getElementById('notes').value = logbook.notes || '';
                    document.getElementById('status').value = logbook.status || '';
                    
                    // Set car selection
                    if (logbook.customer_id) {
                        document.getElementById('carSelection').value = `tradein_${logbook.customer_id}`;
                        document.getElementById('customerId').value = logbook.customer_id;
                        document.getElementById('importedId').value = '';
                    } else if (logbook.imported_id) {
                        document.getElementById('carSelection').value = `imported_${logbook.imported_id}`;
                        document.getElementById('importedId').value = logbook.imported_id;
                        document.getElementById('customerId').value = '';
                    }
                    
                    // Hide documents section for edit
                    document.getElementById('documentsSection').style.display = 'none';
                    document.getElementById('documents').removeAttribute('required');
                    
                    new bootstrap.Modal(document.getElementById('logbookModal')).show();
                }
            })
            .catch(error => {
                showToast('Failed to load logbook for editing', 'error');
            });
        }

        // Delete logbook
        function deleteLogbook(id) {
            Swal.fire({
                title: 'Delete Logbook?',
                text: 'This action cannot be undone and will delete all associated documents.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/logbooks/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Logbook deleted successfully!');
                            document.getElementById(`logbook-${id}`).remove();
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        showToast(error.message, 'error');
                    });
                }
            });
        }

        // Manage documents
        function manageDocuments(id) {
            currentLogbookId = id;
            
            // Load logbook details for header
            fetch(`/logbooks/${id}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('docCarDetails').textContent = data.logbook.car_details;
                    document.getElementById('docLogbookTitle').textContent = data.logbook.title;
                }
            });
            
            loadDocuments(id);
            new bootstrap.Modal(document.getElementById('documentsModal')).show();
        }

        // Load documents
        function loadDocuments(id) {
            document.getElementById('documentsContainer').innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div></div>';
            
            fetch(`/logbooks/${id}/documents`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDocuments(data.documents);
                } else {
                    document.getElementById('documentsContainer').innerHTML = '<div class="text-center py-4 text-muted">No documents found</div>';
                }
            })
            .catch(() => {
                document.getElementById('documentsContainer').innerHTML = '<div class="text-center py-4 text-muted">Failed to load documents</div>';
            });
        }

        // Display documents
        function displayDocuments(documents) {
            if (!documents.length) {
                document.getElementById('documentsContainer').innerHTML = '<div class="text-center py-4 text-muted">No documents uploaded yet</div>';
                return;
            }

            const html = documents.map((doc, index) => `
                <div class="document-item d-flex align-items-center">
                    <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${doc.name}</h6>
                        <small class="text-muted">${doc.type || 'Document'}</small>
                    </div>
                    <div class="btn-group">
                        <a href="${doc.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> View
                        </a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDocument(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');

            document.getElementById('documentsContainer').innerHTML = html;
        }

        // Open upload modal
        function openUploadModal() {
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        }

        // Delete document
        function deleteDocument(index) {
            Swal.fire({
                title: 'Delete Document?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/logbooks/${currentLogbookId}/documents/${index}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Document deleted successfully!');
                            loadDocuments(currentLogbookId);
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        showToast(error.message, 'error');
                    });
                }
            });
        }

        // Document ready initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Handle car selection
            document.getElementById('carSelection').addEventListener('change', function() {
                const value = this.value;
                const customerIdInput = document.getElementById('customerId');
                const importedIdInput = document.getElementById('importedId');
                
                if (value.startsWith('imported_')) {
                    importedIdInput.value = value.replace('imported_', '');
                    customerIdInput.value = '';
                } else if (value.startsWith('tradein_')) {
                    customerIdInput.value = value.replace('tradein_', '');
                    importedIdInput.value = '';
                } else {
                    customerIdInput.value = '';
                    importedIdInput.value = '';
                }
            });

            // Form submission
            document.getElementById('logbookForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitBtn');
                const formData = new FormData(this);
                const logbookId = document.getElementById('logbookId').value;
                const method = document.getElementById('method').value;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                
                let url = '/logbooks';
                if (method === 'PUT' && logbookId) {
                    url = `/logbooks/${logbookId}`;
                    formData.append('_method', 'PUT');
                }
                
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Operation completed successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('logbookModal')).hide();
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Operation failed');
                    }
                })
                .catch(error => {
                    showToast(error.message, 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Save Logbook';
                });
            });

            // Upload documents
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch(`/logbooks/${currentLogbookId}/upload-documents`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Documents uploaded successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                        this.reset();
                        loadDocuments(currentLogbookId);
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    showToast(error.message, 'error');
                });
            });
        });
    </script>

    <style>
        .btn-group-sm .btn {
            margin: 0 1px;
        }
        .document-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }
        .document-item:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        .badge {
            font-size: 0.75em;
        }
        .modal-lg {
            max-width: 900px;
        }
        .modal-xl {
            max-width: 1200px;
        }
    </style>
</x-app-layout>