<template>
  <div>
    <div class="page-head">
      <div><h1>System health</h1><p>Monitor background tasks, backups, and result notifications.</p></div>
      <button class="primary" :disabled="loading" @click="load">{{ loading ? 'Refreshing…' : 'Refresh status' }}</button>
    </div>
    <p v-if="error" class="notice failure" role="alert">{{ error }}</p>
    <p v-if="feedback" class="notice good" role="status">{{ feedback }}</p>
    <p v-if="loading && !data" class="panel loading" role="status">Checking system health…</p>
    <template v-if="data">
      <div class="health-grid" :aria-busy="loading">
        <article class="panel health-card">
          <div class="card-head"><h2>Database</h2><span class="badge" :class="data.database === 'OK' ? 'good' : 'attention'">{{ data.database === 'OK' ? 'Connected' : 'Needs attention' }}</span></div>
          <h3>{{ data.database === 'OK' ? 'Connection available' : data.database }}</h3>
          <p>The portal checks that it can reach the database.</p>
        </article>
        <article class="panel health-card">
          <div class="card-head"><h2>Background tasks</h2><span class="badge" :class="data.scheduler_ok ? 'good' : 'attention'">{{ data.scheduler_ok ? 'Running' : 'Needs attention' }}</span></div>
          <h3>{{ data.scheduler_ok ? 'Maintenance is active' : 'No recent maintenance run' }}</h3>
          <p>{{ data.scheduler_ok ? 'Handles result emails, expired exams, and cleanup.' : 'Configure portal maintenance to run every minute on the server.' }}</p>
          <div class="detail"><span>Last run</span><strong>{{ formatDate(data.scheduler_last_run, 'Not recorded') }}</strong></div>
        </article>
        <article class="panel health-card">
          <div class="card-head"><h2>Database backup</h2><span class="badge" :class="data.latest_backup ? 'neutral' : 'attention'">{{ data.latest_backup ? 'Recorded' : 'Not recorded' }}</span></div>
          <h3>{{ data.latest_backup ? 'Backup found' : 'No backup recorded' }}</h3>
          <p>{{ data.latest_backup ? 'Most recent backup recorded by the portal backup tool.' : 'Set up scheduled database backups on the server.' }}</p>
          <div class="detail"><span>Latest backup</span><strong>{{ formatDate(data.latest_backup, 'Not available') }}</strong></div>
        </article>
        <article class="panel health-card">
          <div class="card-head"><h2>Result emails</h2><span class="badge" :class="data.pending_email_count ? 'neutral' : 'good'">{{ data.pending_email_count ? 'Pending' : 'Queue clear' }}</span></div>
          <div class="count">{{ data.pending_email_count }} <span>pending</span></div>
          <p>Unsent result notifications, including failed deliveries. Background tasks process the queue.</p>
        </article>
      </div>
      <section class="panel deliveries">
        <div class="delivery-head"><div><h2>Failed email deliveries</h2><p>Notifications that reached the retry limit.</p></div><span class="badge" :class="data.failed_emails.length ? 'attention' : 'good'">{{ data.failed_emails.length ? `${data.failed_emails.length} shown` : 'No failures' }}</span></div>
        <div v-if="!data.failed_emails.length" class="empty-state"><h3>No failed notifications</h3><p>Notifications that exhaust their delivery attempts will appear here.</p></div>
        <template v-else>
          <p class="delivery-help">After resolving the email issue, retry a notification to queue it for the next maintenance run.</p>
          <div class="table-wrap"><table><thead><tr><th>Student</th><th>Notification</th><th>Attempts</th><th>Action</th></tr></thead><tbody><tr v-for="item in data.failed_emails" :key="item.id"><td><strong>Student #{{ item.student_id }}</strong></td><td>{{ item.message }}</td><td>{{ item.email_attempts }}</td><td><button class="secondary" :disabled="retrying !== null" @click="retry(item.id)">{{ retrying === item.id ? 'Queuing…' : 'Retry delivery' }}</button></td></tr></tbody></table></div>
        </template>
      </section>
      <p class="footnote">Refresh status checks the latest information. It does not run maintenance or create a backup.</p>
    </template>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import api, { errorMessage } from '../utils/api';
const data = ref(null), error = ref(''), feedback = ref(''), loading = ref(false), retrying = ref(null);
const formatDate = (value, fallback) => {
  if (!value) return fallback;
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? value : date.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
};
const load = async () => {
  loading.value = true;
  error.value = '';
  try { data.value = (await api.get('/admin/operations')).data; }
  catch (e) { error.value = errorMessage(e); }
  finally { loading.value = false; }
};
const retry = async id => {
  retrying.value = id;
  feedback.value = '';
  error.value = '';
  try {
    const response = await api.post(`/admin/operations/notifications/${id}/retry`);
    feedback.value = response.data.message;
    await load();
  } catch (e) { error.value = errorMessage(e); }
  finally { retrying.value = null; }
};
onMounted(load);
</script>
<style scoped>
.health-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-bottom:24px}.health-card{padding:24px;border-radius:8px;border-top:3px solid #b6cabc}.card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.card-head h2{margin:0;font-size:13px;font-weight:700;color:#526459}.badge{display:inline-block;border-radius:20px;padding:5px 10px;font-size:11px;font-weight:700;line-height:1.4;white-space:nowrap}.good{background:#e8f4eb;color:#24613b}.attention{background:#fff3da;color:#835b0c}.neutral{background:#eef1f3;color:#4b5f6b}.health-card h3{font-size:20px;font-weight:650;color:#173e29;margin:20px 0 8px;line-height:1.35}.health-card p{font-size:13px;line-height:1.65;color:#647168;margin:0}.detail{border-top:1px solid #e9eeea;padding-top:14px;margin-top:18px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:12px;color:#647168}.detail strong{font-weight:500;color:#34483b}.count{font-size:36px;font-weight:650;color:#173e29;margin:14px 0 8px}.count span{font-size:14px;font-weight:400;color:#647168}.deliveries{border-radius:8px;overflow:hidden}.delivery-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 24px;border-bottom:1px solid #e5ebe6}.delivery-head h2{margin:0;font-size:17px;color:#173e29}.delivery-head p,.empty-state p,.delivery-help{font-size:13px;color:#647168;line-height:1.6;margin:6px 0 0}.empty-state{padding:36px 24px;text-align:center;background:#fafcfb}.empty-state h3{margin:0;font-size:15px;color:#345840}.delivery-help{padding:14px 24px}.footnote{font-size:12px;line-height:1.6;color:#647168;margin:16px 0}.notice{padding:14px 18px;border-radius:6px;font-size:13px;line-height:1.5}.failure{background:#fceeee;color:#922e2e}button{border-radius:5px}button:disabled{opacity:.6;cursor:wait}button:focus-visible{outline:2px solid #175632;outline-offset:3px}@media(max-width:650px){.health-grid{grid-template-columns:1fr}.health-card{padding:20px}.delivery-head{align-items:flex-start;flex-direction:column;padding:20px}.health-card h3{font-size:18px}}
</style>
