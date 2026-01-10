<?php

namespace Essencience\Passport;

use Essencience\Passport\Commands\IssueUserCertificateCommand;
use Essencience\Passport\Commands\ListUserCertificatesCommand;
use Essencience\Passport\Commands\RevokeUserCertificateCommand;
use Essencience\Passport\Middleware\AuthenticateWithCertificate;
use Essencience\Passport\Services\PassportService;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class PassportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/passport.php',
            'passport'
        );

        $this->app->singleton(PassportService::class, function ($app) {
            return new PassportService(
                $app->make(\Essencience\CertificateAuthority\Services\CertificateService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/Config/passport.php' => config_path('passport.php'),
            ], 'passport-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'passport-migrations');

            // Register commands
            $this->commands([
                IssueUserCertificateCommand::class,
                RevokeUserCertificateCommand::class,
                ListUserCertificatesCommand::class,
            ]);
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('passport', AuthenticateWithCertificate::class);
    }
}
