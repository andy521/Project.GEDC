<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataTables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->dateTime('timestamp');
            $table->float('acv')->comment('Acceleration value');
            $table->float('acx')->comment('Acceleration direction: cos(x)');
            $table->float('acy')->comment('Acceleration direction: cos(y)');
            $table->float('acz')->comment('Acceleration direction: cos(z)');
            $table->integer('ibi')->comment('IBI');
            $table->integer('bpm')->comment('BPM');
            $table->float('tem')->comment('Temperature');
            $table->timestamps();

            $table->index(['sensor_id']);
        });
        
        Schema::create('hourly_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->dateTime('timestamp');
            $table->integer('count')->comment('Hourly points count');
            $table->float('ibi')->comment('Daily average IBI');
            $table->float('bpm')->comment('Daily average BPM');
            $table->float('tem')->comment('Daily average Temperature');
            $table->timestamps();

            $table->index(['sensor_id']);
        });
        
        Schema::create('daily_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->dateTime('timestamp');
            $table->integer('count')->comment('Daily points count');
            $table->float('ibi')->comment('Daily average IBI');
            $table->float('bpm')->comment('Daily average BPM');
            $table->float('tem')->comment('Daily average Temperature');
            $table->timestamps();

            $table->index(['sensor_id']);
        });

        Schema::create('monthly_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->dateTime('timestamp');
            $table->integer('count')->comment('Monthly points count');
            $table->float('ibi')->comment('Daily average IBI');
            $table->float('bpm')->comment('Daily average BPM');
            $table->float('tem')->comment('Daily average Temperature');
            $table->timestamps();

            $table->index(['sensor_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('sensor_data');
        Schema::drop('hourly_data');
        Schema::drop('daily_data');
        Schema::drop('monthly_data');
    }
}
