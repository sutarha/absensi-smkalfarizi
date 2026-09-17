<?php
use App\Config\App;
use Firebase\JWT\JWT;

$config = \App\Models\KonfigurasiSekolah::get();
$sessionSiswa = $_SESSION['user'] ?? null;
$sessionToken = null;
if ($sessionSiswa && ($sessionSiswa['role'] ?? '') === 'siswa') {
    $now = time();
    $payload = [
        'iss'      => 'smk-alfarizi-pwa',
        'iat'      => $now,
        'exp'      => $now + (86400 * 7),
        'siswa_id' => (int)$sessionSiswa['id'],
        'nisn'     => $sessionSiswa['nisn'],
        'nama'     => $sessionSiswa['nama_siswa'],
        'kelas_id' => (int)$sessionSiswa['kelas_id'],
        'role'     => 'siswa',
    ];
    $secret = getenv('JWT_SECRET') ?: 'smk_alfarizi_secret_key_2024_xK9pL3mN7qR1vT5wY8uZ';
    $sessionToken = JWT::encode($payload, $secret, 'HS256');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Siswa AF">
    <meta name="description" content="Portal Siswa SMK Al-Farizi — Absensi, Nilai, LMS & Tabungan">
    <title>Portal Siswa — SMK Al-Farizi</title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/10.9.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging-compat.js"></script>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        :root {
            --nav-h: 68px;
            --safe-b: env(safe-area-inset-bottom, 0px);
            --bg-app: #f8fafc;
            --bg-card: #ffffff;
            --border-card: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --accent: #2563eb;
            --accent-glow: rgba(37,99,235,0.15);
        }
        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            background: #e2e8f0;
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* === MOBILE CONTAINER (HANDPHONE VIEW) === */
        #phone-shell {
            max-width: 480px;
            margin: 0 auto;
            height: 100dvh;
            height: 100vh;
            background: var(--bg-app);
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
            position: relative;
            overflow: hidden;
            border-left: 1px solid rgba(226, 232, 240, 0.8);
            border-right: 1px solid rgba(226, 232, 240, 0.8);
        }
        @media (max-width: 640px) {
            #phone-shell {
                max-width: 100%;
                box-shadow: none;
                border: none;
            }
        }
        #top-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-card);
            padding: 12px 16px;
            flex-shrink: 0;
            padding-top: max(12px, env(safe-area-inset-top));
            z-index: 20;
        }
        #content-area {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }
        #bottom-nav {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-card);
            height: calc(var(--nav-h) + var(--safe-b));
            padding-bottom: var(--safe-b);
            flex-shrink: 0;
            z-index: 30;
        }
        /* === PAGES === */
        .page { display: none; min-height: 100%; padding: 16px 16px calc(var(--nav-h) + 24px); }
        .page.active { display: block; animation: fadeIn 0.2s ease; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
        /* === CARDS === */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 20px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.02);
        }
        .card-gradient {
            background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 24px;
        }
        /* === BOTTOM NAV === */
        .nav-item {
            display: flex; flex-direction: column; align-items: center;
            gap: 4px; padding: 10px 6px; flex: 1; cursor: pointer;
            color: #94a3b8; transition: all 0.2s;
            -webkit-user-select: none; user-select: none;
        }
        .nav-item.active { color: #2563eb; }
        .nav-item svg { transition: transform 0.2s; }
        .nav-item.active svg { transform: scale(1.1); }
        .nav-item span { font-size: 10px; font-weight: 700; }
        .nav-indicator {
            position: absolute; top: -4px; left: 50%; transform: translateX(-50%);
            width: 22px; height: 3px; border-radius: 2px;
            background: #2563eb; display: none;
        }
        .nav-item.active .nav-indicator { display: block; }
        /* === PROFILE BANNER === */
        .profile-bg {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4338ca 100%);
            border-radius: 24px; padding: 20px;
            position: relative; overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.35);
        }
        .profile-bg::before {
            content:''; position:absolute; top:-30px; right:-30px;
            width:130px; height:130px; background:rgba(255,255,255,0.12);
            border-radius:50%; filter:blur(15px);
        }
        .profile-bg::after {
            content:''; position:absolute; bottom:-40px; left:-20px;
            width:110px; height:110px; background:rgba(255,255,255,0.08);
            border-radius:50%; filter:blur(10px);
        }
        /* === STAT CARDS === */
        .stat-card { border-radius: 18px; padding: 14px; border-width: 1px; transition: transform 0.15s; }
        .stat-card:active { transform: scale(0.98); }
        .stat-card.hadir { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .stat-card.telat  { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .stat-card.alpha  { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
        .stat-card.izin   { background: #f5f3ff; border-color: #ddd6fe; color: #5b21b6; }
        /* === SCHEDULE === */
        .schedule-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            border-radius: 16px;
            padding: 12px 14px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        /* === SKELETON LOADER === */
        .skeleton {
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
        /* === BADGE === */
        .badge-notif {
            position:absolute; top:-4px; right:-4px;
            background:#ef4444; color:white; font-size:9px;
            font-weight:700; min-width:16px; height:16px;
            border-radius:8px; display:flex; align-items:center; justify-content:center;
            padding:0 4px; border:2px solid #ffffff;
        }
        /* === PROGRESS BAR === */
        .progress-bar { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; transition: width 1s ease; background: linear-gradient(90deg, #2563eb, #7c3aed); }
        /* === TOAST === */
        #toast {
            position: fixed; bottom: calc(var(--nav-h) + 16px + var(--safe-b));
            left: 50%; transform: translateX(-50%) translateY(20px);
            background: #0f172a; border: 1px solid rgba(255,255,255,0.1);
            color: white; padding: 10px 20px; border-radius: 100px;
            font-size: 13px; font-weight: 600; z-index: 999;
            opacity: 0; transition: all 0.3s; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            white-space: nowrap; pointer-events: none;
        }
        #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        /* === NILAI BADGES === */
        .nilai-badge { padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 800; }
        .nilai-a { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .nilai-b { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .nilai-c { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .nilai-d { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        /* === QUICK MENU ICON SQUIRCLE === */
        .menu-btn {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            background: none; border: none; padding: 4px 0; cursor: pointer;
            transition: transform 0.15s ease;
        }
        .menu-btn:active { transform: scale(0.92); }
        .menu-icon-box {
            width: 52px; height: 52px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        .menu-btn:hover .menu-icon-box {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.14);
        }
        .menu-label {
            font-size: 11px; font-weight: 600; color: #334155;
            text-align: center; line-height: 1.2;
        }
    </style>
</head>
<body>

<div id="phone-shell">
    <!-- ============ TOP BAR ============ -->
    <header id="top-bar">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php if (!empty($config['logo_kop'])): ?>
                    <img src="<?= App::baseUrl('uploads/' . htmlspecialchars($config['logo_kop'])) ?>" alt="Logo" class="w-10 h-10 rounded-2xl object-contain bg-white shadow-md shadow-blue-500/30 border border-slate-200 p-1">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-outfit font-black text-white text-base shadow-md shadow-blue-500/30">
                        AF
                    </div>
                <?php endif; ?>
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 tracking-wide uppercase">Portal Siswa</div>
                    <div id="topbar-nama" class="text-sm font-bold text-slate-800 leading-tight">SMK Al-Farizi</div>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <!-- Notif Bell -->
                <button type="button" onclick="App.switchPage('notif')" class="relative w-9 h-9 rounded-xl bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span id="notif-badge" class="badge-notif hidden">0</span>
                </button>
                <!-- Logout -->
                <button type="button" onclick="App.logout()" title="Keluar" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- ============ CONTENT AREA ============ -->
    <main id="content-area">

        <!-- ====== PAGE: BERANDA ====== -->
        <div class="page active" id="page-beranda">
            <div class="flex justify-between items-center mb-3 px-1">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800">Halo, Semangat Belajar! 👋</h2>
                    <p class="text-xs text-slate-500">Selamat datang di sistem presensi digital</p>
                </div>
                <span id="hari-ini" class="text-xs text-blue-600 font-bold bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100"></span>
            </div>

            <!-- Profil Card Banner -->
            <div class="profile-bg mb-5">
                <div class="flex items-center gap-3.5 relative z-10">
                    <div id="profil-foto" class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md border border-white/40 flex items-center justify-center text-white font-extrabold text-2xl font-outfit flex-shrink-0 shadow-md">
                        ?
                    </div>
                    <div class="flex-1 min-w-0">
                        <div id="profil-nama" class="text-white font-black text-lg leading-tight truncate skeleton h-5 w-40 rounded mb-1"></div>
                        <div id="profil-kelas" class="text-blue-100 text-xs font-medium skeleton h-4 w-28 rounded mb-2"></div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-blue-200 uppercase font-semibold">NISN:</span>
                            <span id="profil-nisn" class="text-xs font-mono text-white bg-white/20 px-2 py-0.5 rounded font-bold skeleton h-4 w-24"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcement Banner from School (Dynamic) -->
            <div id="beranda-pengumuman-section" class="mb-5 hidden">
                <div onclick="App.switchPage('notif')" class="p-3.5 rounded-2xl bg-gradient-to-r from-rose-50 to-amber-50 border border-rose-200/80 shadow-soft-sm cursor-pointer hover:shadow-md transition">
                    <div class="flex items-start gap-2.5">
                        <span class="text-xl flex-shrink-0 animate-bounce">📢</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <span id="beranda-pengumuman-tipe" class="text-[9px] font-extrabold uppercase tracking-wider text-rose-700 bg-rose-100 px-1.5 py-0.2 rounded">
                                    PENGUMUMAN
                                </span>
                                <span class="text-[10px] text-rose-600 font-bold">Buka Notifikasi ➔</span>
                            </div>
                            <h4 id="beranda-pengumuman-judul" class="text-xs font-bold text-slate-900 leading-snug line-clamp-1"></h4>
                            <p id="beranda-pengumuman-pesan" class="text-[11px] text-slate-600 line-clamp-1 mt-0.5"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- MENU HANDPHONE (APP LAUNCHER GRID) -->
            <!-- ============================================== -->
            <div class="mb-5">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                        <span>📱</span> Menu Aplikasi
                    </h3>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Menu Cepat</span>
                </div>
                
                <div class="grid grid-cols-4 gap-y-4 gap-x-2 bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm">
                    <!-- 1. Presensi -->
                    <button type="button" onclick="App.switchPage('absensi')" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-blue-600 to-sky-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-blue-600 transition">Absensi</span>
                    </button>

                    <!-- 2. Nilai Rapor -->
                    <button type="button" onclick="App.switchPage('nilai')" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-amber-500 to-orange-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-amber-600 transition">Rapor Digital</span>
                    </button>

                    <!-- 3. Tabungan -->
                    <button type="button" onclick="App.switchPage('tabungan')" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-emerald-600 to-teal-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-emerald-600 transition">Tabungan</span>
                    </button>

                    <!-- 4. LMS & Tugas -->
                    <button type="button" onclick="App.switchPage('lms')" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-indigo-600 to-violet-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-indigo-600 transition">Tugas LMS</span>
                    </button>

                    <!-- 5. Jadwal Belajar -->
                    <button type="button" onclick="document.getElementById('jadwal-section')?.scrollIntoView({behavior:'smooth'})" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-cyan-600 to-sky-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-cyan-600 transition">Jadwal KBM</span>
                    </button>

                    <!-- 6. Forum Diskusi -->
                    <button type="button" onclick="App.switchPage('lms')" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-purple-600 to-pink-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-purple-600 transition">Diskusi</span>
                    </button>

                    <!-- 7. Notifikasi -->
                    <button type="button" onclick="App.switchPage('notif')" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-rose-500 to-red-400 relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-rose-600 transition">Notifikasi</span>
                    </button>

                    <!-- 8. Profil Siswa -->
                    <button type="button" onclick="App.switchPage('profil')" class="menu-btn group">
                        <div class="menu-icon-box bg-gradient-to-tr from-slate-700 to-slate-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="menu-label group-hover:text-slate-800 transition">Akun Saya</span>
                    </button>
                </div>
            </div>

            <!-- Absensi Bulan Ini -->
            <div class="mb-5">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">
                        Kehadiran Bulan Ini
                    </h3>
                    <button type="button" onclick="App.switchPage('absensi')" class="text-xs text-blue-600 font-bold hover:underline">
                        Lihat Log &rarr;
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="stat-card hadir flex items-center gap-3">
                        <div class="text-2xl">✅</div>
                        <div>
                            <div id="stat-hadir" class="text-2xl font-black text-emerald-700">–</div>
                            <div class="text-xs text-emerald-800 font-semibold">Hadir</div>
                        </div>
                    </div>
                    <div class="stat-card telat flex items-center gap-3">
                        <div class="text-2xl">⏰</div>
                        <div>
                            <div id="stat-telat" class="text-2xl font-black text-amber-700">–</div>
                            <div class="text-xs text-amber-800 font-semibold">Telat</div>
                        </div>
                    </div>
                    <div class="stat-card izin flex items-center gap-3">
                        <div class="text-2xl">📋</div>
                        <div>
                            <div id="stat-izin" class="text-2xl font-black text-purple-700">–</div>
                            <div class="text-xs text-purple-800 font-semibold">Izin/Sakit</div>
                        </div>
                    </div>
                    <div class="stat-card alpha flex items-center gap-3">
                        <div class="text-2xl">❌</div>
                        <div>
                            <div id="stat-alpha" class="text-2xl font-black text-rose-700">–</div>
                            <div class="text-xs text-rose-800 font-semibold">Alpha</div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Tugas Menunggu Dikerjakan -->
            <div class="mb-5 hidden" id="tugas-pending-section">
                <div class="flex items-center justify-between mb-2.5 px-1">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 flex items-center gap-1.5">
                        <span>🔥</span> Tugas Belum Dikumpulkan
                    </h3>
                    <span id="tugas-pending-count" class="text-[10px] font-black text-rose-700 bg-rose-100 border border-rose-200 px-2 py-0.5 rounded-full"></span>
                </div>
                <div id="tugas-pending-list" class="space-y-2.5">
                    <!-- Injected dynamically by loadDashboard -->
                </div>
            </div>

            <!-- Jadwal Hari Ini -->
            <div class="mb-4" id="jadwal-section">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Jadwal Pelajaran Hari Ini</h3>
                    <span class="text-[11px] text-slate-400 font-medium">KBM Aktif</span>
                </div>
                <div id="jadwal-list" class="space-y-2.5">
                    <!-- Skeleton -->
                    <div class="schedule-item skeleton h-14"></div>
                    <div class="schedule-item skeleton h-14"></div>
                </div>
            </div>
        </div>

        <!-- ====== PAGE: ABSENSI ====== -->
        <div class="page" id="page-absensi">
            <h2 class="text-xl font-black text-slate-800 mb-1">Rekap Presensi</h2>
            <p class="text-xs text-slate-500 mb-4">Catatan kehadiran gerbang sekolah</p>

            <div class="card p-4 mb-4">
                <div class="flex gap-2.5 mb-4">
                    <select id="filter-bulan-absensi" class="flex-1 bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                        <?php
                        $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        for ($i = 1; $i <= 12; $i++):
                            $selected = $i == date('n') ? 'selected' : '';
                        ?>
                        <option value="<?= $i ?>" <?= $selected ?>><?= $months[$i-1] ?></option>
                        <?php endfor; ?>
                    </select>
                    <select id="filter-tahun-absensi" class="flex-1 bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                        <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div id="absensi-content">
                    <div class="text-center text-slate-400 text-sm py-8">Memuat data...</div>
                </div>
            </div>
        </div>

        <!-- ====== PAGE: NILAI / RAPOR ====== -->
        <div class="page" id="page-nilai">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-xl font-black text-slate-800">Rapor Digital Siswa</h2>
                <button type="button" onclick="App.cetakRaporAktif()" class="px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 text-xs font-bold flex items-center gap-1.5 shadow-sm active:scale-95 transition cursor-pointer">
                    <span>🖨️</span> Cetak Rapor
                </button>
            </div>
            <p class="text-xs text-slate-500 mb-4">Laporan capaian kompetensi dan hasil belajar resmi per semester</p>

            <!-- Ringkasan Rapor (Dipindahkan dari Beranda) -->
            <div class="mb-5" id="rapor-banner-section">
                <div class="card p-4 bg-gradient-to-br from-amber-50 via-orange-50/50 to-white border border-amber-200/90 shadow-sm relative overflow-hidden">
                    <div class="flex items-start justify-between mb-3 relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center font-black text-xl shadow-md shadow-amber-500/25">
                                🎓
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Ringkasan Nilai</h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-200 text-amber-900" id="rapor-banner-sem">Semester 1</span>
                                </div>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Akumulasi hasil capaian belajar</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Rata-rata</div>
                            <div class="text-xl font-black text-amber-700 leading-tight" id="rapor-banner-rata">–</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs relative z-10">
                        <div class="bg-white/90 p-2 rounded-xl border border-amber-200/60 flex items-center justify-between">
                            <span class="text-slate-500 text-[11px]">Predikat Umum</span>
                            <span class="font-black text-amber-800 text-xs px-2 py-0.5 rounded-lg bg-amber-100" id="rapor-banner-predikat">–</span>
                        </div>
                        <div class="bg-white/90 p-2 rounded-xl border border-amber-200/60 flex items-center justify-between">
                            <span class="text-slate-500 text-[11px]">Mapel Tuntas</span>
                            <span class="font-black text-emerald-700 text-xs" id="rapor-banner-tuntas">–</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mb-4 overflow-x-auto pb-1" id="semester-tabs">
                <?php for ($s = 1; $s <= 6; $s++): ?>
                <button onclick="App.loadNilai(<?= $s ?>)"
                    class="semester-tab flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all relative
                    <?= $s === 1 ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/25' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>"
                    data-sem="<?= $s ?>">
                    Semester <?= $s ?>
                </button>
                <?php endfor; ?>
            </div>
            <div id="nilai-content" class="space-y-3">
                <div class="text-center text-slate-400 text-sm py-8">Memuat rapor digital...</div>
            </div>
        </div>

        <!-- ====== PAGE: TABUNGAN ====== -->
        <div class="page" id="page-tabungan">
            <h2 class="text-xl font-black text-slate-800 mb-1">Tabungan Sekolah</h2>
            <p class="text-xs text-slate-500 mb-4">Pantau target dan riwayat setoran tabungan 🎯</p>
            <div id="tabungan-content">
                <div class="text-center text-slate-400 text-sm py-8">Memuat program tabungan...</div>
            </div>
        </div>

        <!-- ====== PAGE: LMS ====== -->
        <div class="page" id="page-lms">
            <h2 class="text-xl font-black text-slate-800 mb-1">LMS & Ujian Siswa</h2>
            <p class="text-xs text-slate-500 mb-3">Materi belajar, penugasan, dan tes online CBT 📚</p>
            
            <!-- Segmented Control Tabs -->
            <div class="flex p-1 bg-slate-200/80 rounded-2xl mb-4 text-xs font-bold">
                <button type="button" id="tab-lms-materi" onclick="App.switchLmsTab('materi')" class="flex-1 py-2 rounded-xl transition bg-white text-blue-600 shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>📁</span> Materi & Tugas
                </button>
                <button type="button" id="tab-lms-ujian" onclick="App.switchLmsTab('ujian')" class="flex-1 py-2 rounded-xl transition text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>📝</span> Tes / CBT Online
                </button>
            </div>

            <!-- Content Area Materi -->
            <div id="lms-materi-container">
                <div id="lms-content">
                    <div class="text-center text-slate-400 text-sm py-8">Memuat materi LMS...</div>
                </div>
            </div>

            <!-- Content Area Ujian -->
            <div id="lms-ujian-container" class="hidden">
                <div id="lms-ujian-content">
                    <div class="text-center text-slate-400 text-sm py-8">Memuat daftar tes CBT...</div>
                </div>
            </div>
        </div>

        <!-- ====== PAGE: NOTIFIKASI ====== -->
        <div class="page" id="page-notif">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xl font-black text-slate-800 leading-tight">Pusat Notifikasi</h2>
                    <p class="text-xs text-slate-500">Pemberitahuan akademik & sekolah 🔔</p>
                </div>
                <button type="button" onclick="App.markAllNotifRead()" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200/80 px-3 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1">
                    <span>✓</span> Baca Semua
                </button>
            </div>

            <div id="notif-list-container" class="space-y-3">
                <div class="card p-8 text-center text-slate-400 text-xs">
                    Memuat notifikasi...
                </div>
            </div>
        </div>

        <!-- ====== PAGE: PROFIL ====== -->
        <div class="page" id="page-profil">
            <h2 class="text-xl font-black text-slate-800 mb-3">Profil Saya</h2>
            
            <div class="profile-bg p-5 mb-4 text-white">
                <div class="flex items-center gap-4">
                    <div id="pp-foto" class="w-18 h-18 rounded-2xl bg-white/20 backdrop-blur-md border border-white/40 flex items-center justify-center text-white font-black text-3xl font-outfit overflow-hidden flex-shrink-0"></div>
                    <div>
                        <div id="pp-nama" class="font-black text-lg leading-tight"></div>
                        <div id="pp-kelas" class="text-blue-100 text-sm mt-0.5"></div>
                        <div id="pp-jurusan" class="text-blue-200 text-xs mt-0.5"></div>
                    </div>
                </div>
            </div>

            <div class="card divide-y divide-slate-100 mb-4">
                <div class="px-4 py-3.5 flex justify-between items-center">
                    <span class="text-slate-500 text-xs font-semibold">NISN</span>
                    <span id="pp-nisn" class="text-slate-800 font-mono text-xs font-bold"></span>
                </div>
                <div class="px-4 py-3.5 flex justify-between items-center">
                    <span class="text-slate-500 text-xs font-semibold">Tanggal Lahir</span>
                    <span id="pp-tgl-lahir" class="text-slate-800 text-xs font-medium"></span>
                </div>
                <div class="px-4 py-3.5 flex justify-between items-center">
                    <span class="text-slate-500 text-xs font-semibold">Jenis Kelamin</span>
                    <span id="pp-jk" class="text-slate-800 text-xs font-medium"></span>
                </div>
                <div class="px-4 py-3.5 flex justify-between items-center">
                    <span class="text-slate-500 text-xs font-semibold">No. HP Orang Tua</span>
                    <span id="pp-hp-ortu" class="text-slate-800 text-xs font-medium">–</span>
                </div>
            </div>

            <button type="button" onclick="App.logout()" class="w-full py-3.5 rounded-2xl text-rose-600 border border-rose-200 bg-rose-50 text-sm font-bold hover:bg-rose-100 active:scale-98 transition flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Keluar dari Akun</span>
            </button>
        </div>

    </main><!-- /content-area -->

    <!-- ============ BOTTOM NAV ============ -->
    <nav id="bottom-nav">
        <div class="flex h-full items-start pt-1.5">
            <div class="nav-item active" id="nav-beranda" onclick="App.switchPage('beranda')">
                <div class="relative">
                    <div class="nav-indicator"></div>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <span>Beranda</span>
            </div>

            <div class="nav-item" id="nav-absensi" onclick="App.switchPage('absensi')">
                <div class="relative">
                    <div class="nav-indicator"></div>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <span>Absensi</span>
            </div>

            <div class="nav-item" id="nav-nilai" onclick="App.switchPage('nilai')">
                <div class="relative">
                    <div class="nav-indicator"></div>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span>Rapor</span>
            </div>

            <div class="nav-item" id="nav-tabungan" onclick="App.switchPage('tabungan')">
                <div class="relative">
                    <div class="nav-indicator"></div>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span>Tabungan</span>
            </div>

            <div class="nav-item" id="nav-lms" onclick="App.switchPage('lms')">
                <div class="relative">
                    <div class="nav-indicator"></div>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span>LMS</span>
            </div>

            <div class="nav-item" id="nav-profil" onclick="App.switchPage('profil')">
                <div class="relative">
                    <div class="nav-indicator"></div>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <span>Profil</span>
            </div>
        </div>
    </nav>
</div>

<!-- Toast -->
<div id="toast"></div>

<script>
const API_BASE = '<?= App::baseUrl('api/v1') ?>';
const BASE_URL = '<?= App::baseUrl() ?>';
const SESSION_TOKEN = <?= json_encode($sessionToken) ?>;
const SESSION_SISWA = <?= json_encode($sessionSiswa) ?>;

if (SESSION_TOKEN) {
    localStorage.setItem('siswa_token', SESSION_TOKEN);
    if (SESSION_SISWA) {
        localStorage.setItem('siswa_data', JSON.stringify(SESSION_SISWA));
    }
}

const App = {
    token: localStorage.getItem('siswa_token') || SESSION_TOKEN,
    siswa: JSON.parse(localStorage.getItem('siswa_data') || 'null') || SESSION_SISWA,
    currentPage: 'beranda',

    async init() {
        if (!this.token) {
            window.location.href = BASE_URL + 'login';
            return;
        }
        if (!this.siswa) {
            try {
                const res = await this.api('siswa/me');
                if (res && res.success && res.siswa) {
                    this.siswa = res.siswa;
                    localStorage.setItem('siswa_data', JSON.stringify(this.siswa));
                } else {
                    this.logout();
                    return;
                }
            } catch (e) {
                console.error('Gagal mengambil data siswa:', e);
            }
        }
        this.renderProfile();
        this.loadDashboard();
        this.updateHariIni();
        this.startRealtimeNotif();
    },

    switchPage(page) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

        document.getElementById('page-' + page)?.classList.add('active');
        document.getElementById('nav-' + page)?.classList.add('active');

        // Scroll to top
        document.getElementById('content-area').scrollTo(0, 0);
        this.currentPage = page;

        // Load data on page switch
        if (page === 'absensi') this.loadAbsensi();
        if (page === 'nilai') this.loadNilai(1);
        if (page === 'tabungan') this.loadTabungan();
        if (page === 'lms') this.loadLms();
        if (page === 'notif') this.loadNotifikasi();
        if (page === 'profil') this.renderProfil();
    },

    async api(endpoint, opts = {}) {
        const res = await fetch(`${API_BASE}/${endpoint}`, {
            ...opts,
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json',
                ...(opts.headers || {})
            }
        });
        const data = await res.json();
        if (res.status === 401) {
            this.logout();
        }
        return data;
    },

    renderProfile() {
        if (!this.siswa) return;
        const s = this.siswa;
        const inisial = (s.nama_siswa || 'S').split(' ').slice(0,2).map(n => n[0]).join('').toUpperCase();

        document.getElementById('topbar-nama').textContent = (s.nama_siswa || 'Siswa').split(' ')[0];
        document.getElementById('profil-nama').textContent = s.nama_siswa;
        document.getElementById('profil-nama').classList.remove('skeleton', 'h-5', 'w-40', 'rounded', 'mb-1');
        document.getElementById('profil-kelas').textContent = s.nama_kelas ? `Kelas ${s.nama_kelas}${s.jurusan ? ' · ' + s.jurusan : ''}` : 'SMK Al-Farizi';
        document.getElementById('profil-kelas').classList.remove('skeleton', 'h-4', 'w-28', 'rounded', 'mb-2');
        document.getElementById('profil-nisn').textContent = s.nisn;
        document.getElementById('profil-nisn').classList.remove('skeleton', 'h-4', 'w-24');
        document.getElementById('profil-foto').textContent = inisial;

        // Profil page
        document.getElementById('pp-nama').textContent = s.nama_siswa;
        document.getElementById('pp-kelas').textContent = s.nama_kelas ? `Kelas ${s.nama_kelas}` : '–';
        document.getElementById('pp-jurusan').textContent = s.jurusan || '–';
        document.getElementById('pp-nisn').textContent = s.nisn;
        document.getElementById('pp-tgl-lahir').textContent = s.tanggal_lahir
            ? new Date(s.tanggal_lahir).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})
            : '–';
        document.getElementById('pp-jk').textContent = s.jenis_kelamin === 'L' ? '👦 Laki-laki' : '👧 Perempuan';
        document.getElementById('pp-hp-ortu').textContent = s.no_hp_ortu || '–';
        document.getElementById('pp-foto').textContent = inisial;
    },

    renderProfil() {
        this.renderProfile();
    },

    async loadDashboard() {
        try {
            const data = await this.api('siswa/dashboard');
            if (!data.success) return;

            // Absensi stats
            const a = data.absensi_bulan;
            document.getElementById('stat-hadir').textContent = a.hadir || 0;
            document.getElementById('stat-telat').textContent = a.terlambat || 0;
            document.getElementById('stat-izin').textContent = (parseInt(a.izin||0) + parseInt(a.sakit||0));
            document.getElementById('stat-alpha').textContent = a.alpha || 0;

            // Jadwal
            const jadwalEl = document.getElementById('jadwal-list');
            if (data.jadwal_hari_ini && data.jadwal_hari_ini.length > 0) {
                jadwalEl.innerHTML = data.jadwal_hari_ini.map(j => `
                    <div class="schedule-item">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-slate-800 font-bold text-sm">${j.nama_mapel}</div>
                                <div class="text-slate-500 text-xs mt-0.5 font-medium">${j.nama_guru}</div>
                            </div>
                            <span class="text-blue-600 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-lg text-xs font-mono font-bold">${j.jam_mulai.slice(0,5)} – ${j.jam_selesai.slice(0,5)}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                jadwalEl.innerHTML = `
                    <div class="card p-5 text-center">
                        <div class="text-3xl mb-2">🎉</div>
                        <p class="text-slate-500 font-medium text-sm">Tidak ada jadwal hari ini</p>
                    </div>`;
            }

            // Tugas Pending Section di Dashboard
            const tugasSec = document.getElementById('tugas-pending-section');
            const tugasList = document.getElementById('tugas-pending-list');
            const tugasCount = document.getElementById('tugas-pending-count');

            if (tugasSec && tugasList && data.tugas_pending && data.tugas_pending.length > 0) {
                tugasSec.classList.remove('hidden');
                tugasCount.textContent = `${data.tugas_pending.length} Tugas`;
                tugasList.innerHTML = data.tugas_pending.map(t => {
                    const deadlineStr = t.deadline ? new Date(t.deadline).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'}) : 'Tanpa batas waktu';
                    const isOverdue = t.deadline && new Date(t.deadline) < new Date();

                    return `
                        <div class="card p-3.5 border border-rose-200/80 bg-gradient-to-r from-white to-rose-50/30 shadow-sm">
                            <div class="flex justify-between items-start mb-1.5">
                                <div>
                                    <span class="text-[10px] font-extrabold text-blue-600 uppercase tracking-wider">${t.nama_mapel}</span>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight mt-0.5">${t.judul}</h4>
                                    <p class="text-[11px] text-slate-400">Guru: ${t.nama_guru}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold ${isOverdue ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800'}">
                                    ⏳ ${deadlineStr}
                                </span>
                            </div>
                            <button type="button" onclick="App.showLmsDetail(${t.id})" class="mt-2 w-full py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm transition active:scale-98 cursor-pointer">
                                <span>✍️</span> Jawab & Kerjakan Tugas ➔
                            </button>
                        </div>
                    `;
                }).join('');
            } else if (tugasSec) {
                tugasSec.classList.add('hidden');
            }

            // Rapor Banner di Dashboard
            const raporSec = document.getElementById('rapor-banner-section');
            const r = data.rapor_ringkasan;
            if (raporSec && r) {
                raporSec.classList.remove('hidden');
                document.getElementById('rapor-banner-sem').textContent = `Semester ${r.semester_ke}`;
                document.getElementById('rapor-banner-rata').textContent = r.rata_rata;
                document.getElementById('rapor-banner-predikat').textContent = `Predikat ${r.predikat}`;
                document.getElementById('rapor-banner-tuntas').textContent = `${r.mapel_tuntas || r.total_mapel}/${r.total_mapel} Mapel`;
            } else if (raporSec) {
                document.getElementById('rapor-banner-sem').textContent = 'Semester 1';
                document.getElementById('rapor-banner-rata').textContent = '–';
                document.getElementById('rapor-banner-predikat').textContent = 'Dalam Proses';
                document.getElementById('rapor-banner-tuntas').textContent = '–';
            }

            // Pengumuman Banner di Beranda
            const pengumumanSec = document.getElementById('beranda-pengumuman-section');
            if (pengumumanSec) {
                if (data.pengumuman_terbaru) {
                    pengumumanSec.classList.remove('hidden');
                    const pt = document.getElementById('beranda-pengumuman-tipe');
                    if (pt) pt.textContent = (data.pengumuman_terbaru.tipe || 'PENGUMUMAN').toUpperCase();
                    const pj = document.getElementById('beranda-pengumuman-judul');
                    if (pj) pj.textContent = data.pengumuman_terbaru.judul || '';
                    const pp = document.getElementById('beranda-pengumuman-pesan');
                    if (pp) pp.textContent = data.pengumuman_terbaru.pesan || '';
                } else {
                    pengumumanSec.classList.add('hidden');
                }
            }

            // Notif badge
            this.updateNotifBadge(data.unread_notif || 0);
        } catch (e) {
            console.error('Dashboard error:', e);
        }
    },

    async loadAbsensi() {
        const bulan = document.getElementById('filter-bulan-absensi').value;
        const tahun = document.getElementById('filter-tahun-absensi').value;
        const el = document.getElementById('absensi-content');
        el.innerHTML = '<div class="text-center text-slate-400 text-sm py-8">Memuat data...</div>';

        try {
            const data = await this.api(`siswa/absensi?bulan=${bulan}&tahun=${tahun}`);
            if (data.success && data.records?.length) {
                const statusMap = {
                    'HADIR': ['✅','bg-emerald-50 text-emerald-700 border-emerald-200'], 
                    'TERLAMBAT': ['⏰','bg-amber-50 text-amber-700 border-amber-200'],
                    'IZIN':  ['📋','bg-blue-50 text-blue-700 border-blue-200'],  
                    'SAKIT': ['🏥','bg-purple-50 text-purple-700 border-purple-200'],
                    'ALPHA': ['❌','bg-rose-50 text-rose-700 border-rose-200']
                };
                el.innerHTML = data.records.map(r => {
                    const [ico, cls] = statusMap[r.status] || ['❓','bg-slate-50 text-slate-600 border-slate-200'];
                    return `
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 last:border-0">
                            <div>
                                <span class="text-slate-800 text-sm font-semibold">${new Date(r.tanggal).toLocaleDateString('id-ID',{weekday:'short',day:'numeric',month:'short'})}</span>
                                ${r.jam_masuk ? `<div class="text-[10px] text-slate-400 font-mono">Masuk: ${r.jam_masuk.slice(0,5)}</div>` : ''}
                            </div>
                            <span class="${cls} border px-2.5 py-1 rounded-full text-xs font-bold">${ico} ${r.status}</span>
                        </div>`;
                }).join('');
            } else {
                el.innerHTML = '<div class="text-center text-slate-400 text-sm py-8">Tidak ada catatan kehadiran pada periode ini.</div>';
            }
        } catch(e) {
            el.innerHTML = '<div class="text-center text-rose-500 text-sm py-8">Gagal memuat data absensi.</div>';
        }
    },

    currentRaporData: null,

    async loadNilai(semester = 1) {
        // Update active tab
        document.querySelectorAll('.semester-tab').forEach(t => {
            t.className = 'semester-tab flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 relative';
        });
        const activeTab = document.querySelector(`.semester-tab[data-sem="${semester}"]`);
        if (activeTab) {
            activeTab.className = 'semester-tab flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/25 relative';
        }

        const el = document.getElementById('nilai-content');
        el.innerHTML = '<div class="text-center text-slate-400 text-sm py-8"><span>⏳</span> Memuat rapor digital...</div>';

        try {
            const data = await this.api(`siswa/nilai?semester=${semester}`);
            this.currentRaporData = data;

            // Berikan indikator dot hijau pada tab semester yang memiliki nilai
            if (data.available_semesters && data.available_semesters.length) {
                data.available_semesters.forEach(sNum => {
                    const tab = document.querySelector(`.semester-tab[data-sem="${sNum}"]`);
                    if (tab && !tab.querySelector('.sem-dot')) {
                        tab.insertAdjacentHTML('beforeend', '<span class="sem-dot w-2 h-2 rounded-full bg-emerald-400 absolute top-1 right-1"></span>');
                    }
                });
            }

            if (data.success && data.nilai && data.nilai.length > 0) {
                const s = data.siswa || {};
                const stat = data.statistik || {};
                const sch = data.sekolah || { nama: 'SMK Al-Farizi' };

                el.innerHTML = `
                    <!-- 1. Kartu Identitas Rapor Siswa -->
                    <div class="card p-4 bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl shadow-lg relative overflow-hidden border border-slate-800">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-lg shadow-inner">
                                    🎓
                                </div>
                                <div>
                                    <h3 class="font-black text-xs uppercase tracking-wider text-blue-300">Rapor Digital Siswa</h3>
                                    <div class="text-[11px] text-slate-300 font-medium">${sch.nama}</div>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-500/30 border border-blue-400/30 text-blue-200">
                                Semester ${semester}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-y-2 gap-x-3 text-xs">
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Nama Lengkap</div>
                                <div class="font-black text-white text-xs truncate">${s.nama_siswa || '-'}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">NISN</div>
                                <div class="font-bold text-slate-200 text-xs">${s.nisn || '-'}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Kelas & Jurusan</div>
                                <div class="text-slate-200 text-xs truncate">${s.nama_kelas || '-'}${s.jurusan ? ' · ' + s.jurusan : ''}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Wali Kelas</div>
                                <div class="text-slate-200 text-xs truncate">${s.wali_kelas || '–'}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Ringkasan Capaian Prestasi Semester -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="card p-3 text-center bg-blue-50/80 border border-blue-200/90 shadow-sm">
                            <div class="text-[10px] font-extrabold text-blue-600 uppercase">Rata-rata</div>
                            <div class="text-2xl font-black text-blue-800 leading-tight my-0.5">${stat.rata_rata}</div>
                            <div class="text-[9px] text-blue-500 font-bold">Skala 0-100</div>
                        </div>
                        <div class="card p-3 text-center bg-emerald-50/80 border border-emerald-200/90 shadow-sm">
                            <div class="text-[10px] font-extrabold text-emerald-600 uppercase">Predikat</div>
                            <div class="text-2xl font-black text-emerald-800 leading-tight my-0.5">${stat.predikat_umum}</div>
                            <div class="text-[9px] text-emerald-600 font-bold">${stat.rata_rata >= 86 ? 'Sangat Baik' : (stat.rata_rata >= 71 ? 'Baik' : 'Cukup')}</div>
                        </div>
                        <div class="card p-3 text-center bg-amber-50/80 border border-amber-200/90 shadow-sm">
                            <div class="text-[10px] font-extrabold text-amber-700 uppercase">Ketuntasan</div>
                            <div class="text-2xl font-black text-amber-800 leading-tight my-0.5">${stat.mapel_tuntas}/${stat.total_mapel}</div>
                            <div class="text-[9px] text-amber-700 font-bold">${stat.total_mapel > 0 ? Math.round((stat.mapel_tuntas/stat.total_mapel)*100) : 0}% Tuntas</div>
                        </div>
                    </div>

                    <!-- 3. Header Action Tabel -->
                    <div class="flex items-center justify-between pt-2 px-1">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <span>📋</span> Rincian Nilai Mata Pelajaran
                        </h4>
                        <button type="button" onclick="App.cetakRapor(${semester})" class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-blue-500/20 active:scale-95 transition cursor-pointer">
                            <span>🖨️</span> Cetak Lembar Rapor
                        </button>
                    </div>

                    <!-- 4. Daftar Kartu Nilai Mata Pelajaran -->
                    <div class="space-y-3">
                        ${data.nilai.map((n, idx) => {
                            const na = parseFloat(n.nilai_akhir || 0);
                            const isTuntas = na >= 75;

                            return `
                                <div class="card p-4 border border-slate-200/90 shadow-sm hover:border-blue-300 transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1 pr-2">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <span class="text-[10px] font-extrabold text-blue-600 uppercase">${n.kode_mapel || ('MAPEL ' + (idx+1))}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold ${isTuntas ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                                                    ${isTuntas ? '✅ Tuntas' : '⚠️ Remedial'}
                                                </span>
                                            </div>
                                            <h4 class="font-bold text-slate-800 text-sm leading-tight">${n.nama_mapel}</h4>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Guru: ${n.nama_guru || '–'}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-2xl font-black text-slate-800 leading-tight">${n.nilai_akhir}</div>
                                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md text-xs font-black ${this.nilaiBadgeColor(n.nilai_akhir)}">
                                                Predikat ${n.predikat || this.nilaiHuruf(n.nilai_akhir)}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Komponen Nilai KBM -->
                                    <div class="grid grid-cols-4 gap-1.5 p-2 bg-slate-50 rounded-xl text-center border border-slate-100 my-2.5">
                                        <div>
                                            <div class="text-[9px] text-slate-400 font-bold uppercase">Tugas/PR</div>
                                            <div class="text-xs font-extrabold text-slate-700">${parseFloat(n.nilai_tugas || 0) > 0 ? n.nilai_tugas : '-'}</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] text-slate-400 font-bold uppercase">UH/Formatif</div>
                                            <div class="text-xs font-extrabold text-slate-700">${parseFloat(n.nilai_uh || 0) > 0 ? n.nilai_uh : '-'}</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] text-slate-400 font-bold uppercase">UTS/STS</div>
                                            <div class="text-xs font-extrabold text-slate-700">${parseFloat(n.nilai_uts || 0) > 0 ? n.nilai_uts : '-'}</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] text-slate-400 font-bold uppercase">UAS/SAS</div>
                                            <div class="text-xs font-extrabold text-slate-700">${parseFloat(n.nilai_uas || 0) > 0 ? n.nilai_uas : '-'}</div>
                                        </div>
                                    </div>

                                    <!-- Capaian Kompetensi -->
                                    <div class="text-xs text-slate-600 bg-blue-50/50 p-2.5 rounded-xl border border-blue-100/70 leading-relaxed">
                                        <div class="text-[10px] font-bold text-blue-700 uppercase mb-0.5 flex items-center gap-1">
                                            <span>💡</span> Capaian Kompetensi:
                                        </div>
                                        <p class="italic text-[11px] text-slate-700">"${n.capaian_kompetensi || 'Menunjukkan penguasaan kompetensi yang baik dan telah mencapai kriteria ketuntasan minimal.'}"</p>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>

                    <!-- Tombol Cetak Bawah -->
                    <div class="pt-2 pb-4">
                        <button type="button" onclick="App.cetakRapor(${semester})" class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black rounded-2xl text-xs flex items-center justify-center gap-2 shadow-md shadow-blue-500/25 active:scale-98 transition cursor-pointer">
                            <span>🖨️</span> Cetak / Unduh Lembar Rapor Digital (A4 PDF)
                        </button>
                    </div>
                `;
            } else {
                el.innerHTML = `
                    <div class="card p-8 text-center border border-slate-200/80">
                        <div class="text-5xl mb-3">📂</div>
                        <h4 class="text-slate-800 font-bold text-sm mb-1">Belum Ada Nilai Rapor Semester ${semester}</h4>
                        <p class="text-slate-500 text-xs leading-relaxed max-w-xs mx-auto">
                            Nilai untuk semester ini belum diinput atau sedang dalam tahap pengolahan oleh dewan guru. Silakan periksa semester lainnya.
                        </p>
                    </div>
                `;
            }
        } catch(e) {
            console.error('Nilai error:', e);
            el.innerHTML = '<div class="text-center text-rose-500 text-sm py-8">Gagal memuat rapor digital.</div>';
        }
    },

    cetakRaporAktif() {
        const activeTab = document.querySelector('.semester-tab.bg-blue-600, .semester-tab[class*="from-blue-600"]');
        const sem = activeTab ? parseInt(activeTab.getAttribute('data-sem')) : 1;
        this.cetakRapor(sem);
    },

    cetakRapor(semester) {
        if (!this.currentRaporData || !this.currentRaporData.nilai || this.currentRaporData.nilai.length === 0) {
            this.showToast('Data rapor semester ini belum tersedia untuk dicetak.');
            return;
        }

        const d = this.currentRaporData;
        const s = d.siswa || {};
        const stat = d.statistik || {};
        const today = new Date().toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});

        const printWin = window.open('', '_blank');
        if (!printWin) {
            alert('Silakan izinkan popup browser untuk membuka lembar cetak rapor.');
            return;
        }

        const rowsHtml = d.nilai.map((n, i) => `
            <tr>
                <td style="text-align:center; padding: 6px 4px;">${i + 1}</td>
                <td style="padding: 6px 8px; font-weight: bold;">
                    ${n.nama_mapel}
                    <div style="font-size: 8pt; color: #555; font-weight: normal;">Guru: ${n.nama_guru || '-'}</div>
                </td>
                <td style="text-align:center; padding: 6px 4px;">${n.kkm || 75}</td>
                <td style="text-align:center; padding: 6px 4px;">${parseFloat(n.nilai_tugas || 0) > 0 ? n.nilai_tugas : '-'}</td>
                <td style="text-align:center; padding: 6px 4px;">${parseFloat(n.nilai_uh || 0) > 0 ? n.nilai_uh : '-'}</td>
                <td style="text-align:center; padding: 6px 4px;">${parseFloat(n.nilai_uts || 0) > 0 ? n.nilai_uts : '-'}</td>
                <td style="text-align:center; padding: 6px 4px;">${parseFloat(n.nilai_uas || 0) > 0 ? n.nilai_uas : '-'}</td>
                <td style="text-align:center; padding: 6px 4px; font-weight: bold; background: #f8fafc;">${n.nilai_akhir}</td>
                <td style="text-align:center; padding: 6px 4px; font-weight: bold;">${n.predikat || 'B'}</td>
                <td style="padding: 6px 8px; font-size: 8.5pt; line-height: 1.25;">
                    ${n.capaian_kompetensi || 'Tuntas dalam penguasaan materi pembelajaran.'}
                </td>
            </tr>
        `).join('');

        const html = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Rapor Digital - ${s.nama_siswa || 'Siswa'} (Semester ${semester})</title>
                <style>
                    @page { size: A4 portrait; margin: 12mm 15mm; }
                    body { font-family: 'Times New Roman', serif; font-size: 10pt; line-height: 1.3; color: #000; margin: 0; padding: 15px; }
                    .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
                    .header h2 { margin: 0; font-size: 15pt; text-transform: uppercase; }
                    .header h3 { margin: 2px 0; font-size: 12pt; text-transform: uppercase; font-weight: normal; }
                    .header p { margin: 2px 0; font-size: 8.5pt; font-family: Arial, sans-serif; }
                    .title-doc { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin: 10px 0 2px 0; }
                    .sub-doc { text-align: center; font-size: 10pt; margin-bottom: 12px; }
                    table.info { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9.5pt; }
                    table.info td { padding: 2px 4px; vertical-align: top; }
                    table.data { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 9pt; }
                    table.data th, table.data td { border: 1px solid #000; }
                    table.data th { background: #f1f5f9; padding: 6px 4px; font-weight: bold; text-align: center; }
                    .ttd-box { width: 100%; margin-top: 30px; display: flex; justify-content: space-between; font-size: 9.5pt; }
                    .ttd-col { width: 30%; text-align: center; }
                    .no-print { background: #1e293b; color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-family: Arial, sans-serif; border-radius: 8px; }
                    .btn-print { background: #2563eb; color: #fff; border: 0; padding: 8px 16px; font-weight: bold; border-radius: 6px; cursor: pointer; }
                    @media print { .no-print { display: none; } body { padding: 0; } }
                </style>
            </head>
            <body>
                <div class="no-print">
                    <div><strong>Lembar Cetak Rapor Digital Siswa - Semester ${semester}</strong></div>
                    <button class="btn-print" onclick="window.print()">🖨️ Cetak Lembar Rapor</button>
                </div>

                <div class="header">
                    <h2>SMK AL-FARIZI TASIKMALAYA</h2>
                    <h3>Laporan Capaian Kompetensi Belajar Peserta Didik</h3>
                    <p>Jl. Raya Karangnunggal, Tasikmalaya, Jawa Barat | NPSN: 69888999 | Email: smkalfarizi@sch.id</p>
                </div>

                <div class="title-doc">RAPOR HASIL BELAJAR PESERTA DIDIK</div>
                <div class="sub-doc">SEMESTER ${semester} (TAHUN AJARAN AKTIF)</div>

                <table class="info">
                    <tr>
                        <td style="width: 15%;">Nama Lengkap</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 35%;"><strong>${s.nama_siswa || '-'}</strong></td>
                        <td style="width: 15%;">Kelas / Jurusan</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 31%;">${s.nama_kelas || '-'} (${s.jurusan || '-'})</td>
                    </tr>
                    <tr>
                        <td>NISN</td>
                        <td>:</td>
                        <td><strong>${s.nisn || '-'}</strong></td>
                        <td>Semester</td>
                        <td>:</td>
                        <td>Semester ${semester}</td>
                    </tr>
                    <tr>
                        <td>Wali Kelas</td>
                        <td>:</td>
                        <td>${s.wali_kelas || '–'}</td>
                        <td>Tanggal Cetak</td>
                        <td>:</td>
                        <td>${today}</td>
                    </tr>
                </table>

                <table class="data">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 4%;">No</th>
                            <th rowspan="2" style="width: 26%;">Mata Pelajaran</th>
                            <th rowspan="2" style="width: 6%;">KKM</th>
                            <th colspan="4" style="width: 20%;">Komponen Nilai</th>
                            <th rowspan="2" style="width: 7%;">Nilai Akhir</th>
                            <th rowspan="2" style="width: 6%;">Predikat</th>
                            <th rowspan="2" style="width: 31%;">Capaian Pembelajaran</th>
                        </tr>
                        <tr>
                            <th style="font-size: 8pt;">Tugas</th>
                            <th style="font-size: 8pt;">UH</th>
                            <th style="font-size: 8pt;">UTS</th>
                            <th style="font-size: 8pt;">UAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                        <tr style="background: #f8fafc; font-weight: bold;">
                            <td colspan="7" style="text-align: right; padding: 6px 10px;">RATA-RATA NILAI RAPOR :</td>
                            <td style="text-align: center; font-size: 10.5pt; color: #1e3a8a;">${stat.rata_rata}</td>
                            <td style="text-align: center;">${stat.predikat_umum}</td>
                            <td style="padding: 6px 8px; font-size: 8.5pt;">${stat.mapel_tuntas}/${stat.total_mapel} Mata Pelajaran Memenuhi Ketuntasan</td>
                        </tr>
                    </tbody>
                </table>

                <div class="ttd-box">
                    <div class="ttd-col">
                        <div>Mengetahui,</div>
                        <div>Orang Tua / Wali Siswa</div>
                        <div style="height: 60px;"></div>
                        <div>( .................................................. )</div>
                    </div>
                    <div class="ttd-col">
                        <div>Tasikmalaya, ${today}</div>
                        <div>Wali Kelas,</div>
                        <div style="height: 60px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">${s.wali_kelas || '( .................................................. )'}</div>
                    </div>
                    <div class="ttd-col">
                        <div>Mengetahui,</div>
                        <div>Kepala Sekolah SMK Al-Farizi</div>
                        <div style="height: 60px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">Drs. H. M. Alfarizi, M.Pd.</div>
                    </div>
                </div>
            </body>
            </html>
        `;

        printWin.document.open();
        printWin.document.write(html);
        printWin.document.close();
    },

    async loadNotifikasi() {
        const container = document.getElementById('notif-list-container');
        if (!container) return;
        container.innerHTML = '<div class="card p-8 text-center text-slate-400 text-xs">Memuat notifikasi...</div>';

        try {
            const res = await this.api('siswa/notifikasi');
            if (res.success && res.data && res.data.length > 0) {
                const tipeBadges = {
                    'penting':    'bg-rose-50 text-rose-700 border-rose-200',
                    'akademik':   'bg-blue-50 text-blue-700 border-blue-200',
                    'kegiatan':   'bg-purple-50 text-purple-700 border-purple-200',
                    'info':       'bg-amber-50 text-amber-700 border-amber-200',
                    'pengumuman': 'bg-emerald-50 text-emerald-700 border-emerald-200',
                };
                container.innerHTML = res.data.map(n => {
                    const isUnread = parseInt(n.is_read) === 0;
                    const badgeCls = tipeBadges[n.tipe] || 'bg-slate-50 text-slate-700 border-slate-200';
                    const tgl = new Date(n.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'});
                    return `
                        <div id="siswa-notif-item-${n.id}" class="card p-4 border transition ${isUnread ? 'bg-rose-50/40 border-rose-200 ring-1 ring-rose-200/60' : 'border-slate-200/80'}">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="text-[9px] font-black px-2 py-0.5 rounded-md border uppercase tracking-wider ${badgeCls}">
                                    ${n.tipe}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">${tgl}</span>
                            </div>
                            <h4 class="font-bold text-slate-900 text-sm leading-snug">${n.judul}</h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed whitespace-pre-line">${n.pesan}</p>

                            <div class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-slate-100 text-[11px]">
                                <span class="text-slate-400">Dari: <strong class="text-slate-600">${n.pengirim || 'Admin Sekolah'}</strong></span>
                                <div class="flex items-center gap-2">
                                    ${n.link_url ? `<a href="${n.link_url}" target="_blank" class="text-blue-600 font-bold hover:underline">🔗 Buka Tautan</a>` : ''}
                                    ${isUnread ? `<button type="button" onclick="App.markNotifRead(${n.id})" class="text-emerald-700 bg-emerald-100/80 hover:bg-emerald-200 font-bold px-2 py-0.5 rounded-lg text-xs transition cursor-pointer">✓ Tandai Dibaca</button>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                this.updateNotifBadge(res.unread_count || 0);
            } else {
                container.innerHTML = `
                    <div class="card p-8 text-center">
                        <div class="text-4xl mb-2 text-slate-300">📭</div>
                        <h4 class="font-bold text-slate-700 text-sm mb-1">Belum Ada Notifikasi</h4>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">Pengumuman penting dan pemberitahuan dari sekolah akan ditampilkan di sini.</p>
                    </div>
                `;
                this.updateNotifBadge(0);
            }
        } catch (e) {
            console.error('Notifikasi error:', e);
            container.innerHTML = '<div class="card p-6 text-center text-rose-500 text-xs">Gagal memuat notifikasi. Silakan coba lagi.</div>';
        }
    },

    async markNotifRead(id) {
        try {
            const res = await this.api(`siswa/notifikasi/read/${id}`, { method: 'POST' });
            if (res.success) {
                const item = document.getElementById('siswa-notif-item-' + id);
                if (item) {
                    item.classList.remove('bg-rose-50/40', 'border-rose-200', 'ring-1', 'ring-rose-200/60');
                    item.classList.add('border-slate-200/80');
                    const btn = item.querySelector('button[onclick*="markNotifRead"]');
                    if (btn) btn.remove();
                }
                this.updateNotifBadge(res.unread_count || 0);
            }
        } catch (e) {
            console.error('Mark notif error:', e);
        }
    },

    async markAllNotifRead() {
        try {
            const res = await this.api('siswa/notifikasi/read-all', { method: 'POST' });
            if (res.success) {
                this.loadNotifikasi();
                this.updateNotifBadge(0);
            }
        } catch (e) {
            console.error('Mark all notif error:', e);
        }
    },

    updateNotifBadge(count) {
        const badge = document.getElementById('notif-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '0';
                badge.classList.add('hidden');
            }
        }
    },

    lastUnreadNotifSiswa: 0,
    realtimeTimer: null,

    startRealtimeNotif() {
        if (this.realtimeTimer) clearInterval(this.realtimeTimer);

        const checkLive = async () => {
            if (!this.token || document.hidden) return;
            try {
                const res = await this.api('siswa/notifikasi/live-check');
                if (res && res.success) {
                    const unread = parseInt(res.unread_count || 0);
                    if (unread > this.lastUnreadNotifSiswa) {
                        this.playNotifChime();
                        if (res.latest) {
                            this.showLiveToast(res.latest);
                            // Update banner pengumuman di beranda
                            const pengumumanSec = document.getElementById('beranda-pengumuman-section');
                            if (pengumumanSec) {
                                pengumumanSec.classList.remove('hidden');
                                const pt = document.getElementById('beranda-pengumuman-tipe');
                                if (pt) pt.textContent = (res.latest.tipe || 'PENGUMUMAN').toUpperCase();
                                const pj = document.getElementById('beranda-pengumuman-judul');
                                if (pj) pj.textContent = res.latest.judul || '';
                                const pp = document.getElementById('beranda-pengumuman-pesan');
                                if (pp) pp.textContent = res.latest.pesan || '';
                            }
                        }
                        // Jika siswa sedang di halaman notifikasi, reload otomatis
                        if (this.currentPage === 'notif') {
                            this.loadNotifikasi();
                        }
                    }
                    this.lastUnreadNotifSiswa = unread;
                    this.updateNotifBadge(unread);
                }
            } catch (e) {
                // Silently ignore network errors
            }
        };

        // Mulai interval realtime polling setiap 4 detik
        this.realtimeTimer = setInterval(checkLive, 4000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) checkLive();
        });
    },

    playNotifChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const now = ctx.currentTime;

            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.12, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.3);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(830.61, now + 0.1);
            gain2.gain.setValueAtTime(0.15, now + 0.1);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.1);
            osc2.stop(now + 0.5);
        } catch(e) {}
    },

    showLiveToast(notif) {
        if (!notif) return;
        let toast = document.getElementById('liveSiswaNotifToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'liveSiswaNotifToast';
            toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-[92%] max-w-sm transition-all duration-300 transform -translate-y-20 opacity-0 pointer-events-auto';
            document.body.appendChild(toast);
        }

        const tipeIcons = {
            'penting': '🚨',
            'akademik': '📚',
            'kegiatan': '🗓️',
            'info': 'ℹ️',
            'pengumuman': '📢'
        };
        const icon = tipeIcons[notif.tipe] || '🔔';

        toast.innerHTML = `
            <div onclick="App.switchPage('notif'); App.hideLiveToast();" class="p-3.5 rounded-3xl bg-slate-900/95 backdrop-blur-md text-white shadow-2xl border border-slate-700/80 flex items-start gap-3 cursor-pointer hover:bg-slate-900 transition">
                <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center justify-center text-xl flex-shrink-0 animate-pulse">
                    ${icon}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <span class="text-[9px] font-black uppercase tracking-wider text-rose-400 bg-rose-500/20 px-1.5 py-0.2 rounded">
                            ${notif.tipe || 'PENGUMUMAN'} BARU
                        </span>
                        <span class="text-[10px] text-slate-400">Baru saja</span>
                    </div>
                    <h4 class="font-bold text-white text-xs leading-snug truncate">${notif.judul || 'Pengumuman Baru'}</h4>
                    <p class="text-[11px] text-slate-300 line-clamp-1 mt-0.5">${notif.pesan || ''}</p>
                </div>
                <button type="button" onclick="event.stopPropagation(); App.hideLiveToast();" class="text-slate-400 hover:text-white text-sm font-bold p-1 cursor-pointer">
                    ✕
                </button>
            </div>
        `;

        toast.classList.remove('-translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');

        if (this._siswaToastTimeout) clearTimeout(this._siswaToastTimeout);
        this._siswaToastTimeout = setTimeout(() => this.hideLiveToast(), 6000);
    },

    hideLiveToast() {
        const toast = document.getElementById('liveSiswaNotifToast');
        if (toast) {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('-translate-y-20', 'opacity-0');
        }
    },

    nilaiHuruf(n) {
        n = parseFloat(n);
        if (n >= 90) return 'A';
        if (n >= 80) return 'B';
        if (n >= 70) return 'C';
        return 'D';
    },

    nilaiBadgeColor(n) {
        n = parseFloat(n);
        if (n >= 90) return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
        if (n >= 80) return 'bg-blue-100 text-blue-800 border border-blue-200';
        if (n >= 70) return 'bg-amber-100 text-amber-800 border border-amber-200';
        return 'bg-rose-100 text-rose-800 border border-rose-200';
    },

    nilaiClass(n) {
        n = parseFloat(n);
        if (n >= 90) return 'nilai-a';
        if (n >= 80) return 'nilai-b';
        if (n >= 70) return 'nilai-c';
        return 'nilai-d';
    },

    async loadTabungan() {
        const el = document.getElementById('tabungan-content');
        el.innerHTML = '<div class="text-center text-slate-400 text-sm py-8">Memuat tabungan...</div>';

        try {
            const data = await this.api('siswa/tabungan');
            if (data.success && data.programs?.length) {
                el.innerHTML = data.programs.map(p => {
                    const pct = Math.min(100, Math.round((p.total_terkumpul / p.target_nominal) * 100));
                    return `
                        <div class="card p-4 mb-3">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xl flex-shrink-0">
                                    💰
                                </div>
                                <div class="flex-1">
                                    <div class="text-slate-800 font-bold text-sm">${p.nama_program}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">${p.deskripsi || ''}</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold ${p.status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'}">
                                    ${p.status === 'lunas' ? 'LUNAS ✅' : pct + '%'}
                                </span>
                            </div>
                            <div class="progress-bar mb-2">
                                <div class="progress-fill" style="width:${pct}%"></div>
                            </div>
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-emerald-700">Rp ${parseInt(p.total_terkumpul).toLocaleString('id-ID')}</span>
                                <span class="text-slate-500">Target: Rp ${parseInt(p.target_nominal).toLocaleString('id-ID')}</span>
                            </div>
                            ${p.target_tanggal ? `<div class="text-xs text-amber-700 mt-2">⏳ Deadline: ${new Date(p.target_tanggal).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})}</div>` : ''}
                            
                            ${p.riwayat_setoran && p.riwayat_setoran.length > 0 ? `
                                <div class="mt-4 pt-3 border-t border-slate-100">
                                    <div class="text-xs font-bold text-slate-500 mb-2">Riwayat Setoran:</div>
                                    <div class="space-y-2">
                                        ${p.riwayat_setoran.map(r => `
                                            <div class="flex justify-between items-center text-xs bg-slate-50 p-2 rounded-xl">
                                                <div>
                                                    <div class="text-slate-800 font-semibold">${new Date(r.tanggal).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'})}</div>
                                                    <div class="text-[10px] text-slate-400">${r.dicatat_oleh || '-'} ${r.catatan ? `· ${r.catatan}` : ''}</div>
                                                </div>
                                                <div class="text-emerald-700 font-bold">+Rp ${parseInt(r.jumlah).toLocaleString('id-ID')}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : `
                                <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-400 text-center">
                                    Belum ada catatan setoran
                                </div>
                            `}
                        </div>`;
                }).join('');
            } else {
                el.innerHTML = `
                    <div class="card p-8 text-center">
                        <div class="text-5xl mb-4">💰</div>
                        <p class="text-slate-800 font-bold mb-1">Belum ada program tabungan</p>
                        <p class="text-slate-500 text-xs">Program tabungan akan muncul di sini setelah dibuat oleh sekolah.</p>
                    </div>`;
            }
        } catch(e) {
            el.innerHTML = '<div class="text-center text-rose-500 text-sm py-8">Gagal memuat tabungan.</div>';
        }
    },

    async loadLms() {
        const el = document.getElementById('lms-content');
        el.innerHTML = '<div class="text-center text-slate-400 text-sm py-8">Memuat materi LMS...</div>';

        try {
            const data = await this.api('siswa/lms/materi');
            if (data.success && data.data?.length) {
                el.innerHTML = data.data.map(mapel => `
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base border border-blue-200">
                                    📘
                                </div>
                                <div>
                                    <h3 class="text-slate-800 font-bold text-sm leading-tight">${mapel.nama_mapel}</h3>
                                    <p class="text-slate-500 text-[11px]">${mapel.nama_guru}</p>
                                </div>
                            </div>
                            <button onclick="App.showDiskusiMapel(${mapel.mapel_id})" class="px-3 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold flex items-center gap-1 hover:bg-purple-100 transition cursor-pointer">
                                💬 Diskusi
                            </button>
                        </div>
                        <div class="space-y-3">
                            ${mapel.items.map(m => {
                                const isTugas = (m.tipe || '').toLowerCase() === 'tugas';
                                const isSubmitted = parseInt(m.is_submitted || 0) > 0;
                                const isOverdue = m.deadline && new Date(m.deadline) < new Date();
                                const deadlineStr = m.deadline ? new Date(m.deadline).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'}) : '';

                                return `
                                <div class="card p-4 relative overflow-hidden cursor-pointer hover:border-blue-300 transition" onclick="App.showLmsDetail(${m.id})">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="font-bold text-slate-800 text-sm leading-tight pr-3">${m.judul}</div>
                                        ${!isTugas 
                                            ? '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 shrink-0">MATERI</span>' 
                                            : '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 shrink-0">TUGAS</span>'}
                                    </div>
                                    <p class="text-slate-500 text-xs line-clamp-2 mb-2.5 leading-relaxed">${m.deskripsi}</p>
                                    <div class="flex items-center justify-between text-[11px] mb-3">
                                        <span class="text-slate-400 font-medium">📅 ${new Date(m.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'})}</span>
                                        ${isTugas ? `
                                            ${isSubmitted 
                                                ? '<span class="text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px]">✅ Sudah Dikumpulkan</span>' 
                                                : (isOverdue 
                                                    ? '<span class="text-rose-600 font-bold bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full text-[10px]">⚠️ Terlewat (' + deadlineStr + ')</span>' 
                                                    : '<span class="text-amber-700 font-bold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full text-[10px]">⏳ Batas: ' + deadlineStr + '</span>')}
                                        ` : ''}
                                    </div>
                                    ${isTugas ? `
                                        <button type="button" onclick="event.stopPropagation(); App.showLmsDetail(${m.id})" class="w-full py-2.5 ${isSubmitted ? 'bg-emerald-50 border border-emerald-200 text-emerald-800 hover:bg-emerald-100' : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md shadow-blue-500/20'} font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-98 cursor-pointer">
                                            <span>${isSubmitted ? '✅' : '✍️'}</span> ${isSubmitted ? 'Lihat & Perbarui Jawaban Tugas' : 'Jawab & Kerjakan Tugas ➔'}
                                        </button>
                                    ` : `
                                        <button type="button" onclick="event.stopPropagation(); App.showLmsDetail(${m.id})" class="w-full py-2 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-98 cursor-pointer">
                                            <span>📖</span> Buka & Pelajari Materi
                                        </button>
                                    `}
                                </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `).join('');
            } else {
                el.innerHTML = `
                    <div class="card p-8 text-center">
                        <div class="text-5xl mb-4">📚</div>
                        <p class="text-slate-800 font-bold mb-1">Belum ada materi atau tugas</p>
                        <p class="text-slate-500 text-xs">Materi yang diunggah oleh guru akan tampil di sini.</p>
                    </div>`;
            }
        } catch(e) {
            el.innerHTML = '<div class="text-center text-rose-500 text-sm py-8">Gagal memuat materi LMS.</div>';
        }
    },

    async showLmsDetail(id) {
        if(!document.getElementById('lmsModal')) {
            const modalHtml = `
                <div id="lmsModal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end sm:justify-center p-4">
                    <div class="bg-white w-full max-w-md rounded-3xl border border-slate-200 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden transform transition-transform translate-y-full" id="lmsModalContent">
                        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0 z-10">
                            <h3 class="font-bold text-slate-800 text-base" id="lmsModalTitle">Detail Konten LMS</h3>
                            <button onclick="App.closeLmsDetail()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-800 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div id="lmsModalBody" class="p-5 overflow-y-auto pb-8">
                            <div class="text-center text-slate-400 text-sm py-8">Memuat detail...</div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        const modal = document.getElementById('lmsModal');
        const modalContent = document.getElementById('lmsModalContent');
        const modalTitle = document.getElementById('lmsModalTitle');
        const modalBody = document.getElementById('lmsModalBody');
        
        modal.classList.remove('hidden');
        setTimeout(() => modalContent.classList.remove('translate-y-full'), 10);

        try {
            const data = await this.api('siswa/lms/materi/' + id);
            if(data.success) {
                const m = data.materi;
                const s = data.submission;
                const isTugas = (m.tipe || '').toLowerCase() === 'tugas';
                
                if (modalTitle) {
                    modalTitle.textContent = isTugas ? '📝 Lembar Tugas Siswa' : '📖 Modul Pembelajaran';
                }

                let html = `
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            ${!isTugas 
                                ? '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">MATERI</span>' 
                                : '<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">TUGAS</span>'}
                            <span class="text-xs text-slate-500 font-semibold">${m.nama_mapel}</span>
                        </div>
                        <h2 class="text-lg font-black text-slate-800 leading-tight mb-2">${m.judul}</h2>
                        ${m.deadline ? `<div class="text-xs text-amber-800 font-bold mb-3 flex items-center gap-1.5 bg-amber-50 border border-amber-200 p-2.5 rounded-xl"><span>⏳</span> Batas Pengumpulan: ${new Date(m.deadline).toLocaleString('id-ID', {dateStyle:'medium', timeStyle:'short'})}</div>` : ''}
                    </div>
                    
                    <div class="mb-4">
                        <div class="text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1">
                            <span>📌</span> ${isTugas ? 'Instruksi / Soal Tugas:' : 'Deskripsi Materi:'}
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 text-sm text-slate-700 whitespace-pre-wrap border border-slate-200 leading-relaxed">${m.deskripsi || '-'}</div>
                    </div>
                `;

                if(m.file_path) {
                    html += `
                        <a href="${BASE_URL}/${m.file_path}" target="_blank" download class="flex items-center gap-2 w-full p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold mb-4 justify-center hover:bg-blue-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh Berkas Lampiran dari Guru
                        </a>
                    `;
                }

                if(isTugas) {
                    html += `
                        <div class="border-t border-slate-200 pt-4 mt-2">
                            <h4 class="font-black text-slate-800 mb-3 text-sm flex items-center gap-1.5">
                                <span>✍️</span> Lembar Jawaban & Pengumpulan Tugas
                            </h4>
                    `;
                    
                    if(s) {
                        html += `
                            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-1.5 text-emerald-800 font-bold text-sm">
                                        <span>✅</span> Tugas Sudah Dikumpulkan
                                    </div>
                                    <span class="text-[10px] bg-emerald-200 text-emerald-900 font-bold px-2 py-0.5 rounded-full">Terkirim</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mb-2.5">
                                    Waktu kirim: ${new Date(s.submitted_at).toLocaleString('id-ID', {dateStyle:'medium', timeStyle:'short'})}
                                </div>
                                
                                <div class="mb-2">
                                    <span class="text-[11px] font-bold text-slate-600 block mb-1">Jawaban yang kamu kirimkan:</span>
                                    <div class="bg-white p-3 rounded-xl border border-emerald-200 text-xs text-slate-800 font-mono whitespace-pre-wrap">${s.jawaban || '(Tidak ada teks jawaban)'}</div>
                                </div>

                                ${s.file_path ? `
                                    <a href="${BASE_URL}/${s.file_path}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-700 hover:text-blue-900 font-bold bg-white px-3 py-2 rounded-xl border border-blue-200 mb-2">
                                        <span>📎</span> Unduh File Lampiran Jawaban Terkirim
                                    </a>
                                ` : ''}

                                ${s.nilai !== null ? `
                                    <div class="mt-3 p-3.5 bg-white rounded-xl border border-emerald-300">
                                        <div class="text-[10px] text-slate-500 uppercase font-extrabold mb-1">Hasil Penilaian Guru:</div>
                                        <div class="text-3xl font-black text-emerald-600">${s.nilai} <span class="text-xs text-slate-400 font-normal">/ 100</span></div>
                                        ${s.catatan_guru ? `<div class="text-xs text-slate-700 mt-2 p-2.5 bg-slate-50 rounded-lg border border-slate-200"><strong>Catatan Guru:</strong><br>${s.catatan_guru}</div>` : ''}
                                    </div>
                                ` : '<div class="text-xs text-amber-800 font-semibold mt-2 flex items-center gap-1 bg-amber-100/60 p-2.5 rounded-xl"><span>⏳</span> Menunggu guru memeriksa & memberikan nilai. Kamu masih dapat memperbarui jawaban di bawah ini jika diperlukan.</div>'}
                            </div>
                        `;
                    }

                    if(!s || s.nilai === null) {
                        html += `
                            <form id="formSubmitLms" onsubmit="App.submitLms(event, ${m.id})" class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        ${s ? '✏️ Perbarui Jawaban Tugas Kamu:' : '✏️ Ketik Jawaban Tugas Kamu:'}
                                    </label>
                                    <textarea name="jawaban" rows="4" placeholder="Ketik jawaban tugas kamu di sini secara jelas..." class="w-full bg-white border border-slate-300 rounded-xl p-3 text-sm text-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:outline-none transition" required>${s ? (s.jawaban || '') : ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        📎 Upload File / Foto Lampiran ${s && s.file_path ? '(Pilih baru jika ingin mengganti lampiran)' : '(Opsional)'}
                                    </label>
                                    <input type="file" name="file_lampiran" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 bg-white border border-slate-300 rounded-xl p-2 cursor-pointer">
                                    <p class="text-[10px] text-slate-400 mt-1">Format dokumen/foto: PDF, DOCX, JPG, PNG, ZIP max 10MB</p>
                                </div>
                                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black rounded-xl text-sm transition shadow-md shadow-blue-500/25 active:scale-98 cursor-pointer flex items-center justify-center gap-2">
                                    <span>🚀</span> ${s ? 'Perbarui Jawaban Tugas' : 'Kirim Jawaban Tugas Sekarang'}
                                </button>
                            </form>
                        `;
                    }
                    html += `</div>`;
                }

                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = '<div class="text-center text-rose-500 text-sm py-8">Gagal memuat materi.</div>';
            }
        } catch(e) {
            modalBody.innerHTML = '<div class="text-center text-rose-500 text-sm py-8">Terjadi kendala saat memuat materi.</div>';
        }
    },

    closeLmsDetail() {
        const modal = document.getElementById('lmsModal');
        const modalContent = document.getElementById('lmsModalContent');
        if(modal) {
            modalContent.classList.add('translate-y-full');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    },

    async submitLms(e, materiId) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Mengirim...';

        try {
            const res = await fetch(`${API_BASE}/siswa/lms/submit/${materiId}`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.token
                },
                body: formData
            });
            const data = await res.json();
            
            if(data.success) {
                this.showToast('Tugas berhasil dikumpulkan ✅');
                this.loadLms();
                this.loadDashboard();
                this.closeLmsDetail();
            } else {
                this.showToast(data.message || 'Gagal mengirim tugas');
                btn.disabled = false;
                btn.innerHTML = '<span>🚀</span> Kirim Jawaban';
            }
        } catch(err) {
            this.showToast('Terjadi kesalahan jaringan');
            btn.disabled = false;
            btn.innerHTML = '<span>🚀</span> Kirim Jawaban';
        }
    },

    async showDiskusiMapel(mapelId) {
        if(!document.getElementById('lmsModal')) {
            const modalHtml = `
                <div id="lmsModal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end sm:justify-center p-4">
                    <div class="bg-white w-full max-w-md rounded-3xl border border-slate-200 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden transform transition-transform translate-y-full" id="lmsModalContent">
                        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0 z-10">
                            <h3 class="font-bold text-slate-800 text-base">Forum Diskusi</h3>
                            <button onclick="App.closeLmsDetail()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-800 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div id="lmsModalBody" class="p-5 overflow-y-auto pb-8 flex-1">
                            <div class="text-center text-slate-400 text-sm py-8">Memuat diskusi...</div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        const modal = document.getElementById('lmsModal');
        const modalContent = document.getElementById('lmsModalContent');
        const modalBody = document.getElementById('lmsModalBody');
        
        modalContent.querySelector('h3').textContent = 'Forum Diskusi Belajar';
        modalBody.innerHTML = '<div class="text-center text-slate-400 text-sm py-8">Memuat diskusi...</div>';
        
        modal.classList.remove('hidden');
        setTimeout(() => modalContent.classList.remove('translate-y-full'), 10);

        try {
            const data = await this.api('siswa/lms/diskusi/' + mapelId);
            if(data.success) {
                const msgs = data.data;
                const me = data.me;
                
                let html = `<div class="space-y-3 mb-4" id="diskusi-container">`;
                
                if (msgs.length === 0) {
                    html += `<div class="text-center text-slate-400 text-xs py-8">Belum ada diskusi, yuk mulai bertanya!</div>`;
                } else {
                    html += msgs.map(m => {
                        const isMe = (m.user_type === me.type && m.user_id == me.id);
                        const isGuru = (m.user_type === 'guru');
                        
                        return `
                            <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'}">
                                <div class="text-[10px] text-slate-400 mb-1 px-1">
                                    ${m.nama_pengirim} ${isGuru ? '👨‍🏫 Guru' : ''} • ${new Date(m.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'short', hour:'2-digit', minute:'2-digit'})}
                                </div>
                                <div class="max-w-[85%] px-4 py-2.5 rounded-2xl text-sm ${isMe ? 'bg-blue-600 text-white rounded-tr-sm shadow-sm' : (isGuru ? 'bg-amber-100 text-amber-900 border border-amber-200 rounded-tl-sm' : 'bg-slate-100 text-slate-800 border border-slate-200 rounded-tl-sm')}">
                                    ${m.pesan.replace(/\n/g, '<br>')}
                                </div>
                            </div>
                        `;
                    }).join('');
                }
                
                html += `</div>`;
                
                // Form reply
                html += `
                    <form onsubmit="App.postDiskusi(event, ${mapelId})" class="mt-4 pt-3 border-t border-slate-100 flex gap-2 sticky bottom-0 bg-white pb-2">
                        <input type="text" name="pesan" placeholder="Tulis pertanyaan atau tanggapan..." required class="flex-1 bg-slate-50 border border-slate-200 rounded-full px-4 py-2 text-sm text-slate-800 focus:outline-none focus:border-blue-500" autocomplete="off">
                        <button type="submit" class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 cursor-pointer shadow-md shadow-blue-500/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </form>
                `;
                
                modalBody.innerHTML = html;
                
                setTimeout(() => {
                    const c = document.getElementById('diskusi-container');
                    if(c) c.scrollIntoView({behavior: "smooth", block: "end"});
                }, 100);
            }
        } catch(e) {
            modalBody.innerHTML = '<div class="text-center text-rose-500 text-sm py-8">Gagal memuat forum diskusi.</div>';
        }
    },

    async postDiskusi(e, mapelId) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button');
        const input = form.querySelector('input');
        const pesan = input.value.trim();
        
        if(!pesan) return;
        btn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('pesan', pesan);
            
            const res = await fetch(`${API_BASE}/siswa/lms/diskusi/${mapelId}`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + this.token },
                body: formData
            });
            const data = await res.json();
            
            if(data.success) {
                input.value = '';
                this.showDiskusiMapel(mapelId);
            } else {
                this.showToast(data.message || 'Gagal mengirim pesan');
            }
        } catch(err) {
            this.showToast('Gagal mengirim pesan');
        }
        btn.disabled = false;
    },

    // =========================================================================
    // CBT / UJIAN ONLINE SISWA (PILIHAN GANDA & ESSAY + REALTIME COUNTDOWN)
    // =========================================================================
    cbt: {
        state: null,
        timer: null
    },

    switchLmsTab(tab) {
        const btnMateri = document.getElementById('tab-lms-materi');
        const btnUjian = document.getElementById('tab-lms-ujian');
        const contMateri = document.getElementById('lms-materi-container');
        const contUjian = document.getElementById('lms-ujian-container');

        if (tab === 'materi') {
            btnMateri.className = 'flex-1 py-2 rounded-xl transition bg-white text-blue-600 shadow-sm flex items-center justify-center gap-1.5 cursor-pointer';
            btnUjian.className = 'flex-1 py-2 rounded-xl transition text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5 cursor-pointer';
            contMateri.classList.remove('hidden');
            contUjian.classList.add('hidden');
            this.loadLms();
        } else {
            btnUjian.className = 'flex-1 py-2 rounded-xl transition bg-white text-blue-600 shadow-sm flex items-center justify-center gap-1.5 cursor-pointer';
            btnMateri.className = 'flex-1 py-2 rounded-xl transition text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5 cursor-pointer';
            contUjian.classList.remove('hidden');
            contMateri.classList.add('hidden');
            this.loadUjianList();
        }
    },

    async loadUjianList() {
        const el = document.getElementById('lms-ujian-content');
        el.innerHTML = '<div class="text-center text-slate-400 text-xs py-8">Memuat daftar tes CBT...</div>';

        try {
            const res = await this.api('siswa/lms/ujian');
            if (res.success && res.data && res.data.length > 0) {
                el.innerHTML = res.data.map(u => {
                    const isSelesai = (u.status_pengerjaan === 'selesai' || u.status_pengerjaan === 'waktu_habis');
                    const isSedang = (u.status_pengerjaan === 'sedang_mengerjakan');
                    const isLulus = isSelesai && (parseFloat(u.nilai_akhir) >= parseFloat(u.kkm));

                    let actionBtn = '';
                    if (isSelesai) {
                        actionBtn = `
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] text-emerald-800 font-bold uppercase tracking-wider">Nilai Ujian Kamu</div>
                                    <div class="text-2xl font-black ${isLulus ? 'text-emerald-700' : 'text-rose-600'}">
                                        ${parseFloat(u.nilai_akhir).toFixed(1)}
                                    </div>
                                    <div class="text-[10px] ${isLulus ? 'text-emerald-600' : 'text-rose-600'} font-semibold">
                                        ${isLulus ? '🎉 Memenuhi KKM' : 'Perlu Remedial'}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ✅ Selesai
                                    </span>
                                </div>
                            </div>
                        `;
                    } else if (isSedang) {
                        actionBtn = `
                            <button type="button" onclick="App.startUjian(${u.id})" class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold rounded-2xl text-xs flex items-center justify-center gap-2 shadow-md animate-pulse cursor-pointer">
                                <span>⚡</span> Lanjutkan Ujian (Sesi Aktif)
                            </button>
                        `;
                    } else {
                        actionBtn = `
                            <button type="button" onclick="App.startUjian(${u.id})" class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl text-xs flex items-center justify-center gap-2 shadow-md cursor-pointer">
                                <span>🚀</span> Mulai Kerjakan Ujian
                            </button>
                        `;
                    }

                    return `
                        <div class="card p-4 mb-3 border border-slate-200 shadow-sm hover:border-slate-300 transition">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">${u.nama_mapel}</span>
                                    <h3 class="text-sm font-black text-slate-800 leading-snug mt-0.5">${u.judul}</h3>
                                    <p class="text-[11px] text-slate-400">Guru: ${u.nama_guru}</p>
                                </div>
                            </div>

                            ${u.deskripsi ? `<p class="text-xs text-slate-600 mb-3 bg-slate-50 p-2.5 rounded-xl border border-slate-100">${u.deskripsi}</p>` : ''}

                            <div class="flex flex-wrap items-center gap-2 text-[10px] font-bold mb-3">
                                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                    ⏱️ ${u.durasi_menit} Menit
                                </span>
                                <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                                    🎯 KKM: ${parseFloat(u.kkm).toFixed(0)}
                                </span>
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                    🔘 ${u.total_pg} PG + ✍️ ${u.total_essay} Essay
                                </span>
                            </div>

                            ${actionBtn}
                        </div>
                    `;
                }).join('');
            } else {
                el.innerHTML = `
                    <div class="card p-8 text-center border border-slate-200">
                        <div class="text-5xl mb-3">📝</div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Belum Ada Tes Aktif</h4>
                        <p class="text-xs text-slate-400">Tes pilihan ganda atau essay dari guru akan tampil di sini.</p>
                    </div>
                `;
            }
        } catch(err) {
            console.error(err);
            el.innerHTML = '<div class="text-center text-rose-500 text-xs py-8">Gagal memuat tes CBT.</div>';
        }
    },

    async startUjian(ujianId) {
        if (!confirm('Apakah kamu siap memulai pengerjaan tes ini? Waktu akan otomatis berjalan mundur.')) {
            return;
        }

        try {
            const res = await this.api(`siswa/lms/ujian/start/${ujianId}`, { method: 'POST' });
            if (!res.success) {
                alert(res.message || 'Tidak dapat memulai ujian.');
                this.loadUjianList();
                return;
            }

            // Init CBT State
            this.cbt.state = {
                ujian: res.ujian,
                sesi: res.sesi,
                soal: res.soal,
                jawaban: res.jawaban_tersimpan || {},
                currentIndex: 0,
                sisaDetik: parseInt(res.sesi.sisa_detik) || (res.ujian.durasi_menit * 60)
            };

            this.openCbtModal();
            this.renderCurrentSoal();
            this.startCountdownTimer();
        } catch(err) {
            alert('Terjadi kesalahan saat memulai ujian.');
        }
    },

    openCbtModal() {
        let m = document.getElementById('cbtModal');
        if (!m) {
            const html = `
                <div id="cbtModal" class="fixed inset-0 z-[120] bg-slate-900/80 backdrop-blur-md flex flex-col justify-end sm:justify-center p-0 sm:p-4">
                    <div class="bg-slate-50 w-full max-w-md mx-auto h-[100dvh] sm:h-[92vh] sm:rounded-3xl border border-slate-200 shadow-2xl flex flex-col overflow-hidden">
                        
                        <!-- CBT Topbar -->
                        <div class="bg-slate-900 text-white p-3.5 flex items-center justify-between shadow-md shrink-0">
                            <div>
                                <div class="text-[10px] text-blue-300 font-bold uppercase tracking-wider" id="cbtHeaderMapel">Mapel</div>
                                <h3 class="text-xs font-black text-white truncate max-w-[200px]" id="cbtHeaderJudul">Judul Ujian</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <div id="cbtTimerBadge" class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-mono font-black">
                                    <span>⏱️</span>
                                    <span id="cbtTimerDisplay">00:00</span>
                                </div>
                                <button type="button" onclick="App.openDaftarSoalModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xs font-bold cursor-pointer" title="Daftar Soal">
                                    📑
                                </button>
                            </div>
                        </div>

                        <!-- CBT Question Area -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="cbtQuestionContainer">
                            <!-- Injected by renderCurrentSoal -->
                        </div>

                        <!-- CBT Bottom Navigation Controls -->
                        <div class="bg-white border-t border-slate-200 p-3 flex items-center justify-between gap-2 shrink-0">
                            <button type="button" id="cbtBtnPrev" onclick="App.prevSoal()" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs disabled:opacity-40 disabled:pointer-events-none cursor-pointer">
                                ◀ Sebelumnya
                            </button>

                            <button type="button" id="cbtBtnRagu" onclick="App.toggleRagu()" class="py-2.5 px-3 rounded-xl border font-bold text-xs flex items-center gap-1 cursor-pointer">
                                <span>⚠️</span> <span id="cbtRaguText">Ragu-ragu</span>
                            </button>

                            <button type="button" id="cbtBtnNext" onclick="App.nextSoal()" class="py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs cursor-pointer shadow-sm">
                                Selanjutnya ▶
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Daftar Nomor Soal -->
                <div id="cbtDaftarModal" class="fixed inset-0 z-[130] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                    <div class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl flex flex-col max-h-[80vh]">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-black text-slate-800">Daftar Nomor Soal</h4>
                            <button type="button" onclick="App.closeDaftarSoalModal()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 font-bold text-xs flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex items-center gap-3 text-[10px] font-bold mb-3 text-slate-500">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Dijawab</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Ragu</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-slate-200"></span> Belum</span>
                        </div>
                        <div class="grid grid-cols-5 gap-2 overflow-y-auto p-1 flex-1" id="cbtGridNomor">
                            <!-- Injected by renderGridNomor -->
                        </div>
                        <button type="button" onclick="App.finishUjianConfirm()" class="mt-4 w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl cursor-pointer">
                            Selesaikan Ujian Sekarang
                        </button>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
        } else {
            m.classList.remove('hidden');
        }

        document.getElementById('cbtHeaderMapel').textContent = this.cbt.state.ujian.nama_mapel;
        document.getElementById('cbtHeaderJudul').textContent = this.cbt.state.ujian.judul;
    },

    startCountdownTimer() {
        if (this.cbt.timer) clearInterval(this.cbt.timer);

        const updateDisplay = () => {
            const sisa = this.cbt.state.sisaDetik;
            if (sisa <= 0) {
                clearInterval(this.cbt.timer);
                document.getElementById('cbtTimerDisplay').textContent = '00:00';
                alert('Waktu ujian telah berakhir! Jawaban Anda akan otomatis dikumpulkan.');
                this.submitUjian(true);
                return;
            }

            const m = Math.floor(sisa / 60);
            const s = sisa % 60;
            const str = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            const displayEl = document.getElementById('cbtTimerDisplay');
            const badgeEl = document.getElementById('cbtTimerBadge');
            if (displayEl) displayEl.textContent = str;

            if (badgeEl) {
                if (sisa < 300) { // Kurang dari 5 menit
                    badgeEl.className = 'flex items-center gap-1 px-3 py-1.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-mono font-black animate-pulse';
                } else if (sisa < 600) {
                    badgeEl.className = 'flex items-center gap-1 px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-mono font-black';
                } else {
                    badgeEl.className = 'flex items-center gap-1 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-mono font-black';
                }
            }
        };

        updateDisplay();
        this.cbt.timer = setInterval(() => {
            if (!this.cbt.state) return;
            this.cbt.state.sisaDetik--;
            updateDisplay();
        }, 1000);
    },

    renderCurrentSoal() {
        const state = this.cbt.state;
        if (!state) return;

        const idx = state.currentIndex;
        const s = state.soal[idx];
        const j = state.jawaban[s.id] || {};
        const container = document.getElementById('cbtQuestionContainer');

        document.getElementById('cbtBtnPrev').disabled = (idx === 0);
        const nextBtn = document.getElementById('cbtBtnNext');
        if (idx === state.soal.length - 1) {
            nextBtn.textContent = 'Selesai 🏁';
            nextBtn.className = 'py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs cursor-pointer shadow-sm';
        } else {
            nextBtn.textContent = 'Selanjutnya ▶';
            nextBtn.className = 'py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs cursor-pointer shadow-sm';
        }

        // Ragu badge
        const isRagu = !!j.is_ragu;
        const raguBtn = document.getElementById('cbtBtnRagu');
        if (isRagu) {
            raguBtn.className = 'py-2.5 px-3 rounded-xl border border-amber-400 bg-amber-50 text-amber-800 font-bold text-xs flex items-center gap-1 cursor-pointer';
            document.getElementById('cbtRaguText').textContent = 'Ragu-ragu (Ya)';
        } else {
            raguBtn.className = 'py-2.5 px-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-bold text-xs flex items-center gap-1 cursor-pointer';
            document.getElementById('cbtRaguText').textContent = 'Ragu-ragu';
        }

        let html = `
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-900 text-white">
                    Soal No. ${idx + 1} dari ${state.soal.length}
                </span>
                <span class="text-[11px] font-bold ${s.tipe_soal === 'essay' ? 'text-purple-600' : 'text-blue-600'}">
                    ${s.tipe_soal === 'essay' ? '✍️ Soal Uraian / Essay' : '🔘 Pilihan Ganda'}
                </span>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold text-slate-800 whitespace-pre-wrap leading-relaxed mb-3">
                    ${s.pertanyaan}
                </p>
                ${s.gambar ? `<img src="${s.gambar}" alt="Gambar Soal" class="rounded-xl max-h-48 w-auto mb-3 border border-slate-200">` : ''}
            </div>
        `;

        if (s.tipe_soal === 'pilihan_ganda') {
            html += `<div class="space-y-2">`;
            const currentAnswer = (j.jawaban_pg || '').toUpperCase();

            ['A', 'B', 'C', 'D', 'E'].forEach(opt => {
                const optText = s['pilihan_' + opt.toLowerCase()];
                if (optText) {
                    const isSelected = (currentAnswer === opt);
                    html += `
                        <div onclick="App.pilihJawabanPG('${opt}')" class="flex items-center gap-3 p-3 rounded-2xl border-2 cursor-pointer transition ${isSelected ? 'border-blue-600 bg-blue-50/80 shadow-soft-sm' : 'border-slate-200 bg-white hover:border-slate-300'}">
                            <span class="w-7 h-7 rounded-xl ${isSelected ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 font-bold'} flex items-center justify-center font-black text-xs shrink-0">
                                ${opt}
                            </span>
                            <span class="text-xs text-slate-800 font-medium leading-tight">${optText}</span>
                        </div>
                    `;
                }
            });
            html += `</div>`;
        } else {
            // ESSAY
            const essayAnswer = j.jawaban_essay || '';
            html += `
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Tuliskan Jawaban Essay Kamu:</label>
                    <textarea id="cbtEssayInput" rows="5" oninput="App.inputJawabanEssay(this.value)" placeholder="Ketik jawaban lengkap dan jelas di sini..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:border-purple-500 focus:bg-white leading-relaxed">${essayAnswer}</textarea>
                    <div class="text-[10px] text-slate-400 text-right flex items-center justify-end gap-1">
                        <span>💾 Tersimpan otomatis</span>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
    },

    async pilihJawabanPG(opt) {
        const state = this.cbt.state;
        if (!state) return;

        const s = state.soal[state.currentIndex];
        if (!state.jawaban[s.id]) {
            state.jawaban[s.id] = {};
        }

        state.jawaban[s.id].jawaban_pg = opt;
        this.renderCurrentSoal();

        // Auto-save realtime to server
        this.syncJawaban(s.id, opt, null, state.jawaban[s.id].is_ragu || 0);
    },

    inputJawabanEssay(val) {
        const state = this.cbt.state;
        if (!state) return;

        const s = state.soal[state.currentIndex];
        if (!state.jawaban[s.id]) {
            state.jawaban[s.id] = {};
        }
        state.jawaban[s.id].jawaban_essay = val;

        // Debounced sync
        clearTimeout(this.cbt._syncDebounce);
        this.cbt._syncDebounce = setTimeout(() => {
            this.syncJawaban(s.id, null, val, state.jawaban[s.id].is_ragu || 0);
        }, 800);
    },

    toggleRagu() {
        const state = this.cbt.state;
        if (!state) return;

        const s = state.soal[state.currentIndex];
        if (!state.jawaban[s.id]) {
            state.jawaban[s.id] = {};
        }

        state.jawaban[s.id].is_ragu = state.jawaban[s.id].is_ragu ? 0 : 1;
        this.renderCurrentSoal();

        this.syncJawaban(s.id, state.jawaban[s.id].jawaban_pg, state.jawaban[s.id].jawaban_essay, state.jawaban[s.id].is_ragu);
    },

    async syncJawaban(soalId, jawabanPG, jawabanEssay, isRagu) {
        const state = this.cbt.state;
        if (!state || !state.sesi) return;

        try {
            const fd = new FormData();
            fd.append('soal_id', soalId);
            if (jawabanPG) fd.append('jawaban_pg', jawabanPG);
            if (jawabanEssay !== null && jawabanEssay !== undefined) fd.append('jawaban_essay', jawabanEssay);
            fd.append('is_ragu', isRagu ? 1 : 0);
            fd.append('sisa_detik', state.sisaDetik);

            await fetch(`${API_BASE}/siswa/lms/ujian/save-jawaban/${state.sesi.id}`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + this.token },
                body: fd
            });
        } catch(e) {
            console.warn('Sync answer err', e);
        }
    },

    nextSoal() {
        const state = this.cbt.state;
        if (!state) return;

        if (state.currentIndex < state.soal.length - 1) {
            state.currentIndex++;
            this.renderCurrentSoal();
        } else {
            this.finishUjianConfirm();
        }
    },

    prevSoal() {
        const state = this.cbt.state;
        if (!state) return;

        if (state.currentIndex > 0) {
            state.currentIndex--;
            this.renderCurrentSoal();
        }
    },

    goToSoal(idx) {
        if (!this.cbt.state) return;
        this.cbt.state.currentIndex = idx;
        this.renderCurrentSoal();
        this.closeDaftarSoalModal();
    },

    openDaftarSoalModal() {
        const state = this.cbt.state;
        if (!state) return;

        const grid = document.getElementById('cbtGridNomor');
        grid.innerHTML = state.soal.map((s, idx) => {
            const j = state.jawaban[s.id] || {};
            const isAnswered = !!(j.jawaban_pg || (j.jawaban_essay && j.jawaban_essay.trim() !== ''));
            const isRagu = !!j.is_ragu;
            const isCurrent = (state.currentIndex === idx);

            let bg = 'bg-slate-100 text-slate-700 border-slate-200';
            if (isRagu) {
                bg = 'bg-amber-400 text-white border-amber-500 font-black';
            } else if (isAnswered) {
                bg = 'bg-emerald-600 text-white border-emerald-700 font-black';
            }

            return `
                <button type="button" onclick="App.goToSoal(${idx})" class="w-10 h-10 rounded-xl border-2 ${bg} ${isCurrent ? 'ring-2 ring-blue-600 ring-offset-2' : ''} text-xs flex items-center justify-center cursor-pointer transition">
                    ${idx + 1}
                </button>
            `;
        }).join('');

        document.getElementById('cbtDaftarModal').classList.remove('hidden');
    },

    closeDaftarSoalModal() {
        const m = document.getElementById('cbtDaftarModal');
        if (m) m.classList.add('hidden');
    },

    finishUjianConfirm() {
        const state = this.cbt.state;
        if (!state) return;

        let answeredCount = 0;
        state.soal.forEach(s => {
            const j = state.jawaban[s.id] || {};
            if (j.jawaban_pg || (j.jawaban_essay && j.jawaban_essay.trim() !== '')) {
                answeredCount++;
            }
        });

        const total = state.soal.length;
        const msg = (answeredCount < total)
            ? `Kamu baru menjawab ${answeredCount} dari ${total} soal! Yakin ingin menyelesaikan ujian sekarang?`
            : `Semua ${total} soal sudah dijawab. Yakin ingin mengumpulkan ujian?`;

        if (confirm(msg)) {
            this.submitUjian(false);
        }
    },

    async submitUjian(isTimeout = false) {
        const state = this.cbt.state;
        if (!state || !state.sesi) return;

        if (this.cbt.timer) {
            clearInterval(this.cbt.timer);
            this.cbt.timer = null;
        }

        try {
            const fd = new FormData();
            if (isTimeout) fd.append('is_timeout', '1');

            const res = await fetch(`${API_BASE}/siswa/lms/ujian/submit/${state.sesi.id}`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + this.token },
                body: fd
            });
            const data = await res.json();

            // Close Exam Modal
            const modal = document.getElementById('cbtModal');
            if (modal) modal.classList.add('hidden');
            this.closeDaftarSoalModal();

            this.cbt.state = null;

            // Show Result
            alert(`Ujian berhasil dikumpulkan! Nilai Pilihan Ganda kamu: ${parseFloat(data.nilai_pg || data.nilai_akhir || 0).toFixed(1)}.\n(Soal essay akan dikoreksi dan dinilai oleh guru)`);
            this.loadUjianList();
        } catch(e) {
            alert('Terjadi kendala saat mengumpulkan ujian.');
        }
    },

    updateHariIni() {
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const now = new Date();
        const el = document.getElementById('hari-ini');
        if (el) {
            el.textContent = `${days[now.getDay()]}, ${now.toLocaleDateString('id-ID',{day:'numeric',month:'short'})}`;
        }
    },

    showToast(msg, dur = 2500) {
        const t = document.getElementById('toast');
        if (!t) return;
        t.textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), dur);
    },

    logout() {
        if (!confirm('Yakin ingin keluar?')) return;
        localStorage.removeItem('siswa_token');
        localStorage.removeItem('siswa_data');
        window.location.href = BASE_URL + 'logout';
    }
};

// Init
document.addEventListener('DOMContentLoaded', () => App.init());

// Filter absensi
document.getElementById('filter-bulan-absensi')?.addEventListener('change', () => App.loadAbsensi());
document.getElementById('filter-tahun-absensi')?.addEventListener('change', () => App.loadAbsensi());

// Service Worker & Firebase Push Notification
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= App::baseUrl('sw-siswa.js') ?>')
    .then(registration => {
        console.log('Service Worker registered');
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
            projectId: "YOUR_PROJECT_ID",
            storageBucket: "YOUR_PROJECT_ID.appspot.com",
            messagingSenderId: "YOUR_SENDER_ID",
            appId: "YOUR_APP_ID"
        };
        const vapidKey = "YOUR_VAPID_KEY";
        
        if (firebaseConfig.apiKey !== "YOUR_API_KEY" && typeof firebase !== 'undefined') {
            firebase.initializeApp(firebaseConfig);
            const messaging = firebase.messaging();
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    messaging.getToken({ serviceWorkerRegistration: registration, vapidKey: vapidKey })
                    .then(token => {
                        if (token) {
                            fetch(`${API_BASE}/siswa/fcm-token`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + App.token
                                },
                                body: JSON.stringify({ fcm_token: token })
                            }).catch(console.error);
                        }
                    }).catch(err => console.log('Gagal get FCM token:', err));
                }
            });

            messaging.onMessage((payload) => {
                App.showToast(payload.notification?.title + ' - ' + payload.notification?.body, 4000);
            });
        }
    }).catch(console.error);
}
</script>
</body>
</html>
