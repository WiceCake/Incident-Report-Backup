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
            ];
        })->values()->first();

        return view('pages.reports.security-events.view-report', compact('data'));
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
