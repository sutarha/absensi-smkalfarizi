<?php
use App\Config\App;

$activeNav = 'kelas';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Kelas & Rombel - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
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
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Master Data Kelas & Rombongan Belajar</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data kelas, tingkat pendidikan, wali kelas, dan program keahlian di <?= htmlspecialchars($config['nama_sekolah']) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openCreateModal()" 
                        class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Kelas Baru</span>
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

            <!-- Table Kelas -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Rombongan Belajar Aktif</span>
                    <span class="text-xs text-slate-500 font-medium">Total: <strong class="text-slate-800 font-bold"><?= count($kelasList) ?></strong> kelas</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">Nama Kelas</th>
                                <th class="py-3.5 px-4">Tingkat</th>
                                <th class="py-3.5 px-4">Kompetensi Keahlian / Jurusan</th>
                                <th class="py-3.5 px-4">Wali Kelas</th>
                                <th class="py-3.5 px-4 text-center">Jumlah Siswa</th>
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($kelasList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <div class="text-2xl mb-1">🏫</div>
                                    <p class="font-medium">Belum ada data kelas yang dibuat.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($kelasList as $k): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($k['nama_kelas']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            Kelas <?= htmlspecialchars($k['tingkat']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-medium"><?= htmlspecialchars($k['jurusan']) ?></td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($k['nama_wali_kelas'])): ?>
                                            <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                                <span>👨‍🏫</span>
                                                <span><?= htmlspecialchars($k['nama_wali_kelas']) ?></span>
                                            </div>
                                            <?php if (!empty($k['nip_wali_kelas'])): ?>
                                                <div class="text-[10px] text-slate-400 font-mono">NIP: <?= htmlspecialchars($k['nip_wali_kelas']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">Belum Ditugaskan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 font-bold text-xs bg-brand-50 text-brand-700 px-2.5 py-1 rounded-full border border-brand-100">
                                            <?= (int)$k['total_siswa'] ?> Siswa
                                        </span>
                                    </td>
                                     <td class="py-3 px-4 text-center">
                                         <div class="inline-flex items-center gap-1.5 justify-center">
                                             <!-- Tombol Plotting Mapel & Guru -->
                                             <button onclick='openPlottingModal(<?= json_encode($k) ?>)' 
                                                     title="Kelola & Plotting Mata Pelajaran serta Guru Pengampu Kelas Ini"
                                                     class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 transition shadow-soft-sm">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                 <span>Plotting Mapel</span>
                                             </button>

                                             <!-- Link Langsung Jadwal KBM Kelas -->
                                             <a href="<?= App::baseUrl("admin/jadwal?kelas_id={$k['id']}") ?>" 
                                                title="Lihat Jadwal Pelajaran Kelas Ini"
                                                class="p-1.5 rounded-xl text-brand-600 hover:text-brand-700 hover:bg-brand-50 border border-brand-200 transition" target="_blank">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                             </a>

                                             <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($k), ENT_QUOTES, 'UTF-8') ?>)" 
                                                     title="Edit Kelas"
                                                     class="p-1.5 rounded-xl text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition border border-slate-200">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                             </button>
                                             <form action="<?= App::baseUrl("admin/kelas/delete/{$k['id']}") ?>" method="POST"
                                                   onsubmit="return confirm('Menghapus kelas ini akan berdampak pada siswa di dalamnya. Yakin?')">
                                                 <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                                 <button type="submit" 
                                                         title="Hapus Kelas"
                                                         class="p-1.5 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition border border-slate-200">
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

     <!-- Modal Form Kelas -->
     <div id="kelasModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
         <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 transition-all">
             <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                 <h3 id="modalTitle" class="font-display font-bold text-base text-slate-900">Tambah Kelas Baru</h3>
                 <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                     &times;
                 </button>
             </div>

             <form id="kelasForm" method="POST" action="<?= App::baseUrl('admin/kelas/store') ?>" class="space-y-4">
                 <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                 <div>
                     <label class="block text-xs font-bold text-slate-700 mb-1">Nama Rombel / Kelas</label>
                     <input type="text" name="nama_kelas" id="m_nama_kelas" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Contoh: X PPLG 1" required>
                 </div>

                 <div>
                     <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Pendidikan</label>
                     <select name="tingkat" id="m_tingkat" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                         <option value="X">Kelas X (Sepuluh)</option>
                         <option value="XI">Kelas XI (Sebelas)</option>
                         <option value="XII">Kelas XII (Dua Belas)</option>
                     </select>
                 </div>

                 <div>
                     <label class="block text-xs font-bold text-slate-700 mb-1">Kompetensi Keahlian / Jurusan</label>
                     <input type="text" name="jurusan" id="m_jurusan" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                 </div>

                 <div>
                     <label class="block text-xs font-bold text-slate-700 mb-1">Wali Kelas (Guru)</label>
                     <select name="wali_kelas_guru_id" id="m_wali_kelas_guru_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition">
                         <option value="">-- Pilih Guru Wali Kelas --</option>
                         <?php foreach ($guruList as $g): ?>
                             <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?> (<?= htmlspecialchars($g['nik_nip']) ?>)</option>
                         <?php endforeach; ?>
                     </select>
                 </div>

                 <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                     <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                         Batal
                     </button>
                     <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-soft-sm transition">
                         Simpan Kelas
                     </button>
                 </div>
             </form>
         </div>
     </div>

     <!-- Modal Plotting Mapel & Guru Kelas -->
     <div id="plottingModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
         <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 transition-all max-h-[90vh] flex flex-col">
             <!-- Modal Header -->
             <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                 <div>
                     <h3 id="plotModalTitle" class="font-display font-black text-base text-slate-900 flex items-center gap-2">
                         <span class="w-7 h-7 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">📚</span>
                         <span>Plotting Mapel & Guru Pengampu</span>
                     </h3>
                     <p id="plotModalSubtitle" class="text-xs text-slate-500 mt-0.5">Tentukan mata pelajaran, alokasi jam (JP), dan guru pengampu untuk kelas ini.</p>
                 </div>
                 <button type="button" onclick="closePlottingModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                     &times;
                 </button>
             </div>

             <!-- Modal Body -->
             <div class="overflow-y-auto py-4 space-y-5 flex-1">
                 <!-- Form Pasang Mapel & Guru -->
                 <form id="plotForm" method="POST" action="" class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80 space-y-3">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                     <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                         <span>➕</span>
                         <span>Pasangkan Mata Pelajaran & Guru Baru ke Kelas Ini</span>
                     </div>
                     <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                         <div class="sm:col-span-6">
                             <label class="block text-[11px] font-bold text-slate-600 mb-1">Mata Pelajaran Kurikulum</label>
                             <select name="mapel_id" id="plot_mapel_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none transition" required>
                                 <option value="">-- Pilih Mata Pelajaran --</option>
                                 <?php foreach ($mapelList as $m): 
                                     $labelTingkat = ($m['tingkat'] === 'SEMUA') ? 'Semua Tingkat' : "Tingkat {$m['tingkat']}";
                                 ?>
                                 <option value="<?= $m['id'] ?>" data-tingkat="<?= htmlspecialchars($m['tingkat']) ?>">
                                     [<?= htmlspecialchars($m['kode_mapel']) ?>] <?= htmlspecialchars($m['nama_mapel']) ?> (<?= htmlspecialchars($labelTingkat) ?>)
                                 </option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                         <div class="sm:col-span-4">
                             <label class="block text-[11px] font-bold text-slate-600 mb-1">Guru Pengampu</label>
                             <select name="guru_id" id="plot_guru_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none transition" required>
                                 <option value="">-- Pilih Guru --</option>
                                 <?php foreach ($guruList as $g): ?>
                                 <option value="<?= $g['id'] ?>">
                                     <?= htmlspecialchars($g['nama_lengkap']) ?>
                                 </option>
                                 <?php endforeach; ?>
                             </select>
                         </div>
                         <div class="sm:col-span-2">
                             <label class="block text-[11px] font-bold text-slate-600 mb-1">Beban JP</label>
                             <input type="number" name="alokasi_jp" id="plot_alokasi_jp" value="2" min="1" max="10" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none transition text-center" required>
                         </div>
                     </div>
                     <div class="flex justify-end pt-1">
                         <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-soft-sm transition">
                             <span>Simpan Plotting Mapel</span>
                         </button>
                     </div>
                 </form>

                 <!-- Tabel Daftar Mapel Terpasang -->
                 <div>
                     <div class="flex items-center justify-between mb-2">
                         <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Mata Pelajaran yang Diajarkan di Kelas Ini:</span>
                         <span id="plotTotalCount" class="text-xs text-slate-500 font-semibold">0 Mapel</span>
                     </div>
                     <div id="plotLoading" class="text-center py-6 text-slate-400 text-xs font-semibold">
                         <div class="animate-spin inline-block w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full mb-2"></div>
                         <p>Memuat data plotting mapel...</p>
                     </div>
                     <div id="plotEmpty" class="hidden text-center py-8 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                         <div class="text-2xl mb-1">📖</div>
                         <p class="font-medium">Belum ada mata pelajaran yang dipasangkan ke kelas ini.</p>
                         <p class="text-[11px] text-slate-400 mt-0.5">Gunakan formulir di atas untuk menambahkan mapel & guru pengampu.</p>
                     </div>
                     <div id="plotTableWrapper" class="hidden bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-soft-sm">
                         <table class="w-full text-left border-collapse text-xs">
                             <thead>
                                 <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                     <th class="py-2.5 px-3">Mata Pelajaran</th>
                                     <th class="py-2.5 px-3">Guru Pengampu</th>
                                     <th class="py-2.5 px-3 text-center">Beban JP</th>
                                     <th class="py-2.5 px-3 text-center">Jadwal KBM</th>
                                     <th class="py-2.5 px-3 text-center w-20">Aksi</th>
                                 </tr>
                             </thead>
                             <tbody id="plotTableBody" class="divide-y divide-slate-100">
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>

             <!-- Modal Footer -->
             <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                 <a id="btnLihatJadwalLengkap" href="#" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700">
                     <span>📅 Buka Jadwal KBM Kelas Ini di Tab Baru ➜</span>
                 </a>
                 <button type="button" onclick="closePlottingModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                     Tutup
                 </button>
             </div>
         </div>
     </div>

     <script>
         const modal = document.getElementById('kelasModal');
         const form = document.getElementById('kelasForm');

         const plotModal = document.getElementById('plottingModal');
         const plotForm = document.getElementById('plotForm');
         let currentPlottingKelas = null;

         function openCreateModal() {
             document.getElementById('modalTitle').textContent = 'Tambah Kelas Baru';
             form.action = '<?= App::baseUrl('admin/kelas/store') ?>';
             form.reset();
             modal.classList.remove('hidden');
             modal.classList.add('flex');
         }

         function openEditModal(k) {
             document.getElementById('modalTitle').textContent = 'Edit Kelas: ' + k.nama_kelas;
             form.action = '<?= App::baseUrl('admin/kelas/update/') ?>' + k.id;
             document.getElementById('m_nama_kelas').value = k.nama_kelas || '';
             document.getElementById('m_tingkat').value = k.tingkat || 'X';
             document.getElementById('m_jurusan').value = k.jurusan || '';
             document.getElementById('m_wali_kelas_guru_id').value = k.wali_kelas_guru_id || '';
             modal.classList.remove('hidden');
             modal.classList.add('flex');
         }

         function closeModal() {
             modal.classList.add('hidden');
             modal.classList.remove('flex');
         }

         // Open Plotting Modal
         function openPlottingModal(k) {
             currentPlottingKelas = k;
             document.getElementById('plotModalTitle').innerHTML = `
                 <span class="w-7 h-7 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">📚</span>
                 <span>Plotting Mapel & Guru: ${escapeHtml(k.nama_kelas)}</span>
             `;
             document.getElementById('plotModalSubtitle').textContent = `Tingkat ${k.tingkat} • ${k.jurusan} — Tentukan mata pelajaran, alokasi jam (JP), dan guru pengampu.`;
             plotForm.action = '<?= App::baseUrl('admin/kelas/') ?>' + k.id + '/mapel/store';
             document.getElementById('btnLihatJadwalLengkap').href = '<?= App::baseUrl('admin/jadwal?kelas_id=') ?>' + k.id;
             
             // Filter mapel dropdown options according to class tingkat
             filterMapelOptionsByTingkat(k.tingkat);

             plotModal.classList.remove('hidden');
             plotModal.classList.add('flex');

             loadKelasMapel(k.id);
         }

         function closePlottingModal() {
             plotModal.classList.add('hidden');
             plotModal.classList.remove('flex');
         }

         function filterMapelOptionsByTingkat(tingkatKelas) {
             const sel = document.getElementById('plot_mapel_id');
             const opts = sel.querySelectorAll('option');
             opts.forEach(opt => {
                 if (!opt.value) return; // Keep placeholder
                 const t = (opt.dataset.tingkat || 'SEMUA').toUpperCase();
                 if (t === 'SEMUA') {
                     opt.hidden = false;
                 } else {
                     const parts = t.split(',').map(s => s.trim());
                     opt.hidden = !parts.includes(tingkatKelas);
                 }
             });
             sel.value = '';
         }

         function loadKelasMapel(kelasId) {
             const loading = document.getElementById('plotLoading');
             const emptyBox = document.getElementById('plotEmpty');
             const tableWrapper = document.getElementById('plotTableWrapper');
             const tableBody = document.getElementById('plotTableBody');
             const countLabel = document.getElementById('plotTotalCount');

             loading.classList.remove('hidden');
             emptyBox.classList.add('hidden');
             tableWrapper.classList.add('hidden');
             tableBody.innerHTML = '';

             fetch('<?= App::baseUrl('admin/kelas/') ?>' + kelasId + '/mapel-json')
                 .then(r => r.json())
                 .then(res => {
                     loading.classList.add('hidden');
                     if (res.status === 'success' && res.data && res.data.length > 0) {
                         countLabel.textContent = `${res.data.length} Mapel Terpasang`;
                         tableWrapper.classList.remove('hidden');

                         res.data.forEach(item => {
                             const tr = document.createElement('tr');
                             tr.className = 'hover:bg-slate-50 transition';

                             const statusBadge = (parseInt(item.jp_terjadwal) > 0) 
                                 ? `<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✅ Terjadwal ${item.jp_terjadwal} JP</span>`
                                 : `<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">⏳ Belum Terjadwal</span>`;

                             tr.innerHTML = `
                                 <td class="py-2.5 px-3">
                                     <div class="font-bold text-slate-800">${escapeHtml(item.nama_mapel)}</div>
                                     <div class="text-[10px] font-mono text-slate-400">[${escapeHtml(item.kode_mapel)}] • ${escapeHtml(item.kelompok)}</div>
                                 </td>
                                 <td class="py-2.5 px-3 font-semibold text-slate-700">
                                     👨‍🏫 ${escapeHtml(item.nama_guru)}
                                 </td>
                                 <td class="py-2.5 px-3 text-center">
                                     <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                         ${item.alokasi_jp} JP
                                     </span>
                                 </td>
                                 <td class="py-2.5 px-3 text-center">
                                     ${statusBadge}
                                 </td>
                                 <td class="py-2.5 px-3 text-center">
                                     <div class="inline-flex items-center gap-1 justify-center">
                                         <a href="<?= App::baseUrl('admin/jadwal?kelas_id=') ?>${kelasId}&mapel_id=${item.mapel_id}" 
                                            title="Jadwalkan ke KBM"
                                            class="p-1 rounded-lg text-brand-600 hover:bg-brand-50 transition">
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                         </a>
                                         <form action="<?= App::baseUrl('admin/kelas/mapel/delete/') ?>${item.id}" method="POST" onsubmit="return confirm('Hapus plotting mapel ${escapeHtml(item.nama_mapel)} dari kelas ini?')">
                                             <button type="submit" title="Hapus Plotting" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                             </button>
                                         </form>
                                     </div>
                                 </td>
                             `;
                             tableBody.appendChild(tr);
                         });
                     } else {
                         countLabel.textContent = '0 Mapel';
                         emptyBox.classList.remove('hidden');
                     }
                 })
                 .catch(err => {
                     loading.classList.add('hidden');
                     emptyBox.classList.remove('hidden');
                     console.error(err);
                 });
         }

         function escapeHtml(str) {
             if (!str) return '';
             return String(str).replace(/[&<>"']/g, function(m) {
                 return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[m];
             });
         }

         window.onclick = function(e) {
             if (e.target === modal) closeModal();
             if (e.target === plotModal) closePlottingModal();
         }

         // Auto open plotting modal if URL has ?open_mapel=ID
         document.addEventListener('DOMContentLoaded', () => {
             const params = new URLSearchParams(window.location.search);
             const openMapelKelasId = params.get('open_mapel');
             if (openMapelKelasId) {
                 <?php foreach ($kelasList as $k): ?>
                 if ('<?= $k['id'] ?>' === openMapelKelasId) {
                     openPlottingModal(<?= json_encode($k) ?>);
                 }
                 <?php endforeach; ?>
             }
         });
     </script>
 </body>
 </html>
