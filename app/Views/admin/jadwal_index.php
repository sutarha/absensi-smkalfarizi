<?php
use App\Config\App;

$activeNav = 'jadwal';
$selectedHari = $_GET['hari'] ?? '';
$selectedKelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
$selectedGuruId = isset($_GET['guru_id']) && $_GET['guru_id'] !== '' ? (int)$_GET['guru_id'] : null;
$prefillMapelId = isset($_GET['mapel_id']) && $_GET['mapel_id'] !== '' ? (int)$_GET['mapel_id'] : null;

// Helper untuk URL filter dinamis
$buildFilterUrl = function(?string $h, ?int $k, ?int $g) use ($selectedHari, $selectedKelasId, $selectedGuruId): string {
    $params = [];
    $targetHari = ($h !== null) ? $h : $selectedHari;
    $targetKelas = ($k !== null) ? $k : $selectedKelasId;
    $targetGuru = ($g !== null) ? $g : $selectedGuruId;

    if (!empty($targetHari)) $params['hari'] = $targetHari;
    if (!empty($targetKelas)) $params['kelas_id'] = $targetKelas;
    if (!empty($targetGuru)) $params['guru_id'] = $targetGuru;

    return App::baseUrl('admin/jadwal' . (!empty($params) ? ('?' . http_build_query($params)) : ''));
};

$currentRedirectUrl = $buildFilterUrl(null, null, null);
$hasFilterActive = !empty($selectedHari) || !empty($selectedKelasId) || !empty($selectedGuruId);
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Jadwal KBM (Anti-Bentrok) - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Modern Admin Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex flex-wrap items-center justify-between gap-4 sticky top-0 z-30 shadow-soft-sm">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Master Jadwal Pelajaran (KBM)</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        🛡️ Anti-Bentrok Aktif
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Plotting jadwal mata pelajaran, alokasi guru pengampu otomatis, dan validasi bentrok KBM di <?= htmlspecialchars($config['nama_sekolah']) ?></p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="<?= App::baseUrl('admin/kelas') ?>" 
                   class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-soft-sm transition">
                    <span>📚 Plotting Mapel Kelas</span>
                </a>
                <button onclick="openCreateModal()" 
                        class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Jadwal KBM</span>
                </button>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <!-- Flash Message Sukses -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <!-- Flash Message Error (Termasuk Peringatan Bentrok Backend) -->
            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="bg-rose-50 border border-rose-300 text-rose-900 px-4 py-3 rounded-2xl flex items-start gap-3 shadow-soft-sm text-xs font-semibold animate-pulse">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <p class="font-bold text-rose-950">Peringatan Bentrok / Kesalahan:</p>
                    <p class="mt-0.5"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Filter Card (Hari, Kelas, Guru) -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft-sm space-y-4">
                
                <!-- Day Filter Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 border-b border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-2 shrink-0">Hari:</span>
                    <a href="<?= $buildFilterUrl('', null, null) ?>" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 <?= empty($selectedHari) ? 'bg-brand-600 text-white shadow-soft-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                        Semua Hari
                    </a>
                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                    <a href="<?= $buildFilterUrl($h, null, null) ?>" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 <?= $selectedHari === $h ? 'bg-brand-600 text-white shadow-soft-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                        <?= $h ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Dropdown Filters: Kelas & Guru -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <form method="GET" action="<?= App::baseUrl('admin/jadwal') ?>" class="flex flex-wrap items-center gap-3 text-xs">
                        <?php if (!empty($selectedHari)): ?>
                            <input type="hidden" name="hari" value="<?= htmlspecialchars($selectedHari) ?>">
                        <?php endif; ?>

                        <!-- Filter Kelas -->
                        <div class="flex items-center gap-1.5">
                            <label class="text-slate-500 font-bold shrink-0">Kelas:</label>
                            <select name="kelas_id" onchange="this.form.submit()" 
                                    class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $selectedKelasId === (int)$k['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?> (Tingkat <?= htmlspecialchars($k['tingkat']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter Guru -->
                        <div class="flex items-center gap-1.5">
                            <label class="text-slate-500 font-bold shrink-0">Guru:</label>
                            <select name="guru_id" onchange="this.form.submit()" 
                                    class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition">
                                <option value="">-- Semua Guru Pengampu --</option>
                                <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= $selectedGuruId === (int)$g['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['nama_lengkap']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($hasFilterActive): ?>
                        <a href="<?= App::baseUrl('admin/jadwal') ?>" 
                           class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-xl border border-rose-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Reset Filter</span>
                        </a>
                        <?php endif; ?>
                    </form>

                    <div class="text-xs text-slate-500 font-medium">
                        Total Ditampilkan: <strong class="text-slate-800 font-bold"><?= count($jadwalList) ?></strong> sesi KBM
                    </div>
                </div>

            </div>

            <!-- Table Jadwal -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        <?= !empty($selectedHari) ? "Jadwal Hari {$selectedHari}" : "Seluruh Jadwal Pelajaran" ?>
                        <?php if ($selectedKelasId): ?>
                            <?php foreach ($kelasList as $k) if ((int)$k['id'] === $selectedKelasId) echo " • Kelas " . htmlspecialchars($k['nama_kelas']); ?>
                        <?php endif; ?>
                        <?php if ($selectedGuruId): ?>
                            <?php foreach ($guruList as $g) if ((int)$g['id'] === $selectedGuruId) echo " • Guru " . htmlspecialchars($g['nama_lengkap']); ?>
                        <?php endif; ?>
                    </span>
                    <span class="text-xs text-slate-400">🛡️ Proteksi bentrok otomatis saat penambahan & pengubahan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">Hari</th>
                                <th class="py-3.5 px-4">Jam Pelajaran</th>
                                <th class="py-3.5 px-4 text-center">JP</th>
                                <th class="py-3.5 px-4">Mata Pelajaran</th>
                                <th class="py-3.5 px-4">Kelas</th>
                                <th class="py-3.5 px-4">Guru Pengampu</th>
                                <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($jadwalList)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    <div class="text-3xl mb-2">📅</div>
                                    <p class="font-bold text-slate-600">Belum ada jadwal KBM yang sesuai dengan filter.</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Silakan klik tombol "Tambah Jadwal KBM" untuk mulai menyusun jadwal.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($jadwalList as $j): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                            <?= htmlspecialchars($j['hari']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-mono font-bold text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-md border border-brand-100 text-[11px]">
                                            <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <?= (int)$j['jumlah_jp'] ?> JP
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($j['nama_mapel']) ?></div>
                                        <?php if (!empty($j['kode_mapel'])): ?>
                                            <div class="text-[10px] text-slate-400 font-mono">[<?= htmlspecialchars($j['kode_mapel']) ?>] • <?= htmlspecialchars($j['kelompok_mapel'] ?? '') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($j['nama_kelas']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 font-medium">
                                        👨‍🏫 <?= htmlspecialchars($j['nama_guru']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 justify-center">
                                            <!-- Tombol Akses Cepat Input Nilai -->
                                            <a href="<?= App::baseUrl("admin/nilai/input?kelas_id={$j['kelas_id']}&mapel_id=" . ($j['mapel_id'] ?? 1)) ?>" 
                                               title="Input Nilai KBM Kelas & Mapel Ini" 
                                               class="p-1.5 rounded-lg text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($j), ENT_QUOTES, 'UTF-8') ?>)" 
                                                    title="Edit Jadwal"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <form action="<?= App::baseUrl("admin/jadwal/delete/{$j['id']}") ?>" method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                                <button type="submit" 
                                                        title="Hapus Jadwal"
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal Form Jadwal (Dengan Otomatisasi Mapel/Guru & Live Anti-Bentrok) -->
    <div id="jadwalModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transition-all">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <div>
                    <h3 id="modalTitle" class="font-display font-bold text-base text-slate-900">Tambah Jadwal KBM Baru</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pilih kelas & mapel, jam pelajaran akan tervalidasi secara otomatis</p>
                </div>
                <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                    &times;
                </button>
            </div>

            <form id="jadwalForm" method="POST" action="<?= App::baseUrl('admin/jadwal/store') ?>" class="space-y-4">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <input type="hidden" name="exclude_id" id="m_exclude_id" value="">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentRedirectUrl) ?>">

                <!-- Row 1: Kelas Rombel & Hari -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kelas Rombel <span class="text-rose-500">*</span>
                        </label>
                        <select name="kelas_id" id="m_kelas_id" onchange="onKelasChange()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                            <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($selectedKelasId === (int)$k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Hari KBM <span class="text-rose-500">*</span>
                        </label>
                        <select name="hari" id="m_hari" onchange="checkConflictDebounced()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Mata Pelajaran (Otomatis menampilkan guru & alokasi JP terplot) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <span id="mapelPlottedBadge" class="text-[10px] font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md hidden">
                            ✨ Rekomendasi Terplot Kelas
                        </span>
                    </div>
                    <select name="mapel_id" id="m_mapel_id" onchange="onMapelSelect()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                        <option value="">-- Memuat Mata Pelajaran... --</option>
                    </select>
                    <input type="hidden" name="nama_mapel" id="m_nama_mapel">
                </div>

                <!-- Row 3: Guru Pengampu (Otomatis terisi dari alokasi kelas_mapel) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Guru Pengampu Mapel <span class="text-rose-500">*</span>
                        </label>
                        <span id="guruAutoLabel" class="text-[10px] text-emerald-600 font-semibold hidden">
                            ✓ Otomatis terpasang dari data kelas
                        </span>
                    </div>
                    <select name="guru_id" id="m_guru_id" onchange="checkConflictDebounced()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($guruList as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Row 4: Jumlah JP, Jam Mulai, Jam Selesai -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah JP</label>
                        <input type="number" name="jumlah_jp" id="m_jumlah_jp" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" value="2" min="1" max="10" required oninput="calcEndTime(); checkConflictDebounced();">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">1 JP = 40 Menit</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="m_jam_mulai" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" value="07:30" required onchange="calcEndTime(); checkConflictDebounced();">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="m_jam_selesai" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" value="08:50" required onchange="checkConflictDebounced()">
                    </div>
                </div>

                <!-- Row 5: Realtime Anti-Bentrok Alert Indicator Box -->
                <div id="conflictAlertBox" class="p-3 rounded-2xl text-xs font-medium border transition-all duration-200 bg-slate-50 border-slate-200 text-slate-600 flex items-start gap-2.5">
                    <span id="conflictIcon" class="text-sm shrink-0">⏳</span>
                    <div class="flex-1">
                        <div id="conflictTitle" class="font-bold text-[11px]">Memeriksa Jadwal...</div>
                        <div id="conflictMsg" class="text-[11px] mt-0.5 text-slate-500">Memeriksa kemungkinan bentrok kelas dan guru...</div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitJadwal" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-soft-sm hover:shadow-glow-brand transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="btnSubmitText">Simpan Jadwal KBM</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Master Fallback untuk Mapel Seluruhnya -->
    <script>
        const allCurriculumMapel = <?= json_encode($mapelList) ?>;
        const modal = document.getElementById('jadwalModal');
        const form = document.getElementById('jadwalForm');
        let currentKelasMapelData = [];
        let debounceTimer = null;
        let hasActiveConflict = false;

        // Fetch plotted mapel & teachers for selected class
        async function loadKelasMapelOptions(kelasId, selectMapelId = null) {
            const selMapel = document.getElementById('m_mapel_id');
            selMapel.innerHTML = '<option value="">-- Memuat Mapel Terplot Kelas... --</option>';

            try {
                const res = await fetch(`<?= App::baseUrl('admin/kelas/') ?>${kelasId}/mapel-json`);
                const json = await res.json();
                currentKelasMapelData = (json.status === 'success') ? json.data : [];

                selMapel.innerHTML = '';
                const defOpt = document.createElement('option');
                defOpt.value = '';
                defOpt.textContent = '-- Pilih Mata Pelajaran --';
                selMapel.appendChild(defOpt);

                // Group 1: Plotted mapel in this class
                if (currentKelasMapelData.length > 0) {
                    const optgroupPlotted = document.createElement('optgroup');
                    optgroupPlotted.label = '📌 Mapel Terplot di Kelas Ini (Rekomendasi)';

                    currentKelasMapelData.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.mapel_id;
                        opt.textContent = `[${item.kode_mapel}] ${item.nama_mapel} — 👨‍🏫 ${item.nama_guru} (${item.alokasi_jp} JP)`;
                        opt.dataset.nama = item.nama_mapel;
                        opt.dataset.guruId = item.guru_id;
                        opt.dataset.guruNama = item.nama_guru;
                        opt.dataset.jp = item.alokasi_jp;
                        opt.dataset.isPlotted = '1';
                        optgroupPlotted.appendChild(opt);
                    });
                    selMapel.appendChild(optgroupPlotted);
                }

                // Group 2: All curriculum mapel (fallback if unplotted)
                const optgroupAll = document.createElement('optgroup');
                optgroupAll.label = '🌐 Semua Mata Pelajaran Kurikulum';
                allCurriculumMapel.forEach(m => {
                    const isAlreadyInPlotted = currentKelasMapelData.some(p => parseInt(p.mapel_id) === parseInt(m.id));
                    if (!isAlreadyInPlotted) {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = `[${m.kode_mapel}] ${m.nama_mapel} (${m.kelompok})`;
                        opt.dataset.nama = m.nama_mapel;
                        opt.dataset.isPlotted = '0';
                        optgroupAll.appendChild(opt);
                    }
                });
                selMapel.appendChild(optgroupAll);

                // Auto select if provided
                if (selectMapelId) {
                    selMapel.value = selectMapelId;
                    onMapelSelect();
                } else if (currentKelasMapelData.length > 0 && !document.getElementById('m_exclude_id').value) {
                    // Pick the first plotted item by default
                    selMapel.value = currentKelasMapelData[0].mapel_id;
                    onMapelSelect();
                }

            } catch (err) {
                console.error('Error fetching kelas mapel:', err);
                selMapel.innerHTML = '<option value="">Gagal memuat mapel</option>';
            }
        }

        function onKelasChange() {
            const kelasId = document.getElementById('m_kelas_id').value;
            loadKelasMapelOptions(kelasId);
            checkConflictDebounced();
        }

        function onMapelSelect() {
            const selMapel = document.getElementById('m_mapel_id');
            const opt = selMapel.options[selMapel.selectedIndex];
            const badge = document.getElementById('mapelPlottedBadge');
            const autoGuruLabel = document.getElementById('guruAutoLabel');

            if (opt && opt.dataset.nama) {
                document.getElementById('m_nama_mapel').value = opt.dataset.nama;

                // Jika mapel ini terplot di kelas, otomatis pasang Guru dan JP!
                if (opt.dataset.isPlotted === '1') {
                    badge.classList.remove('hidden');
                    autoGuruLabel.classList.remove('hidden');

                    if (opt.dataset.guruId) {
                        document.getElementById('m_guru_id').value = opt.dataset.guruId;
                    }
                    if (opt.dataset.jp) {
                        document.getElementById('m_jumlah_jp').value = opt.dataset.jp;
                    }
                    calcEndTime();
                } else {
                    badge.classList.add('hidden');
                    autoGuruLabel.classList.add('hidden');
                }
            } else {
                badge.classList.add('hidden');
                autoGuruLabel.classList.add('hidden');
            }

            checkConflictDebounced();
        }

        function calcEndTime() {
            const startVal = document.getElementById('m_jam_mulai').value;
            const jpVal = parseInt(document.getElementById('m_jumlah_jp').value) || 2;
            if (startVal) {
                const parts = startVal.split(':');
                let h = parseInt(parts[0]);
                let m = parseInt(parts[1]);
                let totalMinutes = h * 60 + m + (jpVal * 40);
                let endH = Math.floor(totalMinutes / 60) % 24;
                let endM = totalMinutes % 60;
                document.getElementById('m_jam_selesai').value = 
                    String(endH).padStart(2, '0') + ':' + String(endM).padStart(2, '0');
            }
        }

        // Live Anti-Bentrok Checker
        function checkConflictDebounced() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(checkConflictRealtime, 250);
        }

        async function checkConflictRealtime() {
            const hari = document.getElementById('m_hari').value;
            const kelasId = document.getElementById('m_kelas_id').value;
            const guruId = document.getElementById('m_guru_id').value;
            const jamMulai = document.getElementById('m_jam_mulai').value;
            const jamSelesai = document.getElementById('m_jam_selesai').value;
            const excludeId = document.getElementById('m_exclude_id').value;

            const box = document.getElementById('conflictAlertBox');
            const icon = document.getElementById('conflictIcon');
            const title = document.getElementById('conflictTitle');
            const msg = document.getElementById('conflictMsg');
            const btnSubmit = document.getElementById('btnSubmitJadwal');

            if (!hari || !kelasId || !guruId || !jamMulai || !jamSelesai) {
                box.className = 'p-3 rounded-2xl text-xs font-medium border bg-slate-50 border-slate-200 text-slate-600 flex items-start gap-2.5';
                icon.textContent = 'ℹ️';
                title.textContent = 'Lengkapi Pilihan Jadwal';
                msg.textContent = 'Pilih kelas, guru, dan waktu KBM untuk verifikasi otomatis.';
                btnSubmit.disabled = false;
                hasActiveConflict = false;
                return;
            }

            // Status loading
            box.className = 'p-3 rounded-2xl text-xs font-medium border bg-amber-50/70 border-amber-200 text-amber-900 flex items-start gap-2.5 animate-pulse';
            icon.textContent = '⏳';
            title.textContent = 'Memeriksa Waktu KBM...';
            msg.textContent = `Memvalidasi slot ${jamMulai} - ${jamSelesai} pada hari ${hari}...`;

            try {
                const formData = new FormData();
                formData.append('hari', hari);
                formData.append('kelas_id', kelasId);
                formData.append('guru_id', guruId);
                formData.append('jam_mulai', jamMulai);
                formData.append('jam_selesai', jamSelesai);
                if (excludeId) formData.append('exclude_id', excludeId);

                const res = await fetch('<?= App::baseUrl('admin/jadwal/check-conflict') ?>', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();

                if (result.has_conflict) {
                    hasActiveConflict = true;
                    box.className = 'p-3 rounded-2xl text-xs font-semibold border bg-rose-50 border-rose-300 text-rose-950 flex items-start gap-2.5 shadow-soft-sm';
                    icon.textContent = '⛔';
                    title.textContent = result.title || 'JADWAL BENTROK!';
                    msg.innerHTML = result.message;
                    btnSubmit.classList.remove('bg-brand-600', 'hover:bg-brand-700');
                    btnSubmit.classList.add('bg-rose-600', 'hover:bg-rose-700');
                    document.getElementById('btnSubmitText').textContent = 'Bentrok Terdeteksi (Cek Jam)';
                } else {
                    hasActiveConflict = false;
                    box.className = 'p-3 rounded-2xl text-xs font-semibold border bg-emerald-50 border-emerald-200 text-emerald-900 flex items-start gap-2.5';
                    icon.textContent = '✅';
                    title.textContent = 'Slot Jam KBM Aman';
                    msg.textContent = 'Tidak ada bentrok jadwal kelas maupun guru pengampu pada jam ini.';
                    btnSubmit.classList.remove('bg-rose-600', 'hover:bg-rose-700');
                    btnSubmit.classList.add('bg-brand-600', 'hover:bg-brand-700');
                    document.getElementById('btnSubmitText').textContent = 'Simpan Jadwal KBM';
                }
            } catch (err) {
                console.error('Error checking conflict:', err);
            }
        }

        // Intercept form submit if conflict exists
        form.onsubmit = function(e) {
            if (hasActiveConflict) {
                const confirmSave = confirm('PERINGATAN: Jadwal yang Anda pilih bentrok dengan sesi KBM lain! Tetap ingin mencoba menyimpan? (Sistem backend akan menolak bentrok).');
                if (!confirmSave) {
                    e.preventDefault();
                    return false;
                }
            }
            return true;
        };

        function openCreateModal(prefillKelas = null, prefillMapel = null) {
            document.getElementById('modalTitle').textContent = 'Tambah Jadwal KBM Baru';
            form.action = '<?= App::baseUrl('admin/jadwal/store') ?>';
            form.reset();
            document.getElementById('m_exclude_id').value = '';

            const targetKelas = prefillKelas || <?= !empty($selectedKelasId) ? (int)$selectedKelasId : "document.getElementById('m_kelas_id').value" ?>;
            if (targetKelas) {
                document.getElementById('m_kelas_id').value = targetKelas;
            }
            if (<?= !empty($selectedHari) ? "'" . htmlspecialchars($selectedHari) . "'" : "''" ?>) {
                document.getElementById('m_hari').value = '<?= htmlspecialchars($selectedHari) ?>';
            }

            calcEndTime();
            loadKelasMapelOptions(document.getElementById('m_kelas_id').value, prefillMapel);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(checkConflictRealtime, 350);
        }

        function openEditModal(j) {
            document.getElementById('modalTitle').textContent = 'Edit Jadwal: ' + j.nama_mapel;
            form.action = '<?= App::baseUrl('admin/jadwal/update/') ?>' + j.id;
            document.getElementById('m_exclude_id').value = j.id;

            document.getElementById('m_hari').value = j.hari;
            document.getElementById('m_jumlah_jp').value = j.jumlah_jp;
            document.getElementById('m_nama_mapel').value = j.nama_mapel;
            document.getElementById('m_kelas_id').value = j.kelas_id;
            document.getElementById('m_guru_id').value = j.guru_id;
            document.getElementById('m_jam_mulai').value = j.jam_mulai.substring(0, 5);
            document.getElementById('m_jam_selesai').value = j.jam_selesai.substring(0, 5);

            loadKelasMapelOptions(j.kelas_id, j.mapel_id);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(checkConflictRealtime, 350);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.onclick = function(e) {
            if (e.target === modal) closeModal();
        };

        // Auto-open modal jika URL membawa query string kelas_id & mapel_id (dari halaman plotting kelas)
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const paramKelas = urlParams.get('kelas_id');
            const paramMapel = urlParams.get('mapel_id');
            if (paramKelas && paramMapel) {
                openCreateModal(paramKelas, paramMapel);
            }
        });
    </script>
</body>
</html>
