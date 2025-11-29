<template>
  <div>
    <!-- Hero Section -->
    <section class="text-center py-16 md:py-24">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
          Win Amazing Prizes with
          <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
            Fair Lotteries
          </span>
        </h1>
        <p class="text-xl text-slate-400 mb-8 max-w-2xl mx-auto">
          Create or participate in transparent lotteries. Buy tickets, support charities, and win incredible prizes!
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <router-link to="/browse" class="btn-primary text-lg px-8 py-4">
            Browse Lotteries
          </router-link>
          <router-link to="/dashboard/create-lottery" class="btn-outline text-lg px-8 py-4">
            Create a Lottery
          </router-link>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="glass rounded-2xl p-6 text-center border border-slate-700/50">
          <p class="text-3xl font-bold text-indigo-400">1,234</p>
          <p class="text-slate-400 mt-1">Active Lotteries</p>
        </div>
        <div class="glass rounded-2xl p-6 text-center border border-slate-700/50">
          <p class="text-3xl font-bold text-purple-400">€89,500</p>
          <p class="text-slate-400 mt-1">Prizes Won</p>
        </div>
        <div class="glass rounded-2xl p-6 text-center border border-slate-700/50">
          <p class="text-3xl font-bold text-pink-400">5,678</p>
          <p class="text-slate-400 mt-1">Happy Winners</p>
        </div>
        <div class="glass rounded-2xl p-6 text-center border border-slate-700/50">
          <p class="text-3xl font-bold text-emerald-400">€12,300</p>
          <p class="text-slate-400 mt-1">Donated to Charity</p>
        </div>
      </div>
    </section>

    <!-- Popular Lotteries -->
    <section class="py-12">
      <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-white">
          Popular Lotteries
        </h2>
        <router-link to="/browse" class="text-indigo-400 hover:text-indigo-300 flex items-center space-x-1">
          <span>View All</span>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </router-link>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
      </div>

      <div v-else-if="popularLotteries.length === 0" class="text-center py-12 glass rounded-2xl border border-slate-700/50">
        <svg class="w-16 h-16 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <p class="text-slate-400 text-lg">No lotteries available yet</p>
        <router-link to="/dashboard/create-lottery" class="btn-primary mt-4 inline-block">
          Create the First One!
        </router-link>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <LotteryCard
          v-for="lottery in popularLotteries"
          :key="lottery.id"
          :lottery="lottery"
          class="animate-fade-in"
          :style="{ animationDelay: `${popularLotteries.indexOf(lottery) * 100}ms` }"
        />
      </div>
    </section>

    <!-- How It Works -->
    <section class="py-16">
      <h2 class="text-2xl md:text-3xl font-bold text-white text-center mb-12">
        How It Works
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl font-bold text-white">1</span>
          </div>
          <h3 class="text-xl font-semibold text-white mb-2">Create or Browse</h3>
          <p class="text-slate-400">List your item for lottery or browse existing lotteries to find prizes you want</p>
        </div>
        <div class="text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl font-bold text-white">2</span>
          </div>
          <h3 class="text-xl font-semibold text-white mb-2">Buy Tickets</h3>
          <p class="text-slate-400">Purchase tickets for lotteries you want to enter. More tickets = better chances!</p>
        </div>
        <div class="text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl font-bold text-white">3</span>
          </div>
          <h3 class="text-xl font-semibold text-white mb-2">Win Prizes</h3>
          <p class="text-slate-400">When the timer ends, a random winner is selected from all ticket holders</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16">
      <div class="glass rounded-3xl p-8 md:p-12 text-center border border-slate-700/50 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10"></div>
        <div class="relative">
          <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
            Ready to Get Started?
          </h2>
          <p class="text-slate-400 text-lg mb-8 max-w-xl mx-auto">
            Join thousands of users already winning amazing prizes on our platform
          </p>
          <router-link to="/register" class="btn-secondary text-lg px-8 py-4">
            Create Free Account
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLotteryStore } from '../stores/lottery'
import LotteryCard from '../components/LotteryCard.vue'

const lotteryStore = useLotteryStore()
const loading = ref(true)
const popularLotteries = ref([])

onMounted(async () => {
  try {
    await lotteryStore.fetchPopular()
    popularLotteries.value = lotteryStore.popularLotteries
  } finally {
    loading.value = false
  }
})
</script>

