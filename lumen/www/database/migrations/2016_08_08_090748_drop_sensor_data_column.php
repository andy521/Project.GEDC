<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSensorDataColumn extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropColumn('acv');
            $table->dropColumn('acx');
            $table->dropColumn('acy');
            $table->dropColumn('acz');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->float('acv')->comment('Acceleration value');
            $table->float('acx')->comment('Acceleration direction: cos(x)');
            $table->float('acy')->comment('Acceleration direction: cos(y)');
            $table->float('acz')->comment('Acceleration direction: cos(z)');
        });
    }
}
