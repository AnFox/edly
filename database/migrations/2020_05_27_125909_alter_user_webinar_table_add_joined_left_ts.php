<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserWebinarTableAddJoinedLeftTs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_webinar', function (Blueprint $table) {
            $table->timestamp('joined_at')->nullable()->after('user_status');
            $table->timestamp('left_at')->nullable()->after('joined_at');
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
            $table->dropColumn([
                'joined_at',
                'left_at'
            ]);
        });
    }
}
