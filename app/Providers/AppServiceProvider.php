<?php

namespace App\Providers;

use App\Core\Vkontakte\VkontakteMethodCore;
use App\Facades\WithdrawUser;
use App\Services\WithdrawUserServices;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WithdrawUser::class, WithdrawUserServices::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
