<template>
  <div>
    <h1 class="text-2xl font-bold text-white mb-6">My Tickets</h1>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="tickets.length === 0" class="text-center py-16 glass rounded-2xl border border-slate-700/50">
      <svg class="w-16 h-16 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
      </svg>
      <p class="text-slate-400 text-lg mb-2">No tickets yet</p>
      <p class="text-slate-500 mb-4">Browse lotteries and buy tickets to win prizes!</p>
      <router-link to="/browse" class="btn-primary">
        Browse Lotteries
      </router-link>
    </div>

    <!-- Grouped Tickets List -->
    <div v-else class="space-y-4">
      <div
        v-for="group in groupedTickets"
        :key="group.lottery.id"
        :class="[
          'glass rounded-xl p-6 border',
          isWinnerGroup(group) ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-slate-700/50'
        ]"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <!-- Image -->
          <div class="w-20 h-20 bg-slate-700 rounded-xl overflow-hidden shrink-0">
            <img
              v-if="group.lottery?.images?.length"
              :src="group.lottery.images[0].url"
              :alt="group.lottery?.title"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2 mb-1">
              <h3 class="text-lg font-semibold text-white truncate">{{ group.lottery?.title }}</h3>
              <span
                v-if="isWinnerGroup(group)"
                class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500 text-white shrink-0"
              >
                Winner!
              </span>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-sm">
              <span class="text-slate-400">
                <span class="text-white font-semibold">{{ group.count }}</span> {{ group.count === 1 ? 'ticket' : 'tickets' }}
              </span>
              <span class="text-slate-400">
                Purchased: {{ formatDate(group.firstPurchase) }}
              </span>
              <span
                :class="[
                  'px-2 py-0.5 rounded-full text-xs font-medium',
                  lotteryStatusClass(group.lottery?.status)
                ]"
              >
                {{ lotteryStatusLabel(group.lottery?.status) }}
              </span>
            </div>
          </div>

          <!-- View Button -->
          <router-link
            :to="`/lottery/${group.lottery?.id}`"
            class="btn-outline py-2 px-4 shrink-0"
          >
            View Lottery
          </router-link>
        </div>

        <!-- Winner Celebration -->
        <div v-if="isWinnerGroup(group)" class="mt-4 p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/30">
          <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
            </svg>
            <p class="text-emerald-400 font-medium">
              Congratulations! You won this lottery!
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLotteryStore } from '../../stores/lottery'
import { useAuthStore } from '../../stores/auth'

const lotteryStore = useLotteryStore()
const authStore = useAuthStore()
const loading = ref(true)
const tickets = ref([])

// Group tickets by lottery
const groupedTickets = computed(() => {
  const groups = {}
  
  for (const ticket of tickets.value) {
    const lotteryId = ticket.lottery?.id
    if (!lotteryId) continue
    
    if (!groups[lotteryId]) {
      groups[lotteryId] = {
        lottery: ticket.lottery,
        count: 0,
        firstPurchase: ticket.purchased_at,
        tickets: []
      }
    }
    
    groups[lotteryId].count++
    groups[lotteryId].tickets.push(ticket)
    
    // Track earliest purchase date
    if (new Date(ticket.purchased_at) < new Date(groups[lotteryId].firstPurchase)) {
      groups[lotteryId].firstPurchase = ticket.purchased_at
    }
  }
  
  // Convert to array and sort by most recent purchase first
  return Object.values(groups).sort((a, b) => 
    new Date(b.firstPurchase) - new Date(a.firstPurchase)
  )
})

const isWinnerGroup = (group) => {
  return group.lottery?.winner_user_id === authStore.user?.id
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const lotteryStatusClass = (status) => {
  switch (status) {
    case 'active':
      return 'bg-emerald-500/20 text-emerald-400'
    case 'completed':
      return 'bg-slate-500/20 text-slate-400'
    default:
      return 'bg-slate-500/20 text-slate-400'
  }
}

const lotteryStatusLabel = (status) => {
  switch (status) {
    case 'active':
      return 'Active'
    case 'completed':
      return 'Ended'
    default:
      return status
  }
}

onMounted(async () => {
  try {
    await lotteryStore.fetchUserTickets()
    tickets.value = lotteryStore.userTickets
  } finally {
    loading.value = false
  }
})
</script>

