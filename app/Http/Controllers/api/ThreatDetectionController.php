<?php

namespace App\Http\Controllers\api;

use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class ThreatDetectionController extends Controller
{
    //
    public function logged_in()
    {
        $urls = [
            "http://localhost:9200/filebeat-7.14.0/_search"
        ];

        $logs = collect($this->getLogs($urls[0]));

        $logs = collect($logs['hits']->hits)->map(function ($data) {
            $checker = $data->_source->transaction->messages[0]->message;
            if (!Str::contains($checker, 'healthcheck')) {
                return $data;
            }
            return null;
        })->filter()->values();


        return response()->json([
            "total" => $logs->count()
        ]);
    }

    public function logged_in_attempt()
    {
        $urls = [
            "http://localhost:9200/join_logs/_search",
        ];

        $hp_logs = $this->getLogs($urls[0]);

        $filtered_hp_logs = collect($hp_logs['hits']->hits)->map(function ($data) {
            $event = $data->_source->event ?? null;

            if ($event && Str::contains($event, 'A user attempts to logged in')) {
                return [
                    "timestamp" => $data->_source->timestamp ?? null,
                    "event" => $data->_source
                ];
            }

            return null;
        })->filter()->values();

        return response()->json([
            "total" => $filtered_hp_logs->count(),
            "values" => $filtered_hp_logs
        ]);
    }

    public function hp_events(Request $request)
    {
        $urls = [
            "http://localhost:9200/join_logs/_search",
            "http://localhost:9200/filebeat-7.14.0/_search"
        ];

        $hp_logs = $this->getLogs($urls[0]);
        $filebeat_logs = $this->getLogs($urls[1]);


        $filebeat_logs = collect($filebeat_logs['hits']->hits)->map(function ($data) {
            $checker = $data->_source->transaction->messages[0]->message;
            if (!Str::contains($checker, 'healthcheck')) {
                return $data;
            }
            return null;
        })->filter()->values();


        $hp_logs = collect($hp_logs['hits']->hits)->map(function ($data) {
            $btn_name = $data->_source->btn_name ?? null;
            if ($btn_name != null) {
                return [
                    "timestamp" => $data->_source->timestamp,
                    "event" => $data->_source->event,
                    "btn_name" => $data->_source->btn_name,
                ];
            }
            return null;
        })->filter()->values();

        return response()->json([
            "total" => $hp_logs->count() + $filebeat_logs->count()
        ]);
    }

    public function devices(Request $request)
    {

        $url = "http://localhost:9200/join_logs/_search";

        $response = Http::get($url, [
            'query' => [
                'match_all' => (object)[], // match all documents
            ],
            'size' => 1000,
        ]);

        $response = collect(json_decode($response));;
        // return $response;

        $datas = collect($response['hits']->hits)->map(function ($data) {
            $device = $data->_source->device ?? null;
            if ($device) {
                return [
                    "timestamp" => $data->_source->timestamp,
                    'device' => $data->_source->device
                    // "timestamp" => $data->_source->timestamp,
                    // "event" => $data->_source->event,
                    // "btn_name" => $data->_source->btn_name,
                ];
            }
            return null;
        })->filter()->values();

        return response()->json($datas);
    }

    public function weeklyDetection()
    {
        $urls = [
            "http://localhost:9200/filebeat-7.14.0/_search"
        ];

        $logs = $this->getLogs($urls[0]);

        $filtered_logs = collect($logs['hits']->hits)->unique(function ($data) {
            $date = new DateTime($data->_source->{'@timestamp'});
            return $date->format('Y-m-d');
        })->values()->map(function ($data) use ($logs) {
            $date = new DateTime($data->_source->{'@timestamp'});
            $dayOfMonth = $date->format('j');
            $firstDayOfMonth = $date->format('Y-m-01');
            $weekOfMonth = ceil(($dayOfMonth + date('w', strtotime($firstDayOfMonth)) - 1) / 7);
            return [
                "date" => $date->format('Y-m-d'),
                "day" => $date->format('D'),
                "month" => $date->format('M'),
                "week_of_the_month" => $weekOfMonth,
                "content" => collect($logs['hits']->hits)->filter(function ($data) use ($date) {
                    $get_date = new DateTime($data->_source->{'@timestamp'});
                    return $get_date->format('Y-m-d') == $date->format('Y-m-d');
                })->values()->map(function ($data) {
                    $dataString = json_encode($data);
                    $checker = $data->_source->transaction->messages[0]->message;
                    if ($dataString && str_contains($dataString, 'Anomaly')) {
                        return $data->_source->agent->id;
                    }
                    return null;
                })->filter()->values()
            ];
        });

        return $filtered_logs;
    }

    public function userCookies()
    {
        $url = "http://localhost:9200/join_logs/_search";

        $response = Http::get($url, [
            'query' => [
                'match_all' => (object)[], // match all documents
            ],
            'size' => 1000,
        ]);

        $response = collect(json_decode($response));

        return collect($response['hits']->hits)->filter(function ($data) {
            $check_cookie = $data->_source->user_cookie ?? null;
            return $check_cookie;
        })->unique(function ($data) {
            return $data->_source->user_cookie;
        })->values()->map(function ($data) use ($response) {
            $user_cookie = $data->_source->user_cookie;
            return [
                'user_cookie' => $user_cookie,
                'content' => collect($response['hits']->hits)->filter(function ($data) use ($user_cookie) {
                    $check_cookie = $data->_source->user_cookie ?? null;
                    return $check_cookie == $user_cookie;
                })->values()->map(function ($data) {
                    return $data->_source;
                })
            ];
        });

        return $response;
    }

    public function allThreats(Request $request)
    {
        $urls = [
            "http://localhost:9200/join_logs/_search",
            "http://localhost:9200/filebeat-7.14.0/_search",
            "http://localhost/api/decrypt",
        ];

        $hp_logs = $this->getLogs($urls[0]);
        $modsec_logs = $this->getLogs($urls[1]);

        $getCookies = collect($hp_logs['hits']->hits)->filter(function ($data) {
            $check_cookies =  $data->_source->user_cookies ?? null;
            return $check_cookies;
        })->unique('_source.user_cookies')->pluck('_source.user_cookies')->values();

        $logs = collect($modsec_logs['hits']->hits)->map(function ($data) use ($getCookies, $urls) {
            $dataString = json_encode($data);

            // Check if the word "anomaly" exists anywhere in the $data object
            if ($dataString && str_contains($dataString, 'Anomaly')) {

                $cookie_value = "";
                $check_header = $data->_source->transaction->request->headers->Cookie ?? null;

                if (Str::contains($check_header, 'hp_cookie') && $check_header) {
                    $check_header = explode("; ", $check_header);
                    $cookie = explode("=", $check_header[0]);
                    $cookie = $cookie[1];

                    $cookie = Crypt::decrypt($cookie);

                    // foreach ($getCookies as $cookies) {
                    //     $getData = $this->getApi($urls[2] . "/$cookie");
                    //     if ($getData[0] == $cookies) {
                    //         $cookie_value = $cookies;
                    //         break;
                    //     }
                    //     $cookie_value = 'No Cookie';
                    // }

                }else{
                    $cookie_value = 'No Cookies';
                }



                $anomalyScore = $this->getAnomalyScore($data);
                $attackKeywords = ['Attack', 'Injection', 'Traversal', 'Execution', 'Access', 'XSS'];

                $threatNames = collect($data->_source->transaction->messages)
                    ->pluck('message')
                    ->filter(function ($message) use ($attackKeywords) {
                        // Check if the message contains any attack-related keyword
                        foreach ($attackKeywords as $keyword) {
                            if (str_contains($message, $keyword)) {
                                return true;
                            }
                        }
                        return false;
                    })->values();


                return [
                    "threat_id" => $data->_id,
                    "threat_level" => $anomalyScore,
                    "threat" => $threatNames->implode(', '),
                    "threat_category" => "Not Available",
                    "timestamp" => $data->_source->{'@timestamp'},
                    "action" => 1,
                    "others" => [
                        "cookies" => 'No cookies'
                    ]
                ];
            }
            return null;
        })->filter()->values();

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $logs->count(),
            'recordsFiltered' => $logs->count(),
            'data' => $logs
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

    private function getApi($url){

        $url = Http::get($url);

        return collect(json_decode($url));
    }

    private function getAnomalyScore($data)
    {
        // Initialize anomaly score to 0 or some default value
        $anomalyScore = 0;

        // Check if messages array exists and is iterable
        if (isset($data->_source->transaction->messages) && is_array($data->_source->transaction->messages)) {

            // Loop through the messages array
            foreach ($data->_source->transaction->messages as $message) {

                // Check if the message contains "Anomaly score"
                if (isset($message->message) && str_contains($message->message, 'Total Score')) {

                    // Extract the anomaly score using regex or parsing
                    preg_match('/Total Score: (\d+)/', $message->message, $matches);

                    // Set the anomaly score if found
                    if (isset($matches[1])) {
                        $anomalyScore = (int) $matches[1];
                        break; // Stop searching once anomaly score is found
                    }
                }
            }
        }

        if ($anomalyScore >= 16) {
            $anomalyScore = "High";
        } else if ($anomalyScore >= 6) {
            $anomalyScore = "Medium";
        } else if ($anomalyScore >= 0) {
            $anomalyScore = "Low";
        }

        return $anomalyScore;
    }
}
