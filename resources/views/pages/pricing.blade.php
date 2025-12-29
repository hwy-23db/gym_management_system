<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pricing Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-slate-800 text-white px-6 py-3 flex items-center gap-2 font-semibold">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-white/10 text-xs">M</span>
                    Monthly Subscription Price
                </div>
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col gap-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300">Current monthly subscription price:</p>
                        <p class="text-2xl font-semibold">{{ number_format($monthlyPrice) }} MMK</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <form
                            action="{{ route('pricing.update-monthly') }}"
                            method="POST"
                            class="flex w-full max-w-md overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                        >
                            @csrf
                            @method('PUT')
                            <input
                                type="number"
                                name="monthly_subscription_price"
                                class="w-full border-0 bg-transparent px-4 py-2 text-sm text-gray-900 focus:ring-0 dark:text-gray-100"
                                value="{{ $monthlyPrice }}"
                                min="0"
                                step="0.01"
                            >
                            <span class="flex items-center bg-gray-50 px-3 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">MMK</span>
                            <button
                                type="submit"
                                class="bg-blue-800 px-4 py-2 text-sm font-semibold text-dark hover:bg-slate-700"
                            >
                                Update
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-slate-800 text-white px-6 py-3 flex items-center gap-2 font-semibold">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-white/10 text-xs">T</span>
                    Trainer Session Pricing
                </div>
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">Trainer Name</th>
                                    <th class="px-4 py-2 text-left font-semibold">Price per Session (MMK)</th>
                                    <th class="px-4 py-2 text-left font-semibold whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($trainers as $trainer)
                                    @php
                                        $trainerPricing = $trainerPrices->get($trainer->id);
                                        $trainerPriceValue = $trainerPricing?->price_per_session ?? $defaultTrainerPrice;
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-900/40">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $trainer->name }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                name="price_per_session"
                                                form="trainer-pricing-{{ $trainer->id }}"
                                                class="w-full max-w-[140px] rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                                value="{{ $trainerPriceValue }}"
                                                min="0"
                                                step="0.01"
                                            >
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <form
                                                id="trainer-pricing-{{ $trainer->id }}"
                                                action="{{ route('pricing.update-trainer', $trainer) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PUT')
                                                <button
                                                    type="submit"
                                                    class="rounded-md bg-slate-800 px-4 py-2 text-xs font-semibold text-dark hover:bg-slate-700"
                                                >
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                            No trainers found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        If a trainer has no price set, default is {{ number_format($defaultTrainerPrice) }} MMK.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
