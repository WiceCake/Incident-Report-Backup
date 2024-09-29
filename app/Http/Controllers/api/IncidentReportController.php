<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class IncidentReportController extends Controller
{
    //
    public function lists(){
        $urls = [
            "http://uat.muti.group:9200/prefix-incident_reports/_search"
        ];

        $ir_logs = $this->getLogs($urls[0]);

        $ir_logs = collect($ir_logs['hits']->hits)->map(function($data){
            return [
                "incident_id" => $data->_id,
                "threat_id" => $data->_source->threat_id,
                "admin_name" => $data->_source->admin_name,
                "time_issued" => $data->_source->time_issued,
                "status" => $data->_source->status,
                "threat_type" => $data->_source->threat_type,
                "threat_name" => $data->_source->threat_name,
                "attachment_path" => $data->_source->attachment_path
            ];
        });

        return response()->json($ir_logs);
    }

    private function getLogs($url)
    {

        $logs = Http::get($url, [
            'query' => [
                'match_all' => (object)[], // match all documents
            ],
            'size' => 10000,
        ]);

        return collect(json_decode($logs));
    }
}
