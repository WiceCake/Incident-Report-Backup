<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Http;

class IncidentReportController extends Controller
{
    //
    public function index()
    {
        return view('pages.reports.incident-reports');
    }

    public function show($id)
    {

        $getLogs = $this->findReport($id);
        $threat_id = collect($getLogs['hits']['hits'])->first();
        $threat_id = $threat_id['_source']['threat_id'];

        $old_data = $this->findData($id);

        $report_data = collect($getLogs['hits']['hits'])->first();


        $date = Carbon::parse($report_data['_source']['time_issued']); // Parse the date using Carbon
        $date = $date->format('M d Y h:i:s a');

        $report_id = $report_data['_id'];
        $check_data = $this->findCompletedReports($report_id);
        $checkCount = count($check_data['hits']['hits'] ?? []);
        $status = $report_data['_source']['status'];
        $date_completed = null;


        if($checkCount){
            $status = "Completed";
            $date_completed = $check_data['hits']['hits'][0]['_source']['timestamp'];
            $date_completed = Carbon::parse($date_completed, 'UTC');
            $date_completed = $date_completed->setTimezone('Asia/Manila');
            $date_completed = $date_completed->format('M d Y h:i:s a');
        }

        $report_data = (object)[
            "report_id" => $report_data['_id'],
            "threat_name" => $report_data['_source']['threat_name'],
            "threat_type" => $report_data['_source']['threat_type'],
            "admin_name" => $report_data['_source']['admin_name'],
            "admin_username" => $report_data['_source']['admin_username'],
            "date_completed" => $date_completed,
            "time_issued" => $date,
            "status" => $status,
            "attachment_path" => $report_data['_source']['attachment_path'],
            "threat_data" => $old_data,
        ];

        return view('pages.reports.incident-reports.view-report', compact('report_data'));
    }

    public function complete(Request $request)
    {
        $client = ClientBuilder::create()
            ->setHosts(['uat.muti.group:9200'])
            ->build();

        $logs = [
            "report_id" => $request->report_id,
            "admin_name" => $request->admin_name,
            "status" => "Completed",
            "timestamp" => now()
        ];

        $params = [
            "index" => "prefix-completed_reports",
            'body' => $logs
        ];

        $client->index($params);

        return redirect()->route('report.incident')->with('success', 'Report ' . $request->report_id . ' Successfully Created');
    }

    private function findData($id)
    {
        $url = "http://incident-report.test:81/api/v1/threats/all/not_filtered";

        $response = Http::get($url);

        $response = collect(json_decode($response));

        $getData = collect($response['data'])->filter(function ($data) use ($id) {
            return $data->threat_id == $id;
        })->values();

        return $getData->first();
    }

    private function findReport($threat_id)
    {
        $client = ClientBuilder::create()->build();

        try {
            $params = [
                'index' => 'prefix-incident_reports', // Replace with your index name
                'body'  => [
                    'query' => [
                        'match' => [
                            'threat_id' => $threat_id // Replace with your field name and value
                        ]
                    ]
                ]
            ];

            // Perform the search
            $response = $client->search($params);

            // Check if there are any hits and return their count
            return $response ? $response : null;
        } catch (\Exception $e) {
            // Handle other potential exceptions
            // You may want to log this error or return a default value
            return null; // Or handle differently based on your application's needs
        }
    }

    private function findCompletedReports($report_id)
    {
        $client = ClientBuilder::create()->build();

        try {
            $params = [
                'index' => 'prefix-completed_reports', // Replace with your index name
                'body'  => [
                    'query' => [
                        'match' => [
                            'report_id' => $report_id // Replace with your field name and value
                        ]
                    ]
                ]
            ];

            // Perform the search
            $response = $client->search($params);

            // Check if there are any hits and return their count
            return $response ? $response : null;
        } catch (\Exception $e) {
            // Handle other potential exceptions
            // You may want to log this error or return a default value
            return null; // Or handle differently based on your application's needs
        }
    }
}
