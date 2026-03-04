<?php

namespace App\Http\Controllers;

use App\Models\Meme;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, string $notification)
    {
        $user = $request->user();
        $notificationModel = $user->notifications()->whereKey($notification)->firstOrFail();

        if (is_null($notificationModel->read_at)) {
            $notificationModel->markAsRead();
        }

        return redirect($this->resolveRedirectUrl($notificationModel));
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back();
    }

    private function resolveRedirectUrl(DatabaseNotification $notification): string
    {
        $data = (array) $notification->data;

        if (! empty($data['meme_id'])) {
            $meme = Meme::find($data['meme_id']);
            if ($meme) {
                return route('memes.show', $meme);
            }
        }

        if (! empty($data['follower_id'])) {
            return route('users.show', $data['follower_id']);
        }

        return route('memes.index');
    }
}
