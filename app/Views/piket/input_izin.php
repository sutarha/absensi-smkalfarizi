<?php
use App\Config\App;
use App\Models\Siswa;
$siswaList = Siswa::getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Input Izin Siswa - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-12">
        <!-- Top Navbar -->
        <div class="bg-slate-900 text-white px-5 py-4 flex items-center justify-between shadow-soft-md sticky top-0 z-20">
            <a href="<?= App::baseUrl('piket/scanner') ?>" 
               class="text-sky-400 hover:text-sky-300 text-xs font-semibold flex items-center gap-1 transition-all duration-150">
                <span>◀</span>
                <span>Scanner</span>
            </a>
            <h1 class="font-outfit font-bold text-sm text-white">Input Izin / Sakit</h1>
            <div class="w-12"></div>
        </div>

        <div class="p-5 flex-1 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm">
                <form id="formInputIzin" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Siswa</label>
                        <select name="siswa_id" id="siswa_id" class="w-full" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_siswa']) ?> (<?= htmlspecialchars($s['nama_kelas']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/15" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kehadiran</label>
                        <select name="status_kehadiran" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/15" required>
                            <option value="SAKIT">SAKIT</option>
                            <option value="IZIN">IZIN</option>
                            <option value="TUGAS_LUAR">TUGAS LUAR / DISPENSASI</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Alasan</label>
                        <textarea name="keterangan" rows="3" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/15" placeholder="Cth: Surat dokter / Acara keluarga" required></textarea>
                    </div>

                    <button type="submit" id="btnSubmit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl shadow-soft-md transition-all active:scale-[0.98]">
                        Simpan Data
                    </button>
                    <div id="alertMsg" class="hidden text-xs text-center font-semibold rounded-xl p-3 mt-3"></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#siswa_id').select2({
                placeholder: "-- Cari Nama Siswa --",
                width: '100%'
            });

            $('#formInputIzin').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btnSubmit');
                const alertBox = $('#alertMsg');
                
                btn.prop('disabled', true).text('Menyimpan...');
                alertBox.addClass('hidden').removeClass('bg-emerald-100 text-emerald-700 bg-red-100 text-red-700');

                $.ajax({
                    url: '<?= App::baseUrl('piket/input-izin') ?>',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        alertBox.html(res.message).addClass('bg-emerald-100 text-emerald-700').removeClass('hidden');
                        setTimeout(() => {
                            window.location.href = '<?= App::baseUrl('piket/riwayat') ?>';
                        }, 1500);
                    },
                    error: function(err) {
                        let msg = err.responseJSON?.message || 'Terjadi kesalahan sistem';
                        alertBox.html(msg).addClass('bg-red-100 text-red-700').removeClass('hidden');
                        btn.prop('disabled', false).text('Simpan Data');
                    }
                });
            });
        });
    </script>
</body>
</html>
