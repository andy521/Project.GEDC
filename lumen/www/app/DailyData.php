<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer sensor_id
 * @property string date
 * @property integer count      Daily points count
 * @property float ibi          IBI
 * @property float bpm          BPM
 * @property float tem          Temperature
 */
class DailyData extends Model {
    /**
     * Table name
     * 
     * @var string
     */
    protected $table = 'daily_data';

    public static function saveOrUpdateOnSensorData(SensorData $sensorData) {
        if (!$sensorData) {
            return null;
        }
        $date = new DateTime;
        $today = $date->format('Y-m-d');
        $instance = self::where('sensor_id', $sensorData->sensor_id)->where('date', $today)->first();
        if (!$instance) {
            $instance = new self;
        }
        $lastCount = $instance->count;
        $instance->date = $today;
        $instance->count = $lastCount + 1;
        $instance->sensor_id = $sensorData->sensor_id;
        $instance->ibi = ($instance->ibi * $lastCount + $sensorData->ibi) / ($lastCount + 1);
        $instance->bpm = ($instance->bpm * $lastCount + $sensorData->bpm) / ($lastCount + 1);
        $instance->tem = ($instance->tem * $lastCount + $sensorData->tem) / ($lastCount + 1);
        if ($instance->save()) {
            return $instance;
        } else {
            return null;
        }
    }
}