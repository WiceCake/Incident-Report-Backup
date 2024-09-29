<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LogsController extends Controller
{
    //

    public function honeypot_logs(Request $request){

        $urls = [
            "http://hp.test:9200/join_logs/_search",
            "http://hp.test:9200/filebeat-7.14.0/_search",
        ];

        $hp_logs = $this->getLogs($urls[0]);
        $modsec_logs = $this->getLogs($urls[1]);

        $getData = collect($hp_logs['hits']->hits)->map(function ($data) {
            $hasIPAddress = $data->_source->user_ip ?? null;
            $hasUserCookie = $data->_source->user_cookies ?? null;

            if ($hasIPAddress && $hasUserCookie) {
                return $data->_source;
            }

            return null;
        })->filter()->unique('user_cookies')->values();


        $hp_logs = collect($hp_logs['hits']->hits)->map(function ($data) use ($getData) {
            $hasUserCookie = $data->_source->user_cookie ?? '';
            $hasEvent = $data->_source->event ?? null;

            $getData = $getData->map(function ($data) use ($hasUserCookie) {
                if ($hasUserCookie == $data->user_cookies) {
                    return $data;
                }
                return null;
            })->filter()->values();

            if ($hasEvent) {

                $getData = $getData->first();
                $event = '';
                if(Str::contains($data->_source->event, 'A user attempts')){
                    $event = 'Someone attempts to logged in to honeypot';
                }else if(Str::contains($data->_source->event, 'A user has logged')){
                    $event = 'Someone logged in to honeypot';
                }else{
                    $event = 'Someone clicks buttons inside honeypot';
                }

                return [
                    "threat_id" => $data->_id,
                    "threat" => $event,
                    "timestamp" => $data->_source->timestamp,
                ];
            }

            return null;
        })->filter()->values();

        // dd($hp_logs);


        $modsec_logs = collect($modsec_logs['hits']->hits)->map(function ($data) {
            $dataString = json_encode($data);

            // Check if the word "anomaly" exists anywhere in the $data object
            if ($dataString && str_contains($dataString, 'Anomaly')) {

                $url = $data->_source->transaction->request;

                return [
                    "threat_id" => $data->_id,
                    "threat" => "Someone attempts to attack to honeypot",
                    "timestamp" => $data->_source->{'@timestamp'},
                ];
            }
            return null;
        })->filter()->values();

        // dd($modsec_logs);

        $merge_logs = $hp_logs->merge($modsec_logs);

        // dd($merge_logs);

        return response()->json([
            "draw" => $request->draw ?? 1,
            "recordsTotal" => $merge_logs->count(),
            "recordsFiltered" => $merge_logs->count(),
            "data" => $merge_logs
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

        return collect(json_decode($logs));
    }
}
