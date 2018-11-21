<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        $this->morphMap();
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

    protected function morphMap()
    {
        Relation::morphMap([
            'notes' => \App\Models\Note::class,
            'posts' => \App\Models\Post::class,
        ]);
    }
}
