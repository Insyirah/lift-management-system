<template>
    <AppLayout>
        <template #default>
            <!-- Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <StatCard
                    v-for="card in statCards"
                    :key="card.label"
                    :label="card.label"
                    :value="stats ? stats[card.key] : '—'"
                    :icon="card.icon"
                    :color="card.color"
                    :loading="loading"
                />
            </div>

            <!-- Second row: Due Soon + Failed -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-800">Inspections Due Soon</h2>
                        <span class="badge-pending">{{ stats?.due_soon ?? 0 }} due</span>
                    </div>
                    <div v-if="loading" class="space-y-2">
                        <div v-for="i in 3" :key="i" class="h-4 bg-gray-100 rounded animate-pulse" />
                    </div>
                    <p v-else-if="!stats?.due_soon" class="text-sm text-gray-400">No inspections due within 30 days.</p>
                    <p v-else class="text-sm text-yellow-600 font-medium">
                        {{ stats.due_soon }} inspection(s) are due within the next 30 days.
                    </p>
                </div>

                <div class="card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-800">Failed Inspections (This Month)</h2>
                        <span class="badge-fail">{{ stats?.failed_this_month ?? 0 }} failed</span>
                    </div>
                    <div v-if="loading" class="space-y-2">
                        <div v-for="i in 3" :key="i" class="h-4 bg-gray-100 rounded animate-pulse" />
                    </div>
                    <p v-else-if="!stats?.failed_this_month" class="text-sm text-gray-400">No failed inspections this month.</p>
                    <p v-else class="text-sm text-red-600 font-medium">
                        {{ stats.failed_this_month }} inspection(s) failed this month.
                    </p>
                </div>
            </div>

            <!-- Recent Inspections Table -->
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Recent Inspections</h2>
                    <RouterLink :to="{ name: 'inspections' }" class="text-sm text-primary-600 hover:underline font-medium">
                        View all →
                    </RouterLink>
                </div>

                <div v-if="loading" class="p-6 space-y-3">
                    <div v-for="i in 5" :key="i" class="h-5 bg-gray-100 rounded animate-pulse" />
                </div>

                <div v-else-if="!recentInspections.length" class="p-10 text-center text-gray-400 text-sm">
                    No inspections found.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-5 py-3 font-medium text-gray-500">Lift</th>
                                <th class="text-left px-5 py-3 font-medium text-gray-500">Building</th>
                                <th class="text-left px-5 py-3 font-medium text-gray-500">Inspector</th>
                                <th class="text-left px-5 py-3 font-medium text-gray-500">Date</th>
                                <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                                <th class="text-left px-5 py-3 font-medium text-gray-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="inspection in recentInspections"
                                :key="inspection.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-5 py-3 font-medium text-gray-800">
                                    {{ inspection.lift?.lift_code }}
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ inspection.lift?.building?.name }}
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ inspection.inspector?.name }}
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ formatDate(inspection.inspection_date) }}
                                </td>
                                <td class="px-5 py-3">
                                    <StatusBadge :status="inspection.status" />
                                </td>
                                <td class="px-5 py-3">
                                    <RouterLink
                                        :to="{ name: 'inspections.show', params: { id: inspection.id } }"
                                        class="text-primary-600 hover:underline text-xs font-medium"
                                    >
                                        View
                                    </RouterLink>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import {
    BuildingOffice2Icon,
    BuildingLibraryIcon,
    ArrowsUpDownIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import api from '@/api/axios.js'

// ─── Sub-components defined inline ───────────────────────────────────────────

const StatCard = {
    props: ['label','value','icon','color','loading'],
    template: `
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ label }}</p>
                    <p v-if="loading" class="mt-2 h-7 w-16 bg-gray-100 rounded animate-pulse" />
                    <p v-else class="mt-2 text-3xl font-bold text-gray-900">{{ value ?? 0 }}</p>
                </div>
                <div :class="['w-12 h-12 rounded-xl flex items-center justify-center', color]">
                    <component :is="icon" class="w-6 h-6" />
                </div>
            </div>
        </div>
    `,
}

const StatusBadge = {
    props: ['status'],
    template: `
        <span :class="classes">{{ label }}</span>
    `,
    computed: {
        classes() {
            return {
                pending:     'badge-pending',
                in_progress: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
                completed:   'badge-pass',
                failed:      'badge-fail',
            }[this.status] || 'badge-na'
        },
        label() {
            return this.status?.replace('_', ' ') || '—'
        },
    },
}

// ─── Data ─────────────────────────────────────────────────────────────────────

const stats   = ref(null)
const loading = ref(true)

const statCards = [
    { key: 'total_organisations', label: 'Organisations', icon: BuildingOffice2Icon, color: 'bg-blue-100 text-blue-600' },
    { key: 'total_buildings',     label: 'Buildings',     icon: BuildingLibraryIcon, color: 'bg-indigo-100 text-indigo-600' },
    { key: 'total_lifts',         label: 'Lifts',         icon: ArrowsUpDownIcon,    color: 'bg-violet-100 text-violet-600' },
    { key: 'total_inspections',   label: 'Inspections',   icon: ClipboardDocumentListIcon, color: 'bg-emerald-100 text-emerald-600' },
]

const recentInspections = computed(() => stats.value?.recent_inspections ?? [])

function formatDate(date) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-MY', { day: '2-digit', month: 'short', year: 'numeric' })
}

async function loadStats() {
    loading.value = true
    try {
        const { data } = await api.get('/dashboard/stats')
        stats.value = data
    } finally {
        loading.value = false
    }
}

onMounted(loadStats)
</script>
