<template>
  <div v-if="loading" class="flex min-h-screen items-center justify-center">
    <div class="text-center">
      <div class="text-lg font-semibold" style="color: var(--color-school-green); font-family: var(--font-heading);">Loading...</div>
    </div>
  </div>
  <div v-else class="dashboard-layout flex flex-col md:flex-row min-h-screen">
    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 flex-shrink-0 border-r border-gray-200 bg-white">
      <div class="md:sticky md:top-0 md:h-screen md:overflow-y-auto p-4">
        <!-- Profile Top -->
        <div class="mb-6">
          <div class="flex items-center space-x-3 mb-4">
            <div v-if="user?.profile_picture" class="w-12 h-12 rounded-full border-2 overflow-hidden flex-shrink-0" style="border-color: var(--color-school-gold);">
              <img :src="getProfilePictureUrlWithCacheBuster(user?.profile_picture)" :alt="user?.full_name || 'Student'" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-12 h-12 rounded-full flex items-center justify-center text-white" style="background: #374151;">
              👤
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900 truncate" style="font-family: var(--font-heading);">{{ user?.full_name || 'Student' }}</p>
              <p class="text-xs text-gray-500 truncate">{{ studentNumber || '--' }}</p>
            </div>
          </div>
        </div>

        <nav class="space-y-1">
          <router-link to="/profile" class="flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50" active-class="bg-green-50 text-green-700">
            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700">👤</span>
            <span>My Profile</span>
          </router-link>

          <router-link to="/exam" class="flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50" active-class="bg-green-50 text-green-700">
            <span class="flex items-center justify-center w-8 h-8 rounded-full text-white" style="background: var(--color-school-green);">📝</span>
            <span>Entrance Examination</span>
          </router-link>

          <router-link
            to="/results"
            class="flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            :class="{ 'opacity-50 pointer-events-none': !examTaken }"
            active-class="bg-green-50 text-green-700"
          >
            <span class="flex items-center justify-center w-8 h-8 rounded-full text-white" style="background: var(--color-school-gold); color: #14532d;">📊</span>
            <span>Exam Status</span>
          </router-link>

          <router-link to="/programs" class="flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50" active-class="bg-green-50 text-green-700">
            <span class="flex items-center justify-center w-8 h-8 rounded-full text-white" style="background: var(--color-school-green);">🏫</span>
            <span>Program Overview</span>
          </router-link>

        <router-link to="/recommendation" class="flex items-center px-3 py-3 rounded-md text-sm font-semibold text-green-800 hover:bg-green-50">My Recommendation</router-link></nav>
        <StudentProgress compact class="mt-6" @status="portalStatus = $event" />
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0 p-0 pt-4 md:p-8 space-y-5 md:space-y-8 fade-in">
      <!-- Welcome Banner -->
      <div class="dashboard-box dashboard-box--hero relative overflow-hidden rounded-lg p-8 text-white shadow">
        <div class="absolute top-0 right-0 w-64 h-64 opacity-10" style="background: radial-gradient(circle at top right, var(--color-school-gold), transparent);"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color: var(--color-school-gold-light);">Student Portal</p>
            <h1 class="text-3xl md:text-4xl font-bold mb-2" style="font-family: var(--font-heading);">Welcome, {{ user?.full_name || 'Student' }}</h1>
            <p style="color: #e8dcc8;">Track your application and entrance examination progress.</p>
            <div class="mt-4 flex flex-wrap gap-4 text-sm">
              <span class="school-badge" style="border-color: rgba(255,255,255,0.25); color: #f5f0e6; background: rgba(255,255,255,0.08);">
                Applicant Number: {{ studentNumber || '--' }}
              </span>
              <span class="school-badge" style="border-color: rgba(255,255,255,0.25); color: #f5f0e6; background: rgba(255,255,255,0.08);">
                Admission Year: {{ admissionYear || '--' }}
              </span>
            </div>
          </div>
          <div v-if="user?.profile_picture" class="hidden md:block w-24 h-24 rounded-full border-2 bg-white/10 overflow-hidden flex-shrink-0" style="border-color: var(--color-school-gold);">
            <img :src="getProfilePictureUrlWithCacheBuster(user?.profile_picture)" :alt="user?.full_name || 'Student'" class="w-full h-full object-cover" />
          </div>
        </div>
      </div>

      <RecommendationSummary />
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="dashboard-box school-card p-6">
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Exam Status</p>
          <div class="text-3xl font-bold mb-2" style="color: var(--color-school-green); font-family: var(--font-heading);">
            {{ portalStatusLabel }}
          </div>
          <p class="text-sm text-gray-600">
            {{ examTaken ? 'See your application progress for details' : 'Take the entrance exam' }}
          </p>
        </div>

        <div class="dashboard-box school-card p-6">
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Assessment Format</p>
          <div class="text-3xl font-bold mb-2" style="color: #6b4f1d; font-family: var(--font-heading);">5 Topics</div>
          <p class="text-sm text-gray-600">100 questions across five academic topics</p>
        </div>

        <div class="dashboard-box school-card p-6">
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Official Result</p>
          <div class="text-2xl font-bold mb-2" style="color: var(--color-school-green); font-family: var(--font-heading);">
            {{ ['PASSED','FAILED','RETAKE'].includes(portalStatus?.status) ? portalStatus.status : 'Awaiting release' }}
          </div>
          <p class="text-sm text-gray-600">
            {{ examTaken ? 'See your application progress for updates' : 'Complete the exam first' }}
          </p>
        </div>
      </div>

      <!-- Programs Info -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-gray-900" style="font-family: var(--font-heading);">Available Programs</h2>
          <router-link to="/programs" class="text-sm font-semibold" style="color: var(--color-school-green);">View all programs &rarr;</router-link>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="dashboard-box school-card p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1" style="font-family: var(--font-heading);">BSIT</h3>
            <p class="text-sm text-gray-600">Bachelor of Science in Information Technology</p>
            <p class="text-xs font-semibold mt-3" style="color: var(--color-school-green);">Technical Skills Required</p>
          </div>

          <div class="dashboard-box school-card p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1" style="font-family: var(--font-heading);">BSE</h3>
            <p class="text-sm text-gray-600">Bachelor of Secondary Education</p>
            <p class="text-xs font-semibold mt-3" style="color: var(--color-school-green);">Science &amp; Math Skills Required</p>
          </div>

          <div class="dashboard-box school-card p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1" style="font-family: var(--font-heading);">BSBA</h3>
            <p class="text-sm text-gray-600">Bachelor of Science in Business Administration</p>
            <p class="text-xs font-semibold mt-3" style="color: var(--color-school-green);">Business Acumen Required</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import RecommendationSummary from '../components/RecommendationSummary.vue';
import StudentProgress from '../components/StudentProgress.vue';
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import api from '../utils/api.js';
import { getProfilePictureUrlWithCacheBuster } from '../utils/helpers.js';

const authStore = useAuthStore();
const loading = computed(() => !authStore.isInitialized);

const user = computed(() => authStore.getUser);
const studentNumber = computed(() => authStore.getStudentNumber);
const admissionYear = computed(() => authStore.admissionYear);

const portalStatus=ref(null);
const portalStatusLabel=computed(()=>({NOT_STARTED:'Not started',IN_PROGRESS:'In progress',PENDING:'Awaiting review',APPROVED:'Awaiting publication',PASSED:'Passed',FAILED:'Not passed',RETAKE:'Retake available'}[portalStatus.value?.status] || 'Checking status'));
const examTaken = ref(false);

onMounted(async () => {
  try {
    if (!user.value || !user.value?.email) {
      await authStore.getProfile();
    }
  } catch (error) {
    console.error('Error fetching profile for dashboard:', error);
  }

  try {
    const response = await api.get('/student/dashboard');
    if (response.data.success) {
      examTaken.value = response.data.exam_taken;
      if (!authStore.getStudentNumber && response.data.student_number) {
        authStore.studentNumber = response.data.student_number;
      }
      if (!authStore.admissionYear && response.data.admission_year) {
        authStore.admissionYear = response.data.admission_year;
      }
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  }
});
</script>

<style scoped>
.dashboard-box {
  border-style: solid;
  border-width: 2px 7px;
  border-color: transparent;
  border-left-color: var(--color-school-green);
  border-right-color: var(--color-school-gold);
  background:
    linear-gradient(#ffffff, #ffffff) padding-box,
    linear-gradient(
      135deg,
      var(--color-school-green) 0%,
      var(--color-school-green-light) 45%,
      var(--color-school-gold) 55%,
      var(--color-school-gold-light) 100%
    ) border-box;
}

.dashboard-box--hero {
  background:
    linear-gradient(135deg, var(--color-school-green) 0%, var(--color-school-green-light) 100%) padding-box,
    linear-gradient(
      135deg,
      var(--color-school-green) 0%,
      var(--color-school-green-light) 45%,
      var(--color-school-gold) 55%,
      var(--color-school-gold-light) 100%
    ) border-box;
}
@media(max-width:767px){
  .dashboard-layout{min-width:0}.dashboard-layout>aside{border:1px solid #e5e7eb;border-radius:8px}.dashboard-layout>main{width:100%}.dashboard-box{padding:20px 16px;border-width:1px 4px;overflow-wrap:anywhere}.dashboard-box--hero h1{font-size:24px;line-height:1.25}.dashboard-box--hero .school-badge{white-space:normal;font-size:11px}.dashboard-layout nav a{min-width:0}.dashboard-layout aside .truncate{max-width:190px}.dashboard-layout main h2{font-size:18px}
}
</style>
