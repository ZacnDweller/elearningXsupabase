# E-Learning GUI & REST API - Setup Checklist

## ✅ Pre-Installation Checklist

- [ ] Python 3.7+ installed and accessible from command line
- [ ] pip (Python package manager) available
- [ ] Internet connection for installing dependencies
- [ ] Supabase account with project created
- [ ] Supabase URL and API key obtained
- [ ] Text editor or IDE available

## 📋 Installation Steps

### Step 1: Navigate to Project Directory
```cmd
cd c:\xampp\htdocs\elearningXsupabase\python_app
```
- [ ] Successfully changed directory

### Step 2: Install Python Dependencies
```cmd
python -m pip install -r requirements.txt
```

The following packages will be installed:
- [ ] Flask==2.3.6
- [ ] Flask-CORS==4.0.0
- [ ] python-dotenv==1.0.0
- [ ] supabase==1.0.2
- [ ] requests==2.31.0

### Step 3: Create Environment File
```cmd
copy .env.example .env
```
- [ ] .env file created

### Step 4: Configure Supabase Credentials
Edit the `.env` file with your Supabase details:

```
FLASK_SECRET_KEY=your-random-secret-key
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-key-or-service-key
```

- [ ] FLASK_SECRET_KEY configured
- [ ] SUPABASE_URL configured
- [ ] SUPABASE_KEY configured

### Step 5: Verify Setup
```cmd
python verify_setup.py
```

All checks should show ✅:
- [ ] Python version check
- [ ] Required packages installed
- [ ] .env file exists with required keys
- [ ] GUI files present
- [ ] API file present
- [ ] Directory structure correct

## 🚀 Running the Application

### Start Flask Server
```cmd
python app.py
```

You should see:
- [ ] `WARNING in app.run(...)` message (normal)
- [ ] `Running on http://127.0.0.1:5000` message
- [ ] No error messages in console

### Access GUI
Open your web browser and go to:
```
http://localhost:5000
```

You should see:
- [ ] E-Learning login page
- [ ] Username and password input fields
- [ ] Login button

## 🔐 First Login

### Get Credentials
The first user must exist in your Supabase database:

1. Go to Supabase Console
2. Navigate to SQL Editor
3. Check the `users` table for existing users
4. Or insert a test user:

```sql
INSERT INTO users (nama, username, password, email, role) VALUES
('Admin User', 'admin', MD5('password'), 'admin@example.com', 'admin');
```

### Login to GUI
- [ ] Enter username: `admin`
- [ ] Enter password: `password`
- [ ] Click Login button

After successful login:
- [ ] Dashboard page appears
- [ ] User menu shows your name
- [ ] Sidebar navigation visible

## 📊 Features to Test

### Test Dashboard
- [ ] Dashboard loads with statistics
- [ ] User name displayed in top right
- [ ] Sidebar menu shows appropriate options based on role

### Test Users Module (Admin Only)
- [ ] Click Users in sidebar
- [ ] View all users table
- [ ] Click "Tambah User" button
- [ ] Fill form and create new user

### Test Materi Module
- [ ] Click Materi in sidebar
- [ ] View materi list (if dosen, can add new)
- [ ] Click "Tambah Materi" button
- [ ] Fill form and create new materi

### Test Other Modules
- [ ] Tugas module works
- [ ] Pengumpulan shows submissions
- [ ] Presensi tracks attendance
- [ ] Pembayaran handles payments

## 🔧 Troubleshooting

### If Flask doesn't start:
- [ ] Check if port 5000 is available
- [ ] Ensure Python path is correct
- [ ] Check for error messages in console
- [ ] Try: `python -m flask run`

### If Login fails:
- [ ] Verify .env credentials are correct
- [ ] Check Supabase connection in console
- [ ] Ensure user exists in database
- [ ] Check password is correct (case-sensitive)

### If GUI doesn't load:
- [ ] Check browser console (F12) for JavaScript errors
- [ ] Ensure all static files are in place
- [ ] Clear browser cache
- [ ] Try different browser

### If API returns error:
- [ ] Check Flask console for error messages
- [ ] Verify Supabase credentials
- [ ] Ensure database tables exist
- [ ] Check network tab in browser DevTools

## 📁 File Structure Verification

Verify these files exist:

```
python_app/
├── app.py                          ✅
├── api.py                          ✅
├── verify_setup.py                 ✅
├── requirements.txt                ✅
├── .env.example                    ✅
├── .env                            ✅ (created)
├── QUICK_START.md                  ✅
├── GUI_DOCUMENTATION.md            ✅
├── static/
│   ├── index.html                  ✅
│   ├── css/
│   │   └── dashboard.css           ✅
│   └── js/
│       ├── app.js                  ✅
│       ├── api-client.js           ✅
│       ├── utils.js                ✅
│       └── modules/
│           ├── dashboard.js        ✅
│           ├── users.js            ✅
│           ├── materi.js           ✅
│           ├── tugas.js            ✅
│           ├── pengumpulan.js      ✅
│           ├── presensi.js         ✅
│           └── pembayaran.js       ✅
```

## 🎓 Next Steps After Setup

1. **Database Setup**
   - [ ] Create proper database schema in Supabase
   - [ ] Create test data
   - [ ] Setup user roles

2. **Customization**
   - [ ] Update application name/logo
   - [ ] Customize color scheme in dashboard.css
   - [ ] Add your institution details

3. **Production Deployment**
   - [ ] Change FLASK_SECRET_KEY
   - [ ] Setup HTTPS
   - [ ] Deploy to production server
   - [ ] Setup backups
   - [ ] Configure logging

4. **Testing**
   - [ ] Test all modules with different roles
   - [ ] Test CRUD operations
   - [ ] Test file uploads
   - [ ] Test on different browsers

## 📞 Support Resources

- **Quick Start**: QUICK_START.md
- **Full Documentation**: GUI_DOCUMENTATION.md
- **Implementation Summary**: ../IMPLEMENTATION_SUMMARY.md
- **Setup Verification**: python verify_setup.py

## 🎉 Success Indicators

Once everything is working, you should see:

✅ Flask server running without errors
✅ Web browser can access http://localhost:5000
✅ Login page displays correctly
✅ Can login with valid credentials
✅ Dashboard shows after login
✅ Menu items work for your user role
✅ CRUD operations work in all modules
✅ Data persists in Supabase database

---

**Version**: 1.0.0
**Last Updated**: 2026-06-11
**Status**: Ready for Deployment
