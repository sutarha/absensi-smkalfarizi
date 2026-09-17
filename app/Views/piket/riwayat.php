<?php
use App\Config\App;
use App\Helpers\TimeHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Scan Gerbang - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-12">
        <!-- Top Navbar -->
        <div class="bg-slate-900 text-white px-5 py-4 flex items-center justify-between shadow-soft-md sticky top-0 z-20">
            <a href="<?= App::baseUrl('piket/scanner') ?>" 
               class="text-sky-400 hover:text-sky-300 text-xs font-semibold flex items-center gap-1 transition-all duration-150">
                <span>◀</span>
                <span>Scanner</span>
            </a>
            <h1 class="font-outfit font-bold text-sm text-white">Riwayat Scan Hari Ini</h1>
            <div class="w-12"></div>
        </div>

        <div class="p-5 flex-1 space-y-4">
            <!-- Filter Kelas -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                <form method="GET" action="<?= App::baseUrl('piket/riwayat') ?>" id="formFilterRiwayat">
                    <select name="kelas_id" id="filterKelasId"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/15" 
                            onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelasId == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="flex justify-between items-center px-1">
                <div class="text-xs font-bold font-outfit text-slate-800 uppercase tracking-wider" id="labelTotalSiswa">
                    Log Presensi Gerbang (<?= count($presensiList) ?> Siswa)
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span id="riwayatSyncTime" class="text-[10px] font-mono text-slate-400">Live 3s</span>
                </div>
            </div>

            <div id="riwayatListContainer" class="space-y-2.5">
            <?php if (empty($presensiList)): ?>
                <div class="p-8 text-center rounded-2xl bg-white border border-slate-200 text-slate-400 text-xs shadow-soft-sm" id="emptyStateBox">
                    Belum ada data presensi siswa hari ini.
                </div>
            <?php else: ?>
                <?php foreach ($presensiList as $p): ?>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md transition-all duration-200">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <div class="font-bold text-sm text-slate-900">
                                <?= htmlspecialchars($p['nama_siswa']) ?>
                            </div>
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                <?= htmlspecialchars($p['nama_kelas']) ?> • <span class="font-mono">NISN: <?= htmlspecialchars($p['nisn']) ?></span>
                            </div>
                        </div>
                        <div>
                            <?php if ($p['status_kehadiran'] === 'HADIR'): ?>
                                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider">
                                    HADIR LENGKAP
                                </span>
                            <?php elseif (!empty($p['waktu_datang']) && empty($p['waktu_pulang'])): ?>
                                <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold uppercase tracking-wider">
                                    SUDAH MASUK
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold uppercase tracking-wider">
                                    BELUM SCAN
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex justify-between mt-3 pt-2.5 border-t border-slate-100 text-[11px] text-slate-600">
                        <span>Masuk: <strong class="text-slate-900 font-mono"><?= !empty($p['waktu_datang']) ? date('H:i:s', strtotime($p['waktu_datang'])) : '-' ?></strong></span>
                        <span>Pulang: <strong class="text-slate-900 font-mono"><?= !empty($p['waktu_pulang']) ? date('H:i:s', strtotime($p['waktu_pulang'])) : '-' ?></strong></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        window.BASE_URL = '<?= App::baseUrl() ?>/';
        const currentKelasId = '<?= $kelasId ?? '' ?>';
        let lastRiwayatJson = '';

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
        }

        async function pollRiwayatLive() {
            if (document.hidden) return;
            try {
                let rawBase = (window.BASE_URL || '/').trim().replace(/\/+$/, '');
                if (!rawBase.startsWith('/')) rawBase = '/' + rawBase;
                if (rawBase === '/') rawBase = '';
                let url = rawBase + '/admin/monitoring/siswa-live?';
                if (currentKelasId) url += 'kelas_id=' + encodeURIComponent(currentKelasId);

                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success || !Array.isArray(data.gerbang_list)) return;

                const timeEl = document.getElementById('riwayatSyncTime');
                if (timeEl && data.server_time) {
                    timeEl.textContent = data.server_time + ' WIB';
                }

                const labelTotal = document.getElementById('labelTotalSiswa');
                if (labelTotal) {
                    labelTotal.textContent = `Log Presensi Gerbang (${data.gerbang_list.length} Siswa)`;
                }

                const currentStr = JSON.stringify(data.gerbang_list.map(s => ({
                    id: s.id,
                    d: s.waktu_datang,
                    p: s.waktu_pulang,
                    st: s.status_kehadiran
                })));

                if (currentStr === lastRiwayatJson) return;
                lastRiwayatJson = currentStr;

                const container = document.getElementById('riwayatListContainer');
                if (!container) return;

                if (data.gerbang_list.length === 0) {
                    container.innerHTML = `
                        <div class="p-8 text-center rounded-2xl bg-white border border-slate-200 text-slate-400 text-xs shadow-soft-sm">
                            Belum ada data presensi siswa hari ini.
                        </div>
                    `;
                    return;
                }

                container.innerHTML = data.gerbang_list.map(p => {
                    let badgeHtml = '';
                    if (p.status_kehadiran === 'HADIR') {
                        badgeHtml = `<span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider">HADIR LENGKAP</span>`;
                    } else if (p.waktu_datang && !p.waktu_pulang) {
                        badgeHtml = `<span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold uppercase tracking-wider">SUDAH MASUK</span>`;
                    } else {
                        badgeHtml = `<span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold uppercase tracking-wider">BELUM SCAN</span>`;
                    }

                    const masukTime = p.waktu_datang ? p.waktu_datang.substring(11, 19) : '-';
                    const pulangTime = p.waktu_pulang ? p.waktu_pulang.substring(11, 19) : '-';

                    return `
                        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md transition-all duration-200">
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <div class="font-bold text-sm text-slate-900">
                                        ${escapeHtml(p.nama_siswa)}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                        ${escapeHtml(p.nama_kelas || '')} • <span class="font-mono">NISN: ${escapeHtml(p.nisn || '')}</span>
                                    </div>
                                </div>
                                <div>
                                    ${badgeHtml}
                                </div>
                            </div>
                            <div class="flex justify-between mt-3 pt-2.5 border-t border-slate-100 text-[11px] text-slate-600">
                                <span>Masuk: <strong class="text-slate-900 font-mono">${masukTime}</strong></span>
                                <span>Pulang: <strong class="text-slate-900 font-mono">${pulangTime}</strong></span>
                            </div>
                        </div>
                    `;
                }).join('');

            } catch (err) {
                console.warn('Riwayat live sync error:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setInterval(pollRiwayatLive, 3000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) pollRiwayatLive();
            });
        });
    </script>
</body>
</html>
