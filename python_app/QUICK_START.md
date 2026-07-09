# Quick Start Guide - E-Learning GUI & REST API

## Prerequisites
- Python 3.7+
- pip package manager
- Web browser (Chrome, Firefox, Safari, Edge)

## Setup Steps

### 1. Install Dependencies
```cmd
cd python_app
python -m pip install -r requirements.txt
```

### 2. Configure Environment Variables
Copy `.env.example` to `.env` and update with your Supabase credentials:

```cmd
copy .env.example .env
```

Edit `.env`:
```
FLASK_SECRET_KEY=your-secret-key-here
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-or-service-key
```

### 3. Start the Application
```cmd
python app.py
```

The server akan berjalan di: `http://localhost:5000`

### 4. Access the GUI
Buka browser dan kunjungi:
```
http://localhost:5000
```

## Login Credentials

Gunakan username dan password yang sudah terdaftar di database Supabase.

Contoh kredensial (harus disesuaikan dengan data di database):
- Username: `admin`
- Password: `password`

## What's Included

### ✅ REST API (`/api/v1`)
- Authentication (Login, Register)
- User Management
- Materi (Learning Materials)
- Tugas (Assignments)
- Pengumpulan (Submissions)
- Presensi (Attendance)
- Pembayaran (Payments)
- Statistics & Analytics

### ✅ JavaScript GUI
- Responsive Dashboard
- User Management Interface
- CRUD operations untuk semua modules
- Real-time data synchronization
- Authentication & Authorization
- Role-based access control (Admin, Dosen, Mahasiswa)

### ✅ Features
- **Dashboard**: Overview statistik sistem
- **Users Management**: Manage admin, dosen, mahasiswa
- **Materi**: Kelola materi pembelajaran
- **Tugas**: Manage assignments dengan deadline
- **Pengumpulan**: Track student submissions
- **Presensi**: Attendance management
- **Pembayaran**: Payment tracking & reporting

## Directory Structure

```
elearningXsupabase/
├── python_app/
│   ├── app.py                    # Main Flask application
│   ├── api.py                    # REST API endpoints
│   ├── requirements.txt           # Python dependencies
│   ├── .env.example              # Environment template
│   ├── static/
│   │   ├── index.html            # Main GUI page
│   │   ├── css/
│   │   │   └── dashboard.css     # Styling
│   │   └── js/
│   │       ├── app.js            # Main app logic
│   │       ├── api-client.js     # API client
│   │       ├── utils.js          # Utilities
│   │       └── modules/          # Feature modules
│   │           ├── dashboard.js
│   │           ├── users.js
│   │           ├── materi.js
│   │           ├── tugas.js
│   │           ├── pengumpulan.js
│   │           ├── presensi.js
│   │           └── pembayaran.js
│   ├── templates/                # Flask templates (legacy)
│   └── GUI_DOCUMENTATION.md     # Detailed documentation
```

## Architecture

```
┌─────────────────────────────────────────┐
│   Web Browser (JavaScript GUI)          │
│  - Dashboard                            │
│  - Users Management                     │
│  - Materi, Tugas, Pengumpulan          │
│  - Presensi, Pembayaran                │
└──────────────┬──────────────────────────┘
               │ HTTP/REST
               ▼
┌─────────────────────────────────────────┐
│   Flask REST API (/api/v1)              │
│  - Authentication                       │
│  - User endpoints                       │
│  - Materi endpoints                     │
│  - Tugas endpoints                      │
│  - Pengumpulan endpoints                │
│  - Presensi endpoints                   │
│  - Pembayaran endpoints                 │
│  - Statistics endpoints                 │
└──────────────┬──────────────────────────┘
               │ Supabase SDK
               ▼
┌─────────────────────────────────────────┐
│   Supabase (Cloud Database)             │
│  - PostgreSQL Database                  │
│  - Authentication                       │
│  - Real-time subscriptions              │
└─────────────────────────────────────────┘
```

## API Examples

### Login
```cmd
curl -X POST "http://localhost:5000/api/v1/auth/login" -H "Content-Type: application/json" -d "{\"username\": \"admin\", \"password\": \"password\"}"
```

### Get Users
```cmd
curl http://localhost:5000/api/v1/users
```

### Create Materi
```cmd
curl -X POST "http://localhost:5000/api/v1/materi" -H "Content-Type: application/json" -d "{\"judul\": \"Web Development Basics\", \"deskripsi\": \"Learn HTML, CSS, JavaScript\", \"file\": \"https://example.com/materi.pdf\", \"matkul_id\": 1, \"dosen_id\": 2}"
```

## Role-Based Access

### Admin
- View all users
- Manage users (create, edit, delete)
- View all payments
- Dashboard statistics

### Dosen
- View materi untuk kelas mereka
- Create/edit/delete materi & tugas
- View pengumpulan submissions
- Manage presensi
- View student statistics

### Mahasiswa
- View materi pembelajaran
- View tugas yang harus dikerjakan
- Submit pengumpulan tugas
- View presensi status
- Report pembayaran
- View payment history

## Troubleshooting

### CORS Error
```
Error: Access to XMLHttpRequest at 'http://localhost:5000/api/v1/...'
```
**Solution**: Ensure Flask-CORS is installed: `pip install Flask-CORS`

### Connection Refused
```
Error: Cannot GET http://localhost:5000
```
**Solution**: Make sure Flask app is running: `python app.py`

### Supabase Connection Error
```
Error: Please set SUPABASE_URL and SUPABASE_KEY in .env
```
**Solution**: Create `.env` file with correct credentials

### Login Failed
```
Error: Username atau password salah
```
**Solution**: Check database for correct credentials or register new user

## Next Steps

1. **Customize**: Edit `GUI_DOCUMENTATION.md` untuk dokumentasi lengkap
2. **Deploy**: Deploy ke production server
3. **Database**: Setup proper database schema di Supabase
4. **Security**: Change default secret keys dan credentials
5. **Testing**: Test all features dengan different user roles

## Support & Documentation

- Full API Documentation: See `GUI_DOCUMENTATION.md`
- Browser DevTools: Use F12 untuk debug
- Flask Logs: Check console untuk error messages

## Security Notes

⚠️ **Before Production**:
1. Change `FLASK_SECRET_KEY` to random value
2. Use environment variables for sensitive data
3. Enable HTTPS
4. Setup proper database backups
5. Implement rate limiting
6. Add input validation

## Version

- Flask 2.3.6
- Supabase 1.0.2
- Bootstrap 5.1.3
- Python 3.7+

---

Happy Learning! 🚀
