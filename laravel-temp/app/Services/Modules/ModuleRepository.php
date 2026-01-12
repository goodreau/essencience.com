<?php

namespace App\Services\Modules;

class ModuleRepository
{
    protected string $modulesPath;

    public function __construct(?string $modulesPath = null)
    {
        $this->modulesPath = $modulesPath ?: config('modules.path', base_path('modules'));
    }

    public function path(): string
    {
        return $this->modulesPath;
    }

    /**
     * Discover modules by scanning modules directory for composer.json
     * @return array<int,array{name:string,path:string,providers:array<int,string>,enabled:bool}>
     */
    public function discover(): array
    {
        $modules = [];
        $enabled = collect(config('modules.enabled', []))->map(fn($n) => strtolower($n))->toArray();
        $base = $this->modulesPath;
        if (!is_dir($base)) return [];
        $vendorDirs = array_filter(glob($base.'/*') ?: [], 'is_dir');
        foreach ($vendorDirs as $vendorDir) {
            foreach (array_filter(glob($vendorDir.'/*') ?: [], 'is_dir') as $pkgDir) {
                $composer = $pkgDir.'/composer.json';
                $name = basename(dirname($pkgDir)).'/'.basename($pkgDir);
                $providers = [];
                if (is_file($composer)) {
                    $json = json_decode((string) @file_get_contents($composer), true);
                    if (isset($json['extra']['laravel']['providers']) && is_array($json['extra']['laravel']['providers'])) {
                        $providers = array_values(array_filter($json['extra']['laravel']['providers']));
                    }
                    if (isset($json['name'])) {
                        $name = $json['name'];
                    }
                }
                $modules[] = [
                    'name' => $name,
                    'path' => $pkgDir,
                    'providers' => $providers,
                    'enabled' => in_array(strtolower($name), $enabled, true),
                ];
            }
        }
        return $modules;
    }
}
