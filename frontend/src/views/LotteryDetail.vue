<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-24">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-24">
      <p class="text-red-400 text-xl">{{ error }}</p>
      <router-link to="/browse" class="btn-primary mt-4 inline-block">
        Back to Browse
      </router-link>
    </div>

    <!-- Lottery Details -->
    <div v-else-if="lottery" class="max-w-6xl mx-auto">
      <!-- Back Button -->
      <button @click="$router.back()" class="flex items-center text-slate-400 hover:text-white mb-6 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back
      </button>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Image Gallery -->
        <div class="space-y-4">
          <div class="aspect-square bg-slate-700 rounded-2xl overflow-hidden">
            <img
              v-if="lottery.images?.length"
              :src="lottery.images[activeImage]?.url"
              :alt="lottery.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-24 h-24 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
          
          <!-- Thumbnails -->
          <div v-if="lottery.images?.length > 1" class="grid grid-cols-4 gap-2">
            <button
              v-for="(image, index) in lottery.images"
              :key="index"
              @click="activeImage = index"
              :class="[
                'aspect-square rounded-lg overflow-hidden border-2 transition-all',
                activeImage === index ? 'border-indigo-500' : 'border-transparent opacity-70 hover:opacity-100'
              ]"
            >
              <img :src="image.url" :alt="`Image ${index + 1}`" class="w-full h-full object-cover" />
            </button>
          </div>
        </div>

        <!-- Lottery Info -->
        <div class="space-y-6">
          <!-- Status Badge -->
          <div class="flex flex-wrap items-center gap-3">
            <span
              :class="[
                'px-4 py-1 rounded-full text-sm font-semibold',
                statusClass
              ]"
            >
              {{ statusLabel }}
            </span>
            <span
              v-if="lottery.category"
              class="px-4 py-1 rounded-full text-sm font-semibold bg-slate-700 text-slate-200 border border-slate-600"
            >
              {{ categoryLabel }}
            </span>
            <span
              v-if="lottery.charity_percentage > 0"
              class="px-4 py-1 rounded-full text-sm font-semibold bg-emerald-500 text-white"
            >
              {{ lottery.charity_percentage }}% to Charity
            </span>
          </div>

          <h1 class="text-3xl font-bold text-white">{{ lottery.title }}</h1>

          <!-- Price Info -->
          <div class="glass rounded-2xl p-6 border border-slate-700/50">
            <div class="grid grid-cols-2 gap-6">
              <div>
                <p class="text-slate-400 text-sm">Product Value</p>
                <p class="text-2xl font-bold text-white">€{{ formatPrice(lottery.product_value) }}</p>
              </div>
              <div>
                <p class="text-slate-400 text-sm">Ticket Price</p>
                <p class="text-2xl font-bold text-indigo-400">€{{ formatPrice(lottery.ticket_price) }}</p>
              </div>
            </div>
          </div>

          <!-- Countdown Timer -->
          <div v-if="lottery.status === 'active'" class="glass rounded-2xl p-6 border border-slate-700/50">
            <p class="text-slate-400 text-sm mb-2">Time Remaining</p>
            <CountdownTimer :end-time="lottery.ends_at" class="text-2xl" @expired="handleExpired" />
          </div>

          <!-- Progress -->
          <div class="glass rounded-2xl p-6 border border-slate-700/50">
            <div class="flex justify-between text-sm text-slate-400 mb-2">
              <span>Tickets Sold</span>
              <span>{{ lottery.tickets_sold || 0 }} / {{ lottery.total_tickets }}</span>
            </div>
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                :style="{ width: `${progressPercentage}%` }"
              ></div>
            </div>
            <p class="text-slate-500 text-sm mt-2">{{ availableTickets }} tickets available</p>
          </div>

          <!-- Winner Info -->
          <div v-if="lottery.status === 'completed' && lottery.winner" class="glass rounded-2xl p-6 border border-emerald-500/50 bg-emerald-500/10">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
              </div>
              <div>
                <p class="text-emerald-400 text-sm font-medium">Winner</p>
                <p class="text-white font-semibold">{{ lottery.winner.name }}</p>
              </div>
            </div>
          </div>

          <!-- Buy Button -->
          <button
            v-if="lottery.status === 'active' && availableTickets > 0"
            @click="showPurchaseModal = true"
            class="w-full btn-primary text-lg py-4"
            :disabled="!authStore.isAuthenticated"
          >
            {{ authStore.isAuthenticated ? 'Buy Tickets' : 'Login to Buy Tickets' }}
          </button>
          
          <router-link
            v-if="!authStore.isAuthenticated"
            to="/login"
            class="block text-center text-indigo-400 hover:text-indigo-300"
          >
            Login or Register to participate
          </router-link>

          <!-- Description -->
          <div class="space-y-4">
            <h2 class="text-xl font-semibold text-white">Description</h2>
            <p class="text-slate-300 whitespace-pre-wrap">{{ lottery.description }}</p>
          </div>

          <!-- Seller Info -->
          <div class="glass rounded-2xl p-6 border border-slate-700/50">
            <p class="text-slate-400 text-sm mb-2">Listed by</p>
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold">
                  {{ lottery.user?.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
              <span class="text-white font-medium">{{ lottery.user?.name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Purchase Modal -->
    <TicketPurchaseModal
      v-if="lottery"
      :show="showPurchaseModal"
      :lottery="lottery"
      @close="showPurchaseModal = false"
      @success="handlePurchaseSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLotteryStore } from '../stores/lottery'
import { useAuthStore } from '../stores/auth'
import CountdownTimer from '../components/CountdownTimer.vue'
import TicketPurchaseModal from '../components/TicketPurchaseModal.vue'

const route = useRoute()
const router = useRouter()
const lotteryStore = useLotteryStore()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref(null)
const lottery = ref(null)
const activeImage = ref(0)
const showPurchaseModal = ref(false)

// Category labels mapping
const categoryLabels = {
  electronics: 'Electronics',
  gaming: 'Gaming',
  fashion: 'Fashion & Accessories',
  home: 'Home & Garden',
  sports: 'Sports & Outdoors',
  automotive: 'Automotive',
  collectibles: 'Collectibles & Art',
  jewelry: 'Jewelry & Watches',
  travel: 'Travel & Experiences',
  other: 'Other',
}

const progressPercentage = computed(() => {
  if (!lottery.value?.total_tickets) return 0
  return Math.round((lottery.value.tickets_sold || 0) / lottery.value.total_tickets * 100)
})

const availableTickets = computed(() => {
  if (!lottery.value) return 0
  return lottery.value.total_tickets - (lottery.value.tickets_sold || 0)
})

const categoryLabel = computed(() => {
  return categoryLabels[lottery.value?.category] || lottery.value?.category || 'Other'
})

const statusClass = computed(() => {
  switch (lottery.value?.status) {
    case 'active':
      return 'bg-emerald-500 text-white'
    case 'pending':
      return 'bg-amber-500 text-slate-900'
    case 'completed':
      return 'bg-slate-500 text-white'
    case 'cancelled':
      return 'bg-red-500 text-white'
    default:
      return 'bg-slate-500 text-white'
  }
})

const statusLabel = computed(() => {
  switch (lottery.value?.status) {
    case 'active':
      return 'Live'
    case 'pending':
      return 'Pending Approval'
    case 'approved':
      return 'Approved'
    case 'completed':
      return 'Ended'
    case 'cancelled':
      return 'Cancelled'
    default:
      return lottery.value?.status
  }
})

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2)
}

const handleExpired = () => {
  // Refresh lottery data
  fetchLottery()
}

const handlePurchaseSuccess = async (quantity) => {
  showPurchaseModal.value = false
  // Refresh lottery data to update ticket count
  await fetchLottery()
  alert(`Successfully purchased ${quantity} ticket(s)!`)
}

const fetchLottery = async () => {
  loading.value = true
  error.value = null
  try {
    lottery.value = await lotteryStore.fetchLottery(route.params.id)
  } catch (e) {
    error.value = e.response?.data?.message || 'Lottery not found'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchLottery()
})
</script>

