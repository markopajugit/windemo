<template>
  <footer class="glass border-t border-slate-700/50 mt-16">
    <div class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Brand -->
        <div class="col-span-1 md:col-span-2">
          <div class="flex items-center space-x-2 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold text-xl">W</span>
            </div>
            <span class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
              win24
            </span>
          </div>
          <p class="text-slate-400 max-w-md">
            {{ $t('footer.tagline') }}
          </p>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="text-white font-semibold mb-4">{{ $t('footer.quickLinks') }}</h3>
          <ul class="space-y-2">
            <li>
              <router-link to="/browse" class="text-slate-400 hover:text-white transition-colors">
                {{ $t('nav.browse') }}
              </router-link>
            </li>
            <li>
              <router-link to="/dashboard/create-lottery" class="text-slate-400 hover:text-white transition-colors">
                {{ $t('home.createLottery') }}
              </router-link>
            </li>
            <li>
              <a href="#" class="text-slate-400 hover:text-white transition-colors">
                {{ $t('home.howItWorks') }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h3 class="text-white font-semibold mb-4">{{ $t('footer.legal') }}</h3>
          <ul class="space-y-2">
            <li>
              <a href="#" class="text-slate-400 hover:text-white transition-colors">
                {{ $t('footer.termsOfService') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-slate-400 hover:text-white transition-colors">
                {{ $t('footer.privacyPolicy') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-slate-400 hover:text-white transition-colors">
                {{ $t('footer.cookiePolicy') }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="border-t border-slate-700/50 mt-8 pt-8 text-center text-slate-500">
        <p>&copy; {{ new Date().getFullYear() }} win24. {{ $t('footer.allRightsReserved') }}</p>
      </div>
    </div>
  </footer>
</template>

