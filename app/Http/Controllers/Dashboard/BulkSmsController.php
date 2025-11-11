<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BulkSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkSmsController extends Controller
{
    public function index()
    {
        $messages = BulkSms::with('user')
            ->latest()
            ->get();
        
        return view('bulksms.index', compact('messages'));
    }

    public function create()
    {
        return view('bulksms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'target_group' => 'required|in:all,leads,hire_purchase,gentleman'
        ]);

        $recipients = BulkSms::getRecipientsByGroup($request->target_group);

        if (empty($recipients)) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found for the selected group.'
            ], 400);
        }

        $bulkSms = BulkSms::create([
            'message' => $request->message,
            'recipients' => $recipients,
            'target_group' => $request->target_group,
            'sent_by' => Auth::id(),
        ]);

        // Send SMS asynchronously (you can use a job for this)
        $bulkSms->send();

        return response()->json([
            'success' => true,
            'message' => "Bulk SMS queued. Sent to {$bulkSms->total_sent} recipients."
        ]);
    }

    public function show(BulkSms $bulkSms)
    {
        $bulkSms->load('user');
        
        $targetGroupLabels = [
            'all' => '🌐 All Clients',
            'leads' => '👥 Leads',
            'hire_purchase' => '🚗 Hire Purchase Clients',
            'gentleman' => '🤝 Gentleman Agreement Clients'
        ];
        
        $statusBadges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>'
        ];
        
        return response()->json([
            'sender_name' => $bulkSms->user->first_name . ' ' . $bulkSms->user->last_name,
            'target_group_label' => $targetGroupLabels[$bulkSms->target_group] ?? $bulkSms->target_group,
            'total_recipients' => count($bulkSms->recipients),
            'total_sent' => $bulkSms->total_sent,
            'total_failed' => $bulkSms->total_failed,
            'status_badge' => $statusBadges[$bulkSms->status] ?? $bulkSms->status,
            'message' => $bulkSms->message,
            'created_at' => $bulkSms->created_at->format('M d, Y h:i A')
        ]);
    }
    
    public function getRecipientCount(Request $request)
    {
        $group = $request->input('group');
        $recipients = BulkSms::getRecipientsByGroup($group);
        
        return response()->json([
            'count' => count($recipients)
        ]);
    }
}