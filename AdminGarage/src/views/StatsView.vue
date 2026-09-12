<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api, imageUrl } from '@/api/client'
import type { VehicleStats } from '@/api/client'
import type { Vehicle } from '@/types/vehicle'

const stats = ref<VehicleStats | null>(null)
const loading = ref(true)
const error = ref('')

const selectedFilter = ref<'total' | 'available'>('total')
const vehiclesForFilter = ref<Vehicle[]>([])
const loadingVehicles = ref(false)

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'php', maximumFractionDigits: 0 }).format(n)
}

function primaryImage(v: Vehicle) {
  const img = v.images?.find((i) => i.is_primary) || v.images?.[0]
  const path = img?.image_path || ''
  return path ? imageUrl(path, img?.image_url) : ''
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    stats.value = await api.admin.getStats()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to load statistics'
    stats.value = null
  } finally {
    loading.value = false
  }
  try {
    await loadVehicles()
  } catch {
    vehiclesForFilter.value = []
  }
}

async function selectFilter(filter: 'total' | 'available') {
  if (selectedFilter.value === filter) {
    filter = 'total'
  }
  selectedFilter.value = filter
  loadingVehicles.value = true
  vehiclesForFilter.value = []
  try {
    const params: Record<string, string | number> = { per_page: 500 }
    if (filter !== 'total') params.status = filter
    const res = await api.admin.getVehicles(params) as { data?: Vehicle[] }
    vehiclesForFilter.value = Array.isArray(res?.data) ? [...res.data] : []
  } catch (e) {
    vehiclesForFilter.value = []
  } finally {
    loadingVehicles.value = false
  }
}

async function loadVehicles() {
  await selectFilter('total')
}

onMounted(load)
</script>

<template>
  <div class="stats-page">
    <div class="stats-header">
      <h1>Vehicle Statistics</h1>
      <button type="button" class="btn btn-primary" @click="load" :disabled="loading">
        {{ loading ? 'Loading…' : 'Refresh' }}
      </button>
    </div>

    <div v-if="loading" class="loading">Loading statistics…</div>
    <p v-else-if="error" class="error">{{ error }}</p>

    <template v-else-if="stats">
      <div class="stats-grid">
        <div
          class="stat-card stat-total clickable-stat"
          :class="{ active: selectedFilter === 'total' }"
          role="button"
          tabindex="0"
          @click="selectFilter('total')"
          @keydown.enter="selectFilter('total')"
        >
          <span class="stat-label">Total Vehicles</span>
          <span class="stat-value">{{ stats.total }}</span>
        </div>
        <div
          class="stat-card stat-available clickable-stat"
          :class="{ active: selectedFilter === 'available' }"
          role="button"
          tabindex="0"
          @click="selectFilter('available')"
          @keydown.enter="selectFilter('available')"
        >
          <span class="stat-label">Available</span>
          <span class="stat-value">{{ stats.available }}</span>
        </div>
      </div>

      <div class="bottom-section">
        <div class="section-header">
          <h2>
            {{ selectedFilter === 'total' ? 'All Vehicles' : selectedFilter.charAt(0).toUpperCase() + selectedFilter.slice(1) + ' Vehicles' }}
          </h2>
        </div>
        <div v-if="loadingVehicles" class="loading">Loading vehicles…</div>
        <div v-else>
          <div v-if="vehiclesForFilter.length === 0" class="empty-vehicles">No vehicles found.</div>
          <div v-else class="vehicle-grid" :key="`${selectedFilter}-${vehiclesForFilter.length}`">
            <div
              v-for="v in vehiclesForFilter"
              :key="v.id"
              class="vehicle-card"
            >
              <div class="vehicle-card-img">
                <img v-if="primaryImage(v)" :src="primaryImage(v)" :alt="v.title" />
                <div v-else class="no-img">No image</div>
              </div>
              <div class="vehicle-card-body">
                <h3 class="vehicle-card-title">{{ v.year }} {{ v.make }} {{ v.model }}</h3>
                <div class="vehicle-card-footer">
                  <span class="vehicle-card-price">{{ formatPrice(v.price) }}</span>
                  <span class="badge" :class="v.status">{{ v.status }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.stats-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 0;
  width: 100%;
}
@media (min-width: 769px) {
  .stats-page { max-width: none; }
}

.stats-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.stats-header h1 {
  font-size: 2rem;
  margin: 0;
  color: var(--color-heading);
  font-weight: 700;
}

.btn {
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: var(--color-background-mute);
  color: var(--color-text);
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
}

.btn:hover {
  border-color: var(--red-primary);
  color: var(--red-primary);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, var(--red-primary), var(--red-light));
  color: #fff;
  border-color: transparent;
  font-weight: 600;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(216, 31, 38, 0.4);
  color: #000;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: var(--color-text);
  font-size: 1.1rem;
}

.error {
  color: var(--color-accent-text);
  padding: 1rem;
  background: var(--color-accent-soft);
  border: 1px solid var(--color-border-hover);
  border-radius: 8px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid var(--color-border);
  background: var(--color-background-soft);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.stat-card:hover {
  border-color: rgba(216, 31, 38, 0.4);
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(216, 31, 38, 0.15);
}

.clickable-stat {
  cursor: pointer;
}

.clickable-stat.active {
  border-color: var(--red-primary);
  box-shadow: 0 0 0 2px rgba(216, 31, 38, 0.3);
}

.stat-label {
  font-size: 0.9rem;
  color: var(--color-text-muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--color-heading);
}

.stat-total .stat-value { color: var(--red-primary); }
.stat-available .stat-value { color: var(--red-primary); }

.bottom-section {
  margin-top: 1rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.section-header h2 {
  font-size: 1.25rem;
  margin: 0;
  color: var(--color-heading);
  font-weight: 700;
}

.vehicle-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
  list-style: none;
  padding: 0;
  margin: 0;
}

.vehicle-card {
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
  background: var(--color-background-soft);
  display: flex;
  flex-direction: column;
  cursor: default;
}

.vehicle-card-img {
  aspect-ratio: 16/10;
  background: var(--color-background-mute);
  overflow: hidden;
}

.vehicle-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.vehicle-card-img .no-img {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.vehicle-card-body {
  padding: 1rem;
}

.vehicle-card-title {
  font-size: 1rem;
  margin: 0 0 0.5rem;
  color: var(--color-text);
  font-weight: 600;
}

.vehicle-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.vehicle-card-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--red-primary);
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

/* Statuses are ranked by emphasis rather than hue, to stay inside red/black/white */
.badge.available { background: linear-gradient(135deg, var(--red-primary), var(--red-light)); color: #fff; }
.badge.reserved { background: var(--color-accent-soft); color: var(--color-accent-text); }
.badge.coming { background: transparent; color: var(--color-text-muted); border: 1px dashed var(--color-border); }
.badge.sold { background: var(--color-background-mute); color: var(--color-text-muted); }

.empty-vehicles {
  text-align: center;
  padding: 2rem;
  color: var(--color-text-muted);
}

@media (max-width: 768px) {
  .stats-page {
    padding: 0;
  }

  .stats-header {
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .stats-header h1 {
    font-size: 1.25rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
  }

  .stat-card {
    padding: 0.75rem;
  }

  .stat-value {
    font-size: 1.5rem;
  }

  .bottom-section {
    margin-top: 0.5rem;
  }

  .section-header h2 {
    font-size: 1.1rem;
  }

  .vehicle-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }

  .vehicle-card-body {
    padding: 0.5rem;
  }

  .vehicle-card-title {
    font-size: 0.85rem;
  }

  .vehicle-card-price {
    font-size: 0.95rem;
  }
}
</style>
