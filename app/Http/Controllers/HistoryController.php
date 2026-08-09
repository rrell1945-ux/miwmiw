<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $periods = auth()->user()->cycleUser()->periods()
            ->with(['user', 'periodDays'])
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('history', [
            'periods' => $periods,
            'flows' => Period::FLOWS,
            'canEdit' => ! auth()->user()->isAdmin(),
        ]);
    }
}
