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
        $check_data = $this->findPending($report_id);
        $checkCount = count($check_data['hits']['hits'] ?? []);
        $status = $report_data['_source']['status'];
        $draftReport = (object)[];


        if ($checkCount) {
            $draftReport->summary_info = $check_data['hits']['hits'][0]['_source']['summary_info'];
            $draftReport->plan_info = $check_data['hits']['hits'][0]['_source']['plan_info'];
            $draftReport->timestamp = $check_data['hits']['hits'][0]['_source']['timestamp'];
            $draftReport->timestamp = Carbon::parse($draftReport->timestamp, 'UTC');
            $draftReport->timestamp = $draftReport->timestamp->format('M d Y h:i:s a');
        }
        // dd($report_data['_id']);
        // dd($this->findPending($report_data['_id']));

        $report_data = (object)[
            "report_id" => $report_data['_id'],
            "threat_name" => $report_data['_source']['threat_name'],
            "threat_type" => $report_data['_source']['threat_type'],
            "admin_name" => $report_data['_source']['admin_name'],
            "admin_username" => $report_data['_source']['admin_username'],
            "time_issued" => $date,
            "status" => $status,
            "attachment_path" => $report_data['_source']['attachment_path'],
            "threat_data" => $old_data,
            "draft_data" => $draftReport,
        ];
        // dd($report_data);


        return view('pages.reports.incident-reports.view-report', compact('report_data'));
    }

    public function draft(Request $request)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        $carbonDate = Carbon::now();

        // Change the timezone to Asia/Manila
        $localDate = $carbonDate->timezone('Asia/Manila');

        // dd($localDate);

        $logs = [
            "admin_name" => $request->admin_name,
            "status" => "Pending Approval",
            "incident_title" => $request->incident_title,
            "summary_info" => $request->summary_info,
            "plan_info" => $request->plan_info,
            "timestamp" => $localDate->modify('+8 hours')->toISOString(),
            "data_ids" => [
                "event_id" => $request->event_id,
                "incident_id" => $request->incident_id,
            ]
        ];

        // dd($request->incident_id);
        // dd();
        $this->createDraft($request->event_id, $request->incident_id);
        // dd('donw');

        $params = [
            "index" => "prefix-draft_reports",
            'body' => $logs
        ];


        $client->index($params);

        $actiondata = (object)[
            "admin_name" => auth()->user()->name,
            "action" => "Created draft for incident report"
        ];

        $this->logEvents($actiondata);

        return redirect()->route('report.incident')->with('success', 'Draft Report ' . $request->report_id . ' Successfully Created');
    }

    public function approve(Request $request)
    {

        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        try {

            $updateParams = [
                'index' => 'prefix-incident_reports', // Replace with your index name
                'id' => $request->incident_id,
                'body' => [
                    'doc' => [
                        'status' => 'Approved' // Replace with your field and value
                    ]
                ]
            ];


            $params = [
                'index' => 'prefix-draft_reports', // Replace with your index name
                'body'  => [
                    'query' => [
                        'match' => [
                            'data_ids.incident_id' => $request->incident_id // Replace with your field name and value
                        ]
                    ]
                ]
            ];

            // Perform the search
            $draft = $client->search($params);
            $draftId = $draft['hits']['hits'][0]['_id'];

            $updateParams2 = [
                'index' => 'prefix-draft_reports', // Replace with your index name
                'id' => $draftId,
                'body'  => [
                    'doc' => [
                        'status' => 'In Progress' // Replace with your field name and value
                    ]
                ]
            ];

            $client->update($updateParams);
            $client->update($updateParams2);

            $actiondata = (object)[
                "admin_name" => auth()->user()->name,
                "action" => "Approved to take action of incident report"
            ];

            $this->logEvents($actiondata);

            return redirect()->route('report.incident')->with('success', 'Report ' . $request->report_id . ' Successfully Approved');
        } catch (\Exception $e) {
            // Handle other potential exceptions
            // You may want to log this error or return a default value
            return 'Error: ' . $e->getMessage();
        }
    }

    private function findData($id)
    {
        $url = "http://nginx_two/api/v1/threats/all/not_filtered";

        $response = Http::get($url);

        $response = collect(json_decode($response));

        // dd($response);

        $getData = collect($response['data'])->filter(function ($data) use ($id) {
            return $data->threat_id == $id;
        })->values();

        return $getData->first();
    }

    private function findReport($threat_id)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

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

    private function createDraft($threat_id, $incident_id)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

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
            // dd($response);
            // dd(count($response['hits']['hits']));

            if (count($response['hits']['hits']) > 0) {
                $updateParams = [
                    'index' => 'prefix-incident_reports', // Replace with your index name
                    'id' => $incident_id,
                    'body' => [
                        'doc' => [
                            'status' => 'Pending Approval' // Replace with your field and value
                        ]
                    ]
                ];

                $client->update($updateParams);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            // Handle other potential exceptions
            // You may want to log this error or return a default value
            return 'Error: ' . $e->getMessage();
        }
    }

    private function findPending($incident_id)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        // dd($incident_id);

        try {
            $params = [
                'index' => 'prefix-draft_reports', // Replace with your index name
                'body'  => [
                    'query' => [
                        'match' => [
                            'data_ids.incident_id' => $incident_id // Replace with your field name and value
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
