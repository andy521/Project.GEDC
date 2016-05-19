<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer sensor_id
 * @property float acv          Acceleration value
 * @property float acx          Acceleration direction: cos(x)
 * @property float acy          Acceleration direction: cos(y)
 * @property float acz          Acceleration direction: cos(z)
 * @property float ibi          IBI
 * @property float bpm          BPM
 * @property float tem          Temperature
 */
class SensorData extends Model {
    /**
     * Table name
     * 
     * @var string
     */
    protected $table = 'sensor_data';
}