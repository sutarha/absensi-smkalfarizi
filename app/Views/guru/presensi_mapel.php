<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$nowTime = date('H:i:s');
$isTimeReached = ($nowTime >= $sesi['jam_selesai']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Presensi Siswa - <?= htmlspecialchars($sesi['nama_mapel']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <style>
        .toggle-btn {
            flex: 1;
            padding: 9px 6px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            transition: all 0.18s ease;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .toggle-btn:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
        }
        .toggle-btn.active.hadir { background: #059669; color: white; border-color: #059669; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35); }
        .toggle-btn.active.sakit { background: #d97706; color: white; border-color: #d97706; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35); }
        .toggle-btn.active.izin { background: #0284c7; color: white; border-color: #0284c7; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); }
        .toggle-btn.active.alpha { background: #e11d48; color: white; border-color: #e11d48; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35); }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-20">
        <!-- Top App Bar -->
        <header class="bg-slate-900 text-white px-5 py-4 flex items-center justify-between shadow-soft-md sticky top-0 z-20">
            <a href="<?= App::baseUrl('guru/dashboard') ?>" 
               class="text-sky-400 hover:text-sky-300 text-xs font-semibold flex items-center gap-1 transition-colors">
                <span>◀</span>
                <span>Kembali</span>
            </a>
            <h1 class="font-outfit font-bold text-sm text-white">Presensi Siswa KBM</h1>
            <div class="w-12"></div>
        </header>

        <main class="p-5 flex-1 space-y-4">
            <!-- Info Sesi Mengajar Card -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-base font-outfit font-bold text-slate-900"><?= htmlspecialchars($sesi['nama_mapel']) ?></h2>
                        <div class="text-xs font-bold text-brand-600 mt-0.5">
                            🏫 <?= htmlspecialchars($sesi['nama_kelas']) ?> • <?= (int)$sesi['jumlah_jp'] ?> JP (<?= substr($sesi['jam_mulai'], 0, 5) ?> - <?= substr($sesi['jam_selesai'], 0, 5) ?>)
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200 text-xs font-semibold">
                        <?= date('d/m/Y', strtotime($sesi['tanggal'])) ?>
                    </span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 text-xs text-slate-500 flex justify-between">
                    <span>Check-in: <strong class="text-slate-700 font-mono"><?= date('H:i', strtotime($sesi['waktu_checkin'])) ?></strong></span>
                    <span>Status: <strong class="text-slate-800"><?= $sesi['menit_terlambat'] > 0 ? "Telat {$sesi['menit_terlambat']} mnt" : "Tepat Waktu" ?></strong></span>
                </div>
            </div>

            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm animate-fade-in">
                <span class="text-base">✅</span>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm animate-fade-in">
                <span class="text-base">⚠️</span>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Real-time Live Counters Bar -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200 shadow-soft-sm">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex justify-between items-center">
                    <span>Statistik Kehadiran Kelas</span>
                    <span id="totalStudentsText" class="text-slate-700 font-bold"><?= count($siswaList) ?> Siswa</span>
                </div>
                <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold">
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl py-1.5">
                        <div class="text-base font-extrabold" id="countHadir">0</div>
                        <div class="text-[10px]">Hadir</div>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl py-1.5">
                        <div class="text-base font-extrabold" id="countSakit">0</div>
                        <div class="text-[10px]">Sakit</div>
                    </div>
                    <div class="bg-sky-50 border border-sky-200 text-sky-700 rounded-xl py-1.5">
                        <div class="text-base font-extrabold" id="countIzin">0</div>
                        <div class="text-[10px]">Izin</div>
                    </div>
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl py-1.5">
                        <div class="text-base font-extrabold" id="countAlpha">0</div>
                        <div class="text-[10px]">Alpa</div>
                    </div>
                </div>
            </div>

            <form action="<?= App::baseUrl("guru/presensi/{$sesi['id']}") ?>" method="POST" id="presensiForm" class="space-y-3">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <div class="flex justify-between items-center px-1">
                    <span class="font-outfit font-bold text-xs uppercase tracking-wider text-slate-700">
                        Daftar Siswa (<?= count($siswaList) ?> Orang)
                    </span>
                    <button type="button" onclick="setAllHadir()"
                            class="px-3 py-1 rounded-xl bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 border border-slate-200 text-xs font-semibold transition-all duration-150 flex items-center gap-1">
                        <span>✨</span>
                        <span>Semua Hadir</span>
                    </button>
                </div>

                <?php foreach ($siswaList as $s): ?>
                <div class="student-item p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg flex-shrink-0">
                            <?= $s['jenis_kelamin'] === 'P' ? '👧' : '👦' ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm text-slate-900 truncate">
                                <?= htmlspecialchars($s['nama_siswa']) ?>
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                NISN: <?= htmlspecialchars($s['nisn']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Input for Status -->
                    <input type="hidden" name="presensi[<?= $s['siswa_id'] ?>]" id="status_<?= $s['siswa_id'] ?>" value="<?= htmlspecialchars($s['status'] ?: 'HADIR') ?>">

                    <!-- Modern Segmented Toggle Buttons with Clear Text -->
                    <div class="status-toggle-group flex gap-1.5 mt-3">
                        <button type="button" class="toggle-btn hadir <?= ($s['status'] ?: 'HADIR') === 'HADIR' ? 'active' : '' ?>" 
                                onclick="setStatus(<?= $s['siswa_id'] ?>, 'HADIR')">
                            <span>✓</span>
                            <span>Hadir</span>
                        </button>
                        <button type="button" class="toggle-btn sakit <?= $s['status'] === 'SAKIT' ? 'active' : '' ?>" 
                                onclick="setStatus(<?= $s['siswa_id'] ?>, 'SAKIT')">
                            <span>🤒</span>
                            <span>Sakit</span>
                        </button>
                        <button type="button" class="toggle-btn izin <?= $s['status'] === 'IZIN' ? 'active' : '' ?>" 
                                onclick="setStatus(<?= $s['siswa_id'] ?>, 'IZIN')">
                            <span>📋</span>
                            <span>Izin</span>
                        </button>
                        <button type="button" class="toggle-btn alpha <?= $s['status'] === 'ALPHA' ? 'active' : '' ?>" 
                                onclick="setStatus(<?= $s['siswa_id'] ?>, 'ALPHA')">
                            <span>✕</span>
                            <span>Alpa</span>
                        </button>
                    </div>

                    <input type="text" name="catatan[<?= $s['siswa_id'] ?>]" 
                           class="w-full mt-2.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-800 placeholder-slate-400 text-xs focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/15" 
                           placeholder="Catatan siswa (opsional, cth: izin UKS / surat dokter)" value="<?= htmlspecialchars($s['catatan'] ?? '') ?>">
                </div>
                <?php endforeach; ?>

                <!-- Sticky Action Floating Bar -->
                <div class="sticky bottom-4 pt-3 pb-1 z-10">
                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-bold text-sm shadow-glow-brand hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2">
                        <span>💾 Simpan Presensi Siswa</span>
                    </button>
                </div>
            </form>

            <?php if (empty($sesi['waktu_checkout'])): ?>
            <div class="mt-6 pt-4 border-t border-dashed border-slate-200">
                <form action="<?= App::baseUrl("guru/selesai/{$sesi['id']}") ?>" method="POST" id="formSelesaiKbm"
                      onsubmit="return confirm('Apakah Anda yakin sudah selesai mengajar? Saldo honor akan dihitung final.')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    <button type="submit" id="btnSelesaiKbm"
                            class="w-full py-3 px-4 rounded-2xl font-bold text-xs transition-all duration-200 flex items-center justify-center gap-2 <?= $isTimeReached ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-glow-emerald cursor-pointer' : 'bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed' ?>"
                            <?= !$isTimeReached ? 'disabled' : '' ?>>
                        <span id="iconSelesai"><?= $isTimeReached ? '🏁' : '🔒' ?></span>
                        <span id="textSelesai">
                            <?= $isTimeReached ? 'Selesai Mengajar Sesi Ini' : 'Tombol Akhiri KBM Aktif Pukul ' . substr($sesi['jam_selesai'], 0, 5) . ' WIB' ?>
                        </span>
                    </button>
                    <?php if (!$isTimeReached): ?>
                    <p id="noticeSelesai" class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 p-2.5 rounded-xl mt-2 text-center">
                        ⏳ Sesi KBM berakhir pukul <strong><?= substr($sesi['jam_selesai'], 0, 5) ?> WIB</strong>. Tombol akhiri KBM akan aktif otomatis ketika jam pembelajaran sudah selesai.
                    </p>
                    <?php endif; ?>
                </form>
            </div>
            <?php else: ?>
            <div class="mt-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2 text-center justify-center">
                <span>✅ Sesi KBM ini telah selesai pada <?= date('H:i', strtotime($sesi['waktu_checkout'])) ?> WIB.</span>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const targetJamSelesai = "<?= substr($sesi['jam_selesai'], 0, 8) ?>";

        function setStatus(siswaId, status) {
            const input = document.getElementById('status_' + siswaId);
            if (!input) return;
            input.value = status;
            
            const parent = input.closest('.student-item');
            if (parent) {
                const btns = parent.querySelectorAll('.toggle-btn');
                btns.forEach(b => {
                    b.classList.remove('active');
                    if (b.classList.contains(status.toLowerCase())) {
                        b.classList.add('active');
                    }
                });
            }
            updateLiveCounters();
        }

        function setAllHadir() {
            document.querySelectorAll('.student-item').forEach(item => {
                const hiddenInput = item.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    const siswaId = hiddenInput.id.replace('status_', '');
                    setStatus(siswaId, 'HADIR');
                }
            });
            updateLiveCounters();
        }

        function updateLiveCounters() {
            let hadir = 0, sakit = 0, izin = 0, alpha = 0;
            document.querySelectorAll('input[id^="status_"]').forEach(inp => {
                const val = (inp.value || 'HADIR').toUpperCase();
                if (val === 'HADIR') hadir++;
                else if (val === 'SAKIT') sakit++;
                else if (val === 'IZIN') izin++;
                else if (val === 'ALPHA') alpha++;
            });

            const elHadir = document.getElementById('countHadir');
            const elSakit = document.getElementById('countSakit');
            const elIzin = document.getElementById('countIzin');
            const elAlpha = document.getElementById('countAlpha');

            if (elHadir) elHadir.textContent = hadir;
            if (elSakit) elSakit.textContent = sakit;
            if (elIzin) elIzin.textContent = izin;
            if (elAlpha) elAlpha.textContent = alpha;
        }

        // Live timer untuk mengecek jam selesai pembelajaran
        function checkJamSelesai() {
            const btn = document.getElementById('btnSelesaiKbm');
            const text = document.getElementById('textSelesai');
            const icon = document.getElementById('iconSelesai');
            const notice = document.getElementById('noticeSelesai');
            if (!btn || !text || !icon) return;

            const now = new Date();
            const curTime = String(now.getHours()).padStart(2, '0') + ':' + 
                            String(now.getMinutes()).padStart(2, '0') + ':' + 
                            String(now.getSeconds()).padStart(2, '0');

            if (curTime >= targetJamSelesai) {
                if (btn.disabled) {
                    btn.disabled = false;
                    btn.className = "w-full py-3 px-4 rounded-2xl font-bold text-xs transition-all duration-200 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white shadow-glow-emerald cursor-pointer";
                    icon.textContent = "🏁";
                    text.textContent = "Selesai Mengajar Sesi Ini";
                    if (notice) notice.style.display = 'none';
                }
            }
        }

        // Jalankan kalkulasi counter awal dan interval pengecekan jam
        document.addEventListener('DOMContentLoaded', () => {
            updateLiveCounters();
            setInterval(checkJamSelesai, 1000);
            checkJamSelesai();
        });
    </script>
</body>
</html>
