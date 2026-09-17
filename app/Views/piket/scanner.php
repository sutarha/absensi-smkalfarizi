<?php
use App\Config\App;
use App\Helpers\BarcodeHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Scanner Barcode Kotak Gerbang - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <style>
        .scan-guide-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 220px;
            border: 2px solid #38bdf8;
            border-radius: 18px;
            box-shadow: 0 0 0 4000px rgba(15, 23, 42, 0.55);
            pointer-events: none;
            z-index: 10;
        }
        .scan-laser-line {
            position: absolute;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, #ffffff, #38bdf8, transparent);
            box-shadow: 0 0 10px #38bdf8;
            animation: scanLine 2s infinite ease-in-out;
        }
        @keyframes scanLine {
            0% { top: 12px; }
            50% { top: 204px; }
            100% { top: 12px; }
        }
        .corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: #38bdf8;
            border-style: solid;
        }
        .tl { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 14px; }
        .tr { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 14px; }
        .bl { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 14px; }
        .br { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 14px; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-900 border-x border-slate-800/80 shadow-2xl flex flex-col pb-16">
        <!-- Top Navbar -->
        <header class="px-5 py-4 bg-slate-900/90 backdrop-blur-md sticky top-0 z-30 border-b border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-brand-500 flex items-center justify-center text-lg shadow-glow-brand">
                    📷
                </div>
                <div>
                    <h1 class="text-sm font-outfit font-bold tracking-tight text-white flex items-center gap-1.5">
                        SCANNER GERBANG
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium">Piket: <?= htmlspecialchars($user['nama_lengkap']) ?></p>
                </div>
            </div>
            <a href="<?= App::baseUrl('logout') ?>" 
               class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-semibold border border-rose-500/20 transition-all duration-200">
                Keluar 🚪
            </a>
        </header>

        <main class="p-5 flex-1 space-y-4">
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs font-medium flex items-center justify-center gap-2 animate-fade-in shadow-soft-sm">
                <span>✅</span>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs font-medium flex items-center justify-center gap-2 animate-fade-in shadow-soft-sm">
                <span>❌</span>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Real-time Gate Sync Pulse Badge -->
            <div class="flex justify-between items-center px-1">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-bold text-emerald-400 tracking-wide uppercase">Gate Sync Realtime</span>
                </div>
                <div class="flex items-center gap-2">
                    <span id="piketSyncClock" class="text-[11px] font-mono text-slate-400">--:--:--</span>
                    <button type="button" onclick="pollPiketLive(true)" 
                            class="text-[10px] px-2 py-0.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-400 border border-slate-700 font-semibold transition"
                            title="Refresh instan">
                        🔄 Sync
                    </button>
                </div>
            </div>

            <!-- Real-time Counters Grid -->
            <div class="grid grid-cols-3 gap-2.5">
                <div class="p-3 rounded-2xl bg-slate-800/80 border border-slate-700/60 text-center shadow-soft-sm">
                    <div class="text-xl font-extrabold font-outfit text-sky-400" id="cntDatang"><?= $totalDatang ?></div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Tap-In Masuk</div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-800/80 border border-slate-700/60 text-center shadow-soft-sm">
                    <div class="text-xl font-extrabold font-outfit text-amber-400" id="cntPulang"><?= $totalPulang ?></div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Tap-Out Pulang</div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-800/80 border border-slate-700/60 text-center shadow-soft-sm">
                    <div class="text-xl font-extrabold font-outfit text-emerald-400" id="cntLengkap"><?= $totalHadirLengkap ?></div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Hadir Lengkap</div>
                </div>
            </div>

            <!-- Mode Switcher Tabs -->
            <div class="p-1 rounded-xl bg-slate-800 border border-slate-700/70 flex gap-1 shadow-inner">
                <button type="button" id="btnModeDatang" onclick="setScannerMode('DATANG')"
                        class="flex-1 py-2.5 px-3 rounded-lg text-xs font-bold transition-all duration-200 text-white bg-brand-600 shadow-soft-sm active">
                    ☀️ DATANG (06.30 - 07.30)
                </button>
                <button type="button" id="btnModePulang" onclick="setScannerMode('PULANG')"
                        class="flex-1 py-2.5 px-3 rounded-lg text-xs font-bold transition-all duration-200 text-slate-400 hover:text-slate-200">
                    🌙 PULANG (14.00 - 16.00)
                </button>
            </div>

            <!-- Active Mode Banner -->
            <div id="activeModeBadge" 
                 class="py-2 px-3 text-center text-xs font-bold rounded-xl bg-brand-500/10 border border-brand-500/30 text-sky-400 tracking-wide">
                MODE AKTIF: DATANG (TAP-IN GERBANG MASUK)
            </div>

            <!-- Error Notification Banner -->
            <div id="scanErrorToast" class="hidden p-3 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs font-medium text-center animate-fade-in"></div>

            <!-- Camera Viewport Box -->
            <div class="relative rounded-2xl overflow-hidden bg-black border-2 border-slate-700 shadow-2xl">
                <div id="reader" class="w-full min-h-[290px] bg-black"></div>

                <!-- Square Scan Guide Frame -->
                <div class="scan-guide-box">
                    <div class="corner tl"></div>
                    <div class="corner tr"></div>
                    <div class="corner bl"></div>
                    <div class="corner br"></div>
                    <div class="scan-laser-line"></div>
                </div>

                <!-- Green Pop-Up Profile Card Modal -->
                <div id="scannedStudentCard" 
                     class="hidden absolute inset-3 rounded-2xl bg-emerald-600/95 backdrop-blur-md text-white flex flex-col items-center justify-center p-6 text-center shadow-2xl z-40 animate-fade-in border border-emerald-400/30">
                    <div id="popupStudentAvatar" class="w-16 h-16 rounded-full bg-white text-emerald-600 text-3xl flex items-center justify-center shadow-soft-md mb-2">
                        👦
                    </div>
                    <h2 id="popupStudentName" class="text-lg font-outfit font-extrabold tracking-tight">Nama Siswa</h2>
                    <div id="popupStudentNisn" class="text-xs text-emerald-100 font-mono mt-0.5">NISN: 0071234501</div>
                    <div id="popupStudentClass" class="mt-2 px-3.5 py-1 rounded-full bg-emerald-700/80 text-xs font-bold text-emerald-100 border border-emerald-500/30">
                        X PPLG 1
                    </div>
                    <div id="popupScanTime" class="mt-3 text-sm font-extrabold text-amber-200">Masuk: 07:15:32</div>
                    <div id="popupMessage" class="text-xs text-emerald-100 mt-1">Tap-In Masuk Berhasil!</div>
                </div>
            </div>

            <!-- Camera Switcher & File Upload -->
            <div class="flex gap-2">
                <select id="cameraSelect" onchange="switchSelectedCamera()"
                        class="flex-1 px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 text-xs focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Mencari Kamera...</option>
                </select>
                <label class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-semibold cursor-pointer flex items-center gap-1.5 transition-all duration-150">
                    <span>📁 Foto QR</span>
                    <input type="file" accept="image/*" class="hidden" onchange="scanFromFile(this)">
                </label>
            </div>

            <!-- Manual Barcode Input -->
            <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/70 shadow-soft-sm">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                    ⌨️ Input Barcode / NISN Manual
                </label>
                <form id="manualScanForm" class="flex gap-2">
                    <input type="text" id="manualBarcodeInput" 
                           class="flex-1 px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 text-xs font-mono placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" 
                           placeholder="Contoh: ALF-0071234501">
                    <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-soft-sm transition-all duration-150">
                        Proses
                    </button>
                </form>
            </div>

            <!-- Live Activity Stream Widget -->
            <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/70 shadow-soft-sm space-y-2.5">
                <div class="flex justify-between items-center">
                    <div class="text-xs font-bold text-slate-200 flex items-center gap-1.5">
                        <span class="text-amber-400">⚡</span>
                        <span>Aktivitas Scan Terkini (Live Feed)</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono" id="feedLastUpdated">Live Sync 2.5s</span>
                </div>
                <div id="liveRecentScansList" class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                    <div class="text-center py-4 text-xs text-slate-500">
                        Memuat aktivitas scan terkini...
                    </div>
                </div>
            </div>

            <!-- Fast Tap Simulation Panel -->
            <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/70 shadow-soft-sm">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-xs font-bold text-sky-400 flex items-center gap-1">
                        <span>⚡</span>
                        <span>Panel Tap Cepat Siswa (Anti-Gagal Test):</span>
                    </h3>
                    <a href="<?= App::baseUrl('piket/riwayat') ?>" class="text-[11px] text-slate-400 hover:text-sky-300 underline font-medium">
                        Log Hari Ini 📋
                    </a>
                </div>
                <p class="text-[11px] text-slate-400 mb-3 leading-relaxed">
                    Klik nama siswa untuk simulasi pemindaian kartu langsung dengan umpan balik suara BEEP:
                </p>
                <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1">
                    <?php foreach ($siswaList as $s): ?>
                    <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-700/50 flex justify-between items-center hover:border-brand-500/40 transition-all duration-150">
                        <div>
                            <div class="text-xs font-bold text-slate-200"><?= htmlspecialchars($s['nama_siswa']) ?></div>
                            <div class="text-[10px] text-slate-400">
                                <?= htmlspecialchars($s['nama_kelas']) ?> | <code class="text-sky-400"><?= htmlspecialchars($s['barcode_code']) ?></code>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="simulateScan('<?= htmlspecialchars($s['barcode_code']) ?>')"
                                class="px-2.5 py-1.5 rounded-lg bg-brand-600/20 hover:bg-brand-600 text-brand-300 hover:text-white border border-brand-500/30 text-[11px] font-semibold transition-all duration-150">
                            Scan ➜
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Html5-Qrcode Library CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        window.BASE_URL = '<?= App::baseUrl() ?>/';
    </script>
    <script src="<?= App::baseUrl('js/scanner.js') ?>"></script>
    <script>
        let lastPiketScansJson = '';

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
        }

        function renderRecentScans(scans) {
            const container = document.getElementById('liveRecentScansList');
            if (!container) return;
            if (!scans || scans.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-xs text-slate-500">
                        Belum ada aktivitas pemindaian kartu hari ini.
                    </div>
                `;
                return;
            }

            container.innerHTML = scans.map(s => {
                const isDatang = s.scan_direction === 'DATANG';
                const badgeColor = isDatang ? 'bg-sky-500/15 text-sky-300 border-sky-500/30' : 'bg-amber-500/15 text-amber-300 border-amber-500/30';
                const icon = isDatang ? '☀️' : '🌙';
                const timeStr = s.last_scan_time ? s.last_scan_time.substring(11, 19) : '--:--:--';
                return `
                    <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-700/60 flex items-center justify-between gap-2 hover:border-slate-600 transition">
                        <div class="min-w-0 flex items-center gap-2">
                            <span class="text-base">${isDatang ? '👦' : '🎒'}</span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-100 truncate">${escapeHtml(s.nama_siswa)}</div>
                                <div class="text-[10px] text-slate-400 truncate">${escapeHtml(s.nama_kelas)} • NISN: ${escapeHtml(s.nisn)}</div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md border ${badgeColor}">
                                ${icon} ${isDatang ? 'Masuk' : 'Pulang'}
                            </span>
                            <div class="text-[10px] font-mono text-slate-400 mt-0.5">${timeStr}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function pollPiketLive(force = false) {
            if (!force && document.hidden) return;
            try {
                let rawBase = (window.BASE_URL || '/').trim().replace(/\/+$/, '');
                if (!rawBase.startsWith('/')) rawBase = '/' + rawBase;
                if (rawBase === '/') rawBase = '';
                const endpoint = rawBase + '/piket/live-feed';

                const res = await fetch(endpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;

                // Update server clock
                const clockEl = document.getElementById('piketSyncClock');
                if (clockEl && data.server_time) {
                    clockEl.textContent = data.server_time + ' WIB';
                }

                // Update counters
                if (data.counters) {
                    const cd = document.getElementById('cntDatang');
                    const cp = document.getElementById('cntPulang');
                    const cl = document.getElementById('cntLengkap');
                    if (cd) cd.textContent = data.counters.total_datang;
                    if (cp) cp.textContent = data.counters.total_pulang;
                    if (cl) cl.textContent = data.counters.total_hadir_lengkap;
                }

                // Update recent scans list
                if (Array.isArray(data.recent_scans)) {
                    const scansStr = JSON.stringify(data.recent_scans);
                    if (scansStr !== lastPiketScansJson || force) {
                        lastPiketScansJson = scansStr;
                        renderRecentScans(data.recent_scans);
                    }
                }
            } catch (err) {
                console.warn('Piket live feed error:', err);
            }
        }

        // Global callback for scanner.js to refresh immediately on successful scan
        window.onPiketScanSuccess = function() {
            pollPiketLive(true);
        };

        document.addEventListener('DOMContentLoaded', () => {
            pollPiketLive(true);
            setInterval(() => pollPiketLive(false), 2500);

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) pollPiketLive(true);
            });

            setTimeout(() => {
                initCamera();
            }, 500);
        });
    </script>
</body>
</html>
