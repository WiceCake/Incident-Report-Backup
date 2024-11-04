<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CompletedReportsController extends Controller
{
    //
    public function lists(Request $request)
    {
        $urls = [
            "http://elasticsearch:9200/prefix-post_assessment_logs/_search",
            "http://elasticsearch:9200/prefix-complete_report/_search",
        ];

        $post_assessment_logs = $this->getLogs($urls[0]);
        $complete_report = $this->getLogs($urls[1]);

        // dd();

        // dd($request->all());

        if ($complete_report->count()) {
            $complete_report = collect($complete_report['hits']->hits)->map(function ($data) use ($request, $post_assessment_logs) {

                $post_asssessment_id = $data->_source->data_ids->post_asssessment_id;
                // dd($data->_source->threat_id);
                // dd($request->incident_id);

                $title = collect($post_assessment_logs['hits']->hits)->map(function ($data) use ($post_asssessment_id) {
                    if($data->_id == $post_asssessment_id){
                        return $data->_source->incident_title;
                    }
                    return null;
                })->filter()->values();

                return [
                    "complete_report_id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "incident_title" => $title->first(),
                    "time_issued" => $data->_source->time_issued, //$date,
                ];

                return null;
            })->filter()->values();
        }


        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $complete_report->count() ?? 0,
            'recordsFiltered' => $complete_report->count() ?? 0,
            'data' => $complete_report ?? 0
        ]);
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
}
