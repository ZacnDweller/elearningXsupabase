#!/usr/bin/env python3
"""
E-Learning System - Setup Verification Script
Checks if all dependencies and configurations are correct
"""

import os
import sys
from pathlib import Path

def check_python_version():
    """Check if Python version is 3.7 or higher"""
    version = sys.version_info
    if version.major >= 3 and version.minor >= 7:
        print("✅ Python version: {}.{}.{}".format(version.major, version.minor, version.micro))
        return True
    else:
        print("❌ Python version must be 3.7 or higher")
        return False

def check_file_exists(filepath):
    """Check if file exists"""
    if os.path.exists(filepath):
        print(f"✅ Found: {filepath}")
        return True
    else:
        print(f"❌ Missing: {filepath}")
        return False

def check_directory_exists(dirpath):
    """Check if directory exists"""
    if os.path.isdir(dirpath):
        print(f"✅ Found directory: {dirpath}")
        return True
    else:
        print(f"❌ Missing directory: {dirpath}")
        return False

def check_required_packages():
    """Check if required Python packages are installed"""
    packages = {
        'flask': 'Flask',
        'flask_cors': 'Flask-CORS',
        'dotenv': 'python-dotenv',
        'supabase': 'supabase',
        'requests': 'requests'
    }
    
    all_installed = True
    for package, name in packages.items():
        try:
            __import__(package)
            print(f"✅ {name} is installed")
        except ImportError:
            print(f"❌ {name} is NOT installed")
            all_installed = False
    
    return all_installed

def check_env_file():
    """Check if .env file exists and has required keys"""
    if not os.path.exists('.env'):
        print("❌ .env file not found")
        print("   Please create .env file from .env.example")
        return False
    
    required_keys = ['SUPABASE_URL', 'SUPABASE_KEY', 'FLASK_SECRET_KEY']
    with open('.env', 'r') as f:
        content = f.read()
    
    all_found = True
    for key in required_keys:
        if key in content:
            print(f"✅ {key} found in .env")
        else:
            print(f"❌ {key} NOT found in .env")
            all_found = False
    
    return all_found

def check_gui_files():
    """Check if GUI files exist"""
    files = [
        'static/index.html',
        'static/css/dashboard.css',
        'static/js/app.js',
        'static/js/api-client.js',
        'static/js/utils.js',
        'static/js/modules/dashboard.js',
        'static/js/modules/users.js',
        'static/js/modules/materi.js',
        'static/js/modules/tugas.js',
        'static/js/modules/pengumpulan.js',
        'static/js/modules/presensi.js',
        'static/js/modules/pembayaran.js',
    ]
    
    all_found = True
    for file in files:
        if not check_file_exists(file):
            all_found = False
    
    return all_found

def check_api_file():
    """Check if API file exists"""
    return check_file_exists('api.py')

def main():
    print("=" * 60)
    print("E-Learning System - Setup Verification")
    print("=" * 60)
    print()
    
    # Get current directory
    current_dir = os.getcwd()
    print(f"Working directory: {current_dir}")
    print()
    
    # Check Python version
    print("1. Checking Python version...")
    python_ok = check_python_version()
    print()
    
    # Check required packages
    print("2. Checking required packages...")
    packages_ok = check_required_packages()
    print()
    
    # Change to python_app directory if needed
    if not os.path.exists('app.py'):
        if os.path.exists('python_app'):
            os.chdir('python_app')
            print(f"Changed directory to: {os.getcwd()}")
        else:
            print("❌ Cannot find python_app directory")
            return False
    
    print()
    
    # Check files
    print("3. Checking required files...")
    app_ok = check_file_exists('app.py')
    api_ok = check_api_file()
    req_ok = check_file_exists('requirements.txt')
    env_ok = check_env_file()
    print()
    
    # Check GUI files
    print("4. Checking GUI files...")
    gui_ok = check_gui_files()
    print()
    
    # Check directories
    print("5. Checking directories...")
    static_ok = check_directory_exists('static')
    js_ok = check_directory_exists('static/js')
    modules_ok = check_directory_exists('static/js/modules')
    css_ok = check_directory_exists('static/css')
    print()
    
    # Summary
    print("=" * 60)
    print("VERIFICATION SUMMARY")
    print("=" * 60)
    
    all_ok = all([
        python_ok, packages_ok, app_ok, api_ok, req_ok, 
        env_ok, gui_ok, static_ok, js_ok, modules_ok, css_ok
    ])
    
    if all_ok:
        print("✅ All checks passed! System is ready to run.")
        print()
        print("Next steps:")
        print("1. Make sure .env file has correct Supabase credentials")
        print("2. Run: python app.py")
        print("3. Open browser: http://localhost:5000")
        return True
    else:
        print("❌ Some checks failed. Please fix the issues above.")
        print()
        print("Common fixes:")
        print("- Install missing packages: pip install -r requirements.txt")
        print("- Create .env file: cp .env.example .env")
        print("- Update .env with correct Supabase credentials")
        return False

if __name__ == '__main__':
    success = main()
    sys.exit(0 if success else 1)
