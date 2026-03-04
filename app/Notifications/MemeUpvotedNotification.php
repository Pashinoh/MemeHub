<?php

namespace App\Notifications;

use App\Models\Meme;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemeUpvotedNotification extends Notification
{
    use Queueable;

    protected $meme;
    protected $upvoter;

    public function __construct(Meme $meme, User $upvoter)
    {
        $this->meme = $meme;
        $this->upvoter = $upvoter;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'meme_id' => $this->meme->id,
            'meme_title' => $this->meme->title,
            'upvoter_name' => $this->upvoter->name,
            'upvoter_id' => $this->upvoter->id,
        ];
    }
}
