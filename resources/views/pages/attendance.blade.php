<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold">Attendance Center</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Track trainer attendance, scan QR codes, and monitor active gym users.
                        </p>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex flex-wrap gap-6" aria-label="Attendance tabs">
                            <button type="button" data-tab="records" class="attendance-tab border-emerald-500 text-emerald-600 dark:text-emerald-400 whitespace-nowrap border-b-2 py-2 text-sm font-semibold">
                                Attendance Records
                            </button>
                            <button type="button" data-tab="qr-codes" class="attendance-tab border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:border-gray-300 whitespace-nowrap border-b-2 py-2 text-sm font-semibold">
                                QR Codes
                            </button>
                            <button type="button" data-tab="checked-in" class="attendance-tab border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:border-gray-300 whitespace-nowrap border-b-2 py-2 text-sm font-semibold">
                                Checked-in Users
                            </button>
                        </nav>
                    </div>

                    <section id="attendance-records" class="attendance-panel space-y-6">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="attendance-username">Username</label>
                                    <input id="attendance-username" type="text" placeholder="Enter username (optional)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="attendance-start">Start Date</label>
                                    <input id="attendance-start" type="date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="attendance-end">End Date</label>
                                    <input id="attendance-end" type="date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                </div>
                                <div>
                                    <button type="button" id="attendance-filter" class="inline-flex items-center gap-2 px-5 py-2 bg-gray-900 text-dark rounded-md text-sm font-semibold hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                        <span>Filter</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold">Username</th>
                                            <th class="px-4 py-3 text-left font-semibold">Role</th>
                                            <th class="px-4 py-3 text-left font-semibold">Action</th>
                                            <th class="px-4 py-3 text-left font-semibold">Timestamp</th>
                                            <th class="px-4 py-3 text-left font-semibold">Total Check-in Days</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendance-table" class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                Use the filters above to search for attendance records.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section id="attendance-qr-codes" class="attendance-panel hidden space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold">Gym QR Codes</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Share these codes for members and trainers to scan on entry or exit.
                                </p>
                            </div>
                            <button type="button" id="qr-refresh-button" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-dark rounded-md text-sm font-semibold hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                Refresh QR Codes
                            </button>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                                <div>
                                    <h4 class="text-lg font-semibold">Member QR Code</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Members scan this QR to check in and check out each day.
                                    </p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <img
                                        id="member-qr-image"
                                        class="h-40 w-40 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($userQrData) }}"
                                        alt="Member QR code"
                                    >
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 break-all">
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">Scan link:</span>
                                    <span id="member-qr-link">{{ $userQrData }}</span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Scan twice daily (in/out) to record attendance.
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                                <div>
                                    <h4 class="text-lg font-semibold">Trainer QR Code</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Trainers scan this QR for working day tracking and payroll.
                                    </p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <img
                                        id="trainer-qr-image"
                                        class="h-40 w-40 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($trainerQrData) }}"
                                        alt="Trainer QR code"
                                    >
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 break-all">
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">Scan link:</span>
                                    <span id="trainer-qr-link">{{ $trainerQrData }}</span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    A working day is counted when two scans are recorded.
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-6 space-y-4">
                            <div>
                                <h4 class="text-lg font-semibold text-amber-900 dark:text-amber-100">Admin Scan Override</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                   Use this form only if someone cannot scan the QR. Scanning the QR will now record check-ins automatically.
                                </p>
                            </div>
                            <div id="scan-message" class="rounded-md bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                Select a QR type and user, then submit to record a scan.
                            </div>
                            <form id="scan-form" class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="scan-qr-type">QR Type</label>
                                    <select id="scan-qr-type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="user">Member</option>
                                        <option value="trainer">Trainer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="scan-user">User</label>
                                    <select id="scan-user" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" data-role="{{ $user->role }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="inline-flex items-center justify-center w-full px-5 py-2 bg-emerald-600 text-white rounded-md text-sm font-semibold hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                        Record Scan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <section id="attendance-checked-in" class="attendance-panel hidden space-y-6">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                            <div class="flex flex-col gap-2">
                                <h4 class="text-lg font-semibold">Checked-in Users</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Compare active check-ins with the total number of members to spot missing users.
                                </p>
                                <div class="flex flex-wrap gap-3 text-sm text-gray-700 dark:text-gray-200">
                                    <span class="rounded-full bg-white dark:bg-gray-800 px-3 py-1">Total members: <strong id="total-members">{{ $totalMembers }}</strong></span>
                                    <span class="rounded-full bg-white dark:bg-gray-800 px-3 py-1">Active check-ins: <strong id="active-members">0</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold">Username</th>
                                            <th class="px-4 py-3 text-left font-semibold">Role</th>
                                            <th class="px-4 py-3 text-left font-semibold">Last Scan</th>
                                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="checked-in-table" class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                No active check-ins yet.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        const tabs = document.querySelectorAll('.attendance-tab');
        const panels = {
            records: document.getElementById('attendance-records'),
            'qr-codes': document.getElementById('attendance-qr-codes'),
            'checked-in': document.getElementById('attendance-checked-in'),
        };

        const attendanceTable = document.getElementById('attendance-table');
        const usernameInput = document.getElementById('attendance-username');
        const startDateInput = document.getElementById('attendance-start');
        const endDateInput = document.getElementById('attendance-end');
        const filterButton = document.getElementById('attendance-filter');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const attendanceUsers = @json($users);
        const scanForm = document.getElementById('scan-form');
        const scanQrType = document.getElementById('scan-qr-type');
        const scanUser = document.getElementById('scan-user');
        const scanMessage = document.getElementById('scan-message');
        const totalMembers = document.getElementById('total-members');
        const activeMembers = document.getElementById('active-members');
        const checkedInTable = document.getElementById('checked-in-table');
        const rfidScanUrl = '{{ url('/api/attendance/rfid/scan') }}';
        const rfidRegisterUrl = '{{ url('/api/attendance/rfid/register') }}';
        const refreshQrButton = document.getElementById('qr-refresh-button');
        const memberQrImage = document.getElementById('member-qr-image');
        const trainerQrImage = document.getElementById('trainer-qr-image');
        const memberQrLink = document.getElementById('member-qr-link');
        const trainerQrLink = document.getElementById('trainer-qr-link');
        const qrBaseUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=';
        let rfidBuffer = '';
        let rfidBufferTimer = null;
        const formatTimestamp = (isoString) => {
            const date = new Date(isoString);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        const renderAttendance = (records) => {
            if (!records.length) {
                attendanceTable.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            No attendance records found for the selected filters.
                        </td>
                    </tr>
                `;
                return;
            }

            attendanceTable.innerHTML = records.map((record) => `
                <tr>
                    <td class="px-4 py-3">${record.username}</td>
                    <td class="px-4 py-3 capitalize">${record.role}</td>
                    <td class="px-4 py-3">${record.action.replace('_', ' ')}</td>
                    <td class="px-4 py-3">${formatTimestamp(record.timestamp)}</td>
                    <td class="px-4 py-3 font-semibold">${record.total_check_in_days}</td>
                </tr>
            `).join('');
        };

        const renderCheckedIn = (data) => {
            totalMembers.textContent = data.total_members;
            activeMembers.textContent = data.active_count;

            if (!data.active_users.length) {
                checkedInTable.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            No active check-ins yet.
                        </td>
                    </tr>
                `;
                return;
            }

            checkedInTable.innerHTML = data.active_users.map((user) => `
                <tr>
                    <td class="px-4 py-3">${user.username}</td>
                    <td class="px-4 py-3 capitalize">${user.role}</td>
                    <td class="px-4 py-3">${formatTimestamp(user.last_scan)}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-100 px-3 py-1 text-xs font-semibold">
                            ${user.status}
                        </span>
                    </td>
                </tr>
            `).join('');
        };

        const fetchAttendance = async () => {
            const params = new URLSearchParams();
            if (usernameInput.value.trim()) {
                params.set('username', usernameInput.value.trim());
            }
            if (startDateInput.value) {
                params.set('start_date', startDateInput.value);
            }
            if (endDateInput.value) {
                params.set('end_date', endDateInput.value);
            }

            const response = await fetch(`{{ route('attendance.records') }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            renderAttendance(data.records || []);
        };

        const fetchCheckedIn = async () => {
            const response = await fetch(`{{ route('attendance.checked-in') }}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            renderCheckedIn(data);
        };

        const setScanMessage = (message, type = 'info') => {
            const base = 'rounded-md px-4 py-3 text-sm ';
            const styles = {
                info: 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200',
                success: 'bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-100',
                error: 'bg-rose-50 dark:bg-rose-900 text-rose-700 dark:text-rose-100',
            };
            scanMessage.className = base + (styles[type] || styles.info);
            scanMessage.textContent = message;
        };

        const registerRfidCard = async (cardId) => {
            if (!scanUser.value) {
                setScanMessage('Select a user before registering a card.', 'error');
                return;
            }

            const response = await fetch(rfidRegisterUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    user_id: scanUser.value,
                    card_id: String(cardId).trim(),
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                setScanMessage(data.message || 'Unable to register the RFID card.', 'error');
                return;
            }

            setScanMessage(data.message || 'Card registered successfully.', 'success');
        };

        const recordRfidScan = async (cardId) => {
            const normalizedCardId = String(cardId || '').trim();

            if (!normalizedCardId) {
                setScanMessage('Please provide a card ID.', 'error');
                return;
            }

            setScanMessage('Recording scan...', 'info');

            const response = await fetch(rfidScanUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ card_id: normalizedCardId }),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data?.message?.includes('Card not registered')) {
                    const shouldRegister = confirm('Card not registered. Register this card to the selected user?');
                    if (shouldRegister) {
                        await registerRfidCard(normalizedCardId);
                    } else {
                        setScanMessage('Card not registered.', 'error');
                    }
                    return;
                }

                setScanMessage(data.message || 'Unable to record scan.', 'error');
                return;
            }

            setScanMessage(data.message || 'Scan recorded successfully.', 'success');
            fetchAttendance();
            fetchCheckedIn();
        };

        const filterUsersByRole = () => {
            const role = scanQrType.value;
            const options = attendanceUsers.filter((user) => user.role === role);
            if (!options.length) {
                scanUser.innerHTML = '<option value="">No users available</option>';
                scanUser.setAttribute('disabled', 'disabled');
                return;
            }

            scanUser.removeAttribute('disabled');
            scanUser.innerHTML = options.map((user) => `
                <option value="${user.id}" data-role="${user.role}">${user.name} (${user.role})</option>
            `).join('');
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;
                tabs.forEach((item) => {
                    item.classList.remove('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                    item.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-300');
                });

                tab.classList.add('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-300');

                Object.entries(panels).forEach(([key, panel]) => {
                    if (key === target) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });
            });
        });

        filterButton.addEventListener('click', fetchAttendance);

        scanForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (scanUser.hasAttribute('disabled')) {
                setScanMessage('No users available for the selected QR type.', 'error');
                return;
            }

            setScanMessage('Recording scan...', 'info');

            const payload = {
                user_id: scanUser.value,
                qr_type: scanQrType.value,
            };

            const response = await fetch(`{{ route('attendance.scan') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                setScanMessage(data.message || 'Unable to record scan.', 'error');
                return;
            }

            setScanMessage(data.message, 'success');
            fetchAttendance();
            fetchCheckedIn();
        });

        const resetRfidBuffer = () => {
            rfidBuffer = '';
            if (rfidBufferTimer) {
                clearTimeout(rfidBufferTimer);
                rfidBufferTimer = null;
            }
        };

        const shouldCaptureRfidInput = () => {
            const activeElement = document.activeElement;
            if (!activeElement) {
                return true;
            }

            const tagName = activeElement.tagName;
            return !['INPUT', 'TEXTAREA', 'SELECT'].includes(tagName) && !activeElement.isContentEditable;
        };

        const handleRfidScan = async (cardId) => {
            await recordRfidScan(cardId);
        };

        document.addEventListener('keydown', (event) => {
            if (!shouldCaptureRfidInput()) {
                return;
            }

            if (event.key === 'Enter') {
                if (!rfidBuffer) {
                    return;
                }

                const cardId = rfidBuffer;
                resetRfidBuffer();
                handleRfidScan(cardId);
                return;
            }

            if (event.key.length === 1) {
                rfidBuffer += event.key;

                if (rfidBufferTimer) {
                    clearTimeout(rfidBufferTimer);
                }

                rfidBufferTimer = setTimeout(() => {
                    rfidBuffer = '';
                }, 500);
            }
        });

        window.addEventListener('rfid-scan', (event) => {
            const cardId = event.detail?.card_id ?? event.detail;
            if (!cardId) {
                return;
            }

            handleRfidScan(cardId);
        });

        refreshQrButton.addEventListener('click', async () => {
            refreshQrButton.disabled = true;
            refreshQrButton.textContent = 'Refreshing...';

            try {
                const response = await fetch(`{{ route('attendance.qr.refresh') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to refresh QR codes.');
                }

                memberQrLink.textContent = data.user_qr;
                trainerQrLink.textContent = data.trainer_qr;
                memberQrImage.src = `${qrBaseUrl}${encodeURIComponent(data.user_qr)}`;
                trainerQrImage.src = `${qrBaseUrl}${encodeURIComponent(data.trainer_qr)}`;
            } catch (error) {
                setScanMessage(error.message, 'error');
            } finally {
                refreshQrButton.disabled = false;
                refreshQrButton.textContent = 'Refresh QR Codes';
            }
        });


        scanQrType.addEventListener('change', filterUsersByRole);

        filterUsersByRole();
        fetchAttendance();
        fetchCheckedIn();
    </script>
</x-app-layout>
