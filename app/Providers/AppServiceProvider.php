<?php

namespace App\Providers;

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
        \App\Models\Project::observe(\App\Observers\ProjectObserver::class);
        \App\Models\ProjectBatch::observe(\App\Observers\ProjectBatchObserver::class);
    }
}
