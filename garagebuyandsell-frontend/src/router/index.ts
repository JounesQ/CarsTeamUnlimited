import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/vehicles',
      name: 'vehicles',
      component: () => import('../views/VehicleList.vue'),
    },
    {
      path: '/vehicles/:id',
      name: 'vehicle-detail',
      component: () => import('../views/VehicleDetail.vue'),
    },
    {
      path: '/admin/vehicles',
      name: 'admin-vehicles',
      component: () => import('../views/admin/AdminVehicleList.vue'),
    },
    {
      path: '/admin/vehicles/new',
      name: 'admin-vehicle-new',
      component: () => import('../views/admin/AdminVehicleForm.vue'),
    },
    {
      path: '/admin/vehicles/:id',
      name: 'admin-vehicle-edit',
      component: () => import('../views/admin/AdminVehicleForm.vue'),
    },
  ],
})

export default router
