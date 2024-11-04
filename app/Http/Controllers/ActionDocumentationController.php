<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Http;

class ActionDocumentationController extends Controller
{
    //
    public function index()
    {
        return view('pages.reports.action-documentation');
    }

    public function show($id)
    {

        $urls = [
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-draft_reports/_search",
        ];

        $getLogs = $this->getReport($urls[1], $id);
        $draft_data = collect($getLogs['hits']['hits'])->first();

        $incident_logs = $this->getReport($urls[0], $draft_data['_source']['data_ids']['incident_id']);
        $incident_data = collect($incident_logs['hits']['hits'])->first();


        $draft_report = (object)[
            "report_id" => $draft_data['_id'],
            "status" => $draft_data['_source']['status'],
            "admin_name" => $draft_data['_source']['admin_name'],
            "incident_title" => $draft_data['_source']['incident_title'],
            "summary_info" => $draft_data['_source']['summary_info'],
            "plan_info" => $draft_data['_source']['plan_info'],
            "timestamp" => Carbon::parse($draft_data['_source']['timestamp'], 'UTC')->format('M d Y h:i:s a'),
        ];

        return view('pages.reports.action-documentation.view-report', compact('draft_report'));
    }

    public function logProgress(Request $request)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        $carbonDate = Carbon::now();

        // Change the timezone to Asia/Manila
        $localDate = $carbonDate->timezone('Asia/Manila');

        $logs = [
            "threat_id" => $request->draft_id,
            "admin_name" => $request->admin_name,
            "time_issued" => $localDate->modify('+8 hours')->toISOString(),
            "log_description" => $request->log_description,
            "method_used" => $request->method_used,
        ];

        $params = [
            "index" => "prefix-action_progress_logs",
            'body' => $logs
        ];

        $client->index($params);

        $actiondata = (object)[
            "admin_name" => auth()->user()->name,
            "action" => "Logs progress about the report"
        ];

        $this->logEvents($actiondata);

        return redirect()->back()->with('success', 'Logging progress ' . $request->draft_id . ' Successfully Created');
    }

    public function mitigateAttack(Request $request)
    {
        // dd($request->all());

        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        try {

            $updateParams = [
                'index' => 'prefix-draft_reports', // Replace with your index name
                'id' => $request->report_id,
                'body' => [
                    'doc' => [
                        'status' => 'Mitigated' // Replace with your field and value
                    ]
                ]
            ];


            $client->update($updateParams);

            $actiondata = (object)[
                "admin_name" => auth()->user()->name,
                "action" => "Setted the report that the attack is already mitigated"
            ];

            $this->logEvents($actiondata);

            return redirect()->route('report.action_documentation')->with('success', 'Report ' . $request->report_id . ' Successfully Mitigated');
        } catch (\Exception $e) {
            // Handle other potential exceptions
            // You may want to log this error or return a default value
            return 'Error: ' . $e->getMessage();
        }
    }

    public function postAssessment(Request $request){
        $urls = [
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-draft_reports/_search",
        ];

        $draft_data = $this->getReport($urls[1], $request->report_id);
        $draft_data = $draft_data['hits']['hits'][0];

        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        $carbonDate = Carbon::now();

        // Change the timezone to Asia/Manila
        $localDate = $carbonDate->timezone('Asia/Manila');

        $updateParams = [
            'index' => 'prefix-draft_reports', // Replace with your index name
            'id' => $request->report_id,
            'body' => [
                'doc' => [
                    'status' => 'Pending Audit' // Replace with your field and value
                ]
            ]
        ];


        $client->update($updateParams);

        $logs = [
            "admin_name" => $request->admin_name,
            "time_issued" => $localDate->modify('+8 hours')->toISOString(),
            "incident_title" => $draft_data['_source']['incident_title'],
            "summary_info" => $draft_data['_source']['summary_info'],
            "plan_info" => $draft_data['_source']['plan_info'],
            "status" => "Pending Audit",
            "data_ids" => [
                "draft_id" => $draft_data['_id'],
                "event_id" => $draft_data['_source']['data_ids']['event_id'],
                "incident_id" => $draft_data['_source']['data_ids']['incident_id'],
            ]
        ];

        $params = [
            "index" => "prefix-post_assessment_logs",
            'body' => $logs
        ];

        $client->index($params);

        $actiondata = (object)[
            "admin_name" => auth()->user()->name,
            "action" => "Sended the report to the post incident review"
        ];

        $this->logEvents($actiondata);

        return redirect()->route('report.action_documentation')->with('success', 'Post Assessment for ' . $request->report_id . ' Successfully Created');
    }

    private function getLogs($url)
    {
        $logs = Http::get($url, [
            'query' => [
                'match_all' => (object)[], // match all documents
            ],
            'size' => 10000,
        ]);

        // Check if the response is successful
        if ($logs->successful()) {

            $response = json_decode($logs);

            return collect($response);
        } else {
            // Handle unsuccessful response (e.g., error code from Elasticsearch)
            return collect([]);
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
}
