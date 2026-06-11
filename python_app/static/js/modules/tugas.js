/**
 * Tugas Module
 */

const TugasModule = {
    async render() {
        return `
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-clipboard-check"></i> Tugas</h5>
                        <button class="btn btn-primary btn-sm" id="addTugasBtn">
                            <i class="bi bi-plus"></i> Tambah Tugas
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="tugasLoading" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        <table class="table" id="tugasTable" style="display:none;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Judul</th>
                                    <th>Deskripsi</th>
                                    <th>Deadline</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tugasTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Tugas Modal -->
            <div class="modal fade" id="tugasModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add/Edit Tugas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="tugasForm">
                                <input type="hidden" id="tugasId">
                                <div class="mb-3">
                                    <label for="tugasJudul" class="form-label">Judul</label>
                                    <input type="text" class="form-control" id="tugasJudul" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tugasDeskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="tugasDeskripsi" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="tugasDeadline" class="form-label">Deadline</label>
                                    <input type="datetime-local" class="form-control" id="tugasDeadline" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    async init() {
        this.loadTugas();
        const role = api.getUserRole();
        if (role === 'dosen' || role === 'admin') {
            document.getElementById('addTugasBtn').addEventListener('click', () => this.showTugasModal());
            document.getElementById('tugasForm').addEventListener('submit', (e) => this.handleTugasForm(e));
        } else {
            document.getElementById('addTugasBtn').style.display = 'none';
        }
    },

    async loadTugas() {
        try {
            const user = api.getCurrentUser();
            const response = await api.getTugas(user.matkul_id);
            
            if (response.success) {
                const tbody = document.getElementById('tugasTableBody');
                const role = api.getUserRole();
                
                tbody.innerHTML = response.data.map(tugas => {
                    let actionButtons = '';
                    if (role === 'dosen' || role === 'admin') {
                        actionButtons = `
                            <div class="action-buttons">
                                <button class="btn btn-warning btn-sm" onclick="TugasModule.editTugas(${tugas.id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="TugasModule.deleteTugas(${tugas.id})">Hapus</button>
                            </div>
                        `;
                    }
                    
                    return `
                        <tr>
                            <td>${tugas.id}</td>
                            <td>${tugas.judul}</td>
                            <td>${tugas.deskripsi ? tugas.deskripsi.substring(0, 50) + '...' : '-'}</td>
                            <td>${UIUtils.formatDate(tugas.deadline)}</td>
                            <td>${actionButtons}</td>
                        </tr>
                    `;
                }).join('');

                document.getElementById('tugasLoading').style.display = 'none';
                document.getElementById('tugasTable').style.display = 'table';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    showTugasModal(tugasId = null) {
        const modal = new bootstrap.Modal(document.getElementById('tugasModal'));
        
        if (tugasId) {
            this.editTugas(tugasId);
        } else {
            document.getElementById('tugasForm').reset();
            document.getElementById('tugasId').value = '';
        }
        
        modal.show();
    },

    async editTugas(tugasId) {
        try {
            const response = await api.getTugasDetail(tugasId);
            if (response.success) {
                const tugas = response.data;
                document.getElementById('tugasId').value = tugas.id;
                document.getElementById('tugasJudul').value = tugas.judul;
                document.getElementById('tugasDeskripsi').value = tugas.deskripsi;
                document.getElementById('tugasDeadline').value = tugas.deadline?.slice(0, 16) || '';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async handleTugasForm(e) {
        e.preventDefault();

        const tugasId = document.getElementById('tugasId').value;
        const user = api.getCurrentUser();
        const data = {
            judul: document.getElementById('tugasJudul').value,
            deskripsi: document.getElementById('tugasDeskripsi').value,
            deadline: document.getElementById('tugasDeadline').value,
            matkul_id: user.matkul_id,
            dosen_id: user.id
        };

        try {
            let response;
            if (tugasId) {
                response = await api.updateTugas(tugasId, data);
            } else {
                response = await api.createTugas(data);
            }

            if (response.success) {
                UIUtils.showAlert('Tugas berhasil disimpan', 'success');
                bootstrap.Modal.getInstance(document.getElementById('tugasModal')).hide();
                this.loadTugas();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async deleteTugas(tugasId) {
        const confirmed = await UIUtils.confirm('Apakah Anda yakin ingin menghapus tugas ini?');
        if (!confirmed) return;

        try {
            const response = await api.deleteTugas(tugasId);
            if (response.success) {
                UIUtils.showAlert('Tugas berhasil dihapus', 'success');
                this.loadTugas();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    }
};
