import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/vehicles' },
    { path: '/vehicles', name: 'vehicles', component: () => import('../views/VehicleList.vue') },
    { path: '/vehicles/new', name: 'vehicle-new', component: () => import('../views/VehicleForm.vue') },
    { path: '/vehicles/:id', name: 'vehicle-edit', component: () => import('../views/VehicleForm.vue') },
  ],
})

export default router
