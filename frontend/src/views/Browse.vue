<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-white mb-4 md:mb-0">Browse Lotteries</h1>
      
      <!-- Search & Filters (only for Live tab) -->
      <div v-if="activeTab === 'live'" class="flex flex-col sm:flex-row gap-4">
        <div class="relative">
          <input
            v-model="search"
            type="text"
            placeholder="Search lotteries..."
            class="input-field !pl-12 w-full sm:w-64"
            @input="debouncedSearch"
          />
          <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <select v-model="category" @change="fetchLotteries" class="input-field w-full sm:w-48">
          <option value="all">All Categories</option>
          <option v-for="(label, value) in categories" :key="value" :value="value">
            {{ label }}
          </option>
        </select>
        
        <select v-model="sortBy" @change="fetchLotteries" class="input-field w-full sm:w-48">
          <option value="newest">Newest First</option>
          <option value="ending_soon">Ending Soon</option>
          <option value="popular">Most Popular</option>
          <option value="price_low">Price: Low to High</option>
          <option value="price_high">Price: High to Low</option>
        </select>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-2 mb-8">
      <button
        @click="activeTab = 'live'"
        :class="[
          'flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium transition-all duration-200',
          activeTab === 'live'
            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
            : 'bg-slate-800/50 text-slate-400 border border-slate-700/50 hover:bg-slate-700/50 hover:text-slate-300'
        ]"
      >
        <span class="relative flex h-2 w-2">
          <span v-if="activeTab === 'live'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        Live
        <span class="px-2 py-0.5 text-xs rounded-full bg-slate-700/50">{{ lotteries.length }}</span>
      </button>
      
      <button
        @click="activeTab = 'upcoming'"
        :class="[
          'flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium transition-all duration-200',
          activeTab === 'upcoming'
            ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40'
            : 'bg-slate-800/50 text-slate-400 border border-slate-700/50 hover:bg-slate-700/50 hover:text-slate-300'
        ]"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Upcoming
        <span class="px-2 py-0.5 text-xs rounded-full bg-slate-700/50">{{ upcomingLotteries.length }}</span>
      </button>

      <button
        @click="activeTab = 'ended'"
        :class="[
          'flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium transition-all duration-200',
          activeTab === 'ended'
            ? 'bg-purple-500/20 text-purple-400 border border-purple-500/40'
            : 'bg-slate-800/50 text-slate-400 border border-slate-700/50 hover:bg-slate-700/50 hover:text-slate-300'
        ]"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
        </svg>
        Ended
        <span class="px-2 py-0.5 text-xs rounded-full bg-slate-700/50">{{ endedLotteries.length }}</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Live Tab Content -->
    <template v-else-if="activeTab === 'live'">
      <!-- Empty State -->
      <div v-if="lotteries.length === 0" class="text-center py-16 glass rounded-2xl border border-slate-700/50">
        <svg class="w-20 h-20 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-slate-400 text-xl mb-2">No live lotteries right now</p>
        <p class="text-slate-500 mb-4">Check out the upcoming lotteries!</p>
        <button 
          @click="activeTab = 'upcoming'"
          class="px-4 py-2 bg-amber-500/20 text-amber-400 rounded-lg border border-amber-500/40 hover:bg-amber-500/30 transition-colors"
        >
          View Upcoming
        </button>
      </div>

      <!-- Lottery Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <LotteryCard
          v-for="lottery in lotteries"
          :key="lottery.id"
          :lottery="lottery"
        />
      </div>

      <!-- Pagination -->
      <div v-if="pagination.lastPage > 1" class="flex justify-center mt-8 space-x-2">
        <button
          @click="goToPage(pagination.currentPage - 1)"
          :disabled="pagination.currentPage <= 1"
          class="px-4 py-2 rounded-lg bg-slate-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-600 transition-colors"
        >
          Previous
        </button>
        
        <template v-for="page in visiblePages" :key="page">
          <button
            v-if="page !== '...'"
            @click="goToPage(page)"
            :class="[
              'px-4 py-2 rounded-lg transition-colors',
              page === pagination.currentPage
                ? 'bg-indigo-500 text-white'
                : 'bg-slate-700 text-white hover:bg-slate-600'
            ]"
          >
            {{ page }}
          </button>
          <span v-else class="px-4 py-2 text-slate-500">...</span>
        </template>
        
        <button
          @click="goToPage(pagination.currentPage + 1)"
          :disabled="pagination.currentPage >= pagination.lastPage"
          class="px-4 py-2 rounded-lg bg-slate-700 text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-600 transition-colors"
        >
          Next
        </button>
      </div>
    </template>

    <!-- Upcoming Tab Content -->
    <template v-else-if="activeTab === 'upcoming'">
      <!-- Empty State -->
      <div v-if="upcomingLotteries.length === 0" class="text-center py-16 glass rounded-2xl border border-slate-700/50">
        <svg class="w-20 h-20 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-slate-400 text-xl mb-2">No upcoming lotteries</p>
        <p class="text-slate-500">New lotteries will appear here once approved</p>
      </div>

      <!-- Upcoming Lottery Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <LotteryCard
          v-for="lottery in upcomingLotteries"
          :key="'upcoming-' + lottery.id"
          :lottery="lottery"
        />
      </div>
    </template>

    <!-- Ended Tab Content -->
    <template v-else-if="activeTab === 'ended'">
      <!-- Empty State -->
      <div v-if="endedLotteries.length === 0" class="text-center py-16 glass rounded-2xl border border-slate-700/50">
        <svg class="w-20 h-20 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
        </svg>
        <p class="text-slate-400 text-xl mb-2">No completed lotteries yet</p>
        <p class="text-slate-500">Ended lotteries with winners will appear here</p>
      </div>

      <!-- Ended Lottery Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <LotteryCard
          v-for="lottery in endedLotteries"
          :key="'ended-' + lottery.id"
          :lottery="lottery"
        />
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLotteryStore } from '../stores/lottery'
import { lotteryApi } from '../api'
import LotteryCard from '../components/LotteryCard.vue'

const lotteryStore = useLotteryStore()
const search = ref('')
const sortBy = ref('newest')
const category = ref('all')
const loading = ref(true)
const activeTab = ref('live')
const lotteries = ref([])
const upcomingLotteries = ref([])
const endedLotteries = ref([])
const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  total: 0,
})

// Categories
const categories = ref({
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
})

let searchTimeout = null

const visiblePages = computed(() => {
  const current = pagination.value.currentPage
  const last = pagination.value.lastPage
  const pages = []
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) pages.push(i)
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, 4, '...', last)
    } else if (current >= last - 2) {
      pages.push(1, '...', last - 3, last - 2, last - 1, last)
    } else {
      pages.push(1, '...', current - 1, current, current + 1, '...', last)
    }
  }
  
  return pages
})

const fetchLotteries = async (page = 1) => {
  loading.value = true
  try {
    await lotteryStore.fetchLotteries({
      page,
      search: search.value,
      sort: sortBy.value,
      category: category.value,
    })
    lotteries.value = lotteryStore.lotteries
    pagination.value = lotteryStore.pagination
  } finally {
    loading.value = false
  }
}

const fetchUpcoming = async () => {
  try {
    const response = await lotteryApi.getUpcoming()
    upcomingLotteries.value = response.data
  } catch (error) {
    console.error('Failed to fetch upcoming lotteries:', error)
  }
}

const fetchEnded = async () => {
  try {
    const response = await lotteryApi.getEnded()
    endedLotteries.value = response.data
  } catch (error) {
    console.error('Failed to fetch ended lotteries:', error)
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchLotteries(1)
  }, 300)
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.lastPage) {
    fetchLotteries(page)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const fetchCategories = async () => {
  try {
    const response = await lotteryApi.getCategories()
    categories.value = response.data
  } catch (e) {
    // Use default categories if fetch fails
  }
}

onMounted(async () => {
  await Promise.all([fetchLotteries(), fetchUpcoming(), fetchEnded(), fetchCategories()])
  // Auto-switch to upcoming tab if no live lotteries but has upcoming
  if (lotteries.value.length === 0 && upcomingLotteries.value.length > 0) {
    activeTab.value = 'upcoming'
  }
})
</script>
