<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ModuleList;
use App\Console\Commands\ModuleEnable;
use App\Console\Commands\ModuleDisable;
use App\Console\Commands\ModuleInstall;

Artisan::command('inspire', function () {
Artisan::resolveCommands([
    ModuleList::class,
    ModuleEnable::class,
    ModuleDisable::class,
    ModuleInstall::class,
]);
    $artisan->resolve(ModuleDisable::class);
    $artisan->resolve(ModuleInstall::class);
});
