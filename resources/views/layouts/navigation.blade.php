<nav
    x-data="{
        open: false,
        scrolled: false,
        openSearch: false,
        openNotifications: false
    }"
    x-init="
        window.addEventListener('scroll', () => {
            scrolled = window.scrollY > 10
        });
        document.documentElement.classList.add('dark')
    "
    :class="scrolled
        ? 'bg-slate-950 shadow-lg'
        : 'bg-slate-950 shadow-sm'"
    class="fixed top-0 left-0 w-full z-50 border-b border-slate-800 transition-all duration-300"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- LEFT SIDE -->
            <div class="flex items-center gap-10">
                <!-- Logo & Title -->
                <a href="{{ route('memes.index') }}" class="flex items-center group">
                    <span class="text-lg font-semibold tracking-wide uppercase text-white">MemeHub</span>
                </a>
                <div class="hidden sm:flex items-center gap-2 ml-4 text-sm">
                    @php
                        $navSort = request('sort', 'for_you');
                        $navSort = in_array($navSort, ['for_you', 'fresh', 'trending'], true) ? $navSort : 'for_you';
                    @endphp
                    <a href="{{ route('memes.index', array_filter(['sort' => 'for_you', 'tag' => request('tag'), 'q' => request('q')])) }}" class="rounded-full border px-3 py-1 transition {{ $navSort === 'for_you' ? 'border-slate-500 bg-slate-200 text-slate-900' : 'border-slate-700 bg-slate-800 text-slate-200 hover:border-slate-500' }}">For You</a>
                    <a href="{{ route('memes.index', array_filter(['sort' => 'fresh', 'tag' => request('tag'), 'q' => request('q')])) }}" class="rounded-full border px-3 py-1 transition {{ $navSort === 'fresh' ? 'border-slate-500 bg-slate-200 text-slate-900' : 'border-slate-700 bg-slate-800 text-slate-200 hover:border-slate-500' }}">Fresh</a>
                    <a href="{{ route('memes.index', array_filter(['sort' => 'trending', 'tag' => request('tag'), 'q' => request('q')])) }}" class="rounded-full border px-3 py-1 transition {{ $navSort === 'trending' ? 'border-slate-500 bg-slate-200 text-slate-900' : 'border-slate-700 bg-slate-800 text-slate-200 hover:border-slate-500' }}">Trending</a>
                    <a href="{{ route('memes.index') }}" class="rounded-full border px-3 py-1 transition border-slate-700 bg-slate-800 text-slate-200 hover:border-slate-500">Home</a>
                </div>
            </div>
            <!-- RIGHT SIDE -->
            @auth
                @php
                    $navUnreadCount = auth()->user()->unreadNotifications()->count();
                    $navNotifications = auth()->user()->notifications()->latest()->limit(6)->get();
                    $navAdminEmails = array_map('strtolower', (array) config('services.account.admin_emails', []));
                    $navIsAdmin = in_array(strtolower((string) auth()->user()->email), $navAdminEmails, true);
                @endphp
            @endauth
            <div class="hidden sm:flex items-center gap-3">
                <div class="relative" @click.outside="openSearch = false">
                    <button
                        type="button"
                        @click="openSearch = !openSearch"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-slate-300 transition hover:border-slate-500 hover:text-white"
                        aria-label="Search memes"
                        :aria-expanded="openSearch.toString()"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                        </svg>
                    </button>

                    <div
                        x-show="openSearch"
                        x-transition.origin.top.right
                        class="absolute right-0 top-full mt-2 rounded-xl border border-slate-700 bg-slate-900 p-3 shadow-xl"
                        style="display: none; width: 540px; max-width: calc(100vw - 2rem);"
                    >
                        <form action="{{ route('memes.index') }}" method="GET" class="flex w-full items-center gap-2">
                            <input type="hidden" name="sort" value="{{ request('sort', 'for_you') }}">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search meme titles..." class="w-full min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-400 focus:border-slate-500 focus:outline-none focus:ring-0" style="min-width: 360px;">
                            <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-200 transition hover:bg-slate-700" aria-label="Submit search">
                                Search
                            </button>
                        </form>
                    </div>
                </div>
                @auth
                <div class="flex items-center gap-2.5">
                    <div class="relative" @click.outside="openNotifications = false">
                        <button
                            type="button"
                            @click="openNotifications = !openNotifications"
                            class="relative inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-slate-300 transition hover:border-slate-500 hover:text-white"
                            aria-label="Notifications"
                            :aria-expanded="openNotifications.toString()"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.03 2.03 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($navUnreadCount > 0)
                                <span class="absolute -right-1 -top-1 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                                    {{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}
                                </span>
                            @endif
                        </button>

                        <div
                            x-show="openNotifications"
                            x-transition.origin.top.right
                            class="absolute right-0 top-full z-40 mt-2 w-[360px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-700 bg-slate-900 shadow-xl"
                            style="display: none;"
                        >
                            <div class="flex items-center justify-between border-b border-slate-800 px-3 py-2">
                                <p class="text-sm font-semibold text-slate-100">Notifications</p>
                                @if ($navUnreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-slate-300 transition hover:text-white">Mark all read</button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                @forelse ($navNotifications as $notification)
                                    @php
                                        $data = (array) $notification->data;
                                        $isUnread = is_null($notification->read_at);
                                        $message = 'You have a new notification.';

                                        if (str_contains($notification->type, 'MemeUpvotedNotification')) {
                                            $message = ($data['upvoter_name'] ?? 'Someone') . ' upvoted your meme.';
                                        } elseif (str_contains($notification->type, 'NewCommentNotification')) {
                                            $message = ($data['commenter_name'] ?? 'Someone') . ' commented on your meme.';
                                        } elseif (str_contains($notification->type, 'UserFollowedNotification')) {
                                            $message = ($data['follower_name'] ?? 'Someone') . ' started following you.';
                                        }
                                    @endphp
                                    <form method="POST" action="{{ route('notifications.open', $notification->id) }}" class="border-b border-slate-800 last:border-b-0">
                                        @csrf
                                        <button type="submit" class="flex w-full items-start justify-between gap-2 px-3 py-2 text-left transition hover:bg-slate-800/70 {{ $isUnread ? 'bg-slate-800/40' : '' }}">
                                            <div class="min-w-0">
                                                <p class="line-clamp-2 text-sm text-slate-100">{{ $message }}</p>
                                                <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if ($isUnread)
                                                <span class="mt-1 inline-block h-2.5 w-2.5 shrink-0 rounded-full bg-sky-400"></span>
                                            @endif
                                        </button>
                                    </form>
                                @empty
                                    <p class="px-3 py-8 text-center text-sm text-slate-400">No notifications yet.</p>
                                @endforelse
                            </div>

                            <a href="{{ route('notifications.index') }}" class="block border-t border-slate-800 px-3 py-2 text-center text-sm text-slate-200 transition hover:bg-slate-800">
                                View all notifications
                            </a>
                        </div>
                    </div>
                    <!-- Upload Button -->
                    <a href="{{ route('memes.index', ['upload' => 1]) }}"
                               class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm font-medium hover:bg-slate-600 transition shadow"
                       title="Upload meme">
                        + Upload
                    </a>
                    @if ($navIsAdmin)
                        <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-slate-100 text-sm hover:bg-slate-700 transition" title="{{ __('ui.nav_moderation') }}">
                            {{ __('ui.nav_moderation') }}
                        </a>
                    @endif
                        <a href="{{ route('users.show', Auth::user()) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 transition" title="Profile">
                        <img
                            src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(Auth::user()->email))) . '?s=80&d=mp' }}"
                            alt="{{ Auth::user()->name }}"
                            class="h-7 w-7 rounded-full object-cover bg-slate-700"
                        >
                        <span class="text-sm text-slate-100">{{ Auth::user()->name }}</span>
                    </a>
                </div>
                @else
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-sm text-slate-200 hover:text-slate-100">Login</a>
                    <a href="{{ route('auth.google.redirect') }}" class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm hover:bg-slate-600 transition">Daftar</a>
                </div>
                @endauth
            </div>
            <!-- MOBILE BUTTON -->
            <div class="sm:hidden">
                <button @click="open = !open" class="p-2 text-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Spacer so content is not covered by navbar -->
<div class="h-16"></div>