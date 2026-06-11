/**
 * Users Management Module
 */

const UsersModule = {
    async render() {
        return `
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-people"></i> Manajemen Users</h5>
                        <button class="btn btn-primary btn-sm" id="addUserBtn">
                            <i class="bi bi-plus"></i> Tambah User
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="usersLoading" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        <table class="table" id="usersTable" style="display:none;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add/Edit User Modal -->
            <div class="modal fade" id="userModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add/Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="userForm">
                                <input type="hidden" id="userId">
                                <div class="mb-3">
                                    <label for="userNama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" id="userNama" required>
                                </div>
                                <div class="mb-3">
                                    <label for="userUsername" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="userUsername" required>
                                </div>
                                <div class="mb-3">
                                    <label for="userEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="userEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="userPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="userPassword">
                                </div>
                                <div class="mb-3">
                                    <label for="userRole" class="form-label">Role</label>
                                    <select class="form-control" id="userRole" required>
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="dosen">Dosen</option>
                                        <option value="mahasiswa">Mahasiswa</option>
                                    </select>
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
        this.loadUsers();
        document.getElementById('addUserBtn').addEventListener('click', () => this.showUserModal());
        document.getElementById('userForm').addEventListener('submit', (e) => this.handleUserForm(e));
    },

    async loadUsers() {
        try {
            const response = await api.getUsers();
            if (response.success) {
                const tbody = document.getElementById('usersTableBody');
                tbody.innerHTML = response.data.map(user => `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.nama}</td>
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td><span class="badge badge-primary">${user.role}</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-warning btn-sm" onclick="UsersModule.editUser(${user.id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="UsersModule.deleteUser(${user.id})">Hapus</button>
                            </div>
                        </td>
                    </tr>
                `).join('');

                document.getElementById('usersLoading').style.display = 'none';
                document.getElementById('usersTable').style.display = 'table';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    showUserModal(userId = null) {
        const modal = new bootstrap.Modal(document.getElementById('userModal'));
        
        if (userId) {
            this.editUser(userId);
        } else {
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('userPassword').required = true;
        }
        
        modal.show();
    },

    async editUser(userId) {
        try {
            const response = await api.getUser(userId);
            if (response.success) {
                const user = response.data;
                document.getElementById('userId').value = user.id;
                document.getElementById('userNama').value = user.nama;
                document.getElementById('userUsername').value = user.username;
                document.getElementById('userEmail').value = user.email;
                document.getElementById('userRole').value = user.role;
                document.getElementById('userPassword').required = false;
                document.getElementById('userPassword').value = '';
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async handleUserForm(e) {
        e.preventDefault();

        const userId = document.getElementById('userId').value;
        const data = {
            nama: document.getElementById('userNama').value,
            username: document.getElementById('userUsername').value,
            email: document.getElementById('userEmail').value,
            role: document.getElementById('userRole').value
        };

        if (document.getElementById('userPassword').value) {
            data.password = document.getElementById('userPassword').value;
        }

        try {
            let response;
            if (userId) {
                response = await api.updateUser(userId, data);
            } else {
                data.password = document.getElementById('userPassword').value;
                response = await api.register(data);
            }

            if (response.success) {
                UIUtils.showAlert('User berhasil disimpan', 'success');
                bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                this.loadUsers();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    },

    async deleteUser(userId) {
        const confirmed = await UIUtils.confirm('Apakah Anda yakin ingin menghapus user ini?');
        if (!confirmed) return;

        try {
            const response = await api.deleteUser(userId);
            if (response.success) {
                UIUtils.showAlert('User berhasil dihapus', 'success');
                this.loadUsers();
            } else {
                UIUtils.showAlert(response.message, 'danger');
            }
        } catch (error) {
            UIUtils.showAlert('Error: ' + error.message, 'danger');
        }
    }
};
