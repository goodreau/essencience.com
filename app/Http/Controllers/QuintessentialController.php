<?php

namespace App\Http\Controllers;

use App\Models\Quintessential;

class QuintessentialController extends Controller
{
    /**
     * Display a listing of the quintessentials.
     */
    public function index()
    {
        $quintessentials = Quintessential::orderBy('order_by')->get();
        
        return view('pages.quintessentials.index', compact('quintessentials'));
    }

    /**
     * Display the specified quintessential.
     */
    public function show(Quintessential $quintessential)
    {
        // Get previous and next quintessentials for navigation
        $previous = Quintessential::where('number', $quintessential->number - 1)->first();
        $next = Quintessential::where('number', $quintessential->number + 1)->first();
        
        return view('pages.quintessentials.show', compact('quintessential', 'previous', 'next'));
    }
}
