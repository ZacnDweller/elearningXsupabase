# E-Learning PHP

Aplikasi e-learning berbasis PHP/MySQL.

## Siapkan deployment di Hostinger

1. Buat database di Hostinger MySQL Database:
   - nama database
   - username database
   - password database

2. Import file `elearning.sql` ke database melalui phpMyAdmin.

3. Sesuaikan `koneksi.php`:
   - `DB_HOST` biasanya `localhost`
   - `DB_USER` = username database Hostinger
   - `DB_PASS` = password database Hostinger
   - `DB_NAME` = nama database Hostinger

   Contoh manual:
   ```php
   $db_host = 'localhost';
   $db_user = 'your_db_user';
   $db_pass = 'your_db_password';
   $db_name = 'your_db_name';
   ```

4. Upload semua file dan folder ke `public_html` atau folder target di Hostinger.
   - Sertakan file root seperti `login.php`, `koneksi.php`, `footer.php`, `header.php`, dan folder `admin`, `dosen`, `mahasiswa`, `materi`, `upload`, dll.
   - Pastikan struktur folder tetap sama.

5. Buka domain/subdomain Hostinger.

## Catatan penting

- GitHub Pages tidak bisa menjalankan PHP. Untuk aplikasi ini, gunakan hosting PHP/MySQL seperti Hostinger.
- Jika muncul error database, periksa kembali `koneksi.php` dan detail akun MySQL.
- Pastikan file `error.log` tidak diupload ke repo karena hanya log lokal.
