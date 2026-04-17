<template>
    <AppLayout>
        <div v-if="loading" class="p-8 text-center text-gray-400">Loading inspection...</div>

        <div v-else-if="inspection">
            <!-- Header -->
            <div class="flex items-start gap-4 mb-6">
                <RouterLink :to="{ name: 'inspections' }" class="text-gray-400 hover:text-gray-600 mt-1">
                    <ArrowLeftIcon class="w-5 h-5" />
                </RouterLink>
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-xl font-bold text-gray-900">
                            Inspection: {{ inspection.lift?.lift_code }}
                        </h1>
                        <StatusBadge :status="inspection.status" />
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ inspection.lift?.building?.name }} &mdash;
                        {{ formatDate(inspection.inspection_date) }} &mdash;
                        {{ inspection.inspection_type?.replace('_',' ') }} inspection
                    </p>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <RouterLink
                        v-if="inspection.status !== 'completed' && inspection.status !== 'failed'"
                        :to="{ name: 'inspections.perform', params: { id: inspection.id } }"
                        class="btn-primary"
                    >
                        Perform Inspection
                    </RouterLink>
                    <button
                        v-if="(inspection.status === 'completed' || inspection.status === 'failed') && !inspection.report && auth.canManage"
                        @click="generateReport"
                        class="btn-secondary"
                        :disabled="generatingReport"
                    >
                        <span v-if="generatingReport" class="w-4 h-4 border-2 border-gray-400/40 border-t-gray-600 rounded-full animate-spin" />
                        Generate PDF
                    </button>
                    <a
                        v-if="inspection.report"
                        :href="`/api/reports/${inspection.report.id}/download`"
                        class="btn-secondary"
                        target="_blank"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4" /> Download Report
                    </a>
                </div>
            </div>

            <!-- Alert -->
            <AlertBanner :message="alert.message" :type="alert.type" />

            <!-- Info cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <div class="card p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Lift Details</p>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Code</dt><dd class="font-medium">{{ inspection.lift?.lift_code }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd>{{ inspection.lift?.lift_type }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Brand</dt><dd>{{ inspection.lift?.brand }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Model</dt><dd>{{ inspection.lift?.model }}</dd></div>
                    </dl>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Inspection Info</p>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd>{{ formatDate(inspection.inspection_date) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Next Due</dt><dd>{{ formatDate(inspection.next_due_date) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="capitalize">{{ inspection.inspection_type?.replace('_',' ') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Inspector</dt><dd>{{ inspection.inspector?.name }}</dd></div>
                    </dl>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Results Summary</p>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-green-50 rounded-lg py-2">
                            <div class="text-xl font-bold text-green-700">{{ passCount }}</div>
                            <div class="text-xs text-green-600">Pass</div>
                        </div>
                        <div class="bg-red-50 rounded-lg py-2">
                            <div class="text-xl font-bold text-red-700">{{ failCount }}</div>
                            <div class="text-xs text-red-600">Fail</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg py-2">
                            <div class="text-xl font-bold text-gray-600">{{ naCount }}</div>
                            <div class="text-xs text-gray-500">N/A</div>
                        </div>
                    </div>
                    <div v-if="inspection.notes" class="mt-3 p-2 bg-yellow-50 rounded text-xs text-yellow-800">
                        <span class="font-medium">Notes:</span> {{ inspection.notes }}
                    </div>
                </div>
            </div>

            <!-- Checklist Results -->
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Checklist Results</div>
                <div v-if="!inspection.results?.length" class="p-6 text-center text-gray-400 text-sm">No results recorded yet.</div>
                <div v-else>
                    <div v-for="(items, category) in groupedResults" :key="category">
                        <div class="px-5 py-2 bg-blue-50 text-blue-800 text-xs font-bold uppercase tracking-wide">
                            {{ category }}
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="result in items" :key="result.id" class="flex items-start px-5 py-3 gap-4">
                                <div class="flex-1 text-sm text-gray-700">{{ result.item?.name }}</div>
                                <div class="flex-shrink-0">
                                    <span :class="resultClass(result.result)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase">
                                        {{ result.result || '—' }}
                                    </span>
                                </div>
                                <div class="w-48 text-xs text-gray-500 truncate">{{ result.remark || '—' }}</div>
                                <div v-if="result.photo_path" class="flex-shrink-0">
                                    <a :href="`/storage/${result.photo_path}`" target="_blank" class="text-primary-600 text-xs hover:underline">Photo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { ArrowLeftIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import AlertBanner from '@/components/AlertBanner.vue'
import { useAuthStore } from '@/stores/auth.js'
import api from '@/api/axios.js'

const route = useRoute()
const auth  = useAuthStore()

const inspection     = ref(null)
const loading        = ref(true)
const generatingReport = ref(false)
const alert          = ref({ message: '', type: 'success' })

const StatusBadge = {
    props: ['status'],
    template: `<span :class="cls" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">{{ (status||'').replace('_',' ') }}</span>`,
    computed: {
        cls() {
            return { pending:'bg-yellow-100 text-yellow-800', in_progress:'bg-blue-100 text-blue-800', completed:'bg-green-100 text-green-800', failed:'bg-red-100 text-red-800' }[this.status] || 'bg-gray-100 text-gray-600'
        }
    },
}

const passCount = computed(() => inspection.value?.results?.filter(r => r.result === 'pass').length ?? 0)
const failCount = computed(() => inspection.value?.results?.filter(r => r.result === 'fail').length ?? 0)
const naCount   = computed(() => inspection.value?.results?.filter(r => r.result === 'na').length ?? 0)

const groupedResults = computed(() => {
    const groups = {}
    inspection.value?.results?.forEach(r => {
        const cat = r.inspection_item?.category || 'General'
        if (!groups[cat]) groups[cat] = []
        groups[cat].push(r)
    })
    console.log('Grouped Results:', groups)
    return groups
})

function resultClass(result) {
    return { pass: 'bg-green-100 text-green-800', fail: 'bg-red-100 text-red-800', na: 'bg-gray-100 text-gray-600' }[result] || 'bg-gray-100 text-gray-500'
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-MY', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
}

async function load() {
    loading.value = true
    try {
        const { data } = await api.get(`/inspections/${route.params.id}`)
        inspection.value = data.data || data
    } finally {
        loading.value = false
    }
}

async function generateReport() {
    generatingReport.value = true
    try {
        const { data } = await api.post(`/inspections/${route.params.id}/generate-report`)
        inspection.value.report = data.report || data
        alert.value = { message: 'PDF report generated successfully.', type: 'success' }
    } catch (err) {
        alert.value = { message: err.response?.data?.message || 'Failed to generate report.', type: 'error' }
    } finally {
        generatingReport.value = false
    }
}

onMounted(load)
</script>
