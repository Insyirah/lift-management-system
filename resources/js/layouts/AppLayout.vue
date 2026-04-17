<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Sidebar -->
        <aside
            :class="['flex flex-col w-64 bg-gray-900 text-white flex-shrink-0 transition-transform duration-300 z-30',
                     sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0 fixed md:relative h-full']"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-700">
                <div class="w-9 h-9 rounded-lg bg-primary-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-sm leading-tight">Lift Inspection</div>
                    <div class="text-xs text-gray-400">Management System</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <SidebarNav @navigate="closeSidebar" />
            </nav>

            <!-- User info -->
            <div class="border-t border-gray-700 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ userInitials }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-medium truncate">{{ auth.user?.name }}</div>
                        <div class="text-xs text-gray-400 capitalize">{{ auth.user?.role?.replace('_', ' ') }}</div>
                    </div>
                    <button @click="handleLogout" class="ml-auto text-gray-400 hover:text-white transition-colors" title="Logout">
                        <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/50 z-20 md:hidden"
            @click="closeSidebar"
        />

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top bar -->
            <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-4">
                <button
                    class="md:hidden p-1 rounded-md text-gray-500 hover:text-gray-700"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <Bars3Icon class="w-6 h-6" />
                </button>
                <h1 class="text-base font-semibold text-gray-800 truncate">{{ pageTitle }}</h1>
                <div class="ml-auto text-sm text-gray-500">
                    {{ currentDate }}
                </div>
            </header>

            <!-- Page slot -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowRightOnRectangleIcon, Bars3Icon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth.js'
import SidebarNav from '@/components/SidebarNav.vue'

const auth     = useAuthStore()
const router   = useRouter()
const route    = useRoute()
const sidebarOpen = ref(false)

const userInitials = computed(() => {
    const name = auth.user?.name || ''
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const pageTitle = computed(() => route.meta?.title || 'Dashboard')

const currentDate = computed(() =>
    new Date().toLocaleDateString('en-MY', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })
)

function closeSidebar() { sidebarOpen.value = false }

async function handleLogout() {
    await auth.logout()
    router.push({ name: 'login' })
}
</script>
