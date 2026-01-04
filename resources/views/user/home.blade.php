<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Member Home') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">Administrator Blog Updates</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Stay updated with the latest announcements from the admin panel.
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                @forelse($posts as $post)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 space-y-3 text-gray-900 dark:text-gray-100">
                            @if($post->cover_image_path)
                                <img
                                    src="{{ asset('storage/' . $post->cover_image_path) }}"
                                    alt="{{ $post->title }} cover image"
                                    class="h-40 w-full rounded-lg object-cover"
                                >
                            @endif
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-semibold">{{ $post->title }}</h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}
                                </span>
                            </div>
                            @if($post->summary)
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $post->summary }}</p>
                            @endif
                            <div class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line">
                                {!! nl2br(e($post->content)) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-600 dark:text-gray-300">
                            No blog posts are available yet.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
