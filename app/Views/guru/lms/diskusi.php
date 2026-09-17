<?php
use App\Config\App;
$currentUserId = $user['id'] ?? ($_SESSION['user']['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Forum Diskusi - <?= htmlspecialchars($config['nama_sekolah'] ?? 'Sekolah') ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
    <style>
        .chat-bubble { max-width: 85%; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-24">
        
        <!-- Mobile Header -->
        <header class="bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white shadow-soft-lg p-5 relative">
            <div class="flex items-center gap-3">
                <a href="<?= App::baseUrl('guru/lms') ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-base font-outfit font-extrabold tracking-tight text-white"><?= htmlspecialchars($mapelInfo['nama_mapel']) ?></h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1"></span> Live
                        </span>
                    </div>
                    <p class="text-xs text-slate-300">Kelas <?= htmlspecialchars($kelasInfo['nama_kelas']) ?></p>
                </div>
            </div>
        </header>

        <!-- 3 Modern Tabs -->
        <div class="flex border-b border-slate-200 bg-white shadow-sm sticky top-0 z-10">
            <a href="<?= App::baseUrl("guru/lms/materi/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1">
                <span>📁</span> Materi & Tugas
            </a>
            <a href="<?= App::baseUrl("guru/lms/ujian/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1">
                <span>📝</span> Tes / CBT
            </a>
            <a href="<?= App::baseUrl("guru/lms/diskusi/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold border-b-2 border-brand-600 text-brand-600 flex items-center justify-center gap-1">
                <span>💬</span> Diskusi
            </a>
        </div>

        <main class="p-4 flex-1 flex flex-col" id="chatContainer">
            <div class="flex-1 overflow-y-auto space-y-3 pb-3" id="messagesArea">
                <?php if(empty($diskusiList)): ?>
                    <div class="flex flex-col items-center justify-center h-48 text-slate-400" id="emptyState">
                        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <p class="text-xs">Belum ada obrolan. Mulai diskusi pertama dengan siswa!</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $lastDate = '';
                    foreach($diskusiList as $d): 
                        $currentDate = date('d M Y', strtotime($d['created_at']));
                        if ($currentDate !== $lastDate):
                            $lastDate = $currentDate;
                    ?>
                        <div class="flex justify-center my-2">
                            <span class="px-2.5 py-0.5 bg-slate-200/70 text-slate-600 text-[10px] font-bold rounded-full">
                                <?= $currentDate === date('d M Y') ? 'Hari Ini' : $currentDate ?>
                            </span>
                        </div>
                    <?php endif; ?>

                        <?php $isMe = ($d['user_type'] === 'guru' && (int)$d['user_id'] === (int)$currentUserId); ?>
                        <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?> group">
                            <div class="chat-bubble flex flex-col <?= $isMe ? 'items-end' : 'items-start' ?>">
                                <?php if(!$isMe): ?>
                                    <span class="text-[10px] font-bold text-slate-600 ml-1 mb-0.5 flex items-center gap-1">
                                        <?= htmlspecialchars($d['nama_pengirim'] ?? 'Siswa') ?>
                                        <?= $d['user_type'] === 'guru' ? '<span class="text-brand-600 font-extrabold text-[9px] bg-brand-50 px-1 rounded">(Guru)</span>' : '<span class="text-slate-400 font-medium text-[9px]">(Siswa)</span>' ?>
                                    </span>
                                <?php endif; ?>
                                
                                <div class="px-3.5 py-2 rounded-2xl text-xs shadow-sm leading-relaxed
                                    <?= $isMe 
                                        ? 'bg-brand-600 text-white rounded-tr-sm' 
                                        : ($d['user_type'] === 'guru' ? 'bg-amber-100 text-amber-900 rounded-tl-sm border border-amber-200' : 'bg-white text-slate-800 rounded-tl-sm border border-slate-200') 
                                    ?>">
                                    <?= nl2br(htmlspecialchars($d['pesan'])) ?>
                                </div>
                                <span class="text-[9px] text-slate-400 mt-0.5 <?= $isMe ? 'mr-1' : 'ml-1' ?>">
                                    <?= date('H:i', strtotime($d['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Chat Input Area (Realtime AJAX) -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-soft-md p-2 mt-auto flex items-end gap-2">
                <form id="chatForm" onsubmit="sendChat(event)" class="flex-1 flex items-end gap-2">
                    <textarea name="pesan" id="pesanInput" rows="1" 
                        class="w-full bg-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all resize-none max-h-24" 
                        placeholder="Ketik pesan atau balasan diskusi..." required></textarea>
                    
                    <button type="submit" id="btnSend" class="shrink-0 w-10 h-10 bg-brand-600 hover:bg-brand-700 text-white rounded-xl flex items-center justify-center transition shadow-soft-sm active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </form>
            </div>
        </main>
        
        <!-- Bottom Nav -->
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white/95 backdrop-blur-xl border-t border-slate-200/80 shadow-soft-xl flex justify-around py-2.5 px-2 z-30">
            <a href="<?= App::baseUrl('guru/dashboard') ?>" class="flex flex-col items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-brand-600 transition-colors">
                <span class="text-lg">📅</span><span>KBM</span>
            </a>
            <a href="<?= App::baseUrl('guru/nilai') ?>" class="flex flex-col items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-brand-600 transition-colors">
                <span class="text-lg">📝</span><span>Nilai</span>
            </a>
            <a href="<?= App::baseUrl('guru/lms') ?>" class="flex flex-col items-center gap-1 text-[10px] font-bold text-brand-600 transition-colors">
                <span class="text-lg">📚</span><span>LMS</span>
            </a>
        </nav>
    </div>

    <script>
        const KELAS_ID = <?= (int)$kelasInfo['id'] ?>;
        const MAPEL_ID = <?= (int)$mapelInfo['id'] ?>;
        const CURRENT_USER_ID = <?= (int)$currentUserId ?>;
        const MESSAGES_URL = '<?= App::baseUrl("guru/lms/diskusi/messages/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>';
        const SEND_URL = '<?= App::baseUrl("guru/lms/diskusi/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>';

        const area = document.getElementById('messagesArea');
        const input = document.getElementById('pesanInput');
        const btnSend = document.getElementById('btnSend');

        function scrollToBottom() {
            if (area) {
                area.scrollTop = area.scrollHeight;
            }
        }
        scrollToBottom();

        // Send via AJAX
        async function sendChat(e) {
            e.preventDefault();
            const text = input.value.trim();
            if(!text) return;

            btnSend.disabled = true;
            input.value = '';

            try {
                const formData = new FormData();
                formData.append('pesan', text);

                const res = await fetch(SEND_URL, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                await fetchMessages();
            } catch(err) {
                console.error('Gagal mengirim pesan', err);
            } finally {
                btnSend.disabled = false;
                input.focus();
            }
        }

        // Auto Poll every 3 seconds for realtime sync
        let lastMsgCount = <?= count($diskusiList) ?>;

        async function fetchMessages() {
            try {
                const res = await fetch(MESSAGES_URL);
                const json = await res.json();
                if(json.success && Array.isArray(json.data)) {
                    if(json.data.length !== lastMsgCount || lastMsgCount === 0) {
                        lastMsgCount = json.data.length;
                        renderMessages(json.data);
                    }
                }
            } catch(e) {
                console.warn('Sync diskusi error', e);
            }
        }

        function escapeHtml(str) {
            return (str || '').replace(/[&<>"']/g, function(m) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
            });
        }

        function renderMessages(list) {
            if(!list.length) {
                area.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-48 text-slate-400" id="emptyState">
                        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <p class="text-xs">Belum ada obrolan. Mulai diskusi pertama dengan siswa!</p>
                    </div>`;
                return;
            }

            let html = '';
            let lastDate = '';

            list.forEach(d => {
                const dateObj = new Date(d.created_at);
                const dateStr = dateObj.toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                const timeStr = dateObj.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});

                if (dateStr !== lastDate) {
                    lastDate = dateStr;
                    html += `
                        <div class="flex justify-center my-2">
                            <span class="px-2.5 py-0.5 bg-slate-200/70 text-slate-600 text-[10px] font-bold rounded-full">
                                ${dateStr}
                            </span>
                        </div>
                    `;
                }

                const isMe = (d.user_type === 'guru' && parseInt(d.user_id) === CURRENT_USER_ID);
                const senderRole = d.user_type === 'guru' ? '<span class="text-brand-600 font-extrabold text-[9px] bg-brand-50 px-1 rounded">(Guru)</span>' : '<span class="text-slate-400 font-medium text-[9px]">(Siswa)</span>';
                
                const bubbleStyle = isMe 
                    ? 'bg-brand-600 text-white rounded-tr-sm' 
                    : (d.user_type === 'guru' ? 'bg-amber-100 text-amber-900 rounded-tl-sm border border-amber-200' : 'bg-white text-slate-800 rounded-tl-sm border border-slate-200');

                html += `
                    <div class="flex ${isMe ? 'justify-end' : 'justify-start'} group">
                        <div class="chat-bubble flex flex-col ${isMe ? 'items-end' : 'items-start'}">
                            ${!isMe ? `<span class="text-[10px] font-bold text-slate-600 ml-1 mb-0.5 flex items-center gap-1">${escapeHtml(d.nama_pengirim || 'Siswa')} ${senderRole}</span>` : ''}
                            <div class="px-3.5 py-2 rounded-2xl text-xs shadow-sm leading-relaxed ${bubbleStyle}">
                                ${escapeHtml(d.pesan).replace(/\n/g, '<br>')}
                            </div>
                            <span class="text-[9px] text-slate-400 mt-0.5 ${isMe ? 'mr-1' : 'ml-1'}">
                                ${timeStr}
                            </span>
                        </div>
                    </div>
                `;
            });

            area.innerHTML = html;
            scrollToBottom();
        }

        // Live polling every 3 seconds
        setInterval(fetchMessages, 3000);
    </script>
</body>
</html>
