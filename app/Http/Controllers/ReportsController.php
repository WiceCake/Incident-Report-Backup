<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportsController extends Controller
{
    //
    public function incidentIndex($id)
    {
        $url = "http://localhost:9200/join_logs/_search";

        $response = Http::post($url, [
            'query' => [
                'term' => [
                    '_id' => $id
                ]
            ]
        ]);

        $response = collect(json_decode($response));

        // dd($this->findData(''));

        $data = collect($response['hits']->hits);
        // $user_cookie = $data->first()->_source->user_cookie;
        // $cookie_data = $this->findData($user_cookie)->first()->_source;

        $data = $data->map(function ($data){
            return [
                $data
                // '_index' => $data->_index,
                // '_id' => $data->_id,
                // '_source' => [
                //     'timestamp' => $data->_source->timestamp,
                //     'btn_name' => $data->_source->btn_name ?? null,
                //     'event' => $data->_source->event ?? null,
                //     'threat_name' => 'Honeypot Interaction',
                //     'threat_level' => 'High',
                //     'user_cookie' => $data->_source->event,
                //     'user_ip' => $cookie_data->user_ip,
                //     'user_email' => $cookie_data->user_email,
                //     'user_password' => $cookie_data->user_password,
                //     'user_agent' => $cookie_data->user_agent,
                //     'device' => $cookie_data->device,
                // ]
            ];
        })->values()->first();

        return view('pages.reports.incident-report.view-report', compact('data'));
    }

    private function findData($user_cookies)
    {
        $url = "http://localhost:9200/join_logs/_search";

        $response = Http::get($url, [
            'query' => [
                'match_all' => (object)[], // match all documents
            ],
            'size' => 10000,
        ]);

        $response = collect(json_decode($response));

        $getIPs = collect($response['hits']->hits)->filter(function ($data){
            $check_ip =  $data->_source->user_ip ?? null;
            $check_cookies =  $data->_source->user_cookies ?? null;
            return $check_ip && $check_cookies;
        })->unique('_source.user_cookies')->values();

        $data = $getIPs->filter(function ($data) use ($user_cookies){
            return $data->_source->user_cookies == $user_cookies;
        });

        return $data;
    }
}
