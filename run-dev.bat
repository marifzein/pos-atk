@echo off
echo Menjalankan Laravel Server dan Vite...

:: Membuka jendela baru untuk Laravel Serve di Port 3000
start "Laravel Serve" cmd /k "php artisan serve --port=3000"

:: Membuka jendela baru untuk NPM Vite
start "NPM Dev" cmd /k "npm run dev"

: Membuka jendela baru dan langsung masuk MySQL
start "MySQL Client" cmd /k "mysql -uroot -p"