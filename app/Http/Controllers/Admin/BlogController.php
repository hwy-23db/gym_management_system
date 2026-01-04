<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\User;
use App\Notifications\BlogPostPublished;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class BlogController extends Controller
{
    public function index(): View
    {
        return view('pages.blogs.index', [
            'posts' => BlogPost::query()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.blogs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePost($request);
        $validated['slug'] = $this->generateUniqueSlug($validated['title']);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $this->storeCoverImage($request->file('cover_image'));
        }

        $validated = $this->syncPublishDates($validated);

        $post = BlogPost::create($validated);

        if ($post->is_published && $post->published_at && $post->published_at->lessThanOrEqualTo(now())) {
            $this->notifyAudience($post);
        }

        return redirect()
            ->route('blogs.index')
            ->with('status', 'Blog post created successfully.');
    }

    public function edit(BlogPost $blog): View
    {
        return view('pages.blogs.edit', [
            'post' => $blog,
        ]);
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $validated = $this->validatePost($request);
        $validated['slug'] = $this->generateUniqueSlug($validated['title'], $blog->id);

        if ($request->hasFile('cover_image')) {
            if ($blog->cover_image_path) {
                Storage::disk('public')->delete($blog->cover_image_path);
            }

            $validated['cover_image_path'] = $this->storeCoverImage($request->file('cover_image'));        }

        $validated = $this->syncPublishDates($validated, $blog->published_at);

        $wasPublished = $blog->is_published;
        $blog->update($validated);

        if (! $wasPublished && $blog->is_published && $blog->published_at && $blog->published_at->lessThanOrEqualTo(now())) {
            $this->notifyAudience($blog);
        }

        return redirect()
            ->route('blogs.index')
            ->with('status', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        if ($blog->cover_image_path) {
            Storage::disk('public')->delete($blog->cover_image_path);
        }

        $blog->delete();

        return redirect()
            ->route('blogs.index')
            ->with('status', 'Blog post deleted successfully.');
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
        ]);
    }

    private function syncPublishDates(array $validated, ?\Carbon\CarbonInterface $existingPublishedAt = null): array
    {
        $isPublished = (bool) ($validated['is_published'] ?? false);
        $validated['is_published'] = $isPublished;
        $publishedAt = $validated['published_at'] ?? null;

        if (!$isPublished && $publishedAt) {
            $isPublished = true;
            $validated['is_published'] = true;
        }

        if ($isPublished && !$publishedAt) {
            $validated['published_at'] = $existingPublishedAt ?? now();
        }

        if (!$isPublished && !$publishedAt) {
            $validated['published_at'] = null;
        }

        return $validated;
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
