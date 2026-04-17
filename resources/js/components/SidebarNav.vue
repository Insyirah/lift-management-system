<template>
    <ul class="space-y-0.5">
        <li v-for="item in visibleItems" :key="item.name">
            <RouterLink
                :to="{ name: item.route }"
                @click="$emit('navigate')"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                :class="isActive(item.route)
                    ? 'bg-primary-600 text-white'
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'"
            >
                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                {{ item.label }}
            </RouterLink>
        </li>
    </ul>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import {
    HomeIcon,
    BuildingOffice2Icon,
    BuildingLibraryIcon,
    ArrowsUpDownIcon,
    ClipboardDocumentListIcon,
    UsersIcon,
    BuildingOfficeIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline'

defineEmits(['navigate'])

const auth  = useAuthStore()
const route = useRoute()

const allItems = [
    { name: 'dashboard',        label: 'Dashboard',        route: 'dashboard',        icon: HomeIcon,                   roles: ['super_admin','admin','inspector'] },
    { name: 'organisations',    label: 'Organisations',    route: 'organisations',    icon: BuildingOffice2Icon,        roles: ['super_admin','admin'] },
    { name: 'buildings',        label: 'Buildings',        route: 'buildings',        icon: BuildingLibraryIcon,        roles: ['super_admin','admin'] },
    { name: 'lifts',            label: 'Lifts',            route: 'lifts',            icon: ArrowsUpDownIcon,           roles: ['super_admin','admin'] },
    { name: 'inspections',      label: 'Inspections',      route: 'inspections',      icon: ClipboardDocumentListIcon,  roles: ['super_admin','admin','inspector'] },
    { name: 'users',            label: 'Users',            route: 'users',            icon: UsersIcon,                  roles: ['super_admin'] },
    { name: 'inspection-items', label: 'Checklist Items',  route: 'inspection-items', icon: WrenchScrewdriverIcon,      roles: ['super_admin',] },
    { name: 'companies',        label: 'Companies',        route: 'companies',        icon: BuildingOfficeIcon,         roles: ['super_admin'] },
]

const visibleItems = computed(() =>
    allItems.filter(item => item.roles.includes(auth.user?.role))
)

function isActive(routeName) {
    return route.name === routeName || route.name?.startsWith(routeName + '.')
}
</script>
