<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->string('payment_id');
            $table->timestamp('payment_ts')->nullable();
            $table->enum('status', ['draft', 'pending','waiting_for_capture','succeeded','canceled', 'failed', 'refunded'])->default('draft');
            $table->boolean('paid');
            $table->text('amount');
            $table->text('payment_method');
            $table->text('description')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('test');
            $table->timestamp('synced_at')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
