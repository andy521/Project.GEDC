<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMonthlyAndHourlyDataTables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('monthly_data', function (Blueprint $table) {
            $table->date('month')->change();
            $table->dropColumn('year');
        });
        Schema::table('hourly_data', function (Blueprint $table) {
            $table->dateTime('hour')->change();
            $table->dropColumn('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('monthly_data', function (Blueprint $table) {
            $table->unsignedSmallInteger('year');
            $table->smallInteger('month')->unsignedTinyInteger('month')->change();
        });
        Schema::table('hourly_data', function (Blueprint $table) {
            $table->date('date');
            $table->smallInteger('hour')->unsignedTinyInteger('hour')->change();
        });
    }
}
