<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductsTableAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->default(Product::TYPE_BANNER)->after('id');
        });

        Product::create([
            'type' => Product::TYPE_BALANCE_REFILL,
            'name' => 'Пополнение счета',
            'description'  => 'Пополнение баланса счета владельца вебинаров.',
            'currency_id' => \App\Models\Currency::whereCode('RUB')->firstOrFail()->id,
            'price' => 600,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
