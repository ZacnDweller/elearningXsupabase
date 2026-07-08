-- Sample seed data for existing E-Learning tables.
-- Use `supabase db query --file supabase_seed.sql` after your tables/columns exist.

-- Reset identity sequences to avoid duplicate key errors when rows already exist
SELECT setval(pg_get_serial_sequence('prodi', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM prodi;
SELECT setval(pg_get_serial_sequence('matkul', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM matkul;
SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM users;
SELECT setval(pg_get_serial_sequence('website_settings', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM website_settings;
SELECT setval(pg_get_serial_sequence('materi', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM materi;
SELECT setval(pg_get_serial_sequence('tugas', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM tugas;
SELECT setval(pg_get_serial_sequence('pengumpulan_tugas', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM pengumpulan_tugas;
SELECT setval(pg_get_serial_sequence('tugas_kumpul', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM tugas_kumpul;
SELECT setval(pg_get_serial_sequence('presensi', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM presensi;
SELECT setval(pg_get_serial_sequence('presensi_mahasiswa', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM presensi_mahasiswa;
SELECT setval(pg_get_serial_sequence('payments', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM payments;
SELECT setval(pg_get_serial_sequence('aktivitas', 'id'), COALESCE(MAX(id), 0) + 1, false) FROM aktivitas;

-- 1. Program studi
INSERT INTO prodi (nama_prodi) VALUES
('Teknik Informatika'),
('Sistem Informasi'),
('Manajemen')
ON CONFLICT DO NOTHING;

-- 2. Mata kuliah
INSERT INTO matkul (prodi_id, nama_matkul) VALUES
(1, 'Basis Data'),
(1, 'Pemrograman Web'),
(2, 'Analisis Sistem'),
(3, 'Manajemen Proyek');

-- 3. Website settings
INSERT INTO website_settings (nama_website, deskripsi, logo_url) VALUES
('E-Learning', 'Platform pembelajaran online untuk admin, dosen, dan mahasiswa', NULL);

-- 4. User sample
-- Password harus disimpan MD5 sesuai logika aplikasi.
INSERT INTO users (nama, username, password, email, role, prodi_id, matkul_id, umur, no_hp, agama, alamat, nisn, nidn)
VALUES
('Admin Sistem', 'admin', md5('admin123'), 'admin@example.com', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('Dosen A', 'dosen_a', md5('dosen123'), 'dosen_a@example.com', 'dosen', 1, 1, NULL, NULL, NULL, NULL, NULL, 'D001'),
('Mahasiswa B', 'mhs_b', md5('mahasiswa123'), 'mhs_b@example.com', 'mahasiswa', 1, 1, 20, '081234567890', 'Islam', 'Jakarta', '1234567890', NULL)
ON CONFLICT (username) DO NOTHING;

-- 5. Materi example
INSERT INTO materi (judul, file, matkul_id, dosen_id)
VALUES
('Pengantar Basis Data', 'pengantar_basis_data.pdf', 1, 2),
('Pemrograman Web Dasar', 'web_dasar.pdf', 2, 2);

-- 6. Tugas example
INSERT INTO tugas (judul, deskripsi, deadline, file, matkul_id, dosen_id)
VALUES
('Tugas 1 Basis Data', 'Buat ERD sederhana', '2026-12-31', NULL, 1, 2),
('Tugas 1 Pemrograman Web', 'Buat halaman login', '2026-12-31', NULL, 2, 2);

-- 7. Pembayaran sample
INSERT INTO payments (student_id, amount, description, payment_method, transaction_id, status)
VALUES
(3, 1500000.00, 'SPP Semester 1', 'Transfer', 'TRX123456', 'pending');

-- 8. Presensi sample
INSERT INTO presensi (tanggal, status, matkul_id, dosen_id)
VALUES
(CURRENT_DATE, 'buka', 1, 2);

-- 9. Aktivitas sample
INSERT INTO aktivitas (user_id, nama, role, aktivitas)
VALUES
(1, 'Admin Sistem', 'admin', 'Login ke dashboard admin'),
(2, 'Dosen A', 'dosen', 'Menambahkan materi basis data');

-- 10. Contoh update jika kamu sudah punya baris tapi perlu isi isian tambahan
-- UPDATE users SET umur = 21, alamat = 'Bandung' WHERE id = 3;
-- UPDATE payments SET status = 'confirmed', confirmed_by = 1, confirmed_date = CURRENT_TIMESTAMP WHERE id = 1;
