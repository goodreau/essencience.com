<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class ServicesController
{
    public function __invoke(): View
    {
        return view('pages.services');
    }
}
