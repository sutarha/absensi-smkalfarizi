<?php
use App\Config\App;

$activeNav = 'mapel';
$selectedKelompok = $_GET['kelompok'] ?? '';
$selectedTingkat = $_GET['tingkat'] ?? '';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Mata Pelajaran Kurikulum - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Modern Admin Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Master Mata Pelajaran Kurikulum</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola struktur mata pelajaran, kelompok muatan, dan tingkat kurikulum SMK Al-Farizi</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openCreateModal()" 
                        class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Mata Pelajaran</span>
                </button>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft-sm flex items-center justify-between flex-wrap gap-4">
                <form method="GET" action="<?= App::baseUrl('admin/mapel') ?>" class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Kelompok:</label>
                        <select name="kelompok" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition">
                            <option value="">-- Semua Kelompok --</option>
                            <option value="Umum" <?= $selectedKelompok === 'Umum' ? 'selected' : '' ?>>Muatan Umum (A)</option>
                            <option value="Kejuruan" <?= $selectedKelompok === 'Kejuruan' ? 'selected' : '' ?>>Muatan Kejuruan (B)</option>
                            <option value="Muatan Lokal" <?= $selectedKelompok === 'Muatan Lokal' ? 'selected' : '' ?>>Muatan Lokal (C)</option>
                            <option value="Pilihan" <?= $selectedKelompok === 'Pilihan' ? 'selected' : '' ?>>Program Pilihan</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Tingkat:</label>
                        <select name="tingkat" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition">
                            <option value="">-- Semua Tingkat (Tampilkan Semua) --</option>
                            <option value="X" <?= $selectedTingkat === 'X' ? 'selected' : '' ?>>Kelas X (Tingkat 10)</option>
                            <option value="XI" <?= $selectedTingkat === 'XI' ? 'selected' : '' ?>>Kelas XI (Tingkat 11)</option>
                            <option value="XII" <?= $selectedTingkat === 'XII' ? 'selected' : '' ?>>Kelas XII (Tingkat 12)</option>
                            <option value="SEMUA" <?= $selectedTingkat === 'SEMUA' ? 'selected' : '' ?>>Khusus Semua Tingkat (SEMUA)</option>
                        </select>
                    </div>

                    <?php if (!empty($selectedKelompok) || !empty($selectedTingkat)): ?>
                    <a href="<?= App::baseUrl('admin/mapel') ?>" class="text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition">
                        Reset Filter
                    </a>
                    <?php endif; ?>
                </form>

                <div class="text-xs text-slate-500 font-medium">
                    Total Mata Pelajaran: <strong class="text-slate-800 font-bold"><?= count($mapelList) ?></strong> mapel
                </div>
            </div>

            <!-- Table Mapel -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4 w-36">Kode Mapel</th>
                                <th class="py-3.5 px-4">Nama Mata Pelajaran</th>
                                <th class="py-3.5 px-4">Kelompok Kurikulum</th>
                                <th class="py-3.5 px-4 text-center">Tingkat Kelas</th>
                                <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($mapelList)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <div class="text-2xl mb-1">📚</div>
                                    <p class="font-medium">Belum ada mata pelajaran yang terdaftar.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($mapelList as $m): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                    <td class="py-3 px-4">
                                        <span class="font-mono font-bold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-lg border border-brand-100 text-[11px]">
                                             <?= htmlspecialchars($m['kode_mapel']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        <?= htmlspecialchars($m['nama_mapel']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php
                                        $kel = $m['kelompok'];
                                        $badgeBg = match($kel) {
                                            'Umum' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'Kejuruan' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'Muatan Lokal' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            default => 'bg-purple-50 text-purple-700 border-purple-200'
                                        };
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold border <?= $badgeBg ?>">
                                            <?= htmlspecialchars($kel) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php
                                        $rawTingkat = trim($m['tingkat'] ?? '');
                                        $tingkats = array_filter(array_map('trim', explode(',', $rawTingkat)));
                                        if ($rawTingkat === 'SEMUA' || count($tingkats) === 3):
                                        ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                Semua (X, XI, XII)
                                            </span>
                                        <?php elseif (empty($tingkats)): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-500">
                                                -
                                            </span>
                                        <?php else: ?>
                                            <div class="inline-flex flex-wrap items-center justify-center gap-1.5">
                                                <?php foreach ($tingkats as $t): 
                                                    $badgeStyle = match($t) {
                                                        'X' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'XI' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        'XII' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        default => 'bg-purple-50 text-purple-700 border-purple-200'
                                                    };
                                                    $labelTingkat = match($t) {
                                                        'X' => 'Kelas X (10)',
                                                        'XI' => 'Kelas XI (11)',
                                                        'XII' => 'Kelas XII (12)',
                                                        default => "Kelas {$t}"
                                                    };
                                                ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold border <?= $badgeStyle ?> shadow-sm">
                                                    <?= htmlspecialchars($labelTingkat) ?>
                                                </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 justify-center">
                                            <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)" 
                                                    title="Edit Mata Pelajaran"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <form action="<?= App::baseUrl("admin/mapel/delete/{$m['id']}") ?>" method="POST"
                                                  onsubmit="return confirm('Hapus mata pelajaran <?= addslashes($m['nama_mapel']) ?>? Nilai siswa terkait mungkin terdampak.')">
                                                <button type="submit" 
                                                        title="Hapus Mata Pelajaran"
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

    <!-- Modal Form Tambah / Edit Mapel -->
    <div id="mapelModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 id="modalTitle" class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span>Tambah Mata Pelajaran Baru</span>
                </h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form id="mapelForm" method="POST" action="<?= App::baseUrl('admin/mapel/store') ?>
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Mata Pelajaran</label>
                    <input type="text" name="kode_mapel" id="m_kode_mapel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition uppercase" placeholder="Contoh: PPLG-01, PAI, PJOK" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" id="m_nama_mapel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Contoh: Pemrograman Berorientasi Objek" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok Kurikulum</label>
                    <select name="kelompok" id="m_kelompok" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                        <option value="Umum">Muatan Umum (A)</option>
                        <option value="Kejuruan">Muatan Kejuruan (B)</option>
                        <option value="Muatan Lokal">Muatan Lokal (C)</option>
                        <option value="Pilihan">Program Pilihan</option>
                    </select>
                </div>

                <!-- Multi-select Tingkat Kelas -->
                <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800">
                            Tingkat Kelas Diampu <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" onclick="toggleAllTingkat()" id="btnToggleAll" class="text-[11px] font-bold text-brand-600 hover:text-brand-800 hover:underline">
                            Pilih Semua
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Bisa memilih 1, 2, atau seluruh tingkat kelas sekaligus (misal: mata pelajaran diajarkan di <strong>Kelas 10 dan 12</strong> bersamaan).
                    </p>

                    <div class="grid grid-cols-3 gap-2.5 pt-1">
                        <!-- Kelas X -->
                        <label id="card_t_x" class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-200 bg-white cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition shadow-sm select-none">
                            <input type="checkbox" name="tingkat[]" value="X" id="chk_tingkat_X" class="sr-only" onchange="updateTingkatCards()">
                            <span class="text-base font-black text-blue-600 font-display">X</span>
                            <span class="text-[10px] font-bold text-slate-600 mt-0.5">Kelas 10</span>
                            <span id="badge_t_x" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center bg-white text-transparent text-[9px] transition">✓</span>
                        </label>

                        <!-- Kelas XI -->
                        <label id="card_t_xi" class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-200 bg-white cursor-pointer hover:border-amber-300 hover:bg-amber-50/30 transition shadow-sm select-none">
                            <input type="checkbox" name="tingkat[]" value="XI" id="chk_tingkat_XI" class="sr-only" onchange="updateTingkatCards()">
                            <span class="text-base font-black text-amber-600 font-display">XI</span>
                            <span class="text-[10px] font-bold text-slate-600 mt-0.5">Kelas 11</span>
                            <span id="badge_t_xi" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center bg-white text-transparent text-[9px] transition">✓</span>
                        </label>

                        <!-- Kelas XII -->
                        <label id="card_t_xii" class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-200 bg-white cursor-pointer hover:border-emerald-300 hover:bg-emerald-50/30 transition shadow-sm select-none">
                            <input type="checkbox" name="tingkat[]" value="XII" id="chk_tingkat_XII" class="sr-only" onchange="updateTingkatCards()">
                            <span class="text-base font-black text-emerald-600 font-display">XII</span>
                            <span class="text-[10px] font-bold text-slate-600 mt-0.5">Kelas 12</span>
                            <span id="badge_t_xii" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center bg-white text-transparent text-[9px] transition">✓</span>
                        </label>
                    </div>

                    <div id="tingkatAlert" class="hidden text-[11px] font-bold text-rose-600 pt-1">
                        * Wajib memilih minimal satu tingkat kelas!
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-soft-sm transition">
                        Simpan Mata Pelajaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('mapelModal');
        const form = document.getElementById('mapelForm');
        const title = document.getElementById('modalTitle');

        function setCardActive(cardId, badgeId, isChecked, activeCardClass, activeBadgeClass) {
            const card = document.getElementById(cardId);
            const badge = document.getElementById(badgeId);
            if (isChecked) {
                card.className = "relative flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition shadow-sm select-none " + activeCardClass;
                badge.className = "absolute top-1.5 right-1.5 w-4 h-4 rounded-full border flex items-center justify-center text-[10px] font-bold " + activeBadgeClass;
            } else {
                card.className = "relative flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-200 bg-white cursor-pointer hover:bg-slate-50 transition shadow-sm select-none";
                badge.className = "absolute top-1.5 right-1.5 w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center bg-white text-transparent text-[10px]";
            }
        }

        function updateTingkatCards() {
            const chkX = document.getElementById('chk_tingkat_X');
            const chkXI = document.getElementById('chk_tingkat_XI');
            const chkXII = document.getElementById('chk_tingkat_XII');

            setCardActive('card_t_x', 'badge_t_x', chkX.checked, 'border-blue-500 bg-blue-50/60 shadow-blue-100', 'bg-blue-600 text-white border-blue-600');
            setCardActive('card_t_xi', 'badge_t_xi', chkXI.checked, 'border-amber-500 bg-amber-50/60 shadow-amber-100', 'bg-amber-600 text-white border-amber-600');
            setCardActive('card_t_xii', 'badge_t_xii', chkXII.checked, 'border-emerald-500 bg-emerald-50/60 shadow-emerald-100', 'bg-emerald-600 text-white border-emerald-600');

            const btnAll = document.getElementById('btnToggleAll');
            if (chkX.checked && chkXI.checked && chkXII.checked) {
                btnAll.innerText = 'Batal Pilih Semua';
            } else {
                btnAll.innerText = 'Pilih Semua';
            }

            const alertEl = document.getElementById('tingkatAlert');
            if (!chkX.checked && !chkXI.checked && !chkXII.checked) {
                alertEl.classList.remove('hidden');
            } else {
                alertEl.classList.add('hidden');
            }
        }

        function toggleAllTingkat() {
            const chkX = document.getElementById('chk_tingkat_X');
            const chkXI = document.getElementById('chk_tingkat_XI');
            const chkXII = document.getElementById('chk_tingkat_XII');
            const allChecked = chkX.checked && chkXI.checked && chkXII.checked;
            
            chkX.checked = !allChecked;
            chkXI.checked = !allChecked;
            chkXII.checked = !allChecked;
            updateTingkatCards();
        }

        function setTingkatSelection(tingkatStr) {
            const chkX = document.getElementById('chk_tingkat_X');
            const chkXI = document.getElementById('chk_tingkat_XI');
            const chkXII = document.getElementById('chk_tingkat_XII');
            
            const str = (tingkatStr || 'SEMUA').toUpperCase().trim();
            if (str === 'SEMUA') {
                chkX.checked = true;
                chkXI.checked = true;
                chkXII.checked = true;
            } else {
                const parts = str.split(',').map(s => s.trim());
                chkX.checked = parts.includes('X');
                chkXI.checked = parts.includes('XI');
                chkXII.checked = parts.includes('XII');
            }
            updateTingkatCards();
        }

        function openCreateModal() {
            title.innerText = 'Tambah Mata Pelajaran Baru';
            form.action = '<?= App::baseUrl('admin/mapel/store') ?>';
            document.getElementById('m_kode_mapel').value = '';
            document.getElementById('m_nama_mapel').value = '';
            document.getElementById('m_kelompok').value = 'Umum';
            setTingkatSelection('SEMUA');
            modal.classList.remove('hidden');
        }

        function openEditModal(mapel) {
            title.innerText = 'Edit Mata Pelajaran';
            form.action = '<?= App::baseUrl('admin/mapel/update/') ?>' + mapel.id;
            document.getElementById('m_kode_mapel').value = mapel.kode_mapel || '';
            document.getElementById('m_nama_mapel').value = mapel.nama_mapel || '';
            document.getElementById('m_kelompok').value = mapel.kelompok || 'Umum';
            setTingkatSelection(mapel.tingkat || 'SEMUA');
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        form.onsubmit = function(e) {
            const chkX = document.getElementById('chk_tingkat_X');
            const chkXI = document.getElementById('chk_tingkat_XI');
            const chkXII = document.getElementById('chk_tingkat_XII');
            if (!chkX.checked && !chkXI.checked && !chkXII.checked) {
                e.preventDefault();
                document.getElementById('tingkatAlert').classList.remove('hidden');
                alert('Silakan pilih minimal satu tingkat kelas untuk mata pelajaran ini!');
                return false;
            }
        };

        window.onclick = function(e) {
            if (e.target === modal) closeModal();
        }
    </script>
</body>
</html>
