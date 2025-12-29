<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscriptions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Subscription Management</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Track active members, place subscriptions on hold, and resume when they return.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-white">
                                +
                            </span>
                            Add Subscription
                        </button>
                    </div>

                    <div id="subscriptions-message" class="rounded-md bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                        Subscriptions are loading.
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">ID</th>
                                    <th class="px-4 py-2 text-left font-semibold">Username</th>
                                    <th class="px-4 py-2 text-left font-semibold">Type</th>
                                    <th class="px-4 py-2 text-left font-semibold">Details</th>
                                    <th class="px-4 py-2 text-left font-semibold">Price</th>
                                    <th class="px-4 py-2 text-left font-semibold">Activated Date</th>
                                    <th class="px-4 py-2 text-left font-semibold">Expire Date</th>
                                    <th class="px-4 py-2 text-left font-semibold">Status</th>
                                    <th class="px-4 py-2 text-left font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody id="subscriptions-table" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="9" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No subscriptions loaded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const subscriptionMessage = document.getElementById('subscriptions-message');
        const subscriptionsTable = document.getElementById('subscriptions-table');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const setMessage = (message, type = 'info') => {
            const base = 'rounded-md px-4 py-3 text-sm ';
            const styles = {
                info: 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200',
                success: 'bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-100',
                error: 'bg-rose-50 dark:bg-rose-900 text-rose-700 dark:text-rose-100',
            };
            subscriptionMessage.className = base + (styles[type] || styles.info);
            subscriptionMessage.textContent = message;
        };

        const apiFetch = async (url, options = {}) => {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers || {}),
                },
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = data.message || 'Request failed.';
                setMessage(message, 'error');
                throw new Error(message);
            }
            return data;
        };

        const formatDate = (value) => {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return new Intl.DateTimeFormat('en-GB', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
            }).format(date);
        };

        const formatCurrency = (value) => {
            if (value === null || value === undefined) return '-';
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'MMK',
                maximumFractionDigits: 0,
            }).format(value);
        };

        const statusBadge = (status) => {
            const classes = {
                Active: 'bg-emerald-100 text-emerald-700',
                'On Hold': 'bg-amber-100 text-amber-700',
                Expired: 'bg-rose-100 text-rose-700',
            };
            const className = classes[status] || 'bg-gray-100 text-gray-700';
            return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ${className}">${status}</span>`;
        };

        const renderSubscriptions = (subscriptions) => {
            if (!subscriptions.length) {
                subscriptionsTable.innerHTML = '<tr><td colspan="9" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No subscriptions found.</td></tr>';
                return;
            }

            subscriptionsTable.innerHTML = subscriptions.map((subscription) => {
                const details = subscription.duration_days
                    ? `${Math.ceil(subscription.duration_days / 30)} month(s)`
                    : '-';
                const buttonLabel = subscription.is_on_hold ? 'Resume' : 'Hold';
                const buttonClass = subscription.is_on_hold
                    ? 'bg-blue-600 hover:bg-blue-500'
                    : 'bg-amber-600 hover:bg-amber-500';
                const action = subscription.is_on_hold ? 'resume' : 'hold';
                const disabled = subscription.status === 'Expired';

                return `
                    <tr>
                        <td class="px-4 py-3">${subscription.id}</td>
                        <td class="px-4 py-3">${subscription.member_name}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                                ${subscription.plan_name}
                            </span>
                        </td>
                        <td class="px-4 py-3">${details}</td>
                        <td class="px-4 py-3">${formatCurrency(subscription.price)}</td>
                        <td class="px-4 py-3">${formatDate(subscription.start_date)}</td>
                        <td class="px-4 py-3">${formatDate(subscription.end_date)}</td>
                        <td class="px-4 py-3">${statusBadge(subscription.status)}</td>
                        <td class="px-4 py-3">
                            <button
                                type="button"
                                data-id="${subscription.id}"
                                data-action="${action}"
                                class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold text-white ${buttonClass} ${disabled ? 'opacity-50 cursor-not-allowed' : ''}"
                                ${disabled ? 'disabled' : ''}
                            >
                                ${buttonLabel}
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const loadSubscriptions = async () => {
            try {
                setMessage('Loading subscriptions...');
                const data = await apiFetch('/api/subscriptions');
                renderSubscriptions(data.subscriptions || []);
                setMessage('Subscriptions updated.', 'success');
            } catch (error) {
                console.error(error);
            }
        };

        subscriptionsTable.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button || button.disabled) return;

            const subscriptionId = button.dataset.id;
            const action = button.dataset.action;

            try {
                setMessage('Updating subscription...');
                await apiFetch(`/api/subscriptions/${subscriptionId}/${action}`, { method: 'POST' });
                await loadSubscriptions();
            } catch (error) {
                console.error(error);
            }
        });

        loadSubscriptions();
    </script>
</x-app-layout>
