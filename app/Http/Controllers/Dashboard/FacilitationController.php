<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Facilitation;
use Illuminate\Http\Request;

class FacilitationController extends Controller
{
     public function index()
        {
            $userRole = Auth::user()->role;
            
            if (in_array($userRole, ['Managing-Director', 'Accountant'])) {
                // Show all facilitations for Managing Director and Accountant
                $facilitations = Facilitation::with('requester')
                                        ->orderBy('created_at', 'desc')
                                        ->get();
            } else {
                // Show only user's own facilitations for other roles
                $facilitations = Facilitation::where('request_id', Auth::id())
                                        ->with('requester')
                                        ->orderBy('created_at', 'desc')
                                        ->get();
            }
            return view('facilitation.index', compact('facilitations'));
        }

    public function store(Request $request)
    {
        Facilitation::create([
            'request'    => $request->frequest,
            'amount'     => $request->famount,
            'status'     => 1,
            'request_id' => Auth::user()->id,
        ]);
        
        return response()->json([
            'message' => 'Request submitted successfully!',
        ]);
    }
    public function update(Request $request)
        {
            // Validate the form input
        $id = $request->id;
    
        // Find the existing employee record by ID
        $Facilitation = Facilitation::findOrFail($id);
    
        // Update the employee record with validated data
        $Facilitation->update([
            'request' => $request->editrequest,
            'amount' => $request->editamount,
        ]);
    
        return response()->json(['success' => 'Request updated successfully']);
        }

    public function approve(Request $request)
        {
            $id = $request->id;
    
            // Find the existing employee record by ID
            $Facilitation = Facilitation::findOrFail($id);
        
            // Update the employee record with validated data
            $Facilitation->update([
                'status' =>2,
            ]);

            return redirect()->route('Facilitation.requests')->with('success', 'Request updated successfully!');
        }
    public function reject(Request $request)
        {
            $id = $request->id;
    
            // Find the existing employee record by ID
            $Facilitation = Facilitation::findOrFail($id);
        
            // Update the employee record with validated data
            $Facilitation->update([
                'status' =>3,
            ]);

            return redirect()->route('Facilitation.requests')->with('success', 'Request rejected successfully!');
        }
}
