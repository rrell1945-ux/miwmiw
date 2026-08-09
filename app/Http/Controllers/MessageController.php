<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\PeriodDay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $counterpart = $user->counterpart();

        $conversation = Message::query()
            ->where(function ($query) use ($user, $counterpart) {
                $query->where('sender_id', $user->id)->where('recipient_id', $counterpart->id);
            })
            ->orWhere(function ($query) use ($user, $counterpart) {
                $query->where('sender_id', $counterpart->id)->where('recipient_id', $user->id);
            })
            ->with(['sender', 'periodDay'])
            ->orderBy('created_at')
            ->get();

        if ($conversation->isNotEmpty()) {
            Message::query()
                ->where('recipient_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        if ($user->isAdmin()) {
            $subject = $user->cycleUser();

            $checkins = PeriodDay::query()
                ->whereHas('period', fn ($query) => $query->where('user_id', $subject->id))
                ->with(['period', 'message'])
                ->orderByDesc('day_date')
                ->paginate(20);

            return view('messages', [
                'isAdmin' => true,
                'subject' => $subject,
                'checkins' => $checkins,
                'messages' => $conversation,
                'counterpart' => $counterpart,
            ]);
        }

        return view('messages', [
            'isAdmin' => false,
            'subject' => null,
            'checkins' => collect(),
            'messages' => $conversation,
            'counterpart' => $counterpart,
        ]);
    }

    /**
     * Send a message. Admin may also attach a recommendation to a specific
     * check-in day; the user sends a general status message to the admin.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'period_day_id' => ['nullable', 'integer', 'exists:period_days,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $subject = $user->cycleUser();

        // Admin recommendation attached to a check-in day (one per day, editable).
        if (! empty($validated['period_day_id'])) {
            abort_unless($user->isAdmin(), 403);

            $day = PeriodDay::with('period')->findOrFail($validated['period_day_id']);

            abort_unless($day->period->user_id === $subject->id, 403);

            $message = Message::updateOrCreate(
                ['period_day_id' => $day->id],
                [
                    'sender_id' => $user->id,
                    'recipient_id' => $subject->id,
                    'body' => $validated['body'],
                    'read_at' => null,
                ]
            );

            return response()->json([
                'message' => 'Rekomendasi terkirim',
                'id' => $message->id,
            ]);
        }

        // General conversation message, either direction.
        $message = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $user->counterpart()->id,
            'body' => $validated['body'],
            'read_at' => null,
        ]);

        return response()->json([
            'message' => 'Pesan terkirim',
            'id' => $message->id,
        ]);
    }
}
