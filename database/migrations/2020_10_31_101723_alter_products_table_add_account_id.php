<?php

use App\Contracts\Repositories\UserRepository;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductsTableAddAccountId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        try {
            Bouncer::disallow('owner')->toManage(Banner::class);

            Bouncer::allow('owner')->toOwn(Product::class);
            Bouncer::allow('owner')->to('view-products', Product::class);
            Bouncer::allow('owner')->to('create-product', Product::class);

            Bouncer::allow('owner')->toOwn(Banner::class);
            Bouncer::allow('owner')->to('view-banners', Banner::class);
            Bouncer::allow('owner')->to('create-banner', Banner::class);

            Bouncer::allow('moderator')->toOwn(Product::class);
            Bouncer::allow('moderator')->to('view-products', Product::class);
            Bouncer::allow('moderator')->to('create-product', Product::class);

            Bouncer::allow('moderator')->toOwn(Banner::class);
            Bouncer::allow('moderator')->to('view-banners', Banner::class);
            Bouncer::allow('moderator')->to('create-banner', Banner::class);

            $banners = Banner::all();
            foreach ($banners as $banner) {
                $userRepository = app(UserRepository::class);
                $account = $userRepository->setModel($banner->room->owner)->getFirstLinkedAccount();
                if ($account) {
                    if ($product = $banner->product) {
                        $product->account_id = $account->id;
                        $product->save();
                    }
                }
            }
        } catch (Exception $e) {

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
}
