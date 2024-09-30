<?php

namespace App\Http\Controllers;

use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

        return view('pages.reports.security-events.view-report', compact('data'));
    }

    public function create(Request $request)
    {

        $request->validate([
            'threat_type' => 'required'
        ]);

        $client = ClientBuilder::create()
            ->setHosts(['uat.muti.group:9200'])
            ->build();

        $uploadedFilePaths = [];

        if ($request->hasFile('incident_attachments')) {

            $files = $request->file('incident_attachments');

            foreach ($files as $file) {
                if ($file->isValid()) {
                    $storedPath = $file->store('public/incident_attachments/' . $request->threat_id);

                    $url = Storage::url($storedPath);

                    $uploadedFilePaths[] = $url;
                }
            }
        }

        $logs = [
            "threat_id" => $request->threat_id,
            "admin_name" => $request->admin_name,
            "admin_username" => $request->username,
            "time_issued" => $request->time_issued,
            "threat_type" => $request->threat_type,
            "threat_name" => $request->threat_name,
            "attachment_path" => $uploadedFilePaths ?? [],
            "status" => "Processing"
        ];

        $params = [
            "index" => "prefix-incident_reports",
            'body' => $logs
        ];

        $client->index($params);

        return redirect()->route('report.security')->with('success', 'Incident Report Successfully Created');
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
