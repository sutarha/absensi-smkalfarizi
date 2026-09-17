<?php
use App\Config\App;
?>
<!-- Google Fonts: Plus Jakarta Sans & Outfit -->
<meta name="csrf-token" content="<?= \App\Helpers\CsrfHelper::getToken() ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Tailwind CSS v3 via CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb', // Royal Sapphire Primary
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
                slate: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                },
                emerald: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                },
                amber: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                },
                rose: {
                    50: '#fff1f2',
                    100: '#ffe4e6',
                    500: '#f43f5e',
                    600: '#e11d48',
                    700: '#be123c',
                }
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                outfit: ['"Outfit"', 'sans-serif'],
            },
            boxShadow: {
                'soft-sm': '0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.04)',
                'soft-md': '0 4px 14px -2px rgba(15, 23, 42, 0.07), 0 2px 6px -2px rgba(15, 23, 42, 0.04)',
                'soft-lg': '0 10px 25px -3px rgba(15, 23, 42, 0.08), 0 4px 10px -4px rgba(15, 23, 42, 0.04)',
                'soft-xl': '0 20px 30px -4px rgba(15, 23, 42, 0.1), 0 8px 12px -4px rgba(15, 23, 42, 0.05)',
                'glow-brand': '0 4px 20px -2px rgba(37, 99, 235, 0.35)',
                'glow-emerald': '0 4px 20px -2px rgba(5, 150, 105, 0.35)',
            },
            borderRadius: {
                'xl': '16px',
                '2xl': '20px',
                '3xl': '28px',
            }
        }
    }
}

// Intercept Fetch API untuk mengirimkan CSRF token secara otomatis ke header
const originalFetch = window.fetch;
window.fetch = async function() {
    let [resource, config] = arguments;
    if (!config) {
        config = {};
    }
    
    // Jangan ubah requests ke eksternal API, hanya internal
    const urlStr = typeof resource === 'string' ? resource : resource.url;
    if (!urlStr || urlStr.startsWith('/') || urlStr.startsWith(window.location.origin) || !urlStr.startsWith('http')) {
        config.headers = {
            ...config.headers,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        };
    }
    
    return originalFetch(resource, config);
};
</script>

<!-- App Core CSS (Animasi Khusus, Laser Scan, Print Media) -->
<link rel="stylesheet" href="<?= App::baseUrl('css/app.css') ?>">
