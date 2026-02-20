import { createRouter, createWebHistory } from 'vue-router'
import { isAuthenticated } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), meta: { public: true } },
    { path: '/', redirect: '/vehicles' },
    { path: '/stats', name: 'stats', component: () => import('../views/StatsView.vue') },
    { path: '/vehicles', name: 'vehicles', component: () => import('../views/VehicleList.vue') },
    { path: '/vehicles/new', name: 'vehicle-new', component: () => import('../views/VehicleForm.vue') },
    { path: '/vehicles/:id', name: 'vehicle-edit', component: () => import('../views/VehicleForm.vue') },
  ],
})

router.beforeEach((to, _from, next) => {
  if (to.meta.public) {
    if (isAuthenticated.value) next({ path: '/' })
    else next()
    return
  }
  if (!isAuthenticated.value) {
    next({ path: '/login', query: { redirect: to.fullPath } })
    return
  }
  next()
})

export default router
