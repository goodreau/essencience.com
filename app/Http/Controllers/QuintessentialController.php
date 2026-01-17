<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Quintessential;
use Illuminate\View\View;

class QuintessentialController extends Controller
{
    /**
     * Display a listing of the quintessentials.
     */
    public function index(): View
    {
        $quintessentials = Quintessential::orderBy('order_by')->get();
        
        return view('pages.quintessentials.index', compact('quintessentials'));
    }

    /**
     * Display the specified quintessential.
     */
    public function show(Quintessential $quintessential): View
    {
        // Get previous and next quintessentials for navigation
        $previous = Quintessential::where('number', $quintessential->number - 1)->first();
        $next = Quintessential::where('number', $quintessential->number + 1)->first();
        
        return view('pages.quintessentials.show', compact('quintessential', 'previous', 'next'));
    }
}
