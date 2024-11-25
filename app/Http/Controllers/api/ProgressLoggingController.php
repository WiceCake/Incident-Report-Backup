<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProgressLoggingController extends Controller
{
    //
    public function lists(Request $request)
    {
        $urls = [
            "http://elasticsearch:9200/prefix-action_progress_logs/_search",
        ];

        $progress_logs = $this->getLogs($urls[0]);

        // dd($request->all());

        if ($progress_logs->count()) {
            $progress_logs = collect($progress_logs['hits']->hits)->map(function ($data) use ($request) {

                $draft_id = $data->_id;
                // dd($data->_source->threat_id);
                // dd($request->incident_id);

                if ($data->_source->threat_id == $request->incident_id) {
                    return [
                        "threat_id" => $data->_source->threat_id,
                        "progress_id" => $data->_id,
                        "admin_name" => $data->_source->admin_name,
                        "log_description" => $data->_source->log_description,
                        "time_issued" => $data->_source->time_issued, //$date,
                        "method_used" => $data->_source->method_used,
                    ];
                }

                return null;
            })->filter()->values();
        }


        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $progress_logs->count() ?? 0,
            'recordsFiltered' => $progress_logs->count() ?? 0,
            'data' => $progress_logs ?? 0
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
