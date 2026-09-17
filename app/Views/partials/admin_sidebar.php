<?php
use App\Config\App;

/**
 * Shared Admin Sidebar Component
 * Expects:
 * - $activeNav (string): 'dashboard', 'geofence', 'guru', 'siswa', 'kelas', 'jadwal', 'monitoring_guru', 'monitoring_siswa', 'payroll'
 * - $config (array)
 * - $user (array)
 */
$activeNav = $activeNav ?? 'dashboard';

$navItems = [
    'Utama' => [
        [
            'id' => 'dashboard',
            'label' => 'Dashboard',
            'url' => App::baseUrl('admin/dashboard'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'
        ],
        [
            'id' => 'geofence',
            'label' => 'Geofence & Tarif',
            'roles' => ['admin'],
            'url' => App::baseUrl('admin/geofence'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
        ],
        [
            'id' => 'reset_data',
            'label' => 'Pembersihan Data',
            'roles' => ['admin'],
            'url' => App::baseUrl('admin/reset-data'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
        ],
        [
            'id' => 'backup',
            'label' => 'Backup & Restore',
            'roles' => ['admin', 'kepala_sekolah'],
            'url' => App::baseUrl('admin/backup'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>'
        ],
        [
            'id' => 'notifikasi',
            'label' => 'Notifikasi & Pengumuman',
            'roles' => ['admin'],
            'url' => App::baseUrl('admin/notifikasi'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>'
        ],
    ],
    'Master Data' => [
        [
            'id' => 'buku_induk',
            'label' => 'Buku Induk Siswa',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/buku-induk'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        ],
        [
            'id' => 'alumni',
            'label' => 'Data Alumni / Mutasi',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/buku-induk/alumni'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>'
        ],
        [
            'id' => 'guru',
            'label' => 'Data Guru',
            'roles' => ['admin'],
            'url' => App::baseUrl('admin/guru'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        ],
        [
            'id' => 'siswa',
            'label' => 'Data Siswa & Barcode',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/siswa'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>'
        ],
        [
            'id' => 'kelas',
            'label' => 'Data Kelas',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/kelas'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
        ],
        [
            'id' => 'mapel',
            'label' => 'Mata Pelajaran',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/mapel'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>'
        ],
        [
            'id' => 'jadwal',
            'label' => 'Jadwal Pelajaran',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/jadwal'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        ],
    ],
    'Akademik & Penilaian' => [
        [
            'id' => 'nilai',
            'label' => 'Penilaian Semester (1-6)',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/nilai'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        ],
        [
            'id' => 'tapel',
            'label' => 'Tahun Pelajaran',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/akademik/tahun-pelajaran'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        ],
        [
            'id' => 'kenaikan',
            'label' => 'Kenaikan Kelas Komplit',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/akademik/kenaikan-kelas'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'
        ],
        [
            'id' => 'lms',
            'label' => 'Learning Management System',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/lms'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        ],
    ],

    'Persuratan Dinas' => [
        [
            'id' => 'surat',
            'label' => 'Arsip & Buat Surat / SPPD',
            'roles' => ['admin', 'kepala_sekolah'],
            'url' => App::baseUrl('admin/surat'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
        ],
        [
            'id' => 'kop_surat',
            'label' => 'Pengaturan KOP Surat',
            'roles' => ['admin'],
            'url' => App::baseUrl('admin/surat/pengaturan-kop'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
        ],
    ],
    'Monitoring & Gaji' => [
        [
            'id' => 'monitoring_guru',
            'label' => 'Monitoring Guru',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara'],
            'url' => App::baseUrl('admin/monitoring/guru'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
        [
            'id' => 'monitoring_siswa',
            'label' => 'Monitoring Siswa',
            'roles' => ['admin', 'kepala_sekolah', 'wakasek_kurikulum'],
            'url' => App::baseUrl('admin/monitoring/siswa'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>'
        ],
        [
            'id' => 'payroll',
            'label' => 'Penggajian & Slip',
            'roles' => ['admin', 'kepala_sekolah', 'bendahara'],
            'url' => App::baseUrl('payroll'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
        ],
    ],
    'Tabungan Siswa' => [
        [
            'id' => 'tabungan_program',
            'label' => 'Program Tabungan',
            'roles' => ['admin'],
            'url' => App::baseUrl('admin/tabungan/program'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ],

        [
            'id' => 'tabungan_monitoring',
            'label' => 'Monitoring Tabungan',
            'roles' => ['admin'],
            'url' => App::baseUrl('admin/tabungan/monitoring'),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>'
        ],
    ],
    'Akses Cepat' => [
        [
            'id' => 'scanner_piket',
            'label' => 'Scanner Gerbang',
            'roles' => ['admin'],
            'url' => App::baseUrl('piket/scanner'),
            'target' => '_blank',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>'
        ]
    ]
];
?>
<aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col flex-shrink-0 min-h-screen text-slate-300 select-none">
    <!-- Brand Header -->
    <div class="p-5 border-b border-slate-800/80 flex items-center gap-3">
        <?php if (!empty($config['logo_kop'])): ?>
            <img src="<?= App::baseUrl('uploads/' . htmlspecialchars($config['logo_kop'])) ?>" alt="Logo" class="w-10 h-10 rounded-xl object-contain bg-white shadow-glow-brand flex-shrink-0 p-1">
        <?php else: ?>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-brand-500 to-indigo-500 flex items-center justify-center font-display font-black text-white text-lg shadow-glow-brand flex-shrink-0">
                AF
            </div>
        <?php endif; ?>
        <div class="overflow-hidden">
            <div class="font-display font-bold text-white text-sm tracking-tight truncate leading-snug">
                <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?>
            </div>
            <div class="text-[11px] text-brand-400 font-semibold tracking-wide uppercase flex items-center gap-1.5 mt-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <?= ucwords(str_replace('_', ' ', $user['role'] ?? 'Admin')) ?>
            </div>
        </div>
    </div>

    <!-- Quick Switch to Guru Dashboard -->
    <div class="px-3 pt-4">
        <a href="<?= App::baseUrl('guru/dashboard') ?>" class="flex items-center justify-center gap-2 w-full py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-colors shadow-md shadow-blue-900/50">
            <span>🎓</span> Kembali ke Panel Guru
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
        <?php foreach ($navItems as $section => $items): 
            $allowedItems = array_filter($items, function($item) use ($user) {
                if (!isset($item['roles'])) return true;
                return in_array($user['role'], $item['roles']);
            });
            if (empty($allowedItems)) continue;
        ?>
        <div>
            <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                <?= $section ?>
            </div>
            <div class="space-y-1">
                <?php foreach ($allowedItems as $item): 
                    $isActive = ($activeNav === $item['id']);
                ?>
                <a href="<?= $item['url'] ?>" <?= !empty($item['target']) ? 'target="' . $item['target'] . '"' : '' ?>
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-150 <?= $isActive 
                       ? 'bg-brand-600 text-white shadow-soft-sm font-bold' 
                       : 'text-slate-400 hover:text-white hover:bg-slate-800/70' ?>">
                    <span class="<?= $isActive ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' ?>">
                        <?= $item['icon'] ?>
                    </span>
                    <span class="truncate"><?= $item['label'] ?></span>
                    <?php if (!empty($item['target'])): ?>
                        <svg class="w-3 h-3 ml-auto text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </nav>

    <!-- Footer Profile & Logout -->
    <div class="p-3 border-t border-slate-800/80 bg-slate-950/40">
        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/50 border border-slate-700/50">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-8 h-8 rounded-lg bg-brand-900/60 border border-brand-700/60 text-brand-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                    <?= strtoupper(substr($user['nama_lengkap'] ?? 'TU', 0, 2)) ?>
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs font-bold text-slate-200 truncate leading-tight">
                        <?= htmlspecialchars($user['nama_lengkap'] ?? 'Tata Usaha') ?>
                    </div>
                    <div class="text-[10px] text-slate-400 font-medium"><?= ucfirst($user['role'] ?? 'Admin') ?></div>
                </div>
            </div>
            <a href="<?= App::baseUrl('logout') ?>" 
               title="Keluar dari Sistem" 
               class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-950/50 transition"
               onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </a>
        </div>
    </div>
</aside>
