<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/{sensor_id}', 'DataController@index');
$app->get('/{sensor_id}/hour', 'DataController@hourIndex');
$app->get('/{sensor_id}/day', 'DataController@dayIndex');
$app->get('/{sensor_id}/month', 'DataController@monthIndex');
