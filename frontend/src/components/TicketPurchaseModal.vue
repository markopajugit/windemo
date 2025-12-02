<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="$emit('close')"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

        <!-- Modal -->
        <div class="relative glass rounded-2xl w-full max-w-md p-6 border border-slate-600 animate-fade-in">
          <!-- Close Button -->
          <button
            @click="$emit('close')"
            class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <h2 class="text-2xl font-bold text-white mb-6">{{ $t('ticketModal.title') }}</h2>

          <!-- Lottery Info -->
          <div class="glass rounded-xl p-4 mb-6 border border-slate-600">
            <h3 class="text-white font-semibold">{{ lottery.title }}</h3>
            <p class="text-slate-400 text-sm mt-1">€{{ formatPrice(lottery.ticket_price) }} {{ $t('ticketModal.perTicket') }}</p>
          </div>

          <!-- Quantity Selector -->
          <div class="mb-6">
            <label class="block text-slate-300 text-sm font-medium mb-2">{{ $t('ticketModal.numberOfTickets') }}</label>
            <div class="flex items-center space-x-4">
              <button
                @click="decreaseQuantity"
                :disabled="quantity <= 1"
                class="w-12 h-12 rounded-xl bg-slate-700 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center text-white transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </button>
              <input
                v-model.number="quantity"
                type="number"
                min="1"
                :max="maxTickets"
                class="flex-1 text-center input-field text-xl font-bold"
              />
              <button
                @click="increaseQuantity"
                :disabled="quantity >= maxTickets"
                class="w-12 h-12 rounded-xl bg-slate-700 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center text-white transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
            <p class="text-slate-500 text-sm mt-2 text-center">
              {{ availableTickets }} tickets available
            </p>
          </div>

          <!-- Total -->
          <div class="glass rounded-xl p-4 mb-6 border border-indigo-500/50">
            <div class="flex justify-between items-center">
              <span class="text-slate-300">{{ $t('ticketModal.total') }}</span>
              <span class="text-2xl font-bold text-white">€{{ formatPrice(total) }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-4">
            <button
              @click="$emit('close')"
              class="flex-1 btn-outline"
            >
              {{ $t('common.cancel') }}
            </button>
            <button
              @click="handlePurchase"
              :disabled="loading || quantity < 1 || quantity > maxTickets"
              class="flex-1 btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ $t('ticketModal.processing') }}
              </span>
              <span v-else>{{ $t('ticketModal.buyNow') }}</span>
            </button>
          </div>

          <!-- Stripe Payment Notice -->
          <p class="text-slate-500 text-xs text-center mt-4 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
            </svg>
            {{ $t('ticketModal.stripeNotice') }}
          </p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { paymentApi } from '../api'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  lottery: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['close', 'success'])

const quantity = ref(1)
const loading = ref(false)

const availableTickets = computed(() => {
  return props.lottery.total_tickets - (props.lottery.tickets_sold || 0)
})

const maxTickets = computed(() => {
  return Math.min(availableTickets.value, 10) // Limit to 10 per purchase
})

const total = computed(() => {
  return quantity.value * parseFloat(props.lottery.ticket_price)
})

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2)
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const increaseQuantity = () => {
  if (quantity.value < maxTickets.value) {
    quantity.value++
  }
}

const handlePurchase = async () => {
  loading.value = true
  try {
    const response = await paymentApi.createCheckoutSession(props.lottery.id, quantity.value)
    // Redirect to Stripe Checkout
    window.location.href = response.data.checkout_url
  } catch (error) {
    console.error('Purchase failed:', error)
    alert(error.response?.data?.message || 'Failed to create checkout session')
    loading.value = false
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
