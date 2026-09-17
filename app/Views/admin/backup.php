<?php
use App\Config\App;

$activeNav = 'backup';
require __DIR__ . '/../partials/tailwind_head.php';
?>

<div class="flex h-screen bg-slate-50 font-sans">
    <!-- Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 leading-tight">Backup & Restore Database</h1>
                    <p class="text-xs text-slate-500 font-medium">Pencadangan Data Keseluruhan Sistem</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Topbar Actions if any -->
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if(isset($_SESSION['flash_success'])): ?>
            <div class="m-6 mb-0 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-semibold"><?= $_SESSION['flash_success'] ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="m-6 mb-0 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-semibold"><?= $_SESSION['flash_error'] ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-4xl mx-auto space-y-6">

                <!-- Info Card -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-indigo-900 mb-2">Pentingnya Melakukan Backup Data!</h2>
                    <p class="text-indigo-800 text-sm mb-4 leading-relaxed">
                        Pencadangan (backup) data berguna untuk mencegah hilangnya data akibat kerusakan sistem, serangan siber, atau ketika Anda ingin memindahkan aplikasi ke Server / Hosting baru. 
                        Data yang diunduh adalah file `.sql` murni yang mencakup struktur tabel beserta isi (baris) datanya.
                    </p>
                    <ul class="text-sm text-indigo-800 list-disc list-inside space-y-1 mb-6">
                        <li>Lakukan pencadangan ini secara rutin (misal: setiap akhir semester).</li>
                        <li>Simpan file `.sql` yang diunduh di tempat yang sangat aman (komputer pribadi atau flashdisk/cloud drive).</li>
                        <li>Jangan sembarangan membagikan file backup ini kepada orang lain karena berisi seluruh informasi sekolah termasuk password dan data siswa.</li>
                    </ul>

                    <form action="<?= App::baseUrl('admin/backup/download') ?>" method="POST" class="inline-block" onsubmit="return confirm('Proses ini mungkin memakan waktu beberapa detik tergantung besarnya data. Lanjutkan?')">
                        <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-md transition transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-4 focus:ring-indigo-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Database Backup (.sql)
                        </button>
                    </form>
                </div>

                <!-- Restore Card (Info only) -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Restore Database</h3>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">
                        Fitur **Restore** (Pemulihan Data) sengaja tidak disediakan di dalam aplikasi web ini demi keamanan. Apabila Anda memfasilitasi upload `.sql` melalui antarmuka web, ini dapat berpotensi tinggi disalahgunakan oleh peretas yang berhasil login sebagai Admin (atau mencuri sesi).
                    </p>
                    <p class="text-slate-600 text-sm font-semibold mb-2">Cara Memulihkan Data (Restore):</p>
                    <ol class="text-slate-600 text-sm list-decimal list-inside space-y-2">
                        <li>Buka panel kontrol server/hosting Anda (misal: <strong>phpMyAdmin</strong> pada cPanel).</li>
                        <li>Pilih database yang Anda gunakan.</li>
                        <li>Klik menu <strong>Import</strong>, lalu unggah file `.sql` hasil backup yang Anda miliki.</li>
                        <li>Tunggu hingga proses impor selesai.</li>
                    </ol>
                </div>

            </div>
        </main>
    </div>
</div>

<?php require __DIR__ . '/../partials/tailwind_foot.php'; ?>
