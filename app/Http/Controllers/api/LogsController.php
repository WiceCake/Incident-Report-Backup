<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class LogsController extends Controller
{
    //

    public function honeypot_logs(Request $request)
    {

        $urls = [
            "http://elasticsearch:9200/join_logs/_search",
            "http://elasticsearch:9200/filebeat-7.14.0/_search",
        ];

        $hp_logs = $this->getLogs($urls[0]);
        $modsec_logs = $this->getLogs($urls[1]);

        if ($hp_logs->count()) {

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
                $hasEvent =  $data->_source->attack ?? $data->_source->event ?? null;

                $getData = $getData->map(function ($data) use ($hasUserCookie) {
                    if ($hasUserCookie == $data->user_cookies) {
                        return $data;
                    }
                    return null;
                })->filter()->values();

                if ($hasEvent) {

                    $getData = $getData->first();
                    $event = '';

                    $checkCookie = $getData->user_cookie ?? $data->_source->user_cookie ?? $data->_source->cookies ?? null;

                    $checkAttack = $data->_source->attack ?? null;

                    if ($checkCookie) {
                        if (Str::contains($data->_source->event ?? '', 'A user has logged')) {
                            $event = $getData->user_cookie ?? $data->_source->cookies;
                            $event = $event . " logged in to honeypot";
                        }else if(Str::contains($data->_source->event ?? '', 'Click Button')){
                            $event = $getData->user_cookie ?? $data->_source->user_cookie ?? $data->_source->cookies;
                            $event = $event . ' clicks button in honeypot';
                        }else if($checkAttack){
                            $event = $data->_source->cookies . ' attempts to attack honeypot';
                        }else {
                            $event = "$getData->user_cookie clicks a button inside honeypot";
                        }
                    } else {
                        if (Str::contains($data->_source->event ?? '', 'A user attempts')) {
                            $event = 'Someone attempts to logged in to honeypot';
                        }else if($checkAttack){
                            $event = 'Someone attempts to attack honeypot';
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
        }

        // dd($hp_logs);

        // if ($modsec_logs->count()) {
        //     $modsec_logs = collect($modsec_logs['hits']->hits)->map(function ($data) {
        //         $dataString = json_encode($data);

        //         // Check if the word "anomaly" exists anywhere in the $data object
        //         if ($dataString && str_contains($dataString, 'Anomaly')) {

        //             $cookie_value = "";
        //             $check_header = $data->_source->transaction->request->headers->Cookie ?? null;



        //             if (Str::contains($check_header, 'hp_cookie') && $check_header) {
        //                 $check_header = explode("; ", $check_header);
        //                 $cookie = '';
        //                 foreach ($check_header as $header) {
        //                     if (strpos($header, 'hp_cookie') !== false) {
        //                         $cookie = explode('=', $header)[1]; // Get the value after 'hp_cookie='
        //                         break; // Stop looping once the value is found
        //                     }
        //                 }
        //                 $cookie_value = $cookie;
        //             } else {
        //                 $cookie_value = null;
        //             }

        //             $events = '';

        //             if ($cookie_value) {
        //                 $events = "$cookie_value attempts to attack to honeypot";
        //             } else {
        //                 $events = "Someone attempts to attack to honeypot";
        //             }

        //             return [
        //                 "threat_id" => $data->_id,
        //                 // "check_header" => $check_header,
        //                 "threat" => $events,
        //                 "timestamp" => $data->_source->{'@timestamp'},
        //             ];
        //         }
        //         return null;
        //     })->filter()->values();
        // }

        // dd($modsec_logs);

        // $merge_logs = $hp_logs->merge($modsec_logs);

        // dd($merge_logs);

        return response()->json([
            "draw" => $request->draw ?? 1,
            "recordsTotal" => $hp_logs->count(),
            "recordsFiltered" => $hp_logs->count(),
            "data" => $hp_logs
        ]);
    }

    public function user_logs()
    {
        $urls = [
            "http://elasticsearch:9200/prefix-user_event_logs/_search",
        ];

        $all_logs = $this->getLogs($urls[0]);
        // $cr_logs = $this->getLogs($urls[1]);

        if ($all_logs->count()) {

            $all_logs = collect($all_logs['hits']->hits)->map(function ($data) {

                return [
                    "report_id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "event" => $data->_source->action . " | Event id: " . $data->_id,
                    "timestamp" => $data->_source->time_issued,
                ];
            })->values();
        }


        return response()->json([
            "draw" => $request->draw ?? 1,
            "recordsTotal" => $all_logs->count() ?? 0,
            "recordsFiltered" => $all_logs->count() ?? 0,
            "data" => $all_logs ?? []
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

        if ($logs->successful()) {

            $response = json_decode($logs);

            return collect($response);
        } else {
            // Handle unsuccessful response (e.g., error code from Elasticsearch)
            return collect([]);
        }
    }
}
