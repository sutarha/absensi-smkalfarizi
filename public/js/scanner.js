// public/js/scanner.js - Barcode Kotak (QR Code) Scanner Engine & Web Audio API Feedback

let html5QrCode = null;
let currentMode = 'DATANG'; // 'DATANG' atau 'PULANG'
let isScanningPaused = false;
let audioCtx = null;
let currentCameraId = null;
let availableCameras = [];
let isTorchOn = false;

// Inisialisasi Web Audio API untuk Beep Instan tanpa jeda download file
function initAudio() {
    if (!audioCtx) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) {
            audioCtx = new AudioContextClass();
        }
    }
    if (audioCtx && audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
}

function playBeepSound(isSuccess = true) {
    try {
        initAudio();
        if (!audioCtx) return;

        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        if (isSuccess) {
            // High pitch pleasant dual-chime
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1046.5, audioCtx.currentTime); // C6
            osc.frequency.setValueAtTime(1318.5, audioCtx.currentTime + 0.08); // E6
            gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.22);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.22);
        } else {
            // Low error buzz (220 Hz)
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(220, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.35, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.35);
        }
    } catch (e) {
        console.warn('Audio play error:', e);
    }
}

// Ganti Mode Datang / Pulang
function setScannerMode(mode) {
    currentMode = mode;
    const btnDatang = document.getElementById('btnModeDatang');
    const btnPulang = document.getElementById('btnModePulang');
    const badgeMode = document.getElementById('activeModeBadge');

    if (mode === 'DATANG') {
        btnDatang?.classList.add('active');
        btnPulang?.classList.remove('active');
        if (badgeMode) {
            badgeMode.textContent = 'MODE: DATANG (TAP-IN GERBANG)';
            badgeMode.className = 'mode-badge datang';
        }
    } else {
        btnPulang?.classList.add('active');
        btnDatang?.classList.remove('active');
        if (badgeMode) {
            badgeMode.textContent = 'MODE: PULANG (TAP-OUT GERBANG)';
            badgeMode.className = 'mode-badge pulang';
        }
    }
}

// Kirim Barcode / QR Code ke Server
async function processBarcode(barcodeText) {
    if (isScanningPaused) return;
    isScanningPaused = true;

    initAudio();

    const cleanCode = barcodeText.trim();
    if (!cleanCode) {
        isScanningPaused = false;
        return;
    }

    try {
        const formData = new FormData();
        formData.append('barcode', cleanCode);
        formData.append('barcode_code', cleanCode);
        formData.append('mode', currentMode);

        // Pastikan endpoint URL selalu benar terformat (contoh: /piket/scan-ajax)
        // Hindari format '//piket/scan-ajax' (protocol-relative URL)
        let rawBase = (window.BASE_URL || '/').trim();
        // Hapus double slash di awal atau akhir
        let cleanBase = rawBase.replace(/\/+$/, '');
        if (!cleanBase.startsWith('/')) {
            cleanBase = '/' + cleanBase;
        }
        if (cleanBase === '/') {
            cleanBase = '';
        }
        const endpoint = cleanBase + '/piket/scan-ajax';

        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        let res;
        try {
            res = await response.json();
        } catch (jsonErr) {
            console.error('Server non-JSON response:', jsonErr, 'HTTP Status:', response.status);
            throw new Error('Respon server tidak sesuai format (Status ' + response.status + ')');
        }

        if (res.success) {
            playBeepSound(true);

            // Tampilkan pop-up kartu profil siswa hijau selama 1.5 detik
            showStudentPopup(res.siswa, res.message, res.time);

            // Perbarui counter tap jika ada elemen counter di halaman
            updateCounter(currentMode);

            if (typeof window.onPiketScanSuccess === 'function') {
                window.onPiketScanSuccess();
            }
        } else {
            playBeepSound(false);
            showErrorToast(res.message || 'Kartu barcode/QR tidak valid!');
        }
    } catch (err) {
        console.error('Scan processing error:', err);
        playBeepSound(false);
        showErrorToast(err.message || 'Terjadi gangguan jaringan atau server.');
    }

    // Jeda 1.5 detik sebelum scanner aktif kembali untuk siswa berikutnya
    setTimeout(() => {
        hideStudentPopup();
        isScanningPaused = false;
    }, 1500);
}

function updateCounter(mode) {
    const elId = mode === 'DATANG' ? 'cntDatang' : 'cntPulang';
    const el = document.getElementById(elId);
    if (el) {
        const val = parseInt(el.textContent) || 0;
        el.textContent = val + 1;
    }
}

function showStudentPopup(siswa, message, time) {
    const card = document.getElementById('scannedStudentCard');
    if (!card) return;

    document.getElementById('popupStudentName').textContent = siswa.nama_siswa;
    document.getElementById('popupStudentNisn').textContent = 'NISN: ' + siswa.nisn;
    document.getElementById('popupStudentClass').textContent = siswa.nama_kelas + ' (' + siswa.jurusan + ')';
    document.getElementById('popupScanTime').textContent = (currentMode === 'DATANG' ? 'Masuk: ' : 'Pulang: ') + time;
    document.getElementById('popupMessage').textContent = message;

    const avatar = document.getElementById('popupStudentAvatar');
    if (avatar) {
        avatar.textContent = siswa.jenis_kelamin === 'P' ? '👧' : '👦';
    }

    card.classList.remove('hidden');
    card.classList.add('animate-fade-in');
}

function hideStudentPopup() {
    const card = document.getElementById('scannedStudentCard');
    if (card) {
        card.classList.add('hidden');
    }
}

function showErrorToast(msg) {
    const errBox = document.getElementById('scanErrorToast');
    if (!errBox) {
        alert(msg);
        return;
    }
    errBox.textContent = '❌ ' + msg;
    errBox.classList.remove('hidden');
    setTimeout(() => {
        errBox.classList.add('hidden');
    }, 2500);
}

// Inisialisasi Kamera HTML5-QRCode
async function initCamera() {
    const scannerElement = document.getElementById('reader');
    if (!scannerElement) return;

    if (typeof Html5Qrcode === 'undefined') {
        showErrorToast('Library pemindai kamera belum selesai dimuat.');
        return;
    }

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode('reader');
    }

    // Jika kamera sedang aktif, hentikan terlebih dahulu sebelum ganti kamera
    if (html5QrCode.isScanning) {
        try {
            await html5QrCode.stop();
        } catch (e) {
            console.warn('Stop error:', e);
        }
    }

    try {
        const cameras = await Html5Qrcode.getCameras();
        availableCameras = cameras;

        const select = document.getElementById('cameraSelect');
        if (select) {
            select.innerHTML = '';
            cameras.forEach((cam, idx) => {
                const opt = document.createElement('option');
                opt.value = cam.id;
                opt.textContent = cam.label || `Kamera ${idx + 1}`;
                select.appendChild(opt);
            });
        }

        if (cameras && cameras.length > 0) {
            // Prioritaskan kamera belakang jika belum ada kamera terpilih
            if (!currentCameraId) {
                currentCameraId = cameras[0].id;
                for (const c of cameras) {
                    const label = (c.label || '').toLowerCase();
                    if (label.includes('back') || label.includes('belakang') || label.includes('environment')) {
                        currentCameraId = c.id;
                        break;
                    }
                }
            }

            if (select) {
                select.value = currentCameraId;
            }

            startScannerWithCamera(currentCameraId);
        } else {
            showErrorToast('Tidak ada kamera yang ditemukan pada perangkat.');
        }
    } catch (err) {
        console.error('Error fetching cameras:', err);
        showErrorToast('Izin akses kamera ditolak atau kamera tidak ditemukan.');
    }
}

function startScannerWithCamera(cameraId) {
    currentCameraId = cameraId;

    // Konfigurasi scan optimal untuk QR Code (Barcode Kotak)
    const qrboxFunction = function(viewfinderWidth, viewfinderHeight) {
        const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
        const qrboxSize = Math.floor(minEdge * 0.75);
        return {
            width: qrboxSize,
            height: qrboxSize
        };
    };

    const config = {
        fps: 20,
        qrbox: qrboxFunction,
        aspectRatio: 1.0,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.EAN_13
        ],
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true
        }
    };

    html5QrCode.start(
        cameraId,
        config,
        (decodedText) => {
            processBarcode(decodedText);
        },
        (errorMessage) => {
            // Abaikan kegagalan scan per frame individual
        }
    ).then(() => {
        const statusEl = document.getElementById('cameraStatusText');
        if (statusEl) {
            statusEl.textContent = '🟢 Kamera Aktif (Arahkan Barcode Kotak / QR ke dalam bingkai)';
        }
    }).catch(err => {
        console.error('Start error:', err);
        showErrorToast('Kamera tidak dapat dimulai: ' + err);
    });
}

function switchSelectedCamera() {
    const select = document.getElementById('cameraSelect');
    if (select && select.value) {
        startScannerWithCamera(select.value);
    }
}

// Fitur Pemindaian dari File Foto
function scanFromFile(inputElement) {
    if (!inputElement.files || inputElement.files.length === 0) return;
    const file = inputElement.files[0];

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode('reader');
    }

    html5QrCode.scanFile(file, true)
        .then(decodedText => {
            processBarcode(decodedText);
            inputElement.value = '';
        })
        .catch(err => {
            showErrorToast('Barcode kotak tidak terdeteksi pada gambar yang diunggah.');
            inputElement.value = '';
        });
}

// Simulasi Tap Cepat dari panel siswa
function simulateScan(barcodeCode) {
    processBarcode(barcodeCode);
}

// Inisialisasi Event Listener
document.addEventListener('DOMContentLoaded', () => {
    const manualForm = document.getElementById('manualScanForm');
    const manualInput = document.getElementById('manualBarcodeInput');

    if (manualForm && manualInput) {
        manualForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const code = manualInput.value.trim();
            if (code) {
                processBarcode(code);
                manualInput.value = '';
            }
        });
    }

    document.body.addEventListener('click', () => {
        initAudio();
    }, { once: true });
});
