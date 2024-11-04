<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ActionDocumentationController extends Controller
{
    public function lists()
    {
        $urls = [
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-draft_reports/_search"
        ];

        $ir_logs = $this->getLogs($urls[0]);
        $draft_logs = $this->getLogs($urls[1]);


        if ($draft_logs->count()) {
            $draft_logs = collect($draft_logs['hits']->hits)->map(function ($data) {

                $draft_id = $data->_id;

                if($data->_source->status == 'Pending Approval' || $data->_source->status == 'Pending Audit'){
                    return null;
                }

                return [
                    "draft_id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "incident_title" => $data->_source->incident_title,
                    "time_issued" => $data->_source->timestamp, //$date,
                    "status" => $data->_source->status,
                ];
            })->filter()->values();
        }



        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $draft_logs->count() ?? 0,
            'recordsFiltered' => $draft_logs->count() ?? 0,
            'data' => $draft_logs ?? 0
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
