@echo off
title Server SMK Al-Farizi (Localhost:8000)
echo ========================================================
echo   MENJALANKAN SERVER APLIKASI PRESENSI SMK AL-FARIZI
echo ========================================================
echo.
echo 1. Pastikan MySQL di Laragon / XAMPP sudah AKTIF (Running).
echo 2. Membuka browser ke http://127.0.0.1:8000 ...
echo.
echo Tekan Ctrl + C pada jendela ini jika ingin mematikan server.
echo ========================================================
echo.

start http://127.0.0.1:8000
php -S 127.0.0.1:8000 -t public
pause
