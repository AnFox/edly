<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWebinarsTableRenameAuthorUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropForeign('webinars_author_user_id_foreign');
            $table->renameColumn('author_user_id', 'user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
            $table->dropForeign('webinars_user_id_foreign');
            $table->renameColumn('user_id', 'author_user_id');
            $table->foreign('author_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
