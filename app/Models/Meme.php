<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meme extends Model
{
    use HasFactory;

    public function upvotedBy()
    {
        return $this->belongsToMany(User::class, 'meme_upvotes')->withTimestamps();
    }

    public function hasUpvoted($userId)
    {
        return $this->upvotedBy()->where('user_id', $userId)->exists();
    }

    protected $fillable = [
        'title',
        'slug',
        'image_path',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function bookmarkedBy()
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function getTrendingScoreAttribute()
    {
        $hoursSinceCreated = max($this->created_at->diffInHours(now()), 1);
        return $this->score / $hoursSinceCreated;
    }

    public function isVideo(): bool
    {
        return self::isVideoPath($this->image_path);
    }

    public static function isVideoPath(?string $path): bool
    {
        $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'webm', 'mov', 'mkv', 'm4v', 'ogg'], true);
    }
}
