<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('zh');
        Resource::withoutWrapping();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (class_exists('Laravel\Telescope\TelescopeApplicationServiceProvider')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
