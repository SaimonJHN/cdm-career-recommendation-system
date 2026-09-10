<template>
  <div>
    <div class="page-head"><div><h1>Official assessment results</h1><p>Review, approve, and publish registrar-verified results to applicants.</p></div></div>
    <section v-if="auth.canManageStudents" class="panel import-panel">
      <div class="import-copy"><h2>Import registrar results</h2><p>Upload Excel, CSV, TSV, TXT, or JSON. Use a fresh template with <b>applicant_number</b>, <b>result_id</b>, <b>result_version</b> and <b>score</b> (up to 1,000 rows); <b>remarks</b> is optional.</p></div>
      <div class="import-actions">
        <label class="file-picker"><input ref="fileInput" type="file" accept=".xlsx,.csv,.tsv,.txt,.json,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/json" @change="pickFile"><span>Choose file</span></label>
        <span class="file-name" :title="csvFile?.name">{{ csvFile?.name || 'No file selected' }}</span>
        <button class="primary action-button" :disabled="!csvFile || busy" @click="importCsv">{{ busy ? 'Importing…' : 'Preview import' }}</button>
        <button class="secondary action-button" :disabled="downloadingTemplate" @click="downloadTemplate">{{ downloadingTemplate ? 'Preparing…' : 'Download template' }}</button>
      </div>
      <div v-if="feedback" class="feedback">{{ feedback }}</div>
      <ul v-if="importErrors.length" class="import-errors"><li v-for="error in importErrors" :key="error">{{ error }}</li></ul>
    </section>
    <section class="panel">
      <div v-if="auth.canManageStudents && !retakeFilter && officialStatus === 'pending'" class="import-panel">
        <p>Select pending results to approve using their calculated scores.</p>
        <button class="secondary" @click="selectedIds = [...(page.matching_ids || [])]">Select all matching pending results</button>
        <button class="secondary" @click="selectedIds = []">Clear selection</button>
        <button class="primary" :disabled="passingBatch || !selectedIds.length" @click="approveSelected">Approve {{ selectedIds.length }} selected using system scores</button>
      </div>
      <div v-if="importPreview" class="import-panel">
        <h2>Import preview — {{ importPreview.rows.length }} changes</h2>
        <div style="max-height:300px;overflow:auto"><p v-for="row in importPreview.rows" :key="row.result_id">{{ row.applicant_number }} / result #{{ row.result_id }}: {{ row.old_score ?? 'No official score' }} → {{ row.score }}</p></div>
        <button class="primary" :disabled="busy || !importPreview.preview_id" @click="commitImport">Confirm import and approve</button>
        <button class="secondary" @click="importPreview=null">Cancel preview</button>
      </div>
      <div v-if="auth.canManageStudents && retakeFilter" class="import-panel">
        <template v-if="retakeFilter">
          <p>Select students below or select all {{ page.total || 0 }} eligible students across all pages.</p>
          <div class="import-actions">
            <button class="secondary" :disabled="passingBatch || !page.eligible_ids?.length" @click="selectedIds = [...page.eligible_ids]">Select all eligible</button>
            <button class="secondary" :disabled="passingBatch" @click="selectedIds = []">Clear selection</button>
            <span>{{ selectedIds.length }} selected</span>
          </div>
          <label>Internal decision reason (visible only to admins)
            <textarea v-model="decisionReason" maxlength="2000" rows="3" :disabled="passingBatch" style="display:block;width:100%"></textarea>
          </label>
          <button class="primary" :disabled="passingBatch || !selectedIds.length || !decisionReason.trim()" @click="passSelected">{{ passingBatch ? 'Publishing...' : 'Mark selected as passed and publish' }}</button>
        </template>
      </div>
      <div class="toolbar"><select :disabled="retakeFilter" v-model="passed" @change="load(1)"><option value="">All outcomes</option><option value="true">System passed</option><option value="false">System not passed</option></select><select :disabled="retakeFilter" v-model="officialStatus" @change="load(1)"><option value="">All release statuses</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="published">Published</option></select><button v-if="auth.canManageStudents" class="primary publish-all" :disabled="publishingAll" @click="publishAllApproved">{{ publishingAll ? 'Publishing…' : 'Publish all approved' }}</button></div>
      <div class="table-wrap"><table>
        <thead><tr><th v-if="auth.canManageStudents && (retakeFilter || officialStatus === 'pending')">Select</th><th>Applicant</th><th>System score</th><th>Official score</th><th>Release status</th><th>Recommended program</th><th>Exam date</th><th>Action</th></tr></thead>
        <tbody><tr v-for="result in page.data" :key="result.id"><td v-if="auth.canManageStudents && (retakeFilter || officialStatus === 'pending')"><input type="checkbox" v-model="selectedIds" :value="result.id" :disabled="passingBatch" :aria-label="`Select ${result.student?.student_number}`"></td><td><strong>{{ result.student?.first_name }} {{ result.student?.last_name }}</strong><br>{{ result.student?.student_number }}<br><small>Attempt {{ result.attempt_number }} · Result #{{ result.id }}</small></td><td>{{ result.total_score }}/100</td><td>{{ result.official_score === null ? '—' : `${result.official_score}/100` }}</td><td><span :class="['status', result.official_status]">{{ result.official_outcome }}</span><div v-if="result.registrar_pass"><strong>Passed by Registrar decision</strong><p>{{ result.registrar_pass_reason }}</p><small>Admin #{{ result.registrar_pass_by }} · {{ new Date(result.registrar_pass_at).toLocaleString() }}</small></div></td><td>{{ result.student?.recommendation?.course?.name || 'Not generated' }}</td><td>{{ new Date(result.exam_date).toLocaleDateString() }}</td><td class="actions"><button v-if="auth.canManageStudents && result.official_status !== 'published'" class="secondary" @click="approve(result)">Approve</button><button v-if="auth.canManageStudents && result.official_status === 'approved'" class="primary" @click="publishResult(result)">Publish</button><button v-if="auth.canManageStudents && result.official_status === 'published'" class="secondary" @click="correct(result)">Correct result</button></td></tr><tr v-if="!page.data?.length"><td :colspan="auth.canManageStudents && (retakeFilter || officialStatus === 'pending') ? 8 : 7" class="empty">No results found.</td></tr></tbody>
      </table></div>
      <div class="pagination"><button class="secondary" :disabled="!page.prev_page_url" @click="load(page.current_page-1)">Previous</button><button class="secondary" :disabled="!page.next_page_url" @click="load(page.current_page+1)">Next</button></div>
      <div class="results-filters">
        <div class="filter-heading"><h2>Filter results</h2><p>Find applicants and choose which exam attempts to display.</p></div>
        <div class="filter-fields">
          <label class="filter-field"><span>Search applicant</span><input v-model="search" placeholder="Name or applicant number" @change="load(1)"></label>
          <label class="filter-field"><span>Admission year</span><input v-model="admissionYear" placeholder="e.g. 2026-2027" @change="load(1)"></label>
          <label class="filter-check"><input type="checkbox" v-model="latestOnly" @change="load(1)"><span><strong>Latest attempt only</strong><small>Show each student's most recent exam.</small></span></label>
        </div>
        <label v-if="auth.canManageStudents" class="filter-check decision-filter" :class="{ 'is-selected': retakeFilter }"><input type="checkbox" v-model="retakeFilter" :disabled="passingBatch" @change="changeRetakeFilter"><span><strong>Eligible for Registrar decision</strong><small>Show failed second attempts that qualify for review.</small></span></label>
      </div>
    </section>
  </div>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import api, { errorMessage } from '../utils/api';
import { useAdminAuth } from '../stores/auth';
const auth=useAdminAuth(),page=ref({data:[]}),passed=ref(''),officialStatus=ref(''),csvFile=ref(null),fileInput=ref(null),busy=ref(false),downloadingTemplate=ref(false),publishingAll=ref(false),feedback=ref(''),importErrors=ref([]);
const retakeFilter=ref(false),selectedIds=ref([]),decisionReason=ref(''),passingBatch=ref(false);
const search=ref(''),admissionYear=ref(''),latestOnly=ref(false),importPreview=ref(null);
const approveSelected=async()=>{if(!window.confirm(`Approve ${selectedIds.value.length} results using system scores?`))return;passingBatch.value=true;try{feedback.value=(await api.patch('/admin/results/approve-batch',{result_ids:selectedIds.value})).data.message;selectedIds.value=[];await load(1)}catch(e){feedback.value=errorMessage(e)}finally{passingBatch.value=false}};
const correct=async result=>{const reason=window.prompt('Internal reason for correcting the published result:');if(!reason?.trim())return;const value=window.prompt('New official score (0–100). This replaces any Registrar pass decision; the original remains in the audit log.',result.official_score??result.total_score);if(value===null||value.trim()==='')return;const score=Number(value);if(!Number.isInteger(score)||score<0||score>100)return window.alert('Enter a whole number from 0 to 100.');if(!window.confirm('Publish this corrected result now?'))return;try{feedback.value=(await api.patch(`/admin/results/${result.id}/correct`,{official_score:score,reason,version:result.result_version})).data.message;await load(page.value.current_page||1)}catch(e){feedback.value=errorMessage(e)}};
const commitImport=async()=>{busy.value=true;try{feedback.value=(await api.post('/admin/results/import/commit',{preview_id:importPreview.value.preview_id})).data.message;importPreview.value=null;await load(1)}catch(e){feedback.value=errorMessage(e)}finally{busy.value=false}};
let previousFilters='';
const load=async(currentPage=1)=>{const filters=JSON.stringify([search.value,admissionYear.value,latestOnly.value,passed.value,officialStatus.value,retakeFilter.value]);if(filters!==previousFilters)selectedIds.value=[];previousFilters=filters;page.value=(await api.get('/admin/results',{params:{search:search.value,admission_year:admissionYear.value,latest_only:latestOnly.value?1:0,page:currentPage,passed:passed.value,official_status:officialStatus.value,registrar_pass_eligible:retakeFilter.value ? 1 : 0}})).data};
const changeRetakeFilter=async()=>{selectedIds.value=[];passed.value='';officialStatus.value='';try{await load(1)}catch(error){feedback.value=errorMessage(error)}};
const passSelected=async()=>{
  if(passingBatch.value || !selectedIds.value.length || !decisionReason.value.trim())return;
  if(!window.confirm(`Publish PASSED for ${selectedIds.value.length} selected students? Students will see PASSED. The reason stays in admin records and exam scores are preserved.`))return;
  passingBatch.value=true;feedback.value='';
  try{
    const {data}=await api.patch('/admin/results/registrar-pass',{result_ids:[...selectedIds.value],reason:decisionReason.value.trim()});
    feedback.value=data.message;selectedIds.value=[];decisionReason.value='';await load(1);
  }catch(error){feedback.value=errorMessage(error)}finally{passingBatch.value=false}
};
const pickFile=event=>{importPreview.value=null;csvFile.value=event.target.files?.[0]||null};
const importCsv=async()=>{if(!csvFile.value)return;busy.value=true;feedback.value='';importErrors.value=[];try{const form=new FormData();form.append('file',csvFile.value);const{data}=await api.post('/admin/results/import',form);feedback.value=data.message;importErrors.value=data.errors||[];importPreview.value=data;csvFile.value=null;if(fileInput.value)fileInput.value.value='';await load(1)}catch(error){feedback.value=errorMessage(error)}finally{busy.value=false}};
const approve=async result=>{const rawScore=window.prompt('Official score (0–100):',result.official_score??result.total_score);if(rawScore===null)return;const score=Number(rawScore);if(!Number.isInteger(score)||score<0||score>100){window.alert('Enter a whole number from 0 to 100.');return}const remarks=window.prompt('Registrar remarks (optional):',result.registrar_remarks||'');if(remarks===null)return;await api.patch(`/admin/results/${result.id}/approve`,{official_score:score,registrar_remarks:remarks,version:result.result_version});await load(page.value.current_page||1)};
const publishResult=async result=>{if(!window.confirm(`Publish the official result for ${result.student?.first_name} ${result.student?.last_name}? The applicant will be able to see it.`))return;await api.patch(`/admin/results/${result.id}/publish`,{version:result.result_version});await load(page.value.current_page||1)};
const publishAllApproved=async()=>{if(!window.confirm('Publish ALL approved official results? Every approved applicant will be able to see their result immediately.'))return;publishingAll.value=true;feedback.value='';try{const{data}=await api.patch('/admin/results/publish-approved');feedback.value=data.message;await load(1)}catch(error){feedback.value=errorMessage(error)}finally{publishingAll.value=false}};
const downloadTemplate=async()=>{downloadingTemplate.value=true;feedback.value='';try{const response=await api.get('/admin/results/import-template',{responseType:'blob'});const disposition=response.headers['content-disposition']||'',match=disposition.match(/filename="?([^";]+)"?/i);const filename=match?.[1]||'cdm-registrar-import-template.csv';const url=URL.createObjectURL(response.data),link=document.createElement('a');link.href=url;link.download=filename;link.click();URL.revokeObjectURL(url);feedback.value='The latest applicant template was downloaded.'}catch(error){feedback.value=errorMessage(error)}finally{downloadingTemplate.value=false}};
onMounted(load);
</script>
<style scoped>
.results-filters{padding:24px;border-top:1px solid #e2e8e4;background:#fafcfb}.filter-heading h2{margin:0;font-size:16px;color:#153e28}.filter-heading p{margin:5px 0 18px;font-size:13px;color:#647168;line-height:1.5}.filter-fields{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,.8fr) minmax(0,1fr);gap:20px;align-items:center}.filter-field{display:grid;gap:8px;min-width:0;font-size:13px;font-weight:600;color:#34483b}.filter-field input{width:100%;min-width:0;box-sizing:border-box;height:44px;padding:10px 12px;border:1px solid #cbd8cf;border-radius:6px;background:white;font-size:14px;font-weight:400}.filter-check{display:flex;align-items:flex-start;gap:10px;cursor:pointer;min-width:0}.filter-check input[type="checkbox"]{width:17px;height:17px;min-width:17px;flex:0 0 17px;margin:2px 0 0;padding:0;accent-color:#175632}.filter-check strong{display:block;font-size:13px;font-weight:600;line-height:1.5;color:#284532}.filter-check small{display:block;margin-top:3px;font-size:12px;font-weight:400;line-height:1.5;color:#647168}.decision-filter{margin-top:20px;padding:14px 16px;background:white;border:1px solid #dce5df;border-radius:6px}.decision-filter.is-selected{background:#edf7f0;border-color:#80a98c}.filter-field input:focus-visible,.filter-check input:focus-visible{outline:2px solid #175632;outline-offset:3px}@media(max-width:900px){.filter-fields{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}.filter-fields>.filter-check{grid-column:1/-1}}@media(max-width:600px){.results-filters{padding:18px}.filter-fields{grid-template-columns:minmax(0,1fr);gap:16px}}
.import-panel{margin-bottom:22px;padding:24px;display:grid;gap:18px}.import-copy{max-width:850px}.import-panel h2{margin:0 0 7px;font-size:22px;line-height:1.2;color:#153e28}.import-panel p{margin:0;color:#647168;font-size:13px;line-height:1.6}.import-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.file-picker{height:42px;display:inline-flex;align-items:center;padding:0 17px;border:1px solid #aec0b4;background:#f8faf8;color:#174b2e;font-size:12px;font-weight:800;cursor:pointer;white-space:nowrap}.file-picker:hover{background:#edf4ef}.file-picker input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}.file-name{width:190px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#657269;font-size:12px}.action-button{height:42px;padding:0 20px;min-width:165px}.publish-all{margin-left:auto;white-space:nowrap}.feedback{padding:11px;background:#edf7f0;color:#24603c;font-size:12px}.import-errors{margin:0;padding-left:20px;color:#9e3030;font-size:12px}.actions{display:flex;gap:6px;align-items:center}.status.pending{background:#f5f0dc;color:#806615}.status.approved{background:#e8efff;color:#315a9e}.status.published{background:#e4f4e9;color:#22613a}button:disabled{opacity:.5;cursor:not-allowed}@media(max-width:700px){.import-panel{padding:18px}.import-panel h2{font-size:19px}.import-actions{align-items:stretch}.file-picker,.action-button{justify-content:center;width:100%}.file-name{width:100%;text-align:center}.publish-all{margin-left:0;width:100%}}
</style>
