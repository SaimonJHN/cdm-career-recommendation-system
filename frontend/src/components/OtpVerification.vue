<template>
  <div>
    <div class="flex items-center space-x-2 mb-6">
      <div class="h-px flex-1 bg-gray-200"></div>
      <span class="text-xs font-semibold uppercase tracking-widest text-gray-500">Email Verification</span>
      <div class="h-px flex-1 bg-gray-200"></div>
    </div>

    <div class="text-center mb-6">
      <div
        class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-xl"
        style="background: #ecfdf5; color: var(--color-school-green);"
        aria-hidden="true"
      >
        &#9993;
      </div>
      <h2 class="text-xl font-bold text-gray-900">{{ title }}</h2>
      <p class="text-sm text-gray-600 mt-2">
        Enter the six-digit code sent to
        <strong class="block mt-1 text-gray-800">{{ maskedEmail }}</strong>
      </p>
    </div>

    <form class="space-y-4" @submit.prevent="submitCode">
      <div>
        <label for="otp-code" class="block text-sm font-medium text-gray-700 mb-1.5 text-center">
          Verification code
        </label>
        <input
          id="otp-code"
          ref="otpInput"
          :value="otpCode"
          type="text"
          inputmode="numeric"
          autocomplete="one-time-code"
          pattern="[0-9]*"
          maxlength="6"
          placeholder="000000"
          class="otp-input w-full px-4 py-3 border border-gray-300 rounded-md text-center text-xl font-semibold transition"
          :disabled="loading || resending"
          aria-describedby="otp-expiry"
          required
          @input="handleInput"
        />
      </div>

      <p id="otp-expiry" class="text-xs text-center" :class="isExpired ? 'text-red-700' : 'text-gray-500'">
        {{ isExpired ? 'This code has expired. Request a new code below.' : `Code expires in ${expiryText}.` }}
      </p>

      <div
        v-if="error"
        class="p-3 border rounded-md text-sm"
        style="background: #fef2f2; border-color: #fecaca; color: #991b1b;"
        role="alert"
      >
        {{ error }}
      </div>

      <div
        v-if="statusMessage"
        class="p-3 border rounded-md text-sm"
        style="background: #ecfdf5; border-color: #a7f3d0; color: #14532d;"
        role="status"
      >
        {{ statusMessage }}
      </div>

      <button
        type="submit"
        :disabled="loading || resending || otpCode.length !== 6 || isExpired"
        class="w-full py-2.5 rounded-md font-semibold text-white text-sm transition disabled:opacity-60"
        style="background: var(--color-school-green);"
      >
        {{ loading ? 'Verifying...' : submitLabel }}
      </button>
    </form>

    <div class="mt-5 text-center space-y-3">
      <p class="text-sm text-gray-600">
        Didn't receive the code?
        <button
          type="button"
          class="font-semibold hover:underline disabled:opacity-50 disabled:no-underline"
          style="color: var(--color-school-green);"
          :disabled="loading || resending || resendRemaining > 0"
          @click="requestResend"
        >
          {{ resendButtonText }}
        </button>
      </p>

      <button
        type="button"
        class="text-sm font-medium text-gray-500 hover:text-gray-800 disabled:opacity-50"
        :disabled="loading || resending"
        @click="$emit('back')"
      >
        &larr; Back and change details
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Check your email',
  },
  maskedEmail: {
    type: String,
    required: true,
  },
  submitLabel: {
    type: String,
    default: 'Verify',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  resending: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  statusMessage: {
    type: String,
    default: '',
  },
  expiresAt: {
    type: Number,
    required: true,
  },
  resendAvailableAt: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['verify', 'resend', 'back']);

const otpCode = ref('');
const otpInput = ref(null);
const now = ref(Date.now());
let timerId = null;

const secondsUntil = (timestamp) => Math.max(0, Math.ceil((timestamp - now.value) / 1000));
const expiryRemaining = computed(() => secondsUntil(props.expiresAt));
const resendRemaining = computed(() => secondsUntil(props.resendAvailableAt));
const isExpired = computed(() => expiryRemaining.value === 0);

const expiryText = computed(() => {
  const minutes = Math.floor(expiryRemaining.value / 60);
  const seconds = String(expiryRemaining.value % 60).padStart(2, '0');
  return `${minutes}:${seconds}`;
});

const resendButtonText = computed(() => {
  if (props.resending) return 'Sending...';
  if (resendRemaining.value > 0) return `Resend in ${resendRemaining.value}s`;
  return 'Resend code';
});

const handleInput = (event) => {
  const digits = event.target.value.replace(/\D/g, '').slice(0, 6);
  otpCode.value = digits;
  event.target.value = digits;
};

const submitCode = () => {
  if (otpCode.value.length !== 6 || isExpired.value || props.loading || props.resending) return;
  emit('verify', otpCode.value);
};

const requestResend = () => {
  if (resendRemaining.value > 0 || props.loading || props.resending) return;
  otpCode.value = '';
  emit('resend');
  nextTick(() => otpInput.value?.focus());
};

watch(() => props.resending, (isResending, wasResending) => {
  if (wasResending && !isResending) {
    nextTick(() => otpInput.value?.focus());
  }
});

onMounted(() => {
  timerId = window.setInterval(() => {
    now.value = Date.now();
  }, 1000);
  nextTick(() => otpInput.value?.focus());
});

onBeforeUnmount(() => {
  if (timerId) window.clearInterval(timerId);
});
</script>

<style scoped>
.otp-input {
  letter-spacing: 0.45em;
  padding-left: calc(1rem + 0.45em);
}
</style>
