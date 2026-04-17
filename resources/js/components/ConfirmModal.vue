<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="$emit('update:modelValue', false)" />
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ message }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button class="btn-secondary" @click="$emit('update:modelValue', false)">Cancel</button>
                        <button class="btn-danger" :disabled="loading" @click="$emit('confirm')">
                            <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                            {{ confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

defineProps({
    modelValue:   { type: Boolean, default: false },
    title:        { type: String, default: 'Confirm Delete' },
    message:      { type: String, default: 'Are you sure? This action cannot be undone.' },
    confirmLabel: { type: String, default: 'Delete' },
    loading:      { type: Boolean, default: false },
})
defineEmits(['update:modelValue', 'confirm'])
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
