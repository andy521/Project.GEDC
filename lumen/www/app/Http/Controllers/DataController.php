<?php

namespace App\Http\Controllers;

use App\DailyData;
use App\HourlyData;
use App\MonthlyData;
use App\SensorData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DataController extends Controller {

    public function index (Request $request) {
        $this->validate($request, [
            'sensor_id' => 'required|integer',
            'scale' => 'in:month,day,hour',
            'from' => 'date',
            'to' => 'date',
        ]);
        $sensorId = Input::get('sensor_id');
        $scale = Input::get('scale', 'second');
        $to = Input::get('to', date("Y-m-d H:i:s"));
        $from = Input::get('from', date("Y-m-d H:i:s"));
        switch ($scale) {
            case 'month':
                $results = MonthlyData::where([
                    ['sensor_id', $sensorId],
                    ['month', '>=', date('Y-m-00', strtotime($from))],
                    ['month', '<=', date('Y-m-00', strtotime($to))],
                ])->get();
                break;
            case 'day':
                $results = DailyData::where([
                    ['sensor_id', $sensorId],
                    ['date', '>=', date('Y-m-d', strtotime($from))],
                    ['date', '<=', date('Y-m-d', strtotime($to))],
                ])->get();
                break;
            case 'hour':
                $results = HourlyData::where([
                    ['sensor_id', $sensorId],
                    ['hour', '>=', date('Y-m-d H:00:00', strtotime($from))],
                    ['hour', '<=', date('Y-m-d H:00:00', strtotime($to))],
                ])->get();
                break;
            default:
                $results = SensorData::where([
                    ['sensor_id', $sensorId],
                    ['created_at', '>=', date('Y-m-d H:i:s', strtotime($from))],
                    ['created_at', '<=', date('Y-m-d H:i:s', strtotime($to))],
                ])->get();
                break;
        }
        return $results->toJson();
    }

}
