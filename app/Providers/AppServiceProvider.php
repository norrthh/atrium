<?php

namespace App\Providers;

use App\Core\Vkontakte\VkontakteMethodCore;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VkontakteMethodCore::class, function ($app) {
            return new VkontakteMethodCore();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
