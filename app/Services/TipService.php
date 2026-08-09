<?php

namespace App\Services;

use App\Models\Period;

class TipService
{
    /**
     * @return array{phase: string, title: string, body: string}
     */
    public function forPhase(string $phase): array
    {
        return match ($phase) {
            'Menstruation' => [
                'phase' => $phase,
                'title' => 'Istirahat dan beri ruang untuk diri sendiri',
                'body' => 'Tubuh Anda sedang bekerja dengan baik. Tetap hangat, sediakan botol air panas, dan berikan perhatian ekstra untuk diri Anda.',
            ],
            'Fertile Window' => [
                'phase' => $phase,
                'title' => 'Energi meningkat',
                'body' => 'Ini adalah masa subur Anda. Anda mungkin merasa lebih bersemangat dan mudah bersosialisasi — nikmati suasana ini.',
            ],
            'Ovulation' => [
                'phase' => $phase,
                'title' => 'Hari ovulasi — energi berada di puncak',
                'body' => 'Energi dan rasa percaya diri memuncak di sekitar ovulasi. Waktu yang baik untuk beraktivitas, berolahraga, dan merasa nyaman.',
            ],
            'Luteal Phase' => [
                'phase' => $phase,
                'title' => 'Tenang dan cek kondisi tubuh',
                'body' => 'Hormon sedang menyesuaikan diri menjelang siklus berikutnya. Konsumsi makanan hangat, tidur cukup, dan bersikap lembut pada suasana hati.',
            ],
            'Follicular Phase' => [
                'phase' => $phase,
                'title' => 'Awal baru — saatnya bergerak',
                'body' => 'Tubuh Anda sedang bersiap untuk siklus baru. Energi bagus untuk olahraga, rencana, dan memulai sesuatu yang baru.',
            ],
            default => [
                'phase' => $phase,
                'title' => 'Mulai mencatat siklus Anda',
                'body' => 'Catat menstruasi pertama Anda di kalender, dan Bloom akan mulai memahami ritme siklus Anda.',
            ],
        };
    }

    /**
     * @return array<int, array{title: string, body: string}>
     */
    public function cycleFacts(): array
    {
        return [
            ['title' => 'Tetap terhidrasi', 'body' => 'Minum cukup air membantu mengurangi kembung dan sakit kepala. Minumlah air hangat hari ini.'],
            ['title' => 'Magnesium membantu', 'body' => 'Makanan seperti cokelat hitam, kacang-kacangan, dan pisang dapat meredakan kram dan memperbaiki suasana hati.'],
            ['title' => 'Gerakan ringan membantu', 'body' => 'Jalan santai atau peregangan ringan melancarkan aliran darah dan mengurangi rasa tidak nyaman.'],
            ['title' => 'Tidur memulihkan keseimbangan', 'body' => 'Utamakan tidur 7–9 jam — membantu menjaga siklus tetap teratur dan suasana hati stabil.'],
            ['title' => 'Hangat meredakan kram', 'body' => 'Kompres hangat di perut bagian bawah dapat meredakan kram lebih efektif dari yang Anda kira.'],
        ];
    }

    public function randomFact(): array
    {
        $facts = $this->cycleFacts();

        return $facts[array_rand($facts)];
    }
}
