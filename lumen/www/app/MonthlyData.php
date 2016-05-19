<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer sensor_id
 * @property integer year
 * @property integer month
 * @property integer count      Monthly points count
 * @property float ibi          IBI
 * @property float bpm          BPM
 * @property float tem          Temperature
 */
class MonthlyData extends Model {
    /**
     * Table name
     * 
     * @var string
     */
    protected $table = 'monthly_data';

    public static function saveOrUpdateOnSensorData(SensorData $sensorData) {
        if (!$sensorData) {
            return null;
        }
        $date = new DateTime;
        $year = $date->format('Y');
        $month = $date->format('m');
        $instance = self::where('sensor_id', $sensorData->sensor_id)->where('year', $year)->where('month', $month)->first();
        if (!$instance) {
            $instance = new self;
        }
        $lastCount = $instance->count;
        $instance->year = $year;
        $instance->month = $month;
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