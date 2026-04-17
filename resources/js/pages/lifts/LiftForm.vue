<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <RouterLink :to="{ name: 'lifts' }" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="w-5 h-5" />
                </RouterLink>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Edit Lift' : 'Register Lift' }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ isEdit ? 'Update lift details' : 'Register a new lift under a building' }}</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="card p-6 space-y-5">
                <div v-if="errorMsg" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ errorMsg }}</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="form-label">Building <span class="text-red-500">*</span></label>
                        <select v-model="form.building_id" class="form-select" required>
                            <option value="">— Select building —</option>
                            <option v-for="b in buildings" :key="b.id" :value="b.id">
                                {{ b.name }} ({{ b.organisation?.name }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Lift Code <span class="text-red-500">*</span></label>
                        <input v-model="form.lift_code" type="text" class="form-input" required placeholder="e.g. LIFT-TW A-01" />
                    </div>

                    <div>
                        <label class="form-label">Lift Type <span class="text-red-500">*</span></label>
                        <select v-model="form.lift_type" class="form-select" required>
                            <option value="">— Select type —</option>
                            <option value="passenger">Passenger</option>
                            <option value="cargo">Cargo</option>
                            <option value="service">Service</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Brand <span class="text-red-500">*</span></label>
                        <input v-model="form.brand" type="text" class="form-input" required placeholder="e.g. OTIS" />
                    </div>

                    <div>
                        <label class="form-label">Model <span class="text-red-500">*</span></label>
                        <input v-model="form.model" type="text" class="form-input" required placeholder="e.g. Gen2" />
                    </div>

                    <div>
                        <label class="form-label">Serial Number <span class="text-red-500">*</span></label>
                        <input v-model="form.serial_number" type="text" class="form-input" required placeholder="e.g. SN-20050101-001" />
                    </div>

                    <div>
                        <label class="form-label">Capacity (kg)</label>
                        <input v-model.number="form.capacity" type="number" class="form-input" min="0" placeholder="e.g. 1000" />
                    </div>

                    <div>
                        <label class="form-label">Installation Date <span class="text-red-500">*</span></label>
                        <input v-model="form.installation_date" type="date" class="form-input" required />
                    </div>

                    <div>
                        <label class="form-label">Status <span class="text-red-500">*</span></label>
                        <select v-model="form.status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="under_maintenance">Under Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <RouterLink :to="{ name: 'lifts' }" class="btn-secondary">Cancel</RouterLink>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                        {{ isEdit ? 'Update Lift' : 'Register Lift' }}
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
    building_id: '', lift_code: '', lift_type: '', brand: '', model: '',
    serial_number: '', capacity: '', installation_date: '', status: 'active',
})
const buildings = ref([])
const loading   = ref(false)
const errorMsg  = ref('')

async function load() {
    const { data } = await api.get('/buildings')
    buildings.value = data.data || data

    if (isEdit.value) {
        const { data: liftData } = await api.get(`/lifts/${route.params.id}`)
        const l = liftData.data || liftData
        Object.assign(form.value, {
            building_id: l.building_id, lift_code: l.lift_code, lift_type: l.lift_type,
            brand: l.brand, model: l.model, serial_number: l.serial_number,
            capacity: l.capacity || '', installation_date: l.installation_date?.substring(0, 10) || '',
            status: l.status,
        })
    }
}

async function handleSubmit() {
    loading.value  = true
    errorMsg.value = ''
    try {
        const payload = { ...form.value, capacity: form.value.capacity || null }
        if (isEdit.value) {
            await api.put(`/lifts/${route.params.id}`, payload)
        } else {
            await api.post('/lifts', payload)
        }
        router.push({ name: 'lifts' })
    } catch (err) {
        const errors = err.response?.data?.errors
        errorMsg.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'An error occurred.')
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
