<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSensorDataTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->float('acv')->change();
            $table->float('acx')->change();
            $table->float('acy')->change();
            $table->float('acz')->change();
            $table->float('tem')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->integer('acv')->change();
            $table->integer('acx')->change();
            $table->integer('acy')->change();
            $table->integer('acz')->change();
            $table->integer('tem')->change();
        });
    }
}
