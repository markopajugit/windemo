<template>
  <div>
    <h1 class="text-2xl font-bold text-white mb-6">Edit Lottery</h1>

    <!-- Loading -->
    <div v-if="pageLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>

    <!-- Not Editable -->
    <div v-else-if="lottery && lottery.status !== 'pending'" class="glass rounded-xl p-8 border border-amber-500/50 text-center">
      <svg class="w-16 h-16 mx-auto text-amber-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <h2 class="text-xl font-semibold text-white mb-2">Cannot Edit</h2>
      <p class="text-slate-400 mb-4">This lottery can no longer be edited because it has been {{ lottery.status }}.</p>
      <router-link to="/dashboard/my-lotteries" class="btn-primary">
        Back to My Lotteries
      </router-link>
    </div>

    <!-- Edit Form -->
    <form v-else-if="lottery" @submit.prevent="handleSubmit" class="space-y-6">
      <div class="glass rounded-xl p-6 border border-slate-700/50 space-y-6">
        <h2 class="text-lg font-semibold text-white">Product Information</h2>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-2">Title *</label>
          <input
            v-model="form.title"
            type="text"
            required
            class="input-field"
            placeholder="e.g., PlayStation 5 Console"
          />
        </div>

        <div>
          <label class="block text-slate-300 text-sm font-medium mb-2">Description *</label>
          <textarea
            v-model="form.description"
            required
            rows="4"
            class="input-field resize-none"
            placeholder="Describe the product in detail..."
          ></textarea>
        </div>

        <!-- Existing Images -->
        <div v-if="existingImages.length > 0">
          <label class="block text-slate-300 text-sm font-medium mb-2">Current Images</label>
          <div class="grid grid-cols-4 gap-2">
            <div v-for="image in existingImages" :key="image.id" class="relative aspect-square rounded-lg overflow-hidden">
              <img :src="image.url" class="w-full h-full object-cover" />
            </div>
          </div>
          <p class="text-slate-500 text-xs mt-2">Upload new images below to replace these</p>
        </div>

        <ImageUpload v-model="form.images" label="New Images (optional)" />
      </div>

      <div class="glass rounded-xl p-6 border border-slate-700/50 space-y-6">
        <h2 class="text-lg font-semibold text-white">Pricing</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Product Value (€) *</label>
            <input
              v-model.number="form.product_value"
              type="number"
              step="0.01"
              min="1"
              required
              class="input-field"
            />
          </div>

          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Ticket Price (€) *</label>
            <input
              v-model.number="form.ticket_price"
              type="number"
              step="0.01"
              min="0.50"
              required
              class="input-field"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Total Tickets *</label>
            <input
              v-model.number="form.total_tickets"
              type="number"
              min="10"
              max="10000"
              required
              class="input-field"
            />
          </div>

          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Charity Percentage (%)</label>
            <input
              v-model.number="form.charity_percentage"
              type="number"
              min="0"
              max="100"
              class="input-field"
            />
          </div>
        </div>
      </div>

      <div class="glass rounded-xl p-6 border border-slate-700/50 space-y-6">
        <h2 class="text-lg font-semibold text-white">Duration</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">Start Date *</label>
            <input
              v-model="form.starts_at"
              type="datetime-local"
              required
              class="input-field"
            />
          </div>

          <div>
            <label class="block text-slate-300 text-sm font-medium mb-2">End Date *</label>
            <input
              v-model="form.ends_at"
              type="datetime-local"
              required
              class="input-field"
            />
          </div>
        </div>
      </div>

      <div v-if="error" class="p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-400">
        {{ error }}
      </div>

      <div class="flex items-center justify-end space-x-4">
        <router-link to="/dashboard/my-lotteries" class="btn-outline">
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="loading"
          class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving...
          </span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLotteryStore } from '../../stores/lottery'
import ImageUpload from '../../components/ImageUpload.vue'

const route = useRoute()
const router = useRouter()
const lotteryStore = useLotteryStore()

const pageLoading = ref(true)
const loading = ref(false)
const error = ref('')
const lottery = ref(null)
const existingImages = ref([])

const form = ref({
  title: '',
  description: '',
  product_value: null,
  ticket_price: null,
  total_tickets: null,
  charity_percentage: 0,
  starts_at: '',
  ends_at: '',
  images: [],
})

const formatDateForInput = (dateString) => {
  const date = new Date(dateString)
  date.setMinutes(date.getMinutes() - date.getTimezoneOffset())
  return date.toISOString().slice(0, 16)
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('title', form.value.title)
    formData.append('description', form.value.description)
    formData.append('product_value', form.value.product_value)
    formData.append('ticket_price', form.value.ticket_price)
    formData.append('total_tickets', form.value.total_tickets)
    formData.append('charity_percentage', form.value.charity_percentage || 0)
    formData.append('starts_at', form.value.starts_at)
    formData.append('ends_at', form.value.ends_at)
    
    if (form.value.images.length > 0) {
      form.value.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image)
      })
    }

    await lotteryStore.updateLottery(route.params.id, formData)
    router.push('/dashboard/my-lotteries')
  } catch (e) {
    if (e.response?.data?.errors) {
      const errors = Object.values(e.response.data.errors).flat()
      error.value = errors.join('. ')
    } else {
      error.value = e.response?.data?.message || 'Failed to update lottery'
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    lottery.value = await lotteryStore.fetchLottery(route.params.id)
    
    if (lottery.value) {
      form.value = {
        title: lottery.value.title,
        description: lottery.value.description,
        product_value: parseFloat(lottery.value.product_value),
        ticket_price: parseFloat(lottery.value.ticket_price),
        total_tickets: lottery.value.total_tickets,
        charity_percentage: parseFloat(lottery.value.charity_percentage) || 0,
        starts_at: formatDateForInput(lottery.value.starts_at),
        ends_at: formatDateForInput(lottery.value.ends_at),
        images: [],
      }
      existingImages.value = lottery.value.images || []
    }
  } catch (e) {
    error.value = 'Failed to load lottery'
  } finally {
    pageLoading.value = false
  }
})
</script>

