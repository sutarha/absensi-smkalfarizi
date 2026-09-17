<?php
use App\Config\App;
$activeNav = 'tabungan_monitoring';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Tabungan Siswa</title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-50 font-sans text-slate-900 flex h-screen overflow-hidden">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 shadow-sm z-10 flex-shrink-0">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    <button id="mobileMenuBtn" class="lg:hidden text-slate-500 hover:text-brand-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Monitoring Tabungan</h2>
                        <p class="text-sm text-slate-500">Pantau saldo & aktivitas tabungan siswa</p>
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

                <?php if(isset($_SESSION['flash_success'])): ?>
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="font-medium"><?= $_SESSION['flash_success'] ?></span>
                        <?php unset($_SESSION['flash_success']); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Kiri: Daftar Saldo Tabungan -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Filter Program -->
                        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                            <form method="GET" action="<?= App::baseUrl('admin/tabungan/monitoring') ?>" class="flex flex-col sm:flex-row gap-4 items-center">
                                <label for="program_id" class="text-sm font-bold text-slate-700 whitespace-nowrap">Pilih Program:</label>
                                <select name="program_id" id="program_id" class="w-full sm:w-auto bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-2.5 font-medium" onchange="this.form.submit()">
                                    <?php if(empty($programList)): ?>
                                        <option value="">Belum ada program tabungan</option>
                                    <?php else: ?>
                                        <?php foreach($programList as $prog): ?>
                                            <option value="<?= $prog['id'] ?>" <?= $prog['id'] == $selectedProgramId ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($prog['nama_program']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </form>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                    <span class="text-brand-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
                                    Saldo Tabungan Siswa
                                </h3>
                                <span class="bg-brand-100 text-brand-700 py-1 px-3 rounded-full text-xs font-bold">
                                    Total: <?= count($tabunganList) ?> siswa
                                </span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                            <th class="p-4 font-semibold border-b border-slate-200">Siswa / Kelas</th>
                                            <th class="p-4 font-semibold border-b border-slate-200">Program</th>
                                            <th class="p-4 font-semibold border-b border-slate-200 text-right">Saldo Terkumpul</th>
                                            <th class="p-4 font-semibold border-b border-slate-200 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if (empty($tabunganList)): ?>
                                            <tr>
                                                <td colspan="4" class="p-8 text-center text-slate-500">
                                                    Belum ada data tabungan siswa.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($tabunganList as $ts): ?>
                                                <?php
                                                    $progress = 0;
                                                    if ($ts['target_nominal'] > 0) {
                                                        $progress = ($ts['total_terkumpul'] / $ts['target_nominal']) * 100;
                                                        $progress = min(100, max(0, $progress));
                                                    }
                                                ?>
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="p-4">
                                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($ts['nama_siswa']) ?></div>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="text-xs font-medium text-slate-500"><?= htmlspecialchars($ts['nisn']) ?></span>
                                                            <span class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded">
                                                                <?= htmlspecialchars($ts['nama_kelas']) ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="p-4 text-sm font-medium text-slate-700">
                                                        <?= htmlspecialchars($ts['nama_program']) ?>
                                                    </td>
                                                    <td class="p-4 text-right">
                                                        <div class="font-bold text-emerald-600 text-base">
                                                            Rp <?= number_format($ts['total_terkumpul'], 0, ',', '.') ?>
                                                        </div>
                                                        <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2 overflow-hidden">
                                                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= $progress ?>%"></div>
                                                        </div>
                                                    </td>
                                                    <td class="p-4 text-center">
                                                        <?php if ($ts['status'] === 'berjalan'): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                Berjalan
                                                            </span>
                                                        <?php elseif ($ts['status'] === 'selesai'): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                                Selesai
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                                                <?= htmlspecialchars($ts['status']) ?>
                                                            </span>
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

                    <!-- Kanan: Aktivitas Transaksi Terbaru -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                    <span class="text-amber-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </span>
                                    10 Transaksi Terakhir
                                </h3>
                            </div>
                            <div class="p-5">
                                <?php if (empty($recentTransactions)): ?>
                                    <div class="text-center py-6 text-sm text-slate-500">Belum ada transaksi.</div>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($recentTransactions as $tx): ?>
                                            <div class="flex items-start gap-3 border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                                                <div class="mt-1 flex-shrink-0">
                                                    <?php if ($tx['jumlah'] >= 0): ?>
                                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-bold text-slate-800 truncate">
                                                        <?= htmlspecialchars($tx['nama_siswa']) ?>
                                                    </p>
                                                    <div class="flex justify-between items-center mt-0.5">
                                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($tx['nama_program']) ?></p>
                                                        <p class="text-xs font-bold <?= $tx['jumlah'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                                            <?= $tx['jumlah'] >= 0 ? '+' : '' ?> Rp <?= number_format($tx['jumlah'], 0, ',', '.') ?>
                                                        </p>
                                                    </div>
                                                    <p class="text-[10px] text-slate-400 mt-1">
                                                        <?= date('d M Y, H:i', strtotime($tx['created_at'])) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

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
