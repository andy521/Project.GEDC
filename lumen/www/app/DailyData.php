<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer sensor_id
 * @property \DateTime timestamp
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
    protected $hidden = ['created_at', 'updated_at'];

    public static function saveOrUpdateOnSensorData(SensorData $sensorData) {
        if (!$sensorData) {
            return null;
        }
        $timestamp = $sensorData->timestamp->format('Y-m-d 00:00:00');
        $instance = self::where('sensor_id', $sensorData->sensor_id)->where('timestamp', $timestamp)->first();
        if (!$instance) {
            $instance = new self;
        }
        $lastCount = $instance->count;
        $instance->timestamp = $timestamp;
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