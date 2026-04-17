<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-primary-900 to-gray-900 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-primary-600 px-8 py-8 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Lift Inspection</h1>
                    <p class="text-primary-200 text-sm mt-1">Management System</p>
                </div>

                <!-- Form -->
                <div class="px-8 py-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Sign in to your account</h2>

                    <form @submit.prevent="handleLogin" class="space-y-5">
                        <!-- Error alert -->
                        <div v-if="errorMsg" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center gap-2 text-sm text-red-700">
                            <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0" />
                            {{ errorMsg }}
                        </div>

                        <div>
                            <label class="form-label">Email Address</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="form-input"
                                placeholder="you@example.com"
                                required
                                autocomplete="email"
                            />
                        </div>

                        <div>
                            <label class="form-label">Password</label>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-input pr-10"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="current-password"
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeIcon v-if="!showPassword" class="w-4 h-4" />
                                    <EyeSlashIcon v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-primary w-full justify-center py-2.5"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                            {{ loading ? 'Signing in...' : 'Sign In' }}
                        </button>
                    </form>

                    <!-- Demo credentials -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-500 space-y-1">
                        <p class="font-semibold text-gray-700 mb-2">Demo Credentials</p>
                        <p><span class="font-medium">Super Admin:</span> superadmin@test.com</p>
                        <p><span class="font-medium">Admin:</span> admin@test.com</p>
                        <p><span class="font-medium">Inspector:</span> inspector@test.com</p>
                        <p class="mt-1 italic">Password: <span class="font-medium">password</span></p>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">
                &copy; {{ new Date().getFullYear() }} Lift Inspection Management System
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ExclamationCircleIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth.js'

const auth   = useAuthStore()
const router = useRouter()

const form = ref({ email: '', password: '' })
const loading      = ref(false)
const errorMsg     = ref('')
const showPassword = ref(false)

async function handleLogin() {
    loading.value  = true
    errorMsg.value = ''
    try {
        await auth.login(form.value.email, form.value.password)
        router.push({ name: 'dashboard' })
    } catch (err) {
        errorMsg.value = err.response?.data?.message || 'Invalid credentials. Please try again.'
    } finally {
        loading.value = false
    }
}
</script>
