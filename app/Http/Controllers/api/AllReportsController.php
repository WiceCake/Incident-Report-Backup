<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AllReportsController extends Controller
{
    //
    public function allReports(){
        $urls = [
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-draft_reports/_search",
            "http://nginx_two/api/v1/threats/all/not_filtered",
        ];

        $events = $this->getApi($urls[2])['data'];

        $ir_logs = collect($this->getLogs($urls[0])['hits']->hits)->map(function($data) use ($events){
            $event = collect($events)->firstWhere('threat_id', $data->_source->security_event_id);

            return [
                'id' => $data->_id,
                'report_type' => 'Incident Report',
                'admin_name' => $data->_source->admin_name,
                'threat_name' => $event->threat,
                'timestamp' => $data->_source->time_issued,
                'status' => $data->_source->status
            ];
        });

        $dr_logs = collect($this->getLogs($urls[1])['hits']->hits)->map(function($data) use ($events){
            $event = collect($events)->firstWhere('threat_id', $data->_source->security_event_id);

            $report_type = $data->_source->status == 'Completed' ? 'Completed Reports' : 'Draft Reports';

            return [
                'id' => $data->_id,
                'report_type' => $report_type,
                'admin_name' => $data->_source->admin_name,
                'threat_name' => $event->threat,
                'timestamp' => $data->_source->timestamp,
                'status' => $data->_source->status
            ];
        });

        $mergedLogs = $ir_logs->concat($dr_logs);

        // Sorted Logs
        $sortedLogs = $mergedLogs->sortBy('timestamp');

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $mergedLogs->count() ?? 0,
            'recordsFiltered' => $mergedLogs->count() ?? 0,
            'data' => $mergedLogs ?? []
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
