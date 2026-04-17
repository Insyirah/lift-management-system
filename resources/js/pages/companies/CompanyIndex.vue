<template>
    <AppLayout>
        <AlertBanner :message="alert.message" :type="alert.type" />
        <ConfirmModal
            v-model="deleteModal.show"
            title="Delete Company"
            :message="`Delete company '${deleteModal.name}'? All organisations, buildings, lifts, and inspections under it will be permanently deleted.`"
            :loading="deleteModal.loading"
            @confirm="confirmDelete"
        />

        <!-- Create / Edit Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="formModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50" @click="closeForm" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-5">
                            {{ formModal.isEdit ? 'Edit Company' : 'New Company' }}
                        </h3>

                        <div v-if="formError" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700 mb-4">{{ formError }}</div>

                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Company Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" class="form-input" required />
                            </div>
                            <div>
                                <label class="form-label">Registration No. <span class="text-red-500">*</span></label>
                                <input v-model="form.registration_no" type="text" class="form-input" required placeholder="e.g. ES-2024-001" />
                            </div>
                            <div>
                                <label class="form-label">Address <span class="text-red-500">*</span></label>
                                <textarea v-model="form.address" class="form-input" rows="2" required />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Phone</label>
                                    <input v-model="form.phone" type="text" class="form-input" />
                                </div>
                                <div>
                                    <label class="form-label">Email</label>
                                    <input v-model="form.email" type="email" class="form-input" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button class="btn-secondary" @click="closeForm">Cancel</button>
                            <button class="btn-primary" :disabled="formLoading" @click="handleSubmit">
                                <span v-if="formLoading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                {{ formModal.isEdit ? 'Update' : 'Create Company' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Companies</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage inspection companies (Super Admin only)</p>
            </div>
            <button @click="openCreate" class="btn-primary">
                <PlusIcon class="w-4 h-4" /> New Company
            </button>
        </div>

        <div class="card p-4 mb-4">
            <input v-model="search" type="text" class="form-input max-w-sm" placeholder="Search companies..." />
        </div>

        <div class="card overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Loading...</div>
            <div v-else-if="!filtered.length" class="p-10 text-center text-gray-400 text-sm">No companies found.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Company</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Reg. No.</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Phone</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Email</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="c in filtered" :key="c.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-900">{{ c.name }}</div>
                                <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ c.address }}</div>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ c.registration_no }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ c.phone || '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ c.email || '—' }}</td>
                            <td class="px-5 py-3">
                                <span :class="c.is_active ? 'badge-pass' : 'badge-fail'">{{ c.is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit(c)" class="text-primary-600 hover:text-primary-800 text-xs font-medium">Edit</button>
                                    <button @click="promptDelete(c)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
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
import { PlusIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import AlertBanner from '@/components/AlertBanner.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import api from '@/api/axios.js'

const companies   = ref([])
const loading     = ref(true)
const search      = ref('')
const alert       = ref({ message: '', type: 'success' })
const deleteModal = ref({ show: false, id: null, name: '', loading: false })

const formModal   = ref({ show: false, isEdit: false, id: null })
const form        = ref({ name: '', registration_no: '', address: '', phone: '', email: '' })
const formLoading = ref(false)
const formError   = ref('')

const filtered = computed(() =>
    companies.value.filter(c =>
        c.name.toLowerCase().includes(search.value.toLowerCase()) ||
        c.registration_no.toLowerCase().includes(search.value.toLowerCase())
    )
)

async function load() {
    loading.value = true
    try {
        const { data } = await api.get('/companies')
        companies.value = data.data || data
    } finally {
        loading.value = false
    }
}

function resetForm() {
    form.value = { name: '', registration_no: '', address: '', phone: '', email: '' }
    formError.value = ''
}

function openCreate() { resetForm(); formModal.value = { show: true, isEdit: false, id: null } }
function openEdit(c)  { resetForm(); Object.assign(form.value, { name: c.name, registration_no: c.registration_no, address: c.address, phone: c.phone || '', email: c.email || '' }); formModal.value = { show: true, isEdit: true, id: c.id } }
function closeForm()  { formModal.value.show = false }

async function handleSubmit() {
    formLoading.value = true
    formError.value   = ''
    try {
        if (formModal.value.isEdit) {
            await api.put(`/companies/${formModal.value.id}`, form.value)
            companies.value = companies.value.map(c => c.id === formModal.value.id ? { ...c, ...form.value } : c)
        } else {
            const { data } = await api.post('/companies', form.value)
            companies.value.unshift(data.data || data)
        }
        closeForm()
        alert.value = { message: `Company ${formModal.value.isEdit ? 'updated' : 'created'} successfully.`, type: 'success' }
    } catch (err) {
        const errors = err.response?.data?.errors
        formError.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'An error occurred.')
    } finally {
        formLoading.value = false
    }
}

function promptDelete(c) { deleteModal.value = { show: true, id: c.id, name: c.name, loading: false } }

async function confirmDelete() {
    deleteModal.value.loading = true
    try {
        await api.delete(`/companies/${deleteModal.value.id}`)
        companies.value = companies.value.filter(c => c.id !== deleteModal.value.id)
        alert.value = { message: 'Company deleted.', type: 'success' }
    } catch {
        alert.value = { message: 'Failed to delete company.', type: 'error' }
    } finally {
        deleteModal.value = { show: false, id: null, name: '', loading: false }
    }
}

onMounted(load)
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
