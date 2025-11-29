<template>
  <div>
    <h1 class="text-2xl font-bold text-white mb-6">Dashboard Overview</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Active Lotteries</p>
        <p class="text-2xl font-bold text-white mt-1">{{ stats.activeLotteries }}</p>
      </div>
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Total Tickets Sold</p>
        <p class="text-2xl font-bold text-indigo-400 mt-1">{{ stats.ticketsSold }}</p>
      </div>
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Tickets Purchased</p>
        <p class="text-2xl font-bold text-purple-400 mt-1">{{ stats.ticketsPurchased }}</p>
      </div>
      <div class="glass rounded-xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-sm">Lotteries Won</p>
        <p class="text-2xl font-bold text-emerald-400 mt-1">{{ stats.lotteriesWon }}</p>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass rounded-xl p-6 border border-slate-700/50 mb-8">
      <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
      <div class="flex flex-wrap gap-4">
        <router-link to="/dashboard/create-lottery" class="btn-primary">
          Create New Lottery
        </router-link>
        <router-link to="/browse" class="btn-outline">
          Browse Lotteries
        </router-link>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="glass rounded-xl p-6 border border-slate-700/50">
      <h2 class="text-lg font-semibold text-white mb-4">Recent Activity</h2>
      
      <div v-if="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
      </div>

      <div v-else-if="recentActivity.length === 0" class="text-center py-8">
        <p class="text-slate-400">No recent activity</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="activity in recentActivity"
          :key="activity.id"
          class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl"
        >
          <div class="flex items-center space-x-4">
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center',
                activity.type === 'ticket' ? 'bg-indigo-500/20' : 'bg-emerald-500/20'
              ]"
            >
              <svg v-if="activity.type === 'ticket'" class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
              </svg>
              <svg v-else class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-white">{{ activity.description }}</p>
              <p class="text-slate-400 text-sm">{{ formatDate(activity.created_at) }}</p>
            </div>
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

const stats = ref({
  activeLotteries: 0,
  ticketsSold: 0,
  ticketsPurchased: 0,
  lotteriesWon: 0,
})

const recentActivity = ref([])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(async () => {
  try {
    await Promise.all([
      lotteryStore.fetchUserLotteries(),
      lotteryStore.fetchUserTickets(),
    ])
    
    // Calculate stats
    const lotteries = lotteryStore.userLotteries
    const tickets = lotteryStore.userTickets
    
    stats.value.activeLotteries = lotteries.filter(l => l.status === 'active').length
    stats.value.ticketsSold = lotteries.reduce((sum, l) => sum + (l.tickets_sold || 0), 0)
    stats.value.ticketsPurchased = tickets.length
    stats.value.lotteriesWon = tickets.filter(t => t.lottery?.winner_user_id === t.user_id).length
    
    // Build recent activity
    const activities = []
    
    tickets.slice(0, 5).forEach(ticket => {
      activities.push({
        id: `ticket-${ticket.id}`,
        type: 'ticket',
        description: `Purchased ticket for "${ticket.lottery?.title}"`,
        created_at: ticket.purchased_at,
      })
    })
    
    recentActivity.value = activities.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5)
  } finally {
    loading.value = false
  }
})
</script>

