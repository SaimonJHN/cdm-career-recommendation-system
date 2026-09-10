<template>
  <section class="recommendation-summary school-card p-4 sm:p-6 md:p-8 border-l-4 border-green-800">
    <p class="text-xs font-semibold uppercase tracking-widest text-green-800">My Recommendation</p>
    <h2 class="text-2xl font-bold mt-2">Discover your next direction</h2>
    <p v-if="loading" class="mt-3" role="status">Checking your program guidance…</p>
    <p v-else-if="error" class="mt-3" role="alert">{{ error }} <button class="underline" @click="load">Retry</button></p>
    <template v-else>
      <p class="mt-3 text-gray-700">{{ description }}</p>
      <p v-if="topNames" class="mt-3 font-semibold text-green-800">{{ topNames }}</p>
      <router-link :to="needsExam ? '/exam' : '/recommendation'" class="inline-block mt-5 px-5 py-3 rounded bg-green-800 text-white font-semibold">{{ needsExam ? 'Take the entrance exam' : result?.status !== 'ready' ? 'Review my guidance' : hasInterests ? 'View My Recommendation' : 'Rate my interests' }}</router-link>
      <p class="mt-3 text-xs text-gray-500">Program matches guide your exploration; official exam results remain available in Exam Status.</p>
    </template>
  </section>
</template>
<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../utils/api';
const loading=ref(true),error=ref(''),needsExam=ref(false),result=ref(null),hasInterests=ref(false);
const topNames=computed(()=>hasInterests.value ? (result.value?.ranked_programs || []).filter(p=>result.value?.tied_top_codes?.includes(p.course_code)).map(p=>p.course_name).join(' • ') : '');
const description=computed(()=>needsExam.value ? 'Start with the five-topic entrance exam. Then rate your interests to explore programs that match your strengths and preferences.' : result.value?.status !== 'ready' ? (result.value?.message || 'Open your guidance to review the next steps.') : hasInterests.value ? 'Explore your highest-matching programs, review your guidance, and learn about possible career paths.' : 'Your assessment is complete. Rate your interests and generate your personalized program guidance.');
async function load(){loading.value=true;error.value='';try{const {data}=await api.get('/results/recommendation');result.value=data.recommendation;hasInterests.value=Object.keys(data.interests || {}).length>0;needsExam.value=false;}catch(e){if(e.response?.status===403)needsExam.value=true;else error.value='Unable to load your recommendation.';}finally{loading.value=false;}}
onMounted(load);
</script>

<style scoped>
.recommendation-summary{min-width:0;overflow-wrap:anywhere}.recommendation-summary>a{max-width:100%;text-align:center}@media(max-width:600px){.recommendation-summary{font-size:14px;line-height:1.6}.recommendation-summary h2{font-size:21px;line-height:1.3}.recommendation-summary>a{display:block;padding:12px;font-size:14px}}
</style>