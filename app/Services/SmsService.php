<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS to single recipient
     */
    public static function send(string $phone, string $message): bool
    {
        try {
            $httpClient = Http::withHeaders([
                'Accesskey' => config('services.onfonmedia.access_key'),
                'Content-Type' => 'application/json',
            ]);

            // Disable SSL verification for local development
            if (app()->environment('local') || env('SMS_SSL_VERIFY', true) === false) {
                $httpClient = $httpClient->withOptions(['verify' => false]);
            }

            $response = $httpClient->post('https://api.onfonmedia.com/v1/sms/SendBulkSMS', [
                'SenderId' => config('services.onfonmedia.sender_id'),
                'MessageParameters' => [
                    [
                        'Number' => $phone,
                        'Text' => $message
                    ]
                ],
                'ApiKey' => config('services.onfonmedia.api_key'),
                'ClientId' => config('services.onfonmedia.client_id'),
            ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'message_length' => strlen($message)
                ]);
                return true;
            }

            Log::error('SMS API error', [
                'phone' => $phone,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('SMS service exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send SMS to multiple recipients
     */
    public static function sendBulk(array $recipients): bool
    {
        try {
            $messageParameters = collect($recipients)->map(function($recipient) {
                return [
                    'Number' => $recipient['phone'],
                    'Text' => $recipient['message']
                ];
            })->toArray();

            $httpClient = Http::withHeaders([
                'Accesskey' => config('services.onfonmedia.access_key'),
                'Content-Type' => 'application/json',
            ]);

            // Disable SSL verification for local development
            if (app()->environment('local') || env('SMS_SSL_VERIFY', true) === false) {
                $httpClient = $httpClient->withOptions(['verify' => false]);
            }

            $response = $httpClient->post('https://api.onfonmedia.com/v1/sms/SendBulkSMS', [
                'SenderId' => config('services.onfonmedia.sender_id'),
                'MessageParameters' => $messageParameters,
                'ApiKey' => config('services.onfonmedia.api_key'),
                'ClientId' => config('services.onfonmedia.client_id'),
            ]);

            if ($response->successful()) {
                Log::info('Bulk SMS sent successfully', [
                    'recipient_count' => count($recipients)
                ]);
                return true;
            }

            Log::error('Bulk SMS API error', [
                'recipient_count' => count($recipients),
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Bulk SMS service exception', [
                'recipient_count' => count($recipients),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send same message to multiple phone numbers
     */
    public static function sendToMultiple(array $phones, string $message): bool
    {
        $recipients = collect($phones)->map(function($phone) use ($message) {
            return ['phone' => $phone, 'message' => $message];
        })->toArray();

        return self::sendBulk($recipients);
    }

    /**
     * Validate phone number format (basic validation)
     */
    public static function isValidPhone(string $phone): bool
    {
        // Basic validation for Kenyan numbers
        return preg_match('/^254[17]\d{8}$/', $phone) || 
               preg_match('/^0[17]\d{8}$/', $phone);
    }

    /**
     * Format phone number to international format
     */
    public static function formatPhone(string $phone): string
    {
        // Remove spaces and special characters
        $phone = preg_replace('/[^\d]/', '', $phone);
        
        // Convert local format to international
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        return $phone;
    }
}