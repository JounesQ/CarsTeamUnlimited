import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: () => import('../views/HomeView.vue') },
    { path: '/about', name: 'about', component: () => import('../views/AboutView.vue') },
    { path: '/vehicles', name: 'vehicles', component: () => import('../views/VehicleList.vue') },
    { path: '/vehicles/:id', name: 'vehicle-detail', component: () => import('../views/VehicleDetail.vue') },
  ],
})

export default router
