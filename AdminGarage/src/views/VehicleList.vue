<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { Vehicle, Paginated } from '@/types/vehicle'

const router = useRouter()
const vehicles = ref<Vehicle[]>([])
const loading = ref(true)
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const statusFilter = ref('')
const makeFilter = ref('')

function primaryImage(v: Vehicle) {
  const img = v.images?.find((i) => i.is_primary) || v.images?.[0]
  const path = img?.image_path || ''
  return path ? imageUrl(path) : ''
}

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'php', maximumFractionDigits: 0 }).format(n)
}

async function load(page = 1) {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page, per_page: 15 }
    if (statusFilter.value) params.status = statusFilter.value
    if (makeFilter.value) params.make = makeFilter.value
    const res = await api.admin.getVehicles(params) as Paginated<Vehicle>
    vehicles.value = res.data
    pagination.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total }
  } catch (e) {
    console.error(e)
    vehicles.value = []
  } finally {
    loading.value = false
  }
}

function confirmDelete(v: Vehicle) {
  if (!confirm(`Delete "${v.title}"?`)) return
  api.admin.deleteVehicle(v.id).then(() => load(pagination.value.current_page)).catch(console.error)
}

onMounted(() => load(1))
</script>

<template>
  <div class="admin-list">
    <div class="admin-header">
      <h1>Vehicles</h1>
      <button type="button" class="btn btn-primary" @click="router.push('/vehicles/new')">Add vehicle</button>
    </div>
    <div class="filters">
      <input v-model="statusFilter" type="text" placeholder="Status" class="filter-inp" />
      <input v-model="makeFilter" type="text" placeholder="Make" class="filter-inp" />
      <button type="button" class="btn" @click="load(1)">Filter</button>
    </div>
    <div v-if="loading" class="loading">Loading…</div>
    <div v-else class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Status</th>
            <th>Price</th>
            <th>Views</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="v in vehicles" :key="v.id">
            <td>
              <div class="cell-img">
                <img v-if="primaryImage(v)" :src="primaryImage(v)" :alt="v.title" />
                <span v-else>—</span>
              </div>
            </td>
            <td>{{ v.year }} {{ v.make }} {{ v.model }}</td>
            <td><span class="badge" :class="v.status">{{ v.status }}</span></td>
            <td>{{ formatPrice(v.price) }}</td>
            <td>{{ v.views_count }}</td>
            <td>
              <button type="button" class="btn btn-sm" @click="router.push(`/vehicles/${v.id}`)">Edit</button>
              <button type="button" class="btn btn-sm btn-danger" @click="confirmDelete(v)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="pagination">
      <button type="button" class="btn" :disabled="pagination.current_page <= 1" @click="load(pagination.current_page - 1)">Previous</button>
      <span class="page-num">Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} total)</span>
      <button type="button" class="btn" :disabled="pagination.current_page >= pagination.last_page" @click="load(pagination.current_page + 1)">Next</button>
    </div>
  </div>
</template>

<style scoped>
.admin-list { max-width: 1100px; margin: 0 auto; padding: 1rem 0; }
.admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.admin-header h1 { font-size: 1.5rem; margin: 0; color: var(--color-heading); }
.filters { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
.filter-inp { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-background); color: var(--color-text); width: 120px; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: hsla(160, 100%, 37%, 1); color: white; border-color: transparent; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
.btn-danger { background: #c00; color: white; border-color: transparent; }
.loading { text-align: center; padding: 2rem; }
.table-wrap { overflow-x: auto; border: 1px solid var(--color-border); border-radius: 8px; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border); }
.admin-table th { background: var(--color-background-mute); font-weight: 600; }
.cell-img { width: 60px; height: 40px; }
.cell-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
.badge.available { background: #cfc; color: #161; }
.badge.sold { background: #fcc; color: #611; }
.badge.draft { background: #ccc; color: #333; }
.pagination { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 1rem; }
.page-num { font-size: 0.9rem; }
</style>
