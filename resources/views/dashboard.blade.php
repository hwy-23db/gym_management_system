<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                <div class="flex items-center gap-6 text-sm font-medium text-gray-600 dark:text-gray-300">
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none">
                            <path d="M3 7h18M7 3v4M17 3v4M5 11h14v10H5V11z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        QR Codes
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none">
                            <path d="M16 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm-8 0c1.657 0 3-1.343 3-3S9.657 5 8 5 5 6.343 5 8s1.343 3 3 3zm8 2c-2.762 0-5 2.239-5 5v1h10v-1c0-2.761-2.238-5-5-5zM8 13c-2.762 0-5 2.239-5 5v1h6v-1c0-1.716.716-3.265 1.869-4.356A5.01 5.01 0 0 0 8 13z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Checked-in Users
                    </span>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-50 text-blue-600 mx-auto">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path d="M16 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm-8 0c1.657 0 3-1.343 3-3S9.657 5 8 5 5 6.343 5 8s1.343 3 3 3zm8 2c-2.762 0-5 2.239-5 5v1h10v-1c0-2.761-2.238-5-5-5zM8 13c-2.762 0-5 2.239-5 5v1h6v-1c0-1.716.716-3.265 1.869-4.356A5.01 5.01 0 0 0 8 13z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Total Users</p>
                        <p class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['totalUsers'] }}</p>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-amber-50 text-amber-500 mx-auto">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path d="M3 7h18M7 3v4M17 3v4M5 11h14v10H5V11z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Active</p>
                        <p class="mt-2 text-2xl font-bold text-amber-500">{{ $stats['activeUsers'] }}</p>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-sky-50 text-sky-500 mx-auto">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path d="M4 6h16v12H4V6zm0 0l8 7 8-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Unread</p>
                        <p class="mt-2 text-2xl font-bold text-sky-500">{{ $stats['unreadMessages'] }}</p>
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-emerald-50 text-emerald-500 mx-auto">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path d="M6 7h12M6 11h12M6 15h8M4 4h16v16H4V4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Blogs</p>
                        <p class="mt-2 text-2xl font-bold text-emerald-500">{{ $stats['blogs'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


