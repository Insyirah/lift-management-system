<template>
    <AppLayout>
        <AlertBanner :message="alert.message" :type="alert.type" />
        <ConfirmModal
            v-model="deleteModal.show"
            title="Delete Checklist Item"
            :message="`Delete '${deleteModal.name}'?`"
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
                            {{ formModal.isEdit ? 'Edit Checklist Item' : 'New Checklist Item' }}
                        </h3>

                        <div v-if="formError" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700 mb-4">{{ formError }}</div>

                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" class="form-input" required placeholder="e.g. Emergency Stop Button" />
                            </div>
                            <div>
                                <label class="form-label">Category <span class="text-red-500">*</span></label>
                                <input v-model="form.category" type="text" class="form-input" required list="category-list" placeholder="e.g. Safety Devices" />
                                <datalist id="category-list">
                                    <option v-for="cat in uniqueCategories" :key="cat" :value="cat" />
                                </datalist>
                            </div>
                            <div>
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-input" rows="2" placeholder="Brief description of what to check..." />
                            </div>
                            <div>
                                <label class="form-label">Sort Order</label>
                                <input v-model.number="form.sort_order" type="number" class="form-input" min="0" placeholder="0" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button class="btn-secondary" @click="closeForm">Cancel</button>
                            <button class="btn-primary" :disabled="formLoading" @click="handleSubmit">
                                <span v-if="formLoading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                {{ formModal.isEdit ? 'Update' : 'Add Item' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Checklist Items</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage inspection checklist templates</p>
            </div>
            <button @click="openCreate" class="btn-primary">
                <PlusIcon class="w-4 h-4" /> Add Item
            </button>
        </div>

        <div class="card p-4 mb-4 flex gap-4 flex-wrap">
            <input v-model="search" type="text" class="form-input w-56" placeholder="Search items..." />
            <select v-model="filterCategory" class="form-select w-56">
                <option value="">All Categories</option>
                <option v-for="cat in uniqueCategories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
        </div>

        <!-- Grouped by category -->
        <div class="space-y-4">
            <div v-for="(items, category) in groupedFiltered" :key="category" class="card overflow-hidden">
                <div class="px-5 py-3 bg-primary-50 border-b border-primary-100 flex items-center justify-between">
                    <h2 class="font-semibold text-primary-800 text-sm">{{ category }}</h2>
                    <span class="text-xs text-primary-600">{{ items.length }} items</span>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="item in items" :key="item.id" class="flex items-start px-5 py-3 gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ item.name }}</p>
                            <p v-if="item.description" class="text-xs text-gray-400 mt-0.5">{{ item.description }}</p>
                        </div>
                        <div class="text-xs text-gray-400 flex-shrink-0 mt-0.5">Order: {{ item.sort_order }}</div>
                        <span :class="item.is_active ? 'badge-pass' : 'badge-fail'" class="flex-shrink-0 mt-0.5">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button @click="openEdit(item)" class="text-primary-600 hover:text-primary-800 text-xs font-medium">Edit</button>
                            <button @click="promptDelete(item)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="!Object.keys(groupedFiltered).length" class="card p-10 text-center text-gray-400 text-sm">
                No checklist items found.
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

const items          = ref([])
const loading        = ref(true)
const search         = ref('')
const filterCategory = ref('')
const alert          = ref({ message: '', type: 'success' })
const deleteModal    = ref({ show: false, id: null, name: '', loading: false })
const formModal      = ref({ show: false, isEdit: false, id: null })
const form           = ref({ name: '', category: '', description: '', sort_order: 0 })
const formLoading    = ref(false)
const formError      = ref('')

const uniqueCategories = computed(() => [...new Set(items.value.map(i => i.category))].sort())

const groupedFiltered = computed(() => {
    const filtered = items.value.filter(i => {
        const q = search.value.toLowerCase()
        const matchSearch   = !q || i.name.toLowerCase().includes(q) || i.category.toLowerCase().includes(q)
        const matchCategory = !filterCategory.value || i.category === filterCategory.value
        return matchSearch && matchCategory
    })
    const groups = {}
    filtered.forEach(i => {
        const cat = i.category || 'General'
        if (!groups[cat]) groups[cat] = []
        groups[cat].push(i)
    })
    return groups
})

async function load() {
    loading.value = true
    try {
        const { data } = await api.get('/inspection-items')
        items.value = data.data || data
    } finally {
        loading.value = false
    }
}

function resetForm() { form.value = { name: '', category: '', description: '', sort_order: 0 }; formError.value = '' }
function openCreate() { resetForm(); formModal.value = { show: true, isEdit: false, id: null } }
function openEdit(item) { resetForm(); Object.assign(form.value, { name: item.name, category: item.category, description: item.description || '', sort_order: item.sort_order }); formModal.value = { show: true, isEdit: true, id: item.id } }
function closeForm() { formModal.value.show = false }

async function handleSubmit() {
    formLoading.value = true
    formError.value   = ''
    try {
        if (formModal.value.isEdit) {
            await api.put(`/inspection-items/${formModal.value.id}`, form.value)
            items.value = items.value.map(i => i.id === formModal.value.id ? { ...i, ...form.value } : i)
        } else {
            const { data } = await api.post('/inspection-items', form.value)
            items.value.push(data.data || data)
        }
        closeForm()
        alert.value = { message: `Checklist item ${formModal.value.isEdit ? 'updated' : 'added'}.`, type: 'success' }
    } catch (err) {
        const errors = err.response?.data?.errors
        formError.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'An error occurred.')
    } finally {
        formLoading.value = false
    }
}

function promptDelete(item) { deleteModal.value = { show: true, id: item.id, name: item.name, loading: false } }

async function confirmDelete() {
    deleteModal.value.loading = true
    try {
        await api.delete(`/inspection-items/${deleteModal.value.id}`)
        items.value = items.value.filter(i => i.id !== deleteModal.value.id)
        alert.value = { message: 'Checklist item deleted.', type: 'success' }
    } catch {
        alert.value = { message: 'Failed to delete item.', type: 'error' }
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
