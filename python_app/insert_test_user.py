#!/usr/bin/env python3
"""
Script untuk insert test user ke Supabase atau fallback ke users.json
Jalankan: python insert_test_user.py
"""
import os
import sys
import json
import hashlib
from pathlib import Path
from dotenv import load_dotenv

# Load environment
env_path = Path(__file__).parent / '.env'
load_dotenv(env_path)

SUPABASE_URL = os.getenv('SUPABASE_URL')
SUPABASE_KEY = os.getenv('SUPABASE_KEY')
USERS_FILE_PATH = Path(__file__).parent / 'users.json'

supabase = None
try:
    if SUPABASE_URL and SUPABASE_KEY:
        from supabase import create_client
        supabase = create_client(SUPABASE_URL, SUPABASE_KEY)
except Exception:
    pass


def md5_hash(text):
    return hashlib.md5(text.encode('utf-8')).hexdigest()


def load_users_from_file():
    """Load existing users from JSON file."""
    if not USERS_FILE_PATH.exists():
        return []
    try:
        data = json.loads(USERS_FILE_PATH.read_text(encoding='utf-8'))
        return data if isinstance(data, list) else []
    except Exception:
        return []


def save_users_to_file(users):
    """Save users to JSON file."""
    USERS_FILE_PATH.parent.mkdir(parents=True, exist_ok=True)
    USERS_FILE_PATH.write_text(json.dumps(users, indent=2, ensure_ascii=False), encoding='utf-8')


def insert_test_users():
    """Insert 3 test user dengan role berbeda"""
    test_users = [
        {
            'nama': 'Administrator',
            'username': 'admin',
            'password': md5_hash('admin123'),
            'role': 'admin',
            'email': 'admin@test.local',
            'prodi_id': None,
            'matkul_id': None
        },
        {
            'nama': 'Dosen Pembimbing',
            'username': 'dosen',
            'password': md5_hash('dosen123'),
            'role': 'dosen',
            'email': 'dosen@test.local',
            'prodi_id': 1,
            'matkul_id': 1
        },
        {
            'nama': 'Mahasiswa Aktif',
            'username': 'mahasiswa',
            'password': md5_hash('mahasiswa123'),
            'role': 'mahasiswa',
            'email': 'mahasiswa@test.local',
            'prodi_id': 1,
            'matkul_id': 1
        }
    ]

    # Try Supabase first
    supabase_available = False
    if supabase:
        try:
            # Test connection by querying
            test_response = supabase.table('users').select('id').limit(1).execute()
            supabase_available = True
        except Exception:
            supabase_available = False
    
    if supabase_available:
        try:
            for user in test_users:
                try:
                    # Cek apakah user sudah ada
                    existing = supabase.table('users').select('id').eq('username', user['username']).execute()
                    
                    if existing.data:
                        print(f"⚠️  User '{user['username']}' sudah ada di Supabase, skip")
                        continue
                    
                    # Insert user
                    response = supabase.table('users').insert(user).execute()
                    
                    if response.data:
                        print(f"✓ User '{user['username']}' berhasil dibuat di Supabase")
                    else:
                        print(f"❌ Gagal membuat user '{user['username']}' di Supabase")
                            
                except Exception as e:
                    print(f"❌ Error membuat user '{user['username']}': {e}")
            return
        except Exception:
            pass
    
    # Fallback to users.json file
    print("⚠️  Supabase tidak tersedia, menggunakan fallback users.json\n")
    
    existing_users = load_users_from_file()
    existing_usernames = {u.get('username') for u in existing_users}
    
    for user in test_users:
        if user['username'] in existing_usernames:
            print(f"⚠️  User '{user['username']}' sudah ada di users.json, skip")
        else:
            existing_users.append(user)
            print(f"✓ User '{user['username']}' ditambahkan ke users.json")
    
    save_users_to_file(existing_users)
    print(f"\n✓ Users tersimpan di: {USERS_FILE_PATH}")





if __name__ == '__main__':
    print("Inserting test users...\n")
    insert_test_users()
    
    print("\n" + "="*50)
    print("SELESAI!")
    print("="*50)
    print("\nCredential yang bisa Anda gunakan untuk login:")
    print("  1. Admin:")
    print("     Username: admin")
    print("     Password: admin123")
    print("  2. Dosen:")
    print("     Username: dosen")
    print("     Password: dosen123")
    print("  3. Mahasiswa:")
    print("     Username: mahasiswa")
    print("     Password: mahasiswa123")

