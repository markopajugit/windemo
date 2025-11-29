<template>
  <div class="flex flex-col lg:flex-row gap-8">
    <!-- Sidebar -->
    <aside class="lg:w-64 shrink-0">
      <div class="glass rounded-2xl p-6 border border-amber-500/30 sticky top-24">
        <!-- Admin Badge -->
        <div class="flex items-center space-x-3 mb-6 pb-6 border-b border-slate-700/50">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <p class="text-amber-400 text-sm font-medium">Admin Panel</p>
            <p class="text-white font-semibold">{{ authStore.user?.name }}</p>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2">
          <router-link
            to="/admin"
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              $route.name === 'admin-dashboard'
                ? 'bg-amber-500/20 text-amber-400'
                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span>Dashboard</span>
          </router-link>

          <router-link
            to="/admin/pending"
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              $route.name === 'admin-pending'
                ? 'bg-amber-500/20 text-amber-400'
                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Pending Approval</span>
          </router-link>

          <router-link
            to="/admin/all-lotteries"
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              $route.name === 'admin-all-lotteries'
                ? 'bg-amber-500/20 text-amber-400'
                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>All Lotteries</span>
          </router-link>
        </nav>

        <!-- Back to Site -->
        <div class="mt-6 pt-6 border-t border-slate-700/50">
          <router-link
            to="/dashboard"
            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-700/50 hover:text-white transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            <span>Back to Dashboard</span>
          </router-link>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
</script>

