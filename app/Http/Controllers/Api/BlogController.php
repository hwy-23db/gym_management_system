<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\User;
use App\Notifications\BlogPostPublished;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class BlogController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = BlogPost::query()
            ->published()
            ->orderByDesc('published_at')
            ->get();

        return response()->json([
            'data' => $posts->map(fn (BlogPost $post) => $this->formatPost($post)),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatPost($post),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->normalizePublishFields($this->validatePost($request));
        $validated['slug'] = $this->generateUniqueSlug($validated['title']);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $this->storeCoverImage($request->file('cover_image'));
        }

        $validated = $this->syncPublishDates($validated);

        $post = BlogPost::create($validated);

        if ($post->is_published && $post->published_at && $post->published_at->lessThanOrEqualTo(now())) {
            $this->notifyAudience($post);
        }

        return response()->json([
            'message' => 'Blog post created successfully.',
            'data' => $this->formatPost($post),
        ], 201);
    }


    public function update(Request $request, BlogPost $blog): JsonResponse
    {

        $validated = $this->normalizePublishFields($this->validatePost($request));
        if (! array_key_exists('is_published', $validated)) {
            $validated['is_published'] = $blog->is_published;
        }
        $validated['slug'] = $this->generateUniqueSlug($validated['title'], $blog->id);

        if ($request->hasFile('cover_image')) {
            if ($blog->cover_image_path) {
                Storage::disk('public')->delete($blog->cover_image_path);
            }

            $validated['cover_image_path'] = $this->storeCoverImage($request->file('cover_image'));
        }

        $validated = $this->syncPublishDates($validated, $blog->published_at);

        $wasPublished = $blog->is_published;
        $blog->update($validated);

        if (! $wasPublished && $blog->is_published && $blog->published_at && $blog->published_at->lessThanOrEqualTo(now())) {
            $this->notifyAudience($blog);
        }

        return response()->json([
            'message' => 'Blog post updated successfully.',
            'data' => $this->formatPost($blog->fresh()),
        ]);
    }

    public function destroy(BlogPost $blog): JsonResponse
    {
        if ($blog->cover_image_path) {
            Storage::disk('public')->delete($blog->cover_image_path);
        }

        $blog->delete();

        return response()->json([
            'message' => 'Blog post deleted successfully.',
        ]);
    }


    private function formatPost(BlogPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'content' => $post->content,
            'cover_image_url' => $post->cover_image_path
                ? Storage::disk('public')->url($post->cover_image_path)
                : null,
            'published_at' => $post->published_at?->toIso8601String(),
            'publish_immediately' => ['nullable', 'boolean'],
            'publish_date' => ['nullable', 'date'],
            'updated_at' => $post->updated_at->toIso8601String(),
        ];
    }

        private function normalizePublishFields(array $validated): array
    {
        if (array_key_exists('publish_immediately', $validated) && ! array_key_exists('is_published', $validated)) {
            $validated['is_published'] = (bool) $validated['publish_immediately'];
        }

        if (array_key_exists('publish_date', $validated) && ! array_key_exists('published_at', $validated)) {
            $validated['published_at'] = $validated['publish_date'];
        }

        unset($validated['publish_immediately'], $validated['publish_date']);

        return $validated;
    }

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'timezone_offset' => ['nullable', 'integer'],
        ]);
    }

    private function syncPublishDates(array $validated, ?\Carbon\CarbonInterface $existingPublishedAt = null): array
    {
        $isPublished = (bool) ($validated['is_published'] ?? false);
        $validated['is_published'] = $isPublished;
        $publishedAt = $validated['published_at'] ?? null;
        $timezoneOffset = $validated['timezone_offset'] ?? null;

        if ($publishedAt) {
            $publishedAt = $this->normalizePublishedAt($publishedAt, $timezoneOffset);
            $validated['published_at'] = $publishedAt;
        }

        unset($validated['timezone_offset']);

        if (! $isPublished && $publishedAt) {
            $isPublished = true;
            $validated['is_published'] = true;
        }

        if ($isPublished && ! $publishedAt) {
            $validated['published_at'] = $existingPublishedAt ?? now();
        }

        if (! $isPublished && ! $publishedAt) {
            $validated['published_at'] = null;
        }

        return $validated;
    }

    private function normalizePublishedAt(string $publishedAt, ?int $timezoneOffset): \Carbon\CarbonInterface
    {
        $timezone = config('app.timezone');

        if ($timezoneOffset !== null) {
            return Carbon::createFromFormat('Y-m-d\TH:i', $publishedAt, 'UTC')
                ->addMinutes($timezoneOffset)
                ->setTimezone($timezone);
        }

        return Carbon::parse($publishedAt, $timezone);
    }

    private function generateUniqueSlug(string $title, ?int $existingId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (BlogPost::query()
            ->when($existingId, fn ($query) => $query->where('id', '!=', $existingId))
            ->where('slug', $slug)
            ->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    private function storeCoverImage(\Illuminate\Http\UploadedFile $file): string
    {
        $path = $file->hashName('blogs');
        Storage::disk('public')->makeDirectory('blogs');
        $fullPath = Storage::disk('public')->path($path);
        $manager = new ImageManager(new Driver());

        $manager
            ->read($file->getPathname())
            ->cover(1200, 627)
            ->save($fullPath);

        return $path;
    }

    private function notifyAudience(BlogPost $post): void
    {
        User::query()
            ->whereIn('role', ['trainer', 'user'])
            ->where('notifications_enabled', true)
            ->get()
            ->each(fn (User $user) => $user->notify(new BlogPostPublished($post)));
    }
}
