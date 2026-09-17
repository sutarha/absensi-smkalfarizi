<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Detail Tabungan - Guru</title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col relative pb-28">
        
        <!-- Bright Smartphone Top Header -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs px-5 py-3.5">
            <div class="flex items-center gap-3">
                <a href="<?= App::baseUrl('guru/tabungan') ?>" class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center text-base font-bold transition border border-indigo-200/80 shadow-xs shrink-0" title="Kembali">
                    ◀
                </a>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200/80 uppercase tracking-wider">
                            Rincian Program
                        </span>
                    </div>
                    <h1 class="text-sm font-bold text-slate-900 truncate mt-0.5"><?= htmlspecialchars($program['nama_program']) ?></h1>
                    <p class="text-[10px] text-slate-500 font-mono">Target: Rp <?= number_format($program['target_nominal'], 0, ',', '.') ?></p>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-4 space-y-4 pb-24">
            <?php if(isset($_SESSION['flash_success'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl text-sm font-medium">
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_SESSION['flash_error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm font-medium">
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-2">
                <h2 class="font-bold text-slate-700 text-sm">Daftar Peserta (<?= count($siswaPeserta) ?> Siswa)</h2>
                <?php if($isPengelola): ?>
                <button onclick="document.getElementById('modalDaftar').classList.remove('hidden')" class="text-xs font-semibold bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full hover:bg-brand-200 transition">
                    + Daftarkan Siswa
                </button>
                <?php endif; ?>
            </div>

            <?php if(!empty($siswaPeserta)): ?>
            <!-- Search Bar -->
            <div class="mb-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" id="searchInputPeserta" onkeyup="searchPeserta()" placeholder="Cari nama siswa..." class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-brand-500 focus:border-brand-500 shadow-sm outline-none transition">
                </div>
            </div>
            <?php endif; ?>

            <div class="space-y-3">
                <?php if(empty($siswaPeserta)): ?>
                    <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-sm">
                        <p class="text-sm text-slate-500">Belum ada siswa yang didaftarkan.</p>
                    </div>
                <?php else: ?>
                    <div id="no_peserta_msg" class="hidden bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-sm">
                        <p class="text-sm text-slate-500">Siswa tidak ditemukan.</p>
                    </div>
                <?php endif; ?>

                <?php foreach($siswaPeserta as $sp): ?>
                <?php 
                    $persen = 0;
                    if ($program['target_nominal'] > 0) {
                        $persen = min(100, round(($sp['total_terkumpul'] / $program['target_nominal']) * 100));
                    }
                ?>
                <div class="peserta-item bg-white rounded-2xl p-4 border border-slate-200 shadow-sm relative overflow-hidden" data-nama="<?= strtolower(htmlspecialchars($sp['nama_siswa'], ENT_QUOTES)) ?>">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($sp['nama_siswa']) ?></h3>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($sp['nisn']) ?> &bull; <?= htmlspecialchars($sp['nama_kelas']) ?></p>
                        </div>
                        <?php if($sp['status'] == 'lunas'): ?>
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">LUNAS</span>
                        <?php elseif($sp['status'] == 'mundur'): ?>
                            <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full">MUNDUR</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex justify-between items-end mt-3 mb-1">
                        <span class="text-xs text-slate-500 font-medium">Terkumpul:</span>
                        <span class="font-bold text-slate-800 text-sm">Rp <?= number_format($sp['total_terkumpul'], 0, ',', '.') ?></span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 mb-3">
                        <div class="bg-brand-500 h-1.5 rounded-full" style="width: <?= $persen ?>%"></div>
                    </div>

                    <?php if($sp['status'] != 'lunas'): ?>
                        <?php if($isPengelola): ?>
                        <button onclick="bukaModalSetor(<?= $sp['id'] ?>, '<?= htmlspecialchars($sp['nama_siswa'], ENT_QUOTES) ?>')" class="w-full bg-emerald-50 text-emerald-700 font-semibold py-2 rounded-xl text-center text-sm hover:bg-emerald-100 transition border border-emerald-100">
                            Catat Setoran
                        </button>
                        <?php endif; ?>
                    <?php else: ?>
                    <div class="w-full bg-slate-50 text-slate-400 font-semibold py-2 rounded-xl text-center text-sm border border-slate-100">
                        Selesai
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <?php if($isPengelola): ?>
    <!-- Modal Daftar -->
    <div id="modalDaftar" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex justify-center items-end sm:items-center">
        <div class="bg-white w-full max-w-md sm:rounded-2xl rounded-t-2xl shadow-2xl overflow-hidden transition-all transform translate-y-0 max-h-[90vh] flex flex-col">
            <form action="<?= \App\Config\App::baseUrl('guru/tabungan/daftar/' . $program['id']) ?>" method="POST" class="flex flex-col h-full overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0 z-10 shrink-0">
                    <h3 class="font-bold text-slate-800">Daftarkan Siswa Baru</h3>
                    <button type="button" onclick="document.getElementById('modalDaftar').classList.add('hidden')" class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5 overflow-y-auto flex-1 bg-slate-50/50">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas</label>
                    <select id="filter_kelas" class="w-full border-slate-200 rounded-xl shadow-sm focus:ring-brand-500 focus:border-brand-500 py-3 px-4 bg-white mb-4" onchange="filterSiswaByKelas()">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="flex justify-between items-end mb-2">
                        <label class="block text-sm font-semibold text-slate-700">Pilih Siswa</label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="check_all_siswa" onchange="toggleCheckAllSiswa(this)" class="rounded text-brand-500 focus:ring-brand-500 border-slate-300 w-4 h-4">
                            <span class="ml-2 text-xs text-slate-600 font-medium">Pilih Semua</span>
                        </label>
                    </div>
                    
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1" id="list_siswa_checkboxes">
                        <?php foreach($siswaAll as $s): ?>
                            <label class="siswa-item flex items-center p-3 border border-slate-200 rounded-xl bg-white hover:border-brand-300 hover:bg-brand-50/30 cursor-pointer transition" data-kelas="<?= $s['kelas_id'] ?>">
                                <input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>" class="siswa-checkbox w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                <div class="ml-3">
                                    <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($s['nama_siswa']) ?></div>
                                    <div class="text-xs text-slate-500"><?= htmlspecialchars($s['nama_kelas'] ?? '-') ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                        
                        <div id="no_siswa_msg" class="hidden py-4 text-center text-sm text-slate-500">
                            Tidak ada siswa di kelas ini.
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-100 bg-white shrink-0">
                    <button type="submit" class="w-full bg-brand-600 text-white font-bold rounded-xl py-3.5 hover:bg-brand-700 transition shadow-md shadow-brand-500/20">
                        Daftarkan Terpilih
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Setor -->
    <div id="modalSetor" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex justify-center items-end sm:items-center">
        <div class="bg-white w-full max-w-md sm:rounded-2xl rounded-t-2xl shadow-2xl overflow-hidden transition-all transform translate-y-0">
            <form id="formSetor" method="POST">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white">
                    <h3 class="font-bold text-slate-800">Catat Setoran</h3>
                    <button type="button" onclick="document.getElementById('modalSetor').classList.add('hidden')" class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nama Siswa</label>
                        <input type="text" id="setor_nama_siswa" readonly class="w-full border-0 bg-slate-50 rounded-xl py-3 px-4 text-slate-700 font-medium font-sans shadow-inner">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" name="jumlah" required class="w-full border-slate-200 rounded-xl shadow-sm focus:ring-emerald-500 focus:border-emerald-500 py-3 px-4 font-bold text-lg" placeholder="10000">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Catatan</label>
                        <input type="text" name="catatan" class="w-full border-slate-200 rounded-xl shadow-sm focus:ring-emerald-500 focus:border-emerald-500 py-3 px-4" placeholder="Opsional">
                    </div>
                </div>
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    <button type="submit" class="w-full bg-emerald-600 text-white font-bold rounded-xl py-3.5 hover:bg-emerald-700 transition shadow-md shadow-emerald-500/20">
                        Simpan Setoran
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
    function bukaModalSetor(tsId, namaSiswa) {
        document.getElementById('formSetor').action = "<?= \App\Config\App::baseUrl('guru/tabungan/setoran/') ?>" + tsId;
        document.getElementById('setor_nama_siswa').value = namaSiswa;
        document.getElementById('modalSetor').classList.remove('hidden');
    }

    function searchPeserta() {
        let input = document.getElementById('searchInputPeserta').value.toLowerCase();
        let items = document.querySelectorAll('.peserta-item');
        let count = 0;

        items.forEach(function(item) {
            let nama = item.getAttribute('data-nama');
            if (nama.indexOf(input) > -1) {
                item.style.display = "";
                count++;
            } else {
                item.style.display = "none";
            }
        });

        let noResultMsg = document.getElementById('no_peserta_msg');
        if (noResultMsg) {
            if (count === 0 && items.length > 0) {
                noResultMsg.classList.remove('hidden');
            } else {
                noResultMsg.classList.add('hidden');
            }
        }
    }

    function filterSiswaByKelas() {
        const selectedKelas = document.getElementById('filter_kelas').value;
        const items = document.querySelectorAll('.siswa-item');
        let visibleCount = 0;

        items.forEach(item => {
            if (selectedKelas === "" || item.dataset.kelas === selectedKelas) {
                item.classList.remove('hidden');
                item.classList.add('flex');
                visibleCount++;
            } else {
                item.classList.add('hidden');
                item.classList.remove('flex');
                // uncheck when hidden
                const cb = item.querySelector('.siswa-checkbox');
                if (cb) cb.checked = false;
            }
        });

        // update "check all" status
        document.getElementById('check_all_siswa').checked = false;

        const msg = document.getElementById('no_siswa_msg');
        if (visibleCount === 0) {
            msg.classList.remove('hidden');
        } else {
            msg.classList.add('hidden');
        }
    }

    function toggleCheckAllSiswa(el) {
        const isChecked = el.checked;
        const items = document.querySelectorAll('.siswa-item:not(.hidden) .siswa-checkbox');
        items.forEach(cb => {
            cb.checked = isChecked;
        });
    }
    </script>

    <!-- Bottom Navigation Mobile -->
    <?php require __DIR__ . '/partials/bottom_nav.php'; ?>
</body>
</html>
