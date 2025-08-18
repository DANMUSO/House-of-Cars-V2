<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        return view('Reports.index');
    }
    public function shippinginprocessreport()
    {
        return view('Reports.shippinginprocessreport');
    }
    public function shippedcarsreport()
    {
        return view('Reports.shippedcarsreport');
    }
    public function portclearedcarsreport()
    {
        return view('Reports.portclearedcarsreport');
    }
    public function carsintransitreport()
    {
        return view('Reports.carsintransitreport');
    }
    public function deliveredcarsreport()
    {
        return view('Reports.deliveredcarsreport');
    }
    public function inspectedcarsreport()
    {
        return view('Reports.inspectedcarsreport');
    }
    public function tradeinreport()
    {
        return view('Reports.tradeinreport');
    }
    public function hirepurchasereport()
    {
        return view('Reports.hirepurchasereport');
    }
    public function incashreport()
    {
        return view('Reports.incashreport');
    }
}
