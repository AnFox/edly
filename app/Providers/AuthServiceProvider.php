<?php

namespace App\Providers;

use App\Models\Room;
use App\Models\Webinar;
use App\Policies\BannerPolicy;
use App\Policies\RoomPolicy;
use App\Policies\WebinarPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Class AuthServiceProvider
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Room::class => RoomPolicy::class,
        Webinar::class => WebinarPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
