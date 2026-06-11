import hashlib
import os
import time
import datetime
from flask import Flask, render_template, request, redirect, url_for, session, flash, send_from_directory
from flask_cors import CORS
from dotenv import load_dotenv
from supabase import create_client

load_dotenv()

SUPABASE_URL = os.getenv('SUPABASE_URL')
SUPABASE_KEY = os.getenv('SUPABASE_KEY')
SECRET_KEY = os.getenv('FLASK_SECRET_KEY', 'change-me')

if not SUPABASE_URL or not SUPABASE_KEY:
    raise RuntimeError('Please set SUPABASE_URL and SUPABASE_KEY in .env')

app = Flask(__name__)
app.secret_key = SECRET_KEY

# Enable CORS for API endpoints
CORS(app, resources={r"/api/*": {"origins": "*"}})

UPLOAD_DIR = os.path.join(app.root_path, 'uploads')
MATERI_UPLOAD_DIR = os.path.join(UPLOAD_DIR, 'materi')
TUGAS_UPLOAD_DIR = os.path.join(UPLOAD_DIR, 'tugas')

os.makedirs(MATERI_UPLOAD_DIR, exist_ok=True)
os.makedirs(TUGAS_UPLOAD_DIR, exist_ok=True)

supabase = create_client(SUPABASE_URL, SUPABASE_KEY)


def md5_hash(text: str) -> str:
    return hashlib.md5(text.encode('utf-8')).hexdigest()


def get_website_settings():
    response = supabase.table('website_settings').select('*').limit(1).execute()
    data = response.data
    if data:
        return data[0]
    return {
        'nama_website': 'E-Learning',
        'deskripsi': 'Platform pembelajaran online'
    }


def get_user_by_credentials(username: str, password: str):
    hashed = md5_hash(password)
    response = supabase.table('users').select('*').match({
        'username': username,
        'password': hashed
    }).limit(1).execute()
    data = response.data
    return data[0] if data else None


def count_table(table: str, filters: dict = None):
    query = supabase.table(table).select('id', count='exact')
    if filters:
        query = query.match(filters)
    response = query.execute()

    if hasattr(response, 'count') and response.count is not None:
        return response.count
    if response.data:
        return len(response.data)
    return 0


def get_activity_feed(limit: int = 5):
    response = supabase.table('aktivitas').select('*').order('id', desc=True).limit(limit).execute()
    return response.data or []


def get_monthly_student_counts():
    response = supabase.table('users').select('created_at').eq('role', 'mahasiswa').execute()
    data = response.data or []
    months = []
    totals = []
    names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
    counts = {i: 0 for i in range(1, 13)}

    for row in data:
        created_at = row.get('created_at')
        if created_at:
            month = int(created_at[5:7])
            counts[month] += 1

    for m in range(1, 13):
        if counts[m] > 0:
            months.append(names[m - 1])
            totals.append(counts[m])

    return months, totals


def login_required(view):
    def wrapped_view(**kwargs):
        if 'user' not in session:
            return redirect(url_for('login'))
        return view(**kwargs)
    wrapped_view.__name__ = view.__name__
    return wrapped_view


@app.route('/')
def home():
    if 'user' in session:
        role = session['user']['role']
        return redirect(url_for(role))
    return redirect(url_for('login'))


@app.route('/login', methods=['GET', 'POST'])
def login():
    website_settings = get_website_settings()
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '').strip()

        user = get_user_by_credentials(username, password)
        if user:
            session['user'] = {
                'id': user.get('id'),
                'nama': user.get('nama'),
                'role': user.get('role'),
                'prodi_id': user.get('prodi_id'),
                'matkul_id': user.get('matkul_id')
            }
            return redirect(url_for(user.get('role')))

        flash('Username atau password salah!', 'danger')

    return render_template('login.html', website_settings=website_settings)


@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('login'))


@app.route('/admin')
@login_required
def admin():
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    website_settings = get_website_settings()
    admin_count = count_table('users', {'role': 'admin'})
    dosen_count = count_table('users', {'role': 'dosen'})
    mahasiswa_count = count_table('users', {'role': 'mahasiswa'})

    mahasiswa_labels, mahasiswa_totals = get_monthly_student_counts()
    activities = get_activity_feed()

    return render_template(
        'admin_dashboard.html',
        website_settings=website_settings,
        nama=user['nama'],
        admin_count=admin_count,
        dosen_count=dosen_count,
        mahasiswa_count=mahasiswa_count,
        mahasiswa_labels=mahasiswa_labels,
        mahasiswa_totals=mahasiswa_totals,
        activities=activities
    )


@app.route('/dosen')
@login_required
def dosen():
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    website_settings = get_website_settings()
    materi_count = count_table('materi', {'matkul_id': user['matkul_id']})
    tugas_count = count_table('tugas', {'matkul_id': user['matkul_id']})

    presensi_open = supabase.table('presensi')
    presensi_open = presensi_open.select('*').eq('status', 'buka').eq('matkul_id', user['matkul_id']).order('id', desc=True).limit(1).execute().data
    presensi_status = 'BUKA' if presensi_open else 'TUTUP'

    return render_template(
        'dosen_dashboard.html',
        website_settings=website_settings,
        nama=user['nama'],
        materi_count=materi_count,
        tugas_count=tugas_count,
        presensi_status=presensi_status
    )


@app.route('/mahasiswa')
@login_required
def mahasiswa():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    website_settings = get_website_settings()
    materi_count = count_table('materi', {'matkul_id': user['matkul_id']})
    tugas_count = count_table('tugas', {'matkul_id': user['matkul_id']})

    presensi_open = supabase.table('presensi')
    presensi_open = presensi_open.select('*').eq('status', 'buka').eq('matkul_id', user['matkul_id']).order('id', desc=True).limit(1).execute().data
    presensi_status = 'BUKA' if presensi_open else 'TUTUP'

    return render_template(
        'mahasiswa_dashboard.html',
        website_settings=website_settings,
        nama=user['nama'],
        materi_count=materi_count,
        tugas_count=tugas_count,
        presensi_status=presensi_status
    )


def get_all_prodi():
    response = supabase.table('prodi').select('*').order('id', desc=False).execute()
    return response.data or []


def get_all_matkul():
    response = supabase.table('matkul').select('*').order('id', desc=False).execute()
    return response.data or []


def get_user_by_id(user_id):
    response = supabase.table('users').select('*').eq('id', user_id).limit(1).execute()
    data = response.data
    return data[0] if data else None


def record_activity(user_id, nama, role, aktivitas):
    supabase.table('aktivitas').insert({
        'user_id': user_id,
        'nama': nama,
        'role': role,
        'aktivitas': aktivitas
    }).execute()


@app.route('/uploads/<path:filename>')
def download_file(filename):
    return send_from_directory(UPLOAD_DIR, filename, as_attachment=True)


@app.route('/admin/users')
@login_required
def admin_users():
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    users = supabase.table('users').select('*').order('id', desc=False).execute().data or []
    prodi = {item['id']: item['nama_prodi'] for item in get_all_prodi()}
    matkul = {item['id']: item['nama_matkul'] for item in get_all_matkul()}

    return render_template(
        'admin_users.html',
        website_settings=get_website_settings(),
        users=users,
        prodi=prodi,
        matkul=matkul
    )


@app.route('/admin/users/add', methods=['GET', 'POST'])
@login_required
def admin_user_add():
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    prodi = get_all_prodi()
    matkul = get_all_matkul()

    if request.method == 'POST':
        nama = request.form.get('nama', '').strip()
        username = request.form.get('username', '').strip()
        password = md5_hash(request.form.get('password', '').strip())
        role = request.form.get('role')
        prodi_id = request.form.get('prodi') or None
        matkul_id = request.form.get('matkul') or None
        umur = request.form.get('umur') or None
        no_hp = request.form.get('no_hp') or None
        agama = request.form.get('agama') or None
        alamat = request.form.get('alamat') or None
        nisn = request.form.get('nisn') or None
        nidn = request.form.get('nidn') or None

        supabase.table('users').insert({
            'nama': nama,
            'username': username,
            'password': password,
            'role': role,
            'prodi_id': prodi_id,
            'matkul_id': matkul_id,
            'umur': umur,
            'no_hp': no_hp,
            'agama': agama,
            'alamat': alamat,
            'nisn': nisn,
            'nidn': nidn
        }).execute()

        return redirect(url_for('admin_users'))

    return render_template(
        'admin_user_form.html',
        website_settings=get_website_settings(),
        prodi=prodi,
        matkul=matkul,
        user_data=None
    )


@app.route('/admin/users/<int:user_id>/edit', methods=['GET', 'POST'])
@login_required
def admin_user_edit(user_id):
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    user_data = get_user_by_id(user_id)
    if not user_data:
        return redirect(url_for('admin_users'))

    prodi = get_all_prodi()
    matkul = get_all_matkul()

    if request.method == 'POST':
        nama = request.form.get('nama', '').strip()
        username = request.form.get('username', '').strip()
        prodi_id = request.form.get('prodi') or None
        matkul_id = request.form.get('matkul') or None
        umur = request.form.get('umur') or None
        no_hp = request.form.get('no_hp') or None
        agama = request.form.get('agama') or None
        alamat = request.form.get('alamat') or None
        nisn = request.form.get('nisn') or None
        nidn = request.form.get('nidn') or None

        update_data = {
            'nama': nama,
            'username': username,
            'prodi_id': prodi_id,
            'matkul_id': matkul_id,
            'umur': umur,
            'no_hp': no_hp,
            'agama': agama,
            'alamat': alamat,
            'nisn': nisn,
            'nidn': nidn
        }

        supabase.table('users').update(update_data).eq('id', user_id).execute()
        return redirect(url_for('admin_users'))

    return render_template(
        'admin_user_form.html',
        website_settings=get_website_settings(),
        prodi=prodi,
        matkul=matkul,
        user_data=user_data
    )


@app.route('/admin/payments', methods=['GET', 'POST'])
@login_required
def admin_payments():
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    students = supabase.table('users').select('*').eq('role', 'mahasiswa').order('nama', desc=False).execute().data or []
    payments = supabase.table('payments').select('*').order('payment_date', desc=True).execute().data or []

    if request.method == 'POST':
        if request.form.get('form_type') == 'create_payment':
            student_id = request.form.get('student_id')
            amount = request.form.get('amount')
            description = request.form.get('description')
            payment_method = request.form.get('payment_method')
            transaction_id = request.form.get('transaction_id')

            supabase.table('payments').insert({
                'student_id': student_id,
                'amount': amount,
                'description': description,
                'payment_method': payment_method,
                'transaction_id': transaction_id,
                'status': 'pending'
            }).execute()

            student = get_user_by_id(student_id)
            record_activity(user['id'], user['nama'], 'admin', f"Menambahkan tagihan pembayaran Rp {amount} untuk mahasiswa {student['nama'] if student else ''}")
            return redirect(url_for('admin_payments'))

        elif request.form.get('action') in ['confirm', 'reject']:
            payment_id = request.form.get('payment_id')
            action = request.form.get('action')
            status = 'confirmed' if action == 'confirm' else 'rejected'
            notes = request.form.get('notes') or None

            supabase.table('payments').update({
                'status': status,
                'confirmed_by': user['id'],
                'confirmed_date': datetime.datetime.utcnow().isoformat(),
                'notes': notes
            }).eq('id', payment_id).execute()

            record_activity(user['id'], user['nama'], 'admin', f"{'Mengonfirmasi' if status == 'confirmed' else 'Menolak'} pembayaran ID {payment_id}")
            return redirect(url_for('admin_payments'))

    return render_template(
        'admin_payments.html',
        website_settings=get_website_settings(),
        students=students,
        payments=payments
    )


@app.route('/mahasiswa/materi')
@login_required
def mahasiswa_materi():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    materi = supabase.table('materi').select('*').eq('matkul_id', user['matkul_id']).order('id', desc=True).execute().data or []
    return render_template(
        'mahasiswa_materi.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        materi=materi
    )


@app.route('/mahasiswa/tugas')
@login_required
def mahasiswa_tugas():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    tugas = supabase.table('tugas').select('*').eq('matkul_id', user['matkul_id']).order('id', desc=True).execute().data or []
    submitted = supabase.table('pengumpulan_tugas').select('*').eq('mahasiswa_id', user['id']).execute().data or []
    submitted_map = {int(item['tugas_id']): item for item in submitted}

    return render_template(
        'mahasiswa_tugas.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        tugas=tugas,
        submitted_map=submitted_map
    )


@app.route('/mahasiswa/tugas/<int:tugas_id>', methods=['GET', 'POST'])
@login_required
def mahasiswa_tugas_submit(tugas_id):
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    task = supabase.table('tugas').select('*').eq('id', tugas_id).eq('matkul_id', user['matkul_id']).limit(1).execute().data
    if not task:
        return redirect(url_for('mahasiswa_tugas'))
    task = task[0]

    submitted = supabase.table('pengumpulan_tugas').select('*').eq('tugas_id', tugas_id).eq('mahasiswa_id', user['id']).limit(1).execute().data
    submitted = submitted[0] if submitted else None

    error = None
    if request.method == 'POST' and not submitted:
        file = request.files.get('file')
        if file and file.filename:
            filename = f"{int(time.time())}_{user['id']}_{file.filename.replace(' ', '_')}"
            save_path = os.path.join(TUGAS_UPLOAD_DIR, filename)
            file.save(save_path)
            supabase.table('pengumpulan_tugas').insert({
                'tugas_id': tugas_id,
                'mahasiswa_id': user['id'],
                'file': filename
            }).execute()
            supabase.table('tugas_kumpul').insert({
                'tugas_id': tugas_id,
                'mahasiswa': user['nama'],
                'file': filename
            }).execute()
            record_activity(user['id'], user['nama'], 'mahasiswa', 'Mengupload tugas')
            return redirect(url_for('mahasiswa_tugas_submit', tugas_id=tugas_id))
        error = 'Pilih file untuk diupload.'

    return render_template(
        'mahasiswa_submit_task.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        task=task,
        submitted=submitted,
        error=error
    )


@app.route('/mahasiswa/presensi', methods=['GET', 'POST'])
@login_required
def mahasiswa_presensi():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    presensi_data = supabase.table('presensi').select('*').eq('status', 'buka').eq('matkul_id', user['matkul_id']).order('id', desc=True).limit(1).execute().data
    presensi = presensi_data[0] if presensi_data else None
    submitted = None
    if presensi:
        submitted_data = supabase.table('presensi_mahasiswa').select('*').eq('presensi_id', presensi['id']).eq('mahasiswa', user['nama']).limit(1).execute().data
        submitted = submitted_data[0] if submitted_data else None

    if request.method == 'POST' and presensi and not submitted:
        ket = None
        if 'hadir' in request.form:
            ket = 'hadir'
        elif 'sakit' in request.form:
            ket = 'sakit'
        elif 'izin' in request.form:
            ket = 'izin'

        if ket:
            supabase.table('presensi_mahasiswa').insert({
                'presensi_id': presensi['id'],
                'mahasiswa': user['nama'],
                'keterangan': ket
            }).execute()
            record_activity(user['id'], user['nama'], 'mahasiswa', 'Melakukan absensi')
            return redirect(url_for('mahasiswa_presensi'))

    return render_template(
        'mahasiswa_presensi.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        presensi=presensi,
        submitted=submitted
    )


@app.route('/mahasiswa/pembayaran', methods=['GET', 'POST'])
@login_required
def mahasiswa_payments():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    if request.method == 'POST':
        amount = request.form.get('amount')
        description = request.form.get('description')
        payment_method = request.form.get('payment_method')
        transaction_id = request.form.get('transaction_id')

        supabase.table('payments').insert({
            'student_id': user['id'],
            'amount': amount,
            'description': description,
            'payment_method': payment_method,
            'transaction_id': transaction_id,
            'status': 'pending'
        }).execute()
        record_activity(user['id'], user['nama'], 'mahasiswa', f"Mengajukan pembayaran Rp {amount}")
        return redirect(url_for('mahasiswa_payments'))

    payments = supabase.table('payments').select('*').eq('student_id', user['id']).order('payment_date', desc=True).execute().data or []
    return render_template(
        'mahasiswa_pembayaran.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        payments=payments
    )


@app.route('/dosen/materi', methods=['GET', 'POST'])
@login_required
def dosen_materi():
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    if request.method == 'POST':
        judul = request.form.get('judul')
        file = request.files.get('file')
        filename = None
        if file and file.filename:
            filename = f"{int(time.time())}_{file.filename.replace(' ', '_')}"
            file.save(os.path.join(MATERI_UPLOAD_DIR, filename))
        supabase.table('materi').insert({
            'judul': judul,
            'file': filename,
            'matkul_id': user['matkul_id']
        }).execute()
        record_activity(user['id'], user['nama'], 'dosen', 'Mengupload materi')
        return redirect(url_for('dosen_materi'))

    materi = supabase.table('materi').select('*').eq('matkul_id', user['matkul_id']).order('id', desc=True).execute().data or []
    return render_template(
        'dosen_materi.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        materi=materi
    )


@app.route('/dosen/tugas', methods=['GET', 'POST'])
@login_required
def dosen_tugas():
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    if request.method == 'POST':
        judul = request.form.get('judul')
        deskripsi = request.form.get('deskripsi')
        deadline = request.form.get('deadline')
        file = request.files.get('file')
        filename = None
        if file and file.filename:
            filename = f"{int(time.time())}_{file.filename.replace(' ', '_')}"
            file.save(os.path.join(UPLOAD_DIR, 'tugas', filename))
        supabase.table('tugas').insert({
            'judul': judul,
            'deskripsi': deskripsi,
            'deadline': deadline,
            'file': filename,
            'matkul_id': user['matkul_id']
        }).execute()
        return redirect(url_for('dosen_tugas'))

    tugas = supabase.table('tugas').select('*').eq('matkul_id', user['matkul_id']).order('id', desc=True).execute().data or []
    return render_template(
        'dosen_tugas.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        tugas=tugas
    )


@app.route('/dosen/tugas/<int:tugas_id>/edit', methods=['GET', 'POST'])
@login_required
def dosen_tugas_edit(tugas_id):
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    task_data = supabase.table('tugas').select('*').eq('id', tugas_id).eq('matkul_id', user['matkul_id']).limit(1).execute().data
    if not task_data:
        return redirect(url_for('dosen_tugas'))
    task_data = task_data[0]

    if request.method == 'POST':
        judul = request.form.get('judul')
        deskripsi = request.form.get('deskripsi')
        deadline = request.form.get('deadline')
        update_data = {'judul': judul, 'deskripsi': deskripsi, 'deadline': deadline}
        supabase.table('tugas').update(update_data).eq('id', tugas_id).execute()
        return redirect(url_for('dosen_tugas'))

    return render_template(
        'dosen_tugas_edit.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        task=task_data
    )


@app.route('/dosen/tugas/<int:tugas_id>/delete', methods=['POST'])
@login_required
def dosen_tugas_delete(tugas_id):
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))
    supabase.table('tugas').delete().eq('id', tugas_id).execute()
    return redirect(url_for('dosen_tugas'))


@app.route('/dosen/presensi', methods=['GET', 'POST'])
@login_required
def dosen_presensi():
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    presensi_open_data = supabase.table('presensi').select('*').eq('status', 'buka').eq('matkul_id', user['matkul_id']).order('id', desc=True).limit(1).execute().data
    presensi_open = presensi_open_data[0] if presensi_open_data else None

    if request.method == 'POST':
        if 'buka' in request.form:
            if presensi_open:
                supabase.table('presensi').update({'status': 'tutup'}).eq('id', presensi_open['id']).execute()
            supabase.table('presensi').insert({'tanggal': datetime.date.today().isoformat(), 'status': 'buka', 'matkul_id': user['matkul_id']}).execute()
            return redirect(url_for('dosen_presensi'))
        if 'tutup' in request.form and presensi_open:
            supabase.table('presensi').update({'status': 'tutup'}).eq('id', presensi_open['id']).execute()
            return redirect(url_for('dosen_presensi'))

    return render_template(
        'dosen_presensi.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        presensi_open=presensi_open
    )


@app.route('/dosen/pengumpulan')
@login_required
def dosen_pengumpulan():
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    tugas_items = supabase.table('tugas').select('*').eq('matkul_id', user['matkul_id']).execute().data or []
    tugas_data = {item['id']: item['judul'] for item in tugas_items}
    tugas_ids = [item['id'] for item in tugas_items]

    pengumpulan_all = supabase.table('tugas_kumpul').select('*').execute().data or []
    pengumpulan = [item for item in pengumpulan_all if item.get('tugas_id') in tugas_ids]

    return render_template(
        'dosen_pengumpulan.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        pengumpulan=pengumpulan,
        tugas_data=tugas_data
    )


# Register API Blueprint
from api import api
app.register_blueprint(api)


if __name__ == '__main__':
    app.run(debug=True)
