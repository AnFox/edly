<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserWebinarTableChangeDefaultIsUserOnline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_webinar', function (Blueprint $table) {
            $table->boolean('is_user_online')->change()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_webinar', function (Blueprint $table) {
            $table->boolean('is_user_online')->change()->default(true);
        });
    }
}
