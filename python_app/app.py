import datetime
import re
import hashlib
import json
import os
import time
import urllib.error
import urllib.request
from html import escape
from pathlib import Path

from flask import Flask, render_template, request, redirect, url_for, session, flash, send_from_directory, jsonify
from flask_cors import CORS
from dotenv import load_dotenv
from supabase import create_client

dotenv_path = os.path.join(os.path.dirname(__file__), '.env')
load_dotenv(dotenv_path)

SUPABASE_URL = os.getenv('SUPABASE_URL')
SUPABASE_KEY = os.getenv('SUPABASE_KEY')
SUPABASE_SERVICE_ROLE_KEY = os.getenv('SUPABASE_SERVICE_ROLE_KEY')
SUPABASE_CLIENT_KEY = SUPABASE_SERVICE_ROLE_KEY or SUPABASE_KEY
SECRET_KEY = os.getenv('FLASK_SECRET_KEY', 'change-me')

app = Flask(__name__)
app.secret_key = SECRET_KEY

# Enable CORS for API endpoints
CORS(app, resources={r"/api/*": {"origins": "*"}})

UPLOAD_DIR = os.path.join(app.root_path, 'uploads')
ADS_FILE_PATH = os.getenv('ADS_FILE_PATH') or os.path.join(app.root_path, 'ads.json')
USERS_FILE_PATH = os.getenv('USERS_FILE_PATH') or os.path.join(app.root_path, 'users.json')
MATERI_UPLOAD_DIR = os.path.join(UPLOAD_DIR, 'materi')
TUGAS_UPLOAD_DIR = os.path.join(UPLOAD_DIR, 'tugas')

os.makedirs(MATERI_UPLOAD_DIR, exist_ok=True)
os.makedirs(TUGAS_UPLOAD_DIR, exist_ok=True)


class FakeSupabaseResponse:
    def __init__(self, data=None, count=None):
        self.data = data or []
        self.count = count


class SafeSupabaseResponse:
    def __init__(self, data=None, count=0):
        self.data = data or []
        self.count = count


class SafeSupabaseQuery:
    def __init__(self, query):
        self._query = query

    def select(self, *args, **kwargs):
        self._query = self._query.select(*args, **kwargs)
        return self

    def match(self, filters):
        self._query = self._query.match(filters)
        return self

    def eq(self, column, value):
        self._query = self._query.eq(column, value)
        return self

    def in_(self, column, values):
        self._query = self._query.in_(column, values)
        return self

    def order(self, column, desc=False):
        self._query = self._query.order(column, desc=desc)
        return self

    def limit(self, value):
        self._query = self._query.limit(value)
        return self

    def insert(self, payload):
        self._query = self._query.insert(payload)
        return self

    def update(self, payload):
        self._query = self._query.update(payload)
        return self

    def delete(self):
        self._query = self._query.delete()
        return self

    def execute(self):
        try:
            response = self._query.execute()
            if hasattr(response, 'data') and hasattr(response, 'count'):
                return response
            return SafeSupabaseResponse(data=getattr(response, 'data', []), count=getattr(response, 'count', 0))
        except Exception:
            return SafeSupabaseResponse([], 0)


class FakeSupabaseQuery:
    def __init__(self, table_name):
        self.table_name = table_name
        self._filters = {}
        self._payload = None
        self._delete = False
        self._order = None
        self._limit_value = None

    def select(self, *args, **kwargs):
        return self

    def match(self, filters):
        self._filters.update(filters)
        return self

    def eq(self, column, value):
        self._filters[column] = value
        return self

    def in_(self, column, values):
        self._filters[column] = values
        return self

    def order(self, column, desc=False):
        self._order = (column, desc)
        return self

    def limit(self, value):
        self._limit_value = value
        return self

    def insert(self, payload):
        self._payload = payload
        return self

    def update(self, payload):
        self._payload = payload
        return self

    def delete(self):
        self._delete = True
        return self

    def execute(self):
        if self._delete:
            return FakeSupabaseResponse([])
        if self._payload is not None:
            return FakeSupabaseResponse([self._payload])
        return FakeSupabaseResponse([], count=0)


class FakeSupabaseClient:
    def table(self, table_name):
        return FakeSupabaseQuery(table_name)


class SafeSupabaseClient:
    def __init__(self, client):
        self._client = client

    def table(self, table_name):
        return SafeSupabaseQuery(self._client.table(table_name))


if SUPABASE_URL and SUPABASE_CLIENT_KEY:
    try:
        supabase = SafeSupabaseClient(create_client(SUPABASE_URL, SUPABASE_CLIENT_KEY))
    except Exception:
        supabase = FakeSupabaseClient()
else:
    supabase = FakeSupabaseClient()


@app.context_processor
def inject_app_context():
    return {'campus_ad': get_campus_ad()}


def md5_hash(text: str) -> str:
    return hashlib.md5(text.encode('utf-8')).hexdigest()


def get_website_settings():
    try:
        response = supabase.table('website_settings').select('*').limit(1).execute()
        data = response.data
        if data:
            return data[0]
    except Exception as e:
        pass
    return {
        'nama_website': 'E-Learning',
        'deskripsi': 'Platform pembelajaran online'
    }


def get_presensi_status(matkul_id):
    try:
        presensi_open_data = supabase.table('presensi').select('*').eq('status', 'buka').eq('matkul_id', matkul_id).order('id', desc=True).limit(1).execute().data or []
        return 'BUKA' if presensi_open_data else 'TUTUP'
    except Exception:
        return 'TUTUP'


def get_matkul_ids_for_prodi(prodi_id):
    try:
        response = supabase.table('matkul').select('id').eq('prodi_id', prodi_id).execute()
        if response.data:
            return [item['id'] for item in response.data if item.get('id') is not None]
    except Exception:
        pass
    return []


def get_presensi_status_for_prodi(prodi_id):
    matkul_ids = get_matkul_ids_for_prodi(prodi_id)
    if not matkul_ids:
        return 'TUTUP'
    try:
        presensi_open_data = supabase.table('presensi').select('*').eq('status', 'buka').in_('matkul_id', matkul_ids).order('id', desc=True).limit(1).execute().data or []
        return 'BUKA' if presensi_open_data else 'TUTUP'
    except Exception:
        return 'TUTUP'


def load_ads_from_file(path=None):
    path = Path(path or ADS_FILE_PATH)
    if not path.exists():
        return []
    try:
        data = json.loads(path.read_text(encoding='utf-8'))
    except (OSError, json.JSONDecodeError):
        return []
    return data if isinstance(data, list) else []


def save_ads_to_file(ads, path=None):
    path = Path(path or ADS_FILE_PATH)
    if path.exists() and path.is_dir():
        path = path / 'ads.json'
    if path.name == '.':
        path = path.resolve() / 'ads.json'
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(ads, indent=2, ensure_ascii=False), encoding='utf-8')
    return path


def save_users_to_file(users, path=None):
    """Save users to JSON file fallback."""
    path = Path(path or USERS_FILE_PATH)
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(users, indent=2, ensure_ascii=False), encoding='utf-8')
    return path


def save_user_to_fallback(user_payload, path=None):
    path = Path(path or USERS_FILE_PATH)
    path.parent.mkdir(parents=True, exist_ok=True)
    users = load_users_from_file(path)
    existing_usernames = {u.get('username') for u in users if u.get('username')}
    if user_payload.get('username') in existing_usernames:
        return None
    users.append(user_payload)
    path.write_text(json.dumps(users, indent=2, ensure_ascii=False), encoding='utf-8')
    return user_payload


def get_campus_ad():
    fallback = {
        'title': 'Pendaftaran Kampus',
        'description': 'Segera daftarkan diri Anda dan mulai langkah baru di kampus pilihan.',
        'link': 'https://example.com/pendaftaran',
        'image_url': '',
        'html_content': ''
    }

    ads = load_ads_from_file()
    active_ads = [ad for ad in ads if ad.get('active', True)]
    if active_ads:
        ad = active_ads[0]
        html_content = ad.get('html_content', '')
        return {
            'title': ad.get('title', 'Pendaftaran Kampus'),
            'description': ad.get('description', fallback['description']),
            'link': ad.get('link', fallback['link']),
            'image_url': ad.get('image_url', ''),
            'button_text': ad.get('button_text', 'Buka Link'),
            'html_content': html_content
        }

    ad_url = os.getenv('CAMPUS_AD_URL', '').strip()
    if not ad_url:
        return fallback

    try:
        request = urllib.request.Request(ad_url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(request, timeout=10) as response:
            content = response.read().decode('utf-8', errors='ignore').strip()
    except (urllib.error.URLError, urllib.error.HTTPError, TimeoutError, ValueError, OSError):
        return fallback

    if not content:
        return fallback

    try:
        payload = json.loads(content)
    except json.JSONDecodeError:
        payload = None

    if isinstance(payload, dict):
        title = payload.get('title') or 'Pendaftaran Kampus'
        description = payload.get('description') or payload.get('text') or fallback['description']
        link = payload.get('link') or payload.get('url') or fallback['link']
        image_url = payload.get('image_url') or payload.get('image') or ''
        html_content = payload.get('html') or ''
        if not html_content:
            html_content = (
                f"<div class='campus-ad__card'><h5>{escape(title)}</h5>"
                f"<p>{escape(str(description))}</p></div>"
            )
        return {
            'title': title,
            'description': description,
            'link': link,
            'image_url': image_url,
            'html_content': html_content
        }

    return {
        'title': 'Pendaftaran Kampus',
        'description': content[:220],
        'link': ad_url,
        'image_url': '',
        'html_content': f"<div class='campus-ad__card'><p>{escape(content[:220])}</p></div>"
    }


def load_users_from_file(path=None):
    """Load users from JSON file fallback."""
    path = Path(path or USERS_FILE_PATH)
    if not path.exists():
        return []
    try:
        data = json.loads(path.read_text(encoding='utf-8'))
    except (OSError, json.JSONDecodeError):
        return []
    return data if isinstance(data, list) else []


def get_user_by_credentials(username: str, password: str):
    hashed = md5_hash(password)
    
    # Try Supabase first
    try:
        response = supabase.table('users').select('*').match({
            'username': username,
            'password': hashed
        }).limit(1).execute()
        data = response.data
        if data:
            return data[0]
    except Exception:
        pass
    
    # Fallback to users.json file
    users = load_users_from_file()
    for user in users:
        if user.get('username') == username and user.get('password') == hashed:
            return user
    
    return None


def count_table(table: str, filters: dict = None):
    try:
        query = supabase.table(table).select('id', count='exact')
        if filters:
            for column, value in filters.items():
                if isinstance(value, list):
                    query = query.in_(column, value)
                else:
                    query = query.eq(column, value)
        response = query.execute()

        if hasattr(response, 'count') and response.count is not None:
            return response.count
        if response.data:
            return len(response.data)
    except Exception:
        pass
    return 0


def get_users_by_role(role: str, matkul_id=None):
    try:
        query = supabase.table('users').select('*').eq('role', role)
        if matkul_id is not None:
            query = query.eq('matkul_id', matkul_id)
        response = query.order('nama', desc=False).execute()
        return response.data or []
    except Exception:
        return []


def get_activity_feed(limit: int = 5):
    try:
        response = supabase.table('aktivitas').select('*').order('id', desc=True).limit(limit).execute()
        return response.data or []
    except Exception:
        return []


def get_monthly_student_counts():
    try:
        response = supabase.table('users').select('created_at').eq('role', 'mahasiswa').execute()
        data = response.data or []
    except Exception:
        data = []
    
    months = []
    totals = []
    names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
    counts = {i: 0 for i in range(1, 13)}

    for row in data:
        created_at = row.get('created_at')
        if created_at:
            try:
                month = int(created_at[5:7])
                counts[month] += 1
            except (ValueError, IndexError):
                pass

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


def mahasiswa_needs_selection(user: dict) -> bool:
    if not user or user.get('role') != 'mahasiswa':
        return False
    prodi_id = user.get('prodi_id')
    matkul_id = user.get('matkul_id')
    if not prodi_id or not matkul_id:
        return True
    if isinstance(prodi_id, str) and prodi_id.strip().lower() in ('none', 'null', ''):
        return True
    if isinstance(matkul_id, str) and matkul_id.strip().lower() in ('none', 'null', ''):
        return True
    return False


@app.route('/')
def home():
    if 'user' in session:
        user = session['user']
        if user.get('role') == 'mahasiswa' and mahasiswa_needs_selection(user):
            return redirect(url_for('mahasiswa_pilih_matkul'))
        return redirect(url_for(user['role']))
    return redirect(url_for('login'))


@app.route('/login', methods=['GET', 'POST'])
def login():
    website_settings = get_website_settings()
    if request.method == 'POST':
        try:
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
                record_activity(user.get('id'), user.get('nama'), user.get('role'), 'Login berhasil')
                if session['user'].get('role') == 'mahasiswa':
                    return redirect(url_for('mahasiswa_pilih_matkul'))
                return redirect(url_for(user.get('role')))

            flash('Username atau password salah!', 'danger')
        except Exception as e:
            flash(f'Error: Pastikan Supabase credentials benar di .env file', 'danger')

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
    dosen_users = get_users_by_role('dosen')
    mahasiswa_users = get_users_by_role('mahasiswa')

    prodi_map = get_prodi_map()
    matkul_map = get_matkul_map()

    dosen_users = annotate_users_with_names(dosen_users, prodi_map, matkul_map)
    mahasiswa_users = annotate_users_with_names(mahasiswa_users, prodi_map, matkul_map)

    grouped_dosen = {}
    for dosen_user in dosen_users:
        prodi_label = dosen_user['prodi_name']
        grouped_dosen.setdefault(prodi_label, []).append(dosen_user)

    grouped_mahasiswa = {}
    for mahasiswa in mahasiswa_users:
        prodi_label = mahasiswa['prodi_name']
        matkul_label = mahasiswa['matkul_name']
        grouped_mahasiswa.setdefault(prodi_label, {}).setdefault(matkul_label, []).append(mahasiswa)

    return render_template(
        'admin_dashboard.html',
        website_settings=website_settings,
        nama=user['nama'],
        admin_count=admin_count,
        dosen_count=dosen_count,
        mahasiswa_count=mahasiswa_count,
        mahasiswa_labels=mahasiswa_labels,
        mahasiswa_totals=mahasiswa_totals,
        activities=activities,
        grouped_dosen=grouped_dosen,
        grouped_mahasiswa=grouped_mahasiswa
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

    presensi_status = get_presensi_status(user['matkul_id'])
    mahasiswa_users = get_users_by_role('mahasiswa', matkul_id=user['matkul_id'])

    prodi_map = get_prodi_map()
    matkul_map = get_matkul_map()

    mahasiswa_users = annotate_users_with_names(mahasiswa_users, prodi_map, matkul_map)
    matkul_name = matkul_map.get(user.get('matkul_id')) or 'Tidak terdaftar'
    prodi_name = prodi_map.get(user.get('prodi_id')) or 'Tidak terdaftar'

    return render_template(
        'dosen_dashboard.html',
        website_settings=website_settings,
        nama=user['nama'],
        materi_count=materi_count,
        tugas_count=tugas_count,
        presensi_status=presensi_status,
        mahasiswa_users=mahasiswa_users,
        matkul_name=matkul_name,
        prodi_name=prodi_name
    )


@app.route('/mahasiswa')
@login_required
def mahasiswa():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    if mahasiswa_needs_selection(user):
        return redirect(url_for('mahasiswa_pilih_matkul'))

    website_settings = get_website_settings()
    materi_count = count_table('materi', {'matkul_id': user['matkul_id']})
    tugas_count = count_table('tugas', {'matkul_id': user['matkul_id']})

    presensi_status = get_presensi_status(user['matkul_id'])

    return render_template(
        'mahasiswa_dashboard.html',
        website_settings=website_settings,
        nama=user['nama'],
        materi_count=materi_count,
        tugas_count=tugas_count,
        presensi_status=presensi_status
    )


def get_all_prodi():
    try:
        response = supabase.table('prodi').select('*').order('id', desc=False).execute()
        if response.data:
            return response.data
    except Exception:
        pass

    # Fallback: parse prodi from local SQL dump if Supabase returns no rows or fails
    try:
        sql_path = os.path.join(app.root_path, '..', 'elearning.sql')
        if not os.path.exists(sql_path):
            sql_path = os.path.join(app.root_path, 'elearning.sql')
        if os.path.exists(sql_path):
            txt = open(sql_path, encoding='utf-8', errors='ignore').read()
            m = re.search(r"INSERT INTO `prodi` .*?VALUES\s*(.*?);", txt, re.S | re.I)
            if m:
                vals = m.group(1)
                tuples = re.findall(r"\(([^)]+)\)", vals)
                result = []
                for t in tuples:
                    parts = [p.strip() for p in t.split(',', 1)]
                    if len(parts) >= 2:
                        try:
                            pid = int(parts[0])
                        except Exception:
                            pid = None
                        name = parts[1].strip()
                        # strip quotes
                        if name.startswith("'") and name.endswith("'"):
                            name = name[1:-1]
                        result.append({'id': pid, 'nama_prodi': name})
                return result
    except Exception:
        pass
    return []


def get_all_matkul():
    try:
        response = supabase.table('matkul').select('*').order('id', desc=False).execute()
        if response.data:
            return response.data
    except Exception:
        pass

    # Fallback: parse matkul from local SQL dump if Supabase returns no rows or fails
    try:
        sql_path = os.path.join(app.root_path, '..', 'elearning.sql')
        if not os.path.exists(sql_path):
            sql_path = os.path.join(app.root_path, 'elearning.sql')
        if os.path.exists(sql_path):
            txt = open(sql_path, encoding='utf-8', errors='ignore').read()
            m = re.search(r"INSERT INTO `matkul` .*?VALUES\s*(.*?);", txt, re.S | re.I)
            if m:
                vals = m.group(1)
                tuples = re.findall(r"\(([^)]+)\)", vals)
                result = []
                for t in tuples:
                    parts = [p.strip() for p in t.split(',', 2)]
                    if len(parts) >= 3:
                        try:
                            mid = int(parts[0])
                        except Exception:
                            mid = None
                        try:
                            prid = int(parts[1])
                        except Exception:
                            prid = None
                        name = parts[2].strip()
                        if name.startswith("'") and name.endswith("'"):
                            name = name[1:-1]
                        result.append({'id': mid, 'prodi_id': prid, 'nama_matkul': name})
                return result
    except Exception:
        pass
    return []


def get_prodi_map():
    return {item['id']: item.get('nama_prodi') for item in get_all_prodi()}


def get_matkul_map():
    return {item['id']: item.get('nama_matkul') for item in get_all_matkul()}


def annotate_users_with_names(users, prodi_map, matkul_map):
    result = []
    for user in users:
        item = dict(user)
        item['prodi_name'] = prodi_map.get(user.get('prodi_id')) or 'Tidak terdaftar'
        item['matkul_name'] = matkul_map.get(user.get('matkul_id')) or 'Tidak terdaftar'
        result.append(item)
    return result


def get_next_id_for_table(table_name: str):
    try:
        response = supabase.table(table_name).select('id').order('id', desc=True).limit(1).execute()
        data = response.data or []
        if data:
            first_id = data[0].get('id')
            if first_id is not None:
                return int(first_id) + 1
    except Exception:
        pass
    return 1


def get_user_by_id(user_id):
    try:
        response = supabase.table('users').select('*').eq('id', user_id).limit(1).execute()
        data = response.data
        if data:
            return data[0]
    except Exception:
        pass

    # Fallback: check local users JSON file
    try:
        users = load_users_from_file()
        for u in users:
            # compare both str and int forms
            if str(u.get('id')) == str(user_id):
                return u
    except Exception:
        pass

    return None


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
        password = request.form.get('password', '').strip()
        role = request.form.get('role', '').strip()
        prodi_id = request.form.get('prodi') or None
        matkul_id = request.form.get('matkul') or None
        umur = request.form.get('umur') or None
        no_hp = request.form.get('no_hp') or None
        agama = request.form.get('agama') or None
        alamat = request.form.get('alamat') or None
        nisn = request.form.get('nisn') or None
        nidn = request.form.get('nidn') or None
        email = request.form.get('email', '').strip() or f'{username}@local.test'

        if not nama or not username or not password or not role:
            flash('Nama, username, password, dan role wajib diisi.', 'danger')
            return redirect(url_for('admin_user_add'))

        if role == 'dosen':
            if not prodi_id or not matkul_id:
                flash('Dosen harus memilih Prodi dan Matkul.', 'danger')
                return redirect(url_for('admin_user_add'))
            nisn = None
        elif role == 'mahasiswa':
            if not prodi_id:
                flash('Mahasiswa harus memilih Prodi.', 'danger')
                return redirect(url_for('admin_user_add'))
            matkul_id = None
            nidn = None
        else:
            prodi_id = None
            matkul_id = None
            nisn = None
            nidn = None

        payload = {
            'id': get_next_id_for_table('users'),
            'nama': nama,
            'username': username,
            'password': md5_hash(password),
            'role': role,
            'email': email,
            'prodi_id': prodi_id,
            'matkul_id': matkul_id,
            'umur': umur,
            'no_hp': no_hp,
            'agama': agama,
            'alamat': alamat,
            'nisn': nisn,
            'nidn': nidn,
            'created_at': datetime.datetime.now().isoformat()
        }

        try:
            supabase.table('users').insert(payload).execute()
            record_activity(user['id'], user['nama'], 'admin', f"Menambahkan user {username} dengan role {role}")
            flash('User berhasil ditambahkan.', 'success')
        except Exception as exc:
            fallback_saved = save_user_to_fallback(payload)
            if fallback_saved:
                flash('User disimpan ke fallback lokal karena Supabase gagal.', 'warning')
            else:
                flash(f'Gagal menambahkan user: {exc}', 'danger')

        return redirect(url_for('admin_users'))

    return render_template(
        'admin_user_form.html',
        website_settings=get_website_settings(),
        prodi=prodi,
        matkul=matkul,
        user_data=None
    )


@app.route('/admin/lookup-prodi-matkul')
@login_required
def admin_lookup_prodi_matkul():
    user = session.get('user')
    if not user or user['role'] != 'admin':
        return jsonify({'error': 'unauthorized'}), 403

    return jsonify({
        'prodi': get_all_prodi(),
        'matkul': get_all_matkul()
    })


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
        email = request.form.get('email', '').strip()
        role = request.form.get('role', '').strip()
        prodi_id = request.form.get('prodi') or None
        matkul_id = request.form.get('matkul') or None
        umur = request.form.get('umur') or None
        no_hp = request.form.get('no_hp') or None
        agama = request.form.get('agama') or None
        alamat = request.form.get('alamat') or None
        nisn = request.form.get('nisn') or None
        nidn = request.form.get('nidn') or None

        if role == 'dosen':
            if not prodi_id or not matkul_id:
                flash('Dosen harus memilih Prodi dan Matkul.', 'danger')
                return redirect(url_for('admin_user_edit', user_id=user_id))
            nisn = None
        elif role == 'mahasiswa':
            if not prodi_id:
                flash('Mahasiswa harus memilih Prodi.', 'danger')
                return redirect(url_for('admin_user_edit', user_id=user_id))
            matkul_id = None
            nidn = None
        else:
            prodi_id = None
            matkul_id = None
            nisn = None
            nidn = None

        update_data = {
            'nama': nama,
            'username': username,
            'email': email,
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
        record_activity(user['id'], user['nama'], 'admin', f"Memperbarui data user ID {user_id}")
        return redirect(url_for('admin_users'))

    return render_template(
        'admin_user_form.html',
        website_settings=get_website_settings(),
        prodi=prodi,
        matkul=matkul,
        user_data=user_data
    )


@app.route('/admin/users/<int:user_id>/delete', methods=['POST'])
@login_required
def admin_user_delete(user_id):
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    try:
        # Clear dependent aktivitas references before deleting the user to avoid FK constraint errors.
        supabase.table('aktivitas').update({'user_id': None}).eq('user_id', user_id).execute()
        supabase.table('users').delete().eq('id', user_id).execute()
        record_activity(user['id'], user['nama'], 'admin', f"Menghapus user ID {user_id}")
        flash('User berhasil dihapus.', 'success')
    except Exception as exc:
        flash(f'Gagal menghapus user: {exc}', 'danger')

    return redirect(url_for('admin_users'))


@app.route('/admin/ads', methods=['GET', 'POST'])
@login_required
def admin_ads():
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    if request.method == 'POST':
        title = request.form.get('title', '').strip()
        description = request.form.get('description', '').strip()
        link = request.form.get('link', '').strip()
        image_url = request.form.get('image_url', '').strip()
        button_text = request.form.get('button_text', '').strip() or 'Buka Link'
        active = request.form.get('active') == 'on'
        ad_id = request.form.get('ad_id')

        ads = load_ads_from_file()
        if ad_id:
            for ad in ads:
                if str(ad.get('id')) == str(ad_id):
                    ad.update({
                        'title': title,
                        'description': description,
                        'link': link,
                        'image_url': image_url,
                        'button_text': button_text,
                        'active': active
                    })
                    record_activity(user['id'], user['nama'], 'admin', f'Mengubah iklan ID {ad_id}')
                    break
        else:
            new_ad = {
                'id': int(time.time()),
                'title': title,
                'description': description,
                'link': link,
                'image_url': image_url,
                'button_text': button_text,
                'active': active
            }
            ads.append(new_ad)
            record_activity(user['id'], user['nama'], 'admin', 'Menambahkan iklan baru')

        save_ads_to_file(ads)
        return redirect(url_for('admin_ads'))

    ads = load_ads_from_file()
    return render_template(
        'admin_ads.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        ads=ads
    )


@app.route('/admin/ads/<ad_id>/delete', methods=['POST'])
@login_required
def admin_ads_delete(ad_id):
    user = session['user']
    if user['role'] != 'admin':
        return redirect(url_for(user['role']))

    ads = load_ads_from_file()
    ads = [ad for ad in ads if str(ad.get('id')) != str(ad_id)]
    save_ads_to_file(ads)
    record_activity(user['id'], user['nama'], 'admin', f'Menghapus iklan ID {ad_id}')
    flash('Iklan berhasil dihapus.', 'success')
    return redirect(url_for('admin_ads'))


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
            amount_raw = request.form.get('amount', '').strip()
            description = request.form.get('description')
            payment_method = request.form.get('payment_method')
            transaction_id = request.form.get('transaction_id')

            try:
                amount = float(amount_raw)
                if amount < 0 or amount > 9999999999.99:
                    raise ValueError('Jumlah pembayaran terlalu besar')
                amount = round(amount, 2)
            except (TypeError, ValueError):
                flash('Jumlah pembayaran tidak valid.', 'danger')
                return redirect(url_for('admin_payments'))

            try:
                supabase.table('payments').insert({
                    'student_id': student_id,
                    'amount': amount,
                    'description': description,
                    'payment_method': payment_method,
                    'transaction_id': transaction_id,
                    'payment_date': datetime.datetime.utcnow().isoformat(),
                    'status': 'pending'
                }).execute()
                student = get_user_by_id(student_id)
                record_activity(user['id'], user['nama'], 'admin', f"Menambahkan tagihan pembayaran Rp {amount} untuk mahasiswa {student['nama'] if student else ''}")
                flash('Pembayaran berhasil ditambahkan.', 'success')
            except Exception as exc:
                flash(f'Gagal menambahkan pembayaran: {exc}', 'danger')

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
        payments=[{
            **p,
            'student_name': (get_user_by_id(p.get('student_id')) or {}).get('nama') if p.get('student_id') else ''
        } for p in payments]
    )


@app.route('/mahasiswa/materi')
@login_required
def mahasiswa_materi():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    if not user.get('matkul_id'):
        return redirect(url_for('mahasiswa_pilih_matkul'))

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

    if not user.get('matkul_id'):
        return redirect(url_for('mahasiswa_pilih_matkul'))

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

    if not user.get('matkul_id'):
        return redirect(url_for('mahasiswa_pilih_matkul'))

    task = supabase.table('tugas').select('*').eq('id', tugas_id).eq('matkul_id', user['matkul_id']).limit(1).execute().data
    if not task:
        return redirect(url_for('mahasiswa_tugas'))
    task = task[0]

    submitted = supabase.table('pengumpulan_tugas').select('*').eq('tugas_id', tugas_id).eq('mahasiswa_id', user['id']).limit(1).execute().data
    submitted = submitted[0] if submitted else None

    error = None
    if request.method == 'POST':
        action = request.form.get('action')
        if action == 'delete' and submitted:
            supabase.table('pengumpulan_tugas').delete().eq('tugas_id', tugas_id).eq('mahasiswa_id', user['id']).execute()
            supabase.table('tugas_kumpul').delete().eq('tugas_id', tugas_id).eq('mahasiswa', user['nama']).execute()
            record_activity(user['id'], user['nama'], 'mahasiswa', 'Menghapus pengumpulan tugas')
            flash('Pengumpulan tugas berhasil dihapus.', 'success')
            return redirect(url_for('mahasiswa_tugas'))

        if action == 'edit' and submitted:
            file = request.files.get('file')
            if file and file.filename:
                filename = f"{int(time.time())}_{user['id']}_{file.filename.replace(' ', '_')}"
                save_path = os.path.join(TUGAS_UPLOAD_DIR, filename)
                file.save(save_path)
                supabase.table('pengumpulan_tugas').update({'file': filename, 'submitted_at': datetime.datetime.utcnow().isoformat()}).eq('tugas_id', tugas_id).eq('mahasiswa_id', user['id']).execute()
                supabase.table('tugas_kumpul').update({'file': filename, 'submitted_at': datetime.datetime.utcnow().isoformat()}).eq('tugas_id', tugas_id).eq('mahasiswa', user['nama']).execute()
                record_activity(user['id'], user['nama'], 'mahasiswa', 'Mengubah pengumpulan tugas')
                flash('Pengumpulan tugas berhasil diperbarui.', 'success')
                return redirect(url_for('mahasiswa_tugas_submit', tugas_id=tugas_id))
            error = 'Pilih file baru untuk mengganti pengumpulan.'

        if action in (None, 'upload') and not submitted:
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

    if not user.get('matkul_id'):
        return redirect(url_for('mahasiswa_pilih_matkul'))

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


@app.route('/mahasiswa/pilih-matkul', methods=['GET', 'POST'])
@login_required
def mahasiswa_pilih_matkul():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    prodi = get_all_prodi()
    matkul = get_all_matkul()

    if request.method == 'POST':
        matkul_id = request.form.get('matkul') or None
        if not matkul_id:
            flash('Mata kuliah harus dipilih sebelum masuk dashboard.', 'danger')
            return redirect(url_for('mahasiswa_pilih_matkul'))

        matkul_data = supabase.table('matkul').select('*').eq('id', matkul_id).limit(1).execute().data
        if not matkul_data:
            flash('Mata kuliah tidak valid.', 'danger')
            return redirect(url_for('mahasiswa_pilih_matkul'))

        matkul_data = matkul_data[0]
        prodi_id = matkul_data.get('prodi_id')

        supabase.table('users').update({
            'prodi_id': prodi_id,
            'matkul_id': matkul_id
        }).eq('id', user['id']).execute()

        user['prodi_id'] = prodi_id
        user['matkul_id'] = matkul_id
        session['user'] = user
        record_activity(user['id'], user['nama'], 'mahasiswa', 'Memilih mata kuliah')
        return redirect(url_for('mahasiswa'))

    return render_template(
        'mahasiswa_select_matkul.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        prodi=prodi,
        matkul=matkul,
        selected_prodi=user.get('prodi_id'),
        selected_matkul=user.get('matkul_id'),
        prodi_map={item['id']: item['nama_prodi'] for item in prodi}
    )


@app.route('/mahasiswa/pembayaran', methods=['GET', 'POST'])
@login_required
def mahasiswa_payments():
    user = session['user']
    if user['role'] != 'mahasiswa':
        return redirect(url_for(user['role']))

    if not user.get('matkul_id'):
        return redirect(url_for('mahasiswa_pilih_matkul'))

    if request.method == 'POST':
        amount_raw = request.form.get('amount')
        description = request.form.get('description')
        payment_method = request.form.get('payment_method')
        transaction_id = request.form.get('transaction_id')

        # Normalize and validate amount
        try:
            amount = float(amount_raw)
            amount = round(amount, 2)
        except (TypeError, ValueError):
            flash('Jumlah pembayaran tidak valid.', 'danger')
            return redirect(url_for('mahasiswa_payments'))

        supabase.table('payments').insert({
            'student_id': user['id'],
            'amount': amount,
            'description': description,
            'payment_method': payment_method,
            'transaction_id': transaction_id,
            'payment_date': datetime.datetime.utcnow().isoformat(),
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
        try:
            supabase.table('materi').insert({
                'judul': judul,
                'file': filename,
                'matkul_id': user['matkul_id'],
                'dosen_id': user['id']
            }).execute()
            record_activity(user['id'], user['nama'], 'dosen', 'Mengupload materi')
            flash('Materi berhasil diupload.', 'success')
        except Exception as exc:
            flash(f'Gagal mengupload materi: {exc}', 'danger')
        return redirect(url_for('dosen_materi'))

    materi = supabase.table('materi').select('*').eq('matkul_id', user['matkul_id']).order('id', desc=True).execute().data or []
    return render_template(
        'dosen_materi.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        materi=materi
    )


@app.route('/dosen/materi/<int:materi_id>/edit', methods=['GET', 'POST'])
@login_required
def dosen_materi_edit(materi_id):
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    materi_data = supabase.table('materi').select('*').eq('id', materi_id).eq('dosen_id', user['id']).limit(1).execute().data
    if not materi_data:
        return redirect(url_for('dosen_materi'))
    materi_data = materi_data[0]

    if request.method == 'POST':
        judul = request.form.get('judul')
        file = request.files.get('file')
        update_data = {'judul': judul}

        if file and file.filename:
            filename = f"{int(time.time())}_{user['id']}_{file.filename.replace(' ', '_')}"
            file.save(os.path.join(MATERI_UPLOAD_DIR, filename))
            update_data['file'] = filename

        supabase.table('materi').update(update_data).eq('id', materi_id).execute()
        record_activity(user['id'], user['nama'], 'dosen', f'Mengedit materi {judul}')
        flash('Materi berhasil diperbarui.', 'success')
        return redirect(url_for('dosen_materi'))

    return render_template(
        'dosen_materi_edit.html',
        website_settings=get_website_settings(),
        nama=user['nama'],
        materi=materi_data
    )


@app.route('/dosen/materi/<int:materi_id>/delete', methods=['POST'])
@login_required
def dosen_materi_delete(materi_id):
    user = session['user']
    if user['role'] != 'dosen':
        return redirect(url_for(user['role']))

    supabase.table('materi').delete().eq('id', materi_id).eq('dosen_id', user['id']).execute()
    record_activity(user['id'], user['nama'], 'dosen', f'Menghapus materi ID {materi_id}')
    flash('Materi berhasil dihapus.', 'success')
    return redirect(url_for('dosen_materi'))


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
            file.save(os.path.join(TUGAS_UPLOAD_DIR, filename))
        try:
            supabase.table('tugas').insert({
                'judul': judul,
                'deskripsi': deskripsi,
                'deadline': deadline,
                'file': filename,
                'matkul_id': user['matkul_id'],
                'dosen_id': user['id']
            }).execute()
            record_activity(user['id'], user['nama'], 'dosen', f'Membuat tugas {judul}')
            flash('Tugas berhasil dibuat.', 'success')
        except Exception as exc:
            flash(f'Gagal membuat tugas: {exc}', 'danger')
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

    task_data = supabase.table('tugas').select('*').eq('id', tugas_id).eq('matkul_id', user['matkul_id']).eq('dosen_id', user['id']).limit(1).execute().data
    if not task_data:
        return redirect(url_for('dosen_tugas'))
    task_data = task_data[0]

    if request.method == 'POST':
        judul = request.form.get('judul')
        deskripsi = request.form.get('deskripsi')
        deadline = request.form.get('deadline')
        update_data = {'judul': judul, 'deskripsi': deskripsi, 'deadline': deadline}
        try:
            supabase.table('tugas').update(update_data).eq('id', tugas_id).execute()
            record_activity(user['id'], user['nama'], 'dosen', f'Memperbarui tugas ID {tugas_id}')
            flash('Tugas berhasil diperbarui.', 'success')
        except Exception as exc:
            flash(f'Gagal memperbarui tugas: {exc}', 'danger')
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
    supabase.table('tugas').delete().eq('id', tugas_id).eq('dosen_id', user['id']).execute()
    record_activity(user['id'], user['nama'], 'dosen', f'Menghapus tugas ID {tugas_id}')
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
            try:
                if presensi_open:
                    supabase.table('presensi').update({'status': 'tutup'}).eq('id', presensi_open['id']).execute()
                supabase.table('presensi').insert({'tanggal': datetime.date.today().isoformat(), 'status': 'buka', 'matkul_id': user['matkul_id']}).execute()
                record_activity(user['id'], user['nama'], 'dosen', 'Membuka presensi')
                flash('Presensi berhasil dibuka.', 'success')
            except Exception as exc:
                flash(f'Gagal membuka presensi: {exc}', 'danger')
            return redirect(url_for('dosen_presensi'))
        if 'tutup' in request.form and presensi_open:
            try:
                supabase.table('presensi').update({'status': 'tutup'}).eq('id', presensi_open['id']).execute()
                record_activity(user['id'], user['nama'], 'dosen', 'Menutup presensi')
                flash('Presensi berhasil ditutup.', 'success')
            except Exception as exc:
                flash(f'Gagal menutup presensi: {exc}', 'danger')
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
