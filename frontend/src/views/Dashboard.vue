<template>
  <div class="flex flex-col lg:flex-row gap-8">
    <!-- Sidebar -->
    <aside class="lg:w-64 shrink-0">
      <div class="glass rounded-2xl p-6 border border-slate-700/50 sticky top-24">
        <!-- User Info -->
        <div class="flex items-center space-x-3 mb-6 pb-6 border-b border-slate-700/50">
          <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
            <span class="text-white text-lg font-semibold">
              {{ authStore.user?.name?.charAt(0).toUpperCase() }}
            </span>
          </div>
          <div>
            <p class="text-white font-semibold">{{ authStore.user?.name }}</p>
            <p class="text-slate-400 text-sm">{{ authStore.user?.email }}</p>
          </div>
        </div>

        <!-- Email Verification Notice -->
        <div v-if="!authStore.isEmailVerified" class="mb-6 p-4 rounded-xl bg-amber-500/20 border border-amber-500/50">
          <p class="text-amber-400 text-sm">
            Please verify your email to access all features.
          </p>
          <router-link to="/verify-email" class="text-amber-300 text-sm underline mt-1 inline-block">
            Resend verification
          </router-link>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2">
          <router-link
            to="/dashboard"
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              $route.name === 'dashboard-home'
                ? 'bg-indigo-500/20 text-indigo-400'
                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span>Overview</span>
          </router-link>

          <router-link
            to="/dashboard/my-lotteries"
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              $route.name === 'my-lotteries'
                ? 'bg-indigo-500/20 text-indigo-400'
                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>My Lotteries</span>
          </router-link>

          <router-link
            to="/dashboard/my-tickets"
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              $route.name === 'my-tickets'
                ? 'bg-indigo-500/20 text-indigo-400'
                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
            <span>My Tickets</span>
          </router-link>

          <router-link
            to="/dashboard/create-lottery"
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              $route.name === 'create-lottery'
                ? 'bg-indigo-500/20 text-indigo-400'
                : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Create Lottery</span>
          </router-link>
        </nav>
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

