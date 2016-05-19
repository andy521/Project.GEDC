<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSensorDataTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->integer('acv')->comment('Acceleration value');
            $table->integer('acx')->comment('Acceleration direction: cos(x)');
            $table->integer('acy')->comment('Acceleration direction: cos(y)');
            $table->integer('acz')->comment('Acceleration direction: cos(z)');
            $table->integer('ibi')->comment('IBI');
            $table->integer('bpm')->comment('BPM');
            $table->integer('tem')->comment('Temperature');
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
    }
}
