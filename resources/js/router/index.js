import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

// Lazy-load all pages
const LoginPage           = () => import('@/pages/LoginPage.vue')
const DashboardPage       = () => import('@/pages/DashboardPage.vue')

const OrganisationIndex   = () => import('@/pages/organisations/OrganisationIndex.vue')
const OrganisationForm    = () => import('@/pages/organisations/OrganisationForm.vue')

const BuildingIndex       = () => import('@/pages/buildings/BuildingIndex.vue')
const BuildingForm        = () => import('@/pages/buildings/BuildingForm.vue')

const LiftIndex           = () => import('@/pages/lifts/LiftIndex.vue')
const LiftForm            = () => import('@/pages/lifts/LiftForm.vue')

const InspectionIndex     = () => import('@/pages/inspections/InspectionIndex.vue')
const InspectionForm      = () => import('@/pages/inspections/InspectionForm.vue')
const InspectionDetail    = () => import('@/pages/inspections/InspectionDetail.vue')
const InspectionChecklist = () => import('@/pages/inspections/InspectionChecklist.vue')

const UserIndex           = () => import('@/pages/users/UserIndex.vue')
const CompanyIndex        = () => import('@/pages/companies/CompanyIndex.vue')
const InspectionItemIndex = () => import('@/pages/inspection-items/InspectionItemIndex.vue')

const routes = [
    // Public
    { path: '/login', name: 'login', component: LoginPage, meta: { guest: true } },

    // Protected
    { path: '/',                         name: 'dashboard',           component: DashboardPage,       meta: { auth: true } },

    { path: '/organisations',            name: 'organisations',       component: OrganisationIndex,   meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/organisations/create',     name: 'organisations.create',component: OrganisationForm,    meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/organisations/:id/edit',   name: 'organisations.edit',  component: OrganisationForm,    meta: { auth: true, roles: ['super_admin','admin'] } },

    { path: '/buildings',                name: 'buildings',           component: BuildingIndex,       meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/buildings/create',         name: 'buildings.create',    component: BuildingForm,        meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/buildings/:id/edit',       name: 'buildings.edit',      component: BuildingForm,        meta: { auth: true, roles: ['super_admin','admin'] } },

    { path: '/lifts',                    name: 'lifts',               component: LiftIndex,           meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/lifts/create',             name: 'lifts.create',        component: LiftForm,            meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/lifts/:id/edit',           name: 'lifts.edit',          component: LiftForm,            meta: { auth: true, roles: ['super_admin','admin'] } },

    { path: '/inspections',              name: 'inspections',         component: InspectionIndex,     meta: { auth: true } },
    { path: '/inspections/create',       name: 'inspections.create',  component: InspectionForm,      meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/inspections/:id',          name: 'inspections.show',    component: InspectionDetail,    meta: { auth: true } },
    { path: '/inspections/:id/perform',  name: 'inspections.perform', component: InspectionChecklist, meta: { auth: true } },

    { path: '/users',                    name: 'users',               component: UserIndex,           meta: { auth: true, roles: ['super_admin','admin'] } },
    { path: '/companies',                name: 'companies',           component: CompanyIndex,        meta: { auth: true, roles: ['super_admin'] } },
    { path: '/inspection-items',         name: 'inspection-items',    component: InspectionItemIndex, meta: { auth: true, roles: ['super_admin','admin'] } },

    // Catch-all — redirect to dashboard or login
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to, _from, next) => {
    const auth = useAuthStore()

    if (to.meta.guest && auth.isAuthenticated) {
        return next({ name: 'dashboard' })
    }

    if (to.meta.auth && !auth.isAuthenticated) {
        return next({ name: 'login' })
    }

    if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
        return next({ name: 'dashboard' })
    }

    next()
})

export default router
