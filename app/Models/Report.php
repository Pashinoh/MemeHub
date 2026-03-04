<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_REJECTED = 'rejected';

    public const REASONS = [
        'spam',
        'nsfw',
        'harassment',
        'hate',
        'misinformation',
        'copyright',
        'other',
    ];

    protected $fillable = [
        'meme_id',
        'user_id',
        'reason',
        'details',
        'status',
        'reviewed_by',
        'reviewed_at',
        'moderator_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function meme()
    {
        return $this->belongsTo(Meme::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
