<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Http;

class PostIncidentController extends Controller
{
    //

    public function index()
    {
        return view('pages.reports.post-incident');
    }

    public function show($id)
    {

        $urls = [
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-draft_reports/_search",
            "http://elasticsearch:9200/prefix-post_assessment_logs/_search",
        ];

        $getLogs = $this->getReport($urls[2], $id);
        $post_assessment_data = collect($getLogs['hits']['hits'])->first();

        $incident_logs = $this->getReport($urls[0], $post_assessment_data['_source']['data_ids']['incident_id']);
        $incident_data = collect($incident_logs['hits']['hits'])->first();


        $post_assessment_report = (object)[
            "report_id" => $post_assessment_data['_id'],
            "draft_id" => $post_assessment_data['_source']['data_ids']['draft_id'],
            "status" => $post_assessment_data['_source']['status'],
            "admin_name" => $post_assessment_data['_source']['admin_name'],
            "incident_title" => $post_assessment_data['_source']['incident_title'],
            "summary_info" => $post_assessment_data['_source']['summary_info'],
            "plan_info" => $post_assessment_data['_source']['plan_info'],
            "timestamp" => Carbon::parse($post_assessment_data['_source']['time_issued'], 'UTC')->format('M d Y h:i:s a'),
        ];


        return view('pages.reports.post-incident.view-report', compact('post_assessment_report'));
    }

    public function closeReport(Request $request)
    {
        // dd($request->all());
        $urls = [
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-draft_reports/_search",
            "http://elasticsearch:9200/prefix-post_assessment_logs/_search",
        ];

        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        $carbonDate = Carbon::now();

        // Change the timezone to Asia/Manila
        $localDate = $carbonDate->timezone('Asia/Manila');

        $pa_logs = $this->getReport($urls[2], $request->post_asssessment_id);
        $pa_logs = $pa_logs['hits']['hits'][0];

        $logs = [
            "admin_name" => $request->admin_name,
            "time_issued" => $localDate->modify('+8 hours')->toISOString(),
            "incident_feedback" => $request->incident_feedback,
            "implement_changes" => $request->implement_changes,
            "data_ids" => [
                'post_asssessment_id' => $request->post_asssessment_id,
                'draft_id' => $pa_logs['_source']['data_ids']['draft_id'],
                'event_id' => $pa_logs['_source']['data_ids']['event_id'],
                'incident_id' => $pa_logs['_source']['data_ids']['incident_id'],
            ]
        ];

        $params = [
            "index" => "prefix-complete_report",
            'body' => $logs
        ];

        $client->index($params);

        // try {

        $updateParams = [
            'index' => 'prefix-post_assessment_logs', // Replace with your index name
            'id' => $request->post_asssessment_id,
            'body' => [
                'doc' => [
                    'status' => 'Completed' // Replace with your field and value
                ]
            ]
        ];


        $client->update($updateParams);

        $actiondata = (object)[
            "admin_name" => auth()->user()->name,
            "action" => "Completed the report"
        ];

        $this->logEvents($actiondata);

        return redirect()->route('report.post_incident')->with('success', 'Report ' . $request->post_asssessment_id . ' Successfully Mitigated');
        // } catch (\Exception $e) {
        //     // Handle other potential exceptions
        //     // You may want to log this error or return a default value
        //     return 'Error: ' . $e->getMessage();
        // }
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
