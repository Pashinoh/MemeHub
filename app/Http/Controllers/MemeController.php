<?php

namespace App\Http\Controllers;

use App\Models\Meme;
use App\Models\Tag;
use App\Notifications\MemeUpvotedNotification;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use Symfony\Component\Process\Process;

class MemeController extends Controller
{
    public function index(Request $request)
    {
        $sort = (string) $request->query('sort', 'for_you');
        $sort = match ($sort) {
            'new' => 'fresh',
            'top' => 'for_you',
            'old' => 'fresh',
            default => $sort,
        };
        $q = trim((string) $request->query('q', ''));
        $tag = trim((string) $request->query('tag', ''));

        $userId = auth()->id();
        $memes = Meme::query()
            ->with(['user', 'tags'])
            ->withCount('comments')
            ->with(['comments' => fn ($q2) => $q2->with('user')->latest()->limit(3)])
            ->when(auth()->check(), function ($qb) use ($userId) {
                $qb->addSelect([
                    'is_bookmarked' => function ($subquery) use ($userId) {
                        $subquery->selectRaw('1')
                            ->from('bookmarks')
                            ->whereColumn('bookmarks.meme_id', 'memes.id')
                            ->where('bookmarks.user_id', $userId)
                            ->limit(1);
                    },
                    'has_upvoted' => function ($subquery) use ($userId) {
                        $subquery->selectRaw('1')
                            ->from('meme_upvotes')
                            ->whereColumn('meme_upvotes.meme_id', 'memes.id')
                            ->where('meme_upvotes.user_id', $userId)
                            ->limit(1);
                    },
                ]);
            })
            ->when($q !== '', fn ($qb) => $qb->where('title', 'like', "%{$q}%"))
            ->when($tag !== '', function ($qb) use ($tag) {
                $qb->whereHas('tags', fn ($tq) => $tq->where('slug', $tag));
            })
            ->when($sort === 'for_you', fn ($qb) => $qb->orderByDesc('score')->orderByDesc('created_at'))
            ->when($sort === 'trending', function ($qb) {
                $qb->orderByRaw('(COALESCE(`score`, 0) / (HOUR(TIMEDIFF(NOW(), `created_at`)) + 1)) DESC')->orderByDesc('created_at');
            })
            ->when(! in_array($sort, ['for_you', 'fresh', 'trending'], true), fn ($qb) => $qb->latest())
            ->when($sort === 'fresh', fn ($qb) => $qb->latest())
            ->paginate(20)
            ->withQueryString();

        return view('memes.index', compact('memes', 'sort', 'q', 'tag'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'image' => ['required', 'file', 'max:51200', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,image/heic-sequence,image/heif-sequence,video/mp4,video/webm,video/x-m4v,video/quicktime,video/x-quicktime'],
            'tags' => ['nullable', 'string', 'max:200'],
        ], [
            'image.mimetypes' => 'Format media tidak didukung. Gunakan JPG, PNG, WEBP, GIF, HEIC/HEIF, MP4, WebM, M4V, atau MOV.',
        ]);

        $upload = $request->file('image');
        $mimeType = strtolower((string) $upload->getMimeType());
        $extension = strtolower((string) $upload->getClientOriginalExtension());

        if ($this->isMovVideo($mimeType, $extension)) {
            $path = $this->convertMovToMp4($upload);
        } elseif (str_starts_with($mimeType, 'video/')) {
            if ($extension === '') {
                $extension = 'mp4';
            }

            $filename = Str::uuid() . '.' . $extension;
            Storage::disk('public')->putFileAs('memes', $upload, $filename);
            $path = 'memes/' . $filename;
        } elseif ($this->isHeicImage($mimeType, $extension)) {
            $path = $this->convertHeicToJpg($upload);
        } else {
            $image = Image::make($upload);
            $image->resize(1600, 1200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $filename = Str::uuid() . '.jpg';
            $path = 'memes/' . $filename;
            Storage::disk('public')->put($path, $image->encode('jpg', 60));
        }

        $meme = Meme::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'image_path' => $path,
            'user_id' => auth()->id(),
        ]);

        // Attach tags if provided
        $rawTags = trim((string) $request->input('tags', ''));
        if ($rawTags !== '') {
            $names = collect(preg_split('/[;,]+|\n|\r|\s*,\s*/', $rawTags))
                ->filter()
                ->map(fn ($n) => trim((string) $n))
                ->filter()
                ->unique()
                ->take(15);

            if ($names->isNotEmpty()) {
                $tagIds = $names->map(function ($name) {
                    $slug = Str::slug($name);
                    $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $name]);
                    return $tag->id;
                });
                $meme->tags()->sync($tagIds);
            }
        }

        return redirect()->route('memes.index')->with('status', 'Meme uploaded!');
    }

    private function isMovVideo(string $mimeType, string $extension): bool
    {
        return $extension === 'mov' || in_array($mimeType, ['video/quicktime', 'video/x-quicktime'], true);
    }

    private function isHeicImage(string $mimeType, string $extension): bool
    {
        if (in_array($extension, ['heic', 'heif'], true)) {
            return true;
        }

        return in_array($mimeType, ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'], true);
    }

    private function convertMovToMp4($upload): string
    {
        $tempOutput = tempnam(sys_get_temp_dir(), 'meme-mov-');
        if ($tempOutput === false) {
            throw ValidationException::withMessages([
                'image' => 'Gagal menyiapkan file sementara untuk konversi MOV.',
            ]);
        }

        @unlink($tempOutput);
        $tempOutput .= '.mp4';

        try {
            $this->runFfmpeg([
                '-y',
                '-i',
                (string) $upload->getRealPath(),
                '-movflags',
                '+faststart',
                '-c:v',
                'libx264',
                '-pix_fmt',
                'yuv420p',
                '-vf',
                'scale=1280:-2:force_original_aspect_ratio=decrease',
                '-preset',
                'veryfast',
                '-crf',
                '28',
                '-c:a',
                'aac',
                '-b:a',
                '96k',
                $tempOutput,
            ], 'Gagal mengonversi MOV ke MP4. Pastikan FFmpeg terpasang dan file tidak rusak.');

            if (!is_file($tempOutput) || filesize($tempOutput) === 0) {
                throw ValidationException::withMessages([
                    'image' => 'Hasil konversi MOV kosong atau tidak valid.',
                ]);
            }

            $filename = Str::uuid() . '.mp4';
            Storage::disk('public')->putFileAs('memes', new File($tempOutput), $filename);

            return 'memes/' . $filename;
        } finally {
            if (is_file($tempOutput)) {
                @unlink($tempOutput);
            }
        }
    }

    private function convertHeicToJpg($upload): string
    {
        $tempOutput = tempnam(sys_get_temp_dir(), 'meme-heic-');
        if ($tempOutput === false) {
            throw ValidationException::withMessages([
                'image' => 'Gagal menyiapkan file sementara untuk konversi HEIC.',
            ]);
        }

        @unlink($tempOutput);
        $tempOutput .= '.jpg';

        try {
            $this->runFfmpeg([
                '-y',
                '-i',
                (string) $upload->getRealPath(),
                '-frames:v',
                '1',
                '-q:v',
                '2',
                $tempOutput,
            ], 'Gagal mengonversi HEIC/HEIF ke JPG. Pastikan FFmpeg terpasang dan file tidak rusak.');

            $image = Image::make($tempOutput)->orientate();
            $image->resize(1600, 1200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $filename = Str::uuid() . '.jpg';
            $path = 'memes/' . $filename;
            Storage::disk('public')->put($path, $image->encode('jpg', 60));

            return $path;
        } finally {
            if (is_file($tempOutput)) {
                @unlink($tempOutput);
            }
        }
    }

    private function runFfmpeg(array $arguments, string $failureMessage): void
    {
        $binary = (string) config('services.ffmpeg.bin', 'ffmpeg');
        $process = new Process(array_merge([$binary], $arguments));
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            throw ValidationException::withMessages([
                'image' => $failureMessage,
            ]);
        }
    }

    public function upvote(Meme $meme, Request $request)
    {
        $user = $request->user();
        $currentState = DB::table('meme_upvotes')
            ->where('meme_id', $meme->id)
            ->where('user_id', $user->id)
            ->exists();

        $requestedState = $request->input('upvote_state');
        $targetState = is_null($requestedState)
            ? ! $currentState
            : filter_var($requestedState, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (is_null($targetState)) {
            $targetState = ! $currentState;
        }

        DB::transaction(function () use ($meme, $user, $currentState, $targetState) {
            if ($targetState && ! $currentState) {
                $inserted = DB::table('meme_upvotes')->insertOrIgnore([
                    'meme_id' => $meme->id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ((int) $inserted > 0) {
                    DB::table('memes')->where('id', $meme->id)->increment('score');
                    $memeOwner = $meme->user;
                    if ($memeOwner && $memeOwner->id !== $user->id) {
                        $memeOwner->notify(new MemeUpvotedNotification($meme, $user));
                    }
                }
                return;
            }

            if (! $targetState && $currentState) {
                $deleted = DB::table('meme_upvotes')
                    ->where('meme_id', $meme->id)
                    ->where('user_id', $user->id)
                    ->delete();

                if ((int) $deleted > 0) {
                    DB::table('memes')->where('id', $meme->id)->decrement('score', (int) $deleted);
                }
            }
        });

        $meme->refresh();
        $finalState = DB::table('meme_upvotes')
            ->where('meme_id', $meme->id)
            ->where('user_id', $user->id)
            ->exists();
        $statusMessage = $finalState ? 'Upvoted!' : 'Upvote removed!';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $statusMessage,
                'score' => (int) $meme->score,
                'has_upvoted' => $finalState,
            ]);
        }

        return back()->with('status', $statusMessage);
    }

    public function show(Meme $meme)
    {
        $meme->load(['user', 'comments' => fn ($q) => $q->with('user')->latest()]);
        $meme->loadCount('comments');

        $is_bookmarked = false;
        $has_upvoted = false;
        if (auth()->check()) {
            $user = auth()->user();
            $is_bookmarked = $user->bookmarks()->where('meme_id', $meme->id)->exists();
            $has_upvoted = $meme->hasUpvoted($user->id);
        }

        return view('memes.show', compact('meme', 'is_bookmarked', 'has_upvoted'));
    }

    public function destroy(Meme $meme)
    {
        abort_if($meme->user_id !== auth()->id(), 403);

        if ($meme->image_path) {
            Storage::disk('public')->delete($meme->image_path);
        }

        $meme->delete();

        return redirect()->route('memes.index')->with('status', 'Meme deleted');
    }

    public function bookmark(Meme $meme, Request $request)
    {
        auth()->user()->bookmarks()->syncWithoutDetaching([$meme->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'Bookmarked!',
                'is_bookmarked' => true,
            ]);
        }

        return back()->with('status', 'Bookmarked!');
    }

    public function unbookmark(Meme $meme, Request $request)
    {
        auth()->user()->bookmarks()->detach($meme->id);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'Bookmark removed!',
                'is_bookmarked' => false,
            ]);
        }

        return back()->with('status', 'Bookmark removed!');
    }

    public function bookmarks()
    {
        $memes = auth()->user()->bookmarks()
            ->with(['user', 'tags'])
            ->withCount('comments')
            ->latest('bookmarks.created_at')
            ->paginate(20);

        return view('bookmarks.index', compact('memes'));
    }
}
