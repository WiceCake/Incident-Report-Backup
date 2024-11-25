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
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://nginx_two/api/v1/threats/all/not_filtered",
        ];

        $ir_logs = $this->getLogs($urls[0]);


        if ($ir_logs->count()) {
            $ir_logs = collect($ir_logs['hits']->hits)->map(function ($data) use ($urls) {

                $report_id = $data->_id;
                $event_id = $data->_source->security_event_id;
                // $report_data = $this->findCompletedReports($report_id);
                // $status = "Processing";


                if ($data->_source->status == 'Approved') {
                    return null;
                }
                // $data->_source->status = $status;

                // $date = Carbon::parse($data->_source->time_issued);
                // $date = $date->format('M d Y h:i:s a');

                $event_data = $this->getApi($urls[1], $event_id);
                return [
                    "id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "admin_username" => $data->_source->admin_username,
                    "time_issued" => $data->_source->time_issued, //$date,
                    "status" => $data->_source->status,
                    "threat_type" => $data->_source->threat_type,
                    "threat_name" => $event_data->threat,
                    "attachment_path" => $data->_source->attachment_path
                ];
            })->filter()->values();
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

    private function getApi($url, $id)
    {

        $response = Http::get($url);

        $response = collect(json_decode($response));

        $getData = collect($response['data'])->filter(function ($data) use ($id) {
            return $data->threat_id == $id;
        })->values();

        return $getData->first();

    }


    // private function findCompletedReports($report_id)
    // {
    //     $client = ClientBuilder::create()
    //         ->setHosts(['elasticsearch:9200'])
    //         ->build();

    //     try {
    //         $params = [
    //             'index' => 'prefix-completed_reports', // Replace with your index name
    //             'body'  => [
    //                 'query' => [
    //                     'match' => [
    //                         'report_id' => $report_id // Replace with your field name and value
    //                     ]
    //                 ]
    //             ]
    //         ];

    //         // Perform the search
    //         $response = $client->search($params);

    //         // Check if there are any hits and return their count
    //         return $response ? $response : null;
    //     } catch (\Exception $e) {
    //         // Handle other potential exceptions
    //         // You may want to log this error or return a default value
    //         return null; // Or handle differently based on your application's needs
    //     }
    // }
}
