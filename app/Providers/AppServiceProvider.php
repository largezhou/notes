<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
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
        JsonResource::withoutWrapping();

        $this->morphMap();

        $this->app->singleton('XS', function ($app) {
            return new \XS(base_path('xs/notes.ini'));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (
            $this->app->environment() != 'production' &&
            class_exists('Laravel\Telescope\TelescopeApplicationServiceProvider')
        ) {
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
