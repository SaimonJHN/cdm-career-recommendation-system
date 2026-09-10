import { defineStore } from 'pinia';
import api from '../utils/api.js';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false,
    studentNumber: null,
    admissionYear: null,
    initialized: false,
  }),

  getters: {
    getUser: (state) => state.user,
    getToken: (state) => state.token,
    isLoggedIn: (state) => state.isAuthenticated,
    getStudentNumber: (state) => state.studentNumber,
    isInitialized: (state) => state.initialized,
  },

  actions: {
    async register(userData) {
      try {
        const response = await api.post('/auth/register', userData);
        if (!response.data?.otp_required || !response.data?.challenge_id) {
          throw new Error('Email verification could not be started. Please try again.');
        }

        return response.data;
      } catch (error) {
        let errorMessage = error.response?.data?.message || error.message || 'Registration failed';
        const errors = error.response?.data?.errors;
        if (errors && typeof errors === 'object') {
          errorMessage = Object.values(errors).flat().join(' ');
        }
        throw new Error(errorMessage);
      }
    },

    async login(credentials) {
      try {
        const response = await api.post('/auth/login', {
          ...credentials,
          trusted_device_token: localStorage.getItem('trusted_device_token'),
        });
        if (response.data?.token && response.data?.student) {
          this.setAuthenticatedUser(response.data);
          return response.data;
        }
        if (!response.data?.otp_required || !response.data?.challenge_id) {
          throw new Error('Login verification could not be started. Please try again.');
        }

        return response.data;
      } catch (error) {
        let errorMessage = error.response?.data?.message || error.message || 'Login failed';
        const errors = error.response?.data?.errors;
        if (errors && typeof errors === 'object') {
          errorMessage = Object.values(errors).flat().join(' ');
        }
        throw new Error(errorMessage);
      }
    },

    async verifyOtp(challengeId, otp) {
      try {
        const response = await api.post('/auth/otp/verify', {
          challenge_id: challengeId,
          otp,
        });
        this.setAuthenticatedUser(response.data);
        return response.data;
      } catch (error) {
        let errorMessage = error.response?.data?.message || error.message || 'Verification failed';
        const errors = error.response?.data?.errors;
        if (errors && typeof errors === 'object') {
          errorMessage = Object.values(errors).flat().join(' ');
        }
        throw new Error(errorMessage);
      }
    },

    async resendOtp(challengeId) {
      try {
        const response = await api.post('/auth/otp/resend', {
          challenge_id: challengeId,
        });
        return response.data;
      } catch (error) {
        let errorMessage = error.response?.data?.message || error.message || 'Unable to resend the verification code';
        const errors = error.response?.data?.errors;
        if (errors && typeof errors === 'object') {
          errorMessage = Object.values(errors).flat().join(' ');
        }
        throw new Error(errorMessage);
      }
    },

    setAuthenticatedUser(data) {
      this.user = data.student;
      this.token = data.token;
      this.studentNumber = data.student_number || data.student?.student_number || null;
      this.admissionYear = data.admission_year || data.student?.admission_year || null;
      this.isAuthenticated = true;
      localStorage.setItem('auth_token', this.token);
      if (data.trusted_device_token) {
        localStorage.setItem('trusted_device_token', data.trusted_device_token);
      }
    },

    updateUser(student) {
      this.user = { ...(this.user || {}), ...student };
      this.studentNumber = this.user.student_number || this.studentNumber;
      this.admissionYear = this.user.admission_year || this.admissionYear;
    },

    async googleLogin(googleToken) {
      try {
        const response = await api.post('/auth/google-login', {
          google_token: googleToken,
        });
        this.setAuthenticatedUser(response.data);
        return response.data;
      } catch (error) {
        let errorMessage = error.response?.data?.message || error.message || 'Google login failed';
        const errors = error.response?.data?.errors;
        if (errors && typeof errors === 'object') {
          errorMessage = Object.values(errors).flat().join(' ');
        }
        throw new Error(errorMessage);
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout', {});
      } catch (error) {
        // continue cleanup even if logout request fails
      } finally {
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        this.studentNumber = null;
        this.admissionYear = null;
        localStorage.removeItem('auth_token');
      }
    },

    async getProfile() {
      try {
        const response = await api.get('/auth/profile');
        if (response.data && response.data.student) {
          this.user = response.data.student;
          this.studentNumber = response.data.student.student_number || this.studentNumber;
          this.admissionYear = response.data.student.admission_year || this.admissionYear;
        }
        return response.data;
      } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || 'Failed to fetch profile';
        throw new Error(errorMessage);
      }
    },

    loadTokenFromStorage() {
      const token = localStorage.getItem('auth_token');
      if (token) {
        this.token = token;
        this.isAuthenticated = true;
      }
    },

    async initializeAuth() {
      const token = localStorage.getItem('auth_token');
      if (!token) {
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        this.studentNumber = null;
        this.admissionYear = null;
        this.initialized = true;
        return;
      }

      this.token = token;
      this.isAuthenticated = true;

      try {
        await this.getProfile();
      } catch (error) {
        console.error('Failed to restore session:', error);
        localStorage.removeItem('auth_token');
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        this.studentNumber = null;
        this.admissionYear = null;
      } finally {
        this.initialized = true;
      }
    },
  },
});
