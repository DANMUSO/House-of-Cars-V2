<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/check-upload-limits', function() {
       
});
Route::get('/', function () {
    return view('welcome');
});
Route::get('/redirect-home', function () {
    $role = auth()->user()->role ?? 'user';

    $routes = [
        'Managing-Director' => '/admin/dashboard',
        'Accountant' => '/finance/dashboard',
        'Showroom-Manager' => '/manager/dashboard',
        'Salesperson' => '/operations/dashboard',
        'clients' => '/clients/dashboard',
        'Suppport-Staff' => '/user/dashboard',
    ];

    $destination = $routes[$role] ?? '/dashboard';

    return redirect($destination);
})->middleware(['auth']);


require __DIR__.'/auth.php';
Route::middleware(['auth'])->group(function () {

    Route::get('/finance/dashboard', function () {
        return view('finance.index');
    })->name('finance.index');

    Route::get('/manager/dashboard', function () {
        return view('manager.dashboard');
    })->name('manager.dashboard');

    Route::get('/clients/dashboard', function () {
        return view('clients.index');
    })->name('clients.index');

    Route::get('/operations/dashboard', function () {
        return view('operations.index');
    })->name('operations.index');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

});
Route::get('/proxy-image', function(Request $request) {
    $imageUrl = $request->query('url');
    
    // Validate the URL is from your S3 bucket
    if (!$imageUrl || !str_contains($imageUrl, 'houseofcars.s3.eu-central-1.amazonaws.com')) {
        abort(404);
    }
    
    try {
        // Get image content
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'House of Cars PDF Generator'
            ]
        ]);
        
        $imageContent = file_get_contents($imageUrl, false, $context);
        
        if ($imageContent === false) {
            abort(404);
        }
        
        // Determine content type from URL
        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
        $contentType = match(strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg'
        };
        
        return response($imageContent, 200, [
            'Content-Type' => $contentType,
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=3600'
        ]);
        
    } catch (Exception $e) {
        abort(404);
    }
})->name('proxy.image');  

// SMS Password Reset Routes
Route::get('/password/reset/sms', [App\Http\Controllers\Dashboard\UsersController::class, 'showPasswordResetForm'])
    ->name('password.sms.form')
    ->middleware('guest');

Route::post('/password/reset/sms', [App\Http\Controllers\Dashboard\UsersController::class, 'sendPasswordViaSms'])
    ->name('password.sms.send')
    ->middleware('guest');
Route::middleware(['auth','role:Managing-Director,Showroom-Manager,Accountant,Salesperson,Suppport-Staff,HR,General-Manager'])->group(function () {

      // Gentleman Agreement Loan Restructuring Routes
Route::prefix('gentleman-loan-restructuring')->name('gentleman-loan-restructuring.')->group(function () {
    
    // Show restructuring options page
    Route::get('/{agreementId}/options', [App\Http\Controllers\Dashboard\GentlemanLoanRestructuringController::class, 'showRestructuringPage'])
        ->name('options');
    
    // Get restructuring options (AJAX)
    Route::post('/get-options', [App\Http\Controllers\Dashboard\GentlemanLoanRestructuringController::class, 'getRestructuringOptions'])
        ->name('get-options');
    
    // Process restructuring
    Route::post('/process', [App\Http\Controllers\Dashboard\GentlemanLoanRestructuringController::class, 'processRestructuring'])
        ->name('process');
    
    // API endpoints
    Route::prefix('api')->name('api.')->group(function () {
        
        // Get financial summary
        Route::get('/{agreementId}/financial-summary', [App\Http\Controllers\Dashboard\GentlemanLoanRestructuringController::class, 'getFinancialSummary'])
            ->name('financial-summary');
        
        // Get restructuring history
        Route::get('/{agreementId}/history', [App\Http\Controllers\Dashboard\GentlemanLoanRestructuringController::class, 'getRestructuringHistory'])
            ->name('history');
        
        // Validate restructured schedule
        Route::get('/{agreementId}/validate-schedule', [App\Http\Controllers\Dashboard\GentlemanLoanRestructuringController::class, 'validateRestructuredSchedule'])
            ->name('validate-schedule');
    });
});
    // Loan Restructuring Routes
    Route::prefix('loan-restructuring')->name('loan-restructuring.')->group(function () {
        
        // Show restructuring options page
        // Show restructuring options page
        Route::get('/{agreementId}/options', [App\Http\Controllers\Dashboard\LoanRestructuringController::class, 'showRestructuringPage'])
            ->name('options');
        
        // AJAX endpoints for calculations (these should come BEFORE parameterized routes)
        Route::get('/calculate-options', [App\Http\Controllers\Dashboard\LoanRestructuringController::class, 'getRestructuringOptions'])
            ->name('calculate-options');
        
        // Process restructuring
        Route::post('/process', [App\Http\Controllers\Dashboard\LoanRestructuringController::class, 'processRestructuring'])
            ->name('process');
        
        // Settings endpoint
        Route::get('/settings/get', [App\Http\Controllers\Dashboard\LoanRestructuringController::class, 'getRestructuringSettings'])
            ->name('settings');
        
        // Additional utility routes (these come after the static routes to avoid conflicts)
        Route::get('/{agreementId}/eligibility', [App\Http\Controllers\Dashboard\LoanRestructuringController::class, 'checkEligibility'])
            ->name('check-eligibility');
        
        Route::get('/{agreementId}/financial-summary', [App\Http\Controllers\Dashboard\LoanRestructuringController::class, 'getFinancialSummary'])
            ->name('financial-summary');
    });
    // Leave Applications Routes
    Route::prefix('leave-applications')->name('leave-applications.')->group(function () {
        
        // Store new leave application (AJAX)
        Route::post('/', [App\Http\Controllers\Dashboard\LeavesController::class, 'store'])->name('store');
        
        // View specific leave application (AJAX)
        Route::get('/{id}', [App\Http\Controllers\Dashboard\LeavesController::class, 'show'])->name('show');
        
        // Approve leave application (AJAX) - use {id} parameter, not {leave}
        Route::post('/{id}/approve', [App\Http\Controllers\Dashboard\LeavesController::class, 'approve'])
            ->name('approve');
        
        // Reject leave application (AJAX) - use {id} parameter, not {leave}
        Route::post('/{id}/reject', [App\Http\Controllers\Dashboard\LeavesController::class, 'reject'])
            ->name('reject');
        
        // Cancel leave application (AJAX) - use {id} parameter, not {leave}
        Route::post('/{id}/cancel', [App\Http\Controllers\Dashboard\LeavesController::class, 'cancel'])
            ->name('cancel');
    });
        
        // Main leave management page
        Route::get('/leave-management', [App\Http\Controllers\Dashboard\LeavesController::class, 'index'])->name('leave.index');
    // List all Upload Agreement
    // Logbook upload (matches your agreement upload pattern)
    Route::post('/upload-logbook', [App\Http\Controllers\AgreementfileController::class, 'upload'])->name('logbook.upload');

    // Logbook show (matches your agreement show pattern) 
    Route::get('/logbooks/{id}/{type}', [App\Http\Controllers\AgreementfileController::class, 'show'])->name('logbook.show');

    // Logbook delete (matches your agreement delete pattern)
    Route::delete('/logbooks/{id}', [App\Http\Controllers\AgreementfileController::class, 'destroy'])->name('logbook.delete');

    Route::post('/upload-agreement', [App\Http\Controllers\AgreementfileController::class, 'upload'])->name('agreement.upload');
    Route::get('/agreements/{id}/{type}', [App\Http\Controllers\AgreementfileController::class, 'show'])->name('agreement.show');
    Route::delete('/agreements/{id}', [App\Http\Controllers\AgreementfileController::class, 'destroy'])->name('agreement.delete');
    // List all car imports
    Route::get('/admin/dashboard', [App\Http\Controllers\Dashboard\DashboardsController::class, 'index'])->name('admin/dashboard');
    Route::get('/car-imports', [App\Http\Controllers\Dashboard\CarImportController::class, 'index'])->name('car-imports');
    Route::get('/bid-deposit-confirmed', [App\Http\Controllers\Dashboard\CarImportController::class, 'deposit'])->name('bid-deposit-confirmed'); 
    Route::post('carimport/winbid/{id}', [App\Http\Controllers\Dashboard\CarImportController::class, 'winbid'])->name('carimport.winbid'); 
    Route::post('import/portcharges/{id}', [App\Http\Controllers\Dashboard\CarImportController::class, 'portcharges'])->name('import.portcharges'); 
    Route::post('import/confirmimported/{id}', [App\Http\Controllers\Dashboard\CarImportController::class, 'confirmimported'])->name('import.confirmimported'); 
    Route::post('confirm-reception/{id}', [App\Http\Controllers\Dashboard\CarImportController::class, 'confirmreception'])->name('confirm-reception');
    Route::post('import/confirmimport/{id}', [App\Http\Controllers\Dashboard\CarImportController::class, 'confirmimport'])->name('carimport.confirmimport'); 
    Route::post('carimport/confirmfullpayment/{id}', [App\Http\Controllers\Dashboard\CarImportController::class, 'confirmfullpayment'])->name('carimport.confirmfullpayment');
    Route::post('carimport/losebid/{id}', [App\Http\Controllers\Dashboard\CarImportController::class, 'losebid'])->name('carimport.losebid');
    Route::get('/car-ready-for-shipping', [App\Http\Controllers\Dashboard\CarImportController::class, 'shipping'])->name('car-ready-for-shipping');
    Route::get('/wonbids', [App\Http\Controllers\Dashboard\CarImportController::class, 'wonbids'])->name('wonbids');
    Route::get('/lostbids', [App\Http\Controllers\Dashboard\CarImportController::class, 'lostbids'])->name('lostbids');
    Route::get('/full-payment-confirmed', [App\Http\Controllers\Dashboard\CarImportController::class, 'fullpayment'])->name('full-payment-confirmed');
    Route::get('/shipment-in-progress', [App\Http\Controllers\Dashboard\CarImportController::class, 'shipment'])->name('shipment-in-progress');
    Route::get('/cunstom-duty-cleared', [App\Http\Controllers\Dashboard\CarImportController::class, 'customduty'])->name('cunstom-duty-cleared');
    Route::get('/in-transit-received-cars', [App\Http\Controllers\Dashboard\CarImportController::class, 'receipt'])->name('in-transit-received-cars');
    Route::get('/received-cars', [App\Http\Controllers\Dashboard\CarImportController::class, 'received'])->name('received-cars');
    Route::get('/onboard', [App\Http\Controllers\Dashboard\OnboardController::class, 'index'])->name('onboard');
    Route::get('/imported', [App\Http\Controllers\Dashboard\InspectionController::class, 'index'])->name('imported');
    Route::get('/tradeincars', [App\Http\Controllers\Dashboard\InspectionController::class, 'tradeincars'])->name('tradeincars');
    Route::get('/inspectedimported', [App\Http\Controllers\Dashboard\InspectionController::class, 'inspectedimported'])->name('inspectedimported');
    Route::get('/inspectedtradein', [App\Http\Controllers\Dashboard\InspectionController::class, 'inspectedtradein'])->name('inspectedtradein');
    Route::put('/vehicle-inspections/{id}', [App\Http\Controllers\Dashboard\InspectionController::class, 'update'])->name('vehicle-inspections.update');
    Route::get('/incash', [App\Http\Controllers\Dashboard\SalesController::class, 'index'])->name('incash');
    Route::get('/hirepurchase', [App\Http\Controllers\Dashboard\HirePurchaseController::class, 'hirepurchase'])->name('hirepurchase');
    
    // Place specific routes FIRST
    Route::get('/fleetacquisition/{id}/manage', [App\Http\Controllers\Dashboard\FleetAcquisitionController::class, 'manage'])->name('fleetacquisition.manage');
    Route::post('/fleetacquisition/{id}/approve', [App\Http\Controllers\Dashboard\FleetAcquisitionController::class, 'approve'])->name('fleetacquisition.approve');
    Route::post('/fleetacquisition/{id}/payments', [App\Http\Controllers\Dashboard\FleetAcquisitionController::class, 'storePayment'])->name('fleetacquisition.payments.store');
    
    Route::post('/fleetacquisition/{id}/delete-photo', [App\Http\Controllers\Dashboard\FleetAcquisitionController::class, 'deletePhoto'])->name('fleetacquisition.delete_photo');

    // Then the resource route
    Route::resource('fleetacquisition', App\Http\Controllers\Dashboard\FleetAcquisitionController::class, [
        'names' => [
            'index' => 'fleetacquisition',
            'store' => 'fleet_acquisition.store',
            'show' => 'fleet_acquisition.show',
            'update' => 'fleet_acquisition.update',
            'destroy' => 'fleet_acquisition.destroy'
        ]
    ]);
    //Password reset 
    Route::post('/users/reset-password', [App\Http\Controllers\Dashboard\UsersController::class, 'resetPassword'])
     ->name('users.resetPassword');

    Route::post('/inspection/{inspection}/photos/upload', [App\Http\Controllers\Dashboard\InspectionController::class, 'uploadPhotos'])->name('inspection.photos.upload');
    Route::delete('/inspection/{inspection}/photos/{photoIndex}', [App\Http\Controllers\Dashboard\InspectionController::class, 'deletePhoto'])->name('inspection.photos.delete');
    Route::get('/inspection/{inspection}/photos', [App\Http\Controllers\Dashboard\InspectionController::class, 'getPhotos'])->name('inspection.photos.get');

    Route::post('/leads', [App\Http\Controllers\Dashboard\LeadsController::class, 'store'])->name('leads.store');
    Route::get('/leads/{lead}', [App\Http\Controllers\Dashboard\LeadsController::class, 'show'])->name('leads.show');
    Route::post('/leads/{lead}', [App\Http\Controllers\Dashboard\LeadsController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [App\Http\Controllers\Dashboard\LeadsController::class, 'destroy'])->name('leads.destroy');
    
    // Additional routes
    Route::post('/leads/bulk-update', [LeadsController::class, 'bulkUpdate'])->name('leads.bulkUpdate');
    Route::get('/leads/export/csv', [LeadsController::class, 'export'])->name('leads.export');
    Route::get('/leads/data/ajax', [LeadsController::class, 'getData'])->name('leads.getData');
    Route::prefix('hire-purchase')->name('hire-purchase.')->group(function () {
        // Get rescheduling options (AJAX endpoint for the form)
        Route::get('/rescheduling-options', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'getReschedulingOptions'])
            ->name('rescheduling-options');
        
        // Store lump sum payment and reschedule
        Route::post('/lump-sum-payment', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'storeLumpSumPayment'])
            ->name('lump-sum-payment');
        
        // Other existing routes...
        Route::get('/', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'show'])->name('show');
        Route::post('/payments/store', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'storePayment'])->name('payments.store');
        Route::post('/{id}/approve', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'approveAgreement'])->name('approve');
    });

    // Hire Purchase Penalty Routes
Route::prefix('hire-purchase')->group(function () {
    Route::get('/{agreementId}/penalties', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'getPenalties']);
    Route::post('/{agreementId}/penalties/calculate', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'calculatePenalties']);
    Route::get('/{agreementId}/penalties/breakdown', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'getPenaltyBreakdown']);
});

// Gentleman Agreement Penalty Routes
Route::prefix('gentleman-agreement')->group(function () {
    Route::get('/{agreementId}/penalties', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'getPenalties']);
    Route::post('/{agreementId}/penalties/calculate', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'calculatePenalties']);
    Route::get('/{agreementId}/penalties/breakdown', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'getPenaltyBreakdown']);
});

// Shared Penalty Payment Routes (can work for both)
Route::post('/penalties/{penaltyId}/pay', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'payPenalty']);
Route::put('/penalties/{penaltyId}/waive', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'waivePenalty']);// Fleet Payment Routes
   
//Fleet Agreement
Route::post('/fleet-payments/{id}/confirm', [App\Http\Controllers\Dashboard\FleetPaymentController::class, 'confirm'])->name('fleet_payments.confirm');
    Route::get('/fleet-payments/{id}', [App\Http\Controllers\Dashboard\FleetPaymentController::class, 'show'])->name('fleet_payments.show');
    Route::get('/leads', [App\Http\Controllers\Dashboard\LeadsController::class, 'index'])->name('leads');
    Route::get('/sales', [App\Http\Controllers\Dashboard\SalesController::class, 'sales'])->name('sales');
    Route::get('/gatepasscard', [App\Http\Controllers\Dashboard\SalesController::class, 'gatepasscard'])->name('sowroomreceipt');
    Route::get('/Facilitation/requests', [App\Http\Controllers\Dashboard\FacilitationController::class, 'index'])->name('Facilitation.requests');
    Route::get('/Leaves/requests', [App\Http\Controllers\Dashboard\LeavesController::class, 'index'])->name('Leaves.requests');
    Route::get('/Reports/biddedcarsreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'index'])->name('Reports.biddedcarsreport');
    Route::get('/Reports/shippinginprocessreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'shippinginprocessreport'])->name('Reports.shippinginprocessreport');
    Route::get('/Reports/shippedcarsreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'shippedcarsreport'])->name('Reports.shippedcarsreport');
    Route::get('/Reports/portclearedcarsreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'portclearedcarsreport'])->name('Reports.portclearedcarsreport');
    Route::get('/Reports/carsintransitreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'carsintransitreport'])->name('Reports.carsintransitreport');
    Route::get('/Reports/deliveredcarsreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'deliveredcarsreport'])->name('Reports.deliveredcarsreport');
    Route::get('/Reports/inspectedcarsreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'inspectedcarsreport'])->name('Reports.inspectedcarsreport');
    Route::get('/Reports/tradeinreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'tradeinreport'])->name('Reports.tradeinreport');
    Route::get('/Reports/hirepurchasereport', [App\Http\Controllers\Dashboard\ReportsController::class, 'hirepurchasereport'])->name('Reports.hirepurchasereport');
    Route::get('/Reports/incashreport', [App\Http\Controllers\Dashboard\ReportsController::class, 'incashreport'])->name('Reports.incashreport');
    Route::get('/users', [App\Http\Controllers\Dashboard\UsersController::class, 'index'])->name('users');
    Route::get('/carsmanufacturers', [App\Http\Controllers\Dashboard\ManufacturersController::class, 'index'])->name('carsmanufacturers');
    Route::get('/carsmodels', [App\Http\Controllers\Dashboard\CarsController::class, 'index'])->name('carsmodels');
    Route::get('/tradein', [App\Http\Controllers\Dashboard\TradeInController::class, 'index'])->name('tradein');
    Route::get('/clientprofile', [App\Http\Controllers\Dashboard\HirePurchaseController::class, 'clientprofile'])->name('clientprofile');
    Route::get('/sellinbehalf', [App\Http\Controllers\Dashboard\SellingBehalfController::class, 'index'])->name('sellinbehalf');
    Route::post('/carimport/store', [App\Http\Controllers\Dashboard\CarImportController::class, 'store'])->name('carimport.store');
    Route::post('/tradeinform/store', [App\Http\Controllers\Dashboard\TradeInController::class, 'store'])->name('tradeinform.store');
    Route::post('/incash/store', [App\Http\Controllers\Dashboard\SalesController::class, 'storeincash'])->name('InCashForm.store');
    Route::post('/paymentForm/store', [App\Http\Controllers\Dashboard\SalesController::class, 'storepayments'])->name('PaymentForm.store');
    Route::post('/hirepurchase/store', [App\Http\Controllers\Dashboard\SalesController::class, 'storehirepurchase'])->name('HirePurchaseForm.store');
    Route::post('/incash/update', [App\Http\Controllers\Dashboard\SalesController::class, 'updateincash'])->name('incash.update');
    Route::post('/hirepurchase/update', [App\Http\Controllers\Dashboard\SalesController::class, 'updatehirepurchase'])->name('hirepurchase.update');
    Route::post('/incash/approve', [App\Http\Controllers\Dashboard\SalesController::class, 'approveincash'])->name('incash.approve'); 
    Route::post('/incash/delete', [App\Http\Controllers\Dashboard\SalesController::class, 'deleteincash'])->name('incash.delete');
    Route::post('/hirepurchase/delete', [App\Http\Controllers\Dashboard\SalesController::class, 'deletehirepurchase'])->name('hirepurchase.delete');
    Route::post('/hirepurchase/confirm', [App\Http\Controllers\Dashboard\SalesController::class, 'confirmhirepurchase'])->name('hirepurchase.confirm');
    Route::post('/HirePurchase/approve', [App\Http\Controllers\Dashboard\SalesController::class, 'approveHirePurchase'])->name('HirePurchase.approve');
    Route::get('/client_profile/{id}', [App\Http\Controllers\Dashboard\SalesController::class, 'profile'])->name('client_profile');
    Route::put('/tradein/{id}', [App\Http\Controllers\Dashboard\TradeInController::class, 'update'])->name('tradein.update');
    Route::post('/carimport/update', [App\Http\Controllers\Dashboard\CarImportController::class, 'update'])->name('carimport.update');
    Route::post('/user/store', [App\Http\Controllers\Dashboard\UsersController::class, 'store'])->name('user.store');
    Route::post('/frequest/store', [App\Http\Controllers\Dashboard\FacilitationController::class, 'store'])->name('frequest.store');
    Route::delete('/users/{id}', [App\Http\Controllers\Dashboard\UsersController::class, 'destroy'])->name('users.softDelete');
    Route::delete('/vehicle/{id}', [App\Http\Controllers\Dashboard\TradeInController::class, 'destroy'])->name('vehicle.softDelete');
    Route::post('/approvefrequest/{id}', [App\Http\Controllers\Dashboard\FacilitationController::class, 'approve'])->name('approvefrequest'); 
    Route::post('/rejectfrequest/{id}', [App\Http\Controllers\Dashboard\FacilitationController::class, 'reject'])->name('rejectfrequest');  
    Route::post('carimport/won/{id}', [App\Http\Controllers\Dashboard\UsersController::class, 'restore'])->name('carimport.won');
    Route::post('/users/restore/{id}', [App\Http\Controllers\Dashboard\UsersController::class, 'restore'])->name('users.restore');
    Route::post('/vehicle/restore/{id}', [App\Http\Controllers\Dashboard\TradeInController::class, 'restore'])->name('vehicle.restore');
    Route::get('/users/{id}', [App\Http\Controllers\Dashboard\UsersController::class, 'show'])->name('users.show');
    Route::post('user/update', [App\Http\Controllers\Dashboard\UsersController::class, 'update'])->name('user.update');
    Route::post('deposit/update', [App\Http\Controllers\Dashboard\CarImportController::class, 'depositupdate'])->name('deposit.update');
    Route::post('fullpayment/update', [App\Http\Controllers\Dashboard\CarImportController::class, 'fullamount'])->name('fullpayment.update');
    Route::post('frequest/update', [App\Http\Controllers\Dashboard\FacilitationController::class, 'update'])->name('frequest.update');
    Route::post('/vehicle-inspection-submit', [App\Http\Controllers\Dashboard\InspectionController::class, 'store'])->name('vehicle-inspection-submit');

    // Additional custom routes for leave management fullpayment.update
    Route::prefix('leaves')->name('leaves.')->group(function() {
        Route::post('{leave}/approve', [App\Http\Controllers\Dashboard\LeavesController::class, 'approve'])->name('approve');
        Route::post('/store', [App\Http\Controllers\Dashboard\LeavesController::class, 'store'])->name('store');
        Route::post('{leave}/reject', [App\Http\Controllers\Dashboard\LeavesController::class, 'reject'])->name('reject');
        Route::post('{leave}/cancel', [App\Http\Controllers\Dashboard\LeavesController::class, 'cancel'])->name('cancel');
        Route::get('user/balance', [App\Http\Controllers\Dashboard\LeavesController::class, 'getUserLeaveBalance'])->name('user.balance');
        Route::get('user/applications', [App\Http\Controllers\Dashboard\LeavesController::class, 'getMyApplications'])->name('user.applications');
        Route::get('statistics', [App\Http\Controllers\Dashboard\LeavesController::class, 'getLeaveStatistics'])->name('statistics');
    });


    
    
    // Add these routes to your web.php file
    Route::post('/hire-purchase/payments/store', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'storePayment'])->name('hire-purchase.payments.store');
    Route::post('/hire-purchase/payments/{payment}/verify', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'verifyPayment'])->name('hire-purchase.payments.verify');
    Route::post('/hire-purchase/{agreement}/approve', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'approveAgreement'])->name('hire-purchase.approve');

     Route::prefix('hire-purchase')->group(function () {
     Route::get('/', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'index'])->name('hire-purchase.index');
     Route::delete('/{id}', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'destroy'])->name('hire-purchase.destroy');
     Route::post('/', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'store'])->name('hire-purchase.store');
     Route::get('/{id}', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'show'])->name('hire-purchase.show');
     Route::post('/{id}/approve', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'approve'])->name('hire-purchase.approve');
     Route::post('/payment', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'recordPayment'])->name('hire-purchase.payment');
     Route::get('/calculation/calculate', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'getCalculation'])->name('hire-purchase.calculate');
     Route::get('/export/excel', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'export'])->name('hire-purchase.export');
     Route::get('/{id}/schedule', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'paymentSchedule'])->name('hire-purchase.schedule');
     Route::get('/{id}/print', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'printAgreement'])->name('hire-purchase.print');
     Route::post('/{id}/reminder', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'sendReminder'])->name('hire-purchase.reminder');
     Route::get('/dashboard/overview', [App\Http\Controllers\Dashboard\HirePurchasesController::class, 'dashboard'])->name('hire-purchase.dashboard');
 });
  Route::prefix('gentlemanagreement')->group(function () {
     Route::get('/', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'index'])->name('gentlemanagreement.index');
     Route::delete('/{id}', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'destroy'])->name('gentlemanagreement.destroy');
     Route::post('/', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'store'])->name('gentlemanagreement');
     Route::get('/{id}', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'show'])->name('gentlemanagreement.show');
     Route::post('/payments/{id}/verify', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'verifyPayment'])->name('gentlemanagreement.verify');
     Route::post('/{id}/approve', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'approve'])->name('gentlemanagreement.approve');
     Route::post('/payment', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'recordPayment'])->name('gentlemanagreement.payment');
     Route::get('/export/excel', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'export'])->name('gentlemanagreement.export');
     Route::get('/{id}/schedule', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'paymentSchedule'])->name('gentlemanagreement.schedule');
     Route::get('/{id}/print', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'printAgreement'])->name('gentlemanagreement.print');
     Route::post('/{id}/reminder', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'sendReminder'])->name('gentlemanagreement.reminder');
     Route::get('/dashboard/overview', [App\Http\Controllers\Dashboard\GentlemanAgreementController::class, 'dashboard'])->name('gentlemanagreement.dashboard');
 });
 Route::post('/hire-purchase/record-payment', [HirePurchaseController::class, 'recordPayment'])
    ->name('hire-purchase.record-payment');



    // Main dashboard routes
Route::middleware(['auth'])->group(function () {
    
    // Standard dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Enhanced dashboard with all features
    Route::get('/dashboard/enhanced', [DashboardController::class, 'indexEnhanced'])->name('dashboard.enhanced');
    
    // API endpoint for real-time updates
    Route::get('/dashboard/api', [DashboardController::class, 'getDashboardApi'])->name('dashboard.api');
    
    // Export dashboard data
    Route::get('/dashboard/export', [DashboardController::class, 'exportDashboard'])->name('dashboard.export');
    
    // Additional specific routes for drill-down functionality
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        
        // Overdue payments detail
        Route::get('/overdue-payments', function () {
            $overduePayments = DB::table('hire_purchase_agreements')
                ->where('is_overdue', 1)
                ->where('status', 'active')
                ->select('client_name', 'phone_number', 'outstanding_balance', 'overdue_days')
                ->orderByDesc('overdue_days')
                ->get();
                
            return view('dashboard.overdue-payments', compact('overduePayments'));
        })->name('overdue-payments');
        
        // Inventory analysis detail
        Route::get('/inventory-analysis', function () {
            $staleInventory = DB::table('car_imports')
                ->where('created_at', '<', Carbon::now()->subDays(90))
                ->whereIn('status', [0, 1, 2])
                ->select('make', 'model', 'year', 'created_at', 'bid_amount')
                ->orderBy('created_at')
                ->get();
                
            return view('dashboard.inventory-analysis', compact('staleInventory'));
        })->name('inventory-analysis');
        
        // Fleet management overview
        Route::get('/fleet-overview', function () {
            $fleetData = DB::table('fleet_acquisitions')
                ->select('vehicle_make', 'vehicle_model', 'status', 'outstanding_balance', 'purchase_price')
                ->get()
                ->groupBy('status');
                
            return view('dashboard.fleet-overview', compact('fleetData'));
        })->name('fleet-overview');
        
        // Staff performance detail
        Route::get('/staff-performance', function () {
            $staffPerformance = DB::table('leads as l')
                ->join('users as u', 'l.salesperson_id', '=', 'u.id')
                ->select(
                    'u.first_name',
                    'u.last_name',
                    'u.role',
                    DB::raw('COUNT(l.id) as total_leads'),
                    DB::raw('SUM(CASE WHEN l.status = "Closed" THEN 1 ELSE 0 END) as closed_leads'),
                    DB::raw('SUM(CASE WHEN l.status = "Unsuccessful" THEN 1 ELSE 0 END) as unsuccessful_leads'),
                    DB::raw('SUM(l.commitment_amount) as total_commitments'),
                    DB::raw('ROUND((SUM(CASE WHEN l.status = "Closed" THEN 1 ELSE 0 END) / COUNT(l.id)) * 100, 2) as conversion_rate')
                )
                ->groupBy('u.id', 'u.first_name', 'u.last_name', 'u.role')
                ->orderByDesc('closed_leads')
                ->get();
                
            return view('dashboard.staff-performance', compact('staffPerformance'));
        })->name('staff-performance');
        
        // Monthly breakdown
        Route::get('/monthly-breakdown/{year?}', function ($year = null) {
            $year = $year ?? Carbon::now()->year;
            
            $monthlyBreakdown = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthlyBreakdown[] = [
                    'month' => Carbon::create($year, $month, 1)->format('F'),
                    'sales_count' => DB::table('in_cashes')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('status', 0)
                        ->count(),
                    'sales_amount' => DB::table('in_cashes')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('status', 0)
                        ->sum('paid_amount'),
                    'hp_collections' => DB::table('hire_purchase_payments')
                        ->whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month)
                        ->sum('amount'),
                    'leads_generated' => DB::table('leads')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->count(),
                ];
            }
            
            return view('dashboard.monthly-breakdown', compact('monthlyBreakdown', 'year'));
        })->name('monthly-breakdown');
    });
});

// Additional helper routes for AJAX requests
Route::middleware(['auth'])->prefix('api/dashboard')->group(function () {
    
    // Get real-time statistics
    Route::get('/stats', function () {
        return response()->json([
            'total_cars_sold' => DB::table('in_cashes')->where('status', 0)->count(),
            'active_leads' => DB::table('leads')->where('status', 'Active')->count(),
            'overdue_count' => DB::table('hire_purchase_agreements')->where('is_overdue', 1)->count(),
            'today_collections' => DB::table('hire_purchase_payments')
                ->whereDate('payment_date', Carbon::today())
                ->sum('amount'),
        ]);
    });
    
    // Get recent activities
    Route::get('/recent-activities', function () {
        $activities = collect();
        
        // Recent payments
        $recentPayments = DB::table('hire_purchase_payments')
            ->join('hire_purchase_agreements', 'hire_purchase_payments.agreement_id', '=', 'hire_purchase_agreements.id')
            ->select(
                'hire_purchase_agreements.client_name as description',
                'hire_purchase_payments.amount',
                'hire_purchase_payments.payment_date as date',
                DB::raw("'payment' as type")
            )
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();
            
        // Recent sales
        $recentSales = DB::table('in_cashes')
            ->join('car_imports', 'in_cashes.car_id', '=', 'car_imports.id')
            ->select(
                DB::raw("CONCAT(car_imports.make, ' ', car_imports.model, ' sold to ', in_cashes.Client_Name) as description"),
                'in_cashes.paid_amount as amount',
                'in_cashes.created_at as date',
                DB::raw("'sale' as type")
            )
            ->where('in_cashes.status', 0)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
            
        $activities = $activities->merge($recentPayments)
                               ->merge($recentSales)
                               ->sortByDesc('date')
                               ->take(10);
                               
        return response()->json($activities);
    });
    
    // Get chart data for specific periods
    Route::get('/chart-data/{period}', function ($period) {
        switch ($period) {
            case 'week':
                // Weekly data for last 7 days
                $data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'date' => $date->format('D'),
                        'sales' => DB::table('in_cashes')
                            ->whereDate('created_at', $date)
                            ->where('status', 0)
                            ->sum('paid_amount') / 1000,
                        'collections' => DB::table('hire_purchase_payments')
                            ->whereDate('payment_date', $date)
                            ->sum('amount') / 1000,
                    ];
                }
                break;
                
            case 'month':
                // Daily data for current month
                $data = [];
                $startOfMonth = Carbon::now()->startOfMonth();
                $daysInMonth = Carbon::now()->daysInMonth;
                
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = $startOfMonth->copy()->addDays($day - 1);
                    if ($date > Carbon::now()) break;
                    
                    $data[] = [
                        'date' => $date->format('d'),
                        'sales' => DB::table('in_cashes')
                            ->whereDate('created_at', $date)
                            ->where('status', 0)
                            ->sum('paid_amount') / 1000,
                        'collections' => DB::table('hire_purchase_payments')
                            ->whereDate('payment_date', $date)
                            ->sum('amount') / 1000,
                    ];
                }
                break;
                
            default:
                // Yearly data (monthly breakdown)
                $data = [];
                for ($month = 1; $month <= 12; $month++) {
                    $data[] = [
                        'date' => Carbon::create(null, $month, 1)->format('M'),
                        'sales' => DB::table('in_cashes')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->where('status', 0)
                            ->sum('paid_amount') / 1000,
                        'collections' => DB::table('hire_purchase_payments')
                            ->whereMonth('payment_date', $month)
                            ->whereYear('payment_date', Carbon::now()->year)
                            ->sum('amount') / 1000,
                    ];
                }
        }
        
        return response()->json($data);
    });
});
});

