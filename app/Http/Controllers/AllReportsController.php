<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Http;

class AllReportsController extends Controller
{
    //

    public function index()
    {
        return view('pages.reports.all-reports');
    }

}
