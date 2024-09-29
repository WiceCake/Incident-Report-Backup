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
            "http://uat.muti.group:9200/join_logs/_search",
            "http://uat.muti.group:9200/filebeat-7.14.0/_search",
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

                $checkCookie = $getData->user_cookies ?? null;

                if($checkCookie){
                    if(Str::contains($data->_source->event, 'A user has logged')){
                        $event = "$getData->user_cookies logged in to honeypot";
                    }else{
                        $event = "$getData->user_cookies clicks a button inside honeypot";
                    }
                }else{
                    if(Str::contains($data->_source->event, 'A user attempts')){
                        $event = 'Someone attempts to logged in to honeypot';
                    }
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

                $cookie_value = "";
                $check_header = $data->_source->transaction->request->headers->Cookie ?? null;


                if (Str::contains($check_header, 'hp_cookie') && $check_header) {
                    $check_header = explode("; ", $check_header);
                    $cookie = explode("=", $check_header[0]);
                    $cookie = $cookie[1];
                    $cookie_value = $cookie;
                } else {
                    $cookie_value = null;
                }

                $events = '';

                if($cookie_value){
                    $events = "$cookie_value attempts to attack to honeypot";
                }else{
                    $events = "Someone attempts to attack to honeypot";
                }

                return [
                    "threat_id" => $data->_id,
                    "threat" => $events,
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
