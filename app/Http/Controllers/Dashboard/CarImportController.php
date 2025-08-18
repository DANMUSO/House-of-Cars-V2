<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarImport;
use Illuminate\Support\Facades\Storage;

class CarImportController extends Controller
{
    public function store(Request $request)
        {
            $validated = $request->validate([
                'bidder_name' => 'required|string',
                'make' => 'required|string',
                'model' => 'required|string',
                'year' => 'required|numeric',
                'vin' => 'required|string',
                'engine_type' => 'required|string',
                'body_type' => 'required|string',
                'mileage' => 'required|numeric',
                'bid_amount' => 'required|numeric',
                'bid_start_date' => 'required|date',
                'bid_end_date' => 'required|date',
                //'deposit' => 'required|numeric',
                'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);

            $photoPaths = [];

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $photoPaths[] = $photo->store('car_bids', 'public');
                }
            }

            $validated['photos'] = json_encode($photoPaths);

            CarImport::create($validated);

            return response()->json(['message' => 'Bid created successfully']);
        }
        public function update(Request $request)
        {
            $request->validate([
                'id' => 'required|exists:car_imports,id',
                'editbidder_name' => 'required|string|max:255',
                'editmake' => 'required|string|max:255',
                'editmodel' => 'required|string|max:255',
                'edityear' => 'required|integer',
                'editvin' => 'required|string|max:255',
                'editengine_type' => 'required|string|max:255',
                'editbody_type' => 'required|string|max:255',
                'editmileage' => 'required|numeric',
                'editbid_amount' => 'required|numeric',
                'editbid_start_date' => 'required|date',
                'editbid_end_date' => 'required|date',
                //'editdeposit' => 'required|numeric',
                'editphotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $car = CarImport::findOrFail($request->id);

            // Update standard fields
            $car->bidder_name = $request->editbidder_name;
            $car->make = $request->editmake;
            $car->model = $request->editmodel;
            $car->year = $request->edityear;
            $car->vin = $request->editvin;
            $car->engine_type = $request->editengine_type;
            $car->body_type = $request->editbody_type;
            $car->mileage = $request->editmileage;
            $car->bid_amount = $request->editbid_amount;
            $car->bid_start_date = $request->editbid_start_date;
            $car->bid_end_date = $request->editbid_end_date;
            //$car->deposit = $request->editdeposit;
            $car->status = $request->editstatus;

            // If new photos are uploaded, process and overwrite the old ones
            if ($request->hasFile('editphotos')) {
                $photoPaths = [];

                foreach ($request->file('editphotos') as $photo) {
                    $photoPaths[] = $photo->store('car_bids', 'public');
                }

                $car->photos = json_encode($photoPaths);
            }

            $car->save();

            return response()->json([
                'success' => true,
                'message' => 'Car import details updated successfully',
            ]);
        }

    public function depositupdate(Request $request){
        // Validate the form input fullpayment
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'deposit' => $request->depositamount,
            'status' => '1'
        ]);
        return response()->json(['success' => 'Deposit updated successfully']);

    }
     public function fullamount(Request $request){
        // Validate the form input 
        $id = $request->id;
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'fullamount' => $request->fullamount,
            'status' => '4'
        ]);
        return response()->json(['success' => 'Full Payment updated successfully']);

    }
    public function index()
    {
        $carBids = CarImport::where('status', 0)->get(); // or paginate() if needed
        return view('carimport.index', compact('carBids'));
    }
    public function deposit()
    {
        $carBids = CarImport::where('status', 1)->get(); // or paginate() if needed
        return view('carimport.deposit', compact('carBids'));
    }
    public function winbid(Request $request){
        // Validate the form input
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '2'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function lostbid(Request $request){
        // Validate the form input 
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '3'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function confirmfullpayment(Request $request){
        // Validate the form input  
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '4'

        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function confirmimport(Request $request){
        // Validate the form input  confirmreception
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '5'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
   
    public function confirmimported(Request $request){
        // Validate the form input   
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '6'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function portcharges(Request $request){
        // Validate the form input   
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '7'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }
    public function confirmreception(Request $request){
        // Validate the form input  
        $id = $request->id;
    
        // Find the existing deposit record by ID
        $deposit = CarImport::findOrFail($id);
    
        // Update the deposit record with validated data
        $deposit->update([
            'status' => '8'
        ]);
        return response()->json(['success' => 'Bid updated successfully']);
    }

    public function shipping()
    {
        $carBids = CarImport::where('status', 4)->get();
        return view('carimport.shipping', compact('carBids'));
    }
    public function wonbids()
    {
        $carBids = CarImport::where('status', 2)->get();
        return view('carimport.wonbid', compact('carBids'));
    }
    public function lostbids()
    {
        $carBids = CarImport::where('status', 3)->get();
        return view('carimport.lostbid', compact('carBids'));
    }
    public function fullpayment()
    {
        $carBids = CarImport::all();
        return view('carimport.fullpayment', compact('carBids'));
    }
    public function shipment()
    {
        $carBids = CarImport::where('status', 5)->get();
        return view('carimport.shipment', compact('carBids'));
    }
    public function customduty()
    {
        $carBids = CarImport::where('status', 6)->get();
        return view('carimport.customduty', compact('carBids'));
    }
    public function receipt()
    {
        $carBids = CarImport::where('status', 7)->get();
        return view('carimport.receipt', compact('carBids'));
    }
    public function received()
    {
        $carBids = CarImport::where('status', 8)->get();
        return view('carimport.imported-received', compact('carBids'));
    }
    
}
