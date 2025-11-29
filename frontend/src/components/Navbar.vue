<template>
  <nav class="glass sticky top-0 z-50 border-b border-slate-700/50">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2">
          <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold text-xl">W</span>
          </div>
          <span class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
            win24
          </span>
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <router-link 
            to="/" 
            class="text-slate-300 hover:text-white transition-colors"
            :class="{ 'text-white font-semibold': $route.name === 'home' }"
          >
            Home
          </router-link>
          <router-link 
            to="/browse" 
            class="text-slate-300 hover:text-white transition-colors"
            :class="{ 'text-white font-semibold': $route.name === 'browse' }"
          >
            Browse Lotteries
          </router-link>
        </div>

        <!-- Auth Buttons -->
        <div class="flex items-center space-x-4">
          <template v-if="authStore.isAuthenticated">
            <!-- User Menu -->
            <div class="relative" ref="menuRef">
              <button 
                @click="showMenu = !showMenu"
                class="flex items-center space-x-2 text-slate-300 hover:text-white transition-colors"
              >
                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                  <span class="text-white text-sm font-semibold">
                    {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <span class="hidden sm:block">{{ authStore.user?.name }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <transition name="dropdown">
                <div 
                  v-if="showMenu" 
                  class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-xl shadow-xl py-2 border border-slate-600"
                >
                  <router-link 
                    to="/dashboard" 
                    class="block px-4 py-2 text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors"
                    @click="showMenu = false"
                  >
                    Dashboard
                  </router-link>
                  <router-link 
                    to="/dashboard/my-lotteries" 
                    class="block px-4 py-2 text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors"
                    @click="showMenu = false"
                  >
                    My Lotteries
                  </router-link>
                  <router-link 
                    to="/dashboard/my-tickets" 
                    class="block px-4 py-2 text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors"
                    @click="showMenu = false"
                  >
                    My Tickets
                  </router-link>
                  <template v-if="authStore.isAdmin">
                    <div class="border-t border-slate-600 my-2"></div>
                    <router-link 
                      to="/admin" 
                      class="block px-4 py-2 text-amber-400 hover:bg-slate-700/50 transition-colors"
                      @click="showMenu = false"
                    >
                      Admin Panel
                    </router-link>
                  </template>
                  <div class="border-t border-slate-600 my-2"></div>
                  <button 
                    @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-red-400 hover:bg-slate-700/50 transition-colors"
                  >
                    Logout
                  </button>
                </div>
              </transition>
            </div>
          </template>
          <template v-else>
            <router-link 
              to="/login" 
              class="text-slate-300 hover:text-white transition-colors"
            >
              Login
            </router-link>
            <router-link 
              to="/register" 
              class="btn-primary text-sm py-2 px-4"
            >
              Sign Up
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const showMenu = ref(false)
const menuRef = ref(null)

const handleLogout = async () => {
  await authStore.logout()
  showMenu.value = false
  router.push('/')
}

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    showMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

