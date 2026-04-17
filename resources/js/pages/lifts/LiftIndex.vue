<template>
    <AppLayout>
        <AlertBanner :message="alert.message" :type="alert.type" />
        <ConfirmModal
            v-model="deleteModal.show"
            title="Delete Lift"
            :message="`Delete lift '${deleteModal.name}'? All inspection records for this lift will also be deleted.`"
            :loading="deleteModal.loading"
            @confirm="confirmDelete"
        />

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Lifts</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage registered lifts</p>
            </div>
            <RouterLink :to="{ name: 'lifts.create' }" class="btn-primary">
                <PlusIcon class="w-4 h-4" /> Register Lift
            </RouterLink>
        </div>

        <div class="card p-4 mb-4 flex gap-4 flex-wrap">
            <input v-model="search" type="text" class="form-input w-64" placeholder="Search lift code, brand..." />
            <select v-model="filterStatus" class="form-select w-40">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="under_maintenance">Under Maintenance</option>
            </select>
            <select v-model="filterType" class="form-select w-40">
                <option value="">All Types</option>
                <option value="passenger">Passenger</option>
                <option value="cargo">Cargo</option>
                <option value="service">Service</option>
            </select>
        </div>

        <div class="card overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Loading...</div>
            <div v-else-if="!filtered.length" class="p-10 text-center text-gray-400 text-sm">No lifts found.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Lift Code</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Type</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Brand / Model</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Building</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Capacity</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Last Inspection</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="lift in filtered" :key="lift.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ lift.lift_code }}</td>
                            <td class="px-5 py-3">
                                <span :class="typeClass(lift.lift_type)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize">
                                    {{ lift.lift_type }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ lift.brand }} / {{ lift.model }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ lift.building?.name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ lift.capacity ? lift.capacity + ' kg' : '—' }}</td>
                            <td class="px-5 py-3">
                                <span :class="statusClass(lift.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                    {{ lift.status?.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 text-xs">
                                {{ lift.latest_inspection ? formatDate(lift.latest_inspection.inspection_date) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <RouterLink :to="{ name: 'lifts.edit', params: { id: lift.id } }" class="text-primary-600 hover:text-primary-800 text-xs font-medium">Edit</RouterLink>
                                    <button @click="promptDelete(lift)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
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
import AlertBanner from '@/components/AlertBanner.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import api from '@/api/axios.js'

const lifts       = ref([])
const loading     = ref(true)
const search      = ref('')
const filterStatus = ref('')
const filterType   = ref('')
const alert       = ref({ message: '', type: 'success' })
const deleteModal = ref({ show: false, id: null, name: '', loading: false })

const filtered = computed(() =>
    lifts.value.filter(l => {
        const q = search.value.toLowerCase()
        const matchSearch = !q || l.lift_code.toLowerCase().includes(q) || (l.brand || '').toLowerCase().includes(q) || (l.model || '').toLowerCase().includes(q)
        const matchStatus = !filterStatus.value || l.status === filterStatus.value
        const matchType   = !filterType.value   || l.lift_type === filterType.value
        return matchSearch && matchStatus && matchType
    })
)

function typeClass(type) {
    return { passenger: 'bg-blue-100 text-blue-700', cargo: 'bg-orange-100 text-orange-700', service: 'bg-purple-100 text-purple-700' }[type] || 'bg-gray-100 text-gray-600'
}

function statusClass(status) {
    return { active: 'bg-green-100 text-green-700', inactive: 'bg-gray-100 text-gray-600', under_maintenance: 'bg-yellow-100 text-yellow-700' }[status] || 'bg-gray-100 text-gray-600'
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-MY', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
}

async function load() {
    loading.value = true
    try {
        const { data } = await api.get('/lifts')
        lifts.value = data.data || data
    } finally {
        loading.value = false
    }
}

function promptDelete(lift) {
    deleteModal.value = { show: true, id: lift.id, name: lift.lift_code, loading: false }
}

async function confirmDelete() {
    deleteModal.value.loading = true
    try {
        await api.delete(`/lifts/${deleteModal.value.id}`)
        lifts.value = lifts.value.filter(l => l.id !== deleteModal.value.id)
        alert.value = { message: 'Lift deleted successfully.', type: 'success' }
    } catch {
        alert.value = { message: 'Failed to delete lift.', type: 'error' }
    } finally {
        deleteModal.value = { show: false, id: null, name: '', loading: false }
    }
}

onMounted(load)
</script>
