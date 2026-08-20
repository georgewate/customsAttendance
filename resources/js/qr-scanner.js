const SCAN_URL_PATTERN = /\/scan\/([A-Za-z0-9]+)/i;

let overlay = null;
let scanner = null;

function tokenFromText(text) {
    const trimmed = (text || '').trim();
    const match = trimmed.match(SCAN_URL_PATTERN);
    if (match) {
        return match[1];
    }
    if (/^[A-Za-z0-9]{24,64}$/.test(trimmed)) {
        return trimmed;
    }
    return null;
}

function close() {
    if (scanner) {
        scanner.stop()
            .then(() => scanner.clear())
            .catch(() => {});
        scanner = null;
    }
    if (overlay) {
        overlay.remove();
        overlay = null;
    }
}

function setMessage(element, type, message) {
    element.textContent = message;
    element.classList.remove('hidden');
    element.classList.remove('bg-success-50', 'text-success-700', 'dark:bg-success-500/10', 'dark:text-success-300');
    element.classList.remove('bg-danger-50', 'text-danger-700', 'dark:bg-danger-500/10', 'dark:text-danger-300');
    if (type === 'success') {
        element.classList.add('bg-success-50', 'text-success-700', 'dark:bg-success-500/10', 'dark:text-success-300');
    } else {
        element.classList.add('bg-danger-50', 'text-danger-700', 'dark:bg-danger-500/10', 'dark:text-danger-300');
    }
}

async function submitScan(token, button) {
    const scanUrl = button.dataset.scanUrl;
    const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    const response = await fetch(scanUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({ token }),
    });

    return response.json();
}

async function startScan(button) {
    if (overlay) {
        return;
    }

    overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4';
    overlay.innerHTML = `
        <div class="w-full max-w-md rounded-card bg-white p-5 shadow-lift dark:bg-neutral-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Scan QR code</h3>
                <button type="button" data-qr-scanner-close class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div data-qr-scanner-message class="hidden mb-3 rounded-lg px-4 py-3 text-sm"></div>
            <div id="qr-scanner-region" data-qr-scanner-region></div>
            <p class="mt-3 text-center text-xs text-neutral-500 dark:text-neutral-400">Point your camera at the location's QR poster to clock in or out.</p>
        </div>
    `;
    document.body.appendChild(overlay);

    overlay.querySelector('[data-qr-scanner-close]').addEventListener('click', close);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            close();
        }
    });

    const message = overlay.querySelector('[data-qr-scanner-message]');
    const { Html5Qrcode } = await import('html5-qrcode');
    scanner = new Html5Qrcode('qr-scanner-region');

    try {
        await scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 220, height: 220 } },
            async (decodedText) => {
                const token = tokenFromText(decodedText);
                if (!token) {
                    setMessage(message, 'error', 'That is not a valid attendance QR code. Try again.');
                    return;
                }
                try {
                    const result = await submitScan(token, button);
                    setMessage(message, result.status === 'ok' ? 'success' : 'error', result.message || 'Something went wrong. Please try again.');
                } catch (error) {
                    setMessage(message, 'error', 'Could not reach the server. Please try again.');
                }
            },
            () => {},
        );
    } catch (error) {
        setMessage(message, 'error', 'Camera is not available or permission was denied. Please allow camera access and try again.');
    }
}

function initQrScanner() {
    document.querySelectorAll('[data-qr-scanner]').forEach((button) => {
        button.addEventListener('click', () => startScan(button));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQrScanner);
} else {
    initQrScanner();
}