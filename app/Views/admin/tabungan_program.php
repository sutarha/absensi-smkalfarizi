<?php
use App\Config\App;
$activeNav = 'tabungan_program';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Tabungan - Admin</title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">


<div class="p-4 sm:ml-64 mt-14 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Program Tabungan</h1>
            <p class="text-gray-600 text-sm">Kelola tujuan tabungan siswa (Study Tour, Kaos Olahraga, dll)</p>
        </div>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Program
        </button>
    </div>

    <?php if(isset($_SESSION['flash_success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($programList as $p): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <?php if($p['foto']): ?>
                <img src="<?= \App\Config\App::baseUrl($p['foto']) ?>" class="w-full h-40 object-cover">
            <?php else: ?>
                <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            <?php endif; ?>
            <div class="p-5">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-lg font-bold text-gray-800 leading-tight"><?= htmlspecialchars($p['nama_program']) ?></h3>
                    <?php if($p['status'] == 'aktif'): ?>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Aktif</span>
                    <?php elseif($p['status'] == 'selesai'): ?>
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">Selesai</span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">Batal</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($p['deskripsi'] ?? 'Tidak ada deskripsi') ?></p>
                
                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex justify-between border-b pb-1">
                        <span>Target Nominal:</span>
                        <span class="font-bold text-gray-800">Rp <?= number_format($p['target_nominal'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between border-b pb-1">
                        <span>Target Waktu:</span>
                        <span class="font-bold text-gray-800"><?= $p['target_tanggal'] ? date('d M Y', strtotime($p['target_tanggal'])) : 'Tidak ditentukan' ?></span>
                    </div>
                    <div class="flex justify-between pb-1 border-b">
                        <span>Sasaran:</span>
                        <span class="font-bold text-gray-800"><?= $p['kelas_id'] ? $p['nama_kelas'] : 'Semua Kelas' ?></span>
                    </div>
                    <div class="flex justify-between pb-1">
                        <span>Pengelola:</span>
                        <span class="font-bold text-blue-600"><?= $p['pengelola_id'] ? htmlspecialchars($p['nama_pengelola']) : 'Belum ditentukan' ?></span>
                    </div>
                    <?php if(!empty($p['asisten_pengelola_id'])): ?>
                    <div class="flex justify-between pb-1">
                        <span>Asisten:</span>
                        <span class="font-bold text-indigo-600"><?= htmlspecialchars($p['nama_asisten']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="flex gap-2">
                    <button onclick="editProgram(<?= htmlspecialchars(json_encode($p)) ?>)" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded font-medium hover:bg-gray-200 text-sm">Edit</button>
                    <form action="<?= \App\Config\App::baseUrl('admin/tabungan/program/delete/' . $p['id']) ?>" method="POST" class="flex-1" onsubmit="return confirm('Hapus program ini?')">
                        <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded font-medium hover:bg-red-100 text-sm">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-screen overflow-y-auto">
        <form action="<?= \App\Config\App::baseUrl('admin/tabungan/program/store') ?>" method="POST" enctype="multipart/form-data">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Tambah Program Tabungan</h3>
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program</label>
                    <input type="text" name="nama_program" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Kaos Olahraga">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Nominal (Rp)</label>
                    <input type="text" name="target_nominal" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="100.000">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Tanggal Lunas</label>
                        <input type="date" name="target_tanggal" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas Sasaran</label>
                        <select name="kelas_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Kelas</option>
                            <?php foreach($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pengelola Program (Guru)</label>
                    <select name="pengelola_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Guru Pengelola --</option>
                        <?php foreach($guruList as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asisten Pengelola (Opsional)</label>
                    <select name="asisten_pengelola_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Asisten Pengelola --</option>
                        <?php foreach($guruList as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pengelola dan Asisten dapat mengelola setoran tabungan.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto / Gambar (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-600 bg-white focus:outline-none">
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end gap-3 bg-gray-50 rounded-b-xl">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Program</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-screen overflow-y-auto">
        <form id="formEdit" method="POST" enctype="multipart/form-data">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Edit Program Tabungan</h3>
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program</label>
                    <input type="text" name="nama_program" id="edit_nama" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_desc" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Nominal (Rp)</label>
                    <input type="text" name="target_nominal" id="edit_nominal" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Tanggal</label>
                        <input type="date" name="target_tanggal" id="edit_tanggal" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas Sasaran</label>
                        <select name="kelas_id" id="edit_kelas" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Kelas</option>
                            <?php foreach($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pengelola Program (Guru)</label>
                    <select name="pengelola_id" id="edit_pengelola" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Guru Pengelola --</option>
                        <?php foreach($guruList as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asisten Pengelola (Opsional)</label>
                    <select name="asisten_pengelola_id" id="edit_asisten_pengelola" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Asisten Pengelola --</option>
                        <?php foreach($guruList as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Program</label>
                    <select name="status" id="edit_status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai (Target Tercapai)</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Update Foto (Biarkan kosong jika tidak diganti)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-600 bg-white focus:outline-none">
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end gap-3 bg-gray-50 rounded-b-xl">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editProgram(p) {
    document.getElementById('formEdit').action = "<?= \App\Config\App::baseUrl('admin/tabungan/program/update/') ?>" + p.id;
    document.getElementById('edit_nama').value = p.nama_program;
    document.getElementById('edit_desc').value = p.deskripsi || '';
    document.getElementById('edit_nominal').value = parseFloat(p.target_nominal);
    document.getElementById('edit_tanggal').value = p.target_tanggal || '';
    document.getElementById('edit_kelas').value = p.kelas_id || '';
    document.getElementById('edit_pengelola').value = p.pengelola_id || '';
    document.getElementById('edit_asisten_pengelola').value = p.asisten_pengelola_id || '';
    document.getElementById('edit_status').value = p.status || 'aktif';
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>

    </main>
</body>
</html>
