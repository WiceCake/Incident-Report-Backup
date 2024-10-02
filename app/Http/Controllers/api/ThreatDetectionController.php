<?php

namespace App\Http\Controllers\api;

use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;

class ThreatDetectionController extends Controller
{
    //
    public function logged_in()
    {
        $urls = [
            "http://uat.muti.group:9200/filebeat-7.14.0/_search"
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
            "http://uat.muti.group:9200/join_logs/_search",
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
            "http://uat.muti.group:9200/join_logs/_search",
            "http://uat.muti.group:9200/filebeat-7.14.0/_search"
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

        $url = "http://uat.muti.group:9200/join_logs/_search";


        $response = $this->getLogs($url);
        // return $response;

        if ($response->count()) {
            $response = collect($response['hits']->hits)->map(function ($data) {
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
        }

        return response()->json($response);
    }

    public function weeklyDetection()
    {
        $urls = [
            "http://uat.muti.group:9200/join_logs/_search",
            "http://uat.muti.group:9200/filebeat-7.14.0/_search"
        ];

        $hp_logs = $this->getLogs($urls[0]);
        $modsec_logs = $this->getLogs($urls[1]);
        $final_merged_logs = collect();

        // return $hp_logs;

        // dd($hp_logs);

        if ($hp_logs->count()) {

            $hp_logs = collect($hp_logs['hits']->hits)
                // Group by date first to avoid processing the same date multiple times
                ->groupBy(function ($data) {
                    $date = new DateTime($data->_source->timestamp);
                    return $date->format('Y-m-d');
                })
                // Now process each date group
                ->map(function ($logs, $dateKey) {
                    $date = new DateTime($dateKey);
                    $dayOfMonth = $date->format('j');
                    $firstDayOfMonth = $date->format('Y-m-01');
                    $weekOfMonth = ceil(($dayOfMonth + date('w', strtotime($firstDayOfMonth)) - 1) / 7);

                    $content = collect($logs)->filter(function ($data) use ($date) {
                        $get_date = new DateTime($data->_source->timestamp);
                        return $get_date->format('Y-m-d') == $date->format('Y-m-d');
                    })->values()
                        ->map(function ($data) {
                            $hasUserCookie = $data->_source->user_cookie ?? '';
                            if ($hasUserCookie) {
                                return $data->_id;
                            }
                            return null;
                        })->filter()->values();

                    return [
                        "date" => $date->format('Y-m-d'),
                        "day" => $date->format('D'),
                        "month" => $date->format('M'),
                        "week_of_the_month" => $weekOfMonth,
                        "content" => $content
                    ];

                    return null;
                })
                ->filter() // Remove any null values
                ->values(); // Re-index the collection

        }

        if ($modsec_logs->count()) {
            $modsec_logs = collect($modsec_logs['hits']->hits)->unique(function ($data) {
                $date = new DateTime($data->_source->{'@timestamp'});
                return $date->format('Y-m-d');
            })->values()->map(function ($data) use ($modsec_logs) {
                $date = new DateTime($data->_source->{'@timestamp'});
                $dayOfMonth = $date->format('j');
                $firstDayOfMonth = $date->format('Y-m-01');
                $weekOfMonth = ceil(($dayOfMonth + date('w', strtotime($firstDayOfMonth)) - 1) / 7);

                $content = collect($modsec_logs['hits']->hits)->filter(function ($data) use ($date) {
                    $get_date = new DateTime($data->_source->{'@timestamp'});
                    return $get_date->format('Y-m-d') == $date->format('Y-m-d');
                })->values()->map(function ($data) {
                    $dataString = json_encode($data);
                    $checker = $data->_source->transaction->messages[0]->message;
                    if ($dataString && str_contains($dataString, 'Anomaly')) {
                        return $data->_source->agent->id;
                    }
                    return null;
                })->filter()->values();

                return [
                    "date" => $date->format('Y-m-d'),
                    "day" => $date->format('D'),
                    "month" => $date->format('M'),
                    "week_of_the_month" => $weekOfMonth,
                    "content" => $content
                ];
            });
        }

        if ($hp_logs->count() && $modsec_logs->count()) {
            $merged_logs = $hp_logs->map(function ($log) use ($modsec_logs) {
                // Normalize the date format for both logs
                $log_date = (new DateTime($log['date']))->format('Y-m-d');

                // Find the matching log by normalized date
                $matching_log = $modsec_logs->first(function ($modsec_log) use ($log_date) {
                    return (new DateTime($modsec_log['date']))->format('Y-m-d') === $log_date;
                });

                if ($matching_log) {
                    // Merge the content arrays
                    $log['content'] = collect($log['content'])
                        ->merge($matching_log['content'])
                        ->values();
                }

                return $log;
            });


            // Find logs in modsec_logs that have no match in hp_logs
            $modsec_logs_only = $modsec_logs->reject(function ($modsec_log) use ($hp_logs) {
                $modsec_date = (new DateTime($modsec_log['date']))->format('Y-m-d');
                return $hp_logs->contains(function ($log) use ($modsec_date) {
                    return (new DateTime($log['date']))->format('Y-m-d') === $modsec_date;
                });
            });


            // Merge all logs together
            $final_merged_logs = $merged_logs->merge($modsec_logs_only);

            // Sort the final logs by date and re-index
            $final_merged_logs = $final_merged_logs->sortByDesc('date')->values();
        }



        return $final_merged_logs;
    }

    public function userCookies()
    {
        $url = "http://uat.muti.group:9200/join_logs/_search";

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

    public function allThreatsNotFiltered(Request $request)
    {
        $urls = [
            "http://uat.muti.group:9200/join_logs/_search",
            "http://uat.muti.group:9200/filebeat-7.14.0/_search",
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

                if ($hasUserCookie && $hasEvent) {

                    $getData = $getData->first();

                    return [
                        "threat_id" => $data->_id,
                        "threat_level" => "Low",
                        "threat" => $data->_source->event . ' on Honeypot' ?? null,
                        "threat_category" => "Not Available",
                        "timestamp" => $data->_source->timestamp,
                        "ip_address" => $getData->user_ip,
                        "action" => 1,
                        "others" => [
                            "cookies" => $hasUserCookie,
                            "btn_name" => $data->_source->btn_name ?? null,
                            "user_agent" => $getData->user_agent,
                            "url" => $getData->current_url,
                            "referrer_url" => $getData->referrer_url,
                            "device" => $getData->device,
                        ]
                    ];
                }

                return null;
            })->filter()->values();
        }

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
                        $cookie_value = 'No Cookies';
                    }

                    $anomalyScore = $this->getAnomalyScore($data);
                    $attackKeywords = ['Attack', 'Injection', 'Traversal', 'Execution', 'Access', 'XSS', 'Leakage'];

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

                    $url = $data->_source->transaction->request;

                    $device = $this->getDevice($data->_source->transaction->request->headers->{'User-Agent'});

                    return [
                        "threat_id" => $data->_id,
                        "threat_level" => $anomalyScore,
                        "threat" => $threatNames->implode(', '),
                        // "threat_data" => $data->_source->transaction->messages,
                        "threat_category" => "Not Available",
                        "timestamp" => $data->_source->{'@timestamp'},
                        "ip_address" => $data->_source->transaction->client_ip,
                        "action" => 1,
                        "others" => [
                            "cookies" => $cookie_value,
                            "btn_name" => null,
                            "user_agent" => $data->_source->transaction->request->headers->{'User-Agent'},
                            "url" => "http://" . $url->headers->Host . $url->uri,
                            "referrer_url" => "http://" . $url->headers->Host,
                            "device" => $device
                        ]
                    ];
                }
                return null;
            })->filter()->values();
        }

        $merge_logs = $hp_logs->merge($modsec_logs);

        // dd($merge_logs);

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $merge_logs->count(),
            'recordsFiltered' => $merge_logs->count(),
            'data' => $merge_logs
        ]);
    }
    public function allThreats(Request $request)
    {
        $urls = [
            "http://uat.muti.group:9200/join_logs/_search",
            "http://uat.muti.group:9200/filebeat-7.14.0/_search",
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

                if ($hasUserCookie && $hasEvent) {

                    $checkReport = $this->findReport($data->_id);

                    if ($checkReport) {
                        return null;
                    }

                    $getData = $getData->first();

                    return [
                        "threat_id" => $data->_id,
                        "threat_level" => "Low",
                        "threat" => $data->_source->event . ' on Honeypot' ?? null,
                        "threat_category" => "Not Available",
                        "timestamp" => $data->_source->timestamp,
                        "ip_address" => $getData->user_ip,
                        "action" => 1,
                        "others" => [
                            "cookies" => $hasUserCookie,
                            "btn_name" => $data->_source->btn_name ?? null,
                            "user_agent" => $getData->user_agent,
                            "url" => $getData->current_url,
                            "referrer_url" => $getData->referrer_url,
                            "device" => $getData->device,
                        ]
                    ];
                }

                return null;
            })->filter()->values();
        }

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
                        $cookie_value = 'No Cookies';
                    }

                    $anomalyScore = $this->getAnomalyScore($data);
                    $attackKeywords = ['Attack', 'Injection', 'Traversal', 'Execution', 'Access', 'XSS', 'Leakage'];

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

                    $url = $data->_source->transaction->request;

                    $device = $this->getDevice($data->_source->transaction->request->headers->{'User-Agent'});

                    $checkReport = $this->findReport($data->_id);

                    if ($checkReport) {
                        return null;
                    }

                    return [
                        "threat_id" => $data->_id,
                        "threat_level" => $anomalyScore,
                        "threat" => $threatNames->implode(', '),
                        // "threat_data" => $data->_source->transaction->messages,
                        "threat_category" => "Not Available",
                        "timestamp" => $data->_source->{'@timestamp'},
                        "ip_address" => $data->_source->transaction->client_ip,
                        "action" => 1,
                        "others" => [
                            "cookies" => $cookie_value,
                            "btn_name" => null,
                            "user_agent" => $data->_source->transaction->request->headers->{'User-Agent'},
                            "url" => "http://" . $url->headers->Host . $url->uri,
                            "referrer_url" => "http://" . $url->headers->Host,
                            "device" => $device
                        ]
                    ];
                }
                return null;
            })->filter()->values();
        }

        $merge_logs = $hp_logs->merge($modsec_logs);

        // dd($merge_logs);

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $merge_logs->count(),
            'recordsFiltered' => $merge_logs->count(),
            'data' => $merge_logs
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

    private function getApi($url)
    {

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

    private function getDevice($user_agent)
    {
        if (Str::contains($user_agent, "Tablet")) {
            return "Tablet";
        }

        if (Str::contains($user_agent, "Mobile")) {
            return "Mobile";
        }

        if (Str::contains($user_agent, "Windows")) {
            return "Desktop";
        }
    }

    private function findReport($threat_id)
    {
        $client = ClientBuilder::create()->build();

        try {
            $params = [
                'index' => 'prefix-incident_reports', // Replace with your index name
                'body'  => [
                    'query' => [
                        'match' => [
                            'threat_id' => $threat_id // Replace with your field name and value
                        ]
                    ]
                ]
            ];

            // Perform the search
            $response = $client->search($params);

            // Check if there are any hits and return their count
            return isset($response['hits']['hits']) ? count($response['hits']['hits']) : null;
        } catch (\Exception $e) {
            // Handle other potential exceptions
            // You may want to log this error or return a default value
            return null; // Or handle differently based on your application's needs
        }
    }
}
