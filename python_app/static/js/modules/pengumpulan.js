/**
 * Pengumpulan Module
 */

const PengumpulanModule = {
    async render() {
        return `
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-cloud-upload"></i> Pengumpulan Tugas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="filterTugas" class="form-label">Filter Tugas</label>
                                <select class="form-control" id="filterTugas">
                                    <option value="">Semua Tugas</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="pengumpulanLoading" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        
                        <table class="table" id="pengumpulanTable" style="display:none;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mahasiswa</th>
                                    <th>Tugas</th>
                                    <th>Tanggal Kumpul</th>
                                    <th>File</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="pengumpulanTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    },

    async init() {
        this.loadTugas();
        this.loadPengumpulan();
        document.getElementById('filterTugas').addEventListener('change', () => this.loadPengumpulan());
    },

    async loadTugas() {
        try {
            const user = api.getCurrentUser();
            const response = await api.getTugas(user.matkul_id);
            
            if (response.success) {
                const select = document.getElementById('filterTugas');
                response.data.forEach(tugas => {
                    const option = document.createElement('option');
                    option.value = tugas.id;
                    option.textContent = tugas.judul;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading tugas:', error);
        }
    },

    async loadPengumpulan() {
        try {
            const tugasId = document.getElementById('filterTugas').value;
            const response = await api.getPengumpulan(tugasId || null);
            
            if (response.success) {
                const tbody = document.getElementById('pengumpulanTableBody');
                
                tbody.innerHTML = response.data.map(pengumpulan => `
                    <tr>
                        <td>${pengumpulan.id}</td>
                        <td>${pengumpulan.mahasiswa_nama || 'N/A'}</td>
                        <td>${pengumpulan.tugas_judul || 'N/A'}</td>
                        <td>${UIUtils.formatDate(pengumpulan.created_at)}</td>
                        <td>
                            <a href="${pengumpulan.file}" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </td>
                        <td><span class="badge badge-success">Dikumpulkan</span></td>
                    </tr>
                `).join('');

                document.getElementById('pengumpulanLoading').style.display = 'none';
                document.getElementById('pengumpulanTable').style.display = 'table';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    }
};
