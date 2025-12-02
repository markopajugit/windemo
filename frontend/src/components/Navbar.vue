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
            {{ $t('nav.home') }}
          </router-link>
          <router-link 
            to="/browse" 
            class="text-slate-300 hover:text-white transition-colors"
            :class="{ 'text-white font-semibold': $route.name === 'browse' }"
          >
            {{ $t('nav.browse') }}
          </router-link>
        </div>

        <!-- Language Switcher & Auth Buttons -->
        <div class="flex items-center space-x-4">
          <!-- Language Switcher -->
          <div class="relative" ref="langMenuRef">
            <button 
              @click="showLangMenu = !showLangMenu"
              class="flex items-center space-x-1 px-2 py-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition-colors"
            >
              <span class="text-lg">{{ currentLocale === 'et' ? '🇪🇪' : '🇬🇧' }}</span>
              <span class="text-sm font-medium hidden sm:block">{{ currentLocale.toUpperCase() }}</span>
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <transition name="dropdown">
              <div 
                v-if="showLangMenu" 
                class="absolute right-0 mt-2 w-32 bg-slate-800 rounded-xl shadow-xl py-1 border border-slate-600 z-50"
              >
                <button 
                  @click="switchLocale('en')"
                  class="w-full flex items-center space-x-2 px-4 py-2 text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors"
                  :class="{ 'bg-slate-700/50 text-white': currentLocale === 'en' }"
                >
                  <span>🇬🇧</span>
                  <span>English</span>
                </button>
                <button 
                  @click="switchLocale('et')"
                  class="w-full flex items-center space-x-2 px-4 py-2 text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors"
                  :class="{ 'bg-slate-700/50 text-white': currentLocale === 'et' }"
                >
                  <span>🇪🇪</span>
                  <span>Eesti</span>
                </button>
              </div>
            </transition>
          </div>

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
                    {{ $t('nav.dashboard') }}
                  </router-link>
                  <router-link 
                    to="/dashboard/my-lotteries" 
                    class="block px-4 py-2 text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors"
                    @click="showMenu = false"
                  >
                    {{ $t('nav.myLotteries') }}
                  </router-link>
                  <router-link 
                    to="/dashboard/my-tickets" 
                    class="block px-4 py-2 text-slate-300 hover:bg-slate-700/50 hover:text-white transition-colors"
                    @click="showMenu = false"
                  >
                    {{ $t('nav.myTickets') }}
                  </router-link>
                  <template v-if="authStore.isAdmin">
                    <div class="border-t border-slate-600 my-2"></div>
                    <router-link 
                      to="/admin" 
                      class="block px-4 py-2 text-amber-400 hover:bg-slate-700/50 transition-colors"
                      @click="showMenu = false"
                    >
                      {{ $t('nav.adminPanel') }}
                    </router-link>
                  </template>
                  <div class="border-t border-slate-600 my-2"></div>
                  <button 
                    @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-red-400 hover:bg-slate-700/50 transition-colors"
                  >
                    {{ $t('nav.logout') }}
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
              {{ $t('nav.login') }}
            </router-link>
            <router-link 
              to="/register" 
              class="btn-primary text-sm py-2 px-4"
            >
              {{ $t('nav.signUp') }}
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../stores/auth'
import { setLocale, getLocale } from '../i18n'

const router = useRouter()
const authStore = useAuthStore()
const { locale } = useI18n()

const showMenu = ref(false)
const showLangMenu = ref(false)
const menuRef = ref(null)
const langMenuRef = ref(null)

const currentLocale = computed(() => locale.value)

const switchLocale = (newLocale) => {
  setLocale(newLocale)
  showLangMenu.value = false
}

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
  if (langMenuRef.value && !langMenuRef.value.contains(event.target)) {
    showLangMenu.value = false
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

