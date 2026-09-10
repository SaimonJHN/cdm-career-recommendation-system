<template>
  <section :class="compact ? 'border-t border-gray-200 pt-4 text-xs leading-relaxed break-words' : 'school-card p-6'">
    <p v-if="error" class="mb-3 text-sm text-red-700" role="alert">{{ error }} <button class="underline" @click="load">Retry</button></p>
    <details class="progress-disclosure">
      <summary><span>Application progress</span><span class="chevron" aria-hidden="true">›</span></summary>
      <div class="disclosure-body space-y-3">
        <template v-if="data">
          <p class="font-semibold">{{ labels[data.status] || data.status }}</p>
          <p>{{ data.attempts_used }} of 2 exam attempts submitted.</p>
          <ol class="space-y-2"><li v-for="step in data.timeline" :key="step.attempt">Attempt {{ step.attempt }} — {{ labels[step.status] || step.status }} (submitted {{ new Date(step.submitted_at).toLocaleString() }})</li></ol>
          <router-link class="inline-block underline" :to="['NOT_STARTED','IN_PROGRESS','RETAKE'].includes(data.status) ? '/exam' : '/results'">{{ data.status === 'RETAKE' ? 'Begin final retake' : data.status === 'IN_PROGRESS' ? 'Resume exam' : data.status === 'NOT_STARTED' ? 'Take examination' : 'View results' }}</router-link>
        </template>
        <p v-else>Progress is not available yet.</p>
        <button class="block underline" :disabled="loading" @click="load">{{ loading ? 'Refreshing…' : 'Refresh status' }}</button>
      </div>
    </details>
    <details class="progress-disclosure">
      <summary><span>Notifications</span><span v-if="unreadCount" class="unread-badge" :aria-label="`${unreadCount} unread notifications`">{{ unreadCount }}</span><span class="chevron" aria-hidden="true">›</span></summary>
      <div class="disclosure-body">
        <template v-if="data">
          <p v-if="!data.notifications.length" class="text-gray-500">No notifications yet.</p>
          <div class="notification-list">
            <article v-for="item in data.notifications" :key="item.id" class="notification-message" :class="{ unread: !item.read_at }">
              <span v-if="!item.read_at" class="unread-label">Unread</span>
              <router-link :to="item.link" class="block hover:underline" @click="markRead(item)">{{ item.message }}</router-link>
              <button v-if="!item.read_at" class="mt-2 underline" @click="markRead(item)">Mark read</button>
            </article>
          </div>
        </template>
        <p v-else>Notifications are not available yet.</p>
        <button class="mt-3 underline" :disabled="loading" @click="load">{{ loading ? 'Refreshing…' : 'Refresh notifications' }}</button>
      </div>
    </details>
  </section>
</template>
<script setup>
import { computed, ref, onMounted } from 'vue';
import api from '../utils/api.js';
defineProps({ compact: { type: Boolean, default: false } });
const data=ref(null),error=ref(''),loading=ref(false);
const unreadCount=computed(()=>data.value?.notifications.filter(item=>!item.read_at).length || 0);
const emit=defineEmits(['status']);
const labels={NOT_STARTED:'Ready to begin',IN_PROGRESS:'Exam in progress',PENDING:'Submitted — awaiting Registrar review',APPROVED:'Approved — awaiting publication',RETAKE:'One final retake available',PASSED:'Passed',FAILED:'Final attempt completed — not passed'};
const load=async()=>{loading.value=true;error.value='';try{data.value=(await api.get('/student/status')).data;emit('status',data.value)}catch{error.value='Unable to load your progress.'}finally{loading.value=false}};
const markRead=async item=>{if(item.read_at)return;try{await api.patch(`/student/notifications/${item.id}/read`);item.read_at=new Date().toISOString()}catch{error.value='Unable to mark the notification as read.'}};
onMounted(load);
</script>
<style scoped>
.progress-disclosure{border-bottom:1px solid #e5e7eb}.progress-disclosure summary{display:flex;align-items:center;gap:8px;padding:14px 4px;cursor:pointer;list-style:none;font-size:14px;font-weight:600;color:#254535}.progress-disclosure summary::-webkit-details-marker{display:none}.progress-disclosure summary:hover{background:#f7faf8}.progress-disclosure summary:focus-visible{outline:2px solid #166534;outline-offset:2px;border-radius:4px}.chevron{margin-left:auto;font-size:22px;line-height:1;transition:transform .15s}.progress-disclosure[open]>summary .chevron{transform:rotate(90deg)}.unread-badge{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:#b91c1c;color:#fff;font-size:11px;font-weight:700}.disclosure-body{padding:4px 4px 16px;font-size:13px;line-height:1.6}.notification-list{display:grid;gap:10px;max-height:320px;overflow-y:auto}.notification-message{padding:12px;background:#f6f7f6;border:1px solid #e5e7eb;border-radius:8px;overflow-wrap:anywhere}.notification-message.unread{background:#f0f7f2;border-color:#c6ddcd}.unread-label{display:block;font-size:10px;font-weight:700;color:#b91c1c;margin-bottom:4px}button:disabled{opacity:.6}
</style>
