<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    try {
        $userRole = Auth::user()->role;
        
        // Initialize the query based on user role
        if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            // Show all leads for privileged roles
            $query = Lead::with('users');
        } else {
            // Show only leads assigned to the current user (salesperson)
            $query = Lead::with('users')->where('salesperson_id', Auth::id());
        }
        
        // Get ALL records without pagination (for client-side filtering)
        $leads = $query->orderBy('created_at', 'desc')->get();
        
        // Wrap in a collection that mimics paginator for blade compatibility
        $leadsCollection = new \Illuminate\Pagination\LengthAwarePaginator(
            $leads,
            $leads->count(),
            $leads->count(),
            1,
            ['path' => $request->url()]
        );
        
        // Get salespeople list (only for privileged roles)
        if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $salespeople = User::select('id','last_name','first_name')->get();
        } else {
            $salespeople = collect(); // Empty collection for regular users
        }
        
        // Use the model's static method for statistics
        $statistics = $this->getStatisticsBasedOnRole();

        if ($request->ajax()) {
            return response()->json([
                'leads' => $leadsCollection,
                'statistics' => $statistics,
            ]);
        }
        
        return view('sells.leads', compact('leads', 'salespeople', 'statistics'))
               ->with('leads', $leadsCollection);
        
    } catch (\Exception $e) {
        \Log::error('Error in LeadsController@index: ' . $e->getMessage());
        
        // Return default values in case of error based on role
        $userRole = Auth::user()->role;
        if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $leads = Lead::get();
            $salespeople = User::select('id','last_name','first_name')->get();
        } else {
            $leads = Lead::where('salesperson_id', Auth::id())->get();
            $salespeople = collect();
        }
        
        $leadsCollection = new \Illuminate\Pagination\LengthAwarePaginator(
            $leads,
            $leads->count(),
            $leads->count(),
            1
        );
        
        $statistics = $this->getStatisticsBasedOnRole();
        
        return view('sells.leads', compact('salespeople', 'statistics'))
               ->with('leads', $leadsCollection)
               ->with('error', 'Error loading leads data. Please try again.');
    }
}
    /**
     * Calculate and return statistics based on user role
     */
    private function getStatisticsBasedOnRole()
    {
        try {
            $userRole = Auth::user()->role;
            
            // Build base query based on role
            if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
                // Show statistics for all leads
                $baseQuery = Lead::query();
            } else {
                // Show statistics only for user's own leads
                $baseQuery = Lead::where('salesperson_id', Auth::id());
            }
            
            // Get all counts
            $activeCount = (clone $baseQuery)->where('status', 'Active')->count();
            $closedCount = (clone $baseQuery)->where('status', 'Closed')->count();
            $unsuccessfulCount = (clone $baseQuery)->where('status', 'Unsuccessful')->count();
            $followUpCount = (clone $baseQuery)->where('follow_up_required', true)->count();
            $financeCount = (clone $baseQuery)->where('purchase_type', 'Finance')->count();
            $cashCount = (clone $baseQuery)->where('purchase_type', 'Cash')->count();
            
            // Calculate budget statistics
            $avgBudget = (clone $baseQuery)->avg('client_budget') ?: 0;
            $totalBudget = (clone $baseQuery)->sum('client_budget') ?: 0;
            $totalLeads = (clone $baseQuery)->count();
            
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
        $userRole = Auth::user()->role;
        
        $validated = $request->validate([
            'car_model' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'purchase_type' => ['required', Rule::in(['Cash', 'Finance'])],
            'client_budget' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['Active', 'Closed', 'Unsuccessful'])],
            'follow_up_required' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'commitment_amount' => 'required|numeric|min:0',
        ]);
        // Add the salesperson_id manually
        $validated['salesperson_id'] = auth()->id();
        // For regular users, force the salesperson_id to be their own ID
        if (!in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $validated['salesperson_id'] = Auth::id();
        }

        $validated['follow_up_required'] = $request->has('follow_up_required');

        $lead = Lead::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully!',
                'lead' => $lead->load('users'),
                'statistics' => $this->getStatisticsBasedOnRole(),
            ]);
        }
  
        return redirect()->route('sells.index')->with('success', 'Lead created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        // Check if user can view this lead
        $this->authorizeLeadAccess($lead);
        
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
    // Check if user can update this lead
    $this->authorizeLeadAccess($lead);
    
    $userRole = Auth::user()->role;
    
    $validated = $request->validate([
        'car_model' => 'required|string|max:255',
        'client_name' => 'required|string|max:255',
        'client_phone' => 'required|string|max:20',
        'client_email' => 'nullable|email|max:255',
        'purchase_type' => ['required', Rule::in(['Cash', 'Finance'])],
        'client_budget' => 'required|numeric|min:0',
        'status' => ['required', Rule::in(['Active', 'Closed', 'Unsuccessful'])],
        'salesperson_id' => 'nullable|exists:users,id', // Changed from 'required' to 'nullable'
        'follow_up_required' => 'boolean',
        'notes' => 'nullable|string|max:1000',
        'commitment_amount' => 'required|numeric|min:0',
    ]);

    // For regular users, don't allow changing the salesperson
    if (!in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
        unset($validated['salesperson_id']);
    } else if (!isset($validated['salesperson_id'])) {
        // If salesperson_id not provided, keep the existing one
        $validated['salesperson_id'] = $lead->salesperson_id;
    }

    $validated['follow_up_required'] = $request->has('follow_up_required');

    $lead->update($validated);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully!',
            'lead' => $lead->load('users'),
            'statistics' => $this->getStatisticsBasedOnRole(),
        ]);
    }

    return redirect()->route('dashboard.leads.index')->with('success', 'Lead updated successfully!');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        // Check if user can delete this lead (only privileged roles)
        $userRole = Auth::user()->role;
        if (!in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant','Salesperson', 'Sales-Manager'])) {
            abort(403, 'Unauthorized action.');
        }
        
        $lead->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully!',
                'statistics' => $this->getStatisticsBasedOnRole(),
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

        $userRole = Auth::user()->role;
        
        // Build the update query based on role
        $query = Lead::whereIn('id', $request->lead_ids);
        
        // For regular users, only allow updating their own leads
        if (!in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $query->where('salesperson_id', Auth::id());
        }

        $count = $query->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => "{$count} leads updated successfully!",
            'statistics' => $this->getStatisticsBasedOnRole(),
        ]);
    }

    /**
     * Export leads to CSV.
     */
    public function export(Request $request)
    {
        $userRole = Auth::user()->role;
        
        // Initialize query based on role
        if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $query = Lead::with('users');
        } else {
            $query = Lead::with('users')->where('salesperson_id', Auth::id());
        }

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

        // Only allow filtering by salesperson for privileged roles
        if ($request->filled('salesperson_id') && in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
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
     * Calculate conversion rate based on role.
     */
    protected function calculateConversionRate()
    {
        $userRole = Auth::user()->role;
        
        if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $totalLeads = Lead::count();
            $closedLeads = Lead::where('status', 'Closed')->count();
        } else {
            $totalLeads = Lead::where('salesperson_id', Auth::id())->count();
            $closedLeads = Lead::where('salesperson_id', Auth::id())->where('status', 'Closed')->count();
        }
        
        if ($totalLeads === 0) {
            return 0;
        }

        return round(($closedLeads / $totalLeads) * 100, 2);
    }

    /**
     * Get leads data for API/AJAX requests based on role.
     */
    public function getData(Request $request)
    {
        $userRole = Auth::user()->role;
        
        // Initialize query based on role
        if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $query = Lead::with('users');
        } else {
            $query = Lead::with('users')->where('salesperson_id', Auth::id());
        }

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

        // Only allow filtering by salesperson for privileged roles
        if ($request->filled('salesperson_id') && in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            $query->where('salesperson_id', $request->input('salesperson_id'));
        }

        if ($request->filled('follow_up')) {
            $followUp = $request->input('follow_up') === 'yes';
            $query->where('follow_up_required', $followUp);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(1000);
        $statistics = $this->getStatisticsBasedOnRole();

        return response()->json([
            'leads' => $leads,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Check if the current user can access the given lead
     */
    private function authorizeLeadAccess(Lead $lead)
    {
        $userRole = Auth::user()->role;
        
        // Privileged roles can access all leads
        if (in_array($userRole, ['Managing-Director','Sales-Supervisor','General-Manager', 'Accountant', 'Sales-Manager'])) {
            return true;
        }
        
        // Regular users can only access their own leads
        if ($lead->salesperson_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this lead.');
        }
        
        return true;
    }
}