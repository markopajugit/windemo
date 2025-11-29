<template>
  <div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-md">
      <div class="glass rounded-3xl p-8 border border-slate-700/50">
        <div class="text-center mb-8">
          <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-white font-bold text-2xl">W</span>
          </div>
          <h1 class="text-2xl font-bold text-white">Welcome Back</h1>
          <p class="text-slate-400 mt-2">Sign in to your account</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="input-field"
              placeholder="you@example.com"
            />
          </div>

          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="input-field"
              placeholder="••••••••"
            />
          </div>

          <div v-if="error" class="p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-400 text-sm">
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <p class="text-center text-slate-400 mt-6">
          Don't have an account?
          <router-link to="/register" class="text-indigo-400 hover:text-indigo-300 font-medium">
            Sign up
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    await authStore.login(form.value)
    const redirect = route.query.redirect || '/dashboard'
    router.push(redirect)
  } catch (e) {
    error.value = e.response?.data?.message || 'Invalid credentials'
  } finally {
    loading.value = false
  }
}
</script>

