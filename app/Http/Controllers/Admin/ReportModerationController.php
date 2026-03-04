<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportModerationController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', Report::STATUS_PENDING);
        $allowedStatuses = [Report::STATUS_PENDING, Report::STATUS_REVIEWED, Report::STATUS_REJECTED];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = Report::STATUS_PENDING;
        }

        $reports = Report::query()
            ->with(['meme', 'user', 'reviewer'])
            ->where('status', $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'activeStatus' => $status,
            'pendingCount' => Report::query()->where('status', Report::STATUS_PENDING)->count(),
            'reviewedCount' => Report::query()->where('status', Report::STATUS_REVIEWED)->count(),
            'rejectedCount' => Report::query()->where('status', Report::STATUS_REJECTED)->count(),
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,rejected'],
            'moderator_note' => ['nullable', 'string', 'max:500'],
        ]);

        $status = (string) $validated['status'];

        $report->status = $status;
        $report->moderator_note = $validated['moderator_note'] ?? null;

        if ($status === Report::STATUS_PENDING) {
            $report->reviewed_by = null;
            $report->reviewed_at = null;
        } else {
            $report->reviewed_by = $request->user()->id;
            $report->reviewed_at = now();
        }

        $report->save();

        return back()->with('status', 'Status laporan berhasil diperbarui.');
    }
}
