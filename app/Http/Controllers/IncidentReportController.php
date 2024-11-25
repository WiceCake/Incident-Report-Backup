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

        $urls = [
            "http://nginx_two/api/v1/threats/all/not_filtered"
        ];

        $getLogs = $this->findReport($id);
        $threat = collect($getLogs['hits']['hits'])->first();
        // dd($threat);
        $threat_id = $threat['_source']['security_event_id'];

        $checkUser = auth()->user()->id;


        $old_data = $this->findData($urls[0], $threat_id);

        $report_data = collect($getLogs['hits']['hits'])->first();

        // dd($report_data);


        $date = Carbon::parse($report_data['_source']['time_issued']); // Parse the date using Carbon
        $date = $date->format('M d Y h:i:s a');

        $report_id = $report_data['_id'];
        $check_data = $this->findPending($report_data['_source']['security_event_id']);
        $checkCount = count($check_data['hits']['hits'] ?? []);
        $status = $report_data['_source']['status'];
        $draftReport = (object)[];

        if ($checkCount) {
            $draftReport->action_type = "Created Draft";
            $draftReport->report_id =  $check_data['hits']['hits'][0]['_id'];
            $draftReport->summary_info = $check_data['hits']['hits'][0]['_source']['summary_info'];
            $draftReport->admin_name = $check_data['hits']['hits'][0]['_source']['admin_name'];
            $draftReport->actions = $check_data['hits']['hits'][0]['_source']['actions'];
            $draftReport->timestamp = $check_data['hits']['hits'][0]['_source']['timestamp'];
            $draftReport->timestamp = Carbon::parse($draftReport->timestamp, 'UTC');
            $draftReport->timestamp = $draftReport->timestamp->format('M d Y h:i:s a');
        }
        // dd($report_data['_id']);
        // dd($this->findPending($report_data['_id']));


        $old_data->admin_name = '';
        $old_data->action_type = 'Detected';
        $old_data->timestamp = Carbon::parse($old_data->timestamp);
        $old_data->timestamp = $old_data->timestamp->format('M d Y h:i:s a');

        $report_data = (object)[
            "action_type" => "Created Incident Report",
            "report_id" => $report_data['_id'],
            "threat_name" => $old_data->threat,
            "threat_type" => $report_data['_source']['threat_type'],
            "admin_name" => $report_data['_source']['admin_name'],
            "admin_username" => $report_data['_source']['admin_username'],
            "timestamp" => $date,
            "status" => $status,
            "attachment_path" => $report_data['_source']['attachment_path'],
            "threat_data" => $old_data,
            "draft_data" => $draftReport,
        ];
        // dd($report_data);

        $report_actions = collect(
            [
                'old_data' => $old_data,
                'report_data' => $report_data,
                'draft_data' => $draftReport,
            ]
        );
        // dd($report_actions);
        // dd($report_actions->get('incident_report'));


        return view('pages.reports.incident-reports.view-report', compact('report_data', 'checkUser', 'report_actions'));
    }

    public function draft(Request $request)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        $count = 0;
        $actions_data = array_map(function ($data) use (&$count) {
            $count += 1;
            return [
                "id" => $count,
                "action_title" => $data,
                "status" => "Not Completed",
                "time_created" => now()->timezone('Asia/Manila')->modify('+8 hours')->toISOString(),
                "time_completed" => null,
                "admin_name" => auth()->user()->name,
            ];
        }, $request->actions);


        $logs = [
            "status" => "Pending Approval",
            "summary_info" => $request->summary_info,
            "actions" => $actions_data,
            "admin_name" => auth()->user()->name,
            "timestamp" => now()->timezone('Asia/Manila')->modify('+8 hours')->toISOString(),
            "security_event_id" => $request->security_event_id
        ];

        // dd($request->incident_id);
        // dd();
        $this->createDraft($request->security_event_id, $request->incident_id);
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
            // dd($request->all());

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
                            'security_event_id' => $request->event_id // Replace with your field name and value
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

    private function findData($url, $id)
    {

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
                            '_id' => $threat_id // Replace with your field name and value
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
                            'security_event_id' => $threat_id // Replace with your field name and value
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
                            'security_event_id' => $incident_id // Replace with your field name and value
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
