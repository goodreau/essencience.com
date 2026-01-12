<?php

namespace Acme\Hello;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class HelloServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::get('/hello-module', function () {
            return 'Hello from Acme/Hello module!';
        });
    }
}
