<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer sensor_id
 * @property \DateTime timestamp
 * @property integer type       Notification type
 * @property integer append     Additional information
 */
class Notification extends Model {
    /**
     * Table name
     * 
     * @var string
     */
    protected $hidden = ['created_at', 'updated_at'];
}