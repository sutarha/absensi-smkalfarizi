<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$activeNav = 'guru';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Guru - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
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
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Master Data Guru & Pegawai</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola akun guru mata pelajaran, guru piket, hak akses, dan tunjangan tugas tambahan</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="<?= App::baseUrl('admin/guru/download-template') ?>" class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-emerald-200">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Template Excel (.xlsx)</span>
                </a>
                <a href="<?= App::baseUrl('admin/guru/import') ?>" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-emerald transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Import Guru Dari Dapodik</span>
                </a>
                <button onclick="openCreateModal()" 
                        class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Guru Baru</span>
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

            <!-- Table Guru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Akun Tenaga Pendidik & Kependidikan</span>
                    <span class="text-xs text-slate-500 font-medium">Total: <strong class="text-slate-800 font-bold"><?= count($guruList) ?></strong> orang</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">NIK / NIP</th>
                                <th class="py-3.5 px-4">Nama Lengkap & Gelar</th>
                                <th class="py-3.5 px-4">Username</th>
                                <th class="py-3.5 px-4">Hak Akses</th>
                                <th class="py-3.5 px-4">Tugas Tambahan</th>
                                <th class="py-3.5 px-4">Tunjangan</th>
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($guruList)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    <div class="text-2xl mb-1">👨‍🏫</div>
                                    <p class="font-medium">Belum ada data guru terdaftar.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($guruList as $g): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($g['nik_nip']) ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($g['nama_lengkap']) ?></div>
                                        <?php if (!empty($g['no_hp'])): ?>
                                        <div class="text-[11px] text-slate-400 mt-0.5">📞 <?= htmlspecialchars($g['no_hp']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600">
                                        <span class="bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200 font-medium text-[11px]">
                                            <?= htmlspecialchars($g['username']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($g['role'] === 'admin'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                Super Admin
                                            </span>
                                        <?php elseif ($g['role'] === 'piket'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Guru Piket
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Guru Mapel
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-medium">
                                        <?= htmlspecialchars($g['tugas_tambahan'] ?: '-') ?>
                                    </td>
                                    <td class="py-3 px-4 font-display font-semibold text-slate-900">
                                        <?= TimeHelper::formatRupiah($g['tunjangan_tugas']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 justify-center">
                                            <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($g), ENT_QUOTES, 'UTF-8') ?>)" 
                                                    title="Edit Data Guru"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <form action="<?= App::baseUrl("admin/guru/delete/{$g['id']}") ?>" method="POST" 
                                                  onsubmit="return confirm('Yakin ingin menghapus guru ini?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                                <button type="submit" 
                                                        title="Hapus Guru"
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

    <!-- Modal Form Guru -->
    <div id="guruModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto transition-all">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 id="modalTitle" class="font-display font-bold text-base text-slate-900">Tambah Guru Baru</h3>
                <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                    &times;
                </button>
            </div>

            <form id="guruForm" method="POST" action="<?= App::baseUrl('admin/guru/store') ?>" class="space-y-4">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIK / NIP</label>
                    <input type="text" name="nik_nip" id="m_nik_nip" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Nomor Induk Kepegawaian" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="nama_lengkap" id="m_nama_lengkap" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Contoh: Budi Santoso, S.Kom." required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username</label>
                        <input type="text" name="username" id="m_username" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Username login" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Password <span id="passHelp" class="text-[11px] font-normal text-slate-400"></span>
                        </label>
                        <input type="password" name="password" id="m_password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Default: guru123">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Hak Akses (Role)</label>
                        <select name="role" id="m_role" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                            <option value="guru">Guru Mata Pelajaran</option>
                            <option value="piket">Guru Piket</option>
                            <option value="kepala_sekolah">Kepala Sekolah</option>
                            <option value="wakasek_kurikulum">Wakasek Kurikulum</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="admin">Super Admin / TU</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Handphone / WhatsApp</label>
                        <input type="text" name="no_hp" id="m_no_hp" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="08xxxxxxxx">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tugas Tambahan</label>
                        <input type="text" name="tugas_tambahan" id="m_tugas_tambahan" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Misal: Wali Kelas X RPL 1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tunjangan Tugas (Rp)</label>
                        <input type="number" step="1000" name="tunjangan_tugas" id="m_tunjangan_tugas" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="0">
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-soft-sm transition">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('guruModal');
        const form = document.getElementById('guruForm');

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Guru Baru';
            form.action = '<?= App::baseUrl('admin/guru/store') ?>';
            form.reset();
            document.getElementById('passHelp').textContent = '(default: guru123)';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function openEditModal(guru) {
            document.getElementById('modalTitle').textContent = 'Edit Data Guru: ' + guru.nama_lengkap;
            form.action = '<?= App::baseUrl('admin/guru/update/') ?>' + guru.id;
            document.getElementById('m_nik_nip').value = guru.nik_nip;
            document.getElementById('m_nama_lengkap').value = guru.nama_lengkap;
            document.getElementById('m_username').value = guru.username;
            document.getElementById('m_role').value = guru.role;
            document.getElementById('m_no_hp').value = guru.no_hp || '';
            document.getElementById('m_tugas_tambahan').value = guru.tugas_tambahan || '';
            document.getElementById('m_tunjangan_tugas').value = guru.tunjangan_tugas || 0;
            document.getElementById('m_password').value = '';
            document.getElementById('passHelp').textContent = '(kosongkan jika tidak ganti)';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</body>
</html>
