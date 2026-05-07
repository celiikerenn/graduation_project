<?php

namespace App\Providers;

use App\Support\Currency;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $code = Currency::normalize(session('currency'));
            $view->with('currencyCode', $code);
            $view->with('currencySymbol', Currency::symbol($code));
        });
    }
}
