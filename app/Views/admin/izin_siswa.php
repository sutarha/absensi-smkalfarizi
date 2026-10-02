<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$activeNav = 'izin-siswa';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin Siswa - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK') ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Modern Admin Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full relative min-w-0 transition-all duration-300">
        <!-- Modern Topbar -->
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/60 sticky top-0 z-30 shadow-sm">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button id="mobileMenuBtn" class="p-2 -ml-2 rounded-xl text-slate-500 hover:bg-slate-100 md:hidden transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 font-display">Pengajuan Izin Siswa</h1>
                        <p class="text-sm text-slate-500 hidden sm:block">Kelola permohonan izin/sakit siswa dari PWA</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-3 px-4 py-2 bg-slate-50 rounded-full border border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold">
                            <?= strtoupper(substr($user['nama_lengkap'] ?? $user['username'], 0, 1)) ?>
                        </div>
                        <div class="text-sm font-semibold text-slate-700">
                            <?= htmlspecialchars($user['nama_lengkap'] ?? $user['username']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Scrollable -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
            
            <!-- Filter & Action Bar -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex gap-2">
                    <a href="?status=" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $status === '' ? 'bg-slate-800 text-white shadow-soft-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">Semua</a>
                    <a href="?status=MENUNGGU" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $status === 'MENUNGGU' ? 'bg-amber-500 text-white shadow-soft-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">Menunggu</a>
                    <a href="?status=DISETUJUI" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $status === 'DISETUJUI' ? 'bg-emerald-600 text-white shadow-soft-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">Disetujui</a>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 animate-fade-in shadow-sm">
                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0 text-emerald-600">✓</div>
                    <div class="text-sm font-medium"><?= $_SESSION['flash_success'] ?></div>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 animate-fade-in shadow-sm">
                    <div class="w-8 h-8 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0 text-rose-600">✕</div>
                    <div class="text-sm font-medium"><?= $_SESSION['flash_error'] ?></div>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Table Card -->
            <div class="bg-white rounded-3xl shadow-soft-xl border border-slate-200/60 overflow-hidden flex flex-col animate-slide-up" style="animation-delay: 0.1s;">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider">
                                <th class="p-4 font-bold whitespace-nowrap">Tanggal Pengajuan</th>
                                <th class="p-4 font-bold whitespace-nowrap">Siswa & Kelas</th>
                                <th class="p-4 font-bold whitespace-nowrap">Jenis Izin</th>
                                <th class="p-4 font-bold whitespace-nowrap">Tanggal Izin</th>
                                <th class="p-4 font-bold">Alasan</th>
                                <th class="p-4 font-bold text-center">Lampiran</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (empty($pengajuanList)): ?>
                            <tr>
                                <td colspan="8" class="p-12 text-center">
                                    <div class="text-4xl mb-3">📭</div>
                                    <div class="text-slate-600 font-bold text-sm">Tidak ada data pengajuan.</div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($pengajuanList as $p): ?>
                                <tr class="hover:bg-slate-50/50 transition group">
                                    <td class="p-4">
                                        <div class="text-slate-900 font-medium"><?= date('d M Y', strtotime($p['created_at'])) ?></div>
                                        <div class="text-[10px] text-slate-500 mt-0.5"><?= date('H:i', strtotime($p['created_at'])) ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($p['nama_siswa']) ?></div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 font-mono">NISN: <?= htmlspecialchars($p['nisn']) ?> • Kelas: <?= htmlspecialchars($p['nama_kelas']) ?></div>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($p['jenis_izin'] === 'SAKIT'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">SAKIT</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">IZIN</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-xs font-medium text-slate-700">
                                        <?php if ($p['tanggal_mulai'] === $p['tanggal_selesai']): ?>
                                            <span class="bg-slate-100 px-2 py-1 rounded-md text-slate-600 border border-slate-200 whitespace-nowrap"><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></span>
                                        <?php else: ?>
                                            <span class="bg-slate-100 px-2 py-1 rounded-md text-slate-600 border border-slate-200 whitespace-nowrap"><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?> s/d <?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-xs text-slate-600 max-w-[200px] line-clamp-2" title="<?= htmlspecialchars($p['alasan']) ?>">
                                            <?= htmlspecialchars($p['alasan']) ?>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if (!empty($p['file_lampiran'])): ?>
                                            <a href="<?= App::baseUrl($p['file_lampiran']) ?>" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition" title="Lihat Lampiran">
                                                📄
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($p['status'] === 'MENUNGGU'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">⏳ MENUNGGU</span>
                                        <?php elseif ($p['status'] === 'DISETUJUI'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">✓ DISETUJUI</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">✕ DITOLAK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if ($p['status'] === 'MENUNGGU'): ?>
                                            <button type="button" onclick="bukaModalProses(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_siswa'], ENT_QUOTES) ?>')" class="px-3 py-1.5 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs transition border border-brand-200">
                                                Proses
                                            </button>
                                        <?php else: ?>
                                            <div class="text-[10px] text-slate-400">
                                                Oleh: <?= htmlspecialchars($p['nama_guru_pemroses'] ?? '-') ?><br>
                                                <?= date('d/m H:i', strtotime($p['diproses_at'])) ?>
                                            </div>
                                        <?php endif; ?>
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

    <!-- Modal Proses Izin -->
    <div id="modalProsesIzin" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="tutupModalProses()"></div>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md w-full">
                <form action="<?= App::baseUrl('admin/izin-siswa/proses') ?>" method="POST">
                    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    <input type="hidden" name="id" id="formIzinId" value="">
                    
                    <div class="bg-white px-6 pt-6 pb-6">
                        <div class="flex justify-between items-start mb-5">
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-900 font-display">Proses Izin Siswa</h3>
                                <p class="text-sm text-slate-500 mt-1">Siswa: <span class="font-bold text-slate-800" id="formNamaSiswa">-</span></p>
                            </div>
                            <button type="button" onclick="tutupModalProses()" class="text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Keputusan *</label>
                                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all text-sm font-medium text-slate-700" required>
                                    <option value="DISETUJUI">Setujui (Update status kehadiran otomatis)</option>
                                    <option value="DITOLAK">Tolak Pengajuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Catatan/Komentar (Opsional)</label>
                                <textarea name="catatan_guru" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all text-sm" placeholder="Contoh: Bawa surat dokter asli ke sekolah..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl border-t border-slate-100">
                        <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 rounded-xl border border-transparent text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 focus:ring-4 focus:ring-brand-500/30 transition-all shadow-soft-sm">
                            Simpan Keputusan
                        </button>
                        <button type="button" onclick="tutupModalProses()" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 focus:ring-4 focus:ring-slate-200/50 transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function bukaModalProses(id, nama) {
        document.getElementById('formIzinId').value = id;
        document.getElementById('formNamaSiswa').textContent = nama;
        document.getElementById('modalProsesIzin').classList.remove('hidden');
    }
    
    function tutupModalProses() {
        document.getElementById('modalProsesIzin').classList.add('hidden');
    }
</script>

</body>
</html>
