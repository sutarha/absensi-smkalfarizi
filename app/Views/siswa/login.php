<?php use App\Config\App; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e40af">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="Portal Siswa SMK Al-Farizi — Absensi, Nilai, LMS & Tabungan">
    <title>Login Siswa — SMK Al-Farizi</title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <style>
        body { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #172554 100%); min-height: 100dvh; }
        .glass-card { background: rgba(255,255,255,0.07); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.12); }
        .input-field { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #fff; transition: all 0.2s; }
        .input-field::placeholder { color: rgba(255,255,255,0.35); }
        .input-field:focus { background: rgba(255,255,255,0.13); border-color: #60a5fa; outline: none; box-shadow: 0 0 0 3px rgba(96,165,250,0.2); }
        .btn-login { background: linear-gradient(135deg, #2563eb, #4f46e5); box-shadow: 0 8px 24px rgba(37,99,235,0.4); transition: all 0.2s; }
        .btn-login:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 12px 30px rgba(37,99,235,0.5); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .floating-orb { position: absolute; border-radius: 50%; filter: blur(60px); animation: float 8s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0) scale(1)} 50%{transform:translateY(-20px) scale(1.05)} }
        .error-msg { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .logo-pulse { animation: logoPulse 3s ease-in-out infinite; }
        @keyframes logoPulse { 0%,100%{box-shadow:0 0 0 0 rgba(96,165,250,0.4)} 50%{box-shadow:0 0 0 16px rgba(96,165,250,0)} }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 font-sans overflow-hidden relative">

    <!-- Background Orbs -->
    <div class="floating-orb w-72 h-72 bg-blue-600/20 top-[-5%] left-[-10%]"></div>
    <div class="floating-orb w-96 h-96 bg-indigo-600/15 bottom-[-10%] right-[-5%]" style="animation-delay: -4s;"></div>
    <div class="floating-orb w-48 h-48 bg-sky-400/10 top-1/2 left-1/3" style="animation-delay: -2s;"></div>

    <div class="w-full max-w-sm relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-500 to-sky-400 flex items-center justify-center mx-auto mb-4 font-outfit font-black text-white text-3xl logo-pulse shadow-2xl">
                AF
            </div>
            <h1 class="text-2xl font-outfit font-bold text-white tracking-tight">SMK Al-Farizi</h1>
            <p class="text-blue-200/70 text-sm mt-1">Portal Digital Siswa</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-3xl p-7">
            <h2 class="text-white font-semibold text-lg mb-1">Masuk ke Akun Siswa</h2>
            <p class="text-blue-200/60 text-xs mb-6">Gunakan NISN dan tanggal lahir kamu</p>

            <!-- Error Message -->
            <div id="error-msg" class="error-msg rounded-xl px-4 py-3 text-xs font-medium mb-4 hidden flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="error-text"></span>
            </div>

            <form id="login-form" class="space-y-4" novalidate>
                <!-- NISN -->
                <div>
                    <label class="block text-blue-100/80 text-xs font-semibold mb-2 uppercase tracking-wide">NISN</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-300/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <input
                            type="text"
                            id="nisn"
                            name="nisn"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="10"
                            placeholder="Masukkan NISN kamu"
                            class="input-field w-full pl-10 pr-4 py-3 rounded-xl text-sm font-medium"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-blue-100/80 text-xs font-semibold mb-2 uppercase tracking-wide">Tanggal Lahir</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-300/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <input
                            type="date"
                            id="tanggal_lahir"
                            name="tanggal_lahir"
                            class="input-field w-full pl-10 pr-4 py-3 rounded-xl text-sm font-medium"
                            max="<?= date('Y-m-d') ?>"
                            autocomplete="bday"
                            required
                        >
                    </div>
                    <p class="text-blue-300/40 text-[11px] mt-1.5 ml-1">Format: tanggal lahir sesuai ijazah / KK</p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    id="btn-submit"
                    class="btn-login w-full py-3.5 rounded-xl text-white font-bold text-sm tracking-wide flex items-center justify-center gap-2 mt-6"
                >
                    <svg id="btn-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span id="btn-text">Masuk</span>
                </button>
            </form>

            <!-- Quick Demo Accounts -->
            <div class="mt-6 pt-5 border-t border-white/10">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-300/70">Akun Uji Coba Cepat:</span>
                    <span class="text-[10px] bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full font-medium">Demo</span>
                </div>
                <div class="space-y-2">
                    <button type="button" onclick="pilihDemo('0071234501', '2005-01-01')" class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl p-2.5 transition flex items-center justify-between group cursor-pointer">
                        <div>
                            <div class="text-xs font-bold text-white group-hover:text-blue-300 transition">Aditya Pratama (XI PPLG 1)</div>
                            <div class="text-[10px] text-blue-200/60">NISN: 0071234501 · Tgl: 2005-01-01</div>
                        </div>
                        <span class="text-xs text-blue-400 font-bold group-hover:translate-x-0.5 transition">Pilih &rarr;</span>
                    </button>
                    <button type="button" onclick="pilihDemo('0071234509', '2005-01-01')" class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl p-2.5 transition flex items-center justify-between group cursor-pointer">
                        <div>
                            <div class="text-xs font-bold text-white group-hover:text-blue-300 transition">Intan Permatasari (XI PPLG 1)</div>
                            <div class="text-[10px] text-blue-200/60">NISN: 0071234509 · Tgl: 2005-01-01</div>
                        </div>
                        <span class="text-xs text-blue-400 font-bold group-hover:translate-x-0.5 transition">Pilih &rarr;</span>
                    </button>
                    <button type="button" onclick="pilihDemo('0081234567', '2005-01-01')" class="w-full text-left bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl p-2.5 transition flex items-center justify-between group cursor-pointer">
                        <div>
                            <div class="text-xs font-bold text-white group-hover:text-blue-300 transition">Budi Santoso (X PPLG 1)</div>
                            <div class="text-[10px] text-blue-200/60">NISN: 0081234567 · Tgl: 2005-01-01</div>
                        </div>
                        <span class="text-xs text-blue-400 font-bold group-hover:translate-x-0.5 transition">Pilih &rarr;</span>
                    </button>
                </div>
            </div>

            <!-- Footer info -->
            <p class="text-center text-blue-200/40 text-[11px] mt-4">
                Lupa tanggal lahir? Hubungi wali kelas atau TU sekolah.
            </p>
        </div>

        <!-- Bottom brand -->
        <p class="text-center text-white/20 text-[11px] mt-6">
            SMK Al-Farizi &copy; <?= date('Y') ?> — Sistem Informasi Sekolah
        </p>
    </div>

<script>
const API_BASE = '<?= App::baseUrl('api/v1') ?>';

function pilihDemo(nisn, tgl) {
    document.getElementById('nisn').value = nisn;
    document.getElementById('tanggal_lahir').value = tgl;
    hideError();
    document.getElementById('btn-submit').focus();
}

document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const nisn = document.getElementById('nisn').value.trim();
    const tglLahir = document.getElementById('tanggal_lahir').value;
    const btn = document.getElementById('btn-submit');
    const btnText = document.getElementById('btn-text');
    const btnIcon = document.getElementById('btn-icon');

    if (!nisn || !tglLahir) {
        showError('NISN dan tanggal lahir wajib diisi.');
        return;
    }

    // Loading state
    btn.disabled = true;
    btnIcon.innerHTML = `<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>`;
    btnIcon.setAttribute('viewBox', '0 0 24 24');
    btnIcon.classList.add('animate-spin');
    btnText.textContent = 'Memverifikasi...';
    hideError();

    try {
        const res = await fetch(`${API_BASE}/siswa/login`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ nisn, tanggal_lahir: tglLahir })
        });

        const data = await res.json();

        if (data.success) {
            // Simpan token
            localStorage.setItem('siswa_token', data.token);
            localStorage.setItem('siswa_data', JSON.stringify(data.siswa));

            btnText.textContent = 'Berhasil!';
            btnIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>`;
            btnIcon.classList.remove('animate-spin');

            // Redirect ke dashboard
            setTimeout(() => {
                window.location.href = '<?= App::baseUrl('siswa') ?>';
            }, 600);
        } else {
            showError(data.message || 'Login gagal. Periksa NISN dan tanggal lahir.');
            resetBtn();
        }
    } catch (err) {
        showError('Tidak dapat terhubung ke server. Periksa koneksi internet.');
        resetBtn();
    }
});

function showError(msg) {
    const el = document.getElementById('error-msg');
    document.getElementById('error-text').textContent = msg;
    el.classList.remove('hidden');
    el.classList.add('flex');
}

function hideError() {
    const el = document.getElementById('error-msg');
    el.classList.add('hidden');
    el.classList.remove('flex');
}

function resetBtn() {
    const btn = document.getElementById('btn-submit');
    const btnText = document.getElementById('btn-text');
    const btnIcon = document.getElementById('btn-icon');
    btn.disabled = false;
    btnIcon.classList.remove('animate-spin');
    btnIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>`;
    btnText.textContent = 'Masuk';
}

// Redirect jika sudah login
if (localStorage.getItem('siswa_token')) {
    window.location.href = '<?= App::baseUrl('siswa') ?>';
}
</script>
</body>
</html>
