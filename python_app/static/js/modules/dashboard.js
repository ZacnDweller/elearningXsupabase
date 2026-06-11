/**
 * Dashboard Module
 */

const DashboardModule = {
    async render() {
        const user = api.getCurrentUser();
        const role = user?.role;

        let html = `
            <div class="container-fluid">
                <div class="row">
        `;

        if (role === 'admin') {
            html += `
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <h5>Total Admin</h5>
                        <div class="stat-value" id="adminCount">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <h5>Total Dosen</h5>
                        <div class="stat-value" id="dosenCount">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <h5>Total Mahasiswa</h5>
                        <div class="stat-value" id="mahasiswaCount">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card danger">
                        <h5>Pembayaran Pending</h5>
                        <div class="stat-value" id="pembayaranCount">-</div>
                    </div>
                </div>
            `;
        } else if (role === 'dosen') {
            html += `
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <h5>Total Materi</h5>
                        <div class="stat-value" id="materiCount">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <h5>Total Tugas</h5>
                        <div class="stat-value" id="tugasCount">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info">
                        <h5>Presensi Terbuka</h5>
                        <div class="stat-value" id="presensiStatus">-</div>
                    </div>
                </div>
            `;
        } else if (role === 'mahasiswa') {
            html += `
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <h5>Materi Tersedia</h5>
                        <div class="stat-value" id="materiCount">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <h5>Tugas Aktif</h5>
                        <div class="stat-value" id="tugasCount">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <h5>Status Presensi</h5>
                        <div class="stat-value" id="presensiStatus">-</div>
                    </div>
                </div>
            `;
        }

        html += `
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-info-circle"></i> Informasi Profil</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nama:</strong></p>
                                        <p class="text-muted">${user?.nama || '-'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Username:</strong></p>
                                        <p class="text-muted">${user?.username || '-'}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Role:</strong></p>
                                        <p class="text-muted"><span class="badge badge-primary">${user?.role || '-'}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong></p>
                                        <p class="text-muted">${user?.email || '-'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-calendar"></i> Informasi Sistem</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Versi Platform:</strong> <span class="text-muted">1.0.0</span></p>
                                <p><strong>Database:</strong> <span class="badge badge-success">Connected</span></p>
                                <p><strong>API Status:</strong> <span class="badge badge-success">Online</span></p>
                                <p><strong>Last Update:</strong> <span class="text-muted">${UIUtils.formatDate(new Date())}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return html;
    },

    async init() {
        try {
            const user = api.getCurrentUser();
            const role = user?.role;

            if (role === 'admin') {
                const stats = await api.getDashboardStats();
                if (stats.success) {
                    document.getElementById('adminCount').textContent = stats.data.admin_count;
                    document.getElementById('dosenCount').textContent = stats.data.dosen_count;
                    document.getElementById('mahasiswaCount').textContent = stats.data.mahasiswa_count;
                    document.getElementById('pembayaranCount').textContent = stats.data.pembayaran_pending;
                }
            } else if (role === 'dosen') {
                const materi = await api.getMateri(user.matkul_id);
                const tugas = await api.getTugas(user.matkul_id);

                if (materi.success) {
                    document.getElementById('materiCount').textContent = materi.data.length;
                }
                if (tugas.success) {
                    document.getElementById('tugasCount').textContent = tugas.data.length;
                }
                document.getElementById('presensiStatus').textContent = 'TUTUP';
            } else if (role === 'mahasiswa') {
                const materi = await api.getMateri(user.matkul_id);
                const tugas = await api.getTugas(user.matkul_id);

                if (materi.success) {
                    document.getElementById('materiCount').textContent = materi.data.length;
                }
                if (tugas.success) {
                    document.getElementById('tugasCount').textContent = tugas.data.length;
                }
                document.getElementById('presensiStatus').textContent = 'TUTUP';
            }
        } catch (error) {
            console.error('Dashboard init error:', error);
            UIUtils.showAlert('Error loading dashboard: ' + error.message, 'danger');
        }
    }
};
