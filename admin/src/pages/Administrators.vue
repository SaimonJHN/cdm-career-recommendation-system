<template>
  <div>
    <div class="page-head">
      <div><h1>Administrators</h1><p>Invite approved Google accounts and assign least-privilege roles.</p></div>
      <button class="primary" @click="openInvite">Invite administrator</button>
    </div>
    <div v-if="error" class="error">{{ error }}</div>
    <div v-if="message" class="success">{{ message }}</div>
    <section class="panel">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Administrator</th><th>Role</th><th>Status</th><th>Last login</th><th>Action</th></tr></thead>
          <tbody>
            <tr v-for="admin in admins" :key="admin.id">
              <td><strong>{{ admin.name }}</strong><br>{{ admin.email }}</td>
              <td><span :class="['role-badge', admin.role]">{{ roleLabel(admin.role) }}</span></td>
              <td><span :class="['status', admin.status]">{{ admin.status }}</span></td>
              <td>{{ admin.last_login_at ? new Date(admin.last_login_at).toLocaleString() : 'Never' }}</td>
              <td><button class="secondary" @click="edit(admin)">Manage roles</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
    <div v-if="form" class="modal-wrap" @click.self="closeModal">
      <form class="modal" @submit.prevent="save">
        <h2>{{ form.id ? 'Manage administrator' : 'Invite administrator' }}</h2>
        <div v-if="form.id" class="admin-summary"><strong>{{ form.name }}</strong><span>{{ form.email }}</span></div>
        <div class="form-grid">
          <div v-if="!form.id" class="field"><label>Name</label><input v-model="form.name" required></div>
          <div v-if="!form.id" class="field"><label>Google email</label><input v-model="form.email" type="email" required></div>
          <div class="field">
            <label>Administrator role</label>
            <select v-model="form.role">
              <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
            </select>
            <small>{{ selectedRoleDescription }}</small>
          </div>
          <div v-if="form.id" class="field">
            <label>Status</label>
            <select v-model="form.status"><option value="pending">Pending</option><option value="active">Active</option><option value="suspended">Suspended</option></select>
          </div>
        </div>
        <p class="hint">The exact invited Google account must be used. New invitations activate after the first verified login.</p>
        <div class="modal-actions split-actions">
          <button v-if="form.id && form.role !== 'super_admin'" type="button" class="danger" :disabled="saving" @click="removeAccount">Remove account</button>
          <span class="action-spacer"></span>
          <button type="button" class="secondary" :disabled="saving" @click="closeModal">Cancel</button>
          <button class="primary" :disabled="saving">{{ saving ? 'Saving…' : form.id ? 'Save role' : 'Create invitation' }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import api, { errorMessage } from '../utils/api';

const roles = [
  { value: 'viewer', label: 'Viewer', description: 'Read-only access with no management permissions.' },
  { value: 'admissions_staff', label: 'Admissions staff', description: 'Can manage students and academic programs.' },
  { value: 'exam_manager', label: 'Exam manager', description: 'Can create, update, and remove exam questions.' },
  { value: 'super_admin', label: 'Super administrator', description: 'Full access, including administrator role management.' },
];
const admins = ref([]);
const form = ref(null);
const error = ref('');
const message = ref('');
const saving = ref(false);
const roleLabel = (value) => roles.find((role) => role.value === value)?.label || value;
const selectedRoleDescription = computed(() => roles.find((role) => role.value === form.value?.role)?.description || '');
const clearFeedback = () => { error.value = ''; message.value = ''; };
const load = async () => {
  try { admins.value = (await api.get('/admin/admins')).data.admins; }
  catch (exception) { error.value = errorMessage(exception); }
};
const openInvite = () => { clearFeedback(); form.value = { name: '', email: '', role: 'viewer' }; };
const edit = (admin) => { clearFeedback(); form.value = { ...admin }; };
const closeModal = () => { if (!saving.value) form.value = null; };
const save = async () => {
  clearFeedback(); saving.value = true;
  try {
    if (form.value.id) {
      await api.patch(`/admin/admins/${form.value.id}`, { role: form.value.role, status: form.value.status });
      message.value = 'Administrator role updated.';
    } else {
      await api.post('/admin/admins', form.value);
      message.value = 'Administrator invitation created.';
    }
    form.value = null;
    await load();
  } catch (exception) { error.value = errorMessage(exception); }
  finally { saving.value = false; }
};
const removeAccount = async () => {
  if (!window.confirm(`Permanently remove the administrator account for ${form.value.name} (${form.value.email})? This cannot be undone.`)) return;
  clearFeedback(); saving.value = true;
  try {
    await api.delete(`/admin/admins/${form.value.id}`);
    message.value = 'Administrator account removed permanently.';
    form.value = null;
    await load();
  } catch (exception) { error.value = errorMessage(exception); }
  finally { saving.value = false; }
};
onMounted(load);
</script>

<style scoped>
.hint{background:#fff7d9;color:#6f5a0d;padding:12px;font-size:11px;line-height:1.6;margin-top:18px;text-align:left}
.success{margin-bottom:16px;padding:13px 16px;background:#e6f4ea;color:#23713f;border:1px solid #b9ddc4;font-size:12px}
.role-badge{display:inline-block;padding:5px 9px;background:#edf2ee;color:#385344;font-size:10px;font-weight:750;text-transform:capitalize}
.role-badge.super_admin{background:#fff1bd;color:#755b00}.role-badge.admissions_staff,.role-badge.exam_manager{background:#e3f0e8;color:#175d35}
.admin-summary{display:flex;flex-direction:column;background:#f6f7f4;border-left:4px solid var(--gold);padding:12px 14px;margin-bottom:18px}
.admin-summary strong{font-size:14px;color:#173e29}.admin-summary span{font-size:11px;color:var(--muted);margin-top:3px}
.field small{color:var(--muted);font-size:10px;line-height:1.5}.split-actions{justify-content:flex-start}.action-spacer{flex:1}button:disabled{cursor:not-allowed;opacity:.6}
@media(max-width:600px){.split-actions{flex-wrap:wrap}.action-spacer{display:none}.split-actions button{flex:1}}
</style>
