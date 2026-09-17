<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$activeNav = 'notifikasi';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Notifikasi & Pengumuman - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
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
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Pusat Notifikasi & Pengumuman</h1>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Kelola dan siarkan pengumuman langsung ke Guru, Siswa, atau Keduanya</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-xl">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span><?= TimeHelper::formatDateIndonesian(date('Y-m-d')) ?></span>
                </div>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between shadow-soft-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg">&times;</button>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center justify-between shadow-soft-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">⚠️</span>
                    <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-lg">&times;</button>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 to-red-400 text-white flex items-center justify-center text-xl shadow-md shadow-rose-500/20">
                        📢
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">Total Pengumuman Disiarkan</div>
                        <div class="text-2xl font-black text-slate-900 font-outfit mt-0.5"><?= number_format($stats['total_broadcast'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center text-xl shadow-md shadow-blue-500/20">
                        👨‍🏫
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">Pesan Terdistribusi ke Guru</div>
                        <div class="text-2xl font-black text-slate-900 font-outfit mt-0.5"><?= number_format($stats['total_guru_notif'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-500/20">
                        👨‍🎓
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-semibold">Pesan Terdistribusi ke Siswa</div>
                        <div class="text-2xl font-black text-slate-900 font-outfit mt-0.5"><?= number_format($stats['total_siswa_notif'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left: Form Kirim Notifikasi (5 Cols) -->
                <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/90 shadow-soft-sm p-6 sticky top-24">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-base">
                                ✍️
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Buat Notifikasi Baru</h3>
                                <p class="text-xs text-slate-500">Siarkan pesan ke HP Guru dan Siswa</p>
                            </div>
                        </div>
                    </div>

                    <form action="<?= App::baseUrl('admin/notifikasi/store') ?>" method="POST" class="space-y-4">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                        
                        <!-- Judul Notifikasi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Judul Notifikasi / Pengumuman <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="judul" required placeholder="Contoh: Upacara Bendera Hari Senin..." 
                                   class="w-full text-sm font-medium px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        </div>

                        <!-- Grid Tipe & Target -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Kategori / Tipe -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kategori Pesan
                                </label>
                                <select name="tipe" class="w-full text-sm font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-white">
                                    <option value="pengumuman">📢 Pengumuman Umum</option>
                                    <option value="penting">🚨 Penting / Mendesak</option>
                                    <option value="akademik">📚 Info Akademik</option>
                                    <option value="kegiatan">🗓️ Kegiatan / Event</option>
                                    <option value="info">ℹ️ Informasi / Himbauan</option>
                                </select>
                            </div>

                            <!-- Sasaran Penerima -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Sasaran Penerima <span class="text-rose-500">*</span>
                                </label>
                                <select name="target_role" id="target_role_select" onchange="toggleTargetDetails()" class="w-full text-sm font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-white">
                                    <option value="semua">👥 Semua (Guru & Siswa)</option>
                                    <option value="guru">👨‍🏫 Semua Guru</option>
                                    <option value="siswa">👨‍🎓 Semua Siswa</option>
                                    <option value="kelas">🏫 Siswa Per Kelas Tertentu</option>
                                    <option value="guru_single">👤 Guru Tertentu</option>
                                    <option value="siswa_single">👤 Siswa Tertentu</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dropdown Dinamis: Pilihan Kelas -->
                        <div id="wrapper_kelas" class="hidden">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Pilih Kelas Penerima
                            </label>
                            <select name="target_kelas_id" class="w-full text-sm font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-white">
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($allKelas as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Dropdown Dinamis: Pilihan Guru Tertentu -->
                        <div id="wrapper_guru" class="hidden">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Pilih Guru Penerima
                            </label>
                            <select name="target_guru_id" class="w-full text-sm font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-white">
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($allGuru as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?> (<?= htmlspecialchars($g['nik_nip'] ?? '-') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Dropdown Dinamis: Pilihan Siswa Tertentu -->
                        <div id="wrapper_siswa" class="hidden">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Pilih Siswa Penerima
                            </label>
                            <select name="target_siswa_id" class="w-full text-sm font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-white">
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach ($allSiswa as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_siswa']) ?> (<?= htmlspecialchars($s['nama_kelas'] ?? 'Kelas -') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Isi Pesan Notifikasi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Isi Pesan / Uraian Notifikasi <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="pesan" rows="4" required placeholder="Tuliskan isi pengumuman atau instruksi di sini secara detail..." 
                                      class="w-full text-sm font-medium px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition resize-none"></textarea>
                        </div>

                        <!-- Tautan Tambahan (Opsional) & Nama Pengirim -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tautan / Link (Opsional)
                                </label>
                                <input type="url" name="link_url" placeholder="https://contoh.com/info" 
                                       class="w-full text-xs font-medium px-3.5 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nama Pengirim
                                </label>
                                <input type="text" name="sender_nama" value="Admin TU / Kepala Sekolah" 
                                       class="w-full text-xs font-medium px-3.5 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-rose-600 via-rose-500 to-red-500 hover:from-rose-700 hover:to-red-600 text-white font-bold text-sm py-3 px-5 rounded-2xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 transition flex items-center justify-center gap-2 cursor-pointer">
                                <span>🚀</span>
                                <span>Kirim & Siarkan Notifikasi Sekarang</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Riwayat Notifikasi Terkirim (7 Cols) -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-soft-sm p-6">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Riwayat Notifikasi & Pengumuman</h3>
                                <p class="text-xs text-slate-500">Daftar siaran notifikasi yang telah disebar ke aplikasi Guru & Siswa</p>
                            </div>
                            <span class="bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1 rounded-xl">
                                <?= count($allBroadcast) ?> Siaran
                            </span>
                        </div>

                        <?php if (empty($allBroadcast)): ?>
                        <div class="text-center py-12">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 text-3xl flex items-center justify-center mx-auto mb-3">
                                📭
                            </div>
                            <div class="text-slate-600 font-bold text-sm">Belum Ada Pengumuman</div>
                            <p class="text-slate-400 text-xs max-w-sm mx-auto mt-1">Gunakan formulir di sebelah kiri untuk mengirimkan notifikasi pertama Anda ke Guru dan Siswa.</p>
                        </div>
                        <?php else: ?>
                        <div class="space-y-3.5">
                            <?php foreach ($allBroadcast as $b): ?>
                            <?php
                                // Tipe badge color
                                $tipeColors = [
                                    'penting'    => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'akademik'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'kegiatan'   => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'info'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'pengumuman' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                ];
                                $tColor = $tipeColors[$b['tipe']] ?? 'bg-slate-50 text-slate-700 border-slate-200';

                                // Target text
                                $targetDesc = '👥 Semua Guru & Siswa';
                                if ($b['target_role'] === 'guru') {
                                    $targetDesc = !empty($b['target_guru_nama']) ? '👤 Guru: ' . $b['target_guru_nama'] : '👨‍🏫 Semua Guru';
                                } elseif ($b['target_role'] === 'siswa') {
                                    if (!empty($b['nama_kelas'])) {
                                        $targetDesc = '🏫 Siswa Kelas ' . $b['nama_kelas'];
                                    } elseif (!empty($b['target_siswa_nama'])) {
                                        $targetDesc = '👤 Siswa: ' . $b['target_siswa_nama'];
                                    } else {
                                        $targetDesc = '👨‍🎓 Semua Siswa';
                                    }
                                }
                            ?>
                            <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-slate-300 hover:shadow-soft-sm transition bg-white group">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border uppercase tracking-wider <?= $tColor ?>">
                                                <?= htmlspecialchars($b['tipe']) ?>
                                            </span>
                                            <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                                <?= htmlspecialchars($targetDesc) ?>
                                            </span>
                                            <span class="text-[11px] text-slate-400 font-mono">
                                                <?= date('d M Y, H:i', strtotime($b['created_at'])) ?>
                                            </span>
                                        </div>
                                        <h4 class="font-bold text-slate-900 text-sm leading-snug">
                                            <?= htmlspecialchars($b['judul']) ?>
                                        </h4>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed whitespace-pre-line">
                                            <?= htmlspecialchars($b['pesan']) ?>
                                        </p>
                                        
                                        <div class="flex items-center gap-4 mt-3 pt-2.5 border-t border-slate-100 text-[11px] text-slate-500">
                                            <span>Pengirim: <strong><?= htmlspecialchars($b['sender_nama']) ?></strong></span>
                                            <span>Penerima: <strong class="text-slate-800"><?= number_format($b['total_penerima']) ?> pengguna</strong></span>
                                            <?php if (!empty($b['link_url'])): ?>
                                            <a href="<?= htmlspecialchars($b['link_url']) ?>" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1 font-semibold">
                                                <span>🔗 Link</span>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <form action="<?= App::baseUrl('admin/notifikasi/delete/' . $b['id']) ?>" method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini? Seluruh pesan yang terdistribusi ke Guru/Siswa juga akan terhapus.')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                            <button type="submit" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center text-xs transition" title="Hapus Notifikasi">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script>
        function toggleTargetDetails() {
            const select = document.getElementById('target_role_select');
            const val = select.value;

            const wKelas = document.getElementById('wrapper_kelas');
            const wGuru = document.getElementById('wrapper_guru');
            const wSiswa = document.getElementById('wrapper_siswa');

            wKelas.classList.add('hidden');
            wGuru.classList.add('hidden');
            wSiswa.classList.add('hidden');

            if (val === 'kelas') {
                wKelas.classList.remove('hidden');
            } else if (val === 'guru_single') {
                wGuru.classList.remove('hidden');
            } else if (val === 'siswa_single') {
                wSiswa.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
