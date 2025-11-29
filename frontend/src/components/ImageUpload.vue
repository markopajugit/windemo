<template>
  <div>
    <label class="block text-slate-300 text-sm font-medium mb-2">
      {{ label }}
    </label>
    
    <!-- Drop Zone -->
    <div
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'border-2 border-dashed rounded-xl p-6 text-center transition-all duration-300 cursor-pointer',
        isDragging ? 'border-indigo-500 bg-indigo-500/10' : 'border-slate-600 hover:border-slate-500',
      ]"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/jpeg,image/png,image/webp"
        class="hidden"
        @change="handleFileSelect"
      />
      
      <svg class="w-12 h-12 mx-auto text-slate-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <p class="text-slate-400">
        <span class="text-indigo-400 font-medium">Click to upload</span> or drag and drop
      </p>
      <p class="text-slate-500 text-sm mt-1">PNG, JPG, WebP up to 5MB each</p>
    </div>

    <!-- Preview Grid -->
    <div v-if="previews.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
      <div
        v-for="(preview, index) in previews"
        :key="index"
        class="relative group rounded-xl overflow-hidden aspect-square bg-slate-700"
      >
        <img :src="preview.url" :alt="`Preview ${index + 1}`" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
          <button
            type="button"
            @click.stop="removeImage(index)"
            class="p-2 bg-red-500 rounded-full hover:bg-red-600 transition-colors"
          >
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
        <div v-if="index === 0" class="absolute top-2 left-2 px-2 py-1 bg-indigo-500 text-white text-xs rounded-full">
          Main
        </div>
      </div>
    </div>

    <p v-if="error" class="text-red-400 text-sm mt-2">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: 'Images',
  },
  modelValue: {
    type: Array,
    default: () => [],
  },
  maxFiles: {
    type: Number,
    default: 5,
  },
  maxSize: {
    type: Number,
    default: 5 * 1024 * 1024, // 5MB
  },
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const isDragging = ref(false)
const previews = ref([])
const error = ref('')

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (newVal.length === 0) {
    previews.value = []
  }
}, { deep: true })

const triggerFileInput = () => {
  fileInput.value.click()
}

const validateFile = (file) => {
  const validTypes = ['image/jpeg', 'image/png', 'image/webp']
  if (!validTypes.includes(file.type)) {
    error.value = 'Only JPG, PNG, and WebP images are allowed'
    return false
  }
  if (file.size > props.maxSize) {
    error.value = 'File size must be less than 5MB'
    return false
  }
  return true
}

const processFiles = (files) => {
  error.value = ''
  const currentCount = previews.value.length
  const newFiles = Array.from(files).slice(0, props.maxFiles - currentCount)
  
  if (currentCount + files.length > props.maxFiles) {
    error.value = `Maximum ${props.maxFiles} images allowed`
  }

  newFiles.forEach((file) => {
    if (validateFile(file)) {
      const reader = new FileReader()
      reader.onload = (e) => {
        previews.value.push({
          file,
          url: e.target.result,
        })
        emit('update:modelValue', previews.value.map(p => p.file))
      }
      reader.readAsDataURL(file)
    }
  })
}

const handleFileSelect = (event) => {
  processFiles(event.target.files)
  event.target.value = '' // Reset input
}

const handleDrop = (event) => {
  isDragging.value = false
  processFiles(event.dataTransfer.files)
}

const removeImage = (index) => {
  previews.value.splice(index, 1)
  emit('update:modelValue', previews.value.map(p => p.file))
}
</script>

