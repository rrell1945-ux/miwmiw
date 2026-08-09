<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateRemindersRequest;
use App\Http\Requests\UpdateThemeRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(protected ActivityLogService $logs)
    {
    }

    public function index(): View
    {
        $user = auth()->user();

        return view('settings', [
            'user' => $user,
            'setting' => $user->setting(),
            'hasPeriods' => $user->periods()->exists(),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->safe()->only('name'));

        $this->logs->log($user, 'profile.updated', 'Memperbarui nama tampilan');

        return response()->json(['message' => 'Nama berhasil diperbarui']);
    }

    public function updateTheme(UpdateThemeRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->setting()->update(['theme' => $request->validated('theme')]);

        $this->logs->log($user, 'theme.updated', 'Mengubah tema ke '.$request->validated('theme'));

        return response()->json(['message' => 'Tema berhasil diperbarui']);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);

        $this->logs->log($user, 'password.changed', 'Mengubah kata sandi akun');

        return response()->json(['message' => 'Kata sandi berhasil diperbarui']);
    }

    public function updateReminders(UpdateRemindersRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->setting()->update([
            'notifications_enabled' => $request->boolean('notifications_enabled'),
            'drink_water_reminder' => $request->boolean('drink_water_reminder'),
            'period_reminder' => $request->boolean('period_reminder'),
            'cycle_reminder' => $request->boolean('cycle_reminder'),
            'water_interval_minutes' => $data['water_interval_minutes'],
        ]);

        $this->logs->log($user, 'reminders.updated', 'Memperbarui preferensi pengingat');

        return response()->json(['message' => 'Pengaturan pengingat berhasil diperbarui']);
    }
}
