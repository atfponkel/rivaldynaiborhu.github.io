# Data Center — Panduan Setup di XAMPP

Website ini menampilkan data dari 5 spreadsheet Google Sheets (AXA, Retail Funding, TBR, TBW, Wealth) lewat Google Apps Script yang sudah Anda buat, dengan login password per kategori, pencarian, dan download Excel.

## 1. Persiapan

1. Pastikan **XAMPP** sudah terinstall dan **Apache** berjalan.
2. Pastikan ekstensi PHP `curl` dan `zip` aktif (default-nya XAMPP sudah aktifkan keduanya).
   - Cek di `php.ini` (Config > PHP (php.ini) di XAMPP Control Panel):
     - `extension=curl` -> tidak ada tanda `;` di depannya
     - `extension=zip` -> tidak ada tanda `;` di depannya
   - Kalau Anda ubah php.ini, restart Apache.

## 2. Salin folder project

1. Salin seluruh folder `project` ini ke dalam folder `htdocs` XAMPP Anda, misalnya:
   - Windows: `C:\xampp\htdocs\datacenter`
   - Jadi strukturnya: `C:\xampp\htdocs\datacenter\index.php`, dst.

## 3. Jalankan

1. Buka XAMPP Control Panel, klik **Start** pada Apache.
2. Buka browser, akses: `http://localhost/datacenter/`
3. Anda akan melihat dashboard dengan 5 menu kategori.

## 4. Login

- Klik salah satu kategori (misalnya **AXA**).
- Masukkan password: **Ponkelaku**
- Anda akan diarahkan ke halaman data dengan tab per sheet, kotak pencarian, dan tombol **Download Excel**.

## 5. Catatan penting

- **Akses login per kategori per browser session** — kalau Anda buka kategori lain, akan diminta password lagi (kecuali sudah pernah login di kategori itu pada session yang sama).
- **Password sama untuk semua kategori**: `Ponkelaku`. Untuk mengubahnya, edit baris berikut di `includes/config.php`:
  ```php
  define('SITE_PASSWORD', 'Ponkelaku');
  ```
- **URL Apps Script** ada di `includes/config.php` dalam variabel `$CATEGORIES`. Jika suatu saat Anda re-deploy Apps Script dan URL-nya berubah, tinggal update di sana.
- Data selalu diambil **langsung dari Google Sheets** setiap halaman dibuka / tab diklik / pencarian dilakukan — jadi selalu up-to-date, tidak ada data yang di-cache di server.
- Download Excel akan mengikuti hasil pencarian yang sedang aktif di kotak search (kalau kotak search kosong, akan download semua baris pada tab yang aktif).

## 6. Struktur file

```
project/
├── index.php              -> Dashboard 5 menu kategori
├── login.php               -> Halaman password per kategori
├── data.php                -> Halaman tab + tabel + search + download
├── logout.php              -> Keluar dari satu kategori
├── api.php                 -> Endpoint AJAX (list sheet & data sheet)
├── download.php            -> Endpoint generate & download file xlsx
├── includes/
│   ├── config.php              -> Password & daftar URL Apps Script
│   ├── apps_script_client.php  -> Fungsi fetch ke Google Apps Script
│   └── SimpleXLSXWriter.php    -> Library bikin file .xlsx tanpa Composer
└── assets/
    ├── css/theme.css        -> Tema gelap biru
    └── js/data.js           -> Logic tab, search, render tabel
```

## 7. Troubleshooting

- **"Memuat daftar sheet..." tidak hilang / macet** → Apache tidak bisa mengakses internet, atau ekstensi PHP `curl` belum aktif. Cek koneksi internet laptop dan php.ini.
- **Halaman kosong / blank putih** → biasanya ada error PHP yang disembunyikan. Aktifkan sementara `display_errors` di php.ini untuk melihat detail error, lalu matikan lagi setelah selesai debug.
- **Download Excel gagal / file korup** → pastikan ekstensi PHP `zip` aktif.
- **Lupa password** → edit `includes/config.php`, baris `SITE_PASSWORD`.
