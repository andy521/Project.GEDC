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

$app->group(['namespace' => 'App\Http\Controllers', 'prefix' => 'auth'], function ($app) {
    $app->post('/login', 'AuthController@login');
    $app->post('/register', 'AuthController@register');
});

$app->group(['namespace' => 'App\Http\Controllers', 'prefix' => 'data', 'middleware' => 'auth'], function ($app) {
    $app->get('/{sensor_id}', 'DataController@index');
    $app->get('/{sensor_id}/hour', 'DataController@hourIndex');
    $app->get('/{sensor_id}/day', 'DataController@dayIndex');
    $app->get('/{sensor_id}/month', 'DataController@monthIndex');
});
