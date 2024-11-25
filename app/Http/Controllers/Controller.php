<?php

namespace App\Http\Controllers;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Carbon;

abstract class Controller
{
    //

    public function logEvents($data){
        $client = ClientBuilder::create()
        ->setHosts(['elasticsearch:9200'])
        ->build();

        $carbonDate = Carbon::now();

        // Change the timezone to Asia/Manila
        $localDate = $carbonDate->timezone('Asia/Manila');

        $logs = [
            "admin_name" => $data->admin_name,
            "time_issued" => $localDate->modify('+8 hours')->toISOString(),
            "action" => $data->action,
        ];

        $params = [
            "index" => "prefix-user_event_logs",
            'body' => $logs
        ];


        $client->index($params);
    }
}
