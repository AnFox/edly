<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWebinarsTableAddIsLimitReachedNotified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->boolean('is_limit_reached_notified')->after('is_recordable')->default(0);
            $table->boolean('is_limit_reaching_notified')->after('is_recordable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->removeColumn('is_limit_reached_notified');
            $table->removeColumn('is_limit_reaching_notified');
        });
    }
}
