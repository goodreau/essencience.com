<?php

namespace App\Providers;

use App\Services\Modules\ModuleRepository;
use App\Services\Modules\ModuleAutoloader;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRepository::class, fn () => new ModuleRepository());
    }

    public function boot(ModuleRepository $repo): void
    {
        $modules = $repo->discover();
        $auto = (bool) config('modules.auto_discover', true);
        foreach ($modules as $mod) {
            if (!$mod['enabled'] && !$auto) continue;
            // Register PSR-4 autoload from module composer.json if present
            $composer = $mod['path'].'/composer.json';
            if (is_file($composer)) {
                $json = json_decode((string) @file_get_contents($composer), true);
                if (isset($json['autoload']['psr-4']) && is_array($json['autoload']['psr-4'])) {
                    ModuleAutoloader::addPsr4($json['autoload']['psr-4'], $mod['path']);
                }
            }
            foreach ($mod['providers'] as $provider) {
                try {
                    $this->app->register($provider);
                } catch (\Throwable $e) {
                    // swallow registration errors to not break app
                    logger()->warning('Module provider failed: '.$provider.' - '.$e->getMessage());
                }
            }
        }
    }
}
