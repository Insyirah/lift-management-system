import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios.js'

export const useAuthStore = defineStore('auth', () => {
    const user  = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
    const token = ref(localStorage.getItem('auth_token') || null)

    const isAuthenticated = computed(() => !!token.value)
    const isSuperAdmin   = computed(() => user.value?.role === 'super_admin')
    const isAdmin        = computed(() => user.value?.role === 'admin')
    const isInspector    = computed(() => user.value?.role === 'inspector')
    const canManage      = computed(() => isSuperAdmin.value || isAdmin.value)

    async function login(email, password) {
        const { data } = await api.post('/login', { email, password })
        token.value = data.token
        user.value  = data.user
        localStorage.setItem('auth_token', data.token)
        localStorage.setItem('auth_user', JSON.stringify(data.user))
    }

    async function logout() {
        try {
            await api.post('/logout')
        } finally {
            token.value = null
            user.value  = null
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
        }
    }

    async function fetchMe() {
        const { data } = await api.get('/me')
        user.value = data.user
        localStorage.setItem('auth_user', JSON.stringify(data.user))
    }

    return { user, token, isAuthenticated, isSuperAdmin, isAdmin, isInspector, canManage, login, logout, fetchMe }
})
