<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserWebinarTableAddIsCharging extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_webinar', function (Blueprint $table) {
            $table->boolean('is_charging')->default(false)->after('is_paid');
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
            $table->dropColumn('is_charging');
        });
    }
}
