<template>
  <div class="space-y-8 fade-in">
    <section v-if="loading" class="school-card p-8 text-center text-gray-600">Checking official result status...</section>
    <section v-else-if="loadError" role="alert" class="school-card p-8">{{ loadError }} <button @click="loadResults" class="underline">Retry</button></section><section v-else-if="!completed" class="school-card p-8 md:p-12 text-center">
      <h1 class="text-3xl font-bold mb-4" style="font-family:var(--font-heading);color:var(--color-school-green);">No completed exam yet</h1>
      <p class="text-gray-600 mb-8">Complete the entrance examination before checking its status.</p>
      <router-link to="/exam" class="inline-flex items-center px-8 py-3 rounded-md font-semibold text-white" style="background:var(--color-school-green);">Go to Examination</router-link>
    </section>
    <section v-else-if="officialResult" class="school-card p-8 md:p-12 text-center">
      <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center text-white text-3xl mb-6" style="background:var(--color-school-green);">✓</div>
      <p class="text-xs font-semibold uppercase tracking-widest mb-3" style="color:#9a7d13;">Official CDM Registrar Result</p>
      <h1 class="text-3xl md:text-4xl font-bold mb-3" style="font-family:var(--font-heading);color:var(--color-school-green);">{{ officialResult.outcome }}</h1>
      <p v-if="officialResult.score !== null" class="official-score">{{ officialResult.score }}<span>/100</span></p>
      <div v-if="officialResult.outcome === 'RETAKE'" class="mb-6">
        <p class="text-gray-700 mb-3">You have one retake available. This will be your final attempt.</p>
        <router-link to="/exam" class="result-button">Retake Examination</router-link>
      </div>
      <p v-else-if="officialResult.outcome === 'FAILED'" class="text-gray-700 mb-6">You have used your one allowed retake. No further exam attempts are available.</p>
      <p v-if="officialResult.remarks" class="text-gray-600 max-w-xl mx-auto mb-6">{{ officialResult.remarks }}</p>
      <p class="text-xs text-gray-500 mb-8">Published by the CDM Registrar on {{ formatDate(officialResult.published_at) }}</p>
      <div class="max-w-xl mx-auto mb-6">
        <h2 class="text-lg font-semibold mb-2" style="color: var(--color-school-green);">Discover the programs that match you</h2>
        <p class="text-sm text-gray-700 leading-relaxed">Your assessment is just the beginning. Click <strong>View Program Recommendation</strong> below, rate your interests, and select <strong>Generate My AI Guidance</strong> to explore your highest-matching programs and receive personalized guidance for your next steps.</p>
      </div>
      <p v-if="officialResult.outcome === 'PASSED'" class="text-sm text-gray-700 leading-relaxed max-w-xl mx-auto mb-8">Please visit the CDM Registrar’s Office as soon as possible for guidance on the next steps in your admission process.</p>
      <div class="flex flex-wrap justify-center gap-3"><router-link to="/recommendation" class="result-button">View Program Recommendation</router-link><a href="https://www.facebook.com/profile.php?id=100095451585898" target="_blank" rel="noopener noreferrer" class="result-button">Visit Colegio de Montalban on Facebook</a></div>
    </section>
    <section v-else class="school-card p-8 md:p-12 text-center">
      <div class="mx-auto pending-icon">⌛</div>
      <p class="text-xs font-semibold uppercase tracking-widest mb-3" style="color:#9a7d13;">Registrar verification</p>
      <h1 class="text-3xl md:text-4xl font-bold mb-4" style="font-family:var(--font-heading);color:var(--color-school-green);">Official result not published yet</h1>
      <p class="text-gray-600 max-w-xl mx-auto mb-3">{{ message }}</p>
      <p class="text-sm text-gray-500 mb-8">Current status: <strong class="uppercase">{{ officialStatus }}</strong></p>
      <div class="flex flex-wrap justify-center gap-3"><router-link to="/recommendation" class="result-button">View Program Recommendation</router-link><a href="https://www.facebook.com/profile.php?id=100095451585898" target="_blank" rel="noopener noreferrer" class="result-button">Visit Colegio de Montalban on Facebook</a></div>
    </section>
  </div>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import api from '../utils/api.js';
const loadError=ref('');
const loading=ref(true),completed=ref(false),officialStatus=ref('pending'),officialResult=ref(null),message=ref('Your submission is awaiting publication by the CDM Registrar.');
const formatDate=value=>value?new Date(value).toLocaleDateString(undefined,{year:'numeric',month:'long',day:'numeric'}):'—';
const loadResults=async()=>{loading.value=true;loadError.value='';try{const{data}=await api.get('/exam/result');completed.value=Boolean(data.exam_completed);officialStatus.value=data.official_status||'pending';officialResult.value=data.official_result||null;message.value=data.message||message.value}catch(error){if(error.response?.status!==404)loadError.value=error.response?.data?.message||'Unable to check the official result.'}finally{loading.value=false}};onMounted(loadResults);
</script>
<style scoped>.official-score{font-size:54px;font-weight:800;color:var(--color-school-green);margin:12px 0}.official-score span{font-size:20px;color:#718077}.pending-icon{width:80px;height:80px;border-radius:50%;display:grid;place-items:center;background:#f4e8b4;font-size:30px;margin-bottom:24px}.result-button{display:inline-flex;align-items:center;padding:12px 24px;border-radius:6px;font-weight:600;color:white;background:var(--color-school-green)}</style>
