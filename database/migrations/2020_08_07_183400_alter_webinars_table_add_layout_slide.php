<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWebinarsTableAddLayoutSlide extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->string('layout')->nullable()->after('video_src');
            $table->unsignedBigInteger('current_slide_id')->nullable()->after('layout');
            $table->foreign('current_slide_id')->references('id')->on('media')->onDelete('SET NULL');
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
                'layout',
                'current_slide_id',
            ]);
        });
    }
}
