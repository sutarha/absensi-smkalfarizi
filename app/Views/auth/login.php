<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI Presensi & Akademik SMK Al-Farizi</title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 font-sans selection:bg-brand-500 selection:text-white">
    <div class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-3xl shadow-soft-xl border border-white/40 p-8 sm:p-10 transition-all duration-300">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <?php if (!empty($config['logo_kop'])): ?>
                <img src="<?= App::baseUrl('uploads/' . htmlspecialchars($config['logo_kop'])) ?>" alt="Logo Sekolah" class="w-20 h-20 mx-auto mb-4 object-contain">
            <?php else: ?>
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-sky-400 text-white font-outfit font-extrabold text-2xl flex items-center justify-center shadow-glow-brand mx-auto mb-4 tracking-wider">
                    <?= strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $config['nama_sekolah'] ?? 'AF'), 0, 2)) ?>
                </div>
            <?php endif; ?>
            <h1 class="text-2xl font-outfit font-bold text-slate-900 tracking-tight"><?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK AL-FARIZI') ?></h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Portal Terpadu: Admin, Guru & Siswa</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="mb-6 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2 animate-fade-in">
            <span class="text-base">⚠️</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="<?= App::baseUrl('login') ?>" method="POST" class="space-y-4">
            <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="username">
                    Username / NISN Siswa
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                        👤
                    </span>
                    <input type="text" name="username" id="username" 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-800 placeholder-slate-400 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:bg-white transition-all duration-200" 
                           placeholder="Username guru/admin atau NISN siswa" required autofocus>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" for="password">
                    Kata Sandi / Tanggal Lahir (Siswa)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                        🔒
                    </span>
                    <input type="password" name="password" id="password" 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-800 placeholder-slate-400 text-sm focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 focus:bg-white transition-all duration-200" 
                           placeholder="Password atau tanggal lahir (YYYY-MM-DD)" required>
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5">
                    💡 <strong>Siswa:</strong> Masukkan <strong>NISN</strong> dan <strong>Tanggal Lahir</strong> (contoh: <code>2005-01-01</code>).
                </p>
            </div>

            <button type="submit" 
                    class="w-full mt-2 py-3 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-semibold text-sm shadow-glow-brand hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2">
                <span>Masuk ke Sistem</span>
                <span class="text-base">➜</span>
            </button>
        </form>

    </div>
</body>
</html>
