<?php

namespace App\Providers;

use App\Contracts\Repositories\UserRepository;
use App\Models\Banner;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Models\Webinar;
use App\Observers\BannerObserver;
use App\Observers\ChatMessageObserver;
use App\Observers\ProductObserver;
use App\Observers\RoomObserver;
use App\Observers\UserObserver;
use App\Observers\WebinarObserver;
use Bouncer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Services
        $this->app->bind(
            'App\Contracts\Services\UserServiceInterface',
            'App\Services\UserService'
        );

        $this->app->bind(
            'App\Contracts\Services\PurchaseService',
            'App\Services\CloudPaymentsPurchaseService'
        );

        $this->app->bind(
            'App\Contracts\Services\ScriptImportService',
            'App\Services\BizonScriptImportService'
        );

        //Repositories
        $this->app->bind(
            'App\Contracts\Repositories\UserRepository',
            'App\Repositories\UserRepositoryEloquent'
        );
        
        $this->app->bind(
            'App\Contracts\Repositories\RoomRepository',
            'App\Repositories\RoomRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\WebinarRepository',
            'App\Repositories\WebinarRepositoryEloquent'
        );
        
        $this->app->bind(
            'App\Contracts\Repositories\ChatRepository',
            'App\Repositories\ChatRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\ChatMessageRepository',
            'App\Repositories\ChatMessageRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\BannerRepository',
            'App\Repositories\BannerRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\ProductRepository',
            'App\Repositories\ProductRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\CurrencyRepository',
            'App\Repositories\CurrencyRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\AccountRepository',
            'App\Repositories\AccountRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\OrderRepository',
            'App\Repositories\OrderRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\PaymentRepository',
            'App\Repositories\PaymentRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\ScriptRepository',
            'App\Repositories\ScriptRepositoryEloquent'
        );

        $this->app->bind(
            'App\Contracts\Repositories\SettingRepository',
            'App\Repositories\SettingRepositoryEloquent'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_DEBUG') == true) {
            $this->app['db']->enableQueryLog();
        }

        /**
         * Observers
         */
        ChatMessage::observe(ChatMessageObserver::class);
        Banner::observe(BannerObserver::class);
        User::observe(UserObserver::class);
        Webinar::observe(WebinarObserver::class);
        Room::observe(RoomObserver::class);
        Product::observe(ProductObserver::class);

        /**
         * Owner policies
         */
        Bouncer::ownedVia(Product::class, function ($model, $user) {
            $account = app(UserRepository::class)->setModel($user)->getFirstLinkedAccount();
            return $model->account_id === $account->id;
        });

        Bouncer::ownedVia(Banner::class, function ($model, $user) {
            $account = app(UserRepository::class)->setModel($user)->getFirstLinkedAccount();
            return $model->product->account_id === $account->id;
        });
    }
}
