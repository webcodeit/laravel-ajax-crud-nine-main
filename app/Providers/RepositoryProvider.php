<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('explore', \App\Repositories\ExploreRepository::class);
        $this->app->singleton('operation', \App\Repositories\OperationRepository::class);
        $this->app->singleton('offer', \App\Repositories\OfferRepository::class);
        $this->app->singleton('dashboard', \App\Repositories\DashboardRepository::class);
        $this->app->singleton('deals', \App\Repositories\DealsRepository::class);
        $this->app->singleton('deals_tracking', \App\Repositories\DealsTrackingRepository::class);
        $this->app->singleton('user-repo', \App\Repositories\UserRepository::class);
        $this->app->singleton('setting-notification-repo', \App\Repositories\SettingsNotificationsRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
