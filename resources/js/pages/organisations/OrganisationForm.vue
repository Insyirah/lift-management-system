<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <RouterLink :to="{ name: 'organisations' }" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="w-5 h-5" />
                </RouterLink>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Edit Organisation' : 'New Organisation' }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update organisation details' : 'Register a new client organisation' }}</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="card p-6 space-y-5">
                <!-- Error -->
                <div v-if="errorMsg" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                    {{ errorMsg }}
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="form-label">Organisation Name <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" class="form-input" required placeholder="Menara KL Holdings Sdn Bhd" />
                    </div>

                    <div>
                        <label class="form-label">Registration No.</label>
                        <input v-model="form.registration_no" type="text" class="form-input" placeholder="e.g. 1234567-A" />
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input v-model="form.email" type="email" class="form-input" placeholder="contact@company.com" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Address <span class="text-red-500">*</span></label>
                        <textarea v-model="form.address" class="form-input" rows="2" required placeholder="Full address" />
                    </div>

                    <div>
                        <label class="form-label">Contact Person <span class="text-red-500">*</span></label>
                        <input v-model="form.contact_person" type="text" class="form-input" required placeholder="Full name" />
                    </div>

                    <div>
                        <label class="form-label">Contact Phone <span class="text-red-500">*</span></label>
                        <input v-model="form.contact_phone" type="text" class="form-input" required placeholder="+60 12-345 6789" />
                    </div>

                    <div v-if="isEdit">
                        <label class="form-label">Status</label>
                        <select v-model="form.is_active" class="form-select">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <RouterLink :to="{ name: 'organisations' }" class="btn-secondary">Cancel</RouterLink>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                        {{ isEdit ? 'Update Organisation' : 'Create Organisation' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import api from '@/api/axios.js'

const route  = useRoute()
const router = useRouter()
const isEdit = computed(() => !!route.params.id)

const form = ref({
    name: '', registration_no: '', address: '', contact_person: '',
    contact_phone: '', email: '', is_active: true,
})
const loading  = ref(false)
const errorMsg = ref('')

async function load() {
    if (!isEdit.value) return
    const { data } = await api.get(`/organisations/${route.params.id}`)
    const org = data.data || data
    Object.assign(form.value, {
        name: org.name, registration_no: org.registration_no || '', address: org.address,
        contact_person: org.contact_person, contact_phone: org.contact_phone,
        email: org.email || '', is_active: org.is_active,
    })
}

async function handleSubmit() {
    loading.value  = true
    errorMsg.value = ''
    try {
        if (isEdit.value) {
            await api.put(`/organisations/${route.params.id}`, form.value)
        } else {
            await api.post('/organisations', form.value)
        }
        router.push({ name: 'organisations' })
    } catch (err) {
        const errors = err.response?.data?.errors
        errorMsg.value = errors
            ? Object.values(errors).flat().join(' ')
            : (err.response?.data?.message || 'An error occurred. Please try again.')
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
