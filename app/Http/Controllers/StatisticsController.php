<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(): View
    {
        return view('statistics', [
            'stats' => (new StatisticsService(auth()->user()->cycleUser()))->build(),
        ]);
    }
}
