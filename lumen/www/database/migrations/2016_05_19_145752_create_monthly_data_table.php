<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonthlyDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('monthly_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->integer('count')->comment('Monthly points count');
            $table->float('ibi')->comment('Daily average IBI');
            $table->float('bpm')->comment('Daily average BPM');
            $table->float('tem')->comment('Daily average Temperature');
            $table->timestamps();

            $table->index(['sensor_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('monthly_data');
    }
}
