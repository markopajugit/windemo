<template>
  <div>
    <h1 class="text-2xl font-bold text-white mb-6">Admin Dashboard</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Pending Approval</p>
        <p class="text-2xl font-bold text-amber-400 mt-1">{{ stats.pending }}</p>
      </div>
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Active Lotteries</p>
        <p class="text-2xl font-bold text-emerald-400 mt-1">{{ stats.active }}</p>
      </div>
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Total Lotteries</p>
        <p class="text-2xl font-bold text-indigo-400 mt-1">{{ stats.total }}</p>
      </div>
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Completed</p>
        <p class="text-2xl font-bold text-slate-400 mt-1">{{ stats.completed }}</p>
      </div>
    </div>

    <!-- Recent Pending -->
    <div class="glass rounded-xl p-6 border border-slate-700/50">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-white">Recent Pending Lotteries</h2>
        <router-link to="/admin/pending" class="text-amber-400 hover:text-amber-300 text-sm">
          View All
        </router-link>
      </div>

      <div v-if="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-amber-500"></div>
      </div>

      <div v-else-if="pendingLotteries.length === 0" class="text-center py-8">
        <svg class="w-12 h-12 mx-auto text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-slate-400">No pending lotteries</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="lottery in pendingLotteries"
          :key="lottery.id"
          class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl"
        >
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-slate-700 rounded-lg overflow-hidden shrink-0">
              <img
                v-if="lottery.images?.length"
                :src="lottery.images[0].url"
                class="w-full h-full object-cover"
              />
            </div>
            <div>
              <p class="text-white font-medium">{{ lottery.title }}</p>
              <p class="text-slate-400 text-sm">by {{ lottery.user?.name }}</p>
            </div>
          </div>
          <router-link
            :to="`/admin/lottery/${lottery.id}`"
            class="btn-outline py-2 px-4 text-sm"
          >
            Review
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '../../api'

const loading = ref(true)
const stats = ref({
  pending: 0,
  active: 0,
  total: 0,
  completed: 0,
})
const pendingLotteries = ref([])

onMounted(async () => {
  try {
    const [statsResponse, lotteriesResponse] = await Promise.all([
      adminApi.getStats(),
      adminApi.getAllLotteries({ status: 'pending', limit: 5 }),
    ])
    
    stats.value = statsResponse.data
    pendingLotteries.value = lotteriesResponse.data.data || lotteriesResponse.data
  } catch (error) {
    console.error('Failed to load admin data:', error)
  } finally {
    loading.value = false
  }
})
</script>

