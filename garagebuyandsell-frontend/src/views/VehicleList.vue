<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { Vehicle, Paginated } from '@/types/vehicle'
import { MAKES, VEHICLE_TYPES, CATEGORIES, FUEL_TYPES } from '@/data/vehicleOptions'

const router = useRouter()
const route = useRoute()
const vehicles = ref<Vehicle[]>([])
const loading = ref(true)
const pagination = ref<{ current_page: number; last_page: number; per_page: number; total: number }>({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
})

const make = ref((route.query.make as string) || '')
const vehicleType = ref((route.query.vehicle_type as string) || '')
const category = ref((route.query.category as string) || '')
const fuelType = ref((route.query.fuel_type as string) || '')
const year = ref(route.query.year ? Number(route.query.year) : undefined as number | undefined)
const minPrice = ref(route.query.min_price ? Number(route.query.min_price) : undefined as number | undefined)
const maxPrice = ref(route.query.max_price ? Number(route.query.max_price) : undefined as number | undefined)

function primaryImage(v: Vehicle) {
  const img = v.images?.find((i) => i.is_primary) || v.images?.[0]
  const path = img?.image_path || ''
  return path ? imageUrl(path) : ''
}

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n)
}

async function load(page = 1) {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page, per_page: 12 }
    if (make.value) params.make = make.value
    if (vehicleType.value) params.vehicle_type = vehicleType.value
    if (category.value) params.category = category.value
    if (fuelType.value) params.fuel_type = fuelType.value
    if (year.value) params.year = year.value
    if (minPrice.value != null) params.min_price = minPrice.value
    if (maxPrice.value != null) params.max_price = maxPrice.value
    const res = await api.getVehicles(params) as Paginated<Vehicle>
    vehicles.value = res.data
    pagination.value = {
      current_page: res.current_page,
      last_page: res.last_page,
      per_page: res.per_page,
      total: res.total,
    }
  } catch (e) {
    console.error(e)
    vehicles.value = []
  } finally {
    loading.value = false
  }
}

function search() {
  const q: Record<string, string> = {}
  if (make.value) q.make = make.value
  if (vehicleType.value) q.vehicle_type = vehicleType.value
  if (category.value) q.category = category.value
  if (fuelType.value) q.fuel_type = fuelType.value
  if (year.value) q.year = String(year.value)
  if (minPrice.value != null) q.min_price = String(minPrice.value)
  if (maxPrice.value != null) q.max_price = String(maxPrice.value)
  router.push({ path: '/vehicles', query: q })
  load(1)
}

onMounted(() => load(pagination.value.current_page))
watch(
  () => route.query,
  (q) => {
    make.value = (q.make as string) || ''
    vehicleType.value = (q.vehicle_type as string) || ''
    category.value = (q.category as string) || ''
    fuelType.value = (q.fuel_type as string) || ''
    year.value = q.year ? Number(q.year) : undefined
    minPrice.value = q.min_price ? Number(q.min_price) : undefined
    maxPrice.value = q.max_price ? Number(q.max_price) : undefined
    load(1)
  }
)
</script>

<template>
  <div class="vehicle-list">
    <h1>Vehicles for sale</h1>
    <div class="filters">
      <select v-model="make" class="filter-inp">
        <option value="">All makes</option>
        <option v-for="m in MAKES" :key="m" :value="m">{{ m }}</option>
      </select>
      <select v-model="vehicleType" class="filter-inp">
        <option value="">All types</option>
        <option v-for="t in VEHICLE_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
      </select>
      <select v-model="category" class="filter-inp">
        <option value="">All categories</option>
        <option v-for="c in CATEGORIES" :key="c" :value="c">{{ c }}</option>
      </select>
      <select v-model="fuelType" class="filter-inp">
        <option value="">All fuel types</option>
        <option v-for="f in FUEL_TYPES" :key="f" :value="f">{{ f }}</option>
      </select>
      <input v-model.number="year" type="number" placeholder="Year" class="filter-inp" min="1900" />
      <input v-model.number="minPrice" type="number" placeholder="Min price" class="filter-inp" min="0" />
      <input v-model.number="maxPrice" type="number" placeholder="Max price" class="filter-inp" min="0" />
      <button type="button" class="btn btn-primary" @click="search">Search</button>
    </div>
    <div v-if="loading" class="loading">Loading…</div>
    <ul v-else class="grid">
      <li v-for="v in vehicles" :key="v.id" class="card" @click="router.push(`/vehicles/${v.id}`)">
        <div class="card-img-wrap">
          <img v-if="primaryImage(v)" :src="primaryImage(v)" :alt="v.title" class="card-img" />
          <div v-else class="card-img-placeholder">No image</div>
        </div>
        <div class="card-body">
          <h3 class="card-title">{{ v.year }} {{ v.make }} {{ v.model }}</h3>
          <p class="card-price">{{ formatPrice(v.price) }}</p>
          <p class="card-meta">{{ v.mileage != null ? v.mileage.toLocaleString() + ' mi' : '—' }} · {{ v.transmission }}</p>
        </div>
      </li>
    </ul>
    <div v-if="!loading && vehicles.length === 0" class="empty">No vehicles found.</div>
    <div class="pagination">
      <button
        type="button"
        class="btn"
        :disabled="pagination.current_page <= 1"
        @click="load(pagination.current_page - 1)"
      >
        Previous
      </button>
      <span class="page-num">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      <button
        type="button"
        class="btn"
        :disabled="pagination.current_page >= pagination.last_page"
        @click="load(pagination.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>

<style scoped>
.vehicle-list {
  padding: 1rem 0;
  max-width: 1100px;
  margin: 0 auto;
}
h1 {
  font-size: 1.75rem;
  margin-bottom: 1rem;
  color: var(--color-heading);
}
.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}
.filter-inp {
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  background: var(--color-background);
  color: var(--color-text);
  width: 120px;
}
.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid var(--color-border);
  background: var(--color-background-mute);
  color: var(--color-text);
  cursor: pointer;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.btn-primary {
  background: hsla(160, 100%, 37%, 1);
  color: white;
  border-color: transparent;
}
.loading, .empty {
  text-align: center;
  padding: 2rem;
  color: var(--color-text);
}
.grid {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1.25rem;
}
.card {
  border: 1px solid var(--color-border);
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow 0.2s, transform 0.2s;
  background: var(--color-background-soft);
}
.card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transform: translateY(-2px);
}
.card-img-wrap {
  aspect-ratio: 16/10;
  background: var(--color-background-mute);
}
.card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.card-img-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text);
  font-size: 0.9rem;
}
.card-body { padding: 1rem; }
.card-title {
  font-size: 1rem;
  margin: 0 0 0.25rem;
  color: var(--color-heading);
}
.card-price {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 0.25rem;
  color: hsla(160, 100%, 37%, 1);
}
.card-meta {
  font-size: 0.85rem;
  margin: 0;
  color: var(--color-text);
  opacity: 0.9;
}
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
}
.page-num { font-size: 0.9rem; }
</style>
