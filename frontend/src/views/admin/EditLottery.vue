<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Review Lottery</h1>
      <button @click="$router.back()" class="text-slate-400 hover:text-white transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="pageLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <template v-else-if="lottery">
      <!-- Status Bar -->
      <div
        :class="[
          'glass rounded-xl p-4 mb-6 border flex items-center justify-between',
          lottery.status === 'pending' ? 'border-amber-500/50 bg-amber-500/10' :
          lottery.status === 'active' ? 'border-emerald-500/50 bg-emerald-500/10' :
          'border-slate-700/50'
        ]"
      >
        <div class="flex items-center space-x-3">
          <span
            :class="[
              'px-3 py-1 rounded-full text-sm font-medium',
              statusClass(lottery.status)
            ]"
          >
            {{ statusLabel(lottery.status) }}
          </span>
          <span class="text-slate-400">
            Created by <span class="text-white">{{ lottery.user?.name }}</span>
          </span>
        </div>
        
        <div v-if="lottery.status === 'pending'" class="flex items-center space-x-2">
          <button
            @click="approveLottery"
            :disabled="actionLoading"
            class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors disabled:opacity-50"
          >
            Approve
          </button>
          <button
            @click="showRejectModal = true"
            :disabled="actionLoading"
            class="bg-red-500/20 hover:bg-red-500/30 text-red-400 font-medium py-2 px-4 rounded-xl text-sm transition-colors disabled:opacity-50"
          >
            Reject
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Images -->
        <div>
          <h2 class="text-lg font-semibold text-white mb-4">Images</h2>
          <div class="grid grid-cols-2 gap-4">
            <div
              v-for="(image, index) in lottery.images"
              :key="index"
              class="aspect-square bg-slate-700 rounded-xl overflow-hidden"
            >
              <img :src="image.url" class="w-full h-full object-cover" />
            </div>
            <div v-if="!lottery.images?.length" class="aspect-square bg-slate-700 rounded-xl flex items-center justify-center">
              <p class="text-slate-500">No images</p>
            </div>
          </div>
        </div>

        <!-- Details -->
        <div class="space-y-6">
          <div class="glass rounded-xl p-6 border border-slate-700/50">
            <h2 class="text-lg font-semibold text-white mb-4">Details</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-slate-400 text-sm mb-1">Title</label>
                <p class="text-white">{{ lottery.title }}</p>
              </div>
              
              <div>
                <label class="block text-slate-400 text-sm mb-1">Description</label>
                <p class="text-white whitespace-pre-wrap">{{ lottery.description }}</p>
              </div>
            </div>
          </div>

          <div class="glass rounded-xl p-6 border border-slate-700/50">
            <h2 class="text-lg font-semibold text-white mb-4">Pricing</h2>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-slate-400 text-sm mb-1">Product Value</label>
                <p class="text-white font-medium">€{{ formatPrice(lottery.product_value) }}</p>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1">Ticket Price</label>
                <p class="text-indigo-400 font-medium">€{{ formatPrice(lottery.ticket_price) }}</p>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1">Total Tickets</label>
                <p class="text-white font-medium">{{ lottery.total_tickets }}</p>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1">Charity</label>
                <p class="text-emerald-400 font-medium">{{ lottery.charity_percentage }}%</p>
              </div>
            </div>
          </div>

          <div class="glass rounded-xl p-6 border border-slate-700/50">
            <h2 class="text-lg font-semibold text-white mb-4">Duration</h2>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-slate-400 text-sm mb-1">Start Date</label>
                <p class="text-white">{{ formatDateTime(lottery.starts_at) }}</p>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1">End Date</label>
                <p class="text-white">{{ formatDateTime(lottery.ends_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Stats (if active or completed) -->
          <div v-if="lottery.status === 'active' || lottery.status === 'completed'" class="glass rounded-xl p-6 border border-slate-700/50">
            <h2 class="text-lg font-semibold text-white mb-4">Statistics</h2>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-slate-400 text-sm mb-1">Tickets Sold</label>
                <p class="text-white font-medium">{{ lottery.tickets_sold || 0 }} / {{ lottery.total_tickets }}</p>
              </div>
              <div>
                <label class="block text-slate-400 text-sm mb-1">Revenue</label>
                <p class="text-emerald-400 font-medium">€{{ formatPrice((lottery.tickets_sold || 0) * lottery.ticket_price) }}</p>
              </div>
            </div>

            <div v-if="lottery.winner" class="mt-4 pt-4 border-t border-slate-700/50">
              <label class="block text-slate-400 text-sm mb-1">Winner</label>
              <p class="text-emerald-400 font-medium">{{ lottery.winner.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Reject Modal -->
    <Teleport to="body">
      <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="showRejectModal = false"
      >
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="relative glass rounded-2xl w-full max-w-md p-6 border border-slate-600">
          <h2 class="text-xl font-bold text-white mb-4">Reject Lottery</h2>
          <textarea
            v-model="rejectReason"
            rows="3"
            class="input-field resize-none mb-4"
            placeholder="Reason for rejection..."
          ></textarea>
          <div class="flex space-x-4">
            <button @click="showRejectModal = false" class="flex-1 btn-outline">
              Cancel
            </button>
            <button
              @click="rejectLottery"
              :disabled="!rejectReason.trim() || actionLoading"
              class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-6 rounded-xl transition-colors disabled:opacity-50"
            >
              Reject
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminApi, lotteryApi } from '../../api'

const route = useRoute()
const router = useRouter()

const pageLoading = ref(true)
const actionLoading = ref(false)
const lottery = ref(null)
const showRejectModal = ref(false)
const rejectReason = ref('')

const formatPrice = (price) => parseFloat(price).toFixed(2)
const formatDateTime = (date) => new Date(date).toLocaleString('en-US', {
  month: 'short', day: 'numeric', year: 'numeric',
  hour: '2-digit', minute: '2-digit'
})

const statusClass = (status) => {
  switch (status) {
    case 'active': return 'bg-emerald-500 text-white'
    case 'pending': return 'bg-amber-500 text-slate-900'
    case 'approved': return 'bg-blue-500 text-white'
    case 'completed': return 'bg-slate-500 text-white'
    case 'cancelled': return 'bg-red-500 text-white'
    default: return 'bg-slate-500 text-white'
  }
}

const statusLabel = (status) => {
  switch (status) {
    case 'active': return 'Live'
    case 'pending': return 'Pending Approval'
    case 'approved': return 'Approved'
    case 'completed': return 'Ended'
    case 'cancelled': return 'Cancelled'
    default: return status
  }
}

const approveLottery = async () => {
  actionLoading.value = true
  try {
    await adminApi.approveLottery(lottery.value.id)
    router.push('/admin/pending')
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to approve lottery')
  } finally {
    actionLoading.value = false
  }
}

const rejectLottery = async () => {
  if (!rejectReason.value.trim()) return
  
  actionLoading.value = true
  try {
    await adminApi.rejectLottery(lottery.value.id, rejectReason.value)
    showRejectModal.value = false
    router.push('/admin/pending')
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to reject lottery')
  } finally {
    actionLoading.value = false
  }
}

onMounted(async () => {
  try {
    const response = await lotteryApi.getOne(route.params.id)
    lottery.value = response.data
  } catch (error) {
    console.error('Failed to load lottery:', error)
  } finally {
    pageLoading.value = false
  }
})
</script>

