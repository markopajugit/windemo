<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">My Lotteries</h1>
      <router-link to="/dashboard/create-lottery" class="btn-primary">
        Create Lottery
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="lotteries.length === 0" class="text-center py-16 glass rounded-2xl border border-slate-700/50">
      <svg class="w-16 h-16 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
      <p class="text-slate-400 text-lg mb-2">No lotteries yet</p>
      <p class="text-slate-500 mb-4">Create your first lottery to get started</p>
      <router-link to="/dashboard/create-lottery" class="btn-primary">
        Create Lottery
      </router-link>
    </div>

    <!-- Lottery List -->
    <div v-else class="space-y-4">
      <div
        v-for="lottery in lotteries"
        :key="lottery.id"
        class="glass rounded-xl p-6 border border-slate-700/50"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <!-- Image -->
          <div class="w-24 h-24 bg-slate-700 rounded-xl overflow-hidden shrink-0">
            <img
              v-if="lottery.images?.length"
              :src="lottery.images[0].url"
              :alt="lottery.title"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2 mb-1">
              <h3 class="text-lg font-semibold text-white truncate">{{ lottery.title }}</h3>
              <span
                :class="[
                  'px-2 py-0.5 rounded-full text-xs font-medium shrink-0',
                  statusClass(lottery.status)
                ]"
              >
                {{ statusLabel(lottery.status) }}
              </span>
            </div>
            <p class="text-slate-400 text-sm line-clamp-2 mb-2">{{ lottery.description }}</p>
            <div class="flex items-center space-x-4 text-sm">
              <span class="text-slate-400">
                <span class="text-white font-medium">{{ lottery.tickets_sold || 0 }}</span>
                / {{ lottery.total_tickets }} tickets
              </span>
              <span class="text-slate-400">
                €<span class="text-indigo-400 font-medium">{{ formatPrice(lottery.ticket_price) }}</span>
                per ticket
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center space-x-2 shrink-0">
            <router-link
              :to="`/lottery/${lottery.id}`"
              class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-colors"
              title="View"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </router-link>
            <router-link
              v-if="lottery.status === 'pending'"
              :to="`/dashboard/edit-lottery/${lottery.id}`"
              class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-colors"
              title="Edit"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </router-link>
            <button
              v-if="lottery.status === 'pending'"
              @click="deleteLottery(lottery.id)"
              class="p-2 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 transition-colors"
              title="Delete"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLotteryStore } from '../../stores/lottery'

const lotteryStore = useLotteryStore()
const loading = ref(true)
const lotteries = ref([])

const statusClass = (status) => {
  switch (status) {
    case 'active':
      return 'bg-emerald-500/20 text-emerald-400'
    case 'pending':
      return 'bg-amber-500/20 text-amber-400'
    case 'approved':
      return 'bg-blue-500/20 text-blue-400'
    case 'completed':
      return 'bg-slate-500/20 text-slate-400'
    case 'cancelled':
      return 'bg-red-500/20 text-red-400'
    default:
      return 'bg-slate-500/20 text-slate-400'
  }
}

const statusLabel = (status) => {
  switch (status) {
    case 'active':
      return 'Live'
    case 'pending':
      return 'Pending'
    case 'approved':
      return 'Approved'
    case 'completed':
      return 'Ended'
    case 'cancelled':
      return 'Cancelled'
    default:
      return status
  }
}

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2)
}

const deleteLottery = async (id) => {
  if (!confirm('Are you sure you want to delete this lottery?')) return
  
  try {
    await lotteryStore.deleteLottery(id)
    lotteries.value = lotteries.value.filter(l => l.id !== id)
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to delete lottery')
  }
}

onMounted(async () => {
  try {
    await lotteryStore.fetchUserLotteries()
    lotteries.value = lotteryStore.userLotteries
  } finally {
    loading.value = false
  }
})
</script>

