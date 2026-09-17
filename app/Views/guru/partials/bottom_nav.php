<?php
use App\Config\App;
$activeNav = $activeNav ?? 'beranda';
?>
<!-- Bottom Navigation (Smartphone App Style) -->
<nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] flex justify-around items-center py-2 px-1">
    <!-- 1. KBM / Beranda -->
    <a href="<?= App::baseUrl('guru/dashboard') ?>" 
       class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-2xl transition-all duration-200 <?= $activeNav === 'beranda' ? 'text-blue-600 font-extrabold scale-105' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-lg <?= $activeNav === 'beranda' ? 'bg-blue-50 text-blue-600 shadow-sm border border-blue-200/60' : '' ?>">
            📅
        </div>
        <span class="text-[10px] tracking-tight leading-none mt-0.5">KBM</span>
    </a>

    <!-- 2. Nilai -->
    <a href="<?= App::baseUrl('guru/nilai') ?>" 
       class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-2xl transition-all duration-200 <?= $activeNav === 'nilai' ? 'text-blue-600 font-extrabold scale-105' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-lg <?= $activeNav === 'nilai' ? 'bg-blue-50 text-blue-600 shadow-sm border border-blue-200/60' : '' ?>">
            📝
        </div>
        <span class="text-[10px] tracking-tight leading-none mt-0.5">Nilai</span>
    </a>

    <!-- 3. LMS -->
    <a href="<?= App::baseUrl('guru/lms') ?>" 
       class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-2xl transition-all duration-200 <?= $activeNav === 'lms' ? 'text-purple-600 font-extrabold scale-105' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-lg <?= $activeNav === 'lms' ? 'bg-purple-50 text-purple-600 shadow-sm border border-purple-200/60' : '' ?>">
            📚
        </div>
        <span class="text-[10px] tracking-tight leading-none mt-0.5">LMS</span>
    </a>

    <!-- 4. Tabungan -->
    <a href="<?= App::baseUrl('guru/tabungan') ?>" 
       class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-2xl transition-all duration-200 <?= $activeNav === 'tabungan' ? 'text-indigo-600 font-extrabold scale-105' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-lg <?= $activeNav === 'tabungan' ? 'bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-200/60' : '' ?>">
            💳
        </div>
        <span class="text-[10px] tracking-tight leading-none mt-0.5">Tabungan</span>
    </a>

    <!-- 5. Honor -->
    <a href="<?= App::baseUrl('guru/dompet') ?>" 
       class="flex flex-col items-center gap-0.5 py-1 px-2 rounded-2xl transition-all duration-200 <?= $activeNav === 'honor' ? 'text-emerald-600 font-extrabold scale-105' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-lg <?= $activeNav === 'honor' ? 'bg-emerald-50 text-emerald-600 shadow-sm border border-emerald-200/60' : '' ?>">
            💰
        </div>
        <span class="text-[10px] tracking-tight leading-none mt-0.5">Honor</span>
    </a>
</nav>
