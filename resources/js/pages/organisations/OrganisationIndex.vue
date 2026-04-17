<template>
    <AppLayout>
        <AlertBanner :message="alert.message" :type="alert.type" />
        <ConfirmModal
            v-model="deleteModal.show"
            title="Delete Organisation"
            :message="`Are you sure you want to delete '${deleteModal.name}'? All buildings and lifts under it will also be deleted.`"
            :loading="deleteModal.loading"
            @confirm="confirmDelete"
        />

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Organisations</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage your client organisations</p>
            </div>
            <RouterLink :to="{ name: 'organisations.create' }" class="btn-primary">
                <PlusIcon class="w-4 h-4" /> New Organisation
            </RouterLink>
        </div>

        <!-- Search -->
        <div class="card p-4 mb-4">
            <input
                v-model="search"
                type="text"
                class="form-input max-w-sm"
                placeholder="Search organisations..."
            />
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Loading...</div>

            <div v-else-if="!filtered.length" class="p-10 text-center text-gray-400 text-sm">
                No organisations found.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Name</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Reg. No.</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Contact Person</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Phone</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="org in filtered" :key="org.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ org.name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ org.registration_no || '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ org.contact_person }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ org.contact_phone }}</td>
                            <td class="px-5 py-3">
                                <span :class="org.is_active ? 'badge-pass' : 'badge-fail'">
                                    {{ org.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <RouterLink
                                        :to="{ name: 'organisations.edit', params: { id: org.id } }"
                                        class="text-primary-600 hover:text-primary-800 text-xs font-medium"
                                    >
                                        Edit
                                    </RouterLink>
                                    <button
                                        @click="promptDelete(org)"
                                        class="text-red-500 hover:text-red-700 text-xs font-medium"
                                    >
                                        Delete
                                    </button>
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

const organisations = ref([])
const loading       = ref(true)
const search        = ref('')
const alert         = ref({ message: '', type: 'success' })
const deleteModal   = ref({ show: false, id: null, name: '', loading: false })

const filtered = computed(() =>
    organisations.value.filter(o =>
        o.name.toLowerCase().includes(search.value.toLowerCase()) ||
        (o.contact_person || '').toLowerCase().includes(search.value.toLowerCase())
    )
)

async function load() {
    loading.value = true
    try {
        const { data } = await api.get('/organisations')
        organisations.value = data.data || data
    } finally {
        loading.value = false
    }
}

function promptDelete(org) {
    deleteModal.value = { show: true, id: org.id, name: org.name, loading: false }
}

async function confirmDelete() {
    deleteModal.value.loading = true
    try {
        await api.delete(`/organisations/${deleteModal.value.id}`)
        organisations.value = organisations.value.filter(o => o.id !== deleteModal.value.id)
        alert.value = { message: 'Organisation deleted successfully.', type: 'success' }
    } catch {
        alert.value = { message: 'Failed to delete organisation.', type: 'error' }
    } finally {
        deleteModal.value = { show: false, id: null, name: '', loading: false }
    }
}

onMounted(load)
</script>
