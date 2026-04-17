<template>
    <AppLayout>
        <div v-if="loading" class="p-8 text-center text-gray-400">Loading inspection checklist...</div>

        <div v-else-if="inspection">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <RouterLink :to="{ name: 'inspections.show', params: { id: inspection.id } }" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="w-5 h-5" />
                </RouterLink>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">Perform Inspection</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ inspection.lift?.lift_code }} &mdash;
                        {{ inspection.lift?.building?.name }} &mdash;
                        {{ formatDate(inspection.inspection_date) }}
                    </p>
                </div>
                <!-- Progress -->
                <div class="text-right">
                    <div class="text-sm font-semibold text-gray-700">{{ completedCount }} / {{ results.length }} completed</div>
                    <div class="mt-1 w-40 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-600 rounded-full transition-all" :style="{ width: progressPct + '%' }" />
                    </div>
                </div>
            </div>

            <AlertBanner :message="alert.message" :type="alert.type" />

            <!-- Checklist groups -->
            <div class="space-y-4 mb-6">
                <div v-for="(items, category) in groupedResults" :key="category" class="card overflow-hidden">
                    <div class="px-5 py-3 bg-primary-600 text-white text-sm font-semibold">
                        {{ category }}
                    </div>

                    <div class="divide-y divide-gray-100">
                        <div v-for="result in items" :key="result.id" class="p-4">
                            <div class="flex items-start gap-4">
                                <!-- Item name -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800">{{ result.item?.name }}</p>
                                    <p v-if="result.inspection_item?.description" class="text-xs text-gray-400 mt-0.5">
                                        {{ result.inspection_item.description }}
                                    </p>
                                </div>

                                <!-- Pass / Fail / N/A buttons -->
                                <div class="flex gap-1.5 flex-shrink-0">
                                    <button
                                        v-for="opt in ['pass','fail','na']"
                                        :key="opt"
                                        type="button"
                                        @click="setResult(result, opt)"
                                        :class="[
                                            'px-3 py-1.5 text-xs font-semibold rounded-full border transition-all',
                                            result.result === opt ? activeClass(opt) : 'border-gray-300 text-gray-500 hover:border-gray-400'
                                        ]"
                                    >
                                        {{ opt === 'na' ? 'N/A' : opt.toUpperCase() }}
                                    </button>
                                </div>
                            </div>

                            <!-- Remark + Photo row (shows when result set) -->
                            <div v-if="result.result" class="mt-3 flex gap-3">
                                <input
                                    v-model="result.remark"
                                    type="text"
                                    class="form-input flex-1 text-sm"
                                    :placeholder="result.result === 'fail' ? 'Describe the issue (required for fail)...' : 'Remark (optional)'"
                                />
                                <label class="btn-secondary cursor-pointer flex-shrink-0 text-xs">
                                    <PhotoIcon class="w-4 h-4" />
                                    <span>{{ result._photoName || 'Photo' }}</span>
                                    <input type="file" class="hidden" accept="image/*" @change="onPhotoChange($event, result)" />
                                </label>
                                <button
                                    v-if="result._dirty"
                                    @click="saveResult(result)"
                                    :disabled="result._saving"
                                    class="btn-primary text-xs flex-shrink-0"
                                >
                                    <span v-if="result._saving" class="w-3 h-3 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                    Save
                                </button>
                            </div>

                            <!-- Saved photo thumbnail -->
                            <div v-if="result.photo_path && !result._photoFile" class="mt-2">
                                <a :href="`/storage/${result.photo_path}`" target="_blank">
                                    <img :src="`/storage/${result.photo_path}`" class="h-16 w-24 object-cover rounded-lg border border-gray-200" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complete Inspection -->
            <div class="card p-5 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-800">Ready to submit?</p>
                    <p class="text-sm text-gray-500">All checklist items must be reviewed before completing.</p>
                </div>
                <button
                    @click="completeInspection"
                    :disabled="completing || completedCount < results.length"
                    class="btn-primary"
                >
                    <span v-if="completing" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                    Complete Inspection
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import AlertBanner from '@/components/AlertBanner.vue'
import api from '@/api/axios.js'

const route  = useRoute()
const router = useRouter()

const inspection = ref(null)
const results    = ref([])
const loading    = ref(true)
const completing = ref(false)
const alert      = ref({ message: '', type: 'success' })

// ─── Computed ────────────────────────────────────────────────────────────────

const groupedResults = computed(() => {
    const groups = {}
    results.value.forEach(r => {
        const cat = r.inspection_item?.category || 'General'
        if (!groups[cat]) groups[cat] = []
        groups[cat].push(r)
    })
    return groups
})

const completedCount = computed(() => results.value.filter(r => r.result !== null && r.result !== undefined && r.result !== '').length)
const progressPct    = computed(() => results.value.length ? Math.round(completedCount.value / results.value.length * 100) : 0)

// ─── Helpers ─────────────────────────────────────────────────────────────────

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-MY', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
}

function activeClass(opt) {
    return { pass: 'bg-green-600 border-green-600 text-white', fail: 'bg-red-600 border-red-600 text-white', na: 'bg-gray-500 border-gray-500 text-white' }[opt]
}

// ─── Actions ─────────────────────────────────────────────────────────────────

function setResult(result, value) {
    result.result = value
    result._dirty = true
}

function onPhotoChange(event, result) {
    const file = event.target.files[0]
    if (!file) return
    result._photoFile = file
    result._photoName = file.name
    result._dirty = true
}

async function saveResult(result) {
    result._saving = true
    try {
        // Upload photo first if attached
        if (result._photoFile) {
            const formData = new FormData()
            formData.append('photo', result._photoFile)
            await api.post(
                `/inspections/${inspection.value.id}/results/${result.id}/photo`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            )
            result._photoFile = null
        }

        // Save result + remark
        await api.put(`/inspections/${inspection.value.id}/results/${result.id}`, {
            result: result.result,
            remark: result.remark || null,
        })
        result._dirty = false
        alert.value = { message: 'Result saved.', type: 'success' }
    } catch (err) {
        alert.value = { message: err.response?.data?.message || 'Failed to save result.', type: 'error' }
    } finally {
        result._saving = false
    }
}

async function completeInspection() {
    // Auto-save any dirty items first
    const dirty = results.value.filter(r => r._dirty)
    for (const r of dirty) {
        await saveResult(r)
    }

    completing.value = true
    try {
        await api.post(`/inspections/${inspection.value.id}/complete`)
        router.push({ name: 'inspections.show', params: { id: inspection.value.id } })
    } catch (err) {
        alert.value = { message: err.response?.data?.message || 'Failed to complete inspection.', type: 'error' }
        completing.value = false
    }
}

// ─── Load ────────────────────────────────────────────────────────────────────

async function load() {
    loading.value = true
    try {
        const { data } = await api.get(`/inspections/${route.params.id}`)
        inspection.value = data.data || data
        // Annotate each result with reactive UI state
        results.value = (inspection.value.results || []).map(r => ({
            ...r,
            _dirty:     false,
            _saving:    false,
            _photoFile: null,
            _photoName: null,
        }))
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
