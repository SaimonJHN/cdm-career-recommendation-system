<template>
  <form @submit.prevent="submit" class="school-card p-8 max-w-lg mx-auto my-10 space-y-4">
    <h1 class="text-2xl font-bold">{{ resetting ? 'Set a new password' : 'Forgot password' }}</h1>
    <label class="block">Email<input v-model="email" required type="email" autocomplete="email" class="block border p-2 w-full"></label>
    <template v-if="resetting">
      <label class="block">New password<input v-model="password" required type="password" minlength="8" maxlength="128" autocomplete="new-password" class="block border p-2 w-full"></label>
      <label class="block">Confirm password<input v-model="confirmation" required type="password" minlength="8" maxlength="128" autocomplete="new-password" class="block border p-2 w-full"></label>
    </template>
    <p v-if="message" role="status">{{ message }}</p>
    <button :disabled="busy" class="bg-green-900 text-white rounded px-5 py-3">{{ busy ? 'Please wait...' : resetting ? 'Reset password' : 'Send reset link' }}</button>
    <router-link to="/login" class="block underline">Back to sign in</router-link>
  </form>
</template>
<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../utils/api.js';
const route=useRoute(),resetting=!!route.query.token,email=ref(String(route.query.email||'')),password=ref(''),confirmation=ref(''),message=ref(''),busy=ref(false);
const submit=async()=>{busy.value=true;message.value='';try{const{data}=await api.post(resetting?'/auth/reset-password':'/auth/forgot-password',{email:email.value,token:route.query.token,password:password.value,password_confirmation:confirmation.value});message.value=data.message;password.value='';confirmation.value=''}catch(e){message.value=e.response?.data?.message||'Unable to complete the request. Please try again.'}finally{busy.value=false}};
</script>
