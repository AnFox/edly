<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWebinarsTableAddBotLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->boolean('is_bot_assign_required')->default(false)->after('video_src');
            $table->string('bot_url_telegram')->nullable()->after('is_bot_assign_required');
            $table->string('bot_url_whatsapp')->nullable()->after('bot_url_telegram');
            $table->string('bot_url_viber')->nullable()->after('bot_url_whatsapp');
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
            $table->dropColumn([
                'is_bot_assign_required',
                'bot_url_telegram',
                'bot_url_whatsapp',
                'bot_url_viber',
            ]);
        });
    }
}
