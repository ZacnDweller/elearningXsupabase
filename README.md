# E-Learning Platform - Complete REST API & JavaScript GUI

> A full-featured e-learning platform with REST API backend and modern JavaScript GUI

## 🎯 Quick Navigation

- 📚 **New Users**: Start with [SETUP_CHECKLIST.md](SETUP_CHECKLIST.md)
- 🚀 **Quick Setup**: See [python_app/QUICK_START.md](python_app/QUICK_START.md)
- 📖 **Full Documentation**: Read [python_app/GUI_DOCUMENTATION.md](python_app/GUI_DOCUMENTATION.md)
- 🔧 **Implementation Details**: Check [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)

## ✨ What's New (v2.0)

This repository now includes a **complete REST API and JavaScript GUI** alongside the original PHP application:

### ✅ New REST API Backend (`python_app/api.py`)
- Flask-based REST API with complete endpoints
- Supabase integration for PostgreSQL database
- Authentication & authorization
- CORS support for modern web apps

### ✅ New JavaScript GUI (`python_app/static/`)
- Responsive Bootstrap 5 dashboard
- Modern JavaScript frontend (no jQuery)
- Modular architecture with separate feature modules
- Real-time data synchronization
- Role-based access control

### ✅ New Features
- User management system
- Materi (Learning Materials) tracking
- Tugas (Assignments) with deadlines
- Pengumpulan (Submissions) management
- Presensi (Attendance) system
- Pembayaran (Payments) tracking

## 🚀 Getting Started with New GUI

### Installation
```cmd
cd python_app
python -m pip install -r requirements.txt
copy .env.example .env
rem Edit .env with your Supabase credentials
python app.py
```

### Access
```
http://localhost:5000
```

## 📚 Documentation Map

| File | Purpose | Audience |
|------|---------|----------|
| [SETUP_CHECKLIST.md](SETUP_CHECKLIST.md) | Step-by-step setup | First-time users |
| [python_app/QUICK_START.md](python_app/QUICK_START.md) | Quick reference | Developers |
| [python_app/GUI_DOCUMENTATION.md](python_app/GUI_DOCUMENTATION.md) | Complete guide | All users |
| [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) | Technical details | Developers |

---

## 📚 Original PHP Application

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
