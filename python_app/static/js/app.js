/**
 * Main Application Initialization
 */

class App {
    constructor() {
        this.init();
    }

    init() {
        // Check if user is logged in
        if (!api.isAuthenticated()) {
            this.showLoginPage();
        } else {
            this.initDashboard();
            this.setupRoutes();
            router.init();
        }
    }

    showLoginPage() {
        document.body.innerHTML = `
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white text-center">
                                <h3>E-Learning</h3>
                                <p>Platform Pembelajaran Online</p>
                            </div>
                            <div class="card-body">
                                <form id="loginForm">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Login</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('loginForm').addEventListener('submit', (e) => this.handleLogin(e));
    }

    async handleLogin(e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        try {
            const response = await api.login(username, password);

            if (response.success) {
                UIUtils.showAlert('Login berhasil!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    }

    initDashboard() {
        this.setupHeader();
        this.setupSidebar();
        this.setupLogout();
    }

    setupHeader() {
        const user = api.getCurrentUser();
        const userNameDisplay = document.getElementById('userNameDisplay');
        if (userNameDisplay) {
            userNameDisplay.textContent = user?.nama || 'User';
        }
    }

    setupSidebar() {
        const role = api.getUserRole();
        
        // Sembunyikan menu sesuai role
        if (role !== 'admin') {
            document.getElementById('nav-users').style.display = 'none';
        }

        if (role === 'mahasiswa') {
            document.getElementById('nav-pembayaran').style.display = 'block';
        } else if (role === 'dosen') {
            document.getElementById('nav-pengumpulan').style.display = 'block';
        }
    }

    setupLogout() {
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            api.logout();
            location.reload();
        });
    }

    setupRoutes() {
        const role = api.getUserRole();

        router.register('/dashboard', () => this.loadDashboard());
        router.register('/users', () => {
            if (role === 'admin') {
                this.loadUsers();
            } else {
                UIUtils.showAlert('Anda tidak memiliki akses ke halaman ini', 'danger');
                router.navigate('/dashboard');
            }
        });
        router.register('/materi', () => this.loadMateri());
        router.register('/tugas', () => this.loadTugas());
        router.register('/pengumpulan', () => {
            if (role === 'dosen' || role === 'admin') {
                this.loadPengumpulan();
            } else {
                router.navigate('/dashboard');
            }
        });
        router.register('/presensi', () => this.loadPresensi());
        router.register('/pembayaran', () => {
            if (role === 'mahasiswa' || role === 'admin') {
                this.loadPembayaran();
            } else {
                router.navigate('/dashboard');
            }
        });

        router.register('/', () => this.loadDashboard());
    }

    async loadDashboard() {
        document.getElementById('pageTitle').textContent = 'Dashboard';
        document.getElementById('contentArea').innerHTML = await DashboardModule.render();
        DashboardModule.init();
    }

    async loadUsers() {
        document.getElementById('pageTitle').textContent = 'Users';
        document.getElementById('contentArea').innerHTML = await UsersModule.render();
        UsersModule.init();
    }

    async loadMateri() {
        document.getElementById('pageTitle').textContent = 'Materi';
        document.getElementById('contentArea').innerHTML = await MateriModule.render();
        MateriModule.init();
    }

    async loadTugas() {
        document.getElementById('pageTitle').textContent = 'Tugas';
        document.getElementById('contentArea').innerHTML = await TugasModule.render();
        TugasModule.init();
    }

    async loadPengumpulan() {
        document.getElementById('pageTitle').textContent = 'Pengumpulan Tugas';
        document.getElementById('contentArea').innerHTML = await PengumpulanModule.render();
        PengumpulanModule.init();
    }

    async loadPresensi() {
        document.getElementById('pageTitle').textContent = 'Presensi';
        document.getElementById('contentArea').innerHTML = await PresensiModule.render();
        PresensiModule.init();
    }

    async loadPembayaran() {
        document.getElementById('pageTitle').textContent = 'Pembayaran';
        document.getElementById('contentArea').innerHTML = await PembayaranModule.render();
        PembayaranModule.init();
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new App();
});
