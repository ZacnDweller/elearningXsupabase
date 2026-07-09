/**
 * API Client - Menangani semua komunikasi dengan REST API
 */

const API_BASE_URL = 'http://localhost:5000/api/v1';

class APIClient {
    constructor() {
        this.token = localStorage.getItem('auth_token');
        this.user = JSON.parse(localStorage.getItem('user')) || null;
    }

    /**
     * Helper untuk mengirim request ke API
     */
    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error dari server');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ==================== AUTHENTICATION ====================

    async login(username, password) {
        const response = await this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });

        if (response.success) {
            this.token = response.data.id;
            this.user = response.data;
            localStorage.setItem('auth_token', this.token);
            localStorage.setItem('user', JSON.stringify(response.data));
        }

        return response;
    }

    async register(userData) {
        return await this.request('/auth/register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    }

    logout() {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
    }

    // ==================== USERS ====================

    async getUsers(role = null) {
        const params = role ? `?role=${role}` : '';
        return await this.request(`/users${params}`, { method: 'GET' });
    }

    async getMahasiswa(matkulId = null) {
        const params = matkulId ? `?matkul_id=${matkulId}` : '';
        return await this.request(`/mahasiswa${params}`, { method: 'GET' });
    }

    async getUser(userId) {
        return await this.request(`/users/${userId}`, { method: 'GET' });
    }

    async updateUser(userId, data) {
        return await this.request(`/users/${userId}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async deleteUser(userId) {
        return await this.request(`/users/${userId}`, { method: 'DELETE' });
    }

    // ==================== MATERI ====================

    async getMateri(matkulId = null) {
        const params = matkulId ? `?matkul_id=${matkulId}` : '';
        return await this.request(`/materi${params}`, { method: 'GET' });
    }

    async getMateriDetail(materiId) {
        return await this.request(`/materi/${materiId}`, { method: 'GET' });
    }

    async createMateri(data) {
        return await this.request('/materi', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updateMateri(materiId, data) {
        return await this.request(`/materi/${materiId}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async deleteMateri(materiId) {
        return await this.request(`/materi/${materiId}`, { method: 'DELETE' });
    }

    // ==================== TUGAS ====================

    async getTugas(matkulId = null) {
        const params = matkulId ? `?matkul_id=${matkulId}` : '';
        return await this.request(`/tugas${params}`, { method: 'GET' });
    }

    async getTugasDetail(tugasId) {
        return await this.request(`/tugas/${tugasId}`, { method: 'GET' });
    }

    async createTugas(data) {
        return await this.request('/tugas', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updateTugas(tugasId, data) {
        return await this.request(`/tugas/${tugasId}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async deleteTugas(tugasId) {
        return await this.request(`/tugas/${tugasId}`, { method: 'DELETE' });
    }

    // ==================== PENGUMPULAN ====================

    async getPengumpulan(tugasId = null, mahasiswaId = null) {
        let params = new URLSearchParams();
        if (tugasId) params.append('tugas_id', tugasId);
        if (mahasiswaId) params.append('mahasiswa_id', mahasiswaId);
        
        const queryString = params.toString() ? `?${params.toString()}` : '';
        return await this.request(`/pengumpulan${queryString}`, { method: 'GET' });
    }

    async createPengumpulan(data) {
        return await this.request('/pengumpulan', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    // ==================== PRESENSI ====================

    async getPresensi(matkulId = null, mahasiswaId = null, status = null) {
        let params = new URLSearchParams();
        if (matkulId) params.append('matkul_id', matkulId);
        if (mahasiswaId) params.append('mahasiswa_id', mahasiswaId);
        if (status) params.append('status', status);
        
        const queryString = params.toString() ? `?${params.toString()}` : '';
        return await this.request(`/presensi${queryString}`, { method: 'GET' });
    }

    async createPresensi(data) {
        return await this.request('/presensi', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updatePresensi(presensiId, data) {
        return await this.request(`/presensi/${presensiId}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    // ==================== PEMBAYARAN ====================

    async getPembayaran(mahasiswaId = null, status = null) {
        let params = new URLSearchParams();
        if (mahasiswaId) params.append('mahasiswa_id', mahasiswaId);
        if (status) params.append('status', status);
        
        const queryString = params.toString() ? `?${params.toString()}` : '';
        return await this.request(`/pembayaran${queryString}`, { method: 'GET' });
    }

    async getPembayaranDetail(pembayaranId) {
        return await this.request(`/pembayaran/${pembayaranId}`, { method: 'GET' });
    }

    async createPembayaran(data) {
        return await this.request('/pembayaran', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updatePembayaran(pembayaranId, data) {
        return await this.request(`/pembayaran/${pembayaranId}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    // ==================== PRODI ====================

    async getProdi() {
        return await this.request('/prodi', { method: 'GET' });
    }

    // ==================== MATKUL ====================

    async getMatkul() {
        return await this.request('/matkul', { method: 'GET' });
    }

    async getMatkulDetail(matkulId) {
        return await this.request(`/matkul/${matkulId}`, { method: 'GET' });
    }

    // ==================== STATISTICS ====================

    async getDashboardStats() {
        return await this.request('/stats/dashboard', { method: 'GET' });
    }

    // ==================== HELPER METHODS ====================

    isAuthenticated() {
        return !!this.token && !!this.user;
    }

    getCurrentUser() {
        return this.user;
    }

    getUserRole() {
        return this.user?.role;
    }
}

// Create global API client instance
const api = new APIClient();
