<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Support\ExcelWriter;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(protected ActivityLogService $logs)
    {
    }

    public function pdf(): Response
    {
        $user = auth()->user()->cycleUser();
        $periods = $user->periods()->orderByDesc('start_date')->get();

        $this->logs->log($user, 'export.pdf', 'Mengekspor riwayat menstruasi sebagai PDF');

        $pdf = Pdf::loadView('exports.pdf', [
            'user' => $user,
            'periods' => $periods,
            'flows' => Period::FLOWS,
            'exportedAt' => now()->format('d F Y, H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('mimiw-period-history-'.now()->format('Y-m-d').'.pdf');
    }

    public function excel(): StreamedResponse
    {
        $user = auth()->user()->cycleUser();
        $periods = $user->periods()->orderByDesc('start_date')->get();

        $this->logs->log($user, 'export.excel', 'Mengekspor riwayat menstruasi sebagai Excel');

        $writer = new ExcelWriter;
        $writer->setHeader([
            'Tanggal Mulai', 'Tanggal Selesai', 'Durasi (hari)', 'Panjang Siklus (hari)', 'Volume', 'Suasana Hati', 'Gejala', 'Catatan',
        ]);

        foreach ($periods as $period) {
            $writer->addRow([
                $period->start_date->format('Y-m-d'),
                $period->end_date->format('Y-m-d'),
                $period->duration,
                $period->cycle_length ?? '',
                $period->flowLabel() ?? '',
                $period->mood ? (\App\Models\Mood::labelFor($period->mood) ?? $period->mood) : '',
                implode(', ', $period->symptomsLabels()),
                $period->notes ?? '',
            ]);
        }

        return response()->streamDownload(function () use ($writer) {
            $temp = tempnam(sys_get_temp_dir(), 'mimiw');
            $writer->toFile($temp);
            readfile($temp);
            @unlink($temp);
        }, 'mimiw-period-history-'.now()->format('Y-m-d').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
