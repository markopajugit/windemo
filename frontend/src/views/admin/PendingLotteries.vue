<template>
  <div>
    <h1 class="text-2xl font-bold text-white mb-6">Pending Approval</h1>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-amber-500"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="lotteries.length === 0" class="text-center py-16 glass rounded-2xl border border-slate-700/50">
      <svg class="w-16 h-16 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-slate-400 text-lg mb-2">All caught up!</p>
      <p class="text-slate-500">No lotteries pending approval</p>
    </div>

    <!-- Lottery List -->
    <div v-else class="space-y-4">
      <div
        v-for="lottery in lotteries"
        :key="lottery.id"
        class="glass rounded-xl p-6 border border-amber-500/30"
      >
        <div class="flex flex-col lg:flex-row gap-6">
          <!-- Image -->
          <div class="w-full lg:w-48 h-48 bg-slate-700 rounded-xl overflow-hidden shrink-0">
            <img
              v-if="lottery.images?.length"
              :src="lottery.images[0].url"
              :alt="lottery.title"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Info -->
          <div class="flex-1">
            <div class="flex items-start justify-between mb-2">
              <h3 class="text-xl font-semibold text-white">{{ lottery.title }}</h3>
              <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400">
                Pending
              </span>
            </div>
            
            <p class="text-slate-400 text-sm mb-4 line-clamp-2">{{ lottery.description }}</p>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
              <div>
                <p class="text-slate-500 text-xs">Product Value</p>
                <p class="text-white font-medium">€{{ formatPrice(lottery.product_value) }}</p>
              </div>
              <div>
                <p class="text-slate-500 text-xs">Ticket Price</p>
                <p class="text-indigo-400 font-medium">€{{ formatPrice(lottery.ticket_price) }}</p>
              </div>
              <div>
                <p class="text-slate-500 text-xs">Total Tickets</p>
                <p class="text-white font-medium">{{ lottery.total_tickets }}</p>
              </div>
              <div>
                <p class="text-slate-500 text-xs">Charity</p>
                <p class="text-emerald-400 font-medium">{{ lottery.charity_percentage }}%</p>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div class="text-slate-400 text-sm">
                Submitted by <span class="text-white">{{ lottery.user?.name }}</span>
                on {{ formatDate(lottery.created_at) }}
              </div>
              
              <div class="flex items-center space-x-2">
                <router-link
                  :to="`/admin/lottery/${lottery.id}`"
                  class="btn-outline py-2 px-4 text-sm"
                >
                  Review
                </router-link>
                <button
                  @click="approveLottery(lottery.id)"
                  :disabled="actionLoading === lottery.id"
                  class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors disabled:opacity-50"
                >
                  Approve
                </button>
                <button
                  @click="showRejectModal(lottery)"
                  :disabled="actionLoading === lottery.id"
                  class="bg-red-500/20 hover:bg-red-500/30 text-red-400 font-medium py-2 px-4 rounded-xl text-sm transition-colors disabled:opacity-50"
                >
                  Reject
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <Teleport to="body">
      <div
        v-if="rejectModalLottery"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="rejectModalLottery = null"
      >
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="relative glass rounded-2xl w-full max-w-md p-6 border border-slate-600">
          <h2 class="text-xl font-bold text-white mb-4">Reject Lottery</h2>
          <p class="text-slate-400 mb-4">
            Please provide a reason for rejecting "{{ rejectModalLottery.title }}":
          </p>
          <textarea
            v-model="rejectReason"
            rows="3"
            class="input-field resize-none mb-4"
            placeholder="Reason for rejection..."
          ></textarea>
          <div class="flex space-x-4">
            <button @click="rejectModalLottery = null" class="flex-1 btn-outline">
              Cancel
            </button>
            <button
              @click="confirmReject"
              :disabled="!rejectReason.trim()"
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
import { adminApi } from '../../api'

const loading = ref(true)
const actionLoading = ref(null)
const lotteries = ref([])
const rejectModalLottery = ref(null)
const rejectReason = ref('')

const formatPrice = (price) => parseFloat(price).toFixed(2)
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

const fetchPending = async () => {
  try {
    const response = await adminApi.getAllLotteries({ status: 'pending' })
    lotteries.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch pending lotteries:', error)
  } finally {
    loading.value = false
  }
}

const approveLottery = async (id) => {
  actionLoading.value = id
  try {
    await adminApi.approveLottery(id)
    lotteries.value = lotteries.value.filter(l => l.id !== id)
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to approve lottery')
  } finally {
    actionLoading.value = null
  }
}

const showRejectModal = (lottery) => {
  rejectModalLottery.value = lottery
  rejectReason.value = ''
}

const confirmReject = async () => {
  if (!rejectModalLottery.value || !rejectReason.value.trim()) return
  
  actionLoading.value = rejectModalLottery.value.id
  try {
    await adminApi.rejectLottery(rejectModalLottery.value.id, rejectReason.value)
    lotteries.value = lotteries.value.filter(l => l.id !== rejectModalLottery.value.id)
    rejectModalLottery.value = null
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to reject lottery')
  } finally {
    actionLoading.value = null
  }
}

onMounted(fetchPending)
</script>

