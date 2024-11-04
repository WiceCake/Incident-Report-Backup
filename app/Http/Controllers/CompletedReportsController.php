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
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-draft_reports/_search",
            "http://elasticsearch:9200/prefix-complete_report/_search",
            "http://elasticsearch:9200/prefix-post_assessment_logs/_search",
        ];

        $getLogs = $this->getReport($urls[2], $id);
        $completed_data = collect($getLogs['hits']['hits'])->first();


        $draft_data = $this->getReport($urls[1], $completed_data['_source']['data_ids']['draft_id']);
        $draft_data = collect($draft_data['hits']['hits'])->first();

        $incident_logs = $this->getReport($urls[0], $completed_data['_source']['data_ids']['incident_id']);
        $incident_data = collect($incident_logs['hits']['hits'])->first();

        // $post_assessment_data = $this->getReport($urls[3], $completed_data['_source']['data_ids']['post_asssessment_id']);
        // $post_assessment_data = collect($post_assessment_data['hits']['hits'])->first();
        // dd($completed_data);

        $old_data = $this->findData($incident_data['_source']['threat_id']);


        $completed_report = (object)[
            "report_id" => $draft_data['_id'],
            "status" => 'Completed',
            "admin_name" => $draft_data['_source']['admin_name'],
            "incident_title" => $draft_data['_source']['incident_title'],
            "summary_info" => $draft_data['_source']['summary_info'],
            "plan_info" => $draft_data['_source']['plan_info'],
            "timestamp" => Carbon::parse($completed_data['_source']['time_issued'], 'UTC')->format('M d Y h:i:s a'),
            "threat_name" => $incident_data['_source']['threat_name'],
            "threat_type" => $incident_data['_source']['threat_type'],
            "attachment_path" => $incident_data['_source']['attachment_path'],
            "incident_feedback" => $completed_data['_source']['incident_feedback'],
            "implement_changes" => $completed_data['_source']['implement_changes'],
            "threat_data" => $old_data,
        ];

        // dd($completed_report);

        return view('pages.reports.completed-reports.view-report', compact('completed_report'));
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
}
