"""
REST API Blueprint untuk E-Learning Application
Menyediakan endpoints untuk komunikasi antara Frontend dan Supabase Database
"""

import hashlib
from flask import Blueprint, request, jsonify
from functools import wraps
import os
from dotenv import load_dotenv
from supabase import create_client

load_dotenv()

# Initialize Supabase
SUPABASE_URL = os.getenv('SUPABASE_URL')
SUPABASE_KEY = os.getenv('SUPABASE_KEY')
supabase = create_client(SUPABASE_URL, SUPABASE_KEY)

# Create API Blueprint
api = Blueprint('api', __name__, url_prefix='/api/v1')


def md5_hash(text: str) -> str:
    """Menghitung MD5 hash dari text"""
    return hashlib.md5(text.encode('utf-8')).hexdigest()


def api_response(data=None, message='Success', status_code=200, error=False):
    """Format response API yang konsisten"""
    return jsonify({
        'success': not error,
        'message': message,
        'data': data,
        'status_code': status_code
    }), status_code


# ==================== AUTHENTICATION ENDPOINTS ====================

@api.route('/auth/login', methods=['POST'])
def api_login():
    """
    Login API endpoint
    Body: {username, password}
    """
    try:
        data = request.get_json()
        username = data.get('username', '').strip()
        password = data.get('password', '').strip()

        if not username or not password:
            return api_response(
                message='Username dan password harus diisi',
                status_code=400,
                error=True
            )

        hashed = md5_hash(password)
        response = supabase.table('users').select('*').match({
            'username': username,
            'password': hashed
        }).limit(1).execute()

        user = response.data[0] if response.data else None
        if user:
            return api_response(
                data={
                    'id': user.get('id'),
                    'nama': user.get('nama'),
                    'username': user.get('username'),
                    'role': user.get('role'),
                    'prodi_id': user.get('prodi_id'),
                    'matkul_id': user.get('matkul_id'),
                    'email': user.get('email')
                },
                message='Login berhasil',
                status_code=200
            )
        else:
            return api_response(
                message='Username atau password salah',
                status_code=401,
                error=True
            )
    except Exception as e:
        return api_response(
            message=f'Error: {str(e)}',
            status_code=500,
            error=True
        )


@api.route('/auth/register', methods=['POST'])
def api_register():
    """
    Register API endpoint
    Body: {nama, username, password, email, role, prodi_id, matkul_id}
    """
    try:
        data = request.get_json()
        
        required_fields = ['nama', 'username', 'password', 'email', 'role']
        if not all(field in data for field in required_fields):
            return api_response(
                message='Semua field wajib diisi',
                status_code=400,
                error=True
            )

        # Check if username already exists
        existing = supabase.table('users').select('id').eq('username', data['username']).execute()
        if existing.data:
            return api_response(
                message='Username sudah terdaftar',
                status_code=409,
                error=True
            )

        new_user = {
            'nama': data['nama'],
            'username': data['username'],
            'password': md5_hash(data['password']),
            'email': data['email'],
            'role': data['role'],
            'prodi_id': data.get('prodi_id'),
            'matkul_id': data.get('matkul_id')
        }

        response = supabase.table('users').insert(new_user).execute()
        
        return api_response(
            data=response.data[0] if response.data else None,
            message='Registrasi berhasil',
            status_code=201
        )
    except Exception as e:
        return api_response(
            message=f'Error: {str(e)}',
            status_code=500,
            error=True
        )


# ==================== USER ENDPOINTS ====================

@api.route('/users', methods=['GET'])
def get_users():
    """Get all users dengan optional filter"""
    try:
        role = request.args.get('role')
        query = supabase.table('users').select('*')
        
        if role:
            query = query.eq('role', role)
        
        response = query.order('id', desc=False).execute()
        return api_response(data=response.data, message='Users retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/users/<int:user_id>', methods=['GET'])
def get_user(user_id):
    """Get user by ID"""
    try:
        response = supabase.table('users').select('*').eq('id', user_id).limit(1).execute()
        user = response.data[0] if response.data else None
        
        if not user:
            return api_response(message='User tidak ditemukan', status_code=404, error=True)
        
        return api_response(data=user, message='User retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/users/<int:user_id>', methods=['PUT'])
def update_user(user_id):
    """Update user by ID"""
    try:
        data = request.get_json()
        
        # Hash password jika ada
        if 'password' in data:
            data['password'] = md5_hash(data['password'])
        
        response = supabase.table('users').update(data).eq('id', user_id).execute()
        
        return api_response(
            data=response.data[0] if response.data else None,
            message='User updated',
            status_code=200
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/users/<int:user_id>', methods=['DELETE'])
def delete_user(user_id):
    """Delete user by ID"""
    try:
        response = supabase.table('users').delete().eq('id', user_id).execute()
        return api_response(message='User deleted', status_code=200)
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== MATERI ENDPOINTS ====================

@api.route('/materi', methods=['GET'])
def get_materi():
    """Get all materi dengan optional filter"""
    try:
        matkul_id = request.args.get('matkul_id')
        query = supabase.table('materi').select('*')
        
        if matkul_id:
            query = query.eq('matkul_id', matkul_id)
        
        response = query.order('id', desc=False).execute()
        return api_response(data=response.data, message='Materi retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/materi/<int:materi_id>', methods=['GET'])
def get_materi_detail(materi_id):
    """Get materi by ID"""
    try:
        response = supabase.table('materi').select('*').eq('id', materi_id).limit(1).execute()
        materi = response.data[0] if response.data else None
        
        if not materi:
            return api_response(message='Materi tidak ditemukan', status_code=404, error=True)
        
        return api_response(data=materi, message='Materi retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/materi', methods=['POST'])
def create_materi():
    """Create new materi"""
    try:
        data = request.get_json()
        
        required_fields = ['judul', 'matkul_id', 'dosen_id']
        if not all(field in data for field in required_fields):
            return api_response(
                message='Judul, matkul_id, dan dosen_id harus diisi',
                status_code=400,
                error=True
            )
        
        response = supabase.table('materi').insert(data).execute()
        return api_response(
            data=response.data[0] if response.data else None,
            message='Materi created',
            status_code=201
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/materi/<int:materi_id>', methods=['PUT'])
def update_materi(materi_id):
    """Update materi by ID"""
    try:
        data = request.get_json()
        response = supabase.table('materi').update(data).eq('id', materi_id).execute()
        
        return api_response(
            data=response.data[0] if response.data else None,
            message='Materi updated'
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/materi/<int:materi_id>', methods=['DELETE'])
def delete_materi(materi_id):
    """Delete materi by ID"""
    try:
        response = supabase.table('materi').delete().eq('id', materi_id).execute()
        return api_response(message='Materi deleted')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== TUGAS ENDPOINTS ====================

@api.route('/tugas', methods=['GET'])
def get_tugas():
    """Get all tugas dengan optional filter"""
    try:
        matkul_id = request.args.get('matkul_id')
        query = supabase.table('tugas').select('*')
        
        if matkul_id:
            query = query.eq('matkul_id', matkul_id)
        
        response = query.order('id', desc=False).execute()
        return api_response(data=response.data, message='Tugas retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/tugas/<int:tugas_id>', methods=['GET'])
def get_tugas_detail(tugas_id):
    """Get tugas by ID"""
    try:
        response = supabase.table('tugas').select('*').eq('id', tugas_id).limit(1).execute()
        tugas = response.data[0] if response.data else None
        
        if not tugas:
            return api_response(message='Tugas tidak ditemukan', status_code=404, error=True)
        
        return api_response(data=tugas, message='Tugas retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/tugas', methods=['POST'])
def create_tugas():
    """Create new tugas"""
    try:
        data = request.get_json()
        
        required_fields = ['judul', 'matkul_id', 'dosen_id']
        if not all(field in data for field in required_fields):
            return api_response(
                message='Judul, matkul_id, dan dosen_id harus diisi',
                status_code=400,
                error=True
            )
        
        response = supabase.table('tugas').insert(data).execute()
        return api_response(
            data=response.data[0] if response.data else None,
            message='Tugas created',
            status_code=201
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/tugas/<int:tugas_id>', methods=['PUT'])
def update_tugas(tugas_id):
    """Update tugas by ID"""
    try:
        data = request.get_json()
        response = supabase.table('tugas').update(data).eq('id', tugas_id).execute()
        
        return api_response(
            data=response.data[0] if response.data else None,
            message='Tugas updated'
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/tugas/<int:tugas_id>', methods=['DELETE'])
def delete_tugas(tugas_id):
    """Delete tugas by ID"""
    try:
        response = supabase.table('tugas').delete().eq('id', tugas_id).execute()
        return api_response(message='Tugas deleted')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== PENGUMPULAN TUGAS ENDPOINTS ====================

@api.route('/pengumpulan', methods=['GET'])
def get_pengumpulan():
    """Get all pengumpulan dengan optional filter"""
    try:
        tugas_id = request.args.get('tugas_id')
        mahasiswa_id = request.args.get('mahasiswa_id')
        
        query = supabase.table('pengumpulan').select('*')
        
        if tugas_id:
            query = query.eq('tugas_id', tugas_id)
        if mahasiswa_id:
            query = query.eq('mahasiswa_id', mahasiswa_id)
        
        response = query.order('id', desc=False).execute()
        return api_response(data=response.data, message='Pengumpulan retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/pengumpulan', methods=['POST'])
def create_pengumpulan():
    """Create new pengumpulan"""
    try:
        data = request.get_json()
        
        required_fields = ['tugas_id', 'mahasiswa_id', 'file']
        if not all(field in data for field in required_fields):
            return api_response(
                message='Tugas_id, mahasiswa_id, dan file harus diisi',
                status_code=400,
                error=True
            )
        
        response = supabase.table('pengumpulan').insert(data).execute()
        return api_response(
            data=response.data[0] if response.data else None,
            message='Pengumpulan created',
            status_code=201
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== PRESENSI ENDPOINTS ====================

@api.route('/presensi', methods=['GET'])
def get_presensi():
    """Get all presensi dengan optional filter"""
    try:
        matkul_id = request.args.get('matkul_id')
        mahasiswa_id = request.args.get('mahasiswa_id')
        status = request.args.get('status')
        
        query = supabase.table('presensi').select('*')
        
        if matkul_id:
            query = query.eq('matkul_id', matkul_id)
        if mahasiswa_id:
            query = query.eq('mahasiswa_id', mahasiswa_id)
        if status:
            query = query.eq('status', status)
        
        response = query.order('id', desc=False).execute()
        return api_response(data=response.data, message='Presensi retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/presensi', methods=['POST'])
def create_presensi():
    """Create new presensi"""
    try:
        data = request.get_json()
        
        required_fields = ['matkul_id', 'dosen_id', 'mahasiswa_id']
        if not all(field in data for field in required_fields):
            return api_response(
                message='Matkul_id, dosen_id, dan mahasiswa_id harus diisi',
                status_code=400,
                error=True
            )
        
        response = supabase.table('presensi').insert(data).execute()
        return api_response(
            data=response.data[0] if response.data else None,
            message='Presensi created',
            status_code=201
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/presensi/<int:presensi_id>', methods=['PUT'])
def update_presensi(presensi_id):
    """Update presensi by ID"""
    try:
        data = request.get_json()
        response = supabase.table('presensi').update(data).eq('id', presensi_id).execute()
        
        return api_response(
            data=response.data[0] if response.data else None,
            message='Presensi updated'
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== PEMBAYARAN ENDPOINTS ====================

@api.route('/pembayaran', methods=['GET'])
def get_pembayaran():
    """Get all pembayaran dengan optional filter"""
    try:
        mahasiswa_id = request.args.get('mahasiswa_id')
        status = request.args.get('status')
        
        query = supabase.table('pembayaran').select('*')
        
        if mahasiswa_id:
            query = query.eq('mahasiswa_id', mahasiswa_id)
        if status:
            query = query.eq('status', status)
        
        response = query.order('id', desc=False).execute()
        return api_response(data=response.data, message='Pembayaran retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/pembayaran/<int:pembayaran_id>', methods=['GET'])
def get_pembayaran_detail(pembayaran_id):
    """Get pembayaran by ID"""
    try:
        response = supabase.table('pembayaran').select('*').eq('id', pembayaran_id).limit(1).execute()
        pembayaran = response.data[0] if response.data else None
        
        if not pembayaran:
            return api_response(message='Pembayaran tidak ditemukan', status_code=404, error=True)
        
        return api_response(data=pembayaran, message='Pembayaran retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/pembayaran', methods=['POST'])
def create_pembayaran():
    """Create new pembayaran"""
    try:
        data = request.get_json()
        
        required_fields = ['mahasiswa_id', 'jumlah', 'bulan']
        if not all(field in data for field in required_fields):
            return api_response(
                message='Mahasiswa_id, jumlah, dan bulan harus diisi',
                status_code=400,
                error=True
            )
        
        response = supabase.table('pembayaran').insert(data).execute()
        return api_response(
            data=response.data[0] if response.data else None,
            message='Pembayaran created',
            status_code=201
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/pembayaran/<int:pembayaran_id>', methods=['PUT'])
def update_pembayaran(pembayaran_id):
    """Update pembayaran by ID"""
    try:
        data = request.get_json()
        response = supabase.table('pembayaran').update(data).eq('id', pembayaran_id).execute()
        
        return api_response(
            data=response.data[0] if response.data else None,
            message='Pembayaran updated'
        )
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== PRODI ENDPOINTS ====================

@api.route('/prodi', methods=['GET'])
def get_prodi():
    """Get all prodi"""
    try:
        response = supabase.table('prodi').select('*').order('id', desc=False).execute()
        return api_response(data=response.data, message='Prodi retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== MATKUL ENDPOINTS ====================

@api.route('/matkul', methods=['GET'])
def get_matkul():
    """Get all matkul"""
    try:
        response = supabase.table('matkul').select('*').order('id', desc=False).execute()
        return api_response(data=response.data, message='Matkul retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


@api.route('/matkul/<int:matkul_id>', methods=['GET'])
def get_matkul_detail(matkul_id):
    """Get matkul by ID"""
    try:
        response = supabase.table('matkul').select('*').eq('id', matkul_id).limit(1).execute()
        matkul = response.data[0] if response.data else None
        
        if not matkul:
            return api_response(message='Matkul tidak ditemukan', status_code=404, error=True)
        
        return api_response(data=matkul, message='Matkul retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


# ==================== STATISTICS ENDPOINTS ====================

@api.route('/stats/dashboard', methods=['GET'])
def get_dashboard_stats():
    """Get dashboard statistics"""
    try:
        admin_count = count_from_table('users', {'role': 'admin'})
        dosen_count = count_from_table('users', {'role': 'dosen'})
        mahasiswa_count = count_from_table('users', {'role': 'mahasiswa'})
        materi_count = count_from_table('materi')
        tugas_count = count_from_table('tugas')
        pembayaran_pending = count_from_table('pembayaran', {'status': 'pending'})
        
        return api_response(data={
            'admin_count': admin_count,
            'dosen_count': dosen_count,
            'mahasiswa_count': mahasiswa_count,
            'materi_count': materi_count,
            'tugas_count': tugas_count,
            'pembayaran_pending': pembayaran_pending
        }, message='Dashboard stats retrieved')
    except Exception as e:
        return api_response(message=f'Error: {str(e)}', status_code=500, error=True)


def count_from_table(table: str, filters: dict = None):
    """Helper function to count records in a table"""
    try:
        query = supabase.table(table).select('id', count='exact')
        if filters:
            for key, value in filters.items():
                query = query.eq(key, value)
        
        response = query.execute()
        if hasattr(response, 'count') and response.count is not None:
            return response.count
        return len(response.data) if response.data else 0
    except:
        return 0


# ==================== ERROR HANDLERS ====================

@api.errorhandler(404)
def not_found(error):
    return api_response(message='Endpoint not found', status_code=404, error=True)


@api.errorhandler(500)
def internal_error(error):
    return api_response(message='Internal server error', status_code=500, error=True)
