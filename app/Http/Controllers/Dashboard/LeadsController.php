<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
class LeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    try {
        // Now you can use either 'user' or 'users' - both will work
        $query = Lead::with('users'); // This will now work!
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'LIKE', "%{$search}%")
                  ->orWhere('car_model', 'LIKE', "%{$search}%")
                  ->orWhere('client_phone', 'LIKE', "%{$search}%")
                  ->orWhere('client_email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('purchase_type')) {
            $query->where('purchase_type', $request->input('purchase_type'));
        }

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->input('salesperson_id'));
        }

        if ($request->filled('follow_up')) {
            $followUp = $request->input('follow_up') === 'yes';
            $query->where('follow_up_required', $followUp);
        }

        $leads = $query->with('users')->orderBy('created_at', 'desc')->paginate(15);
        $salespeople = User::select('id','last_name','first_name')->get(); 
        
        // Use the model's static method for statistics
        $statistics = Lead::getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'leads' => $leads,
                'statistics' => $statistics,
            ]);
        }
        return view('sells.leads', compact('leads', 'salespeople', 'statistics'));
        
    } catch (\Exception $e) {
        \Log::error('Error in LeadsController@index: ' . $e->getMessage());
        
        // Return default values in case of error
        $leads = Lead::paginate(15);
        $salespeople = User::select('id','last_name','first_name')->get();
        $statistics = Lead::getStatistics(); // This will return safe defaults
        
        return view('sells.leads', compact('leads', 'salespeople', 'statistics'))
               ->with('error', 'Error loading leads data. Please try again.');
    }
}

    /**
     * Calculate and return statistics for the leads dashboard
     */
    private function getStatistics()
    {
        try {
            // Get all counts
            $activeCount = Lead::where('status', 'Active')->count();
            $closedCount = Lead::where('status', 'Closed')->count();
            $unsuccessfulCount = Lead::where('status', 'Unsuccessful')->count();
            $followUpCount = Lead::where('follow_up_required', true)->count();
            $financeCount = Lead::where('purchase_type', 'Finance')->count();
            $cashCount = Lead::where('purchase_type', 'Cash')->count();
            
            // Calculate budget statistics
            $avgBudget = Lead::avg('client_budget') ?: 0;
            $totalBudget = Lead::sum('client_budget') ?: 0;
            $totalLeads = Lead::count();
            
            // Calculate conversion rate (closed leads vs total completed leads)
            $totalCompleted = $closedCount + $unsuccessfulCount;
            $conversionRate = $totalCompleted > 0 ? round(($closedCount / $totalCompleted) * 100, 1) : 0;

            return [
                'active' => $activeCount,
                'closed' => $closedCount,
                'unsuccessful' => $unsuccessfulCount,
                'follow_up' => $followUpCount,
                'finance' => $financeCount,
                'cash' => $cashCount,
                'avg_budget' => round($avgBudget, 2),
                'total_budget' => $totalBudget,
                'total_leads' => $totalLeads,
                'conversion_rate' => $conversionRate,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error calculating statistics: ' . $e->getMessage());
            return $this->getEmptyStatistics();
        }
    }

    /**
     * Return empty statistics in case of error
     */
    private function getEmptyStatistics()
    {
        return [
            'active' => 0,
            'closed' => 0,
            'unsuccessful' => 0,
            'follow_up' => 0,
            'finance' => 0,
            'cash' => 0,
            'avg_budget' => 0,
            'total_budget' => 0,
            'total_leads' => 0,
            'conversion_rate' => 0,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_model' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'purchase_type' => ['required', Rule::in(['Cash', 'Finance'])],
            'client_budget' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['Active', 'Closed', 'Unsuccessful'])],
            'salesperson_id' => 'required|exists:users,id',
            'follow_up_required' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'commitment_amount' => 'required|numeric|min:0',
        ]);

        $validated['follow_up_required'] = $request->has('follow_up_required');

        $lead = Lead::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully!',
                'lead' => $lead->load('users'),
                'statistics' => $this->getStatistics(),
            ]);
        }
  
        return redirect()->route('sells.index')->with('success', 'Lead created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $lead->load('users');

        if (request()->ajax()) {
            return response()->json([
                'lead' => $lead,
            ]);
        }

        return view('dashboard.leads.show', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'car_model' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'purchase_type' => ['required', Rule::in(['Cash', 'Finance'])],
            'client_budget' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['Active', 'Closed', 'Unsuccessful'])],
            'salesperson_id' => 'required|exists:users,id',
            'follow_up_required' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'commitment_amount' => 'required|numeric|min:0',
        ]);

        $validated['follow_up_required'] = $request->has('follow_up_required');

        $lead->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully!',
                'lead' => $lead->load('users'),
                'statistics' => $this->getStatistics(),
            ]);
        }

        return redirect()->route('dashboard.leads.index')->with('success', 'Lead updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        return $lead;
        $lead->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully!',
                'statistics' => $this->getStatistics(),
            ]);
        }

        return redirect()->route('dashboard.leads.index')->with('success', 'Lead deleted successfully!');
    }

    /**
     * Bulk update leads status.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'status' => ['required', Rule::in(['Active', 'Closed', 'Unsuccessful'])],
        ]);

        $count = Lead::whereIn('id', $request->lead_ids)
                    ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => "{$count} leads updated successfully!",
            'statistics' => $this->getStatistics(),
        ]);
    }

    /**
     * Export leads to CSV.
     */
    public function export(Request $request)
    {
        $query = Lead::with('users');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'LIKE', "%{$search}%")
                  ->orWhere('car_model', 'LIKE', "%{$search}%")
                  ->orWhere('client_phone', 'LIKE', "%{$search}%")
                  ->orWhere('client_email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('purchase_type')) {
            $query->where('purchase_type', $request->input('purchase_type'));
        }

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->input('salesperson_id'));
        }

        if ($request->filled('follow_up')) {
            $followUp = $request->input('follow_up') === 'yes';
            $query->where('follow_up_required', $followUp);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        $filename = 'leads_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, [
                'ID', 'Car Model', 'Client Name', 'Phone', 'Email', 
                'Purchase Type', 'Budget', 'Status', 'Salesperson', 
                'Follow Up', 'Notes', 'Date Created'
            ]);

            // Add data rows
            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->id,
                    $lead->car_model,
                    $lead->client_name,
                    $lead->client_phone,
                    $lead->client_email ?: 'N/A',
                    $lead->purchase_type,
                    $lead->client_budget,
                    $lead->status,
                    $lead->salesperson->name ?? 'Unknown',
                    $lead->follow_up_required ? 'Yes' : 'No',
                    $lead->notes ?: '',
                    $lead->created_at->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    

    /**
     * Calculate conversion rate.
     */
    protected function calculateConversionRate()
    {
        $totalLeads = Lead::count();
        $closedLeads = Lead::closed()->count();
        
        if ($totalLeads === 0) {
            return 0;
        }

        return round(($closedLeads / $totalLeads) * 100, 2);
    }

    /**
     * Get leads data for API/AJAX requests.
     */
    public function getData(Request $request)
    {
        $query = Lead::with('users');

        // Apply filters (same as index method)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'LIKE', "%{$search}%")
                  ->orWhere('car_model', 'LIKE', "%{$search}%")
                  ->orWhere('client_phone', 'LIKE', "%{$search}%")
                  ->orWhere('client_email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('purchase_type')) {
            $query->where('purchase_type', $request->input('purchase_type'));
        }

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->input('salesperson_id'));
        }

        if ($request->filled('follow_up')) {
            $followUp = $request->input('follow_up') === 'yes';
            $query->where('follow_up_required', $followUp);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(15);
        $statistics = $this->getStatistics();

        return response()->json([
            'leads' => $leads,
            'statistics' => $statistics,
        ]);
    }
}