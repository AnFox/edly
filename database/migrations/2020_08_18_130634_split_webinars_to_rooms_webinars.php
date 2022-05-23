<?php

use App\Models\Room;
use App\Models\Webinar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SplitWebinarsToRoomsWebinars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('webinars', 'rooms');

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign('webinars_current_slide_id_foreign');
            $table->dropForeign('webinars_user_id_foreign');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->boolean('is_started')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->string('layout')->nullable();
            $table->unsignedBigInteger('current_slide_id')->nullable();
            $table->foreign('current_slide_id')->references('id')->on('media')->onDelete('SET NULL');
            $table->timestamps();
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->renameColumn('webinar_id', 'room_id');
            $table->dropForeign(['webinar_id']);
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });

        Schema::table('user_webinar', function (Blueprint $table) {
            $table->renameColumn('webinar_id', 'room_id');
        });

        $rooms = Room::all();
        //@var Room $room
        foreach ($rooms as $room) {
            Webinar::create([
                'id' => $room->id,
                'room_id' => $room->id,
                'is_started' => $room->is_started,
                'starts_at' => $room->starts_at,
                'finished_at' => $room->finished_at,
                'layout' => $room->layout,
                'current_slide_id' => $room->current_slide_id,
                'created_at' => $room->created_at,
                'updated_at' => $room->updated_at_at,
            ]);
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'is_started',
                'starts_at',
                'finished_at',
                'layout',
                'current_slide_id',
            ]);
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['webinar_id']);
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['webinar_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('webinar_id')->references('id')->on('webinars');
        });

        Schema::table('user_webinar', function (Blueprint $table) {
            $table->renameColumn('room_id', 'webinar_id');
            $table->dropForeign(['webinar_id']);
            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
        });

        DB::update('update media set model_type = ? where model_type = ?', ['App\\Models\\Room', 'App\\Models\\Webinar']);
        DB::update('update conversions set model_type = ? where model_type = ?', ['App\\Models\\Room', 'App\\Models\\Webinar']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no road back!
    }
}
