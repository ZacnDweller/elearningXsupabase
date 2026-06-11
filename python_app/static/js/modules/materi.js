/**
 * Materi Module
 */

const MateriModule = {
    async render() {
        return `
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-book"></i> Materi Pembelajaran</h5>
                        <button class="btn btn-primary btn-sm" id="addMateriBtn">
                            <i class="bi bi-plus"></i> Tambah Materi
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="materiLoading" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        <table class="table" id="materiTable" style="display:none;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Judul</th>
                                    <th>Deskripsi</th>
                                    <th>Tanggal Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="materiTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Materi Modal -->
            <div class="modal fade" id="materiModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add/Edit Materi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="materiForm">
                                <input type="hidden" id="materiId">
                                <div class="mb-3">
                                    <label for="materiJudul" class="form-label">Judul</label>
                                    <input type="text" class="form-control" id="materiJudul" required>
                                </div>
                                <div class="mb-3">
                                    <label for="materiDeskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="materiDeskripsi" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="materiFile" class="form-label">File (URL)</label>
                                    <input type="url" class="form-control" id="materiFile">
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
        this.loadMateri();
        document.getElementById('addMateriBtn').addEventListener('click', () => this.showMateriModal());
        document.getElementById('materiForm').addEventListener('submit', (e) => this.handleMateriForm(e));
    },

    async loadMateri() {
        try {
            const user = api.getCurrentUser();
            const response = await api.getMateri(user.matkul_id);
            
            if (response.success) {
                const tbody = document.getElementById('materiTableBody');
                tbody.innerHTML = response.data.map(materi => `
                    <tr>
                        <td>${materi.id}</td>
                        <td>${materi.judul}</td>
                        <td>${materi.deskripsi ? materi.deskripsi.substring(0, 50) + '...' : '-'}</td>
                        <td>${UIUtils.formatDate(materi.created_at)}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-warning btn-sm" onclick="MateriModule.editMateri(${materi.id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="MateriModule.deleteMateri(${materi.id})">Hapus</button>
                            </div>
                        </td>
                    </tr>
                `).join('');

                document.getElementById('materiLoading').style.display = 'none';
                document.getElementById('materiTable').style.display = 'table';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    showMateriModal(materiId = null) {
        const modal = new bootstrap.Modal(document.getElementById('materiModal'));
        
        if (materiId) {
            this.editMateri(materiId);
        } else {
            document.getElementById('materiForm').reset();
            document.getElementById('materiId').value = '';
        }
        
        modal.show();
    },

    async editMateri(materiId) {
        try {
            const response = await api.getMateriDetail(materiId);
            if (response.success) {
                const materi = response.data;
                document.getElementById('materiId').value = materi.id;
                document.getElementById('materiJudul').value = materi.judul;
                document.getElementById('materiDeskripsi').value = materi.deskripsi;
                document.getElementById('materiFile').value = materi.file || '';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async handleMateriForm(e) {
        e.preventDefault();

        const materiId = document.getElementById('materiId').value;
        const user = api.getCurrentUser();
        const data = {
            judul: document.getElementById('materiJudul').value,
            deskripsi: document.getElementById('materiDeskripsi').value,
            file: document.getElementById('materiFile').value,
            matkul_id: user.matkul_id,
            dosen_id: user.id
        };

        try {
            let response;
            if (materiId) {
                response = await api.updateMateri(materiId, data);
            } else {
                response = await api.createMateri(data);
            }

            if (response.success) {
                UIUtils.showAlert('Materi berhasil disimpan', 'success');
                bootstrap.Modal.getInstance(document.getElementById('materiModal')).hide();
                this.loadMateri();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async deleteMateri(materiId) {
        const confirmed = await UIUtils.confirm('Apakah Anda yakin ingin menghapus materi ini?');
        if (!confirmed) return;

        try {
            const response = await api.deleteMateri(materiId);
            if (response.success) {
                UIUtils.showAlert('Materi berhasil dihapus', 'success');
                this.loadMateri();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    }
};
