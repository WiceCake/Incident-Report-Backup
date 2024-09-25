<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {

        $urls = [
            "http://incident-report.test:81/api/v1/logged_in",
            "http://incident-report.test:81/api/v1/logged_in/attempt",
            "http://incident-report.test:81/api/v1/hp_events",
            "http://incident-report.test:81/api/v1/devices",
            "http://incident-report.test:81/api/v1/detection/weekly",
            "http://incident-report.test:81/api/v1/cookies",
            "http://incident-report.test:81/api/v1/threats/all"
        ];

        $mobile = $this->getLogs($urls[3])->filter(function ($data) {
            return $data->device === 'Mobile';
        });
        $desktop = $this->getLogs($urls[3])->filter(function ($data) {
            return $data->device === 'Desktop';
        });

        $devices = collect([
            'mobile' => $mobile,
            'desktop' => $desktop
        ])->sortByDesc(function ($item) {
            return $item->count();
        });

        $logged_in = $this->getLogs($urls[0]);
        $logged_in_attempt = $this->getLogs($urls[1]);
        $events = $this->getLogs($urls[2]);
        $weekly_detection = $this->getLogs($urls[4]);
        $cookies = $this->getLogs($urls[5]);
        $all_threats = $this->getLogs($urls[6]);

        $date_now = new DateTime();
        $weekly_threats = collect($weekly_detection)
            ->filter(function ($item) use ($date_now) {
                return $item->month === $date_now->format('M');
            })
            ->sum(function ($item) {
                return count($item->content);
            });

        return view('pages.dashboard', compact(['logged_in', 'logged_in_attempt', 'events', 'devices', 'weekly_detection', 'weekly_threats', 'cookies', 'all_threats']));
    }

    private function getLogs($url)
    {
        $logs = Http::get($url);

        return collect(json_decode($logs));
    }
}
