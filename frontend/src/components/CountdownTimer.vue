<template>
  <div class="flex items-center space-x-1 text-slate-300">
    <svg class="w-4 h-4" :class="label === 'Starts in' ? 'text-amber-400' : 'text-indigo-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span v-if="label" class="text-slate-400 text-xs mr-1">{{ label }}</span>
    <span v-if="isExpired" class="text-red-400 font-medium">{{ label === 'Starts in' ? 'Started' : 'Ended' }}</span>
    <span v-else class="font-mono">
      <span v-if="days > 0" class="text-white font-semibold">{{ days }}d </span>
      <span class="text-white font-semibold">{{ hours.toString().padStart(2, '0') }}</span>:<span class="text-white font-semibold">{{ minutes.toString().padStart(2, '0') }}</span>:<span class="text-white font-semibold">{{ seconds.toString().padStart(2, '0') }}</span>
    </span>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  endTime: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['expired'])

const now = ref(Date.now())
let intervalId = null

const endTimestamp = computed(() => new Date(props.endTime).getTime())
const timeLeft = computed(() => Math.max(0, endTimestamp.value - now.value))
const isExpired = computed(() => timeLeft.value <= 0)

const days = computed(() => Math.floor(timeLeft.value / (1000 * 60 * 60 * 24)))
const hours = computed(() => Math.floor((timeLeft.value % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)))
const minutes = computed(() => Math.floor((timeLeft.value % (1000 * 60 * 60)) / (1000 * 60)))
const seconds = computed(() => Math.floor((timeLeft.value % (1000 * 60)) / 1000))

const updateTime = () => {
  now.value = Date.now()
  if (isExpired.value) {
    emit('expired')
    clearInterval(intervalId)
  }
}

onMounted(() => {
  intervalId = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})
</script>

