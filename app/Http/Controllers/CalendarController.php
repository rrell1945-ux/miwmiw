<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\Period;
use App\Services\PredictionService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public const COLOR_MENSTRUATION = '#F43F5E';

    public const COLOR_CHECKIN = '#10B981';

    public const COLOR_FERTILE = '#BFDBFE';

    public const COLOR_OVULATION = '#7C3AED';

    public const COLOR_PREDICTION = '#F9A8D4';

    public function index(): View
    {
        $user = auth()->user()->cycleUser();

        return view('calendar', [
            'canEdit' => ! auth()->user()->isAdmin(),
            'hasData' => $user->periods()->exists(),
            'hasOngoing' => $user->periods()->ongoing()->exists(),
            'flows' => Period::FLOWS,
            'moods' => Mood::query()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Events for FullCalendar.
     *
     * - Menstruation days are painted as a soft rose background.
     * - Each daily check-in ("absen haid") is marked with a green dot so the
     *   user can instantly see which days were confirmed.
     * - The fertile window and ovulation day of the next cycle are shown from
     *   the prediction, and the predicted next period is painted in pink.
     */
    public function events(): JsonResponse
    {
        $user = auth()->user()->cycleUser();
        $events = [];

        $periods = $user->periods()
            ->orderBy('start_date')
            ->with('periodDays')
            ->get(['id', 'start_date', 'end_date', 'status']);

        foreach ($periods as $period) {
            $events[] = [
                'id' => 'period-'.$period->id,
                'title' => '',
                'start' => $period->start_date->format('Y-m-d'),
                'end' => $period->end_date->copy()->addDay()->format('Y-m-d'),
                'allDay' => true,
                'display' => 'background',
                'color' => self::COLOR_MENSTRUATION,
                'extendedProps' => [
                    'type' => 'period',
                    'period_id' => $period->id,
                    'date' => $period->start_date->format('Y-m-d'),
                ],
            ];

            $periodStart = CarbonImmutable::parse($period->start_date);

            foreach ($period->periodDays as $day) {
                $dayNumber = (int) $periodStart->diffInDays(CarbonImmutable::parse($day->day_date)) + 1;

                $events[] = [
                    'id' => 'checkin-'.$day->id,
                    'title' => '',
                    'start' => $day->day_date->format('Y-m-d'),
                    'allDay' => true,
                    'display' => 'list-item',
                    'color' => self::COLOR_CHECKIN,
                    'className' => 'bloom-checkin',
                    'extendedProps' => [
                        'type' => 'checkin',
                        'period_day_id' => $day->id,
                        'period_id' => $period->id,
                        'day' => $dayNumber,
                        'date' => $day->day_date->format('Y-m-d'),
                    ],
                ];
            }
        }

        $prediction = (new PredictionService($user))->build();

        if ($prediction['has_data']) {
            if ($prediction['fertile_start'] && $prediction['fertile_end']) {
                $events[] = [
                    'id' => 'fertile-window',
                    'title' => '',
                    'start' => $prediction['fertile_start'],
                    'end' => CarbonImmutable::parse($prediction['fertile_end'])->addDay()->format('Y-m-d'),
                    'allDay' => true,
                    'display' => 'background',
                    'color' => self::COLOR_FERTILE,
                    'extendedProps' => [
                        'type' => 'fertile',
                    ],
                ];
            }

            if ($prediction['ovulation']) {
                $events[] = [
                    'id' => 'ovulation',
                    'title' => 'Ovulasi',
                    'start' => $prediction['ovulation'],
                    'allDay' => true,
                    'display' => 'list-item',
                    'color' => self::COLOR_OVULATION,
                    'className' => 'bloom-ovulation',
                    'extendedProps' => [
                        'type' => 'ovulation',
                    ],
                ];
            }

            if ($prediction['next_start'] && $prediction['next_end']) {
                $events[] = [
                    'id' => 'prediction-next',
                    'title' => '',
                    'start' => $prediction['next_start'],
                    'end' => CarbonImmutable::parse($prediction['next_end'])->addDay()->format('Y-m-d'),
                    'allDay' => true,
                    'display' => 'background',
                    'color' => self::COLOR_PREDICTION,
                    'extendedProps' => [
                        'type' => 'prediction',
                    ],
                ];
            }
        }

        return response()->json($events);
    }
}
