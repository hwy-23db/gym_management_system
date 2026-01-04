<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Check In / Out') }}
        </h2>
    </x-slot>

    @php
        $isCheckedIn = $latestScan && $latestScan->action === 'check_in';
        $statusLabel = $isCheckedIn ? 'Currently Checked In' : 'Currently Checked Out';
        $statusClasses = $isCheckedIn
            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-100'
            : 'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-100';
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Member Attendance</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Scan the member QR code twice per day — once to check in and once to check out.
                            </p>
                        </div>
                        <span
                            id="status-pill"
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}"
                            data-action="{{ $latestScan?->action ?? 'check_out' }}"
                        >
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div
                        id="scan-feedback"
                        class="rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm text-gray-700 dark:text-gray-200"
                    >
                        Ready to scan. Use the camera or paste the QR link below.
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            type="button"
                            class="scan-mode-btn inline-flex items-center justify-center rounded-md border border-transparent px-4 py-2 text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            data-mode="camera"
                        >
                            Scan with Camera
                        </button>
                        <button
                            type="button"
                            class="scan-mode-btn inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700"
                            data-mode="link"
                        >
                            Paste QR Link
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6 text-gray-900 dark:text-gray-100">
                        <div id="camera-panel" class="space-y-4">
                            <div class="space-y-1">
                                <h4 class="text-base font-semibold">Camera Scanner</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Point the camera at the member QR code shown at the front desk.
                                </p>
                            </div>
                            <div id="camera-status" class="text-sm text-gray-600 dark:text-gray-300"></div>
                            <div id="qr-reader" class="qr-reader"></div>
                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    id="start-scan"
                                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                >
                                    Start Camera
                                </button>
                                <button
                                    type="button"
                                    id="flip-camera"
                                    class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    disabled
                                >
                                    Flip Camera
                                </button>
                                <button
                                    type="button"
                                    id="stop-scan"
                                    class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    disabled
                                >
                                    Stop Camera
                                </button>
                            </div>
                        </div>

                        <div id="link-panel" class="hidden space-y-4">
                            <div>
                                <h4 class="text-base font-semibold">Paste the QR Link</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    If the camera is not available, paste the QR link or token to record your scan.
                                </p>
                            </div>
                            <form id="link-form" class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="qr-link-input">
                                    QR Link or Token
                                </label>
                                <input
                                    id="qr-link-input"
                                    type="text"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder="Paste the QR link or token"
                                >
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                >
                                    Record Scan
                                </button>
                            </form>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Example: {{ $userQrUrl }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                        <h4 class="text-base font-semibold">Recent Activity</h4>
                        <ul id="recent-activity" class="space-y-3 text-sm">
                            @forelse ($recentScans as $scan)
                                <li class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2">
                                    <span class="font-medium capitalize">
                                        {{ str_replace('_', ' ', $scan->action) }}
                                    </span>
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ $scan->scanned_at?->format('M d, Y h:i A') }}
                                    </span>
                                </li>
                            @empty
                                <li class="text-gray-500 dark:text-gray-400">
                                    No scans yet today.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        .qr-reader {
            width: 240px;
            height: 240px;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            background-color: #f9fafb;
        }

        .dark .qr-reader {
            border-color: #374151;
            background-color: #111827;
        }

        .qr-reader video,
        .qr-reader img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
    <script>
        const scanButtons = document.querySelectorAll('.scan-mode-btn');
        const cameraPanel = document.getElementById('camera-panel');
        const linkPanel = document.getElementById('link-panel');
        const scanFeedback = document.getElementById('scan-feedback');
        const cameraStatus = document.getElementById('camera-status');
        const startScanButton = document.getElementById('start-scan');
        const flipCameraButton = document.getElementById('flip-camera');
        const stopScanButton = document.getElementById('stop-scan');
        const linkForm = document.getElementById('link-form');
        const linkInput = document.getElementById('qr-link-input');
        const statusPill = document.getElementById('status-pill');
        const recentActivity = document.getElementById('recent-activity');

        const baseScanUrl = '{{ url('/attendance/scan') }}';
        let html5QrCode = null;
        let cameras = [];
        let currentCameraIndex = 0;

        const statusStyles = {
            check_in: {
                label: 'Currently Checked In',
                classes: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-100',
            },
            check_out: {
                label: 'Currently Checked Out',
                classes: 'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-100',
            },
        };

        const messageStyles = {
            info: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200',
            success: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
            error: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200',
        };

        const setFeedback = (message, type = 'info') => {
            scanFeedback.textContent = message;
            scanFeedback.className = `rounded-md border px-4 py-3 text-sm ${messageStyles[type] || messageStyles.info}`;
        };

        const setStatus = (action) => {
            const style = statusStyles[action] || statusStyles.check_out;
            statusPill.textContent = style.label;
            statusPill.className = `inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${style.classes}`;
            statusPill.dataset.action = action;
        };

        const formatTimestamp = (isoString) => {
            if (!isoString) {
                return '';
            }

            const date = new Date(isoString);
            return date.toLocaleString(undefined, {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        const prependActivity = (action, timestamp) => {
            if (!recentActivity) {
                return;
            }

            const listItem = document.createElement('li');
            listItem.className = 'flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2';
            listItem.innerHTML = `
                <span class="font-medium capitalize">${action.replace('_', ' ')}</span>
                <span class="text-gray-500 dark:text-gray-400">${formatTimestamp(timestamp)}</span>
            `;

            const firstChild = recentActivity.firstElementChild;
            if (firstChild && firstChild.textContent.includes('No scans yet')) {
                recentActivity.innerHTML = '';
            }

            recentActivity.prepend(listItem);
        };

        const normalizeToken = (input) => {
            if (!input) {
                return null;
            }

            const trimmed = input.trim();

            if (trimmed.startsWith('http')) {
                try {
                    const url = new URL(trimmed);
                    const type = url.searchParams.get('type');
                    const token = url.searchParams.get('token');
                    if (type && type !== 'user') {
                        return { error: 'This QR link is not for members.' };
                    }
                    return token ? { token } : { error: 'QR link is missing a token.' };
                } catch (error) {
                    return { error: 'Invalid QR link format.' };
                }
            }

            return { token: trimmed };
        };

        const recordScan = async (token) => {
            const scanUrl = new URL(baseScanUrl, window.location.origin);
            scanUrl.searchParams.set('type', 'user');
            scanUrl.searchParams.set('token', token);

            setFeedback('Recording scan...', 'info');

            const response = await fetch(scanUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to record scan.');
            }

            setFeedback(data.message || 'Scan recorded successfully.', 'success');
            setStatus(data.record.action);
            prependActivity(data.record.action, data.record.timestamp);
        };

        const setActivePanel = (mode) => {
            if (mode === 'camera') {
                cameraPanel.classList.remove('hidden');
                linkPanel.classList.add('hidden');
            } else {
                cameraPanel.classList.add('hidden');
                linkPanel.classList.remove('hidden');
            }

            scanButtons.forEach((button) => {
                if (button.dataset.mode === mode) {
                    button.classList.add('bg-gray-900', 'text-white');
                    button.classList.remove('border', 'border-gray-300', 'text-gray-700', 'dark:text-gray-100');
                } else {
                    button.classList.remove('bg-gray-900', 'text-white');
                    button.classList.add('border', 'border-gray-300', 'text-gray-700', 'dark:text-gray-100');
                }
            });
        };

        const stopScanner = async () => {
            if (!html5QrCode || !html5QrCode.isScanning) {
                return;
            }

            await html5QrCode.stop();
            stopScanButton.setAttribute('disabled', 'disabled');
            startScanButton.removeAttribute('disabled');
            flipCameraButton.setAttribute('disabled', 'disabled');
        };

        const updateFlipCameraState = () => {
            if (cameras.length > 1) {
                flipCameraButton.removeAttribute('disabled');
            } else {
                flipCameraButton.setAttribute('disabled', 'disabled');
            }
        };

        const requestCameraPermission = async () => {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            stream.getTracks().forEach((track) => track.stop());
        };

        const startScanner = async () => {
            if (!window.Html5Qrcode) {
                setFeedback('Camera scanning is not supported on this device. Paste the QR link instead.', 'error');
                setActivePanel('link');
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setFeedback('Camera scanning is not supported on this device. Paste the QR link instead.', 'error');
                setActivePanel('link');
                return;
            }

            if (!window.isSecureContext) {
                setFeedback('Camera scanning requires a secure (HTTPS) connection. Paste the QR link instead.', 'error');
                setActivePanel('link');
                return;
            }


            try {
                cameras = await Html5Qrcode.getCameras();
            } catch (error) {
                setFeedback(error.message || 'Unable to access camera devices. Check permissions and try again.', 'error');
                setActivePanel('link');
                return;
            }

            if (!cameras.length) {
                try {
                    await requestCameraPermission();
                    cameras = await Html5Qrcode.getCameras();
                } catch (error) {
                    setFeedback(error.message || 'Unable to access the camera. Allow permission and try again.', 'error');
                    setActivePanel('link');
                    return;
                }
            }

            if (!cameras.length) {
                setFeedback('No camera was detected on this device. Paste the QR link instead.', 'error');
                setActivePanel('link');
                return;
            }

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode('qr-reader');
            }

            if (currentCameraIndex >= cameras.length) {
                currentCameraIndex = 0;
            }

            const cameraConfig = cameras.length
                ? cameras[currentCameraIndex]?.id ?? cameras[0].id
                : { facingMode: 'environment' };

            await html5QrCode.start(
                cameraConfig,
                { fps: 10, qrbox: { width: 220, height: 220 } },
                async (decodedText) => {
                    try {
                        await recordScanFromInput(decodedText);
                        await stopScanner();
                    } catch (error) {
                        setFeedback(error.message, 'error');
                    }
                }
            );

            cameraStatus.textContent = 'Camera is active. Align the QR code inside the frame.';
            startScanButton.setAttribute('disabled', 'disabled');
            stopScanButton.removeAttribute('disabled');
            updateFlipCameraState();
        };

        const recordScanFromInput = async (input) => {
            const normalized = normalizeToken(input);

            if (!normalized) {
                setFeedback('Please provide a QR link or token.', 'error');
                return;
            }

            if (normalized.error) {
                setFeedback(normalized.error, 'error');
                return;
            }

            await recordScan(normalized.token);
        };

        scanButtons.forEach((button) => {
            button.addEventListener('click', () => {
                setActivePanel(button.dataset.mode);
            });
        });

        startScanButton.addEventListener('click', async () => {
            try {
                await startScanner();
            } catch (error) {
                setFeedback(error.message || 'Unable to access the camera.', 'error');
            }
        });

        flipCameraButton.addEventListener('click', async () => {
            if (cameras.length < 2) {
                setFeedback('Only one camera is available on this device.', 'error');
                return;
            }

            try {
                if (html5QrCode && html5QrCode.isScanning) {
                    await stopScanner();
                }

                currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
                await startScanner();
            } catch (error) {
                setFeedback(error.message || 'Unable to switch the camera.', 'error');
            }
        });


        stopScanButton.addEventListener('click', async () => {
            try {
                await stopScanner();
                cameraStatus.textContent = 'Camera stopped.';
            } catch (error) {
                setFeedback(error.message || 'Unable to stop the camera.', 'error');
            }
        });

        linkForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                await recordScanFromInput(linkInput.value);
                linkInput.value = '';
            } catch (error) {
                setFeedback(error.message, 'error');
            }
        });

        window.addEventListener('beforeunload', () => {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop();
            }
        });

        setActivePanel('camera');
    </script>
</x-app-layout>
