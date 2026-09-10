<template>
  <div class="space-y-6 fade-in">
    <div v-if="loading" class="school-card p-8 text-center">
      <p class="text-gray-600">Loading profile...</p>
    </div>

    <div v-else-if="loadError" class="school-card p-8 text-center">
      <p class="font-semibold text-red-700">Unable to load your profile</p>
      <p class="mt-1 text-sm text-gray-600">{{ loadError }}</p>
      <button @click="loadProfile" class="mt-4 inline-flex items-center px-5 py-2.5 rounded-md font-semibold text-white" style="background: var(--color-school-green);">
        Try Again
      </button>
    </div>

    <template v-else-if="profile">
    <!-- Green Top Student Profile Header -->
    <div class="relative overflow-hidden rounded-lg border border-gray-200 p-6 text-white shadow" style="background: linear-gradient(135deg, var(--color-school-green) 0%, var(--color-school-green-light) 100%);">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center space-x-5">
          <div class="relative flex-shrink-0">
            <div class="w-20 h-20 rounded-full border-2 overflow-hidden flex items-center justify-center text-2xl font-bold" style="border-color: var(--color-school-gold); background: rgba(255,255,255,0.15);">
              <img v-if="avatarUrl" :key="avatarUrl" :src="avatarUrl" :alt="profile?.full_name || 'Student'" class="w-full h-full object-cover" />
              <span v-else class="text-white">{{ userInitials }}</span>
            </div>
            <button
              v-if="editing"
              @click="triggerFileInput"
              :disabled="uploading"
              class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full flex items-center justify-center text-white shadow disabled:cursor-wait disabled:opacity-70"
              style="background: var(--color-school-gold); color: #14532d;"
              :title="uploading ? 'Uploading photo...' : 'Change photo'"
            >
              <span aria-hidden="true">📷</span>
              <span class="sr-only">Change profile picture</span>
            </button>
          </div>
          <div>
            <h1 class="text-2xl md:text-3xl font-bold" style="font-family: var(--font-heading);">{{ displayFullName }}</h1>
            <p class="text-sm" style="color: #e8dcc8;">{{ profile?.email }}</p>
            <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-semibold" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25);">Active Student</span>
          </div>
        </div>
        <div class="flex items-center space-x-4 text-sm">
          <span class="school-badge" style="border-color: rgba(255,255,255,0.25); color: #f5f0e6; background: rgba(255,255,255,0.08);">
            Applicant Number: {{ studentNumber || '--' }}
          </span>
          <span class="school-badge" style="border-color: rgba(255,255,255,0.25); color: #f5f0e6; background: rgba(255,255,255,0.08);">
            Admission Year: {{ admissionYear || '--' }}
          </span>
        </div>
      </div>
      <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden" @change="handleFileChange" />
    </div>

    <!-- Profile Details -->
    <div class="school-card p-6 md:p-8">
      <div v-if="!editing" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="p-5 rounded-lg border border-gray-100" style="background: rgba(20,83,45,0.03);">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Full Name</p>
            <p class="text-lg font-semibold text-gray-900" style="font-family: var(--font-heading);">{{ profile.full_name }}</p>
          </div>
          <div class="p-5 rounded-lg border border-gray-100" style="background: rgba(20,83,45,0.03);">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Email</p>
            <p class="text-lg font-semibold text-gray-900">{{ profile.email }}</p>
          </div>
          <div class="p-5 rounded-lg border border-gray-100" style="background: rgba(20,83,45,0.03);">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Applicant Number</p>
            <p class="text-lg font-semibold text-gray-900" style="font-family: var(--font-heading);">{{ studentNumber || '--' }}</p>
          </div>
          <div class="p-5 rounded-lg border border-gray-100" style="background: rgba(20,83,45,0.03);">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Admission Year</p>
            <p class="text-lg font-semibold text-gray-900" style="font-family: var(--font-heading);">{{ admissionYear || '--' }}</p>
          </div>
          <div class="p-5 rounded-lg border border-gray-100" style="background: rgba(20,83,45,0.03);">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Phone</p>
            <p class="text-lg font-semibold text-gray-900">{{ profile.phone || '--' }}</p>
          </div>
          <div class="p-5 rounded-lg border border-gray-100" style="background: rgba(20,83,45,0.03);">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Date of Birth</p>
            <p class="text-lg font-semibold text-gray-900">{{ profile.date_of_birth || '--' }}</p>
          </div>
        </div>

        <div class="flex flex-wrap gap-3">
          <button @click="startEditing" class="inline-flex items-center px-5 py-2.5 rounded-md font-semibold text-white transition" style="background: var(--color-school-green);">
            Edit Profile
          </button>
          <router-link to="/dashboard" class="inline-flex items-center px-5 py-2.5 rounded-md font-semibold text-white transition" style="background: var(--color-school-green);">
            Back to Dashboard
          </router-link>
        </div>
      </div>

      <div v-else class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">First Name</label>
            <input v-model="form.first_name" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Last Name</label>
            <input v-model="form.last_name" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Email</label>
            <input type="email" :value="profile.email" disabled class="w-full rounded-md border border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Applicant Number</label>
            <input type="text" :value="studentNumber" disabled class="w-full rounded-md border border-gray-200 bg-gray-50 px-4 py-2.5 text-gray-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Phone</label>
            <input v-model="form.phone" type="tel" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500 mb-1">Date of Birth</label>
            <input v-model="form.date_of_birth" type="date" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500" />
          </div>
        </div>

        <div class="flex flex-wrap gap-3">
          <button @click="saveProfile" :disabled="saving" class="inline-flex items-center px-5 py-2.5 rounded-md font-semibold text-white transition disabled:opacity-60" style="background: var(--color-school-green);">
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
          <button @click="cancelEditing" :disabled="saving" class="inline-flex items-center px-5 py-2.5 rounded-md font-semibold text-gray-700 transition disabled:opacity-60" style="background: #e5e7eb;">
            Cancel
          </button>
        </div>
      </div>
    </div>

    </template>
  </div>
  <SessionSecurity /></template>

<script setup>
import SessionSecurity from '../components/SessionSecurity.vue';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useAuthStore } from '../stores/authStore';
import api from '../utils/api.js';
import { getProfilePictureUrlWithCacheBuster } from '../utils/helpers.js';

const authStore = useAuthStore();
const profile = ref(authStore.getUser || null);
const loading = ref(!profile.value);
const loadError = ref('');
const studentNumber = computed(() => authStore.getStudentNumber);
const admissionYear = computed(() => authStore.admissionYear);
const editing = ref(false);
const saving = ref(false);
const uploading = ref(false);
const fileInput = ref(null);
const avatarPreviewUrl = ref('');

const user = computed(() => authStore.getUser);

const avatarUrl = computed(() => avatarPreviewUrl.value || getProfilePictureUrlWithCacheBuster(profile.value?.profile_picture || user.value?.profile_picture));

const userInitials = computed(() => {
  const name = profile.value?.full_name || user.value?.full_name || '';
  const parts = name.trim().split(' ');
  return parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : parts[0]?.[0]?.toUpperCase() || 'U';
});

const displayFullName = computed(() => profile.value?.full_name || user.value?.full_name || 'Student');

const defaultForm = () => ({
  first_name: '',
  last_name: '',
  phone: '',
  date_of_birth: '',
});

const form = ref(defaultForm());

const startEditing = () => {
  form.value = {
    first_name: profile.value?.first_name || '',
    last_name: profile.value?.last_name || '',
    phone: profile.value?.phone || '',
    date_of_birth: profile.value?.date_of_birth || '',
  };
  editing.value = true;
};

const cancelEditing = () => {
  editing.value = false;
};

const triggerFileInput = () => {
  if (uploading.value) return;
  fileInput.value?.click();
};

const clearAvatarPreview = () => {
  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value);
    avatarPreviewUrl.value = '';
  }
};

const handleFileChange = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    alert('Please select a JPG, PNG, or WebP image.');
    event.target.value = '';
    return;
  }

  if (file.size > 2 * 1024 * 1024) {
    alert('The profile picture must be 2 MB or smaller.');
    event.target.value = '';
    return;
  }

  clearAvatarPreview();
  avatarPreviewUrl.value = URL.createObjectURL(file);

  const formData = new FormData();
  formData.append('profile_picture', file);

  uploading.value = true;
  try {
    formData.append('_method', 'PUT');
    const response = await api.post('/student/profile', formData);
    if (response.data?.success && response.data?.student) {
      profile.value = response.data.student;
      authStore.updateUser(response.data.student);
      alert('Profile picture updated successfully');
    } else {
      alert(response.data?.message || 'Failed to update profile picture');
    }
  } catch (error) {
    console.error('Error updating profile picture:', error);
    clearAvatarPreview();
    const validationErrors = error.response?.data?.errors;
    const message = validationErrors
      ? Object.values(validationErrors).flat().join(' ')
      : error.response?.data?.message || 'Failed to update profile picture';
    alert(message);
  } finally {
    uploading.value = false;
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  }
};

const saveProfile = async () => {
  saving.value = true;

  try {
    const response = await api.put('/student/profile', {
      first_name: form.value.first_name.trim(),
      last_name: form.value.last_name.trim(),
      phone: form.value.phone || null,
      date_of_birth: form.value.date_of_birth || null,
    });
    if (response.data?.success && response.data?.student) {
      profile.value = response.data.student;
      authStore.updateUser(response.data.student);
      editing.value = false;
      alert('Profile updated successfully');
    } else {
      alert(response.data?.message || 'Failed to update profile');
    }
  } catch (error) {
    console.error('Error updating profile:', error);
    const validationErrors = error.response?.data?.errors;
    const message = validationErrors
      ? Object.values(validationErrors).flat().join(' ')
      : error.response?.data?.message || 'Failed to update profile';
    alert(message);
  } finally {
    saving.value = false;
  }
};

const loadProfile = async () => {
  loading.value = true;
  loadError.value = '';
  try {
    const response = await authStore.getProfile();
    if (!response?.student) {
      throw new Error('The server returned an incomplete profile.');
    }
    profile.value = response.student;
  } catch (error) {
    console.error('Error fetching profile:', error);
    loadError.value = error.message || 'Failed to fetch profile.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadProfile);

onBeforeUnmount(clearAvatarPreview);
</script>
