<template>
  <div class="min-h-[60vh] flex items-center justify-center">
    <div class="glass rounded-3xl p-8 md:p-12 max-w-lg w-full text-center border border-slate-700/50 animate-fade-in">
      <div class="w-20 h-20 bg-gradient-to-br from-slate-600 to-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-white mb-4">{{ $t('payment.cancelledTitle') }}</h1>
      <p class="text-slate-400 mb-8">
        {{ $t('payment.cancelledMessage') }}
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <router-link to="/browse" class="btn-primary">
          {{ $t('home.browseLotteries') }}
        </router-link>
        <router-link to="/" class="btn-outline">
          {{ $t('payment.goHome') }}
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>

