<template><section class="school-card p-6 space-y-3"><h2 class="text-xl font-bold">Account sessions</h2><p>Your current session stays open. Other devices will need to sign in again.</p><button :disabled="busy" class="underline" @click="revoke">Sign out other devices</button><p role="status">{{ message }}</p></section></template>
<script setup>
import {ref} from 'vue';import api from '../utils/api.js';
const busy=ref(false),message=ref('');
const revoke=async()=>{if(!window.confirm('Sign out all other sessions and remove trusted-device access?'))return;busy.value=true;try{message.value=(await api.post('/auth/revoke-other-sessions')).data.message}catch{message.value='Unable to sign out other devices. Try again.'}finally{busy.value=false}};
</script>
