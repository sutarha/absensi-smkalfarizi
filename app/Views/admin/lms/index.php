<?php
use App\Config\App;
$activeNav = 'lms';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'LMS Monitoring') ?></title>
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-50 font-sans text-slate-900 flex h-screen overflow-hidden">
    <?php require __DIR__ . '/../../partials/admin_sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 shadow-sm z-10 flex-shrink-0">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    <button id="mobileMenuBtn" class="lg:hidden text-slate-500 hover:text-brand-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Monitoring LMS</h2>
                        <p class="text-sm text-slate-500">Pantau aktivitas pembelajaran & tugas</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                        <div class="text-xs text-slate-500 capitalize"><?= htmlspecialchars($user['role']) ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold border border-brand-200">
                        <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-6">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <span class="text-brand-600">📚</span> Daftar Materi & Tugas Semua Kelas
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="p-4 font-semibold border-b border-slate-200">Tipe</th>
                                    <th class="p-4 font-semibold border-b border-slate-200">Materi / Tugas</th>
                                    <th class="p-4 font-semibold border-b border-slate-200">Guru</th>
                                    <th class="p-4 font-semibold border-b border-slate-200">Mapel & Kelas</th>
                                    <th class="p-4 font-semibold border-b border-slate-200 text-center">Pengumpulan</th>
                                    <th class="p-4 font-semibold border-b border-slate-200">Tgl Dibuat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($data['lmsList'])): ?>
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-500">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                                <span>Belum ada aktivitas LMS.</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data['lmsList'] as $row): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-4">
                                            <?php if ($row['tipe'] === 'tugas'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">TUGAS</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">MATERI</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($row['judul']) ?></div>
                                            <div class="text-xs text-slate-500 truncate max-w-xs"><?= htmlspecialchars(substr($row['deskripsi'], 0, 50)) ?>...</div>
                                        </td>
                                        <td class="p-4 text-sm font-medium text-slate-700">
                                            <?= htmlspecialchars($row['nama_guru']) ?>
                                        </td>
                                        <td class="p-4">
                                            <div class="text-sm font-bold text-slate-700"><?= htmlspecialchars($row['nama_mapel']) ?></div>
                                            <div class="text-xs font-medium text-brand-600 bg-brand-50 inline-block px-2 py-0.5 rounded mt-1">
                                                <?= htmlspecialchars($row['nama_kelas']) ?>
                                            </div>
                                        </td>
                                        <td class="p-4 text-center">
                                            <?php if ($row['tipe'] === 'tugas'): ?>
                                                <span class="font-bold text-emerald-600 text-lg"><?= $row['total_submission'] ?></span>
                                                <span class="text-xs text-slate-400 block">Siswa</span>
                                            <?php else: ?>
                                                <span class="text-slate-300">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-sm text-slate-500 whitespace-nowrap">
                                            <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
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
    </div>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (mobileMenuBtn && sidebar && overlay) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
