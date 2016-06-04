<?php

namespace App\Http\Controllers;

use App\DailyData;
use App\HourlyData;
use App\MonthlyData;
use App\SensorData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DataController extends Controller {

    public function index (Request $request, $sensorId) {
        $this->validate($request, ['from' => 'date', 'to' => 'date']);
        $to = Input::get('to', date("Y-m-d H:i:s"));
        $from = Input::get('from', date("Y-m-d H:i:s"));
        $results = SensorData::where([
            ['sensor_id', $sensorId],
            ['timestamp', '>=', date('Y-m-d H:i:s', strtotime($from))],
            ['timestamp', '<=', date('Y-m-d H:i:s', strtotime($to))],
        ])->get();
        return $results->toJson();
    }

    public function hourIndex (Request $request, $sensorId) {
        $this->validate($request, ['from' => 'date', 'to' => 'date']);
        $to = Input::get('to', date("Y-m-d H:i:s"));
        $from = Input::get('from', date("Y-m-d H:i:s"));
        $results = HourlyData::where([
            ['sensor_id', $sensorId],
            ['timestamp', '>=', date('Y-m-d H:00:00', strtotime($from))],
            ['timestamp', '<=', date('Y-m-d H:00:00', strtotime($to))],
        ])->get();
        return $results->toJson();
    }

    public function dayIndex (Request $request, $sensorId) {
        $this->validate($request, ['from' => 'date', 'to' => 'date']);
        $to = Input::get('to', date("Y-m-d H:i:s"));
        $from = Input::get('from', date("Y-m-d H:i:s"));
        $results = DailyData::where([
            ['sensor_id', $sensorId],
            ['timestamp', '>=', date('Y-m-d 00:00:00', strtotime($from))],
            ['timestamp', '<=', date('Y-m-d 23:59:59', strtotime($to))],
        ])->get();
        return $results->toJson();
    }

    public function monthIndex (Request $request, $sensorId) {
        $this->validate($request, ['from' => 'date', 'to' => 'date']);
        $to = Input::get('to', date("Y-m-d H:i:s"));
        $from = Input::get('from', date("Y-m-d H:i:s"));
        $results = MonthlyData::where([
            ['sensor_id', $sensorId],
            ['timestamp', '>=', date('Y-m-01 00:00:00', strtotime($from))],
            ['timestamp', '<=', date('Y-m-01 23:59:59', strtotime($to))],
        ])->get();
        return $results->toJson();
    }

}
