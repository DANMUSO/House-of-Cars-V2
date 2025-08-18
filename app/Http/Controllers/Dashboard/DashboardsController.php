<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardsController extends Controller
{
    public function index()
    {
        // Get current year and month for filtering
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        
        // 1. CARS STATISTICS
        $totalCarsSold = $this->getTotalCarsSold();
        $carsInShowroom = $this->getCarsInShowroom();
        $totalRevenue = $this->getTotalRevenue();
        $activeLeads = $this->getActiveLeads();
        $conversionRate = $this->getConversionRate();
        
        // 2. NEW STATISTICS REQUESTED
        $inspectedCarsCount = $this->getInspectedCarsCount();
        $soldThroughHirePurchase = $this->getSoldThroughHirePurchase();
        $soldThroughCash = $this->getSoldThroughCash();
        $soldThroughGentleman = $this->getSoldThroughGentleman();
        $totalSoldCarsPurchase = $this->getTotalSoldCarsPurchase();
        $fleetAcquisitionCount = $this->getFleetAcquisitionCount();
        $activeUsersCount = $this->getActiveUsersCount();
        
        // 3. SALES BY PAYMENT METHOD
        $salesByMethod = $this->getSalesByPaymentMethod();
        
        // 4. MONTHLY DATA FOR CHARTS
        $monthlyData = $this->getMonthlyData();
        $topSellingCars = $this->getTopSellingCars();
        
        // 5. RECENT TRANSACTIONS (Updated to show recent 5)
        $recentTransactions = $this->getRecentTransactions(5);
        
        // 6. FINANCIAL OVERVIEW
        $financialOverview = $this->getFinancialOverview();
        
        // 7. FLEET AND HIRE PURCHASE STATS
        $fleetStats = $this->getFleetStats();
        $hirePurchaseStats = $this->getHirePurchaseStats();
        
        // 8. ADDITIONAL DATA FOR ENHANCED DASHBOARD
        $performanceMetrics = $this->getPerformanceMetrics();
        $overduePayments = $this->getOverduePayments();
        $inventoryAnalysis = $this->getInventoryAnalysis();
        $staffPerformance = $this->getStaffPerformance();
        $collectionsForecast = $this->getCollectionsForecast();
        $alerts = $this->getDashboardAlerts();
        
        return view('admin.index', array_merge(
            compact(
                'totalCarsSold',
                'carsInShowroom', 
                'totalRevenue',
                'activeLeads',
                'conversionRate',
                'inspectedCarsCount',
                'soldThroughHirePurchase',
                'soldThroughCash',
                'soldThroughGentleman',
                'totalSoldCarsPurchase',
                'fleetAcquisitionCount',
                'activeUsersCount',
                'monthlyData',
                'topSellingCars',
                'recentTransactions',
                'financialOverview',
                'fleetStats',
                'hirePurchaseStats',
                'performanceMetrics',
                'overduePayments',
                'inventoryAnalysis',
                'staffPerformance',
                'collectionsForecast',
                'alerts'
            ),
            $salesByMethod,
            ['last_updated' => Carbon::now()->format('M d, Y h:i A')]
        ));
    }
    
    /**
     * NEW METHOD: Get number of inspected cars from vehicle_inspections table
     */
    private function getInspectedCarsCount()
    {
        return DB::table('vehicle_inspections')->count();
    }
    
    /**
     * NEW METHOD: Get number of cars sold through Hire Purchase
     */
    private function getSoldThroughHirePurchase()
    {
        return DB::table('hire_purchase_agreements')->count();
    }
    
    /**
     * NEW METHOD: Get number of cars sold through Cash (In Cash)
     */
    private function getSoldThroughCash()
    {
        return DB::table('in_cashes')->count();
    }
    
    /**
     * NEW METHOD: Get number of cars sold through Gentleman Agreement
     */
    private function getSoldThroughGentleman()
    {
        return DB::table('gentlemanagreements')->count();
    }
    
    /**
     * NEW METHOD: Get total sold cars from all purchase methods
     */
    private function getTotalSoldCarsPurchase()
    {
        return $this->getSoldThroughHirePurchase() + 
               $this->getSoldThroughCash() + 
               $this->getSoldThroughGentleman();
    }
    
    /**
     * NEW METHOD: Get number of fleet acquisitions
     */
    private function getFleetAcquisitionCount()
    {
        return DB::table('fleet_acquisitions')->count();
    }
    
    /**
     * NEW METHOD: Get number of active users (where deleted_at is null)
     */
    private function getActiveUsersCount()
    {
        return DB::table('users')
            ->whereNull('deleted_at')
            ->count();
    }
    
    /**
     * Get total cars sold across all payment methods
     */
    private function getTotalCarsSold()
    {
        // Count from all three sales tables
        $cashSales = DB::table('in_cashes')->count();
        $hirePurchaseSales = DB::table('hire_purchase_agreements')->count(); 
        $gentlemanSales = DB::table('gentlemanagreements')->count();
        
        return $cashSales + $hirePurchaseSales + $gentlemanSales;
    }
    
    /**
     * UPDATED METHOD: Get cars available in showroom based on new criteria
     * Cars are in car_imports or customer_vehicles tables with status 8 
     * and are NOT in the three sales tables (hire_purchase_agreements, in_cashes, gentlemanagreements)
     */
    private function getCarsInShowroom()
    {
        // Get all sold car IDs from the three sales tables
        $soldImportedIds = collect()
            ->merge(DB::table('in_cashes')->whereNotNull('imported_id')->pluck('imported_id'))
            ->merge(DB::table('hire_purchase_agreements')->whereNotNull('imported_id')->pluck('imported_id'))
            ->merge(DB::table('gentlemanagreements')->whereNotNull('imported_id')->pluck('imported_id'))
            ->unique()
            ->filter()
            ->toArray();
            
        $soldCustomerIds = collect()
            ->merge(DB::table('in_cashes')->whereNotNull('customer_id')->pluck('customer_id'))
            ->merge(DB::table('hire_purchase_agreements')->whereNotNull('customer_id')->pluck('customer_id'))
            ->merge(DB::table('gentlemanagreements')->whereNotNull('customer_id')->pluck('customer_id'))
            ->unique()
            ->filter()
            ->toArray();
        
        // Count available imported cars (status 8 and not sold)
        $availableImported = DB::table('car_imports')
            ->where('status', 8)
            ->whereNotIn('id', $soldImportedIds)
            ->count();
            
        // Count available customer cars (status 8 and not sold)
        $availableCustomer = DB::table('customer_vehicles')
            ->whereNotIn('id', $soldCustomerIds)
            ->count();
            
        return $availableImported + $availableCustomer;
    }
    
    /**
     * Get sold car IDs organized by source
     * If a car exists in any of the three sales tables, it's considered sold
     */
    private function getSoldCarIds()
    {
        $soldCarIds = [
            'imported' => [],
            'customer' => []
        ];
        
        // From in_cashes - get sold cars
        $inCashSold = DB::table('in_cashes')
            ->select('car_id', 'car_type', 'imported_id', 'customer_id')
            ->get();
            
        foreach ($inCashSold as $sale) {
            // Check if it's an imported car
            if ($sale->imported_id && $sale->imported_id > 0) {
                $soldCarIds['imported'][] = $sale->imported_id;
            }
            // Check if it's a customer car
            if ($sale->customer_id && $sale->customer_id > 0) {
                $soldCarIds['customer'][] = $sale->customer_id;
            }
            // Fallback to car_id and car_type for older records
            if ($sale->car_type === 'import' && $sale->car_id) {
                $soldCarIds['imported'][] = $sale->car_id;
            } elseif ($sale->car_type === 'customer' && $sale->car_id) {
                $soldCarIds['customer'][] = $sale->car_id;
            }
        }
        
        // From hire_purchase_agreements - get sold cars
        $hpSold = DB::table('hire_purchase_agreements')
            ->select('car_id', 'car_type', 'imported_id', 'customer_id')
            ->get();
            
        foreach ($hpSold as $sale) {
            // Check if it's an imported car
            if ($sale->imported_id && $sale->imported_id > 0) {
                $soldCarIds['imported'][] = $sale->imported_id;
            }
            // Check if it's a customer car
            if ($sale->customer_id && $sale->customer_id > 0) {
                $soldCarIds['customer'][] = $sale->customer_id;
            }
            // Fallback to car_id and car_type for older records
            if ($sale->car_type === 'import' && $sale->car_id) {
                $soldCarIds['imported'][] = $sale->car_id;
            } elseif ($sale->car_type === 'customer' && $sale->car_id) {
                $soldCarIds['customer'][] = $sale->car_id;
            }
        }
        
        // From gentlemanagreements - get sold cars
        $gentlemanSold = DB::table('gentlemanagreements')
            ->select('car_id', 'car_type', 'imported_id', 'customer_id')
            ->get();
            
        foreach ($gentlemanSold as $sale) {
            // Check if it's an imported car
            if ($sale->imported_id && $sale->imported_id > 0) {
                $soldCarIds['imported'][] = $sale->imported_id;
            }
            // Check if it's a customer car
            if ($sale->customer_id && $sale->customer_id > 0) {
                $soldCarIds['customer'][] = $sale->customer_id;
            }
            // Fallback to car_id and car_type for older records
            if ($sale->car_type === 'import' && $sale->car_id) {
                $soldCarIds['imported'][] = $sale->car_id;
            } elseif ($sale->car_type === 'customer' && $sale->car_id) {
                $soldCarIds['customer'][] = $sale->car_id;
            }
        }
        
        // Remove duplicates and filter out null/empty values
        $soldCarIds['imported'] = array_unique(array_filter($soldCarIds['imported']));
        $soldCarIds['customer'] = array_unique(array_filter($soldCarIds['customer']));
        
        return $soldCarIds;
    }
    
    /**
     * Get sales count by payment method
     */
    private function getSalesByPaymentMethod()
    {
        return [
            'cashSalesCount' => $this->getSoldThroughCash(),
            'hirePurchaseCount' => $this->getSoldThroughHirePurchase(),
            'gentlemanSalesCount' => $this->getSoldThroughGentleman(),
            'fleetSalesCount' => $this->getFleetAcquisitionCount()
        ];
    }
    
    /**
     * Calculate total revenue from all sources
     */
    private function getTotalRevenue()
    {
        // Revenue from cash sales
        $cashRevenue = DB::table('in_cashes')->sum('paid_amount');
        
        // Revenue from hire purchase (total amount paid so far)
        $hpRevenue = DB::table('hire_purchase_payments')->sum('amount');
        
        // Revenue from gentleman agreements (total amount paid so far)  
        $gentlemanRevenue = DB::table('gentlemanagreements')->sum('amount_paid');
        
        // Revenue from fleet acquisitions
        $fleetRevenue = DB::table('fleet_payments')->sum('payment_amount');
        
        return $cashRevenue + $hpRevenue + $gentlemanRevenue + $fleetRevenue;
    }
    
    /**
     * Get active leads count
     */
    private function getActiveLeads()
    {
        return DB::table('leads')
            ->where('status', 'Active')
            ->count();
    }
    
    /**
     * Calculate conversion rate (closed leads / total leads)
     */
    private function getConversionRate()
    {
        $totalLeads = DB::table('leads')->count();
        $closedLeads = DB::table('leads')
            ->where('status', 'Closed')
            ->count();
            
        if ($totalLeads == 0) return 0;
        
        return round(($closedLeads / $totalLeads) * 100, 2);
    }
    
    /**
     * Get monthly financial data for charts
     */
    private function getMonthlyData()
    {
        $months = [];
        $income = [];
        $expenses = [];
        $revenue = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create(null, $i, 1)->format('M');
            
            // Monthly income from all payment sources
            $monthlyHPIncome = DB::table('hire_purchase_payments')
                ->whereMonth('payment_date', $i)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount') / 1000; // Convert to thousands
                
            $monthlyFleetIncome = DB::table('fleet_payments')
                ->whereMonth('payment_date', $i)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('payment_amount') / 1000;
                
            $monthlyCashIncome = DB::table('in_cashes')
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('paid_amount') / 1000;
                
            $income[] = round($monthlyHPIncome + $monthlyFleetIncome + $monthlyCashIncome, 1);
            
            // Monthly expenses (facilitations)
            $monthlyExpenses = DB::table('facilitations')
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', Carbon::now()->year)
                ->where('status', '2') // Approved
                ->sum('amount') / 1000;
                
            $expenses[] = round($monthlyExpenses, 1);
            
            // Revenue calculation
            $revenue[] = round($income[$i-1] - $expenses[$i-1], 1);
        }
        
        return [
            'months' => $months,
            'income' => $income,
            'expenses' => $expenses,
            'revenue' => $revenue
        ];
    }
    
    /**
     * Get top selling car makes from sold vehicles
     * Cars are considered sold if they exist in any of the three sales tables
     */
    private function getTopSellingCars()
    {
        $carMakes = collect();
        
        // From in_cashes with imported cars
        $cashImportedSales = DB::table('in_cashes as ic')
            ->leftJoin('car_imports as ci', function($join) {
                $join->on('ic.imported_id', '=', 'ci.id')
                     ->orWhere(function($query) {
                         $query->on('ic.car_id', '=', 'ci.id')
                               ->where('ic.car_type', '=', 'import');
                     });
            })
            ->whereNotNull('ci.make')
            ->select('ci.make')
            ->get();
            
        // From in_cashes with customer cars  
        $cashCustomerSales = DB::table('in_cashes as ic')
            ->leftJoin('customer_vehicles as cv', function($join) {
                $join->on('ic.customer_id', '=', 'cv.id')
                     ->orWhere(function($query) {
                         $query->on('ic.car_id', '=', 'cv.id')
                               ->where('ic.car_type', '=', 'customer');
                     });
            })
            ->whereNotNull('cv.vehicle_make')
            ->select('cv.vehicle_make as make')
            ->get();
            
        // From hire_purchase_agreements with imported cars
        $hpImportedSales = DB::table('hire_purchase_agreements as hpa')
            ->leftJoin('car_imports as ci', function($join) {
                $join->on('hpa.imported_id', '=', 'ci.id')
                     ->orWhere(function($query) {
                         $query->on('hpa.car_id', '=', 'ci.id')
                               ->where('hpa.car_type', '=', 'import');
                     });
            })
            ->whereNotNull('ci.make')
            ->select('ci.make')
            ->get();
            
        // From hire_purchase_agreements with customer cars
        $hpCustomerSales = DB::table('hire_purchase_agreements as hpa')
            ->leftJoin('customer_vehicles as cv', function($join) {
                $join->on('hpa.customer_id', '=', 'cv.id')
                     ->orWhere(function($query) {
                         $query->on('hpa.car_id', '=', 'cv.id')
                               ->where('hpa.car_type', '=', 'customer');
                     });
            })
            ->whereNotNull('cv.vehicle_make')
            ->select('cv.vehicle_make as make')
            ->get();
            
        // From gentlemanagreements with imported cars
        $gentlemanImportedSales = DB::table('gentlemanagreements as ga')
            ->leftJoin('car_imports as ci', function($join) {
                $join->on('ga.imported_id', '=', 'ci.id')
                     ->orWhere(function($query) {
                         $query->on('ga.car_id', '=', 'ci.id')
                               ->where('ga.car_type', '=', 'import');
                     });
            })
            ->whereNotNull('ci.make')
            ->select('ci.make')
            ->get();
            
        // From gentlemanagreements with customer cars  
        $gentlemanCustomerSales = DB::table('gentlemanagreements as ga')
            ->leftJoin('customer_vehicles as cv', function($join) {
                $join->on('ga.customer_id', '=', 'cv.id')
                     ->orWhere(function($query) {
                         $query->on('ga.car_id', '=', 'cv.id')
                               ->where('ga.car_type', '=', 'customer');
                     });
            })
            ->whereNotNull('cv.vehicle_make')
            ->select('cv.vehicle_make as make')
            ->get();
        
        // Combine all sales and count by make
        $allSales = collect()
            ->merge($cashImportedSales)
            ->merge($cashCustomerSales)
            ->merge($hpImportedSales)
            ->merge($hpCustomerSales)
            ->merge($gentlemanImportedSales)
            ->merge($gentlemanCustomerSales);
        
        $makesCounts = $allSales->filter(function($item) {
                return !empty($item->make);
            })
            ->groupBy('make')
            ->map(function ($group, $make) {
                return (object) [
                    'make' => $make,
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();
        
        return $makesCounts;
    }
    
    /**
     * UPDATED METHOD: Get recent transactions from all sales sources (parameterized limit)
     */
    private function getRecentTransactions($limit = 10)
    {
        $transactions = collect();
        
        // Cash sales with imported cars
        $cashImportedTransactions = DB::table('in_cashes as ic')
            ->leftJoin('car_imports as ci', function($join) {
                $join->on('ic.imported_id', '=', 'ci.id')
                     ->orWhere(function($query) {
                         $query->on('ic.car_id', '=', 'ci.id')
                               ->where('ic.car_type', '=', 'import');
                     });
            })
            ->whereNotNull('ci.make')
            ->select(
                'ci.make',
                'ci.model', 
                'ic.Client_Name as client_name',
                'ic.Phone_No as phone',
                'ic.paid_amount as amount',
                'ic.created_at',
                DB::raw("'Cash Sale' as payment_mode")
            )
            ->orderBy('ic.created_at', 'desc')
            ->limit($limit)
            ->get();
            
        // Cash sales with customer cars
        $cashCustomerTransactions = DB::table('in_cashes as ic')
            ->leftJoin('customer_vehicles as cv', function($join) {
                $join->on('ic.customer_id', '=', 'cv.id')
                     ->orWhere(function($query) {
                         $query->on('ic.car_id', '=', 'cv.id')
                               ->where('ic.car_type', '=', 'customer');
                     });
            })
            ->whereNotNull('cv.vehicle_make')
            ->select(
                'cv.vehicle_make as make',
                DB::raw("'N/A' as model"), 
                'ic.Client_Name as client_name',
                'ic.Phone_No as phone',
                'ic.paid_amount as amount',
                'ic.created_at',
                DB::raw("'Cash Sale' as payment_mode")
            )
            ->orderBy('ic.created_at', 'desc')
            ->limit($limit)
            ->get();
            
        // Hire purchase agreements with imported cars
        $hpImportedTransactions = DB::table('hire_purchase_agreements as hpa')
            ->leftJoin('car_imports as ci', function($join) {
                $join->on('hpa.imported_id', '=', 'ci.id')
                     ->orWhere(function($query) {
                         $query->on('hpa.car_id', '=', 'ci.id')
                               ->where('hpa.car_type', '=', 'import');
                     });
            })
            ->whereNotNull('ci.make')
            ->select(
                'ci.make',
                'ci.model',
                'hpa.client_name',
                'hpa.phone_number as phone',
                'hpa.vehicle_price as amount',
                'hpa.created_at',
                DB::raw("'Hire Purchase' as payment_mode")
            )
            ->orderBy('hpa.created_at', 'desc')
            ->limit($limit)
            ->get();
            
        // Hire purchase agreements with customer cars
        $hpCustomerTransactions = DB::table('hire_purchase_agreements as hpa')
            ->leftJoin('customer_vehicles as cv', function($join) {
                $join->on('hpa.customer_id', '=', 'cv.id')
                     ->orWhere(function($query) {
                         $query->on('hpa.car_id', '=', 'cv.id')
                               ->where('hpa.car_type', '=', 'customer');
                     });
            })
            ->whereNotNull('cv.vehicle_make')
            ->select(
                'cv.vehicle_make as make',
                DB::raw("'N/A' as model"),
                'hpa.client_name',
                'hpa.phone_number as phone',
                'hpa.vehicle_price as amount',
                'hpa.created_at',
                DB::raw("'Hire Purchase' as payment_mode")
            )
            ->orderBy('hpa.created_at', 'desc')
            ->limit($limit)
            ->get();
            
        // Gentleman agreements with imported cars
        $gentlemanImportedTransactions = DB::table('gentlemanagreements as ga')
            ->leftJoin('car_imports as ci', function($join) {
                $join->on('ga.imported_id', '=', 'ci.id')
                     ->orWhere(function($query) {
                         $query->on('ga.car_id', '=', 'ci.id')
                               ->where('ga.car_type', '=', 'import');
                     });
            })
            ->whereNotNull('ci.make')
            ->select(
                'ci.make',
                'ci.model',
                'ga.client_name',
                'ga.phone_number as phone',
                'ga.vehicle_price as amount',
                'ga.created_at',
                DB::raw("'Gentleman Agreement' as payment_mode")
            )
            ->orderBy('ga.created_at', 'desc')
            ->limit($limit)
            ->get();
            
        // Gentleman agreements with customer cars
        $gentlemanCustomerTransactions = DB::table('gentlemanagreements as ga')
            ->leftJoin('customer_vehicles as cv', function($join) {
                $join->on('ga.customer_id', '=', 'cv.id')
                     ->orWhere(function($query) {
                         $query->on('ga.car_id', '=', 'cv.id')
                               ->where('ga.car_type', '=', 'customer');
                     });
            })
            ->whereNotNull('cv.vehicle_make')
            ->select(
                'cv.vehicle_make as make',
                DB::raw("'N/A' as model"),
                'ga.client_name',
                'ga.phone_number as phone',
                'ga.vehicle_price as amount',
                'ga.created_at',
                DB::raw("'Gentleman Agreement' as payment_mode")
            )
            ->orderBy('ga.created_at', 'desc')
            ->limit($limit)
            ->get();
            
        $transactions = $transactions
            ->merge($cashImportedTransactions)
            ->merge($cashCustomerTransactions)
            ->merge($hpImportedTransactions)
            ->merge($hpCustomerTransactions)
            ->merge($gentlemanImportedTransactions)
            ->merge($gentlemanCustomerTransactions)
            ->sortByDesc('created_at')
            ->take($limit);
                                   
        return $transactions;
    }
    
    /**
     * Get performance metrics for comparison
     */
    private function getPerformanceMetrics()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth();
        
        // Current month revenue from all sources
        $currentMonthSales = DB::table('in_cashes')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('paid_amount');
            
        $currentMonthHP = DB::table('hire_purchase_payments')
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');
            
        $currentMonthTotal = $currentMonthSales + $currentMonthHP;
            
        // Last month revenue
        $lastMonthSales = DB::table('in_cashes')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->sum('paid_amount');
            
        $lastMonthHP = DB::table('hire_purchase_payments')
            ->whereMonth('payment_date', $lastMonth->month)
            ->whereYear('payment_date', $lastMonth->year)
            ->sum('amount');
            
        $lastMonthTotal = $lastMonthSales + $lastMonthHP;
            
        $salesGrowth = $lastMonthTotal > 0 ? 
            round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 2) : 0;
            
        return [
            'current_month_sales' => $currentMonthTotal,
            'last_month_sales' => $lastMonthTotal,
            'sales_growth' => $salesGrowth,
            'growth_direction' => $salesGrowth >= 0 ? 'up' : 'down'
        ];
    }

    /**
     * Get overdue payments summary
     */
    private function getOverduePayments()
    {
        $overdueHP = DB::table('hire_purchase_agreements')
            ->where('is_overdue', 1)
            ->where('status', 'active')
            ->sum('outstanding_balance');
            
        $overdueGentleman = DB::table('gentlemanagreements')
            ->where('is_overdue', 1)
            ->where('status', 'active')
            ->sum('outstanding_balance');
            
        $overdueCount = DB::table('hire_purchase_agreements')
            ->where('is_overdue', 1)
            ->where('status', 'active')
            ->count() + 
            DB::table('gentlemanagreements')
            ->where('is_overdue', 1)
            ->where('status', 'active')
            ->count();
            
        return [
            'total_overdue_amount' => $overdueHP + $overdueGentleman,
            'overdue_count' => $overdueCount
        ];
    }

    /**
     * Get inventory turnover analysis
     */
    private function getInventoryAnalysis()
    {
        $soldCarIds = $this->getSoldCarIds();
        
        // Cars that have been in showroom for more than 90 days (and not sold)
        $staleInventory = DB::table('car_imports')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->where('status', 8)
            ->whereNotIn('id', $soldCarIds['imported'])
            ->count() +
            DB::table('customer_vehicles')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->where('status', 8)
            ->whereNotIn('id', $soldCarIds['customer'])
            ->count();
            
        // Fast-moving inventory (sold within 30 days)
        $fastMoving = DB::table('in_cashes')
            ->where('created_at', '>', Carbon::now()->subDays(30))
            ->count() +
            DB::table('hire_purchase_agreements')
            ->where('created_at', '>', Carbon::now()->subDays(30))
            ->count() +
            DB::table('gentlemanagreements')
            ->where('created_at', '>', Carbon::now()->subDays(30))
            ->count();
            
        return [
            'stale_inventory' => $staleInventory,
            'fast_moving' => $fastMoving,
            'inventory_health' => $staleInventory > 0 ? 
                round(($fastMoving / ($fastMoving + $staleInventory)) * 100, 1) : 100
        ];
    }

    /**
     * Get staff performance summary
     */
    private function getStaffPerformance()
    {
        return DB::table('leads as l')
            ->join('users as u', 'l.salesperson_id', '=', 'u.id')
            ->select(
                'u.first_name',
                'u.last_name',
                DB::raw('COUNT(l.id) as total_leads'),
                DB::raw('SUM(CASE WHEN l.status = "Closed" THEN 1 ELSE 0 END) as closed_leads'),
                DB::raw('SUM(l.commitment_amount) as total_commitments')
            )
            ->where('l.created_at', '>=', Carbon::now()->startOfMonth())
            ->whereNull('u.deleted_at')
            ->groupBy('u.id', 'u.first_name', 'u.last_name')
            ->orderByDesc('closed_leads')
            ->limit(5)
            ->get();
    }

    /**
     * Get payment collections forecast
     */
    private function getCollectionsForecast()
    {
        try {
            // Check if payment_schedules table exists, if not use alternative calculation
            $tableExists = DB::getSchemaBuilder()->hasTable('payment_schedules');
            
            if ($tableExists) {
                // Using payment_schedules table
                
                // Overdue collections (past due date, still pending)
                $overdueCollections = DB::table('payment_schedules')
                    ->where('due_date', '<', Carbon::now()->startOfDay())
                    ->where('status', 'pending')
                    ->sum('total_amount') ?? 0;
                
                // Next 30 days (today to 30 days from now)
                $next30Days = DB::table('payment_schedules')
                    ->where('due_date', '>=', Carbon::now()->startOfDay())
                    ->where('due_date', '<=', Carbon::now()->addDays(30)->endOfDay())
                    ->where('status', 'pending')
                    ->sum('total_amount') ?? 0;
                    
                // Next 31-60 days
                $next60Days = DB::table('payment_schedules')
                    ->where('due_date', '>', Carbon::now()->addDays(30)->endOfDay())
                    ->where('due_date', '<=', Carbon::now()->addDays(60)->endOfDay())
                    ->where('status', 'pending')
                    ->sum('total_amount') ?? 0;
                    
                // Next 61-90 days
                $next90Days = DB::table('payment_schedules')
                    ->where('due_date', '>', Carbon::now()->addDays(60)->endOfDay())
                    ->where('due_date', '<=', Carbon::now()->addDays(90)->endOfDay())
                    ->where('status', 'pending')
                    ->sum('total_amount') ?? 0;
                    
                // Beyond 90 days
                $beyond90Days = DB::table('payment_schedules')
                    ->where('due_date', '>', Carbon::now()->addDays(90)->endOfDay())
                    ->where('status', 'pending')
                    ->sum('total_amount') ?? 0;
                    
            } else {
                // Alternative calculation using hire_purchase_agreements table
                
                // Overdue collections (payment date has passed, agreement still active)
                $overdueCollections = DB::table('hire_purchase_agreements')
                    ->where('next_payment_date', '<', Carbon::now()->startOfDay())
                    ->where('status', 'active')
                    ->sum('monthly_payment') ?? 0;
                
                // Next 30 days
                $next30Days = DB::table('hire_purchase_agreements')
                    ->where('next_payment_date', '>=', Carbon::now()->startOfDay())
                    ->where('next_payment_date', '<=', Carbon::now()->addDays(30)->endOfDay())
                    ->where('status', 'active')
                    ->sum('monthly_payment') ?? 0;
                    
                // Next 31-60 days
                $next60Days = DB::table('hire_purchase_agreements')
                    ->where('next_payment_date', '>', Carbon::now()->addDays(30)->endOfDay())
                    ->where('next_payment_date', '<=', Carbon::now()->addDays(60)->endOfDay())
                    ->where('status', 'active')
                    ->sum('monthly_payment') ?? 0;
                    
                // Next 61-90 days
                $next90Days = DB::table('hire_purchase_agreements')
                    ->where('next_payment_date', '>', Carbon::now()->addDays(60)->endOfDay())
                    ->where('next_payment_date', '<=', Carbon::now()->addDays(90)->endOfDay())
                    ->where('status', 'active')
                    ->sum('monthly_payment') ?? 0;
                    
                // Beyond 90 days (estimate based on remaining payments)
                $beyond90Days = DB::table('hire_purchase_agreements')
                    ->where('next_payment_date', '>', Carbon::now()->addDays(90)->endOfDay())
                    ->where('status', 'active')
                    ->sum('monthly_payment') ?? 0;
            }
            
            // Calculate totals
            $totalUpcoming = $next30Days + $next60Days + $next90Days;
            $totalForecast = $totalUpcoming + $beyond90Days;
            $grandTotal = $overdueCollections + $totalForecast;
            
            return [
                // Overdue collections
                'overdue_collections' => (float) $overdueCollections,
                
                // Upcoming collections by period
                'next_30_days' => (float) $next30Days,
                'next_60_days' => (float) $next60Days,  
                'next_90_days' => (float) $next90Days,
                'beyond_90_days' => (float) $beyond90Days,
                
                // Totals
                'total_upcoming_90_days' => (float) $totalUpcoming,
                'total_forecast' => (float) $totalForecast,
                'grand_total' => (float) $grandTotal,
                
                // Additional metadata
                'calculation_method' => $tableExists ? 'payment_schedules' : 'hire_purchase_agreements',
                'calculated_at' => Carbon::now()->toISOString(),
                
                // Breakdown for easy access
                'breakdown' => [
                    'overdue' => (float) $overdueCollections,
                    '0_30_days' => (float) $next30Days,
                    '31_60_days' => (float) $next60Days,
                    '61_90_days' => (float) $next90Days,
                    'over_90_days' => (float) $beyond90Days,
                ],
                
                // Status indicators
                'has_overdue' => $overdueCollections > 0,
                'overdue_percentage' => $grandTotal > 0 ? ($overdueCollections / $grandTotal) * 100 : 0,
            ];
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Collections forecast calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return safe defaults
            return [
                'overdue_collections' => 0.0,
                'next_30_days' => 0.0,
                'next_60_days' => 0.0,
                'next_90_days' => 0.0,
                'beyond_90_days' => 0.0,
                'total_upcoming_90_days' => 0.0,
                'total_forecast' => 0.0,
                'grand_total' => 0.0,
                'calculation_method' => 'error',
                'calculated_at' => Carbon::now()->toISOString(),
                'breakdown' => [
                    'overdue' => 0.0,
                    '0_30_days' => 0.0,
                    '31_60_days' => 0.0,
                    '61_90_days' => 0.0,
                    'over_90_days' => 0.0,
                ],
                'has_overdue' => false,
                'overdue_percentage' => 0.0,
                'error' => true,
                'error_message' => 'Failed to calculate collections forecast'
            ];
        }
    }

    /**
     * Get alerts and notifications for dashboard
     */
    private function getDashboardAlerts()
    {
        $alerts = [];
        
        // Check if required columns exist before querying
        $hpHasOverdueColumns = DB::getSchemaBuilder()->hasColumn('hire_purchase_agreements', 'is_overdue') && 
                               DB::getSchemaBuilder()->hasColumn('hire_purchase_agreements', 'overdue_days');
        $gaHasOverdueColumns = DB::getSchemaBuilder()->hasColumn('gentlemanagreements', 'is_overdue') && 
                               DB::getSchemaBuilder()->hasColumn('gentlemanagreements', 'overdue_days');
        
        // Overdue payments alert
        $overdueCount = 0;
        
        if ($hpHasOverdueColumns) {
            $overdueCount += DB::table('hire_purchase_agreements')
                ->where('is_overdue', 1)
                ->where('overdue_days', '>', 30)
                ->count();
        }
        
        if ($gaHasOverdueColumns) {
            $overdueCount += DB::table('gentlemanagreements')
                ->where('is_overdue', 1)
                ->where('overdue_days', '>', 30)
                ->count();
        }
            
        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "{$overdueCount} payment agreements are overdue by more than 30 days",
                'action_url' => '#'
            ];
        }
        
        // Low inventory alert
        $lowInventory = $this->getCarsInShowroom();
            
        if ($lowInventory < 10) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Low inventory alert: Only {$lowInventory} cars available in showroom",
                'action_url' => '#'
            ];
        }
        
        // Pending facilitations (check if table exists)
        if (DB::getSchemaBuilder()->hasTable('facilitations')) {
            $pendingFacilitations = DB::table('facilitations')
                ->where('status', '1')
                ->count();
                
            if ($pendingFacilitations > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'message' => "{$pendingFacilitations} facilitation requests pending approval",
                    'action_url' => '#'
                ];
            }
        }
        
        return $alerts;
    }
    
    /**
     * Get financial overview
     */
    private function getFinancialOverview()
    {
        // Check if columns exist before querying
        $hpHasOutstanding = DB::getSchemaBuilder()->hasColumn('hire_purchase_agreements', 'outstanding_balance');
        $gaHasOutstanding = DB::getSchemaBuilder()->hasColumn('gentlemanagreements', 'outstanding_balance');
        $hpHasAmountPaid = DB::getSchemaBuilder()->hasColumn('hire_purchase_agreements', 'amount_paid');
        $gaHasAmountPaid = DB::getSchemaBuilder()->hasColumn('gentlemanagreements', 'amount_paid');
        $fleetHasOutstanding = DB::getSchemaBuilder()->hasTable('fleet_acquisitions') && 
                               DB::getSchemaBuilder()->hasColumn('fleet_acquisitions', 'outstanding_balance');
        
        $totalOutstanding = 0;
        $totalPaid = 0;
        $fleetOutstanding = 0;
        $facilitationsPending = 0;
        
        if ($hpHasOutstanding) {
            $totalOutstanding += DB::table('hire_purchase_agreements')
                ->where('status', 'active')
                ->sum('outstanding_balance') ?? 0;
        }
        
        if ($gaHasOutstanding) {
            $totalOutstanding += DB::table('gentlemanagreements')
                ->where('status', 'active')
                ->sum('outstanding_balance') ?? 0;
        }
        
        if ($hpHasAmountPaid) {
            $totalPaid += DB::table('hire_purchase_agreements')
                ->sum('amount_paid') ?? 0;
        }
        
        if ($gaHasAmountPaid) {
            $totalPaid += DB::table('gentlemanagreements')
                ->sum('amount_paid') ?? 0;
        }
        
        if ($fleetHasOutstanding) {
            $fleetOutstanding = DB::table('fleet_acquisitions')
                ->where('status', 'active')
                ->sum('outstanding_balance') ?? 0;
        }
        
        if (DB::getSchemaBuilder()->hasTable('facilitations')) {
            $facilitationsPending = DB::table('facilitations')
                ->where('status', '1') // Pending
                ->sum('amount') ?? 0;
        }
        
        return [
            'total_outstanding' => $totalOutstanding,
            'total_paid' => $totalPaid,
            'fleet_outstanding' => $fleetOutstanding,
            'facilitations_pending' => $facilitationsPending
        ];
    }
    
    /**
     * Get fleet statistics
     */
    private function getFleetStats()
    {
        if (!DB::getSchemaBuilder()->hasTable('fleet_acquisitions')) {
            return [
                'total_vehicles' => 0,
                'active_loans' => 0,
                'completed_loans' => 0,
                'total_fleet_value' => 0
            ];
        }
        
        $hasPurchasePrice = DB::getSchemaBuilder()->hasColumn('fleet_acquisitions', 'purchase_price');
        
        return [
            'total_vehicles' => DB::table('fleet_acquisitions')->count(),
            'active_loans' => DB::table('fleet_acquisitions')
                ->where('status', 'active')
                ->count(),
            'completed_loans' => DB::table('fleet_acquisitions')
                ->where('status', 'completed')
                ->count(),
            'total_fleet_value' => $hasPurchasePrice ? 
                DB::table('fleet_acquisitions')->sum('purchase_price') : 0
        ];
    }
    
    /**
     * Get hire purchase statistics
     */
    private function getHirePurchaseStats()
    {
        $hpHasIsOverdue = DB::getSchemaBuilder()->hasColumn('hire_purchase_agreements', 'is_overdue');
        $gaHasIsOverdue = DB::getSchemaBuilder()->hasColumn('gentlemanagreements', 'is_overdue');
        $hpPaymentsExists = DB::getSchemaBuilder()->hasTable('hire_purchase_payments');
        
        $activeAgreements = DB::table('hire_purchase_agreements')
            ->where('status', 'active')
            ->count() +
            DB::table('gentlemanagreements')
            ->where('status', 'active')
            ->count();
            
        $overduePayments = 0;
        if ($hpHasIsOverdue) {
            $overduePayments += DB::table('hire_purchase_agreements')
                ->where('is_overdue', 1)
                ->count();
        }
        if ($gaHasIsOverdue) {
            $overduePayments += DB::table('gentlemanagreements')
                ->where('is_overdue', 1)
                ->count();
        }
        
        $thisMonthCollections = 0;
        if ($hpPaymentsExists) {
            $thisMonthCollections = DB::table('hire_purchase_payments')
                ->whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount') ?? 0;
        }
        
        return [
            'active_agreements' => $activeAgreements,
            'overdue_payments' => $overduePayments,
            'this_month_collections' => $thisMonthCollections
        ];
    }

    /**
     * API endpoint for dashboard data (optional)
     */
    public function apiData()
    {
        try {
            $data = [
                'total_cars_sold' => $this->getTotalCarsSold(),
                'cars_in_showroom' => $this->getCarsInShowroom(),
                'total_revenue' => $this->getTotalRevenue(),
                'active_leads' => $this->getActiveLeads(),
                'conversion_rate' => $this->getConversionRate(),
                'inspected_cars_count' => $this->getInspectedCarsCount(),
                'sold_through_hire_purchase' => $this->getSoldThroughHirePurchase(),
                'sold_through_cash' => $this->getSoldThroughCash(),
                'sold_through_gentleman' => $this->getSoldThroughGentleman(),
                'total_sold_cars_purchase' => $this->getTotalSoldCarsPurchase(),
                'fleet_acquisition_count' => $this->getFleetAcquisitionCount(),
                'active_users_count' => $this->getActiveUsersCount(),
                'last_updated' => Carbon::now()->format('M d, Y h:i A')
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export dashboard data (optional)
     */
    public function export()
    {
        try {
            $data = [
                'Dashboard Statistics Export',
                'Generated on: ' . Carbon::now()->format('M d, Y h:i A'),
                '',
                'Cars Statistics:',
                'Total Cars Sold: ' . $this->getTotalCarsSold(),
                'Cars in Showroom: ' . $this->getCarsInShowroom(),
                'Inspected Cars: ' . $this->getInspectedCarsCount(),
                '',
                'Sales Breakdown:',
                'Hire Purchase Sales: ' . $this->getSoldThroughHirePurchase(),
                'Cash Sales: ' . $this->getSoldThroughCash(),
                'Gentleman Agreement Sales: ' . $this->getSoldThroughGentleman(),
                'Fleet Acquisitions: ' . $this->getFleetAcquisitionCount(),
                '',
                'Financial Summary:',
                'Total Revenue: KES ' . number_format($this->getTotalRevenue()),
                'Active Leads: ' . $this->getActiveLeads(),
                'Conversion Rate: ' . $this->getConversionRate() . '%',
                'Active Users: ' . $this->getActiveUsersCount(),
            ];
            
            $filename = 'dashboard_export_' . Carbon::now()->format('Y_m_d_H_i_s') . '.txt';
            
            return response()->streamDownload(function () use ($data) {
                echo implode("\n", $data);
            }, $filename, [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting dashboard data: ' . $e->getMessage());
        }
    }
}