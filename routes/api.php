<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/api-sms-debug', function () {
    try {
        // Use the SmsService which handles SSL automatically
        $sent = SmsService::send('254703894372', 'Test from Laravel SmsService');
        
        return response()->json([
            'sms_sent' => $sent,
            'message' => $sent ? 'SMS sent successfully using SmsService' : 'SMS failed using SmsService',
            'environment' => app()->environment(),
            'ssl_auto_handled' => true,
            'service_used' => 'SmsService::send()'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'type' => get_class($e),
            'service_used' => 'SmsService::send()'
        ]);
    }
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
