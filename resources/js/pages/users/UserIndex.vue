<template>
    <AppLayout>
        <AlertBanner :message="alert.message" :type="alert.type" />
        <ConfirmModal
            v-model="deleteModal.show"
            title="Delete User"
            :message="`Delete user '${deleteModal.name}'?`"
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
                            {{ formModal.isEdit ? 'Edit User' : 'New User' }}
                        </h3>

                        <div v-if="formError" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700 mb-4">{{ formError }}</div>

                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" class="form-input" required placeholder="Full name" />
                            </div>
                            <div>
                                <label class="form-label">Email <span class="text-red-500">*</span></label>
                                <input v-model="form.email" type="email" class="form-input" required placeholder="email@example.com" />
                            </div>
                            <div v-if="!formModal.isEdit">
                                <label class="form-label">Password <span class="text-red-500">*</span></label>
                                <input v-model="form.password" type="password" class="form-input" required placeholder="Min 8 characters" />
                            </div>
                            <div>
                                <label class="form-label">Role <span class="text-red-500">*</span></label>
                                <select v-model="form.role" class="form-select" required>
                                    <option value="inspector">Inspector</option>
                                    <option v-if="auth.isSuperAdmin" value="admin">Admin</option>
                                </select>
                            </div>
                            <div v-if="auth.isSuperAdmin">
                                <label class="form-label">Company</label>
                                <select v-model="form.company_id" class="form-select">
                                    <option value="">— No company —</option>
                                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Cert. Number</label>
                                    <input v-model="form.cert_number" type="text" class="form-input" placeholder="e.g. JKKP-INS-2022-001" />
                                </div>
                                <div>
                                    <label class="form-label">Cert. Expiry</label>
                                    <input v-model="form.cert_expiry" type="date" class="form-input" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button class="btn-secondary" @click="closeForm">Cancel</button>
                            <button class="btn-primary" :disabled="formLoading" @click="handleSubmit">
                                <span v-if="formLoading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                {{ formModal.isEdit ? 'Update' : 'Create User' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Page header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Users</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage admin and inspector accounts</p>
            </div>
            <button @click="openCreate" class="btn-primary">
                <PlusIcon class="w-4 h-4" /> New User
            </button>
        </div>

        <div class="card p-4 mb-4 flex gap-4 flex-wrap">
            <input v-model="search" type="text" class="form-input w-56" placeholder="Search by name or email..." />
            <select v-model="filterRole" class="form-select w-40">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="inspector">Inspector</option>
            </select>
        </div>

        <div class="card overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Loading...</div>
            <div v-else-if="!filtered.length" class="p-10 text-center text-gray-400 text-sm">No users found.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Name</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Email</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Role</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Cert. No.</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="u in filtered" :key="u.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ u.name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ u.email }}</td>
                            <td class="px-5 py-3">
                                <span :class="roleClass(u.role)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                    {{ u.role.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 text-xs">{{ u.cert_number || '—' }}</td>
                            <td class="px-5 py-3">
                                <span :class="u.is_active ? 'badge-pass' : 'badge-fail'">{{ u.is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit(u)" class="text-primary-600 hover:text-primary-800 text-xs font-medium">Edit</button>
                                    <button @click="promptDelete(u)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
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
import { useAuthStore } from '@/stores/auth.js'
import api from '@/api/axios.js'

const auth = useAuthStore()

const users       = ref([])
const companies   = ref([])
const loading     = ref(true)
const search      = ref('')
const filterRole  = ref('')
const alert       = ref({ message: '', type: 'success' })
const deleteModal = ref({ show: false, id: null, name: '', loading: false })

const formModal = ref({ show: false, isEdit: false, id: null })
const form      = ref({ name: '', email: '', password: '', role: 'inspector', company_id: '', cert_number: '', cert_expiry: '' })
const formLoading = ref(false)
const formError   = ref('')

const filtered = computed(() =>
    users.value.filter(u => {
        const q = search.value.toLowerCase()
        const matchSearch = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
        const matchRole   = !filterRole.value || u.role === filterRole.value
        return matchSearch && matchRole
    })
)

function roleClass(role) {
    return { admin: 'bg-blue-100 text-blue-700', inspector: 'bg-purple-100 text-purple-700', super_admin: 'bg-rose-100 text-rose-700' }[role] || 'bg-gray-100 text-gray-600'
}

async function load() {
    loading.value = true
    try {
        const [usersRes, companiesRes] = await Promise.all([
            api.get('/users'),
            auth.isSuperAdmin ? api.get('/companies') : Promise.resolve({ data: [] }),
        ])
        users.value     = usersRes.data.data || usersRes.data
        companies.value = companiesRes.data.data || companiesRes.data
    } finally {
        loading.value = false
    }
}

function resetForm() {
    form.value = { name: '', email: '', password: '', role: 'inspector', company_id: '', cert_number: '', cert_expiry: '' }
    formError.value = ''
}

function openCreate() {
    resetForm()
    formModal.value = { show: true, isEdit: false, id: null }
}

function openEdit(user) {
    resetForm()
    Object.assign(form.value, { name: user.name, email: user.email, role: user.role, company_id: user.company_id || '', cert_number: user.cert_number || '', cert_expiry: user.cert_expiry?.substring(0, 10) || '' })
    formModal.value = { show: true, isEdit: true, id: user.id }
}

function closeForm() {
    formModal.value.show = false
}

async function handleSubmit() {
    formLoading.value = true
    formError.value   = ''
    try {
        const payload = { ...form.value, cert_expiry: form.value.cert_expiry || null, company_id: form.value.company_id || null }
        if (!formModal.value.isEdit) delete payload.is_active
        if (formModal.value.isEdit) {
            delete payload.password
            await api.put(`/users/${formModal.value.id}`, payload)
            users.value = users.value.map(u => u.id === formModal.value.id ? { ...u, ...payload } : u)
        } else {
            const { data } = await api.post('/users', payload)
            users.value.unshift(data.data || data)
        }
        closeForm()
        alert.value = { message: `User ${formModal.value.isEdit ? 'updated' : 'created'} successfully.`, type: 'success' }
    } catch (err) {
        const errors = err.response?.data?.errors
        formError.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'An error occurred.')
    } finally {
        formLoading.value = false
    }
}

function promptDelete(u) {
    deleteModal.value = { show: true, id: u.id, name: u.name, loading: false }
}

async function confirmDelete() {
    deleteModal.value.loading = true
    try {
        await api.delete(`/users/${deleteModal.value.id}`)
        users.value = users.value.filter(u => u.id !== deleteModal.value.id)
        alert.value = { message: 'User deleted successfully.', type: 'success' }
    } catch {
        alert.value = { message: 'Failed to delete user.', type: 'error' }
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
