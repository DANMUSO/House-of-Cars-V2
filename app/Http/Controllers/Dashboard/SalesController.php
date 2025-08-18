<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleInspection;
use App\Models\InCash;
use App\Models\CarImport;
use App\Models\HirePurchase;
use App\Models\CustomerVehicle;
use App\Models\HirePurchaseAgreement;
use App\Models\GentlemanAgreement;
use Carbon\Carbon;
use App\Models\Installment;
use App\Models\Payment;
class SalesController extends Controller
{
   
    public function index()
{
     // Collect IDs from InCash, HirePurchaseAgreement, and GentlemanAgreement
        $importedIds = array_merge(
            InCash::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            HirePurchaseAgreement::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            GentlemanAgreement::whereNotNull('imported_id')->pluck('imported_id')->toArray()
        );

        $customerIds = array_merge(
            InCash::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            HirePurchaseAgreement::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            GentlemanAgreement::whereNotNull('customer_id')->pluck('customer_id')->toArray()
        );

        // Filter VehicleInspections where cars are not already in InCash, HirePurchase, or GentlemanAgreement
        $cars = VehicleInspection::with('carsImport', 'customerVehicle')
            ->whereDoesntHave('carsImport', function ($query) use ($importedIds) {
                $query->whereIn('id', $importedIds);
            })
            ->whereDoesntHave('customerVehicle', function ($query) use ($customerIds) {
                $query->whereIn('id', $customerIds);
            })
            ->latest()
            ->get();
    $inCashes = InCash::latest()->get();
    $importCars = CarImport::whereIn('id', $inCashes->where('car_type', 'import')->pluck('car_id'))->get()->keyBy('id');
    $customerCars = CustomerVehicle::whereIn('id', $inCashes->where('car_type', 'customer')->pluck('car_id'))->get()->keyBy('id');

    // Calculate statistics for the dashboard
    $totalAmount = $inCashes->sum('Amount');
    $totalTransactions = $inCashes->count(); 
    $pendingApproval = $inCashes->where('status', '!=', 1)->count();
    $averageSale = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

    // Car type distribution
    $importedCarsCount = $inCashes->where('car_type', 'import')->count();
    $customerCarsCount = $inCashes->where('car_type', 'customer')->count();
    $importedCarsPercentage = $totalTransactions > 0 ? round(($importedCarsCount / $totalTransactions) * 100, 1) : 0;
    $customerCarsPercentage = $totalTransactions > 0 ? round(($customerCarsCount / $totalTransactions) * 100, 1) : 0;

    // Monthly data
    $thisMonth = now()->month;
    $thisYear = now()->year;
    $lastMonth = now()->subMonth()->month;
    $lastMonthYear = now()->year;

    $thisMonthAmount = $inCashes->filter(function ($cash) use ($thisMonth, $thisYear) {
        return $cash->created_at->month == $thisMonth && $cash->created_at->year == $thisYear;
    })->sum('Amount');

    $thisMonthTransactions = $inCashes->filter(function ($cash) use ($thisMonth, $thisYear) {
        return $cash->created_at->month == $thisMonth && $cash->created_at->year == $thisYear;
    })->count();

    $lastMonthAmount = $inCashes->filter(function ($cash) use ($lastMonth, $lastMonthYear) {
        return $cash->created_at->month == $lastMonth && $cash->created_at->year == $lastMonthYear;
    })->sum('Amount');

    $lastMonthTransactions = $inCashes->filter(function ($cash) use ($lastMonth, $lastMonthYear) {
        return $cash->created_at->month == $lastMonth && $cash->created_at->year == $lastMonthYear;
    })->count();

    // Monthly chart data (last 6 months)
    $monthlyData = [];
    $monthlyLabels = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthlyLabels[] = $month->format('M Y');
        $monthAmount = $inCashes->filter(function ($cash) use ($month) {
            return $cash->created_at->format('Y-m') == $month->format('Y-m');
        })->sum('Amount');
        $monthlyData[] = $monthAmount;
    }

    return view('sells.incash', compact(
        'cars', 
        'inCashes', 
        'importCars', 
        'customerCars',
        'totalAmount', 
        'totalTransactions', 
        'pendingApproval', 
        'averageSale',
        'importedCarsCount', 
        'customerCarsCount', 
        'importedCarsPercentage', 
        'customerCarsPercentage',
        'thisMonthAmount', 
        'thisMonthTransactions', 
        'lastMonthAmount', 
        'lastMonthTransactions',
        'monthlyData', 
        'monthlyLabels'
    ));
}
    public function hirepurchase()
    {
            // Collect IDs from both InCash and fleetacquisition
        $importedIds = array_merge(
            InCash::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            HirePurchase::whereNotNull('imported_id')->pluck('imported_id')->toArray()
        );

        $customerIds = array_merge(
            InCash::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            HirePurchase::whereNotNull('customer_id')->pluck('customer_id')->toArray()
        );

        // Filter VehicleInspections where cars are not already in InCash or HirePurchase
        $cars = VehicleInspection::with('carsImport', 'customerVehicle')
            ->whereDoesntHave('carsImport', function ($query) use ($importedIds) {
                $query->whereIn('id', $importedIds);
            })
            ->whereDoesntHave('customerVehicle', function ($query) use ($customerIds) {
                $query->whereIn('id', $customerIds);
            })
            ->latest()
            ->get();

        // Fetch existing HirePurchase records
        $HirePurchases = HirePurchase::latest()->get();

        // Group imported and customer cars for display
        $importCars = CarImport::whereIn('id', $HirePurchases->where('car_type', 'import')->pluck('car_id'))->get()->keyBy('id');
        $customerCars = CustomerVehicle::whereIn('id', $HirePurchases->where('car_type', 'customer')->pluck('car_id'))->get()->keyBy('id');

        // Return to view
        return view('sells.hirepurchase', compact('cars', 'HirePurchases', 'importCars', 'customerCars'));

       
    }
    public function fleetacquisition()
    {
            // Collect IDs from both InCash and FleetAcquisition
        $importedIds = array_merge(
            InCash::whereNotNull('imported_id')->pluck('imported_id')->toArray(),
            FleetAcquisition::whereNotNull('imported_id')->pluck('imported_id')->toArray()
        );

        $customerIds = array_merge(
            InCash::whereNotNull('customer_id')->pluck('customer_id')->toArray(),
            FleetAcquisition::whereNotNull('customer_id')->pluck('customer_id')->toArray()
        );

        // Filter VehicleInspections where cars are not already in InCash or FleetAcquisition
        $cars = VehicleInspection::with('carsImport', 'customerVehicle')
            ->whereDoesntHave('carsImport', function ($query) use ($importedIds) {
                $query->whereIn('id', $importedIds);
            })
            ->whereDoesntHave('customerVehicle', function ($query) use ($customerIds) {
                $query->whereIn('id', $customerIds);
            })
            ->latest()
            ->get();

        // Fetch existing FleetAcquisition records
        $FleetAcquisition = FleetAcquisition::latest()->get();

        // Group imported and customer cars for display
        $importCars = CarImport::whereIn('id', $FleetAcquisition->where('car_type', 'import')->pluck('car_id'))->get()->keyBy('id');
        $customerCars = CustomerVehicle::whereIn('id', $FleetAcquisition->where('car_type', 'customer')->pluck('car_id'))->get()->keyBy('id');

        // Return to view
        return view('sells.fleetacquisition', compact('cars', 'FleetAcquisition', 'importCars', 'customerCars'));

       
    }
    public function sales()
    {
        return view('sells.sales');
    }
    public function leads()
    {
        return view('sells.leads');
    }
    public function gatepasscard()
    {
        $incash = InCash::with('carImport', 'customerVehicle')->where('status', 1)->latest()->get();
        $hirePurchases = HirePurchaseAgreement::with(['carImport', 'customerVehicle'])
            ->where('status', 'approved')
            ->get();
        $gentlemanagreement = GentlemanAgreement::with(['carImport', 'customerVehicle'])
            ->where('status', 'active')
            ->get();

        // Add type identifier to each collection
        $incash->each(function ($item) {
            $item->record_type = 'incash';
        });

        $hirePurchases->each(function ($item) {
            $item->record_type = 'hire_purchase';
        });

        $gentlemanagreement->each(function ($item) {
            $item->record_type = 'gentleman_agreement';
        });

        // Combine all three collections and sort by creation date
        $combined = $incash->merge($hirePurchases)
                        ->merge($gentlemanagreement)
                        ->sortByDesc('created_at');

        return view('sells.index', compact('combined'));
    }

    public function storeincash(Request $request)
    {
        $request->validate([
            'Client_Name' => 'required|string|max:255',
            'Phone_No' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'KRA' => 'required|string|max:255',
            'National_ID' => 'required|string|max:255',
            'Amount' => 'required|numeric',
            'PaidAmount'  => 'required|numeric',
            'car_id' => 'required|string',
        ]);
        
        $carSelection = $request->input('car_id');
        $car_type = str_starts_with($carSelection, 'import-') ? 'import' : 'customer';
        $car_id = (int) str_replace(['import-', 'customer-'], '', $carSelection);
        
        // Use 0 instead of null for non-applicable IDs
        $data = [
            'Client_Name'   => $request->input('Client_Name'),
            'Phone_No'      => $request->input('Phone_No'),
            'email'         => $request->input('email'),
            'KRA'           => $request->input('KRA'),
            'National_ID'   => $request->input('National_ID'),
            'Amount'        => $request->input('Amount'),
            'paid_amount'        => $request->input('PaidAmount'),
            'car_type'      => $car_type,
            'car_id'        => $car_id,
            'imported_id'   => $car_type === 'import' ? $car_id : 0,
            'customer_id'   => $car_type === 'customer' ? $car_id : 0,
        ];
        
        InCash::create($data);
        
        return response()->json(['message' => 'Info submitted successfully!']);
            
         
    }

    public function storehirepurchase(Request $request)
    {
        $request->validate([
            'Client_Name' => 'required|string|max:255',
            'Phone_No' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'KRA' => 'required|string|max:255',
            'National_ID' => 'required|string|max:255',
            'Amount' => 'required|numeric',
            'Deposit' => 'required|numeric',
            'duration' => 'required|numeric',
            'car_id' => 'required|string',
            'first_due_date' => 'required|date',
        ]);
        
        $carSelection = $request->input('car_id');
        $car_type = str_starts_with($carSelection, 'import-') ? 'import' : 'customer';
        $car_id = (int) str_replace(['import-', 'customer-'], '', $carSelection);

        // Calculate balance and monthly installment
        $amount = $request->input('Amount');
        $deposit = $request->input('Deposit');
        $duration = $request->input('duration');
        $balance = $amount - $deposit;

         // Parse first due date
        $firstDueDate = Carbon::parse($request->input('first_due_date'));

        // Calculate last due date
        $lastDueDate = $firstDueDate->copy()->addMonths($duration - 1);

        if ($duration <= 0) {
            return back()->with('error', 'Duration must be greater than 0 months.');
        }

        $monthlyInstallment = $balance / $duration;
        
        // Use 0 instead of null for non-applicable IDs
        $data = [
            'Client_Name'   => $request->input('Client_Name'),
            'Phone_No'      => $request->input('Phone_No'),
            'email'         => $request->input('email'),
            'KRA'           => $request->input('KRA'),
            'National_ID'   => $request->input('National_ID'),
            'Amount'        => $request->input('Amount'),
            'duration'      => $request->input('duration'),
            'deposit'       => $request->input('Deposit'),
            'paid_percentage'       => ($request->input('Deposit')/$request->input('Amount')) * 100,
            'car_type'      => $car_type,
            'car_id'        => $car_id,
            'imported_id'   => $car_type === 'import' ? $car_id : 0,
            'customer_id'   => $car_type === 'customer' ? $car_id : 0,
            'first_due_date'   => $firstDueDate->format('Y-m-d'),
            'last_due_date'    => $lastDueDate->format('Y-m-d'),
        ];
        
        // Create hire purchase record
        $hirePurchase = HirePurchase::create($data);
        
        // Create installments
        for ($i = 1; $i <= $duration; $i++) {
            $dueDate = $firstDueDate->copy()->addMonths($i - 1);

            Installment::create([
                'hire_purchase_id' => $hirePurchase->id,
                'amount'           => $monthlyInstallment,
                'due_date'         => $dueDate->format('Y-m-d'),
                'status'           => 'pending',
            ]);
        }
        
        return response()->json(['message' => 'Info submitted successfully!']);
            
         
    }
    

    public function updateincash(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'Client_Name' => 'required|string|max:255',
            'Phone_No' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'KRA' => 'required|string|max:255',
            'National_ID' => 'required|string|max:255',
            'Amount' => 'required|numeric',
            'PaidAmount' => 'required|numeric',
        ]);

        // 2. Extract car_type and car_id
        $carSelection = $request->input('car_id');
        $car_type = str_starts_with($carSelection, 'import-') ? 'import' : 'customer';
        $car_id = (int) str_replace(['import-', 'customer-'], '', $carSelection);

        // 3. Build data for update
        $data = [
            'Client_Name'   => $request->input('Client_Name'),
            'Phone_No'      => $request->input('Phone_No'),
            'email'         => $request->input('email'),
            'KRA'           => $request->input('KRA'),
            'National_ID'   => $request->input('National_ID'),
            'Amount'        => $request->input('Amount'),
            'paid_amount' => $request->input('PaidAmount'),
        ];

        $id =$request->input('id');
        // 4. Find the existing record
        $incash = InCash::findOrFail($id);

        // 5. Update and save
        $incash->update($data);

        // 6. Return response (optional)
        return redirect()->back()->with('success', 'Record updated successfully.');
    }
    
    public function updatehirepurchase(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'Client_Name' => 'required|string|max:255',
            'Phone_No' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'KRA' => 'required|string|max:255',
            'National_ID' => 'required|string|max:255',
            'Amount' => 'required|numeric',
            'Deposit' => 'required|numeric',
        ]);

        // 2. Extract car_type and car_id
        $carSelection = $request->input('car_id');
        $car_type = str_starts_with($carSelection, 'import-') ? 'import' : 'customer';
        $car_id = (int) str_replace(['import-', 'customer-'], '', $carSelection);

        // 3. Build data for update
        $data = [
            'Client_Name'   => $request->input('Client_Name'),
            'Phone_No'      => $request->input('Phone_No'),
            'email'         => $request->input('email'),
            'KRA'           => $request->input('KRA'),
            'National_ID'   => $request->input('National_ID'),
            'Amount'        => $request->input('Amount'),
            'deposit'        => $request->input('Deposit'),
            'paid_percentage'       => ($request->input('Deposit')/$request->input('Amount')) * 100,
        ];

        $id =$request->input('id');
        // 4. Find the existing record
        $HirePurchase = HirePurchase::findOrFail($id);

        // 5. Update and save
        $HirePurchase->update($data);

        // 6. Return response (optional)  
        return redirect()->back()->with('success', 'Record updated successfully.');
    }
    public function deleteincash(Request $request)
    {
       // Validate that 'id' exists in the 'in_cashes' table
        $request->validate([
            'id' => 'required|exists:in_cashes,id',
        ]);

        // Find the record and delete it permanently
        $cash = InCash::findOrFail($request->id);
        $cash->delete();

        return response()->json([
            'message' => 'Record deleted permanently!'
        ], 200);
    }
    public function approveincash(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:in_cashes,id',
        ]);

        $cash = InCash::findOrFail($request->id);
        $cash->status = 1;
        $cash->save();

        return response()->json([
            'message' => 'Record approved successfully!'
        ], 200);
    }
    public function approveHirePurchase(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:hire_purchases,id',
        ]);

        $cash = HirePurchase::findOrFail($request->id);
        $cash->status = 1;
        $cash->save();

        return response()->json([
            'message' => 'Record approved successfully!'
        ], 200);
    }

    public function deletehirepurchase(Request $request)
    {
      // Validate that 'id' exists in the 'hire_purchases' table 
        $request->validate([
            'id' => 'required|exists:hire_purchases,id',
        ]);

        
        // Find the HirePurchase record
        $hirePurchase = HirePurchase::findOrFail($request->id);

        // Delete related Installments
        Installment::where('hire_purchase_id', $hirePurchase->id)->delete();

        // Delete the HirePurchase record
        $hirePurchase->delete();

        return response()->json([
            'message' => 'Hire Purchase and its installments deleted permanently!'
        ], 200);
    }

    public function confirmhirepurchase(Request $request)
    {
    
        // Find the HirePurchase record
        $installment = Installment::findOrFail($request->id);
        $installment->status = 'paid';
        $installment->update();

        return response()->json([
            'message' => 'Installment is confirmed successfully!'
        ], 200);
    }
    public function profile(Request $request, $id){

        
        $hirePurchase  = HirePurchase::with(['installments', 'payments','customerVehicle', 'carImport'])
            ->where('id', $id)
            ->firstOrFail();
        
        return view('sells.installment_profile', compact('hirePurchase'));
        

    }
    public function storepayments(Request $request){

        $request->validate([
            'paid_amount' => 'required|numeric',
        ]);
        
        
        // Use 0 instead of null for non-applicable IDs
        $data = [
            'hire_purchase_id'   => $request->input('hire_id'),
            'amount'        => $request->input('paid_amount'),
        ];
        
        Payment::create($data);
        
        return response()->json(['message' => 'Amount submitted successfully!']);
    
    }
    
}
