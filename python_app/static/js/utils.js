/**
 * Utility Functions - Helper functions untuk aplikasi
 */

class UIUtils {
    /**
     * Tampilkan notifikasi alert
     */
    static showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.alert-container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    /**
     * Format tanggal
     */
    static formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID');
    }

    /**
     * Format currency
     */
    static formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Tampilkan loading spinner
     */
    static showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        }
    }

    /**
     * Hide loading spinner
     */
    static hideLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '';
        }
    }

    /**
     * Tampilkan modal dengan pesan error/success
     */
    static showModal(title, message, type = 'info') {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-${type === 'success' ? 'success' : 'danger'} text-white">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    /**
     * Confirm dialog
     */
    static confirm(message) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Konfirmasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger" id="confirmBtn">Hapus</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            document.getElementById('confirmBtn').addEventListener('click', () => {
                resolve(true);
                bsModal.hide();
            });

            modal.addEventListener('hidden.bs.modal', () => {
                resolve(false);
                modal.remove();
            });
        });
    }

    /**
     * Build table rows dari data array
     */
    static buildTableRows(data, columns, actionCallback = null) {
        return data.map(row => {
            let html = '';
            columns.forEach(col => {
                const value = row[col.field];
                if (col.render) {
                    html += `<td>${col.render(value, row)}</td>`;
                } else {
                    html += `<td>${value || '-'}</td>`;
                }
            });

            if (actionCallback) {
                html += `<td>${actionCallback(row)}</td>`;
            }

            return `<tr>${html}</tr>`;
        }).join('');
    }

    /**
     * Parse JWT token
     */
    static parseJWT(token) {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(
            atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join('')
        );
        return JSON.parse(jsonPayload);
    }
}

/**
 * Form Validator
 */
class FormValidator {
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    static validatePassword(password) {
        return password.length >= 6;
    }

    static validateUsername(username) {
        return username.length >= 3 && /^[a-zA-Z0-9_]+$/.test(username);
    }

    static validatePhone(phone) {
        return /^(\+62|0)[0-9]{9,12}$/.test(phone);
    }

    static validateForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        const inputs = form.querySelectorAll('[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        return isValid;
    }
}

/**
 * Storage Helper - Local Storage wrapper
 */
class Storage {
    static set(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    }

    static get(key) {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    }

    static remove(key) {
        localStorage.removeItem(key);
    }

    static clear() {
        localStorage.clear();
    }
}

/**
 * Router - Sederhana navigation sistem
 */
class Router {
    constructor() {
        this.routes = {};
        this.currentView = null;
    }

    register(path, handler) {
        this.routes[path] = handler;
    }

    navigate(path) {
        if (this.routes[path]) {
            this.currentView = path;
            this.routes[path]();
            window.history.pushState({ path }, '', `#${path}`);
        }
    }

    init() {
        window.addEventListener('hashchange', () => {
            const path = window.location.hash.slice(1) || '/';
            this.navigate(path);
        });

        const initialPath = window.location.hash.slice(1) || '/';
        this.navigate(initialPath);
    }
}

// Create global router instance
const router = new Router();
