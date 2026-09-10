import { defineStore } from 'pinia';
import api from '../utils/api';
export const useAdminAuth = defineStore('adminAuth', {
  state: () => ({ admin: null, token: localStorage.getItem('admin_token'), ready: false }),
  getters: { loggedIn: (s) => Boolean(s.token), canManageStudents: (s) => ['super_admin','admissions_staff'].includes(s.admin?.role), canManageExam: (s) => ['super_admin','exam_manager'].includes(s.admin?.role), isSuper: (s) => s.admin?.role === 'super_admin' },
  actions: {
    async googleLogin(googleToken) { const { data } = await api.post('/admin/auth/google-login', { google_token: googleToken }); this.admin = data.admin; this.token = data.token; localStorage.setItem('admin_token', data.token); },
    async initialize() { if (!this.token) { this.ready = true; return; } try { const { data } = await api.get('/admin/auth/profile'); this.admin = data.admin; } catch (_) { this.token = null; localStorage.removeItem('admin_token'); } finally { this.ready = true; } },
    async logout() { try { await api.post('/admin/auth/logout'); } finally { this.admin = null; this.token = null; localStorage.removeItem('admin_token'); } },
  },
});
