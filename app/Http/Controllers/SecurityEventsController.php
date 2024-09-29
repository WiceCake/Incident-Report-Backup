<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SecurityEventsController extends Controller
{
    public function index()
    {
        return view('pages.reports.security-events');
    }

    //
    public function show($id)
    {
        $data = $this->findData($id);

        return view('pages.reports.incident-report.view-report', compact('data'));
    }

    private function findData($id)
    {
        $url = "http://incident-report.test:81/api/v1/threats/all";

        $response = Http::get($url);

        $response = collect(json_decode($response));


        $getData = collect($response['data'])->filter(function ($data) use ($id) {
            return $data->threat_id == $id;
        })->values();

        return $getData->first();
    }
}
