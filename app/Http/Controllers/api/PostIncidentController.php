<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostIncidentController extends Controller
{
    //
    public function lists()
    {
        $urls = [
            "http://elasticsearch:9200/prefix-draft_reports/_search",
            "http://elasticsearch:9200/prefix-post_assessment_logs/_search",
        ];

        $draft_logs = $this->getLogs($urls[0]);
        $post_assessment_logs = $this->getLogs($urls[1]);

        // dd($post_assessment_logs);


        if ($post_assessment_logs->count()) {
            $post_assessment_logs = collect($post_assessment_logs['hits']->hits)->map(function ($data) {

                $draft_id = $data->_id;
                // dd($data);

                if($data->_source->status == 'Completed'){
                    return null;
                }

                return [
                    "post_incident_id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "incident_title" => $data->_source->incident_title,
                    "time_issued" => $data->_source->time_issued, //$date,
                    "status" => $data->_source->status,
                ];
            })->filter()->values();
        }



        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $post_assessment_logs->count() ?? 0,
            'recordsFiltered' => $post_assessment_logs->count() ?? 0,
            'data' => $post_assessment_logs ?? 0
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
