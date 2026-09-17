<?php
use App\Config\App;
use App\Helpers\BarcodeHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Scanner Barcode - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <style>
        /* Custom Animations & Styles */
        @keyframes scanLaser {
            0% { top: 5%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 95%; opacity: 0; }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 15px rgba(56, 189, 248, 0.4); }
            50% { box-shadow: 0 0 30px rgba(56, 189, 248, 0.8); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .scan-guide-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 260px;
            height: 260px;
            border-radius: 24px;
            box-shadow: 0 0 0 4000px rgba(2, 6, 23, 0.7);
            pointer-events: none;
            z-index: 10;
        }
        .corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border-color: #38bdf8;
            border-style: solid;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.5);
            transition: all 0.3s ease;
        }
        .tl { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 24px; }
        .tr { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 24px; }
        .bl { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 24px; }
        .br { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 24px; }
        
        .scan-laser-line {
            position: absolute;
            left: 10%;
            right: 10%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, #fff, #38bdf8, transparent);
            box-shadow: 0 0 15px #38bdf8, 0 0 30px #38bdf8;
            animation: scanLaser 2.5s infinite cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
        }

        /* Mode Switcher Segmented Control */
        .segmented-control input[type="radio"] { display: none; }
        .segmented-control label {
            flex: 1;
            text-align: center;
            padding: 10px 16px;
            cursor: pointer;
            border-radius: 16px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #94a3b8;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 2;
        }
        .segmented-control .slider {
            position: absolute;
            top: 4px;
            bottom: 4px;
            width: calc(50% - 4px);
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            z-index: 1;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        #mode-datang:checked ~ .slider { left: 4px; }
        #mode-pulang:checked ~ .slider { 
            left: calc(50%);
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }
        #mode-datang:checked ~ label[for="mode-datang"],
        #mode-pulang:checked ~ label[for="mode-pulang"] { color: #ffffff; }
        
        /* Hide scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        
        /* Interactive feedback */
        .tap-effect:active { transform: scale(0.97); }
    </style>
</head>
<body class="bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black text-slate-100 font-sans min-h-screen selection:bg-brand-500 selection:text-white pb-20">
    <div class="max-w-md mx-auto min-h-screen relative flex flex-col">
        
        <!-- Header: Floating & Glass -->
        <header class="m-4 p-4 rounded-3xl glass-panel shadow-soft-lg flex justify-between items-center z-30 relative animate-[float_6s_ease-in-out_infinite]">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-400 to-brand-600 flex items-center justify-center text-xl shadow-glow-brand ring-2 ring-white/10">
                    📷
                </div>
                <div>
                    <h1 class="text-base font-outfit font-extrabold tracking-tight text-white flex items-center gap-2">
                        SMART GATE
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 shadow-[0_0_8px_#10b981]"></span>
                        </span>
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium">Petugas: <span class="text-sky-300"><?= htmlspecialchars($user['nama_lengkap']) ?></span></p>
                </div>
            </div>
            <a href="<?= App::baseUrl('logout') ?>" class="w-10 h-10 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/20 transition-all tap-effect">
                🚪
            </a>
        </header>

        <main class="flex-1 px-4 flex flex-col gap-5 relative z-20">
            <!-- Mode Switcher -->
            <div class="relative glass-panel rounded-2xl p-1 flex segmented-control">
                <input type="radio" name="scan_mode" id="mode-datang" value="DATANG" checked onchange="setScannerMode('DATANG')">
                <input type="radio" name="scan_mode" id="mode-pulang" value="PULANG" onchange="setScannerMode('PULANG')">
                <div class="slider"></div>
                <label for="mode-datang" class="flex items-center justify-center gap-2">☀️ MASUK</label>
                <label for="mode-pulang" class="flex items-center justify-center gap-2">🌙 PULANG</label>
            </div>

            <!-- Active Mode Hidden Inputs for JS compatibility -->
            <button id="btnModeDatang" class="hidden"></button>
            <button id="btnModePulang" class="hidden"></button>
            <div id="activeModeBadge" class="hidden"></div>

            <!-- Notifications -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="p-3 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-medium flex items-center justify-center gap-2 shadow-glow-emerald">
                <span>✅</span> <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <div id="scanErrorToast" class="hidden p-3 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-medium text-center animate-bounce"></div>

            <!-- Camera Viewport Container -->
            <div class="relative rounded-[32px] overflow-hidden bg-black ring-1 ring-white/10 shadow-[0_0_40px_rgba(37,99,235,0.15)] group h-[380px] w-full flex-shrink-0">
                <div id="reader" class="w-full h-full bg-black object-cover"></div>

                <!-- Animated Frame -->
                <div class="scan-guide-box">
                    <div class="corner tl"></div>
                    <div class="corner tr"></div>
                    <div class="corner bl"></div>
                    <div class="corner br"></div>
                    <div class="scan-laser-line"></div>
                </div>

                <!-- Floating Scanned Card Modal -->
                <div id="scannedStudentCard" class="hidden absolute inset-4 rounded-3xl bg-emerald-600/90 backdrop-blur-xl text-white flex-col items-center justify-center p-6 text-center shadow-2xl z-40 transition-all border border-white/20">
                    <div class="absolute inset-0 bg-[url('<?= App::baseUrl('img/pattern.svg') ?>')] opacity-20 mix-blend-overlay"></div>
                    <div id="popupStudentAvatar" class="w-20 h-20 rounded-full bg-white text-emerald-600 text-4xl flex items-center justify-center shadow-[0_0_30px_rgba(255,255,255,0.4)] mb-3 relative z-10">
                        👦
                    </div>
                    <h2 id="popupStudentName" class="text-xl font-outfit font-extrabold tracking-tight relative z-10">Nama Siswa</h2>
                    <div id="popupStudentNisn" class="text-xs text-emerald-100 font-mono mt-1 relative z-10">NISN: 0071234501</div>
                    <div id="popupStudentClass" class="mt-3 px-4 py-1.5 rounded-full bg-black/20 text-xs font-extrabold text-white border border-white/20 backdrop-blur-md relative z-10 shadow-inner">
                        X PPLG 1
                    </div>
                    <div id="popupScanTime" class="mt-4 text-sm font-extrabold text-amber-300 relative z-10">Masuk: 07:15:32</div>
                    <div id="popupMessage" class="text-[10px] text-emerald-50 mt-1 uppercase tracking-widest font-bold opacity-80 relative z-10">Tap-In Berhasil</div>
                </div>

                <!-- Camera Switcher Overlaid -->
                <div class="absolute bottom-4 left-4 right-4 flex gap-2 z-20">
                    <select id="cameraSelect" onchange="switchSelectedCamera()" class="flex-1 px-4 py-2.5 rounded-xl bg-black/50 backdrop-blur-md border border-white/10 text-white text-[11px] font-medium focus:outline-none appearance-none">
                        <option value="">Kamera Default...</option>
                    </select>
                    <label class="w-10 h-10 flex-shrink-0 rounded-xl bg-brand-600/80 backdrop-blur-md border border-brand-400/30 text-white flex items-center justify-center cursor-pointer shadow-glow-brand tap-effect">
                        📷
                        <input type="file" accept="image/*" class="hidden" onchange="scanFromFile(this)">
                    </label>
                </div>
            </div>

            <!-- Glass Stats Row (Overlapping camera slightly for depth) -->
            <div class="glass-panel rounded-3xl p-4 grid grid-cols-3 gap-3 -mt-10 z-30 shadow-xl border-t border-white/20">
                <div class="text-center">
                    <div class="text-2xl font-extrabold font-outfit text-sky-400 drop-shadow-[0_0_10px_rgba(56,189,248,0.5)]" id="cntDatang"><?= $totalDatang ?></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Masuk</div>
                </div>
                <div class="text-center border-x border-white/5">
                    <div class="text-2xl font-extrabold font-outfit text-amber-400 drop-shadow-[0_0_10px_rgba(251,191,36,0.5)]" id="cntPulang"><?= $totalPulang ?></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pulang</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold font-outfit text-emerald-400 drop-shadow-[0_0_10px_rgba(52,211,153,0.5)]" id="cntLengkap"><?= $totalHadirLengkap ?></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Hadir</div>
                </div>
            </div>

            <!-- Manual Input & Sync Header -->
            <div class="flex items-center justify-between mt-2">
                <div class="flex items-center gap-2">
                    <span id="piketSyncClock" class="text-xs font-mono font-bold text-slate-300">--:--:--</span>
                    <button type="button" onclick="pollPiketLive(true)" class="w-7 h-7 rounded-full bg-slate-800 text-sky-400 flex items-center justify-center text-xs hover:bg-slate-700 transition tap-effect">
                        🔄
                    </button>
                </div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Input Manual</div>
            </div>

            <form id="manualScanForm" class="flex gap-2">
                <input type="text" id="manualBarcodeInput" 
                       class="flex-1 px-4 py-3 rounded-2xl glass-panel text-white text-sm font-mono placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all" 
                       placeholder="NISN / Kode Barcode">
                <button type="submit" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-brand-600 to-sky-500 text-white font-bold text-xs shadow-glow-brand tap-effect">
                    PROSES
                </button>
            </form>

            <!-- Activity Stream -->
            <div class="glass-panel rounded-3xl p-4 flex flex-col gap-3 mt-2">
                <div class="flex justify-between items-center px-1">
                    <h3 class="text-xs font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span> Live Feed
                    </h3>
                    <a href="<?= App::baseUrl('piket/riwayat') ?>" class="text-[10px] text-sky-400 font-semibold hover:text-sky-300">
                        Log Lengkap &rarr;
                    </a>
                </div>
                <div id="liveRecentScansList" class="space-y-2 max-h-40 overflow-y-auto pr-2">
                    <!-- Loaded via JS -->
                    <div class="text-center py-6 text-xs text-slate-500">Memuat aktivitas...</div>
                </div>
            </div>

            <!-- Simulator Card (Collapsible) -->
            <details class="glass-panel rounded-3xl group mb-4">
                <summary class="p-4 text-xs font-bold text-slate-300 flex items-center justify-between cursor-pointer list-none select-none">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-400">⚡</span> Panel Uji Coba Tap Cepat
                    </div>
                    <span class="transition-transform group-open:rotate-180 text-slate-500">▼</span>
                </summary>
                <div class="p-4 pt-0 border-t border-white/5 max-h-60 overflow-y-auto mt-2 space-y-2">
                    <?php foreach ($siswaList as $s): ?>
                    <div class="p-3 rounded-2xl bg-black/20 border border-white/5 flex justify-between items-center hover:bg-white/5 transition">
                        <div>
                            <div class="text-xs font-bold text-white"><?= htmlspecialchars($s['nama_siswa']) ?></div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                <?= htmlspecialchars($s['nama_kelas']) ?> | <span class="text-brand-400 font-mono"><?= htmlspecialchars($s['barcode_code']) ?></span>
                            </div>
                        </div>
                        <button type="button" onclick="simulateScan('<?= htmlspecialchars($s['barcode_code']) ?>')" class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center tap-effect border border-brand-500/30">
                            +
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </details>

        </main>
    </div>

    <!-- Html5-Qrcode Library CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>window.BASE_URL = '<?= App::baseUrl() ?>/';</script>
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
                container.innerHTML = `<div class="text-center py-6 text-xs text-slate-500">Belum ada aktivitas hari ini.</div>`;
                return;
            }

            container.innerHTML = scans.map(s => {
                const isDatang = s.scan_direction === 'DATANG';
                const colorClass = isDatang ? 'text-sky-400' : 'text-amber-400';
                const bgClass = isDatang ? 'bg-sky-500/10' : 'bg-amber-500/10';
                const icon = isDatang ? 'Masuk' : 'Pulang';
                const timeStr = s.last_scan_time ? s.last_scan_time.substring(11, 16) : '--:--';
                
                return `
                    <div class="p-3 rounded-2xl bg-black/30 border border-white/5 flex items-center justify-between gap-3 backdrop-blur-md">
                        <div class="w-10 h-10 rounded-full ${bgClass} flex items-center justify-center text-lg shadow-inner shrink-0">
                            ${isDatang ? '👦' : '🎒'}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-bold text-white truncate">${escapeHtml(s.nama_siswa)}</div>
                            <div class="text-[10px] text-slate-400 truncate">${escapeHtml(s.nama_kelas)}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[10px] font-extrabold ${colorClass} uppercase tracking-wider">${icon}</div>
                            <div class="text-xs font-mono text-slate-300 mt-0.5">${timeStr}</div>
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
                const res = await fetch(rawBase + '/piket/live-feed', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;

                const clockEl = document.getElementById('piketSyncClock');
                if (clockEl && data.server_time) clockEl.textContent = data.server_time + ' WIB';

                if (data.counters) {
                    const cd = document.getElementById('cntDatang');
                    const cp = document.getElementById('cntPulang');
                    const cl = document.getElementById('cntLengkap');
                    if (cd) cd.textContent = data.counters.total_datang;
                    if (cp) cp.textContent = data.counters.total_pulang;
                    if (cl) cl.textContent = data.counters.total_hadir_lengkap;
                }

                if (Array.isArray(data.recent_scans)) {
                    const scansStr = JSON.stringify(data.recent_scans);
                    if (scansStr !== lastPiketScansJson || force) {
                        lastPiketScansJson = scansStr;
                        renderRecentScans(data.recent_scans);
                    }
                }
            } catch (err) {
                console.warn('Live feed error:', err);
            }
        }

        window.onPiketScanSuccess = function() { pollPiketLive(true); };

        document.addEventListener('DOMContentLoaded', () => {
            pollPiketLive(true);
            setInterval(() => pollPiketLive(false), 2500);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) pollPiketLive(true); });
            setTimeout(() => { initCamera(); }, 500);
        });
        
        // Listen to mode changes from segmented control to update JS variable
        document.querySelectorAll('input[name="scan_mode"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                if(typeof window.currentScanMode !== 'undefined') {
                    window.currentScanMode = e.target.value;
                }
            });
        });
    </script>
</body>
</html>
