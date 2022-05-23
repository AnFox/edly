<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserWebinarPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_webinar', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('webinar_id')->index();
            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
            $table->primary(['user_id', 'webinar_id']);
            $table->boolean('is_user_online')->default(true);
            $table->boolean('is_paid')->default(false);
            $table->unsignedTinyInteger('user_status')->default(User::STATUS_ACTIVE);
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
        Schema::dropIfExists('user_webinar');
    }
}
