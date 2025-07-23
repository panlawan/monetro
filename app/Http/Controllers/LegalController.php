<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalController extends Controller
{
    /**
     * Show the Terms of Service page.
     */
    public function terms(): View
    {
        return view('legal.terms', [
            'title' => 'Terms of Service',
            'lastUpdated' => now()->format('F d, Y'),
            'companyName' => config('app.name', 'Monetro.io')
        ]);
    }

    /**
     * Show the Privacy Policy page.
     */
    public function privacy(): View
    {
        return view('legal.privacy', [
            'title' => 'Privacy Policy',
            'lastUpdated' => now()->format('F d, Y'),
            'companyName' => config('app.name', 'Monetro.io')
        ]);
    }
}