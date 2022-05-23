<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterChatMessagesTableAddFake extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_fake')->default(false)->after('message');
            $table->unsignedBigInteger('fake_sender_user_id')->nullable()->after('is_fake')->index();
            $table->string('fake_sender_user_name')->nullable()->after('fake_sender_user_id');
            $table->unsignedBigInteger('sender_user_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn([
                'is_fake',
                'fake_sender_user_id',
                'fake_sender_user_name'
            ]);
        });
    }
}
