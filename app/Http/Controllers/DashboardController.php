<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Services\PredictionService;
use App\Services\TipService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PredictionService $prediction, TipService $tips): View
    {
        $user = auth()->user();
        $subject = $user->cycleUser();

        $predictionData = (new PredictionService($subject))->build();
        $phase = $predictionData['phase'];
        $tip = $tips->forPhase($phase);
        $predictionData['phase'] = PredictionService::phaseLabel($phase);

        $lastMood = $subject->periods()
            ->whereNotNull('mood')
            ->latest('start_date')
            ->value('mood');

        $ongoingPeriod = $subject->periods()
            ->ongoing()
            ->latest('start_date')
            ->with(['periodDays'])
            ->first();

        $latestPeriod = $subject->periods()
            ->latest('start_date')
            ->with(['periodDays'])
            ->first();

        $recentPeriods = $subject->periods()
            ->latest('start_date')
            ->take(5)
            ->get();

        $latestAdvice = ! $user->isAdmin()
            ? $user->receivedMessages()
                ->latest('created_at')
                ->with(['sender', 'periodDay'])
                ->first()
            : null;

        return view('dashboard', [
            'greeting' => $this->greeting(),
            'prediction' => $predictionData,
            'tip' => $tip,
            'lastMood' => $lastMood,
            'today' => Date::today()->format('Y-m-d'),
            'ongoingPeriod' => $ongoingPeriod,
            'dayNumber' => $ongoingPeriod ? $this->dayNumber($ongoingPeriod) : null,
            'missedDate' => $ongoingPeriod ? $this->missedDate($ongoingPeriod) : null,
            'checkedInToday' => $ongoingPeriod
                ? $ongoingPeriod->periodDays->contains(
                    fn ($day) => $day->day_date->format('Y-m-d') === Date::today()->format('Y-m-d')
                )
                : false,
            'latestPeriod' => $latestPeriod,
            'recentPeriods' => $recentPeriods,
            'latestAdvice' => $latestAdvice,
            'isAdmin' => $user->isAdmin(),
            'subject' => $subject,
            'flows' => Period::FLOWS,
            'moods' => \App\Models\Mood::query()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * JSON payload used by the PWA layer to fire local notifications.
     */
    public function notifications(): JsonResponse
    {
        $user = auth()->user()->cycleUser();
        $setting = $user->setting();
        $prediction = (new PredictionService($user))->build();

        $items = [];

        if ($setting->period_reminder && $prediction['has_data']) {
            if ($prediction['is_late']) {
                $items[] = [
                    'id' => 'period-late',
                    'title' => 'Menstruasi Anda mungkin terlambat',
                    'body' => $prediction['days_late'].' hari dari perkiraan. Tidak perlu khawatir — siklus setiap orang berbeda.',
                ];
            } elseif ($prediction['days_until_next'] <= 3) {
                $items[] = [
                    'id' => 'period-soon',
                    'title' => 'Menstruasi Anda akan segera datang',
                    'body' => $prediction['days_until_next'] === 0
                        ? 'Menstruasi diperkirakan mulai hari ini.'
                        : 'Diperkirakan dimulai dalam '.$prediction['days_until_next'].' hari.',
                ];
            }
        }

        $ongoing = $user->periods()
            ->ongoing()
            ->latest('start_date')
            ->first();

        if ($setting->cycle_reminder && $ongoing) {
            $left = max(1, now()->startOfDay()->diffInDays($ongoing->end_date));
            $dayNumber = ($ongoing->duration - $left) + 1;
            $items[] = [
                'id' => 'period-active',
                'title' => 'Jaga diri Anda baik-baik',
                'body' => 'Menstruasi Anda di hari ke-'.$dayNumber.' dari '.$ongoing->duration.' hari. Tetap hangat dan terhidrasi.',
            ];
        }

        return response()->json(['items' => $items]);
    }

    protected function dayNumber(Period $period): int
    {
        return max(
            $period->duration,
            (int) CarbonImmutable::parse($period->start_date)->diffInDays(today()) + 1
        );
    }

    /**
     * If the ongoing period has not been extended to yesterday yet, the user
     * missed a daily check-in. Returns the Y-m-d date that needs confirming.
     */
    protected function missedDate(Period $period): ?string
    {
        $yesterday = CarbonImmutable::today()->subDay();
        $end = CarbonImmutable::parse($period->end_date);

        if ($end->lt($yesterday)) {
            return $end->addDay()->format('Y-m-d');
        }

        return null;
    }

    protected function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 11 => 'Selamat Pagi',
            $hour < 15 => 'Selamat Siang',
            $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };
    }
}
