<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Inspections</h1>
                <p class="text-sm text-gray-500 mt-0.5">View and manage lift inspections</p>
            </div>
            <RouterLink v-if="auth.canManage" :to="{ name: 'inspections.create' }" class="btn-primary">
                <PlusIcon class="w-4 h-4" /> Schedule Inspection
            </RouterLink>
        </div>

        <!-- Filters -->
        <div class="card p-4 mb-4 flex gap-4 flex-wrap">
            <input v-model="search" type="text" class="form-input w-56" placeholder="Search lift code..." />
            <select v-model="filterStatus" class="form-select w-44">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
            </select>
            <select v-model="filterType" class="form-select w-44">
                <option value="">All Types</option>
                <option value="routine">Routine</option>
                <option value="annual">Annual</option>
                <option value="special">Special</option>
                <option value="follow_up">Follow Up</option>
            </select>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Loading...</div>
            <div v-else-if="!filtered.length" class="p-10 text-center text-gray-400 text-sm">No inspections found.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Lift</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Building</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Inspector</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Type</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Date</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="ins in filtered" :key="ins.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ ins.lift?.lift_code }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ ins.lift?.building?.name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ ins.inspector?.name }}</td>
                            <td class="px-5 py-3 text-gray-600 capitalize">{{ ins.inspection_type?.replace('_',' ') }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ formatDate(ins.inspection_date) }}</td>
                            <td class="px-5 py-3"><StatusBadge :status="ins.status" /></td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <RouterLink
                                        :to="{ name: 'inspections.show', params: { id: ins.id } }"
                                        class="text-primary-600 hover:text-primary-800 text-xs font-medium"
                                    >
                                        View
                                    </RouterLink>
                                    <RouterLink
                                        v-if="ins.status !== 'completed' && ins.status !== 'failed'"
                                        :to="{ name: 'inspections.perform', params: { id: ins.id } }"
                                        class="text-emerald-600 hover:text-emerald-800 text-xs font-medium"
                                    >
                                        Perform
                                    </RouterLink>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { PlusIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import { useAuthStore } from '@/stores/auth.js'
import api from '@/api/axios.js'

const auth = useAuthStore()

const StatusBadge = {
    props: ['status'],
    template: `<span :class="cls">{{ label }}</span>`,
    computed: {
        cls() {
            return {
                pending:     'badge-pending',
                in_progress: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
                completed:   'badge-pass',
                failed:      'badge-fail',
            }[this.status] || 'badge-na'
        },
        label() { return (this.status || '').replace('_', ' ') },
    },
}

const inspections  = ref([])
const loading      = ref(true)
const search       = ref('')
const filterStatus = ref('')
const filterType   = ref('')

const filtered = computed(() =>
    inspections.value.filter(i => {
        const q = search.value.toLowerCase()
        const matchSearch = !q || (i.lift?.lift_code || '').toLowerCase().includes(q)
        const matchStatus = !filterStatus.value || i.status === filterStatus.value
        const matchType   = !filterType.value   || i.inspection_type === filterType.value
        return matchSearch && matchStatus && matchType
    })
)

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-MY', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
}

async function load() {
    loading.value = true
    try {
        const { data } = await api.get('/inspections')
        inspections.value = data.data || data
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
