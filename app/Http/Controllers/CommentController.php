<?php

namespace App\Http\Controllers;

use App\Models\Meme;
use App\Models\Comment;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Meme $meme, Request $request)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:500'],
        ]);

        $comment = $meme->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        if ($meme->user_id !== auth()->id()) {
            $meme->user->notify(new NewCommentNotification($comment));
        }

        return back()->with('status', 'Comment posted!');
    }

    public function update(Comment $comment, Request $request)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:500'],
        ]);

        $comment->update(['content' => $validated['content']]);

        return back()->with('status', 'Comment updated');
    }

    public function destroy(Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $comment->delete();

        return back()->with('status', 'Comment deleted');
    }
}
