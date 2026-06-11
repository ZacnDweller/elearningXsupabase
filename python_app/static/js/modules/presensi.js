/**
 * Presensi Module
 */

const PresensiModule = {
    async render() {
        return `
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-clipboard-list"></i> Presensi</h5>
                        <button class="btn btn-primary btn-sm" id="addPresensiBtn">
                            <i class="bi bi-plus"></i> Buka Presensi
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="presensiLoading" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        <table class="table" id="presensiTable" style="display:none;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mahasiswa</th>
                                    <th>Status</th>
                                    <th>Waktu Hadir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="presensiTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Presensi Modal -->
            <div class="modal fade" id="presensiModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Buka Presensi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="presensiForm">
                                <p class="alert alert-info">Presensi akan dibuka untuk semua mahasiswa dalam kelas ini.</p>
                                <button type="submit" class="btn btn-primary w-100">Buka Presensi</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    async init() {
        this.loadPresensi();
        const role = api.getUserRole();
        if (role === 'dosen' || role === 'admin') {
            document.getElementById('addPresensiBtn').addEventListener('click', () => this.showPresensiModal());
            document.getElementById('presensiForm').addEventListener('submit', (e) => this.handlePresensiForm(e));
        } else {
            document.getElementById('addPresensiBtn').style.display = 'none';
        }
    },

    async loadPresensi() {
        try {
            const user = api.getCurrentUser();
            const response = await api.getPresensi(user.matkul_id);
            
            if (response.success) {
                const tbody = document.getElementById('presensiTableBody');
                
                tbody.innerHTML = response.data.map(presensi => `
                    <tr>
                        <td>${presensi.id}</td>
                        <td>${presensi.mahasiswa_nama || 'N/A'}</td>
                        <td><span class="badge badge-success">${presensi.status || 'Hadir'}</span></td>
                        <td>${UIUtils.formatDate(presensi.created_at)}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="PresensiModule.editPresensi(${presensi.id})">Update</button>
                        </td>
                    </tr>
                `).join('');

                document.getElementById('presensiLoading').style.display = 'none';
                document.getElementById('presensiTable').style.display = 'table';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    showPresensiModal() {
        const modal = new bootstrap.Modal(document.getElementById('presensiModal'));
        modal.show();
    },

    async handlePresensiForm(e) {
        e.preventDefault();
        
        const user = api.getCurrentUser();
        const data = {
            matkul_id: user.matkul_id,
            dosen_id: user.id,
            mahasiswa_id: user.id,
            status: 'buka'
        };

        try {
            const response = await api.createPresensi(data);
            if (response.success) {
                UIUtils.showAlert('Presensi berhasil dibuka', 'success');
                bootstrap.Modal.getInstance(document.getElementById('presensiModal')).hide();
                this.loadPresensi();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async editPresensi(presensiId) {
        UIUtils.showAlert('Fitur update presensi sedang dikembangkan', 'info');
    }
};
