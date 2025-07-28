<?php

namespace App\Providers;

use App\Repositories\Interfaces\RuleRepositoryInterface;
use App\Repositories\RuleRepository;
use App\Services\Interfaces\RuleServiceInterface;
use App\Services\RuleService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(RuleRepositoryInterface::class, RuleRepository::class);

        // Service bindings
        $this->app->bind(RuleServiceInterface::class, RuleService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
