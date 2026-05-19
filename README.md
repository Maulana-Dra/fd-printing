# FD Printing

Aplikasi Katalog dan Pemesanan Percetakan (Printing) modern berbasis website yang dibangun menggunakan Laravel 11, Livewire 3, dan Filament v3. Sistem ini memfasilitasi pelanggan untuk menelusuri katalog produk cetak, melakukan pemesanan, serta memfasilitasi pihak admin dalam manajemen produk, kategori, pelanggan, transaksi, dan konfirmasi pembayaran.

## ✨ Fitur Utama

### 🛒 Untuk Pelanggan (Front-End)
- **Katalog Produk Dinamis**: Penjelajahan produk cetak dengan filter berdasarkan kategori dan pencarian.
- **Sistem Keranjang (Cart)**: Manajemen pesanan yang mudah sebelum melakukan checkout.
- **Checkout & Pembayaran**: Mendukung multi-metode pembayaran (Transfer Bank, QRIS, e-Wallet).
- **Manajemen Akun & Riwayat Pesanan**: Pelanggan dapat memantau status pesanan mereka secara real-time.
- **Upload Bukti Pembayaran**: Pelanggan dapat mengunggah bukti pembayaran untuk pesanan manual.

### 🛡️ Untuk Admin (Back-End / Filament Panel)
- **Dashboard Analitik**: Ringkasan data pesanan, pendapatan, dan statistik pelanggan.
- **Manajemen Katalog**:
  - **Kategori**: Tambah, edit, dan kelola ikon/visibilitas kategori produk.
  - **Produk**: Manajemen detail produk, harga, diskon, opsi kustomisasi (warna, bahan, ukuran), dan multi-gambar S3/Cloudflare R2.
- **Manajemen Transaksi**:
  - **Pesanan (Orders)**: Kelola status pesanan dari "Menunggu Pembayaran" hingga "Selesai/Dikirim".
  - **Konfirmasi Pembayaran**: Verifikasi bukti transfer dan pembayaran dari pelanggan secara manual.
- **Manajemen Pengguna**: Kelola data pelanggan dan hak akses admin.

## 🛠️ Tech Stack

- **Framework**: [Laravel 11.x](https://laravel.com)
- **Admin Panel**: [Filament v3](https://filamentphp.com) (TALL Stack: TailwindCSS, Alpine.js, Laravel, Livewire)
- **Database**: MySQL / MariaDB
- **File Storage**: Cloudflare R2 / AWS S3 (untuk aset gambar & ikon)
- **Styling**: Tailwind CSS (Custom front-end)

## 📋 Persyaratan Sistem

Pastikan server atau environment lokal Anda memenuhi persyaratan berikut:
- PHP >= 8.2
- Composer 2.x
- Node.js & NPM (untuk compile aset frontend)
- MySQL >= 8.0 atau MariaDB >= 10.3
- Extension PHP: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `intl`, `gd`/`imagick`.

## 🚀 Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di environment lokal Anda:

1. **Clone repository ini**
   ```bash
   git clone https://github.com/username-anda/fd-printing.git
   cd fd-printing
   ```

2. **Install dependency PHP**
   ```bash
   composer install
   ```

3. **Install dependency Node.js & Compile assets**
   ```bash
   npm install
   npm run build
   ```

4. **Siapkan konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit file `.env` dan sesuaikan kredensial koneksi Database (`DB_*`), Mail SMTP (`MAIL_*`), dan S3/R2 (`AWS_*`).*

5. **Jalankan Migrasi Database dan Seeder**
   ```bash
   php artisan migrate --seed
   ```
   *(Catatan: Seeder akan membuat akun Admin default dan beberapa data dummy awal jika tersedia)*

6. **Tautkan Storage**
   ```bash
   php artisan storage:link
   ```

7. **Jalankan Development Server**
   ```bash
   php artisan serve
   ```
   Buka `http://localhost:8000` di browser untuk tampilan pelanggan, dan `http://localhost:8000/admin` untuk akses dashboard Filament Admin.

## 📁 Struktur Direktori Penting

- `app/Filament/` - Konfigurasi Resource, Page, dan Widget untuk Admin Panel (Filament).
- `app/Http/Controllers/` - Logika *routing* dan kontroler untuk halaman Front-End pelanggan.
- `app/Models/` - Definisi Eloquent Model dan relasi antar tabel (Product, Category, Order, User, dll).
- `resources/views/` - File Blade template untuk tampilan website Front-End.
- `routes/` - Definisi rute (`web.php` untuk frontend). Rute admin panel dihandle otomatis oleh Filament.

## 📦 Deployment ke Production

Untuk panduan, optimasi, dan checklist langkah demi langkah saat merilis aplikasi ini ke environment *Production*, silakan baca referensi terpisah pada file:
👉 **[DEPLOYMENT.md](./DEPLOYMENT.md)**

## 📄 Lisensi

Proyek ini bersifat tertutup (Proprietary). Penggunaan, distribusi, dan modifikasi tanpa izin eksplisit tidak diperkenankan kecuali ada perjanjian khusus.
