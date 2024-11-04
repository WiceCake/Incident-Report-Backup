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
            "http://elasticsearch:9200/join_logs/_search"
        ];

        $logs = collect($this->getLogs($urls[0]));

        $logs = collect($logs['hits']->hits)->map(function ($data) {

            $checkStatus = $data->_source->status ?? null;

            if ($checkStatus && $checkStatus == 'success') {
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
            "http://elasticsearch:9200/join_logs/_search",
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
            "http://elasticsearch:9200/join_logs/_search",
        ];

        $hp_logs = $this->getLogs($urls[0]);

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
            "total" => $hp_logs->count()
        ]);
    }

    public function devices(Request $request)
    {

        $url = "http://elasticsearch:9200/join_logs/_search";


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
            "http://elasticsearch:9200/join_logs/_search",
        ];

        $hp_logs = $this->getLogs($urls[0]);

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
                            $hasUserCookie = $data->_source->user_cookie ?? $data->_source->cookies ?? '';
                            if ($hasUserCookie) {
                                return $data->_id;
                            }
                            return null;
                        })->filter()->values();


                        // dd($content);

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

        return $hp_logs;
    }

    public function userCookies()
    {
        $url = "http://elasticsearch:9200/join_logs/_search";

        $response = Http::get($url, [
            'query' => [
                'match_all' => (object)[], // match all documents
            ],
            'size' => 1000,
        ]);

        $response = collect(json_decode($response));

        return collect($response['hits']->hits)->filter(function ($data) {
            $check_cookie = $data->_source->cookies ?? null;
            return $check_cookie;
        })->unique(function ($data) {
            return $data->_source->cookies;
        })->values()->map(function ($data) use ($response) {
            $user_cookie = $data->_source->cookies;
            return [
                'user_cookie' => $user_cookie,
                'content' => collect($response['hits']->hits)->filter(function ($data) use ($user_cookie) {
                    $check_cookie = $data->_source->cookies ?? $data->_source->user_cookie ?? null;
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
            "http://elasticsearch:9200/join_logs/_search",
        ];

        $hp_logs = $this->getLogs($urls[0]);

        if ($hp_logs->count()) {
            $getData = collect($hp_logs['hits']->hits)->map(function ($data) {
                $hasIPAddress = $data->_source->ip_address ?? null;
                $hasUserCookie = $data->_source->cookies ?? $data->_source->user_cookie ?? null;

                if ($hasIPAddress && $hasUserCookie) {
                    return $data->_source;
                }

                return null;
            })->filter()->unique('cookies')->values();


            $hp_logs = collect($hp_logs['hits']->hits)->map(function ($data) use ($getData) {
                $hasUserCookie = $data->_source->cookies ?? $data->_source->user_cookie ?? '';
                $hasEvent = $data->_source->attack ?? $data->_source->event ?? null;

                $getData = $getData->map(function ($data) use ($hasUserCookie) {
                    if ($hasUserCookie == $data->cookies) {
                        return $data;
                    }
                    return null;
                })->filter()->values();

                if ($hasUserCookie && $hasEvent) {

                    $getData = $getData->first();
                    // $checkLevel = $this->checkLevel($data->_source->attack ?? '');

                    return [
                        "threat_id" => $data->_id,
                        "threat_level" => $data->_source->attack_details->severity ?? 'Low',
                        "threat_score" => $data->_source->attack_details->riskScore ?? 0,
                        "threat" => $data->_source->attack ?? $data->_source->event . ' on Honeypot' ?? null,
                        "timestamp" => $data->_source->timestamp,
                        "ip_address" => $getData->ip_address ?? $data->_source->ip_address ?? '',
                        "action" => 1,
                        "others" => [
                            "cookies" => $hasUserCookie,
                            "btn_name" => $data->_source->btn_name ?? null,
                            "user_agent" => $getData->user_agent ?? $data->_source->user_agent ?? null,
                            "url" => $getData->current_url ?? $data->_source->url ?? null,
                            "referrer_url" => $getData->referrer_url ?? null,
                            "device" => $getData->device ?? $data->_source->device ?? null,
                        ]
                    ];
                }

                return null;
            })->filter()->values();
        }


        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $hp_logs->count(),
            'recordsFiltered' => $hp_logs->count(),
            'data' => $hp_logs
        ]);
    }
    public function allThreats(Request $request)
    {
        $urls = [
            "http://elasticsearch:9200/join_logs/_search",
        ];

        $hp_logs = $this->getLogs($urls[0]);

        if ($hp_logs->count()) {

            $getData = collect($hp_logs['hits']->hits)->map(function ($data) {
                $hasIPAddress = $data->_source->ip_address ?? null;
                $hasUserCookie = $data->_source->cookies ?? $data->_source->user_cookie ?? null;

                if ($hasIPAddress && $hasUserCookie) {
                    return $data->_source;
                }

                return null;
            })->filter()->unique('cookies')->values();

            // dd($hp_logs);

            // dd($hp_logs);
            $hp_logs = collect($hp_logs['hits']->hits)->map(function ($data) use ($getData) {
                // dd($data->_source->attack);
                $hasUserCookie = $data->_source->cookies ?? $data->_source->user_cookie ?? '';
                $hasEvent = $data->_source->attack ?? $data->_source->event ?? null;

                $getData = $getData->map(function ($data) use ($hasUserCookie) {
                    if ($hasUserCookie == $data->cookies) {
                        return $data;
                    }
                    return null;
                })->filter()->values();

                if ($hasEvent) {

                    $checkReport = $this->findReport($data->_id);

                    if ($checkReport) {
                        return null;
                    }

                    $getData = $getData->first();
                    // $checkLevel = $this->checkLevel($data->_source->attack ?? '');
                    $checkEvent = Str::contains($data->_source->event ?? '', 'attempts');

                    if ($checkEvent) {
                        return null;
                    }

                    return [
                        "threat_id" => $data->_id,
                        "threat_level" => $data->_source->attack_details->severity ?? 'Low',
                        "threat_score" => $data->_source->attack_details->riskScore ?? 0,
                        "threat" => $data->_source->attack ?? $data->_source->event . ' on Honeypot' ?? null,
                        "timestamp" => $data->_source->timestamp,
                        "ip_address" => $getData->ip_address ?? $data->_source->ip_address ?? '',
                        "action" => 1,
                        "others" => [
                            "cookies" => $hasUserCookie,
                            "btn_name" => $data->_source->btn_name ?? null,
                            "user_agent" => $getData->user_agent ?? $data->_source->user_agent ?? null,
                            "url" => $getData->current_url ?? $data->_source->url ?? null,
                            "referrer_url" => $getData->referrer_url ?? null,
                            "device" => $getData->device ?? $data->_source->device ?? null,
                        ]
                    ];
                }

                return null;
            })->filter()->values();
        }

        // dd($hp_logs);

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $hp_logs->count(),
            'recordsFiltered' => $hp_logs->count(),
            'data' => $hp_logs
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

    // private function getAnomalyScore($data)
    // {
    //     // Initialize anomaly score to 0 or some default value
    //     $anomalyScore = 0;

    //     // Check if messages array exists and is iterable
    //     if (isset($data->_source->transaction->messages) && is_array($data->_source->transaction->messages)) {

    //         // Loop through the messages array
    //         foreach ($data->_source->transaction->messages as $message) {

    //             // Check if the message contains "Anomaly score"
    //             if (isset($message->message) && str_contains($message->message, 'Total Score')) {

    //                 // Extract the anomaly score using regex or parsing
    //                 preg_match('/Total Score: (\d+)/', $message->message, $matches);

    //                 // Set the anomaly score if found
    //                 if (isset($matches[1])) {
    //                     $anomalyScore = (int) $matches[1];
    //                     break; // Stop searching once anomaly score is found
    //                 }
    //             }
    //         }
    //     }

    //     if ($anomalyScore >= 16) {
    //         $anomalyScore = "High";
    //     } else if ($anomalyScore >= 6) {
    //         $anomalyScore = "Medium";
    //     } else if ($anomalyScore >= 0) {
    //         $anomalyScore = "Low";
    //     }

    //     return $anomalyScore;
    // }

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

        return "Unknown Device";
    }

    private function findReport($threat_id)
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

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
