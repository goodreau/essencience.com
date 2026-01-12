<?php

namespace Essencience\CertificateAuthority;

use Essencience\CertificateAuthority\Commands\GenerateCACommand;
use Essencience\CertificateAuthority\Commands\GenerateServerCertCommand;
use Essencience\CertificateAuthority\Commands\VerifyCertCommand;
use Essencience\CertificateAuthority\Services\CertificateService;
use Illuminate\Support\ServiceProvider;

class CertificateAuthorityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/certificate-authority.php',
            'certificate-authority'
        );

        $this->app->singleton(CertificateService::class, function ($app) {
            return new CertificateService();
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
                __DIR__.'/Config/certificate-authority.php' => config_path('certificate-authority.php'),
            ], 'certificate-authority-config');

            // Register commands
            $this->commands([
                GenerateCACommand::class,
                GenerateServerCertCommand::class,
                VerifyCertCommand::class,
            ]);
        }
    }
}
