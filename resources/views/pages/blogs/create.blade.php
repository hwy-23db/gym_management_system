<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Blog Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('blogs.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium" for="title">Title</label>
                            <input id="title" name="title" type="text" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                            @error('title')
                                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium" for="summary">Summary</label>
                            <textarea id="summary" name="summary" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('summary') }}</textarea>
                            @error('summary')
                                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium" for="content">Content</label>
                            <textarea id="content" name="content" rows="8" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>{{ old('content') }}</textarea>
                            @error('content')
                                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium" for="cover_image">Cover Image</label>
                            <input id="cover_image" name="cover_image" type="file" class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-200">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Image will be resized to 1200x627.</p>
                            <div class="mt-4 hidden" id="cover-image-preview">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Preview</p>
                                <div class="w-full max-w-3xl aspect-[1200/627] overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                    <img alt="Cover image preview" class="h-full w-full object-cover" id="cover-image-preview-img">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-6">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 dark:border-gray-700 text-emerald-600 shadow-sm focus:ring-emerald-500" {{ old('is_published') ? 'checked' : '' }}>
                                Publish immediately
                            </label>
                            <div class="flex-1">
                                <label class="block text-sm font-medium" for="published_at">Publish Date (optional)</label>
                                <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                @error('published_at')
                                    <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Save Blog Post
                            </button>
                            <a href="{{ route('blogs.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-200">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('cover_image');
            const preview = document.getElementById('cover-image-preview');
            const previewImage = document.getElementById('cover-image-preview-img');

            if (!input || !preview || !previewImage) {
                return;
            }

            input.addEventListener('change', () => {
                const [file] = input.files ?? [];
                if (!file) {
                    preview.classList.add('hidden');
                    previewImage.removeAttribute('src');
                    return;
                }

                previewImage.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            });
        });
    </script>
</x-app-layout>
