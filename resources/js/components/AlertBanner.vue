<template>
    <Transition name="slide-down">
        <div
            v-if="show"
            :class="['fixed top-4 right-4 z-50 flex items-start gap-3 rounded-lg px-4 py-3 shadow-lg max-w-sm w-full', typeClass]"
        >
            <component :is="icon" class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <div class="flex-1 text-sm font-medium">{{ message }}</div>
            <button @click="show = false" class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity">
                <XMarkIcon class="w-4 h-4" />
            </button>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    message:  { type: String, default: '' },
    type:     { type: String, default: 'success' }, // success | error | info
    duration: { type: Number, default: 4000 },
})

const show = ref(false)
let timer  = null

const typeClass = computed(() => ({
    success: 'bg-green-50 text-green-800 border border-green-200',
    error:   'bg-red-50 text-red-800 border border-red-200',
    info:    'bg-blue-50 text-blue-800 border border-blue-200',
}[props.type]))

const icon = computed(() => ({
    success: CheckCircleIcon,
    error:   ExclamationCircleIcon,
    info:    InformationCircleIcon,
}[props.type]))

watch(() => props.message, (val) => {
    if (!val) return
    show.value = true
    clearTimeout(timer)
    timer = setTimeout(() => { show.value = false }, props.duration)
})
</script>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s; }
.slide-down-enter-from { opacity: 0; transform: translateY(-12px); }
.slide-down-leave-to   { opacity: 0; transform: translateY(-12px); }
</style>
