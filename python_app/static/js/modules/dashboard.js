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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-table"></i> Ringkasan Data</h5>
                            </div>
                            <div class="card-body">
                                <div id="dashboardTableContainer">
                                    <div class="text-center" id="dashboardLoading">
                                        <div class="spinner-border text-primary" role="status"></div>
                                    </div>
                                    <table class="table table-striped table-hover" id="dashboardTable" style="display:none;">
                                        <thead>
                                            <tr id="dashboardTableHeader"></tr>
                                        </thead>
                                        <tbody id="dashboardTableBody"></tbody>
                                    </table>
                                    <div id="dashboardEmptyState" class="text-muted" style="display:none;">Tidak ada data untuk ditampilkan.</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
            const dashboardLoading = document.getElementById('dashboardLoading');
            const dashboardTable = document.getElementById('dashboardTable');
            const dashboardTableHeader = document.getElementById('dashboardTableHeader');
            const dashboardTableBody = document.getElementById('dashboardTableBody');
            const dashboardEmptyState = document.getElementById('dashboardEmptyState');

            const hideTable = () => {
                if (dashboardLoading) dashboardLoading.style.display = 'none';
                if (dashboardTable) dashboardTable.style.display = 'none';
                if (dashboardEmptyState) dashboardEmptyState.style.display = 'none';
            };

            const showTable = (headerHtml, rowsHtml) => {
                if (!dashboardLoading || !dashboardTable || !dashboardTableHeader || !dashboardTableBody || !dashboardEmptyState) {
                    return;
                }

                dashboardTableHeader.innerHTML = headerHtml;
                dashboardTableBody.innerHTML = rowsHtml;
                dashboardLoading.style.display = 'none';

                if (rowsHtml.trim()) {
                    dashboardTable.style.display = 'table';
                    dashboardEmptyState.style.display = 'none';
                } else {
                    dashboardTable.style.display = 'none';
                    dashboardEmptyState.style.display = 'block';
                }
            };

            if (role === 'admin') {
                const stats = await api.getDashboardStats();
                const mahasiswa = await api.getMahasiswa();

                if (stats.success) {
                    document.getElementById('adminCount').textContent = stats.data.admin_count;
                    document.getElementById('dosenCount').textContent = stats.data.dosen_count;
                    document.getElementById('mahasiswaCount').textContent = stats.data.mahasiswa_count;
                    document.getElementById('pembayaranCount').textContent = stats.data.pembayaran_pending;
                }

                if (mahasiswa.success) {
                    const rows = mahasiswa.data.map(mahasiswaItem => `
                        <tr>
                            <td>${mahasiswaItem.id}</td>
                            <td>${mahasiswaItem.nama || '-'}</td>
                            <td>${mahasiswaItem.username || '-'}</td>
                            <td>${mahasiswaItem.email || '-'}</td>
                        </tr>
                    `).join('');

                    showTable(
                        '<tr><th>ID</th><th>Nama</th><th>Username</th><th>Email</th></tr>',
                        rows
                    );
                } else {
                    hideTable();
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

                if (tugas.success) {
                    const rows = tugas.data.map(tugasItem => `
                        <tr>
                            <td>${tugasItem.id}</td>
                            <td>${tugasItem.judul || '-'}</td>
                            <td>${tugasItem.deadline ? UIUtils.formatDate(new Date(tugasItem.deadline)) : '-'}</td>
                        </tr>
                    `).join('');

                    showTable('<tr><th>ID</th><th>Judul Tugas</th><th>Deadline</th></tr>', rows);
                } else {
                    hideTable();
                }
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

                if (materi.success) {
                    const rows = materi.data.map(materiItem => `
                        <tr>
                            <td>${materiItem.id}</td>
                            <td>${materiItem.judul || '-'}</td>
                            <td>${materiItem.deskripsi ? materiItem.deskripsi.substring(0, 80) + '...' : '-'}</td>
                        </tr>
                    `).join('');

                    showTable('<tr><th>ID</th><th>Judul Materi</th><th>Deskripsi</th></tr>', rows);
                } else {
                    hideTable();
                }
            } else {
                hideTable();
            }
        } catch (error) {
            console.error('Dashboard init error:', error);
            UIUtils.showAlert('Error loading dashboard: ' + error.message, 'danger');
        }
    }
};
