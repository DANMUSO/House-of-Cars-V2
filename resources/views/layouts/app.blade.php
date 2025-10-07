<!DOCTYPE html>
<html lang="en">

    
<!-- Mirrored from zoyothemes.com/hando/html/ by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 22 Apr 2025 19:48:10 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<head>


        <meta charset="utf-8" />
        <title>House of Cars System Management</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc."/>
        <meta name="author" content="Zoyothemes"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />


        <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset('dashboardv1/assets/images/houseofcars.png')}}">

        <!-- App css -->
        <link href="{{asset('dashboardv1/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <!-- Icons -->
        <link href="{{asset('dashboardv1/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />

        <script src="{{asset('dashboardv1/assets/js/head.js')}}"></script>

        <meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Add these CDN links for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <!-- jQuery (required) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
/* Existing styles... keep all your current styles and ADD these */

/* Professional Agreement Document Styles */
.professional-agreement {
    background: white !important;
    font-family: 'Times New Roman', Times, serif !important;
    line-height: 1.6 !important;
    color: #000 !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: 210mm !important;
    margin: 0 auto !important;
}

/* Letterhead */
.letterhead {
    border-bottom: 3px solid #2c5282 !important;
    padding-bottom: 20px !important;
    margin-bottom: 30px !important;
}

.company-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.logo-section {
    flex: 0 0 150px !important;
}

.letterhead-logo {
    max-width: 150px !important;
    max-height: 80px !important;
}

.company-info {
    flex: 1 !important;
    text-align: center !important;
}

.company-name {
    font-size: 24px !important;
    font-weight: bold !important;
    color: #2c5282 !important;
    margin: 0 0 10px 0 !important;
    letter-spacing: 1px !important;
}

.company-details p {
    margin: 2px 0 !important;
    font-size: 14px !important;
    color: #4a5568 !important;
}

/* Document Title */
.document-title {
    text-align: center !important;
    margin: 40px 0 !important;
    border-bottom: 2px solid #e2e8f0 !important;
    padding-bottom: 20px !important;
}

.document-title h1 {
    font-size: 28px !important;
    font-weight: bold !important;
    text-decoration: underline !important;
    margin: 0 0 15px 0 !important;
    color: #2d3748 !important;
    letter-spacing: 2px !important;
}

.agreement-number {
    font-size: 16px !important;
    font-weight: bold !important;
    color: #4a5568 !important;
    margin-bottom: 5px !important;
}

.agreement-date {
    font-size: 14px !important;
    color: #718096 !important;
}

/* Section Styling */
.section-title {
    font-size: 18px !important;
    font-weight: bold !important;
    color: #2d3748 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    border-bottom: 1px solid #cbd5e0 !important;
    padding-bottom: 8px !important;
    margin: 30px 0 20px 0 !important;
}

.subsection-title {
    font-size: 16px !important;
    font-weight: bold !important;
    color: #4a5568 !important;
    margin: 20px 0 15px 0 !important;
}

/* Parties Section */
.parties-section {
    margin: 30px 0 !important;
}

.party-block {
    margin: 25px 0 !important;
    page-break-inside: avoid !important;
}

.party-heading {
    font-size: 16px !important;
    font-weight: bold !important;
    color: #2d3748 !important;
    margin: 15px 0 10px 0 !important;
}

.highlighted-party {
    background-color: #f7fafc !important;
    border: 2px solid #e2e8f0 !important;
    border-left: 5px solid #3182ce !important;
    padding: 20px !important;
    border-radius: 5px !important;
}

.info-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 10px 0 !important;
}

.info-table td {
    padding: 8px 12px !important;
    border-bottom: 1px solid #e2e8f0 !important;
    vertical-align: top !important;
}

.label-col {
    font-weight: bold !important;
    color: #4a5568 !important;
    width: 35% !important;
}

.value-col {
    color: #2d3748 !important;
}

.buyer-name {
    font-size: 18px !important;
    font-weight: bold !important;
    color: #2c5282 !important;
}

.party-designation {
    font-style: italic !important;
    color: #718096 !important;
    margin: 15px 0 5px 0 !important;
    text-align: center !important;
}

/* Vehicle Section */
.vehicle-section {
    margin: 30px 0 !important;
}

.highlighted-section {
    background-color: #f7fafc !important;
    border: 2px solid #e2e8f0 !important;
    border-left: 5px solid #38a169 !important;
    padding: 20px !important;
    border-radius: 5px !important;
}

.specification-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 15px 0 !important;
}

.specification-table td {
    padding: 10px 15px !important;
    border: 1px solid #e2e8f0 !important;
}

.spec-label {
    background-color: #edf2f7 !important;
    font-weight: bold !important;
    color: #4a5568 !important;
    width: 40% !important;
}

.spec-value {
    color: #2d3748 !important;
    font-weight: 500 !important;
}

/* Financial Section */
.financial-section {
    margin: 30px 0 !important;
}

.financial-details {
    background-color: #f7fafc !important;
    border: 2px solid #e2e8f0 !important;
    border-left: 5px solid #d69e2e !important;
    padding: 20px !important;
    border-radius: 5px !important;
}

.financial-clause {
    margin: 15px 0 !important;
    text-align: justify !important;
}

.financial-clause p {
    margin: 10px 0 !important;
}

.forfeit-clause {
    background-color: #fed7d7 !important;
    border: 1px solid #fc8181 !important;
    border-left: 4px solid #e53e3e !important;
    padding: 15px !important;
    margin: 20px 0 !important;
    border-radius: 5px !important;
}

/* Terms Section */
.terms-section {
    margin: 30px 0 !important;
}

.term-clause {
    margin: 20px 0 !important;
    text-align: justify !important;
}

.term-clause h4 {
    font-size: 14px !important;
    font-weight: bold !important;
    color: #2d3748 !important;
    margin: 15px 0 8px 0 !important;
}

.term-clause p {
    margin: 8px 0 !important;
    text-indent: 0 !important;
}

/* Execution Section */
.execution-section {
    margin: 40px 0 !important;
    page-break-inside: avoid !important;
}

.execution-text {
    font-weight: bold !important;
    text-align: center !important;
    margin: 20px 0 !important;
}

.execution-table {
    margin: 20px auto !important;
}

.execution-table td {
    padding: 5px 10px !important;
    vertical-align: bottom !important;
}

.execution-line {
    border-bottom: 1px solid #000 !important;
    min-width: 200px !important;
    text-align: center !important;
}

.execution-line-short {
    border-bottom: 1px solid #000 !important;
    min-width: 50px !important;
    text-align: center !important;
}

.execution-line-medium {
    border-bottom: 1px solid #000 !important;
    min-width: 120px !important;
    text-align: center !important;
}

.signatures-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 50px !important;
    margin: 40px 0 !important;
}

.signature-block {
    text-align: center !important;
    page-break-inside: avoid !important;
}

.signature-block h4 {
    font-size: 16px !important;
    font-weight: bold !important;
    color: #2d3748 !important;
    margin-bottom: 30px !important;
    text-decoration: underline !important;
}

.signature-area {
    margin: 30px 0 !important;
}

.signature-line {
    border-bottom: 2px solid #000 !important;
    width: 200px !important;
    height: 40px !important;
    margin: 0 auto 10px auto !important;
}

.signature-label {
    font-size: 12px !important;
    color: #718096 !important;
    margin: 5px 0 !important;
}

.signatory-details p {
    margin: 8px 0 !important;
    text-align: left !important;
}

/* Payment Instructions */
.payment-instructions {
    margin: 40px 0 !important;
    border-top: 3px solid #2c5282 !important;
    padding-top: 20px !important;
}

.payment-notice {
    text-align: center !important;
    font-weight: bold !important;
    color: #e53e3e !important;
    margin: 20px 0 !important;
    font-size: 16px !important;
}

.payment-methods-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 30px !important;
    margin: 30px 0 !important;
}

.payment-method {
    border: 2px solid #e2e8f0 !important;
    padding: 20px !important;
    border-radius: 8px !important;
    background-color: #f7fafc !important;
}

.payment-method h4 {
    text-align: center !important;
    font-weight: bold !important;
    color: #2d3748 !important;
    margin-bottom: 15px !important;
    text-decoration: underline !important;
}

.payment-details-table {
    width: 100% !important;
    border-collapse: collapse !important;
}

.payment-details-table td {
    padding: 8px 12px !important;
    border-bottom: 1px solid #e2e8f0 !important;
}

.payment-details-table td:first-child {
    font-weight: bold !important;
    color: #4a5568 !important;
    width: 45% !important;
}

.payment-details-table td:last-child {
    color: #2d3748 !important;
    font-weight: 500 !important;
}

/* Print Styles */
@media print {
    .professional-agreement {
        box-shadow: none !important;
        border: none !important;
        padding: 15mm !important;
        font-size: 12pt !important;
        line-height: 1.4 !important;
    }
    
    .section-title {
        page-break-after: avoid !important;
    }
    
    .party-block,
    .signature-block,
    .execution-section,
    .payment-instructions {
        page-break-inside: avoid !important;
    }
    
    .signatures-grid {
        page-break-before: avoid !important;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .company-header {
        flex-direction: column !important;
        text-align: center !important;
    }
    
    .signatures-grid,
    .payment-methods-grid {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }
    
    .professional-agreement {
        padding: 20px !important;
    }
}
</style>
    <style>
                                                                .pdf-button {
                                                                padding: 10px 15px;
                                                                background-color: #e74c3c;
                                                                color: white;
                                                                border: none;
                                                                border-radius: 4px;
                                                                cursor: pointer;
                                                                font-size: 14px;
                                                                transition: all 0.3s ease;
                                                                display: inline-flex;
                                                                align-items: center;
                                                                gap: 8px;
                                                                }

                                                                .pdf-button:hover {
                                                                background-color: #c0392b;
                                                                transform: translateY(-2px);
                                                                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                                                                }

                                                                .pdf-button:disabled {
                                                                background-color: #95a5a6;
                                                                cursor: not-allowed;
                                                                transform: none;
                                                                box-shadow: none;
                                                                }

                                                                /* Ensure all elements are visible for PDF */
                                                                .inspection-container {
                                                                background-color: white !important;
                                                                color: black !important;
                                                                }

                                                                .status-badge {
                                                                display: inline-block !important;
                                                                padding: 2px 8px !important;
                                                                border-radius: 10px !important;
                                                                font-size: 12px !important;
                                                                font-weight: bold !important;
                                                                }

                                                                </style>
  <style>
    .gate-pass-card {
      max-width: 600px;
      margin: 40px auto;
      border: 2px solid #000;
      border-radius: 15px;
      padding: 20px;
    }
    .gate-pass-header {
      background-color: #0d6efd;
      color: white;
      padding: 10px 20px;
      border-radius: 10px 10px 0 0;
      text-align: center;
    }
    .gate-pass-body {
      padding: 20px;
    }
    .label {
      font-weight: 600;
    }
    /* Add this to your stylesheet */
.img-hover-zoom {
    transition: transform 0.3s ease; /* Smooth transition */
    overflow: hidden; /* Ensures smooth scaling */
}

.img-hover-zoom:hover {
    transform: scale(1.05); /* Slightly zoom in (5%) */
}
.section-title {
        font-size: 1.2rem;
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 10px;
        color: #333;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
    }
    .submit-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        border-radius: 5px;
    }
    .submit-btn:hover {
        background-color: #218838;
    }
    </style>
     <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        .inspection-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .company-details h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .company-details p {
            font-size: 13px;
            line-height: 1.4;
            opacity: 0.9;
            margin: 0;
        }

        .company-logo {
            display: flex;
            align-items: center;
        }

        .logo-placeholder {
            width: 100px;
            height: 100px;
            margin-right: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: bold;
            backdrop-filter: blur(10px);
        }

        .document-title {
            text-align: center;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            padding-top: 15px;
        }

        .document-title h2 {
            font-size: 24px;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .document-title p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .inventory-section {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .inventory-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .inventory-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inventory-item label {
            font-weight: 500;
            color: #495057;
        }

        .inventory-item input {
            border: none;
            border-bottom: 1px solid #ced4da;
            padding: 5px;
            width: 120px;
            text-align: center;
            background: transparent;
        }

        .conditions-section {
            padding: 20px;
        }

        .conditions-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
        }

        .conditions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .conditions-column {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .inspection-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            background: white;
            box-shadow: none;
        }

        .inspection-table th.item-header,
        .inspection-table th.status-header {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid #2c3e50;
            vertical-align: middle;
        }

        .inspection-table th.item-header {
            width: 60%;
            text-align: left;
        }

        .inspection-table th.status-header {
            width: 40%;
        }

        .inspection-table td {
            padding: 12px 20px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .item-name {
            background: #f8f9fa;
            font-weight: 500;
            color: #495057;
            text-align: left;
        }

        .item-status {
            background: #ffffff;
            text-align: center;
        }

        .inspection-table tr:nth-child(even) .item-name {
            background: #ffffff;
        }

        .inspection-table tr:nth-child(even) .item-status {
            background: #f8f9fa;
        }

        .inspection-table tr:hover .item-name,
        .inspection-table tr:hover .item-status {
            background: #e3f2fd;
        }

        .category-row {
            background: #6c757d;
        }

        .category-header {
            background: #495057 !important;
            color: white !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            text-align: center !important;
            padding: 10px 20px !important;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            cursor: default;
            display: inline-block;
            min-width: 70px;
        }

        .status-ok {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-damaged {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-present {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-absent {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .section-divider {
            background: #6c757d;
            height: 2px;
            margin: 0;
        }

        .category-header {
            background: #495057;
            color: white;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media print {
           
            .inspection-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .header {
                background: #2c3e50 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .company-info {
                display: flex !important;
                justify-content: space-between !important;
            }
            
            .logo-placeholder {
                background: rgba(255, 255, 255, 0.1) !important;
                border: 2px solid rgba(255, 255, 255, 0.3) !important;
                -webkit-print-color-adjust: exact;
            }
            
            .conditions-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 20px !important;
            }
            
            .conditions-column {
                box-shadow: none !important;
            }
            
            .inspection-table th {
                background: #34495e !important;
                -webkit-print-color-adjust: exact;
            }
            
            .status-badge {
                -webkit-print-color-adjust: exact;
            }
        }

        @media (max-width: 768px) {
            .conditions-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .inventory-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
        <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .conditions {
            margin-bottom: 30px;
        }
        .condition-item {
            margin-bottom: 15px;
            padding: 10px;
            color: #000;
            border-left: 4px solid #3498db;
        }
        .condition-number {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .authorization {
            background: #e8f5e8;
            border: 2px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            color: #000;
            border-radius: 5px;
        }
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        .signature-box {
            border: 1px solid #dee2e6;
            padding: 20px;
            background: #f8f9fa;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin: 20px 0 10px 0;
            height: 40px;
        }
        .label {
            font-weight: bold;
            color: #495057;
        }
       
        .dealer-info {
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            color: #000;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .signature-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
    </head>

    <!-- body start -->
    <body data-menu-color="light" data-sidebar="default">

        <!-- Begin page -->
        <div id="app-layout">
            
            <!-- Topbar Start -->
            <div class="topbar-custom">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between">
                        <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                            <li>
                                <button class="button-toggle-menu nav-link">
                                    <i data-feather="menu" class="noti-icon"></i>
                                </button>
                            </li>
                            <li class="d-none d-lg-block">
                                <h5 class="mb-0" id="greetingMessage"></h5>
                            </li>

                            

                        </ul>

                        <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                            <li class="d-none d-lg-block">
                                <form class="app-search d-none d-md-block me-auto">
                                    <div class="position-relative topbar-search">
                                        <input type="text" class="form-control ps-4" placeholder="Search..." />
                                        <i class="mdi mdi-magnify fs-16 position-absolute text-muted top-50 translate-middle-y ms-2"></i>
                                    </div>
                                </form>
                            </li>

                            <!-- Button Trigger Customizer Offcanvas -->
                            <li class="d-none d-sm-flex">
                                <button type="button" class="btn nav-link" data-toggle="fullscreen">
                                    <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
                                </button>
                            </li>

                            <!-- Light/Dark Mode Button Themes -->
                            <li class="d-none d-sm-flex">
                                <button type="button" class="btn nav-link" id="light-dark-mode">
                                    <i data-feather="moon" class="align-middle dark-mode"></i>
                                    <i data-feather="sun" class="align-middle light-mode"></i>
                                </button>
                            </li>
                           
                                        <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordLabel" aria-hidden="true" data-bs-backdrop="false">

                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title" id="resetPasswordLabel">Reset Password</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form id="resetPasswordForm">
                                                @csrf
                                                <div class="modal-body">
                                                <input type="hidden" name="user_id" id="reset_user_id">

                                                <div class="mb-3">
                                                    <label for="new_password" class="form-label">New Password</label>
                                                    <input type="password" class="form-control" name="password" id="new_password" required>
                                                </div>
                                                </div>
                                                <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Reset</button>
                                                </div>
                                            </form>
                                            </div>
                                        </div>
                                        </div>

                            <!-- User Dropdown -->
                            <li class="dropdown notification-list topbar-dropdown">
                                <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                   
                                    <span class="pro-user-name ms-1">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }} <i class="mdi mdi-chevron-down"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                                    <!-- item-->
                                    <div class="dropdown-header noti-title">
                                        <h6 class="text-overflow m-0">Welcome !</h6>
                                        <p>  {{ Auth::user()->email }}</p>
                                    </div>
                                      <div class="dropdown-header noti-title">
                                         <!-- Reset Password Modal -->
                                          <button class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#resetPasswordModal" 
                                                    data-user-id="{{ Auth::id() }}">
                                            Reset Password
                                            </button>


                                    </div>


                                    <div class="dropdown-divider">
                                      
                                    </div>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- end Topbar -->

            <!-- Left Sidebar Start -->
            <div class="app-sidebar-menu">
                <div class="h-100" data-simplebar>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <div class="logo-box">
                            <a class='logo logo-light' href='#'>
                                <span class="logo-sm">
                                    <img src="{{asset('dashboardv1/assets/images/hv1.png')}}" alt="" height="50">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{asset('dashboardv1/assets/images/hv1.png')}}" alt="" height="50">
                                </span>
                            </a>
                            <a class='logo logo-dark' href='#'>
                                <span class="logo-sm">
                                    <img src="{{asset('dashboardv1/assets/images/houseofcars.png')}}" alt="" height="100">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{asset('dashboardv1/assets/images/houseofcars.png')}}" alt="" height="100">
                                </span>
                            </a>
                        </div>

                        <ul id="side-menu">
                            @if(in_array(Auth::user()->role, ['Showroom-Manager','Managing-Director', 'Accountant','General-Manager']))
                            <li>
                                <a href="{{url('admin/dashboard')}}" >
                                    <i data-feather="home"></i>
                                    <span> Dashboard </span>
                                </a>
                            </li>
                            
                            <li>
                                <a href="#sidebarAuth" data-bs-toggle="collapse">
                                <i class="fa-solid fa-car"></i> 
                                    <span> Car Import</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAuth">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a class='tp-link' href="{{url('car-imports')}}">Car Bid</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('bid-deposit-confirmed')}}">Bid Deposit Confirmed</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('wonbids')}}">Won Bids</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('car-ready-for-shipping')}}">Cars Ready for Shipping</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('shipment-in-progress')}}">Shipment In Progress</a>
                                        </li>
                                        
                                        <li>
                                            <a class='tp-link' href="{{url('cunstom-duty-cleared')}}">Import Fees and Charges</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('in-transit-received-cars')}}">In Transit Cars</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('received-cars')}}">Received Cars</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('lostbids')}}">Lost Bids</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                             @endif
                             {{-- Fleet Acquisition only for Showroom-Manager + Salesperson --}}
                             @if(in_array(Auth::user()->role, ['Showroom-Manager','Managing-Director', 'Accountant','General-Manager']))
                            <li>
                                <a href="#sidebarAdvancedUI" data-bs-toggle="collapse">
                                <i class="fa-solid fa-truck-field"></i>
                                    <span>Fleet Acquisition</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAdvancedUI">
                                    <ul class="nav-second-level">
                                    <li>
                                            <a class='tp-link' href="{{url('fleetacquisition')}}">Fleet Acquisition</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(in_array(Auth::user()->role, ['Showroom-Manager','Managing-Director','General-Manager']))
                            <li>
                                <a href="#sidebarError" data-bs-toggle="collapse">
                                <i class="fa-solid fa-exchange-alt"></i>
                                    <span>Local Sell</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarError">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a class='tp-link' href="{{url('tradein')}}">Trade Inn | Sell In Behalf</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(in_array(Auth::user()->role, ['Showroom-Manager','Managing-Director','General-Manager']))
                            <li>
                                <a href="#sidebarExpages" data-bs-toggle="collapse">
                                <i class="fa-solid fa-clipboard-check"></i>
                                    <span>Inspection</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarExpages">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a class='tp-link' href="{{url('imported')}}">Imported Cars</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('tradeincars')}}">Trade Inn | Sell In Behalf</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(in_array(Auth::user()->role, ['Showroom-Manager','Managing-Director',  'Accountant','General-Manager' ]))
                            <li>
                                <a href="#sidebarBaseui" data-bs-toggle="collapse">
                                <i class="fa-solid fa-tag"></i>
                                    <span> Sells </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarBaseui">
                                    <ul class="nav-second-level">
                    
                                        <li>
                                            <a class='tp-link' href="{{url('incash')}}">In Cash</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('hire-purchase')}}">Hire Purchase</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('gentlemanagreement')}}">Gentleman Agreement</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('leads')}}">Leads</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('gatepasscard')}}">Gate Pass Card</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(in_array(Auth::user()->role, ['Salesperson','Sales-Supervisor']))
                            <li>
                                <a href="#sidebarBaseui" data-bs-toggle="collapse">
                                <i class="fa-solid fa-tag"></i>
                                    <span> Leads Mgt </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarBaseui">
                                    <ul class="nav-second-level">
                    
                                        <li>
                                            <a class='tp-link' href="{{url('leads')}}">Leads</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>
                             @endif
                            @if(in_array(Auth::user()->role, ['Yard-Supervisor']))
                            <li>
                                <a href="#sidebarBaseui1" data-bs-toggle="collapse">
                                <i class="fa-solid fa-tag"></i>
                                    <span>Gatepass Mgt </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarBaseui1">
                                    <ul class="nav-second-level">
                    
                                          <li>
                                            <a class='tp-link' href="{{url('gatepasscard')}}">Gate Pass Card</a>
                                        </li>

                                    </ul>
                                </div>
                            </li>
                             @endif
                            <li>
                                <a href="#sidebarAdvancedUIs" data-bs-toggle="collapse">
                                <i class="fa-solid fa-people-carry-box"></i>
                                    <span>Facilitation Requests</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAdvancedUIs">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a class='tp-link' href="{{url('/Facilitation/requests')}}">Requests</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="#sidebarIcons" data-bs-toggle="collapse">
                                <i class="fa-solid fa-calendar-check"></i>
                                    <span> Leave Requests </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarIcons">
                                    <ul class="nav-second-level">
                                    <li>
                                            <a class='tp-link' href="{{url('/Leaves/requests')}}">Requests</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                             @if(in_array(Auth::user()->role, ['Accountant','Managing-Director','General-Manager']))
                            <li>
                                <a href="#sidebarForm" data-bs-toggle="collapse">
                                <i class="fa-solid fa-chart-column"></i>
                                    <span> Docs Management </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarForm">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a class='tp-link' href="{{url('/logbooks')}}">Docs Management</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            @if(in_array(Auth::user()->role, ['Showroom-Manager','Managing-Director','General-Manager']))
                            <li>
                                <a href="#sidebarForms" data-bs-toggle="collapse">
                                <i class="fa-solid fa-chart-column"></i>
                                    <span> Reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarForms">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/biddedcarsreport')}}">Bidded Cars Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/shippinginprocessreport')}}">Shipping In Progress Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/shippedcarsreport')}}">Shipped Cars Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/portclearedcarsreport')}}">Port Cleared Cars Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/carsintransitreport')}}"> Car In Transit Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/deliveredcarsreport')}}">Delivered Cars Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/inspectedcarsreport')}}">Inspected Cars Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/tradeinreport')}}">Trade In Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/hirepurchasereport')}}">Hire Purchase Report</a>
                                        </li>
                                        <li>
                                            <a class='tp-link' href="{{url('/Reports/incashreport')}}">In Cash Report</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                            {{-- HR, Managing-Director and General-Manager --}}
                            @if(in_array(Auth::user()->role, ['HR','Managing-Director','General-Manager']))
                            <li>
                                <a href="#sidebarTables" data-bs-toggle="collapse">
                                    <i class="fa-solid fa-users"></i>
                                    <span> Users Management </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTables">
                                    <ul class="nav-second-level">
                                    <li>
                                            <a class='tp-link' href="{{url('users')}}">Users</a>
                                    </li>
                                    </ul>
                                </div>
                            </li>
                            @endif

                        </ul>
            
                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
            </div>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                <main>
                {{ $slot }}
                </main>
                    <!-- Start Content-->
                   
                </div> <!-- content -->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col fs-13 text-muted text-center">
                                &copy; <script>document.write(new Date().getFullYear())</script> - Implemented by <a href="https://qloudpointsolutions.com/" target="_blank" class="text-reset fw-semibold">Qloud Point Solutions Limited</a> 
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->

            </div>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->
       
        <!-- Vendor -->
        <script src="{{asset('dashboardv1/assets/libs/jquery/jquery.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/simplebar/simplebar.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/node-waves/waves.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/waypoints/lib/jquery.waypoints.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/jquery.counterup/jquery.counterup.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/feather-icons/feather.min.js')}}"></script>

        <!-- Apexcharts JS -->
        <script src="{{asset('dashboardv1/assets/libs/apexcharts/apexcharts.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/js/pages/analytics-dashboard.init.js')}}"></script>
        <!-- Widgets Init Js -->
        <!-- App js-->
        <script src="{{asset('dashboardv1/assets/js/app.js')}}"></script>
               

        <!-- Datatables js -->
        <script src="{{asset('dashboardv1/assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>

        <!-- dataTables.bootstrap5 -->
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>

        <!-- buttons.colVis -->
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-buttons/js/buttons.flash.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-buttons/js/buttons.print.min.js')}}"></script>

        <!-- buttons.bootstrap5 -->
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js')}}"></script>

        <!-- dataTables.keyTable -->
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js')}}"></script>

        <!-- dataTable.responsive -->
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}"></script>

        <!-- dataTables.select -->
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-select/js/dataTables.select.min.js')}}"></script>
        <script src="{{asset('dashboardv1/assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js')}}"></script>

        <!-- Datatable Demo App Js -->
        <script src="{{asset('dashboardv1/assets/js/pages/datatable.init.js')}}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <!-- Fleet Acquisition Complete AJAX Script -->
     <script>
$(document).ready(function () {
    // When opening modal, set user id
    $('#resetPasswordModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('user-id');
        $('#reset_user_id').val(userId);
    });

    // Handle form submit
    $('#resetPasswordForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('users.resetPassword') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                $('#resetPasswordModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            },
            error: function (xhr) {
                let errorMessage = "Something went wrong!";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });
});
</script>


        <script>
function testReschedulingRoute() {
    const agreementId = window.agreementData?.id || 1; // Use your actual agreement ID
    const lumpSumAmount = 50000; // Test amount
    
    const url = `/hire-purchase/rescheduling-options?agreement_id=${agreementId}&lump_sum_amount=${lumpSumAmount}`;
    
    console.log('Testing URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response OK:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.text(); // Get as text first to see raw response
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON:', data);
                
                if (data.error) {
                    alert('API Error: ' + data.error);
                } else {
                    alert('Route works! Check console for details.');
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                alert('Response is not valid JSON. Check console.');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Route Error: ' + error.message);
        });
}
</script>
<script>
// Check if we're on the Fleet Acquisition page
if (window.location.pathname === '/fleetacquisition' || window.location.pathname.includes('fleetacquisition')) {
    
    // Fleet Acquisition AJAX Functions
    $(document).ready(function() {
        
        // Initialize DataTable
        if (typeof $.fn.DataTable !== 'undefined' && $('#responsive-datatable').length) {
            $('#responsive-datatable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [1, -1] }
                ]
            });
        }

        // Submit Fleet Acquisition Form
        $('#FleetAcquisitionForm').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            
            const formData = new FormData(this);
            
            const fileInput = this.querySelector('input[name="vehicle_photos[]"]');
            if (fileInput && fileInput.files.length > 0) {
                if (!validateImageTypes(fileInput.files) || !validateFileSize(fileInput.files)) {
                    submitBtn.html(originalText).prop('disabled', false);
                    return;
                }
            }
            
            $.ajax({
                url: "/fleetacquisition",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#addModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while saving the record.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMessage += '• ' + errors[field][0] + '\n';
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        text: errorMessage,
                        width: '600px'
                    });
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // View Fleet Details
        $(document).on('click', '.view-fleet-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            
            $.ajax({
                url: `/fleetacquisition/${id}`,
                method: 'GET',
                success: function(response) {
                    if(response.success) {
                        displayFleetDetails(response.data);
                        $('#viewModal').modal('show');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Unable to load fleet details.'
                    });
                }
            });
        });

        // Edit Fleet Acquisition
        $(document).on('click', '.edit-fleet-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            
            $.ajax({
                url: `/fleetacquisition/${id}`,
                method: 'GET',
                success: function(response) {
                    if(response.success) {
                        populateEditForm(response.data);
                        $('#editModal').modal('show');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Unable to load fleet details for editing.'
                    });
                }
            });
        });

        // Update Fleet Acquisition Form
        $('#updateFleetForm').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#editRecordId').val();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
            
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            
            const fileInput = this.querySelector('input[name="vehicle_photos[]"]');
            if (fileInput && fileInput.files.length > 0) {
                if (!validateImageTypes(fileInput.files) || !validateFileSize(fileInput.files)) {
                    submitBtn.html(originalText).prop('disabled', false);
                    return;
                }
            }
            
            $.ajax({
                url: `/fleetacquisition/${id}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#editModal').modal('hide');
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while updating the record.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'Validation errors:\n';
                        for (let field in errors) {
                            errorMessage += '• ' + errors[field][0] + '\n';
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Error!',
                        text: errorMessage,
                        width: '600px'
                    });
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Approve Fleet Acquisition
        $(document).on('click', '.approve-fleet-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const button = $(this);
            
            Swal.fire({
                title: 'Approve Fleet Acquisition?',
                text: 'Are you sure you want to approve this fleet acquisition?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.html('<i class="fas fa-spinner fa-spin"></i> Approving...').prop('disabled', true);
                    
                    const formData = new FormData();
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    
                    $.ajax({
                        url: `/fleetacquisition/${id}/approve`,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if(response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Approved!',
                                    text: response.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Unable to approve fleet acquisition.'
                            });
                            button.html('<i class="fas fa-check"></i> Approve').prop('disabled', false);
                        }
                    });
                }
            });
        });

        // Record Payment
        $(document).on('click', '.record-payment-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            $('#paymentFleetId').val(id);
            $('#paymentDate').val(new Date().toISOString().split('T')[0]);
            $('#paymentModal').modal('show');
        });

        
        // Delete Fleet Acquisition
        $(document).on('click', '.delete-fleet-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const button = $(this);
            
            Swal.fire({
                title: 'Delete Fleet Acquisition?',
                text: 'This action cannot be undone! All associated data will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
                    
                    const formData = new FormData();
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('_method', 'DELETE');
                    
                    $.ajax({
                        url: `/fleetacquisition/${id}`,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if(response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Error!',
                                text: xhr.responseJSON?.message || 'Unable to delete fleet acquisition.'
                            });
                            button.html('<i class="fas fa-trash"></i> Delete').prop('disabled', false);
                        }
                    });
                }
            });
        });

        // Photo Gallery Functionality
        $(document).on('click', '.fleet-photo-thumb', function() {
            const photos = JSON.parse($(this).attr('data-photos'));
            const currentIndex = parseInt($(this).attr('data-current'));
            
            loadPhotoCarousel(photos, currentIndex);
            $('#photoModal').modal('show');
        });

        // Delete Individual Photo
        $(document).on('click', '.delete-photo-btn', function(e) {
            e.preventDefault();
            const fleetId = $(this).data('fleet-id');
            const photoIndex = $(this).data('photo-index');
            const photoElement = $(this).closest('.photo-item');
            
            Swal.fire({
                title: 'Delete Photo?',
                text: 'Are you sure you want to delete this photo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('photo_index', photoIndex);
                    
                    $.ajax({
                        url: `/fleetacquisition/${fleetId}/delete-photo`,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if(response.success) {
                                photoElement.fadeOut(300, function() {
                                    $(this).remove();
                                });
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Photo deleted successfully.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Unable to delete photo.'
                            });
                        }
                    });
                }
            });
        });

        // Form Reset on Modal Close
        $('#addModal').on('hidden.bs.modal', function() {
            $('#FleetAcquisitionForm')[0].reset();
            $('#FleetAcquisitionForm').find('.is-invalid').removeClass('is-invalid');
            $('#FleetAcquisitionForm').find('.invalid-feedback').remove();
            $('#FleetAcquisitionForm').find('.financial-preview').remove();
            $('.photo-preview').remove();
        });

        $('#editModal').on('hidden.bs.modal', function() {
            $('#updateFleetForm')[0].reset();
            $('#updateFleetForm').find('.is-invalid').removeClass('is-invalid');
            $('#updateFleetForm').find('.invalid-feedback').remove();
            $('#updateFleetForm').find('.financial-preview').remove();
            $('#currentPhotos').empty();
            $('.photo-preview').remove();
        });

        $('#paymentModal').on('hidden.bs.modal', function() {
            $('#recordPaymentForm')[0].reset();
            $('#recordPaymentForm').find('.is-invalid').removeClass('is-invalid');
            $('#recordPaymentForm').find('.invalid-feedback').remove();
        });

        // Calculate financial details automatically
        $('#FleetAcquisitionForm, #updateFleetForm').on('input', 'input[name="purchase_price"], input[name="down_payment"], input[name="interest_rate"], select[name="loan_duration_months"]', function() {
            calculateFinancials($(this).closest('form'));
        });

        // Photo upload preview
        $(document).on('change', 'input[name="vehicle_photos[]"]', function() {
            const files = this.files;
            let previewContainer = $(this).closest('.row').find('.photo-preview');
            
            if (previewContainer.length === 0) {
                $(this).after('<div class="photo-preview mt-2 d-flex flex-wrap gap-2"></div>');
                previewContainer = $(this).closest('.row').find('.photo-preview');
            }
            
            previewContainer.empty();
            
            if (files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewContainer.append(`
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-thumbnail" 
                                         style="width: 80px; height: 60px; object-fit: cover;">
                                    <small class="position-absolute bottom-0 start-0 bg-dark text-white px-1">${index + 1}</small>
                                </div>
                            `);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });

        // Auto-uppercase certain fields
        $(document).on('input', 'input[name="chassis_number"], input[name="engine_number"], input[name="registration_number"], input[name="company_kra_pin"], input[name="hp_agreement_number"]', function() {
            this.value = this.value.toUpperCase();
        });

        // Format number inputs
        $(document).on('blur', 'input[type="number"][step="0.01"]', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });

        // Prevent form submission on Enter key in number inputs
        $(document).on('keypress', 'input[type="number"]', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).blur();
            }
        });

    });
}

// Helper Functions (Available globally)
function populateEditForm(fleet) {
    $('#editRecordId').val(fleet.id);
    $('#editVehicleMake').val(fleet.vehicle_make);
    $('#editVehicleModel').val(fleet.vehicle_model);
    $('#editVehicleYear').val(fleet.vehicle_year);
    $('#editEngineCapacity').val(fleet.engine_capacity);
    $('#editVehicleCategory').val(fleet.vehicle_category);
    $('#editChassisNumber').val(fleet.chassis_number);
    $('#editEngineNumber').val(fleet.engine_number);
    $('#editRegistrationNumber').val(fleet.registration_number);
    $('#editPurchasePrice').val(fleet.purchase_price);
    $('#editMarketValue').val(fleet.market_value);
    $('#editHpAgreementNumber').val(fleet.hp_agreement_number);
    $('#editLogbookCustody').val(fleet.logbook_custody);
    $('#editCompanyKraPin').val(fleet.company_kra_pin);
    $('#editInsurancePolicyNumber').val(fleet.insurance_policy_number);
    $('#editInsuranceCompany').val(fleet.insurance_company);
    
    // Handle insurance expiry date
    if (fleet.insurance_expiry_date) {
        let insuranceDate = fleet.insurance_expiry_date;
        if (insuranceDate.includes('T')) {
            insuranceDate = insuranceDate.split('T')[0];
        }
        $('#editInsuranceExpiryDate').val(insuranceDate);
    } else {
        $('#editInsuranceExpiryDate').val(''); // Clear if no date
    }
    
    $('#editBusinessPermitNumber').val(fleet.business_permit_number);
    $('#editInsurancePremium').val(fleet.insurance_premium);
    $('#editFinancingInstitution').val(fleet.financing_institution);
    $('#editFinancierContactPerson').val(fleet.financier_contact_person);
    $('#editFinancierPhone').val(fleet.financier_phone);
    $('#editFinancierEmail').val(fleet.financier_email);
    $('#editFinancierAgreementRef').val(fleet.financier_agreement_ref);
    $('#editDownPayment').val(fleet.down_payment);
    $('#editInterestRate').val(fleet.interest_rate);
    $('#editLoanDuration').val(fleet.loan_duration_months);
    
    // Handle first payment date - ensure proper format
    if (fleet.first_payment_date) {
        // Convert date to YYYY-MM-DD format if needed
        let paymentDate = fleet.first_payment_date;
        if (paymentDate.includes('T')) {
            paymentDate = paymentDate.split('T')[0];
        }
        $('#editFirstPaymentDate').val(paymentDate);
    }
    
    displayCurrentPhotos(fleet.vehicle_photos, fleet.id);
}

function displayCurrentPhotos(photos, fleetId) {
    const photosContainer = $('#currentPhotos');
    photosContainer.empty();

    if (photos && photos.length > 0) {
        photos.forEach((photo, index) => {
            const photoHtml = `
                <div class="photo-item position-relative">
                    <img src="/storage/${photo}" class="img-thumbnail" 
                         style="width: 100px; height: 80px; object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 delete-photo-btn"
                            data-fleet-id="${fleetId}" data-photo-index="${index}"
                            style="padding: 2px 6px; font-size: 10px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            photosContainer.append(photoHtml);
        });
    } else {
        photosContainer.html('<p class="text-muted">No photos available</p>');
    }
}

function displayFleetDetails(fleet) {
    // Vehicle Information
    $('#viewVehicleMakeModel').text(`${fleet.vehicle_make} ${fleet.vehicle_model}`);
    $('#viewVehicleYear').text(fleet.vehicle_year);
    $('#viewEngineCapacity').text(fleet.engine_capacity);
    $('#viewVehicleCategory').text(fleet.vehicle_category.charAt(0).toUpperCase() + fleet.vehicle_category.slice(1));
    $('#viewChassisNumber').text(fleet.chassis_number);
    $('#viewEngineNumber').text(fleet.engine_number);
    $('#viewRegistrationNumber').text(fleet.registration_number || 'Not registered');

    // Financial Details
    $('#viewPurchasePrice').text(`KSh ${parseFloat(fleet.purchase_price).toLocaleString()}`);
    $('#viewDownPayment').text(`KSh ${parseFloat(fleet.down_payment).toLocaleString()}`);
    $('#viewMonthlyInstallment').text(`KSh ${parseFloat(fleet.monthly_installment).toLocaleString()}`);
    $('#viewTotalAmount').text(`KSh ${parseFloat(fleet.total_amount_payable).toLocaleString()}`);
    $('#viewAmountPaid').text(`KSh ${parseFloat(fleet.amount_paid || 0).toLocaleString()}`);
    $('#viewOutstandingBalance').text(`KSh ${parseFloat(fleet.outstanding_balance).toLocaleString()}`);
    $('#viewInterestRate').text(`${fleet.interest_rate}%`);
    $('#viewDuration').text(`${fleet.loan_duration_months} months`);

    // Status & Progress
    const statusBadgeClass = getStatusBadgeClass(fleet.status);
    $('#viewStatus').removeClass().addClass(`badge ${statusBadgeClass}`).text(fleet.status.charAt(0).toUpperCase() + fleet.status.slice(1));
    
    // Calculate paid percentage properly
    let paidPercentage = 0;
    if (fleet.total_amount_payable > 0 && fleet.amount_paid > 0) {
        paidPercentage = (fleet.amount_paid / fleet.total_amount_payable) * 100;
    } else if (fleet.paid_percentage) {
        paidPercentage = fleet.paid_percentage;
    }
    
    $('#viewProgressBar').css('width', `${paidPercentage}%`).attr('aria-valuenow', paidPercentage).text(`${parseFloat(paidPercentage).toFixed(1)}%`);
    $('#viewPaymentsMade').text(`Payments Made: ${fleet.payments_made || 0} of ${fleet.loan_duration_months}`);

    // Legal & Compliance
    $('#viewHpAgreement').text(fleet.hp_agreement_number);
    $('#viewLogbookCustody').text(fleet.logbook_custody.charAt(0).toUpperCase() + fleet.logbook_custody.slice(1));
    $('#viewCompanyKraPin').text(fleet.company_kra_pin);
    $('#viewInsurancePolicy').text(fleet.insurance_policy_number || 'Not provided');
    $('#viewInsuranceCompany').text(fleet.insurance_company || 'Not provided');

    // Financier Information
    $('#viewFinancingInstitution').text(fleet.financing_institution);
    $('#viewFinancierContact').text(fleet.financier_contact_person || 'Not provided');
    $('#viewFinancierPhone').text(fleet.financier_phone || 'Not provided');
    $('#viewFinancierEmail').text(fleet.financier_email || 'Not provided');

    // Vehicle Photos
    const photosContainer = $('#viewVehiclePhotos');
    photosContainer.empty();
    
    if (fleet.vehicle_photos && fleet.vehicle_photos.length > 0) {
        fleet.vehicle_photos.forEach((photo, index) => {
            photosContainer.append(`
                <div class="col-md-3 mb-2">
                    <img src="/storage/${photo}" class="img-fluid rounded" alt="Vehicle Photo" 
                         style="height: 150px; object-fit: cover; width: 100%; cursor: pointer;"
                         onclick="openPhotoModal(['${fleet.vehicle_photos.join("','")}'], ${index})">
                </div>
            `);
        });
    } else {
        photosContainer.html('<p class="text-muted">No photos available</p>');
    }
}

function loadPhotoCarousel(photos, startIndex = 0) {
    const carouselInner = $('#carouselInner');
    carouselInner.empty();

    photos.forEach((photo, index) => {
        const activeClass = index === startIndex ? 'active' : '';
        const carouselItem = `
            <div class="carousel-item ${activeClass}">
                <img src="/storage/${photo}" class="d-block w-100" alt="Vehicle Photo"
                     style="height: 500px; object-fit: contain;">
            </div>
        `;
        carouselInner.append(carouselItem);
    });
}

function openPhotoModal(photos, startIndex = 0) {
    loadPhotoCarousel(photos, startIndex);
    $('#photoModal').modal('show');
}

function getStatusBadgeClass(status) {
    const statusClasses = {
        'pending': 'bg-warning',
        'approved': 'bg-success',
        'active': 'bg-primary',
        'completed': 'bg-info',
        'defaulted': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

function calculateFinancials(form) {
    const purchasePrice = parseFloat(form.find('input[name="purchase_price"]').val()) || 0;
    const downPayment = parseFloat(form.find('input[name="down_payment"]').val()) || 0;
    const interestRate = parseFloat(form.find('input[name="interest_rate"]').val()) || 0;
    const months = parseInt(form.find('select[name="loan_duration_months"]').val()) || 0;

    if (purchasePrice > 0 && downPayment >= 0 && interestRate >= 0 && months > 0) {
        const principalAmount = purchasePrice - downPayment;
        const totalInterest = principalAmount * (interestRate / 100) * (months / 12);
        const totalAmountPayable = principalAmount + totalInterest;
        const monthlyInstallment = totalAmountPayable / months;

        // Remove existing preview if any
        form.find('.financial-preview').remove();
        
        // Add new preview
        const previewHtml = `
            <div class="financial-preview mt-3 p-3 bg-light rounded">
                <h6><i class="fas fa-calculator"></i> Financial Preview:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small><strong>Principal Amount:</strong> KSh ${principalAmount.toLocaleString()}</small><br>
                        <small><strong>Total Interest:</strong> KSh ${totalInterest.toLocaleString()}</small>
                    </div>
                    <div class="col-md-6">
                        <small><strong>Monthly Installment:</strong> KSh ${monthlyInstallment.toLocaleString()}</small><br>
                        <small><strong>Total Payable:</strong> KSh ${totalAmountPayable.toLocaleString()}</small>
                    </div>
                </div>
            </div>
        `;
        form.find('select[name="loan_duration_months"]').closest('.row').after(previewHtml);
    }
}

// Input validation helpers
function validateFileSize(files, maxSizeMB = 2) {
    for (let file of files) {
        if (file.size > maxSizeMB * 1024 * 1024) {
            Swal.fire({
                icon: 'warning',
                title: 'File Too Large',
                text: `${file.name} is larger than ${maxSizeMB}MB. Please choose a smaller file.`
            });
            return false;
        }
    }
    return true;
}

function validateImageTypes(files) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    for (let file of files) {
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid File Type',
                text: `${file.name} is not a valid image file. Please choose JPG, PNG, GIF, or WebP files only.`
            });
            return false;
        }
    }
    return true;
}

// Add CSRF token to all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


// Initialize tooltips if Bootstrap is available
if (typeof bootstrap !== 'undefined') {
    $(document).ready(function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
}

</script>
        

<script>
    function downloadAgreementPDF() {
    Swal.fire({
        title: 'Generate Agreement PDF',
        text: 'Do you want to download the agreement as PDF?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Download PDF',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // You can implement PDF generation here
            // For now, we'll use the print function
            printSection('agreement-document-content');
        }
    });
}
</script>
        
        <script>
    function submitInspection() {
        let formData = new FormData(document.getElementById('vehicleInspectionForm'));

        $.ajax({
            url: "{{ route('vehicle-inspection-submit') }}", // Adjust route as needed
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Inspection submitted successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    }
</script>
<script>
        $('#HirePurchaseForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('HirePurchaseForm.store') }}", // Adjust route as needed
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Submitted successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
                
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });
</script> 

<script>
        $('#InCashForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('InCashForm.store') }}", // Adjust route as needed
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Submitted successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
                
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });
</script> 
<script>
        $('#PaymentForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('PaymentForm.store') }}", // Adjust route as needed
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Submitted successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
                
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });
</script> 

<script>
$(document).ready(function() {

    // Show modal and populate fields
    $('.editBtn').on('click', function() {
        let button = $(this);
        
        $('#recordId').val(button.data('id'));
        $('#clientName').val(button.data('client'));
        $('#phoneNo').val(button.data('phone'));
        $('#emailAddress').val(button.data('email'));
        $('#kra').val(button.data('kra'));
        $('#nationalId').val(button.data('national'));
        $('#amount').val(button.data('amount'));

        $('#editModal').modal('show');
    });

    // Submit update via AJAX
    $('#CashupdateForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('incash.update') }}", // Adjust route to your update route
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Record updated successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });

    // Approve button logic
    $('.approveBtn').on('click', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to approve this record 1.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: "{{ route('incash.approve') }}",
                    method: 'POST',
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Approved!',
                            response.message,
                            'success'
                        );
                        location.reload();
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'An unexpected error occurred.',
                            'error'
                        );
                    }
                });

            }
        })
    });
    // Delete button logic
    $('.deleteBtn').on('click', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this record.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: "{{ route('incash.delete') }}",
                    method: 'POST',
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        );
                        location.reload();
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'An unexpected error occurred.',
                            'error'
                        );
                    }
                });

            }
        })
    });
});
</script>
<script>
$(document).ready(function() {

    // Show modal and populate fields
    $('.editBtnhire').on('click', function () {
      let button = $(this);
      console.log(button.data('deposit'));
      $('#recordId').val(button.data('id'));
      $('#clientName').val(button.data('client'));
      $('#phoneNo').val(button.data('phone'));
      $('#emailAddress').val(button.data('email'));
      $('#kra').val(button.data('kra'));
      $('#nationalId').val(button.data('national'));
      $('#amount').val(button.data('amount'));
      $('#deposit').val(button.data('deposit'));
      $('#duration').val(button.data('duration'));
      $('#editModal').modal('show');
      });


    // Submit Hire Purchase update via AJAX
    $('#updateHirePurchaseForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('hirepurchase.update') }}", // Adjust route to your update route
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Record updated successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });

    // Approve Hire Purchase button logic
    $('.approveHirePurchaseBtn').on('click', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to approve this record.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: "{{ route('HirePurchase.approve') }}",
                    method: 'POST',
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Approved!',
                            response.message,
                            'success'
                        );
                        location.reload();
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'An unexpected error occurred.',
                            'error'
                        );
                    }
                });

            }
        })
    });
    // Delete Hire Purchase button logic ConfirmBtn
    $('.deletehireBtn').on('click', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this record.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: "{{ route('hirepurchase.delete') }}",
                    method: 'POST',
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        );
                        location.reload();
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'An unexpected error occurred.',
                            'error'
                        );
                    }
                });

            }
        })
    });
    // Delete Hire Purchase button logic 
    $('.ConfirmBtn').on('click', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to confirm this installment.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Confirm'
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: "{{ route('hirepurchase.confirm') }}",
                    method: 'POST',
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire(
                            'Confirmed!',
                            response.message,
                            'success'
                        );
                        location.reload();
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'An unexpected error occurred.',
                            'error'
                        );
                    }
                });

            }
        })
    });
});
</script>


        <script>
    function submitInspection() {
        let formData = new FormData(document.getElementById('vehicleInspectionForm'));

        $.ajax({
            url: "{{ route('vehicle-inspection-submit') }}", // Adjust route as needed
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Inspection submitted successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    }
</script>

        <script>
        $('#carForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('carimport.store') }}", // Adjust route as needed
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Bid submitted successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
                
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });
</script>
<script>
$(document).on('submit', 'form[id^="VehicleInspectionFormupdate"]', function(e) {
    e.preventDefault();

    let form = $(this);
    let formData = new FormData(this);
    let inspectionId = form.data('inspection-id');

    $.ajax({
        url: '/vehicle-inspections/' + inspectionId,  // Laravel PUT route with inspection ID
        method: 'POST',  // Laravel detects PUT from _method field
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Inspection updated successfully!',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Try Again'
            });
        }
    });
});
</script>

<script>
    $(document).on('submit', 'form[id^="TradeInFormupdate"]', function(e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);
        let vehicleId = form.data('vehicle-id');

        $.ajax({
            url: '/tradein/' + vehicleId,  // Laravel PUT route with vehicle ID
            method: 'POST',  // Laravel detects PUT from _method field
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Info updated successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });
</script>

<script>
        $('#TradeInForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('tradeinform.store') }}", // Adjust route as needed
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Info submitted successfully!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                location.reload();
                
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: xhr.responseJSON?.message || 'An unexpected error occurred.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $(document).on('submit', '#editcarForm', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form); // Collects all form fields, including files

            $.ajax({
                url: '{{ route('carimport.update') }}', // Adjust if route differs
                method: 'POST',
                data: formData,
                processData: false, // Required for file upload
                contentType: false, // Required for file upload
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: "Data updated successfully",
                            showConfirmButton: false,
                            timer: 4500
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again!'
                        });
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (const field in errors) {
                        errorMessages += errors[field][0] + '\n';
                    }
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Validation Error",
                        text: errorMessages,
                        showConfirmButton: true
                    });
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
    // Handle form submission for employee edit forms
    $(document).on('submit', '#fullpayment', function(e) {
        e.preventDefault();

        // Clear any previous alert
        $('#responseMessage').hide().removeClass('alert-success alert-danger');

        // Serialize form data
        const formData = $(this).serialize();
        const userId = $(this).find('input[name="id"]').val(); // Get the employee ID

        // Send AJAX request
        $.ajax({
            url: '{{ route('fullpayment.update') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Deposit updated successfully",
                        showConfirmButton: false,
                        timer: 4500
                    });
                    location.reload();
                    // Update the table row dynamically
                 
                } else {
                    
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "danger",
                        title: "Deposit updated successfully",
                        showConfirmButton: false,
                        timer: 4500
                    });
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessages = '';
                for (const field in errors) {
                    errorMessages += errors[field][0] + '\n';
                }
                // Use SweetAlert for displaying errors
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Validation Error",
                    text: errorMessages,
                    showConfirmButton: true
                });
            }
        });
    });
});
</script>
<script>
    $(document).ready(function() {
    // Handle form submission for employee edit forms
    $(document).on('submit', '#updatestatus', function(e) {
        e.preventDefault();

        // Clear any previous alert
        $('#responseMessage').hide().removeClass('alert-success alert-danger');

        // Serialize form data
        const formData = $(this).serialize();
        const userId = $(this).find('input[name="id"]').val(); // Get the employee ID

        // Send AJAX request
        $.ajax({
            url: '{{ route('deposit.update') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Deposit updated successfully",
                        showConfirmButton: false,
                        timer: 4500
                    });
                    location.reload();
                    // Update the table row dynamically
                 
                } else {
                    
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "danger",
                        title: "Deposit updated successfully",
                        showConfirmButton: false,
                        timer: 4500
                    });
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessages = '';
                for (const field in errors) {
                    errorMessages += errors[field][0] + '\n';
                }
                // Use SweetAlert for displaying errors
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Validation Error",
                    text: errorMessages,
                    showConfirmButton: true
                });
            }
        });
    });
});
</script>
<script>
    $(document).ready(function() {
    // Handle form submission for employee edit forms
    $(document).on('submit', '#editfacilitationForm', function(e) {
        e.preventDefault();

        // Clear any previous alert
        $('#responseMessage').hide().removeClass('alert-success alert-danger');

        // Serialize form data
        const formData = $(this).serialize();
        const userId = $(this).find('input[name="id"]').val(); // Get the employee ID

        // Send AJAX request
        $.ajax({
            url: '{{ route('frequest.update') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Request updated successfully",
                        showConfirmButton: false,
                        timer: 4500
                    });
                    location.reload();
                    // Update the table row dynamically
                 
                } else {
                    
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "danger",
                        title: "Request updated successfully",
                        showConfirmButton: false,
                        timer: 4500
                    });
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessages = '';
                for (const field in errors) {
                    errorMessages += errors[field][0] + '\n';
                }
                // Use SweetAlert for displaying errors
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Validation Error",
                    text: errorMessages,
                    showConfirmButton: true
                });
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    // Handle form submission for user creation
    $(document).on('submit', '#userForm', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creating...');
        
        // Serialize form data
        const formData = $(this).serialize();
        
        // Send AJAX request
        $.ajax({
            url: '{{ route('user.store') }}', // Make sure this route exists
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.message) {
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "User Created Successfully",
                        text: response.message,
                        showConfirmButton: false,
                        timer: 4500
                    });
                    
                    // Close modal and reset form
                    $('#standard-modal').modal('hide');
                    $('#userForm')[0].reset();
                    
                    // Reload page to show new user
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while creating the user.';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (const field in errors) {
                        errorMessages += errors[field][0] + '\n';
                    }
                    errorMessage = errorMessages;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Use SweetAlert for displaying errors
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error Creating User",
                    text: errorMessage,
                    showConfirmButton: true
                });
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Handle form submission for user edit forms (dynamic forms)
    $(document).on('submit', 'form[id^="edituserForm-"]', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
        
        // Clear any previous alert
        $('#responseMessage').hide().removeClass('alert-success alert-danger');
        
        // Serialize form data
        const formData = $(this).serialize();
        const userId = $(this).data('user-id'); // Get the user ID from data attribute
        const modalId = '#edit-modal-' + userId;
        
        // Send AJAX request
        $.ajax({
            url: '{{ route('user.update') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success notification
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "User Updated Successfully",
                        text: "User data has been updated successfully.",
                        showConfirmButton: false,
                        timer: 4500
                    });
                    
                    // Close modal
                    $(modalId).modal('hide');
                    
                    // Reload page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    // Show error notification
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Update Failed",
                        text: "Failed to update user data.",
                        showConfirmButton: true
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating the user.';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (const field in errors) {
                        errorMessages += errors[field][0] + '\n';
                    }
                    errorMessage = errorMessages;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                // Use SweetAlert for displaying errors
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Validation Error",
                    text: errorMessage,
                    showConfirmButton: true
                });
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    
    // Reset form when modal is closed
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();
    });
});
</script>
        <script>
    // CSRF token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle Delete User
    $('.delete-user').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This user will be soft-deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for deleting the user
                $.ajax({
                    url: '/users/' + id, // Route for deleting user
                    type: 'POST', // Using POST instead of DELETE
                    data: {
                        _method: 'DELETE', // Overriding method to DELETE
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeOut();  // Hide user row on success
                        Swal.fire('Deleted!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to delete user.', 'error');
                    }
                });
            }
        });
    });

     // Handle Deactivation Vehicle
    $('.delete-vehicle').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This vehicle will be deactivated.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Deactivate it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for deleting the user
                $.ajax({
                    url: '/vehicle/' + id, // Route for deleting user
                    type: 'POST', // Using POST instead of DELETE
                    data: {
                        _method: 'DELETE', // Overriding method to DELETE
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeOut();  // Hide user row on success
                        Swal.fire('Deleted!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to deactivate vehicle.', 'error');
                    }
                });
            }
        });
    });
    // Handle approve-frequest
    $('.approve-frequest').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This request will be approved.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for deleting the user
                $.ajax({
                    url: '/approvefrequest/' + id, // Route for deleting user
                    type: 'POST', // Using POST instead of DELETE
                    data: {
                        _method: 'POST', // Overriding method to DELETE
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeOut();  // Hide user row on success
                        Swal.fire('Approved!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to approve request.', 'error');
                    }
                });
            }
        });
    });

       // Handle reject-frequest
       $('.reject-frequest').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This request will be Rejected.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for deleting the user
                $.ajax({
                    url: '/rejectfrequest/' + id, // Route for deleting user
                    type: 'POST', // Using POST instead of DELETE
                    data: {
                        _method: 'POST', // Overriding method to DELETE
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeOut();  // Hide user row on success
                        Swal.fire('Rejected!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to reject request.', 'error');
                    }
                });
            }
        });
    });

    // Handle Restore User

    $('.restore-user').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Restore user?',
            text: "This will restore the user's access.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/users/restore/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to restore user.', 'error');
                    }
                });
            }
        });
    });
    // Restore Vehicle
    $('.restore-vehicle').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Restore Vehicle?',
            text: "This will restore the vehicle's access.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/vehicle/restore/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to restore user.', 'error');
                    }
                });
            }
        });
    });
      // Handle Won Bid
      $('.won-bid').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Won Bid?',
            text: "This will update Bid.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Won Bid!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/carimport/winbid/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'You won bid', 'error');
                    }
                });
            }
        });
    });


     // Handle Lost Bid
     $('.lost-bid').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Lost Bid?',
            text: "This will update Bid.",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Lost Bid!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/carimport/winbid/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'You lost bid.', 'error');
                    }
                });
            }
        });
    });
     // Handle Confirm-Full-Payment
     $('.Confirm-Full-Payment').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Confirm Full Payment?',
            text: "This will update Full Payment.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Confirm Full Payment!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/carimport/confirmfullpayment/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to restore user.', 'error');
                    }
                });
            }
        });
    });
      // Handle Confirm-Reception
      $('.Confirm-Import').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Confirm Import?',
            text: "This will update Import in Progress.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Confirm Import!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/import/confirmimport/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Import Confirmed.', 'error');
                    }
                });
            }
        });
    });
        // Handle Confirm-Reception
        $('.Confirm-Reception').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Confirm Reception of Imported Vehicle?',
            text: "This will confirm reception of imported vehicle.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Confirm Reception!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/confirm-reception/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Import Confirmed.', 'error');
                    }
                });
            }
        });
    });
     // Handle Confirm-Imported
     $('.Confirm-Imported').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Confirm Car Imported?',
            text: "This will update car have being Imported.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Confirm Imported!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/import/confirmimported/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Failed to restore user.', 'error');
                    }
                });
            }
        });
    });
     // Handle Port Charges
     $('.Port-Charges').click(function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Confirm Car Imported?',
            text: "This will update acr have being Imported.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Confirm Port Charges!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request for restoring the user
                $.ajax({
                    url: '/import/portcharges/' + id, // Route for restoring user
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    },
                    success: function (response) {
                        $('#user-' + id).fadeIn();  // Show user row on restore
                        Swal.fire('Restored!', response.success, 'success');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Port Charges is not confirmed.', 'error');
                    }
                });
            }
        });
    });
</script>
      
     <script>
        $('#FrequestForm').on('submit', function (e) {
            e.preventDefault();

            let formData = $(this).serialize(); // Automatically collects all inputs and CSRF

            $.ajax({
                url: '{{ route("frequest.store") }}', // Adjust to your route
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire('Success!', response.message || 'Request submitted successfully!', 'success');
                    $('#FrequestForm')[0].reset();
                    location.reload();
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'An error occurred.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    </script>
  

        <script>
                            // Function to get the current time in East Africa Time (EAT)
                            function getGreetingBasedOnTime() {
                                // Get current time in EAT timezone (UTC+3)
                                const date = new Date();
                                const options = {
                                    timeZone: 'Africa/Nairobi', // EAT timezone
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit',
                                };

                                // Format the current time in the EAT timezone
                                const formatter = new Intl.DateTimeFormat([], options);
                                const timeInEAT = formatter.format(date);
                                const currentHour = parseInt(timeInEAT.split(':')[0], 10); // Get the hour from the formatted time

                                let greetingMessage = "Good Morning, {{ Auth::user()->name }}"; // Default greeting

                                // Set the greeting based on the time of day
                                if (currentHour >= 12 && currentHour < 18) {
                                    greetingMessage = "Good Afternoon, {{ Auth::user()->name }}";
                                } else if (currentHour >= 18 || currentHour < 6) {
                                    greetingMessage = "Good Evening, {{ Auth::user()->name }}";
                                }

                                // Update the greeting message in the HTML
                                document.getElementById("greetingMessage").textContent = greetingMessage;
                            }

                            // Call the function to update the greeting
                            getGreetingBasedOnTime();
                            </script>

                            

    </body>


<!-- Mirrored from zoyothemes.com/hando/html/ by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 22 Apr 2025 19:50:29 GMT -->
</html>