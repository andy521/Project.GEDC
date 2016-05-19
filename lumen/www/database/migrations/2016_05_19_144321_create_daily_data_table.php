<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyDataTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('daily_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->date('date');
            $table->integer('count')->comment('Daily points count');
            $table->float('ibi')->comment('Daily average IBI');
            $table->float('bpm')->comment('Daily average BPM');
            $table->float('tem')->comment('Daily average Temperature');
            $table->timestamps();

            $table->index(['sensor_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('daily_data');
    }
}
