# E-Learning Python + Supabase

Aplikasi e-learning Python/Flask dengan database Supabase (Postgres).

## Setup

1. Salin `.env.example` menjadi `.env`.
2. Isi `SUPABASE_KEY` dengan kunci Supabase Anda.
3. Set `FLASK_SECRET_KEY`.

> Supabase project reference: `crbwybyrvoohumoykbul`
> Region: `ap-southeast-1`
4. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```
5. Jalankan aplikasi:
   ```bash
   python app.py
   ```
6. Buka `http://127.0.0.1:5000`.

## Tabel yang dipakai

- `users`
- `materi`
- `matkul`
- `prodi`
- `tugas`
- `tugas_kumpul`
- `presensi`
- `presensi_mahasiswa`
- `aktivitas`
- `website_settings`

## Catatan

- Password saat ini menggunakan hash MD5 untuk kompatibilitas data lama.
- Dashboard awal sudah terhubung dengan login, logout, dan halaman per-role.
- Untuk migrasi penuh, impor schema di `supabase_schema.sql` ke database Supabase.
