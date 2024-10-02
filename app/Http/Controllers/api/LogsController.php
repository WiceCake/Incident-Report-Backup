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

                    if ($checkCookie) {
                        if (Str::contains($data->_source->event, 'A user has logged')) {
                            $event = "$getData->user_cookies logged in to honeypot";
                        } else {
                            $event = "$getData->user_cookies clicks a button inside honeypot";
                        }
                    } else {
                        if (Str::contains($data->_source->event, 'A user attempts')) {
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
        }

        // dd($hp_logs);

        if ($modsec_logs->count()) {
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

                    if ($cookie_value) {
                        $events = "$cookie_value attempts to attack to honeypot";
                    } else {
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
        }

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

    public function user_logs()
    {
        $urls = [
            "http://elasticsearch:9200/prefix-incident_reports/_search",
            "http://elasticsearch:9200/prefix-completed_reports/_search",
        ];

        $ir_logs = $this->getLogs($urls[0]);
        $cr_logs = $this->getLogs($urls[1]);

        $all_logs = collect();

        if ($ir_logs->count()) {

            $ir_logs = collect($ir_logs['hits']->hits)->map(function ($data) {

                return [
                    "report_id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "event" => "Created incident report for security event id: " . $data->_source->threat_id,
                    "timestamp" => $data->_source->time_issued,
                ];
            })->values();
        }

        if ($cr_logs->count()) {

            $cr_logs = collect($cr_logs['hits']->hits)->map(function ($data) {

                $date_completed = Carbon::parse($data->_source->timestamp, 'UTC');
                $date_completed = $date_completed->setTimezone('Asia/Manila');
                $date_completed = $date_completed->format('M d Y h:i:s a');

                return [
                    "report_id" => $data->_id,
                    "admin_name" => $data->_source->admin_name,
                    "event" => "Completed report for incident report id: " . $data->_source->report_id,
                    "timestamp" => $data->_source->timestamp,
                ];
            })->values();
        }

        $all_logs = $cr_logs->merge($ir_logs);


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
