<template>
  <div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-md">
      <div class="glass rounded-3xl p-8 border border-slate-700/50">
        <div class="text-center mb-8">
          <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-white font-bold text-2xl">W</span>
          </div>
          <h1 class="text-2xl font-bold text-white">Create Account</h1>
          <p class="text-slate-400 mt-2">Join win24 and start winning!</p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-6">
          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="input-field"
              placeholder="John Doe"
            />
          </div>

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
              minlength="8"
              class="input-field"
              placeholder="••••••••"
            />
            <p class="text-slate-500 text-xs mt-1">Minimum 8 characters</p>
          </div>

          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
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
              Creating account...
            </span>
            <span v-else>Create Account</span>
          </button>
        </form>

        <p class="text-center text-slate-400 mt-6">
          Already have an account?
          <router-link to="/login" class="text-indigo-400 hover:text-indigo-300 font-medium">
            Sign in
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const loading = ref(false)
const error = ref('')

const handleRegister = async () => {
  loading.value = true
  error.value = ''

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    loading.value = false
    return
  }

  try {
    await authStore.register(form.value)
    router.push('/verify-email')
  } catch (e) {
    if (e.response?.data?.errors) {
      const errors = Object.values(e.response.data.errors).flat()
      error.value = errors.join('. ')
    } else {
      error.value = e.response?.data?.message || 'Registration failed'
    }
  } finally {
    loading.value = false
  }
}
</script>

