# Hak Akses Aplikasi E-Learning

Dokumen ini menjelaskan hak akses dan batasan per peran pada aplikasi.

## Ringkasan Peran
- `admin` : mengelola pengguna, iklan, dan pembayaran.
- `dosen` : mengelola materi, tugas, presensi, dan melihat pengumpulan tugas.
- `mahasiswa` : melihat materi, tugas, presensi, pembayaran, dan mengumpulkan tugas.

## Tabel Hak Akses
| Peran | Area Utama | Aksi yang Diizinkan |
| --- | --- | --- |
| `admin` | Dashboard admin, pengguna, iklan, pembayaran | Mengelola user, menambah/edit/hapus user, mengelola iklan, melihat dan memproses pembayaran |
| `dosen` | Dashboard dosen, materi, tugas, presensi, pengumpulan | Membuat/edit/hapus materi, membuat/edit/hapus tugas, membuka/menutup presensi, melihat pengumpulan tugas |
| `mahasiswa` | Dashboard mahasiswa, materi, tugas, presensi, pembayaran | Melihat materi, melihat tugas, mengunggah tugas, mengisi presensi, melihat status pembayaran |

## Ketentuan Umum
- Semua halaman dashboard memerlukan autentikasi (login).
- Jika tidak login, pengguna akan diarahkan ke `/login`.
- Setelah login, pengguna akan diarahkan berdasarkan `role` mereka.
- Peran disimpan di session sebagai `session['user']['role']`.
- Semua rute admin, dosen, dan mahasiswa memiliki pengecekan role.
- Mahasiswa yang belum memilih `prodi` atau `matkul` akan diarahkan ke `/mahasiswa/pilih-matkul`.

## Admin
Akses eksklusif untuk peran `admin`.

### Rute dan fungsi utama
- `/admin` : dashboard admin.
- `/admin/users` : melihat semua user.
- `/admin/users/add` : menambah user baru.
- `/admin/users/<user_id>/edit` : mengubah data user.
- `/admin/users/<user_id>/delete` : menghapus user.
- `/admin/ads` : mengelola iklan kampus.
- `/admin/ads/<ad_id>/delete` : menghapus iklan.
- `/admin/payments` : melihat dan mengelola pembayaran.

### Hak akses khusus
- `admin` dapat mengelola semua role user.
- `admin` dapat membuat/edit iklan iklan banner.
- `admin` dapat membuat dan memproses pembayaran.

## Dosen
Akses eksklusif untuk peran `dosen`.

### Rute dan fungsi utama
- `/dosen` : dashboard dosen.
- `/dosen/materi` : menambah dan melihat materi.
- `/dosen/materi/<materi_id>/edit` : mengedit materi.
- `/dosen/materi/<materi_id>/delete` : menghapus materi.
- `/dosen/tugas` : membuat dan melihat tugas.
- `/dosen/tugas/<tugas_id>/edit` : mengedit tugas.
- `/dosen/tugas/<tugas_id>/delete` : menghapus tugas.
- `/dosen/presensi` : membuka dan menutup presensi.
- `/dosen/pengumpulan` : melihat daftar pengumpulan tugas.

### Hak akses khusus
- `dosen` hanya dapat mengakses data terkait `matkul_id` mereka.
- `dosen` dapat mengunggah file materi dan file tugas.
- `dosen` dapat melihat pengumpulan tugas mahasiswa.

## Mahasiswa
Akses eksklusif untuk peran `mahasiswa`.

### Rute dan fungsi utama
- `/mahasiswa` : dashboard mahasiswa.
- `/mahasiswa/materi` : melihat materi.
- `/mahasiswa/tugas` : melihat daftar tugas.
- `/mahasiswa/tugas/<tugas_id>` : mengunggah atau mengubah pengumpulan tugas.
- `/mahasiswa/presensi` : melihat dan mengisi presensi.
- `/mahasiswa/pembayaran` : melihat status pembayaran.
- `/mahasiswa/pilih-matkul` : memilih matkul jika belum punya `prodi`/`matkul`.

### Hak akses khusus
- Mahasiswa hanya dapat melihat materi dan tugas untuk `matkul_id` mereka.
- Mahasiswa harus memilih `prodi` terlebih dahulu.
- Jika `prodi_id` dan/atau `matkul_id` tidak valid, mahasiswa akan diarahkan untuk memilih matkul.

## Batasan Input dan Validasi Role
- Saat menambah atau edit user:
  - `dosen` wajib memiliki `prodi_id` dan `matkul_id`.
  - `mahasiswa` wajib memiliki `prodi_id`, tetapi `matkul_id` diset menjadi `None` saat pembuatan user.
  - `admin` tidak wajib mengisi `prodi` atau `matkul`.

## Akses File dan Download
- Semua file yang disimpan di `uploads/materi/` dan `uploads/tugas/` dapat diunduh melalui route `/uploads/<path:filename>`.
- Mahasiswa dapat mengunduh file tugas dosen jika tugas berkas tersedia.

## Catatan
- Hak akses ditentukan oleh nilai `role` dalam session.
- Pengaturan `role` dan `session` penting untuk mencegah akses tidak sah.
- Jika route role tidak cocok, pengguna dialihkan ke dashboard role yang sesuai.
