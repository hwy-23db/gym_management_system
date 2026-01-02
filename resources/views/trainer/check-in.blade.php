<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Check In / Out') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10 lg:py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="rounded-md bg-gray-700 px-4 py-3 text-white">
                        <h3 class="flex items-center gap-2 text-sm font-semibold">
                            📍 Check-in / Check-out
                        </h3>
                    </div>

                    @php
                        $isCheckedIn = $latestScan && $latestScan->action === 'check_in';
                    @endphp
                    <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                        <div class="space-y-5">
                            <div class="rounded-md px-4 py-3 text-sm font-semibold {{ $isCheckedIn ? 'border border-emerald-100 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300' : 'border border-red-100 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300' }}">
                                <span class="inline-flex items-center gap-2">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-xs text-white {{ $isCheckedIn ? 'bg-emerald-600' : 'bg-red-600' }}">
                                        {{ $isCheckedIn ? '✓' : '×' }}
                                    </span>
                                    {{ $isCheckedIn ? 'Currently Logged In' : 'Currently Logged Out' }}
                                </span>
                            </div>

                            <button
                                type="button",
                                id="trainer-scan-button",
                                class="inline-flex w-full items-center justify-center rounded-md bg-gray-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-600",
                            >
                                📷 Scan QR Code
                            </button>

                            <p id="trainer-scan-status" class="text-sm text-gray-500 dark:text-gray-400"></p>

                            <div
                                id="trainer-scan-panel"
                                class="hidden w-full overflow-hidden rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900"
                            >
                                <div class="relative w-full overflow-hidden rounded-md bg-black">
                                    <video
                                        id="trainer-scan-video"
                                        class="h-56 w-full object-cover sm:h-64 lg:h-72"
                                        playsinline
                                    ></video>
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Align the QR code inside the frame.</span>
                                    <button
                                        type="button"
                                        id="trainer-scan-stop"
                                        class="text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        Stop
                                    </button>
                                </div>
                            </div>


                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input
                                    id="trainer-qr-input"
                                    type="text"
                                    placeholder="Paste trainer QR link"
                                    class="w-full rounded-md border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                >
                                <button
                                    type="button"
                                    id="trainer-qr-submit"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-emerald-600 px-4 py-2 text-sm font-semibold text-emerald-600 shadow-sm transition hover:bg-emerald-50 dark:border-emerald-400 dark:text-emerald-300 dark:hover:bg-emerald-900/40 sm:w-auto"
                                >
                                    Open QR Link
                                </button>
                            </div>
                        </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Recent Activity</h4>
                            <div class="mt-3 space-y-2">
                                @if($recentScans->isEmpty())
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No check-in/out history yet.</p>
                                @else
                                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                        @foreach($recentScans as $scan)
                                            <li class="flex items-center justify-between">
                                                <span>{{ ucwords(str_replace('_', ' ', $scan->action)) }}</span>
                                                <span>{{ $scan->scanned_at->format('M d, Y h:i A') }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
        </div>
    </div>

    <script type="module">
        (function () {
            const scanButton = document.getElementById('trainer-scan-button');
            const scanPanel = document.getElementById('trainer-scan-panel');
            const scanVideo = document.getElementById('trainer-scan-video');
            const scanStop = document.getElementById('trainer-scan-stop');
            const scanStatus = document.getElementById('trainer-scan-status');
            const manualInput = document.getElementById('trainer-qr-input');
            const manualSubmit = document.getElementById('trainer-qr-submit');
            let stream = null;
            let scanTimer = null;
            let qrScanner = null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function setStatus(message, type = 'info') {
                const classes = {
                    info: 'text-gray-500 dark:text-gray-400',
                    success: 'text-emerald-600 dark:text-emerald-400',
                    error: 'text-rose-600 dark:text-rose-400',
                };
                scanStatus.className = `text-sm ${classes[type] || classes.info}`;
                scanStatus.textContent = message;
            }

            function stopScan() {
                if (scanTimer) {
                    clearInterval(scanTimer);
                    scanTimer = null;
                }
                if (qrScanner) {
                    qrScanner.stop();
                    qrScanner.destroy();
                    qrScanner = null;
                }
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                }
                scanVideo.srcObject = null;
                scanPanel.classList.add('hidden');
            }

            async function submitScan(value) {
                if (!value) {
                    setStatus('Invalid QR code detected.', 'error');
                    return;
                }

                let token = null;
                try {
                    const url = new URL(value, window.location.origin);
                    token = url.searchParams.get('token');
                } catch (error) {
                    token = null;
                }

                if (!token) {
                    setStatus('This QR link is missing the token.', 'error');
                    return;
                }

                setStatus('Recording scan...', 'info');

                try {
                    const response = await fetch(`{{ url('/api/trainer/check-in/scan') }}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ token }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        setStatus(data?.message || 'Unable to record the scan.', 'error');
                        return;
                    }

                    setStatus(data?.message || 'Scan recorded successfully.', 'success');
                    window.location.reload();
                } catch (error) {
                    setStatus('Unable to connect to the server. Please try again.', 'error');
                }
            }


            async function startScan() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setStatus('Camera scanning is not supported on this device. Paste the QR link instead.', 'error');
                    return;
                }
                try {
                    setStatus('Starting camera...', 'info');
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                        audio: false,
                    });
                    scanVideo.srcObject = stream;
                    await scanVideo.play();
                    scanPanel.classList.remove('hidden');
                    setStatus('Scanning for QR code...', 'info');

                    if ('BarcodeDetector' in window) {
                        const detector = new BarcodeDetector({ formats: ['qr_code'] });
                        scanTimer = setInterval(async () => {
                            if (!scanVideo || scanVideo.readyState < 2) {
                                return;
                            }
                            const codes = await detector.detect(scanVideo);
                            if (codes.length > 0) {
                                const value = codes[0].rawValue;
                                stopScan();
                                setStatus('QR code detected. Recording...', 'success');
                                await submitScan(value);
                                window.location.href = value;
                            }
                        }, 500);
                        return;
                    }

                    const module = await import('https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.min.js');
                    const QrScanner = module.default;
                    QrScanner.WORKER_PATH = 'https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner-worker.min.js';

                    qrScanner = new QrScanner(
                        scanVideo,
                        async (result) => {
                            stopScan();
                            setStatus('QR code detected. Recording...', 'success');
                            await submitScan(result.data ?? result);
                            window.location.href = result.data ?? result;
                        },
                        { returnDetailedScanResult: true }
                    );

                    await qrScanner.start();
                } catch (error) {
                    stopScan();
                    const message = error && error.name === 'NotAllowedError'
                        ? 'Camera access blocked. Please allow camera permissions and try again.'
                        : 'Unable to access camera. Paste the QR link instead.';
                    setStatus(message, 'error');
                }
            }

            scanButton.addEventListener('click', startScan);
            scanStop.addEventListener('click', () => {
                stopScan();
                setStatus('Scan stopped.', 'info');
            });
            manualSubmit.addEventListener('click', () => {
                const value = manualInput.value.trim();
                if (!value) {
                    setStatus('Paste the trainer QR link first.', 'error');
                    return;
                }
                submitScan(value);
                window.location.href = value;
            });

            window.addEventListener('beforeunload', stopScan);
        })();
    </script>
</x-app-layout>
