<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    protected $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'meme_id' => $this->comment->meme_id,
            'meme_title' => $this->comment->meme->title,
            'commenter_name' => $this->comment->user->name,
            'commenter_id' => $this->comment->user_id,
            'content' => $this->comment->content,
        ];
    }
}
