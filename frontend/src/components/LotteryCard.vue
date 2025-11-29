<template>
  <router-link 
    :to="`/lottery/${lottery.id}`"
    class="lottery-card block glass rounded-2xl overflow-hidden border border-slate-700/50"
    :class="{ 
      'ring-2 ring-amber-500/30': isUpcoming,
      'ring-2 ring-purple-500/30': lottery.status === 'completed'
    }"
  >
    <!-- Image -->
    <div class="relative h-48 bg-slate-700">
      <img 
        v-if="lottery.images?.length"
        :src="lottery.images[0].url"
        :alt="lottery.title"
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <svg class="w-16 h-16 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>

      <!-- Status Badge -->
      <div 
        class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-semibold"
        :class="statusClass"
      >
        {{ statusLabel }}
      </div>

      <!-- Charity Badge -->
      <div 
        v-if="lottery.charity_percentage > 0"
        class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 text-white"
      >
        {{ lottery.charity_percentage }}% to Charity
      </div>
    </div>

    <!-- Content -->
    <div class="p-5">
      <h3 class="text-lg font-semibold text-white mb-2 line-clamp-1">
        {{ lottery.title }}
      </h3>
      
      <p class="text-slate-400 text-sm mb-4 line-clamp-2">
        {{ lottery.description }}
      </p>

      <!-- Price Info -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <p class="text-slate-500 text-xs">Product Value</p>
          <p class="text-white font-semibold">€{{ formatPrice(lottery.product_value) }}</p>
        </div>
        <div class="text-right">
          <p class="text-slate-500 text-xs">Ticket Price</p>
          <p class="text-indigo-400 font-semibold">€{{ formatPrice(lottery.ticket_price) }}</p>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="mb-4">
        <div class="flex justify-between text-xs text-slate-400 mb-1">
          <span>{{ lottery.tickets_sold || 0 }} / {{ lottery.total_tickets }} tickets</span>
          <span>{{ progressPercentage }}%</span>
        </div>
        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
          <div 
            class="h-full rounded-full transition-all duration-500"
            :class="progressBarClass"
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>
      </div>

      <!-- Countdown / Status -->
      <CountdownTimer 
        v-if="isUpcoming"
        :end-time="lottery.starts_at"
        label="Starts in"
        class="text-sm"
      />
      <CountdownTimer 
        v-else-if="lottery.status === 'active'"
        :end-time="lottery.ends_at"
        label="Ends in"
        class="text-sm"
      />
      <!-- Winner Display -->
      <div v-else-if="lottery.status === 'completed'" class="flex items-center gap-2">
        <div class="flex items-center gap-2 px-3 py-1.5 bg-purple-500/20 rounded-lg border border-purple-500/30">
          <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
          </svg>
          <span class="text-purple-400 text-sm font-medium">
            Winner: {{ lottery.winner?.name || 'Selected' }}
          </span>
        </div>
      </div>
      <p v-else-if="lottery.status === 'pending'" class="text-amber-400 text-sm font-medium">
        Awaiting Approval
      </p>
    </div>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import CountdownTimer from './CountdownTimer.vue'

const props = defineProps({
  lottery: {
    type: Object,
    required: true,
  },
})

// Check if lottery hasn't started yet (upcoming)
const isUpcoming = computed(() => {
  if (!props.lottery.starts_at) return false
  return new Date(props.lottery.starts_at) > new Date()
})

const progressPercentage = computed(() => {
  if (!props.lottery.total_tickets) return 0
  return Math.round((props.lottery.tickets_sold || 0) / props.lottery.total_tickets * 100)
})

const progressBarClass = computed(() => {
  if (isUpcoming.value) return 'bg-gradient-to-r from-amber-500 to-orange-500'
  if (props.lottery.status === 'completed') return 'bg-gradient-to-r from-purple-500 to-pink-500'
  return 'bg-gradient-to-r from-indigo-500 to-purple-500'
})

const statusClass = computed(() => {
  if (isUpcoming.value) {
    return 'bg-amber-500/90 text-slate-900'
  }
  switch (props.lottery.status) {
    case 'active':
      return 'bg-emerald-500/90 text-white'
    case 'pending':
      return 'bg-amber-500/90 text-slate-900'
    case 'completed':
      return 'bg-purple-500/90 text-white'
    case 'cancelled':
      return 'bg-red-500/90 text-white'
    default:
      return 'bg-slate-500/90 text-white'
  }
})

const statusLabel = computed(() => {
  if (isUpcoming.value) {
    return 'Upcoming'
  }
  switch (props.lottery.status) {
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
      return props.lottery.status
  }
})

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2)
}
</script>

