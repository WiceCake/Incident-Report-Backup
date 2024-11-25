<?php

namespace App\Http\Controllers;

use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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
            ->setHosts(['elasticsearch:9200'])
            ->build();


        $uploadedFilePaths = [];

        if ($request->hasFile('incident_attachments')) {

            $files = $request->file('incident_attachments');

            foreach ($files as $file) {
                if ($file->isValid()) {
                    $storedPath = $file->store('incident_attachments/' . $request->threat_id);
                    // dd($storedPath);

                    $url = Storage::url($storedPath);
                    $url = Str::replace("http://localhost", "", $url);

                    $uploadedFilePaths[] = $url;
                }
            }
        }

        $logs = [
            "admin_name" => auth()->user()->name,
            "admin_username" => auth()->user()->username,
            "time_issued" => $request->time_issued,
            "threat_type" => $request->threat_type,
            "attachment_path" => $uploadedFilePaths ?? [],
            "status" => "Under Review",
            "security_event_id" => $request->threat_id
        ];

        $params = [
            "index" => "prefix-incident_reports",
            'body' => $logs
        ];

        $client->index($params);

        $actiondata = (object)[
            "admin_name" => auth()->user()->name,
            "action" => "Created report for security event"
        ];

        $this->logEvents($actiondata);

        return redirect()->route('report.security')->with('success', 'Incident Report Successfully Created');
    }

    private function findData($id)
    {
        $url = "http://nginx_two/api/v1/threats/all";

        $response = Http::get($url);

        $response = collect(json_decode($response));


        $getData = collect($response['data'])->filter(function ($data) use ($id) {
            return $data->threat_id == $id;
        })->values();

        return $getData->first();
    }
}
