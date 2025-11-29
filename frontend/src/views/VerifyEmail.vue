<template>
  <div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-md">
      <div class="glass rounded-3xl p-8 border border-slate-700/50 text-center">
        <!-- Pending Verification -->
        <template v-if="!verified">
          <div class="w-20 h-20 bg-amber-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>

          <h1 class="text-2xl font-bold text-white mb-4">Verify Your Email</h1>
          <p class="text-slate-400 mb-6">
            We've sent a verification link to <span class="text-white font-medium">{{ authStore.user?.email }}</span>. 
            Please check your inbox and click the link to verify your account.
          </p>

          <div v-if="message" class="p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 text-sm mb-6">
            {{ message }}
          </div>

          <div v-if="error" class="p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-400 text-sm mb-6">
            {{ error }}
          </div>

          <button
            @click="resendVerification"
            :disabled="resending || cooldown > 0"
            class="btn-outline w-full disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="resending">Sending...</span>
            <span v-else-if="cooldown > 0">Resend in {{ cooldown }}s</span>
            <span v-else>Resend Verification Email</span>
          </button>

          <router-link to="/dashboard" class="block mt-4 text-slate-400 hover:text-white">
            Continue to Dashboard
          </router-link>
        </template>

        <!-- Verified Successfully -->
        <template v-else>
          <div class="w-20 h-20 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <h1 class="text-2xl font-bold text-white mb-4">Email Verified!</h1>
          <p class="text-slate-400 mb-6">
            Your email has been verified successfully. You can now create lotteries and purchase tickets.
          </p>

          <router-link to="/dashboard" class="btn-primary w-full inline-block">
            Go to Dashboard
          </router-link>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { authApi } from '../api'

const route = useRoute()
const authStore = useAuthStore()

const verified = ref(false)
const resending = ref(false)
const message = ref('')
const error = ref('')
const cooldown = ref(0)

let cooldownInterval = null

const resendVerification = async () => {
  resending.value = true
  error.value = ''
  message.value = ''

  try {
    await authApi.resendVerification()
    message.value = 'Verification email sent!'
    startCooldown()
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to send verification email'
  } finally {
    resending.value = false
  }
}

const startCooldown = () => {
  cooldown.value = 60
  cooldownInterval = setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) {
      clearInterval(cooldownInterval)
    }
  }, 1000)
}

const verifyEmail = async () => {
  const { id, hash } = route.params
  const { expires, signature } = route.query

  if (id && hash) {
    try {
      await authApi.verifyEmail(id, hash, { expires, signature })
      await authStore.fetchUser()
      verified.value = true
    } catch (e) {
      error.value = e.response?.data?.message || 'Verification failed'
    }
  }
}

onMounted(() => {
  // Check if user is already verified
  if (authStore.isEmailVerified) {
    verified.value = true
  }
  
  // Handle verification callback
  if (route.params.id && route.params.hash) {
    verifyEmail()
  }
})
</script>

