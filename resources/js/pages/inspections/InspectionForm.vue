<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <RouterLink :to="{ name: 'inspections' }" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="w-5 h-5" />
                </RouterLink>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Schedule Inspection</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Assign an inspection and inspector for a lift</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="card p-6 space-y-5">
                <div v-if="errorMsg" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">{{ errorMsg }}</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="form-label">Lift <span class="text-red-500">*</span></label>
                        <select v-model="form.lift_id" class="form-select" required>
                            <option value="">— Select lift —</option>
                            <optgroup v-for="(group, building) in liftsByBuilding" :key="building" :label="building">
                                <option v-for="lift in group" :key="lift.id" :value="lift.id">
                                    {{ lift.lift_code }} ({{ lift.lift_type }})
                                </option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Inspector <span class="text-red-500">*</span></label>
                        <select v-model="form.user_id" class="form-select" required>
                            <option value="">— Select inspector —</option>
                            <option v-for="u in inspectors" :key="u.id" :value="u.id">
                                {{ u.name }} ({{ u.role.replace('_', ' ') }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Inspection Date <span class="text-red-500">*</span></label>
                        <input v-model="form.inspection_date" type="date" class="form-input" required />
                    </div>

                    <div>
                        <label class="form-label">Next Due Date</label>
                        <input v-model="form.next_due_date" type="date" class="form-input" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Inspection Type <span class="text-red-500">*</span></label>
                        <select v-model="form.inspection_type" class="form-select" required>
                            <option value="routine">Routine</option>
                            <option value="annual">Annual</option>
                            <option value="special">Special</option>
                            <option value="follow_up">Follow Up</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Notes</label>
                        <textarea v-model="form.notes" class="form-input" rows="3" placeholder="Additional notes or instructions..." />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <RouterLink :to="{ name: 'inspections' }" class="btn-secondary">Cancel</RouterLink>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                        Schedule Inspection
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import api from '@/api/axios.js'

const router = useRouter()

const form = ref({
    lift_id: '', user_id: '', inspection_date: '', next_due_date: '',
    inspection_type: 'routine', notes: '',
})
const lifts      = ref([])
const inspectors = ref([])
const loading    = ref(false)
const errorMsg   = ref('')

const liftsByBuilding = computed(() => {
    const groups = {}
    lifts.value.forEach(l => {
        const key = l.building?.name || 'Unknown Building'
        if (!groups[key]) groups[key] = []
        groups[key].push(l)
    })
    return groups
})

async function load() {
    const [liftsRes, usersRes] = await Promise.all([
        api.get('/lifts'),
        api.get('/users'),
    ])
    lifts.value      = (liftsRes.data.data || liftsRes.data).filter(l => l.status === 'active')
    inspectors.value = (usersRes.data.data || usersRes.data).filter(u => u.role === 'inspector' || u.role === 'admin')
}

async function handleSubmit() {
    loading.value  = true
    errorMsg.value = ''
    try {
        const payload = { ...form.value, next_due_date: form.value.next_due_date || null }
        await api.post('/inspections', payload)
        router.push({ name: 'inspections' })
    } catch (err) {
        const errors = err.response?.data?.errors
        errorMsg.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'An error occurred.')
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
