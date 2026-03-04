<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Meme;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request, Meme $meme): RedirectResponse
    {
        if ($meme->user_id === $request->user()->id) {
            return back()->with('status', 'Kamu tidak bisa melaporkan meme milik sendiri.');
        }

        $rateKey = 'report:'.$request->user()->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 12)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->with('status', 'Terlalu banyak laporan. Coba lagi dalam '.$seconds.' detik.');
        }

        RateLimiter::hit($rateKey, 60);

        $validated = $request->validated();

        $report = Report::query()->firstOrCreate([
            'meme_id' => $meme->id,
            'user_id' => $request->user()->id,
        ], [
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => Report::STATUS_PENDING,
        ]);

        if (! $report->wasRecentlyCreated) {
            return back()->with('status', 'Laporan kamu untuk meme ini sudah pernah dikirim.');
        }

        return back()->with('status', 'Terima kasih, laporan berhasil dikirim.');
    }
}
