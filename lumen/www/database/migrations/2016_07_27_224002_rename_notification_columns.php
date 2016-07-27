<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameNotificationColumns extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('notifications', function ($table) {
            $table->renameColumn('type', 'category');
            $table->renameColumn('append', 'subcategory');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('notifications', function ($table) {
            $table->renameColumn('category', 'type');
            $table->renameColumn('subcategory', 'append');
        });
    }
}
