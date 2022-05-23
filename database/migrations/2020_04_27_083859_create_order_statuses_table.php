<?php

use App\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->unsignedTinyInteger('id');
            $table->string('name');
            $table->timestamps();

            $table->primary('id');
        });

        $statuses = [
            OrderStatus::ORDER_STATUS_DRAFT => 'draft',
            OrderStatus::ORDER_STATUS_PENDING => 'pending',
            OrderStatus::ORDER_STATUS_PAID => 'paid',
            OrderStatus::ORDER_STATUS_CANCELED => 'canceled',
        ];

        foreach ($statuses as $id => $name) {
            OrderStatus::create(compact('id', 'name'));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_statuses');
    }
}
