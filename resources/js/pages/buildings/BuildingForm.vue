<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <RouterLink :to="{ name: 'buildings' }" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="w-5 h-5" />
                </RouterLink>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Edit Building' : 'New Building' }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update building details' : 'Register a new building' }}</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="card p-6 space-y-5">
                <div v-if="errorMsg" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                    {{ errorMsg }}
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="form-label">Organisation <span class="text-red-500">*</span></label>
                        <select v-model="form.organisation_id" class="form-select" required>
                            <option value="">— Select organisation —</option>
                            <option v-for="org in organisations" :key="org.id" :value="org.id">{{ org.name }}</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Building Name <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" class="form-input" required placeholder="e.g. Tower A" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Address <span class="text-red-500">*</span></label>
                        <textarea v-model="form.address" class="form-input" rows="2" required placeholder="Full address" />
                    </div>

                    <div>
                        <label class="form-label">Number of Floors <span class="text-red-500">*</span></label>
                        <input v-model.number="form.number_of_floors" type="number" class="form-input" required min="1" placeholder="e.g. 40" />
                    </div>

                    <div>
                        <label class="form-label">Year Built</label>
                        <input v-model.number="form.year_built" type="number" class="form-input" min="1900" :max="new Date().getFullYear()" placeholder="e.g. 2005" />
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
                    <RouterLink :to="{ name: 'buildings' }" class="btn-secondary">Cancel</RouterLink>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                        {{ isEdit ? 'Update Building' : 'Create Building' }}
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
    organisation_id: '', name: '', address: '',
    number_of_floors: '', year_built: '', is_active: true,
})
const organisations = ref([])
const loading       = ref(false)
const errorMsg      = ref('')

async function load() {
    const [orgsRes] = await Promise.all([
        api.get('/organisations'),
    ])
    organisations.value = orgsRes.data.data || orgsRes.data

    if (isEdit.value) {
        const { data } = await api.get(`/buildings/${route.params.id}`)
        const b = data.data || data
        Object.assign(form.value, {
            organisation_id: b.organisation_id, name: b.name, address: b.address,
            number_of_floors: b.number_of_floors, year_built: b.year_built || '',
            is_active: b.is_active,
        })
    }
}

async function handleSubmit() {
    loading.value  = true
    errorMsg.value = ''
    try {
        if (isEdit.value) {
            await api.put(`/buildings/${route.params.id}`, form.value)
        } else {
            await api.post('/buildings', form.value)
        }
        router.push({ name: 'buildings' })
    } catch (err) {
        const errors = err.response?.data?.errors
        errorMsg.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'An error occurred.')
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
