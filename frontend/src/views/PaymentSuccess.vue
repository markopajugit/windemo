<template>
  <div class="min-h-[60vh] flex items-center justify-center">
    <div class="glass rounded-3xl p-8 md:p-12 max-w-lg w-full text-center border border-slate-700/50">
      <!-- Loading State -->
      <div v-if="loading" class="py-8">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-indigo-500 mx-auto mb-4"></div>
        <p class="text-slate-400">{{ $t('payment.verifying') }}</p>
      </div>

      <!-- Success State -->
      <div v-else-if="verified" class="animate-fade-in">
        <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-4">{{ $t('payment.successTitle') }}</h1>
        <p class="text-slate-400 mb-2">
          {{ $t('payment.successMessage') }}
        </p>
        <p class="text-slate-400 mb-8" v-if="ticketCount">
          {{ $t('payment.ticketsPurchased', { count: ticketCount }) }}
        </p>
        <p class="text-slate-500 text-sm mb-8">
          {{ $t('payment.ticketsAdded') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link to="/dashboard/my-tickets" class="btn-primary">
            {{ $t('payment.viewMyTickets') }}
          </router-link>
          <router-link to="/browse" class="btn-outline">
            {{ $t('payment.browseMore') }}
          </router-link>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="animate-fade-in">
        <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-4">{{ $t('payment.verificationIssue') }}</h1>
        <p class="text-slate-400 mb-8">
          {{ $t('payment.verificationMessage') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link to="/dashboard/my-tickets" class="btn-primary">
            {{ $t('payment.checkMyTickets') }}
          </router-link>
          <router-link to="/" class="btn-outline">
            {{ $t('payment.goHome') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { paymentApi } from '../api'

const route = useRoute()
const loading = ref(true)
const verified = ref(false)
const ticketCount = ref(null)

onMounted(async () => {
  const sessionId = route.query.session_id
  
  if (!sessionId) {
    loading.value = false
    return
  }

  try {
    const response = await paymentApi.verifySession(sessionId)
    if (response.data.status === 'paid') {
      verified.value = true
      ticketCount.value = response.data.quantity
    }
  } catch (error) {
    console.error('Failed to verify payment:', error)
  } finally {
    loading.value = false
  }
})
</script>

