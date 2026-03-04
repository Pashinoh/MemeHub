<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-slate-100">Notifications</h1>

            @if (auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-100 transition hover:bg-slate-700">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl overflow-hidden">
            @forelse ($notifications as $notification)
                @php
                    $data = (array) $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $message = 'You have a new notification.';

                    if (str_contains($notification->type, 'MemeUpvotedNotification')) {
                        $message = ($data['upvoter_name'] ?? 'Someone') . ' upvoted your meme "' . ($data['meme_title'] ?? 'Untitled') . '".';
                    } elseif (str_contains($notification->type, 'NewCommentNotification')) {
                        $message = ($data['commenter_name'] ?? 'Someone') . ' commented on your meme "' . ($data['meme_title'] ?? 'Untitled') . '".';
                    } elseif (str_contains($notification->type, 'UserFollowedNotification')) {
                        $message = ($data['follower_name'] ?? 'Someone') . ' started following you.';
                    }
                @endphp

                <form method="POST" action="{{ route('notifications.open', $notification->id) }}" class="border-b border-slate-800 last:border-b-0">
                    @csrf
                    <button type="submit" class="w-full px-4 py-4 text-left transition hover:bg-slate-800/70 {{ $isUnread ? 'bg-slate-800/40' : 'bg-transparent' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm text-slate-100 break-words">{{ $message }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            @if ($isUnread)
                                <span class="mt-1 inline-block h-2.5 w-2.5 shrink-0 rounded-full bg-sky-400"></span>
                            @endif
                        </div>
                    </button>
                </form>
            @empty
                <div class="px-4 py-10 text-center text-sm text-slate-400">
                    No notifications yet.
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="mt-5">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
