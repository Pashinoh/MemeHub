<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user, Request $request)
    {
        $sort = $request->query('sort', 'new');

        $memes = $user->memes()
            ->with(['tags'])
            ->withCount('comments')
            ->when($sort === 'top', fn ($q) => $q->orderByDesc('score')->orderByDesc('created_at'))
            ->when($sort === 'old', fn ($q) => $q->oldest())
            ->when(! in_array($sort, ['top', 'old', 'new'], true), fn ($q) => $q->latest())
            ->when($sort === 'new', fn ($q) => $q->latest())
            ->paginate(20)
            ->withQueryString();

        return view('users.show', compact('user', 'memes', 'sort'));
    }

    public function follow(User $user)
    {
        auth()->user()->following()->attach($user->id);
        
        $user->notify(new UserFollowedNotification(auth()->user()));
        
        return back();
    }

    public function unfollow(User $user)
    {
        auth()->user()->following()->detach($user->id);
        return back();
    }
}
