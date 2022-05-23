<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWebinarsTableAddFinishedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dateTime('finished_at')->after('duration_minutes')->nullable();
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
            $table->dropColumn('finished_at');
        });
    }
}
