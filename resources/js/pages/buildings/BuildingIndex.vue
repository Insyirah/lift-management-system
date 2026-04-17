<template>
    <AppLayout>
        <AlertBanner :message="alert.message" :type="alert.type" />
        <ConfirmModal
            v-model="deleteModal.show"
            title="Delete Building"
            :message="`Delete '${deleteModal.name}'? All lifts under this building will also be deleted.`"
            :loading="deleteModal.loading"
            @confirm="confirmDelete"
        />

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buildings</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage buildings under organisations</p>
            </div>
            <RouterLink :to="{ name: 'buildings.create' }" class="btn-primary">
                <PlusIcon class="w-4 h-4" /> New Building
            </RouterLink>
        </div>

        <div class="card p-4 mb-4">
            <input v-model="search" type="text" class="form-input max-w-sm" placeholder="Search buildings..." />
        </div>

        <div class="card overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Loading...</div>
            <div v-else-if="!filtered.length" class="p-10 text-center text-gray-400 text-sm">No buildings found.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Building</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Organisation</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Floors</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Year Built</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="b in filtered" :key="b.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-900">{{ b.name }}</div>
                                <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ b.address }}</div>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ b.organisation?.name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ b.number_of_floors }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ b.year_built || '—' }}</td>
                            <td class="px-5 py-3">
                                <span :class="b.is_active ? 'badge-pass' : 'badge-fail'">
                                    {{ b.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <RouterLink :to="{ name: 'buildings.edit', params: { id: b.id } }" class="text-primary-600 hover:text-primary-800 text-xs font-medium">Edit</RouterLink>
                                    <button @click="promptDelete(b)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
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

const buildings   = ref([])
const loading     = ref(true)
const search      = ref('')
const alert       = ref({ message: '', type: 'success' })
const deleteModal = ref({ show: false, id: null, name: '', loading: false })

const filtered = computed(() =>
    buildings.value.filter(b =>
        b.name.toLowerCase().includes(search.value.toLowerCase()) ||
        (b.organisation?.name || '').toLowerCase().includes(search.value.toLowerCase())
    )
)

async function load() {
    loading.value = true
    try {
        const { data } = await api.get('/buildings')
        buildings.value = data.data || data
    } finally {
        loading.value = false
    }
}

function promptDelete(b) {
    deleteModal.value = { show: true, id: b.id, name: b.name, loading: false }
}

async function confirmDelete() {
    deleteModal.value.loading = true
    try {
        await api.delete(`/buildings/${deleteModal.value.id}`)
        buildings.value = buildings.value.filter(b => b.id !== deleteModal.value.id)
        alert.value = { message: 'Building deleted successfully.', type: 'success' }
    } catch {
        alert.value = { message: 'Failed to delete building.', type: 'error' }
    } finally {
        deleteModal.value = { show: false, id: null, name: '', loading: false }
    }
}

onMounted(load)
</script>
