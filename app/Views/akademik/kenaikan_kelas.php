<?php
use App\Config\App;

$activeNav = 'kenaikan';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Kenaikan Kelas Komplit - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Proses Kenaikan Kelas & Kelulusan Komplit
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Siklus tahun ajaran berkelanjutan, pencatatan riwayat rombel, dan roll-over kelas siswa</p>
            </div>
            <div>
                <a href="<?= App::baseUrl('admin/akademik/tahun-pelajaran') ?>" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-slate-300">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Kelola Tahun Pelajaran
                </a>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">

            <!-- Flash Alert -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-soft-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-soft-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Selection Card: Kelas Asal & Tapel Tujuan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                <form method="GET" action="<?= App::baseUrl('admin/akademik/kenaikan-kelas') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 items-end">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">1. Pilih Kelas Asal yang Akan Diproses</label>
                        <select name="kelas_asal_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($kelasAsalId === (int)$k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?> (Tingkat <?= htmlspecialchars($k['tingkat']) ?> - <?= htmlspecialchars($k['jurusan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">2. Tahun Pelajaran Tujuan</label>
                        <select name="tapel_tujuan_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <?php foreach ($tapelList as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($tapelTujuanId === (int)$t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['tahun_ajaran']) ?> (<?= htmlspecialchars($t['semester']) ?>) <?= ($t['is_aktif'] == 1) ? '★ [AKTIF]' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="text-xs text-slate-500 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-slate-700 flex-shrink-0">ℹ️</span>
                        <div>Menampilkan seluruh siswa aktif di <strong><?= htmlspecialchars($selectedKelasAsal['nama_kelas'] ?? '') ?></strong>.</div>
                    </div>
                </form>
            </div>

            <!-- Form Proses Roll-over Siswa -->
            <form method="POST" action="<?= App::baseUrl('admin/akademik/kenaikan-kelas/process') ?>
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>" onsubmit="return confirm('Apakah Anda yakin ingin memproses status kenaikan kelas untuk seluruh siswa terpilih? Data riwayat kelas siswa akan tercatat permanen.');">
                <input type="hidden" name="kelas_asal_id" value="<?= $kelasAsalId ?>">
                <input type="hidden" name="tapel_tujuan_id" value="<?= $tapelTujuanId ?>">

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    
                    <!-- Quick Mass Actions Bar -->
                    <div class="p-5 border-b border-slate-100 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-slate-700">Aksi Cepat Massal:</span>
                            <button type="button" onclick="setAllAction('NAIK_KELAS')" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-[11px] font-bold px-3 py-1.5 rounded-lg transition">
                                Set Semua Naik Kelas
                            </button>
                            <button type="button" onclick="setAllAction('TINGGAL_KELAS')" class="bg-amber-100 hover:bg-amber-200 text-amber-800 text-[11px] font-bold px-3 py-1.5 rounded-lg transition">
                                Set Semua Tinggal Kelas
                            </button>
                            <?php if (!empty($selectedKelasAsal['tingkat']) && (int)$selectedKelasAsal['tingkat'] === 12): ?>
                                <button type="button" onclick="setAllAction('LULUS')" class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-[11px] font-bold px-3 py-1.5 rounded-lg transition">
                                    Set Semua LULUS
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="text-xs font-bold text-slate-700">Kelas Tujuan Default:</label>
                            <select name="kelas_tujuan_default" id="kelas-tujuan-default" onchange="updateAllTargetClass(this.value)" class="bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                                <option value="0">-- Pilih Kelas Tujuan --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (Tingkat <?= htmlspecialchars($k['tingkat']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Table Siswa -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-white text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4">Nama Siswa</th>
                                    <th class="py-3 px-4">NISN</th>
                                    <th class="py-3 px-4">Keputusan Kenaikan</th>
                                    <th class="py-3 px-4">Kelas Tujuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php if (empty($siswaList)): ?>
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400">
                                            Tidak ada siswa ditemukan di kelas asal ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($siswaList as $s): $sid = $s['id']; ?>
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="py-3.5 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                            <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($s['nama_siswa']) ?></td>
                                            <td class="py-3.5 px-4 font-mono text-slate-600"><?= htmlspecialchars($s['nisn']) ?></td>
                                            <td class="py-3 px-4">
                                                <select name="siswa_action[<?= $sid ?>]" class="siswa-action-select w-44 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                                                    <option value="NAIK_KELAS" selected>Naik Kelas</option>
                                                    <option value="TINGGAL_KELAS">Tinggal Kelas</option>
                                                    <option value="LULUS">Lulus</option>
                                                    <option value="MUTASI_KELUAR">Mutasi Keluar</option>
                                                </select>
                                            </td>
                                            <td class="py-3 px-4">
                                                <select name="kelas_tujuan[<?= $sid ?>]" class="siswa-kelas-select w-52 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                                                    <option value="0">-- Tetap / Ikuti Default --</option>
                                                    <?php foreach ($kelasList as $k): ?>
                                                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (Tingkat <?= htmlspecialchars($k['tingkat']) ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            Total siswa yang akan diproses: <strong><?= count($siswaList) ?> siswa</strong>
                        </div>
                        <button type="submit" <?= empty($siswaList) ? 'disabled' : '' ?> class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Eksekusi Kenaikan Kelas Siswa
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </main>

    <script>
    function setAllAction(actionValue) {
        const selects = document.querySelectorAll('.siswa-action-select');
        selects.forEach(sel => {
            sel.value = actionValue;
        });
    }

    function updateAllTargetClass(kelasId) {
        if (kelasId !== '0') {
            const selects = document.querySelectorAll('.siswa-kelas-select');
            selects.forEach(sel => {
                sel.value = kelasId;
            });
        }
    }
    </script>

</body>
</html>
