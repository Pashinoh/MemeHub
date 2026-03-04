@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8 sm:py-10">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ __('ui.stats_page_title') }}</h1>
                <p class="text-slate-300">{{ __('ui.stats_page_subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('settings') }}" class="inline-flex items-center rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-slate-700 transition">
                    {{ __('ui.stats_back_settings') }}
                </a>
                <a href="{{ route('users.show', auth()->user()) }}" class="inline-flex items-center rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-600 transition">
                    {{ __('ui.stats_view_public_profile') }}
                </a>
            </div>
        </div>

        <div class="gap-4 mb-6" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));">
            <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('ui.stats_total_posts') }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-100">{{ $totalPosts }}</p>
            </div>
            <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('ui.stats_total_upvotes') }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-100">{{ $totalVotes }}</p>
            </div>
            <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('ui.stats_total_comments') }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-100">{{ $totalComments }}</p>
            </div>
            <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('ui.stats_average_upvotes') }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-100">{{ $averageVotes }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-6 sm:p-7 mb-8">
            <div class="mb-6 space-y-1.5">
                <h2 class="text-xl font-semibold text-slate-100">{{ __('ui.stats_activity_chart_title') }}</h2>
                <p class="text-sm text-slate-400">{{ __('ui.stats_activity_chart_desc') }}</p>
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900 p-3 sm:p-4">
                <div class="h-56 sm:h-64">
                    <canvas id="postingActivityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-5 sm:p-6">
                <div class="mb-4 border-b border-slate-700 pb-3">
                    <h3 class="text-lg font-semibold text-slate-100 mb-1">{{ __('ui.stats_top_posts') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('ui.stats_top_posts_desc') }}</p>
                </div>
                <div class="space-y-3">
                    @forelse ($topMemes as $meme)
                        <a href="{{ route('memes.show', $meme) }}" class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_auto] items-center gap-1 sm:gap-3 rounded-lg border border-slate-700 px-3 py-2.5 hover:bg-slate-700/40 transition">
                            <span class="truncate text-sm font-medium text-slate-100">{{ $meme->title }}</span>
                            <span class="text-xs text-slate-300 sm:text-right">{{ $meme->score }} {{ __('ui.stats_upvotes') }} · {{ $meme->comments_count }} {{ __('ui.stats_comments') }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-slate-400">{{ __('ui.stats_no_posts') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-5 sm:p-6">
                <div class="mb-4 border-b border-slate-700 pb-3">
                    <h3 class="text-lg font-semibold text-slate-100 mb-1">{{ __('ui.stats_recent_posts') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('ui.stats_recent_posts_desc') }}</p>
                </div>
                <div class="space-y-3">
                    @forelse ($recentMemes as $meme)
                        <a href="{{ route('memes.show', $meme) }}" class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_auto] items-center gap-1 sm:gap-3 rounded-lg border border-slate-700 px-3 py-2.5 hover:bg-slate-700/40 transition">
                            <span class="truncate text-sm font-medium text-slate-100">{{ $meme->title }}</span>
                            <span class="text-xs text-slate-400 sm:text-right">{{ $meme->created_at?->diffForHumans() }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-slate-400">{{ __('ui.stats_no_posts') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartElement = document.getElementById('postingActivityChart');
            if (!chartElement || typeof Chart === 'undefined') {
                return;
            }

            const statisticsDataUrl = @json(route('settings.statistics.data'));
            const labels = @json($dailyPosts->pluck('label')->values());
            const cumulativeData = @json($dailyPosts->pluck('cumulative_upvotes')->values());

            const chart = new Chart(chartElement, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: @json(__('ui.stats_chart_label_upvotes')),
                        data: cumulativeData,
                        borderColor: '#94a3b8',
                        backgroundColor: 'rgba(148, 163, 184, 0.15)',
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#94a3b8',
                        borderWidth: 2,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 14,
                                usePointStyle: true,
                                pointStyle: 'line'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#334155'
                            },
                            ticks: {
                                precision: 0,
                                color: '#94a3b8'
                            }
                        },
                        x: {
                            grid: {
                                color: '#334155'
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });

            const refreshChartData = async () => {
                try {
                    const response = await fetch(statisticsDataUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (!Array.isArray(payload.labels) || !Array.isArray(payload.cumulativeUpvotes)) {
                        return;
                    }

                    chart.data.labels = payload.labels;
                    chart.data.datasets[0].data = payload.cumulativeUpvotes;
                    chart.update();
                } catch (error) {
                    console.error('Failed to refresh statistics chart', error);
                }
            };

            const intervalId = setInterval(refreshChartData, 15000);

            window.addEventListener('beforeunload', function () {
                clearInterval(intervalId);
            });
        });
    </script>
</x-app-layout>
