<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropNotificationTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::drop('notifications');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sensor_id');
            $table->dateTime('timestamp');
            $table->integer('type')->comment('Notification type');
            $table->integer('append')->comment('Additional information');
            $table->timestamps();

            $table->index(['sensor_id']);
        });
    }
}
