<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\OllamaClient;
use App\Services\RagService;
use Illuminate\Support\ServiceProvider;

class OllamaServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container.
     */
    public function register(): void
    {
        // Register OllamaClient as singleton
        $this->app->singleton(OllamaClient::class, function ($app) {
            return new OllamaClient();
        });

        // Register RagService
        $this->app->singleton(RagService::class, function ($app) {
            return new RagService($app->make(OllamaClient::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/ollama.php' => config_path('ollama.php'),
        ], 'ollama-config');
    }
}
