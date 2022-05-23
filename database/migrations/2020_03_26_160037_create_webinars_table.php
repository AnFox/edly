<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('author_user_id');
            $table->foreign('author_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->unsignedTinyInteger('duration_minutes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webinars');
    }
}
