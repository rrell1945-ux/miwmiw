<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodExtendRequest;
use App\Http\Requests\PeriodRequest;
use App\Http\Requests\PeriodStartRequest;
use App\Models\Mood;
use App\Models\Period;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PeriodService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function __construct(
        protected PeriodService $periods,
        protected ActivityLogService $logs,
    ) {
    }

    /**
     * Return everything needed by the day modal: any recorded period
     * covering the date plus prediction context for that date.
     */
    public function show(Request $request, string $date): JsonResponse
    {
        $user = $request->user();
        $day = CarbonImmutable::parse($date);

        $period = $user->periods()
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day)
            ->first();

        $moods = Mood::query()->orderBy('sort_order')->get();

        return response()->json([
            'date' => $day->format('Y-m-d'),
            'date_label' => $day->isoFormat('dddd, D MMMM YYYY'),
            'period' => $period ? $this->payload($period) : null,
            'options' => [
                'flows' => Period::FLOWS,
                'moods' => $moods,
                'symptoms' => Period::SYMPTOMS,
            ],
        ]);
    }

    /**
     * Create a completed period from an explicit date range (used when
     * backfilling or editing via the period form).
     */
    public function store(PeriodRequest $request): JsonResponse
    {
        $this->authorize('create', Period::class);

        $this->ensureDateNotRecorded(
            $request->user(),
            CarbonImmutable::parse($request->validated('start_date'))
        );

        $period = $this->periods->create($request->user(), $request->validated());

        $this->logs->log(
            $request->user(),
            'period.created',
            "Added a period starting {$period->start_date->format('Y-m-d')}"
        );

        return response()->json([
            'message' => 'Periode berhasil disimpan',
            'period' => $this->payload($period),
        ]);
    }

    /**
     * Begin a new period from a single date (the day menstruation started).
     */
    public function start(PeriodStartRequest $request): JsonResponse
    {
        $this->authorize('create', Period::class);

        $this->ensureDateNotRecorded(
            $request->user(),
            CarbonImmutable::parse($request->validated('start_date'))
        );

        $period = $this->periods->start(
            $request->user(),
            CarbonImmutable::parse($request->validated('start_date')),
            $request->only(['flow', 'mood', 'symptoms', 'notes'])
        );

        $this->logs->log(
            $request->user(),
            'period.started',
            "Memulai menstruasi pada {$period->start_date->format('Y-m-d')}"
        );

        return response()->json([
            'message' => 'Menstruasi dimulai — jaga diri Anda baik-baik',
            'period' => $this->payload($period),
        ]);
    }

    /**
     * Extend an ongoing period to today (or to a specific backfilled date)
     * and record the daily condition for that check-in day.
     */
    public function extend(PeriodExtendRequest $request, Period $period): JsonResponse
    {
        $this->authorize('extend', $period);

        $until = $request->filled('until')
            ? CarbonImmutable::parse($request->string('until'))
            : null;

        $condition = $request->only(['flow', 'mood', 'symptoms', 'notes']);

        $period = $this->periods->extend($period, $until, $condition);

        $dayLabel = ($until ?? CarbonImmutable::today())->format('Y-m-d');

        $this->logs->log(
            $request->user(),
            'period.extended',
            "Memperpanjang menstruasi sampai {$period->end_date->format('Y-m-d')} (hari ke-{$period->duration})"
        );

        return response()->json([
            'message' => $until
                ? 'Absen '.$dayLabel.' tercatat — hari ke-'.$period->duration
                : 'Absen hari ini tercatat — hari ke-'.$period->duration,
            'period' => $this->payload($period),
        ]);
    }

    /**
     * Mark the ongoing period as finished.
     */
    public function finish(Request $request, Period $period): JsonResponse
    {
        $this->authorize('finish', $period);

        $period = $this->periods->complete($period);

        $this->logs->log(
            $request->user(),
            'period.finished',
            "Menandai menstruasi selesai ({$period->duration} hari)"
        );

        return response()->json([
            'message' => 'Menstruasi berhasil dicatat',
            'duration' => $period->duration,
            'period' => $this->payload($period),
        ]);
    }

    public function update(PeriodRequest $request, Period $period): JsonResponse
    {
        $this->authorize('update', $period);

        $this->ensureDateNotRecorded(
            $request->user(),
            CarbonImmutable::parse($request->validated('start_date')),
            $period->id
        );

        $period = $this->periods->update($period, $request->validated());

        $this->logs->log(
            $request->user(),
            'period.updated',
            "Memperbarui menstruasi mulai {$period->start_date->format('Y-m-d')}"
        );

        return response()->json([
            'message' => 'Periode berhasil diperbarui',
            'period' => $this->payload($period),
        ]);
    }

    public function destroy(Request $request, Period $period): JsonResponse
    {
        $this->authorize('delete', $period);

        $this->periods->delete($period);

        $this->logs->log(
            $request->user(),
            'period.deleted',
            "Deleted period starting {$period->start_date->format('Y-m-d')}"
        );

        return response()->json(['message' => 'Periode berhasil dihapus']);
    }

    /**
     * Reject a date that is already recorded as a menstruation day so the
     * same day can never be entered twice (from start, backfill or edit).
     */
    protected function ensureDateNotRecorded(User $user, CarbonImmutable $start, ?int $excludeId = null): void
    {
        $query = $user->periods()
            ->whereDate('start_date', '<=', $start->format('Y-m-d'))
            ->whereDate('end_date', '>=', $start->format('Y-m-d'));

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            abort(422, 'Tanggal '.$start->format('Y-m-d').' sudah tercatat sebagai hari menstruasi.');
        }
    }

    protected function payload(Period $period): array
    {
        return [
            'id' => $period->id,
            'start_date' => $period->start_date->format('Y-m-d'),
            'end_date' => $period->end_date->format('Y-m-d'),
            'duration' => $period->duration,
            'cycle_length' => $period->cycle_length,
            'status' => $period->status,
            'flow' => $period->flow,
            'mood' => $period->mood,
            'symptoms' => $period->symptoms ?? [],
            'notes' => $period->notes,
        ];
    }
}
