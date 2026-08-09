<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestoreBackupRequest;
use App\Services\ActivityLogService;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backups,
        protected ActivityLogService $logs,
    ) {
    }

    public function download(): StreamedResponse
    {
        $user = auth()->user()->cycleUser();

        $this->logs->log($user, 'backup.downloaded', 'Mengunduh cadangan data');

        return response()->streamDownload(function () use ($user) {
            echo json_encode($this->backups->export($user), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'mimiw-backup-' . now()->format('Y-m-d') . '.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function restore(RestoreBackupRequest $request): JsonResponse
    {
        $user = $request->user()->cycleUser();

        try {
            $payload = json_decode($request->file('backup_file')->get(), true, 512, JSON_THROW_ON_ERROR);
            $count = $this->backups->restore($user, $payload);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Cadangan tidak dapat dipulihkan. File mungkin tidak valid.',
            ], 422);
        }

        $this->logs->log($user, 'backup.restored', "Memulihkan cadangan dengan {$count} menstruasi");

        return response()->json([
            'message' => "Cadangan berhasil dipulihkan — {$count} menstruasi dimuat",
        ]);
    }
}
