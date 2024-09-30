<?php

namespace App\Http\Controllers\api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Elasticsearch\ClientBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

class IncidentReportController extends Controller
{
    //
    public function lists()
    {
        $urls = [
            "http://uat.muti.group:9200/prefix-incident_reports/_search"
        ];

        $ir_logs = $this->getLogs($urls[0]);


        if ($ir_logs->count()) {
            $ir_logs = collect($ir_logs['hits']->hits)->map(function ($data) {

                $report_id = $data->_id;
                $report_data = $this->findCompletedReports($report_id);
                $checkCount = count($report_data['hits']['hits'] ?? []);
                $status = "Processing";


                if ($checkCount) {
                    $status = "Completed";
                }
                $data->_source->status = $status;

                $date = Carbon::parse($data->_source->time_issued); // Parse the date using Carbon
                $date = $date->format('M d Y h:i:s a');

                return [
                    "incident_id" => $data->_id,
                    "threat_id" => $data->_source->threat_id,
                    "admin_name" => $data->_source->admin_name,
                    "admin_username" => $data->_source->admin_username,
                    "time_issued" => $date,
                    "status" => $data->_source->status,
                    "threat_type" => $data->_source->threat_type,
                    "threat_name" => $data->_source->threat_name,
                    "attachment_path" => $data->_source->attachment_path
                ];
            });
        }

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $ir_logs->count() ?? 0,
            'recordsFiltered' => $ir_logs->count() ?? 0,
            'data' => $ir_logs ?? 0
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


    private function findCompletedReports($report_id)
    {
        $client = ClientBuilder::create()->build();

        try {
            $params = [
                'index' => 'prefix-completed_reports', // Replace with your index name
                'body'  => [
                    'query' => [
                        'match' => [
                            'report_id' => $report_id // Replace with your field name and value
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
