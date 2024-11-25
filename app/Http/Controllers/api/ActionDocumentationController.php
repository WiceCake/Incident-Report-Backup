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
            "http://elasticsearch:9200/prefix-draft_reports/_search",
            "http://nginx_two/api/v1/threats/all/not_filtered",
        ];

        $ir_logs = $this->getLogs($urls[0]);
        $event_logs = $this->getApi($urls[2])['data'];
        $draft_logs = $this->getLogs($urls[1]);


        if ($draft_logs->count()) {
            $draft_logs = collect($draft_logs['hits']->hits)->map(function ($data) use ($event_logs) {

                $draft_id = $data->_id;
                $getName = collect(array_filter($event_logs, function ($event) use ($data) {
                    $event_id = $data->_source->security_event_id;
                    return $event_id == $event->threat_id ? $event : null;
                }));


                if ($data->_source->status == 'Pending Approval' || $data->_source->status == 'Completed') {
                    return null;
                }
                return [
                    "draft_id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "incident_title" => $getName->first()->threat ?? $getName,
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

    private function getApi($url)
    {

        $url = Http::get($url);

        return collect(json_decode($url));
    }
}
