# E-Learning GUI Documentation

Dokumentasi lengkap untuk GUI JavaScript E-Learning yang terhubung ke REST API.

## Setup Instructions

### 1. Install Dependencies
```cmd
cd python_app
python -m pip install -r requirements.txt
```

### 2. Configure Environment
Update file `.env` dengan kredensial Supabase Anda:
```
FLASK_SECRET_KEY=your-secret-key
SUPABASE_URL=your-supabase-url
SUPABASE_KEY=your-supabase-key
```

### 3. Run Application
```cmd
python app.py
```

Server akan berjalan di `http://localhost:5000`

## Accessing the GUI

Buka browser dan navigasi ke:
```
http://localhost:5000
```

Login menggunakan kredensial yang sudah terdaftar.

## Architecture

```
Frontend (JavaScript GUI)
      ↓
REST API (/api/v1)
      ↓
Flask Application
      ↓
Supabase Database
```

## Features

### Dashboard
- Statistik pengguna (Admin)
- Statistik kelas (Dosen/Mahasiswa)
- Informasi profil pengguna
- Status sistem

### User Management (Admin Only)
- Melihat semua users
- Menambah user baru
- Edit user
- Hapus user

### Materi
- Melihat materi pembelajaran
- Menambah materi (Dosen)
- Edit materi (Dosen)
- Hapus materi (Dosen)

### Tugas
- Melihat daftar tugas
- Menambah tugas (Dosen)
- Edit tugas (Dosen)
- Hapus tugas (Dosen)

### Pengumpulan
- Melihat pengumpulan tugas (Dosen)
- Filter berdasarkan tugas
- Download file submission

### Presensi
- Melihat data presensi
- Buka presensi (Dosen)
- Update status presensi

### Pembayaran
- Lapor pembayaran (Mahasiswa)
- Melihat riwayat pembayaran
- Admin dapat melihat semua pembayaran

## File Structure

```
static/
├── index.html          # Main HTML page
├── css/
│   └── dashboard.css   # Styling
└── js/
    ├── app.js          # Main app initialization
    ├── api-client.js   # API communication
    ├── utils.js        # Utility functions
    └── modules/
        ├── dashboard.js    # Dashboard module
        ├── users.js        # Users management
        ├── materi.js       # Materi module
        ├── tugas.js        # Tugas module
        ├── pengumpulan.js  # Pengumpulan module
        ├── presensi.js     # Presensi module
        └── pembayaran.js   # Pembayaran module
```

## API Endpoints

### Authentication
- `POST /api/v1/auth/login` - Login user
- `POST /api/v1/auth/register` - Register user

### Users
- `GET /api/v1/users` - Get all users
- `GET /api/v1/users/<id>` - Get user by ID
- `PUT /api/v1/users/<id>` - Update user
- `DELETE /api/v1/users/<id>` - Delete user

### Materi
- `GET /api/v1/materi` - Get all materi
- `GET /api/v1/materi/<id>` - Get materi by ID
- `POST /api/v1/materi` - Create materi
- `PUT /api/v1/materi/<id>` - Update materi
- `DELETE /api/v1/materi/<id>` - Delete materi

### Tugas
- `GET /api/v1/tugas` - Get all tugas
- `GET /api/v1/tugas/<id>` - Get tugas by ID
- `POST /api/v1/tugas` - Create tugas
- `PUT /api/v1/tugas/<id>` - Update tugas
- `DELETE /api/v1/tugas/<id>` - Delete tugas

### Pengumpulan
- `GET /api/v1/pengumpulan` - Get all pengumpulan
- `POST /api/v1/pengumpulan` - Create pengumpulan

### Presensi
- `GET /api/v1/presensi` - Get all presensi
- `POST /api/v1/presensi` - Create presensi
- `PUT /api/v1/presensi/<id>` - Update presensi

### Pembayaran
- `GET /api/v1/pembayaran` - Get all pembayaran
- `GET /api/v1/pembayaran/<id>` - Get pembayaran by ID
- `POST /api/v1/pembayaran` - Create pembayaran
- `PUT /api/v1/pembayaran/<id>` - Update pembayaran

### Prodi & Matkul
- `GET /api/v1/prodi` - Get all prodi
- `GET /api/v1/matkul` - Get all matkul
- `GET /api/v1/matkul/<id>` - Get matkul by ID

### Statistics
- `GET /api/v1/stats/dashboard` - Get dashboard statistics

## Authentication

Login menggunakan username dan password. Token akan disimpan di localStorage secara otomatis.

Untuk logout, klik tombol Logout di sidebar.

## Usage Examples

### Login
```javascript
const response = await api.login('username', 'password');
if (response.success) {
    // User logged in
}
```

### Get Users
```javascript
const response = await api.getUsers('admin');
if (response.success) {
    console.log(response.data); // Array of users
}
```

### Create Materi
```javascript
const response = await api.createMateri({
    judul: 'Introduction to Web Development',
    deskripsi: 'Belajar dasar-dasar web development',
    file: 'https://example.com/materi1.pdf',
    matkul_id: 1,
    dosen_id: 5
});
```

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Notes

- Aplikasi menggunakan Bootstrap 5 untuk styling
- Bootstrap Icons untuk icons
- localStorage untuk menyimpan authentication token
- CORS enabled untuk API requests
- Responsive design untuk mobile devices

## Troubleshooting

### "CORS error" saat login
- Pastikan Flask-CORS sudah install: `pip install Flask-CORS`
- Pastikan Flask app sudah mendaftarkan blueprint API

### Token tidak tersimpan
- Check browser console untuk error messages
- Clear localStorage: `localStorage.clear()`
- Coba login ulang

### API tidak merespons
- Pastikan server Flask sudah berjalan
- Check Flask console untuk error messages
- Pastikan SUPABASE_URL dan SUPABASE_KEY sudah benar

## Development

### Menambah Module Baru
1. Buat file di `static/js/modules/nama-module.js`
2. Ikuti pattern module yang sudah ada
3. Daftarkan di `index.html` sebelum `app.js`
4. Daftarkan route di `app.js`

### Menambah API Endpoint
1. Edit file `api.py`
2. Tambah function dengan route decorator
3. Return response menggunakan `api_response()` helper
4. Update `api-client.js` dengan method baru

## Support

Untuk pertanyaan dan support, silakan hubungi tim development.
