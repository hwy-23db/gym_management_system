<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Trainer Bookings') }}
        </h2>
    </x-slot>

    @php
        $trainerPriceMap = $trainerPrices->mapWithKeys(function ($pricing, $trainerId) {
            return [$trainerId => (float) $pricing->price_per_session];
        });
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Trainer Bookings</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                View and manage trainer booking sessions.
                            </p>
                        </div>
                        <button
                            type="button"
                            id="open-create-booking"
                            class="inline-flex items-center gap-2 rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                        >
                            <span class="text-lg leading-none">+</span>
                            Create Booking
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">ID</th>
                                    <th class="px-4 py-2 text-left font-semibold">User</th>
                                    <th class="px-4 py-2 text-left font-semibold">Trainer</th>
                                    <th class="px-4 py-2 text-left font-semibold">Session Time</th>
                                    <th class="px-4 py-2 text-left font-semibold">Paid Time</th>
                                    <th class="px-4 py-2 text-left font-semibold">Sessions</th>
                                    <th class="px-4 py-2 text-left font-semibold">Total</th>
                                    <th class="px-4 py-2 text-left font-semibold">Status</th>
                                    <th class="px-4 py-2 text-left font-semibold">Paid</th>
                                    <th class="px-4 py-2 text-left font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($bookings as $booking)
                                    <tr class="bg-white dark:bg-gray-900/40">
                                        <td class="px-4 py-3">{{ $booking->id }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $booking->member?->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-4 py-3">{{ $booking->trainer?->name ?? 'Unknown' }}</td>
                                        <td class="px-4 py-3">
                                            {{ optional($booking->session_datetime)->format('Y-m-d H:i:s') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ optional($booking->paid_at)->format('Y-m-d H:i:s') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">{{ $booking->sessions_count }}</td>
                                        <td class="px-4 py-3">{{ number_format($booking->total_price, 2) }} MMK</td>
                                        <td class="px-4 py-3">
                                            @php
                                                $statusClasses = $booking->status === 'confirmed'
                                                    ? 'bg-slate-600 text-dark'
                                                    : ($booking->status === 'pending' ? 'bg-amber-500 text-dark' : 'bg-gray-300 text-gray-900');
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ $statusClasses }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $paidClasses = $booking->paid_status === 'paid'
                                                    ? 'bg-emerald-600 text-white'
                                                    : 'bg-amber-400 text-gray-900';
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ $paidClasses }}">
                                                {{ ucfirst($booking->paid_status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($booking->paid_status !== 'paid')
                                                <form method="POST" action="{{ route('trainer-bookings.mark-paid', $booking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-500"
                                                    >
                                                        Mark Paid
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-300">
                                                    Paid
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                            No trainer bookings found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div
                id="create-booking-modal"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6"
                aria-hidden="true"
            >
                <div class="w-full max-w-xl rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Create Booking</h3>
                        <button
                            type="button"
                            id="close-create-booking"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100"
                        >
                            Close
                        </button>
                    </div>

                    <form
                        action="{{ route('trainer-bookings.store') }}"
                        method="POST"
                        id="trainer-booking-form"
                        class="mt-4 space-y-4"
                    >
                        @csrf

                        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="member_id">
                                    Member
                                </label>
                                <select
                                    id="member_id"
                                    name="member_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="" disabled selected>Select member</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="trainer_id">
                                    Trainer
                                </label>
                                <select
                                    id="trainer_id"
                                    name="trainer_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="" disabled selected>Select trainer</option>
                                    @foreach ($trainers as $trainer)
                                        <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="session_datetime">
                                    Date &amp; Time
                                </label>
                                <input
                                    id="session_datetime"
                                    name="session_datetime"
                                    type="datetime-local"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="duration_minutes">
                                    Duration (minutes)
                                </label>
                                <input
                                    id="duration_minutes"
                                    name="duration_minutes"
                                    type="number"
                                    min="1"
                                    value="60"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="sessions_count">
                                    Sessions
                                </label>
                                <input
                                    id="sessions_count"
                                    name="sessions_count"
                                    type="number"
                                    min="1"
                                    value="1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="price_per_session">
                                    Price per Session (MMK)
                                </label>
                                <input
                                    id="price_per_session"
                                    name="price_per_session"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value="{{ $defaultTrainerPrice }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="status">
                                    Status
                                </label>
                                <select
                                    id="status"
                                    name="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="confirmed">Confirmed</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="paid_status">
                                    Paid Status
                                </label>
                                <select
                                    id="paid_status"
                                    name="paid_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200" for="notes">
                                Notes
                            </label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            ></textarea>
                        </div>

                        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Total: <span id="booking-total" class="font-semibold text-gray-900 dark:text-gray-100"></span>
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    id="cancel-create-booking"
                                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-grey-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-900"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-dark hover:bg-slate-700"
                                >
                                    Save Booking
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const openCreateBooking = document.getElementById('open-create-booking');
        const closeCreateBooking = document.getElementById('close-create-booking');
        const cancelCreateBooking = document.getElementById('cancel-create-booking');
        const createBookingModal = document.getElementById('create-booking-modal');
        const trainerSelect = document.getElementById('trainer_id');
        const priceInput = document.getElementById('price_per_session');
        const sessionsInput = document.getElementById('sessions_count');
        const totalOutput = document.getElementById('booking-total');

        const trainerPrices = @json($trainerPriceMap);
        const defaultPrice = {{ $defaultTrainerPrice }};

        const updatePrice = () => {
            const trainerId = trainerSelect.value;
            const trainerPrice = trainerPrices[trainerId] ?? defaultPrice;
            if (trainerId) {
                priceInput.value = trainerPrice;
            }
            updateTotal();
        };

        const updateTotal = () => {
            const sessionsCount = Number.parseFloat(sessionsInput.value || 0);
            const pricePerSession = Number.parseFloat(priceInput.value || 0);
            const total = sessionsCount * pricePerSession;
            totalOutput.textContent = `${total.toFixed(2)} MMK`;
        };

        const openModal = () => {
            createBookingModal.classList.remove('hidden');
            createBookingModal.classList.add('flex');
            updateTotal();
        };

        const closeModal = () => {
            createBookingModal.classList.add('hidden');
            createBookingModal.classList.remove('flex');
        };

        openCreateBooking?.addEventListener('click', openModal);
        closeCreateBooking?.addEventListener('click', closeModal);
        cancelCreateBooking?.addEventListener('click', closeModal);
        trainerSelect?.addEventListener('change', updatePrice);
        priceInput?.addEventListener('input', updateTotal);
        sessionsInput?.addEventListener('input', updateTotal);

        updateTotal();
    </script>
</x-app-layout>
