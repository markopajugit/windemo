<template>
  <div>
    <h1 class="text-2xl font-bold text-white mb-6">All Lotteries</h1>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4 mb-6">
      <select v-model="statusFilter" @change="fetchLotteries" class="input-field w-40">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
      
      <div class="relative flex-1 max-w-md">
        <input
          v-model="search"
          type="text"
          placeholder="Search lotteries..."
          class="input-field !pl-12 w-full"
          @input="debouncedSearch"
        />
        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="lotteries.length === 0" class="text-center py-16 glass rounded-2xl border border-slate-700/50">
      <p class="text-slate-400 text-lg">No lotteries found</p>
    </div>

    <!-- Lottery Table -->
    <div v-else class="glass rounded-xl border border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-800/50">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-medium text-slate-300">Lottery</th>
              <th class="px-6 py-4 text-left text-sm font-medium text-slate-300">Creator</th>
              <th class="px-6 py-4 text-left text-sm font-medium text-slate-300">Status</th>
              <th class="px-6 py-4 text-left text-sm font-medium text-slate-300">Tickets</th>
              <th class="px-6 py-4 text-left text-sm font-medium text-slate-300">Price</th>
              <th class="px-6 py-4 text-left text-sm font-medium text-slate-300">Created</th>
              <th class="px-6 py-4 text-right text-sm font-medium text-slate-300">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/50">
            <tr v-for="lottery in lotteries" :key="lottery.id" class="hover:bg-slate-800/30">
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-slate-700 rounded-lg overflow-hidden shrink-0">
                    <img
                      v-if="lottery.images?.length"
                      :src="lottery.images[0].url"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <span class="text-white font-medium truncate max-w-[200px]">{{ lottery.title }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-slate-300">{{ lottery.user?.name }}</td>
              <td class="px-6 py-4">
                <span
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    statusClass(lottery.status)
                  ]"
                >
                  {{ statusLabel(lottery.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-300">
                {{ lottery.tickets_sold || 0 }} / {{ lottery.total_tickets }}
              </td>
              <td class="px-6 py-4 text-indigo-400">€{{ formatPrice(lottery.ticket_price) }}</td>
              <td class="px-6 py-4 text-slate-400 text-sm">{{ formatDate(lottery.created_at) }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end space-x-2">
                  <router-link
                    :to="`/lottery/${lottery.id}`"
                    class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-colors"
                    title="View"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </router-link>
                  <router-link
                    :to="`/admin/lottery/${lottery.id}`"
                    class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-colors"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </router-link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.lastPage > 1" class="flex justify-center mt-6 space-x-2">
      <button
        @click="goToPage(pagination.currentPage - 1)"
        :disabled="pagination.currentPage <= 1"
        class="px-4 py-2 rounded-lg bg-slate-700 text-white disabled:opacity-50 hover:bg-slate-600 transition-colors"
      >
        Previous
      </button>
      <span class="px-4 py-2 text-slate-400">
        Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
      </span>
      <button
        @click="goToPage(pagination.currentPage + 1)"
        :disabled="pagination.currentPage >= pagination.lastPage"
        class="px-4 py-2 rounded-lg bg-slate-700 text-white disabled:opacity-50 hover:bg-slate-600 transition-colors"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '../../api'

const loading = ref(true)
const lotteries = ref([])
const statusFilter = ref('')
const search = ref('')
const pagination = ref({ currentPage: 1, lastPage: 1 })

let searchTimeout = null

const formatPrice = (price) => parseFloat(price).toFixed(2)
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })

const statusClass = (status) => {
  switch (status) {
    case 'active': return 'bg-emerald-500/20 text-emerald-400'
    case 'pending': return 'bg-amber-500/20 text-amber-400'
    case 'approved': return 'bg-blue-500/20 text-blue-400'
    case 'completed': return 'bg-slate-500/20 text-slate-400'
    case 'cancelled': return 'bg-red-500/20 text-red-400'
    default: return 'bg-slate-500/20 text-slate-400'
  }
}

const statusLabel = (status) => {
  switch (status) {
    case 'active': return 'Live'
    case 'pending': return 'Pending'
    case 'approved': return 'Approved'
    case 'completed': return 'Ended'
    case 'cancelled': return 'Cancelled'
    default: return status
  }
}

const fetchLotteries = async (page = 1) => {
  loading.value = true
  try {
    const response = await adminApi.getAllLotteries({
      page,
      status: statusFilter.value,
      search: search.value,
    })
    lotteries.value = response.data.data || response.data
    pagination.value = {
      currentPage: response.data.current_page || 1,
      lastPage: response.data.last_page || 1,
    }
  } catch (error) {
    console.error('Failed to fetch lotteries:', error)
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchLotteries(1), 300)
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.lastPage) {
    fetchLotteries(page)
  }
}

onMounted(() => fetchLotteries())
</script>

