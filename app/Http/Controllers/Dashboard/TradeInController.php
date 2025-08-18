<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerVehicle;
use Illuminate\Support\Facades\Storage;

class TradeInController extends Controller
{
    public function index()
    {
           
       $vehicles = CustomerVehicle::where('status', 1)->get(); // Fetch all vehicle records

        return view('tradein.index', compact('vehicles'));
    }

    public function store(Request $request)
        {
           // Validate input
            $validated = $request->validate([
                'Customer_Name'   => 'required|string|max:255',
                'Phone_No'        => 'required|string|max:20',
                'email'           => 'required|email|max:255',
                'Vehicle_Make'    => 'required|string|max:255',
                'Sell_Type'       => 'required|numeric|min:0',
                'Status'          => 'required|numeric|min:0',
                'Chasis_No'       => 'required|string|max:255|unique:customer_vehicles,chasis_no',
                'Number_Plate'    => 'required|string|max:255|unique:customer_vehicles,number_plate',
                'Minimum_Price'   => 'required|numeric|min:0',
                'photos.*'        => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);

            // Store images
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('car_bids', 'public');
                    $photoPaths[] = Storage::url($path); // Generates full public URL
                }
            }

            // Create vehicle record
            $vehicle = CustomerVehicle::create([
                'customer_name'  => $validated['Customer_Name'],
                'phone_no'       => $validated['Phone_No'],
                'email'          => $validated['email'],
                'vehicle_make'   => $validated['Vehicle_Make'],
                'chasis_no'      => $validated['Chasis_No'],
                'number_plate'   => $validated['Number_Plate'],
                'sell_type'      => $validated['Sell_Type'],
                'status'         => $validated['Status'],
                'minimum_price'  => $validated['Minimum_Price'],
                'photos'         => json_encode($photoPaths), // Save URLs for serving
            ]);

            return response()->json([
                'message' => 'Vehicle information saved successfully.',
                'data' => $vehicle
            ], 201);
        }

        public function update(Request $request)
        {
            // Validate the request data
            $validatedData = $request->validate([
                'sell_typev1'       => 'required|integer|in:1,2',
                'customer_namev1'   => 'required|string|max:255',
                'phone_nov1'        => 'required|string|max:20',
                'emailv1'           => 'required|email|max:255',
                'vehicle_makev1'    => 'required|string|max:255',
                'chasis_nov1'       => 'required|string|max:255',
                'number_platev1'    => 'required|string|max:255',
                'minimum_pricev1'   => 'required|numeric|min:0',
                'photosv1.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // ✅ photos are optional
            ]);
        
            try {
                // Find the vehicle by ID
                $vehicle = CustomerVehicle::findOrFail($request->id);
        
                // Build update array
                $updateData = [
                    'sell_type'      => $validatedData['sell_typev1'],
                    'customer_name'  => $validatedData['customer_namev1'],
                    'phone_no'       => $validatedData['phone_nov1'],
                    'email'          => $validatedData['emailv1'],
                    'vehicle_make'   => $validatedData['vehicle_makev1'],
                    'chasis_no'      => $validatedData['chasis_nov1'],
                    'number_plate'   => $validatedData['number_platev1'],
                    'minimum_price'  => $validatedData['minimum_pricev1'],
                ];
        
                // Handle photos only if present
                if ($request->hasFile('photosv1')) {
                    $photoPaths = [];
                    foreach ($request->file('photosv1') as $photo) {
                        $path = $photo->store('car_bids', 'public');
                        $photoPaths[] = Storage::url($path); // Save public URL
                    }
                    $updateData['photos'] = json_encode($photoPaths);
                }
        
                // Perform update
                $vehicle->update($updateData);
        
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle information updated successfully!'
                ]);
        
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update vehicle information: ' . $e->getMessage()
                ], 500);
            }
        }
}
