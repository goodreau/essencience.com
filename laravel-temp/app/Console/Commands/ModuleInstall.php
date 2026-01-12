<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModuleInstall extends Command
{
    protected $signature = 'module:install {git} {--name=}';
    protected $description = 'Install a module from a Git repository into modules/';

    public function handle(): int
    {
        $git = (string) $this->argument('git');
        $nameOpt = (string) $this->option('name');
        $modulesPath = config('modules.path', base_path('modules'));
        if (!is_dir($modulesPath)) @mkdir($modulesPath, 0775, true);

        $tmp = $modulesPath.'/.tmp_'.uniqid('mod_', true);
        @mkdir($tmp, 0775, true);

        // best-effort clone
        $this->info("Cloning {$git} ...");
        $out = shell_exec(sprintf('git clone --depth 1 %s %s 2>&1', escapeshellarg($git), escapeshellarg($tmp)));
        if (!is_dir($tmp.'/.git')) {
            $this->error('Clone failed. Output: '.($out ?? ''));
            @shell_exec(sprintf('rm -rf %s', escapeshellarg($tmp)));
            return self::FAILURE;
        }

        $composer = $tmp.'/composer.json';
        $name = $nameOpt;
        if (is_file($composer)) {
            $json = json_decode((string) @file_get_contents($composer), true);
            if (isset($json['name'])) $name = $json['name'];
        }
        if (!$name || !str_contains($name, '/')) {
            $this->error('Unable to determine package name. Use --name=vendor/package');
            @shell_exec(sprintf('rm -rf %s', escapeshellarg($tmp)));
            return self::FAILURE;
        }
        [$vendor, $package] = explode('/', $name, 2);
        $dest = $modulesPath.'/'.$vendor.'/'.$package;
        @mkdir(dirname($dest), 0775, true);
        if (is_dir($dest)) {
            $this->error("Destination already exists: {$dest}");
            @shell_exec(sprintf('rm -rf %s', escapeshellarg($tmp)));
            return self::FAILURE;
        }
        rename($tmp, $dest);
        $this->info("Installed to {$dest}");
        $this->info('Enable it with: php artisan module:enable '.$name);
        return self::SUCCESS;
    }
}
