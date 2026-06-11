/**
 * Pembayaran Module
 */

const PembayaranModule = {
    async render() {
        return `
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-credit-card"></i> Pembayaran</h5>
                        <button class="btn btn-primary btn-sm" id="addPembayaranBtn">
                            <i class="bi bi-plus"></i> Lapor Pembayaran
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="pembayaranLoading" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        <table class="table" id="pembayaranTable" style="display:none;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Bulan</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="pembayaranTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Pembayaran Modal -->
            <div class="modal fade" id="pembayaranModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Lapor Pembayaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="pembayaranForm">
                                <input type="hidden" id="pembayaranId">
                                <div class="mb-3">
                                    <label for="pembayaranBulan" class="form-label">Bulan</label>
                                    <select class="form-control" id="pembayaranBulan" required>
                                        <option value="">Pilih Bulan</option>
                                        <option value="Januari">Januari</option>
                                        <option value="Februari">Februari</option>
                                        <option value="Maret">Maret</option>
                                        <option value="April">April</option>
                                        <option value="Mei">Mei</option>
                                        <option value="Juni">Juni</option>
                                        <option value="Juli">Juli</option>
                                        <option value="Agustus">Agustus</option>
                                        <option value="September">September</option>
                                        <option value="Oktober">Oktober</option>
                                        <option value="November">November</option>
                                        <option value="Desember">Desember</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="pembayaranJumlah" class="form-label">Jumlah (Rp)</label>
                                    <input type="number" class="form-control" id="pembayaranJumlah" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pembayaranBukti" class="form-label">Bukti Pembayaran (URL)</label>
                                    <input type="url" class="form-control" id="pembayaranBukti" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Lapor Pembayaran</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    async init() {
        this.loadPembayaran();
        const role = api.getUserRole();
        
        if (role === 'mahasiswa') {
            document.getElementById('addPembayaranBtn').addEventListener('click', () => this.showPembayaranModal());
        } else if (role === 'admin') {
            document.getElementById('addPembayaranBtn').textContent = 'Filter';
        }
        
        document.getElementById('pembayaranForm').addEventListener('submit', (e) => this.handlePembayaranForm(e));
    },

    async loadPembayaran() {
        try {
            const user = api.getCurrentUser();
            let response;
            
            if (user.role === 'mahasiswa') {
                response = await api.getPembayaran(user.id);
            } else if (user.role === 'admin') {
                response = await api.getPembayaran();
            }
            
            if (response.success) {
                const tbody = document.getElementById('pembayaranTableBody');
                
                tbody.innerHTML = response.data.map(pembayaran => {
                    let statusBadge = '';
                    if (pembayaran.status === 'pending') {
                        statusBadge = '<span class="badge badge-warning">Pending</span>';
                    } else if (pembayaran.status === 'approved') {
                        statusBadge = '<span class="badge badge-success">Disetujui</span>';
                    } else {
                        statusBadge = '<span class="badge badge-danger">Ditolak</span>';
                    }
                    
                    return `
                        <tr>
                            <td>${pembayaran.id}</td>
                            <td>${pembayaran.bulan}</td>
                            <td>${UIUtils.formatCurrency(pembayaran.jumlah)}</td>
                            <td>${UIUtils.formatDate(pembayaran.created_at)}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="PembayaranModule.viewPembayaran(${pembayaran.id})">
                                    <i class="bi bi-eye"></i> Lihat
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

                document.getElementById('pembayaranLoading').style.display = 'none';
                document.getElementById('pembayaranTable').style.display = 'table';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    showPembayaranModal(pembayaranId = null) {
        const modal = new bootstrap.Modal(document.getElementById('pembayaranModal'));
        
        if (pembayaranId) {
            this.editPembayaran(pembayaranId);
        } else {
            document.getElementById('pembayaranForm').reset();
            document.getElementById('pembayaranId').value = '';
        }
        
        modal.show();
    },

    async editPembayaran(pembayaranId) {
        try {
            const response = await api.getPembayaranDetail(pembayaranId);
            if (response.success) {
                const pembayaran = response.data;
                document.getElementById('pembayaranId').value = pembayaran.id;
                document.getElementById('pembayaranBulan').value = pembayaran.bulan;
                document.getElementById('pembayaranJumlah').value = pembayaran.jumlah;
                document.getElementById('pembayaranBukti').value = pembayaran.bukti || '';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async handlePembayaranForm(e) {
        e.preventDefault();

        const pembayaranId = document.getElementById('pembayaranId').value;
        const user = api.getCurrentUser();
        const data = {
            bulan: document.getElementById('pembayaranBulan').value,
            jumlah: parseInt(document.getElementById('pembayaranJumlah').value),
            bukti: document.getElementById('pembayaranBukti').value,
            mahasiswa_id: user.id,
            status: 'pending'
        };

        try {
            let response;
            if (pembayaranId) {
                response = await api.updatePembayaran(pembayaranId, data);
            } else {
                response = await api.createPembayaran(data);
            }

            if (response.success) {
                UIUtils.showAlert('Pembayaran berhasil dilaporkan', 'success');
                bootstrap.Modal.getInstance(document.getElementById('pembayaranModal')).hide();
                this.loadPembayaran();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async viewPembayaran(pembayaranId) {
        try {
            const response = await api.getPembayaranDetail(pembayaranId);
            if (response.success) {
                const p = response.data;
                UIUtils.showModal(
                    `Detail Pembayaran #${p.id}`,
                    `
                    <p><strong>Bulan:</strong> ${p.bulan}</p>
                    <p><strong>Jumlah:</strong> ${UIUtils.formatCurrency(p.jumlah)}</p>
                    <p><strong>Status:</strong> ${p.status}</p>
                    <p><strong>Tanggal:</strong> ${UIUtils.formatDate(p.created_at)}</p>
                    <p><strong>Bukti:</strong> <a href="${p.bukti}" target="_blank">Lihat Bukti</a></p>
                    `,
                    'info'
                );
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    }
};
