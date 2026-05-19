# Deployment Checklist

Berikut adalah checklist yang harus dipastikan sebelum dan sesaat setelah melakukan deployment aplikasi ke server production:

- [ ] **1. .env production values**
  Pastikan variable environment utama disesuaikan untuk production:
  ```env
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://domain-anda.com
  ```

- [ ] **2. Cache Configuration**
  Jalankan perintah berikut untuk mempercepat load config:
  ```bash
  php artisan config:cache
  ```

- [ ] **3. Cache Routes**
  Jalankan perintah berikut untuk mempercepat route resolution:
  ```bash
  php artisan route:cache
  ```

- [ ] **4. Cache Views**
  Jalankan perintah berikut untuk pre-compile blade templates:
  ```bash
  php artisan view:cache
  ```

- [ ] **5. Storage Link**
  Pastikan symlink untuk storage publik sudah dibuat agar file statis bisa diakses:
  ```bash
  php artisan storage:link
  ```

- [ ] **6. Queue Worker**
  Pastikan queue worker berjalan di background secara terus-menerus menggunakan Supervisor (atau tool serupa) untuk memproses background jobs. Contoh command yang perlu dijalankan supervisor:
  ```bash
  php artisan queue:work --tries=3 --timeout=90
  ```

- [ ] **7. Cron untuk Laravel Scheduler**
  Setup cron entry di server untuk menjalankan task scheduler Laravel setiap menit:
  ```bash
  * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
  ```

- [ ] **8. S3/R2 Bucket Permissions**
  Pastikan konfigurasi S3 atau Cloudflare R2 bucket diset ke **private** (bukan public), kecuali jika file di-host secara langsung menggunakan custom domain dan public bucket. Pastikan file aman!

- [ ] **9. Database Backup Strategy**
  Pastikan ada skrip backup database secara berkala (misalnya menggunakan spatie/laravel-backup yang dijalankan via scheduler).

- [ ] **10. SSL Certificate**
  Pastikan SSL Certificate (misal Let's Encrypt) terinstal di server dan trafik HTTP di-redirect ke HTTPS dengan aman.
