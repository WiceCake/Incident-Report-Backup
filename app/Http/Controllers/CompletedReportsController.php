<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Http;

class CompletedReportsController extends Controller
{
    //
    public function index()
    {
        return view('pages.reports.completed-reports');
    }

    public function show($id)
    {

        $urls = [
            "http://nginx_two/api/v1/threats/all/not_filtered",
        ];

        $getDraft = $this->findReport($id);
        $threat = collect($getDraft['hits']['hits'])->first();
        // dd($getDraft);

        // dd($threat);
        $threat_id = $threat['_source']['security_event_id'];
        $getLogs = $this->findIncidentReport($threat_id);

        $checkUser = auth()->user()->id;


        $old_data = $this->findData($urls[0], $threat_id);

        $report_data = collect($getLogs['hits']['hits'])->first();

        $date = Carbon::parse($report_data['_source']['time_issued']); // Parse the date using Carbon
        $date = $date->format('M d Y h:i:s a');

        $report_id = $report_data['_id'];
        $check_data = $this->findPending($report_data['_source']['security_event_id']);
        $checkCount = count($check_data['hits']['hits'] ?? []);
        $status = $report_data['_source']['status'];
        $draftReport = (object)[];

        $actions = $check_data['hits']['hits'][0]['_source']['actions'];
        $actions = collect($actions)->map(function ($action){
            return (object)$action;
        });

        $checkActions = $this->findCompletedAction($id)['hits']['hits'] ?? null;

        // dd($id);
        if ($checkActions) {
            $actions = $check_data['hits']['hits'][0]['_source']['actions'];
            $actions = collect($actions)->map(function ($action) use ($checkActions) {
                $checkCompleted = collect($checkActions)->filter(function ($data) use ($action) {
                    return $data['_source']['action_id'] == $action['id'] ? true : null;
                })->first();

                $action['action_type'] = 'Note Completed the Task';
                if ($checkCompleted) {
                    $action['time_completed'] = $checkCompleted['_source']['timestamp'];
                    $action['time_completed'] = Carbon::parse($action['time_completed']); // Parse the date using Carbon
                    $action['time_completed'] = $action['time_completed']->format('M d Y h:i:s a');
                    $action['status'] = 'Completed';
                    $action['action_type'] = 'Completed the Task';
                }

                return (object)$action;
            });
        }

        if ($checkCount) {
            $draftReport->action_type = "Created Draft";
            $draftReport->report_id =  $check_data['hits']['hits'][0]['_id'];
            $draftReport->summary_info = $check_data['hits']['hits'][0]['_source']['summary_info'];
            $draftReport->admin_name = $check_data['hits']['hits'][0]['_source']['admin_name'];
            $draftReport->actions = $actions;
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
        // dd($report_data)
// dd($actions);

        $report_actions = collect(
            array_merge([
                'old_data' => $old_data,
                'report_data' => $report_data,
                'draft_data' => $draftReport,
            ], $actions->all() ?? [])
        );


        $draft_id = $id;


        return view('pages.reports.completed-reports.view-report', compact('report_data', 'checkUser', 'report_actions', 'draft_id', 'actions'));
    }


    private function findIncidentReport($threat_id)
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


            // Check if there are any hits and return their count
            return $response ? $response : null;
        } catch (\Exception $e) {
            // Handle other potential exceptions
            // You may want to log this error or return a default value
            return null; // Or handle differently based on your application's needs
        }
    }

    private function findCompletedAction($threat_id)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        try {
            $params = [
                'index' => 'prefix-completed_actions', // Replace with your index name
                'body'  => [
                    'query' => [
                        'match' => [
                            'draft_id.keyword' => $threat_id // Replace with your field name and value
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

    private function getReport($url, $id)
    {
        $logs = Http::post($url, [
            'query' => [
                'match' => (object)[
                    '_id' => $id
                ]
            ]
        ]);

        // Check if the response is successful
        if ($logs->successful()) {

            return collect($logs->json());
        } else {
            // Handle unsuccessful response (e.g., error code from Elasticsearch)
            return collect([]);
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
                'index' => 'prefix-draft_reports', // Replace with your index name
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
}
