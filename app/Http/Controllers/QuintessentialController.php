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
        return view('pages.quintessentials.show', compact('quintessential'));
    }
}
