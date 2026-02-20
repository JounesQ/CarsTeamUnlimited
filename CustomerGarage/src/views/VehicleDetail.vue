<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { Vehicle } from '@/types/vehicle'

const route = useRoute()
const router = useRouter()
const vehicle = ref<Vehicle | null>(null)
const loading = ref(true)
const error = ref('')

const primaryImage = computed(() => {
  if (!vehicle.value?.images?.length) return ''
  const img = vehicle.value.images.find((i) => i.is_primary) || vehicle.value.images[0]
  const path = img?.image_path || ''
  return path ? imageUrl(path) : ''
})

const otherImages = computed(() => {
  if (!vehicle.value?.images?.length) return []
  const primary = vehicle.value.images.find((i) => i.is_primary) || vehicle.value.images[0]
  return vehicle.value.images.filter((i) => i.id !== primary?.id)
})

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'php', maximumFractionDigits: 0 }).format(n)
}

const sortedInstallments = computed(() => {
  const opts = vehicle.value?.financing_options
  if (!opts || !Object.keys(opts).length) return []
  return Object.entries(opts).sort(([a], [b]) => {
    const numA = parseInt(a.replace(/\D/g, ''), 10) || 0
    const numB = parseInt(b.replace(/\D/g, ''), 10) || 0
    return numA - numB
  })
})

function formatTerm(key: string) {
  const n = key.replace(/\D/g, '')
  if (key.includes('year') && !key.includes('years')) return n ? `${n} year` : key
  return n ? `${n} years` : key.replace(/_/g, ' ')
}

function formatStatus(status: string) {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

onMounted(async () => {
  const id = route.params.id as string
  if (!id) { error.value = 'Invalid vehicle'; loading.value = false; return }
  try {
    vehicle.value = await api.getVehicle(id)
  } catch (e) {
    // The API client already transforms network errors to friendly messages
    error.value = e instanceof Error ? e.message : 'Unable to load vehicle details. Please try again later.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="vehicle-detail">
    <button type="button" class="back" @click="router.push('/vehicles')">← Back to list</button>
    <div v-if="loading" class="loading">Loading…</div>
    <p v-else-if="error" class="error">{{ error }}</p>
    <template v-else-if="vehicle">
      <div class="detail-header">
        <h1>{{ vehicle.year }} {{ vehicle.make }} {{ vehicle.model }}</h1>
        <p class="price">{{ formatPrice(vehicle.price) }} <span v-if="vehicle.is_negotiable" class="neg">(negotiable)</span></p>
      </div>
      <div class="gallery">
        <div class="main-img">
          <img v-if="primaryImage" :src="primaryImage" :alt="vehicle.title" />
          <div v-else class="no-img">No image</div>
        </div>
        <div v-if="otherImages.length" class="thumbs">
          <img v-for="img in otherImages" :key="img.id" :src="imageUrl(img.image_path)" :alt="`${vehicle.title} ${img.position}`" class="thumb" />
        </div>
      </div>
      <div class="specs">
        <h1>Details</h1>
        <dl>
          <dt>Year</dt><dd>{{ vehicle.year }}</dd>
          <dt>Make / Model</dt><dd>{{ vehicle.make }} {{ vehicle.model }}</dd>
          <dt>Type</dt><dd>{{ vehicle.vehicle_type || '—' }}</dd>
          <dt>Category</dt><dd>{{ vehicle.category || '—' }}</dd>
          <dt>Transmission</dt><dd>{{ vehicle.transmission }}</dd>
          <dt>Fuel Type</dt><dd>{{ vehicle.fuel_type || '—' }}</dd>
          <dt>Color</dt><dd>{{ vehicle.color || '—' }}</dd>
          <dt>Mileage</dt><dd>{{ vehicle.mileage != null ? vehicle.mileage.toLocaleString() + ' km' : '—' }}</dd>
          <dt>Doors</dt><dd>{{ vehicle.door_count ?? '—' }}</dd>
          <dt>Seats</dt><dd>{{ vehicle.seat_capacity ?? '—' }}</dd>
          <dt>Grade</dt><dd>{{ vehicle.grade || '—' }}</dd>
        </dl>
      </div>
      <div v-if="vehicle.down_payment != null || (vehicle.financing_options && Object.keys(vehicle.financing_options).length)" class="financing">
        <h1>Financing</h1>
        <p v-if="vehicle.down_payment != null">Down payment: {{ formatPrice(vehicle.down_payment) }} <span v-if="vehicle.dp_all_in">(all-in)</span></p>
        <div v-if="vehicle.financing_options && Object.keys(vehicle.financing_options).length" class="installments">
          <h3>Monthly installments</h3>
          <table class="installments-table">
            <thead>
              <tr>
                <th>Term</th>
                <th>Monthly</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="[key, monthly] in sortedInstallments" :key="key">
                <td>{{ formatTerm(key) }}</td>
                <td>{{ formatPrice(monthly) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.vehicle-detail { max-width: 900px; margin: 0 auto; padding: 1rem 0; }
@media (min-width: 769px) {
  .vehicle-detail { max-width: none; margin: 0; padding: 1rem 0.5rem; }
}
@media (max-width: 768px) {
  .vehicle-detail { max-width: none; margin: 0; padding: 0.5rem 0.25rem; }
}
.back { background: none; border: none; color: hsla(160, 100%, 37%, 1); cursor: pointer; margin-bottom: 1rem; font-size: 1rem; }
.loading, .error { text-align: center; padding: 2rem; }
.error { color: #c00; }
.detail-header { margin-bottom: 1.5rem; }
.detail-header h1 { font-size: 1.5rem; margin: 0 0 0.25rem; color: var(--color-heading); }
.price { font-size: 1.25rem; font-weight: 600; margin: 0; }
.neg { font-weight: normal; opacity: 0.9; }
.gallery { margin-bottom: 2rem; border-radius: 10px; overflow: hidden; border: 1px solid var(--color-border); }
.main-img { aspect-ratio: 16/10; background: var(--color-background-mute); }
.main-img img, .main-img .no-img { width: 100%; height: 100%; object-fit: cover; }
.no-img { display: flex; align-items: center; justify-content: center; color: var(--color-text); }
.thumbs { display: flex; gap: 0.5rem; padding: 0.5rem; background: var(--color-background-soft); overflow-x: auto; }
.thumb { width: 80px; height: 56px; object-fit: cover; border-radius: 6px; }
.specs h2, .financing h2 { font-size: 1.1rem; margin: 0 0 0.75rem; color: var(--color-heading); }
.specs dl, .financing p { margin: 0; font-size: 0.95rem; }
.specs dl { display: grid; grid-template-columns: auto 1fr; gap: 0.25rem 2rem; }
.specs dt { color: var(--color-text); opacity: 0.85; }
.financing { margin-top: 1.5rem; }
.installments { margin-top: 0.75rem; }
.installments h3 { font-size: 0.95rem; margin: 0 0 0.5rem; }
.installments-table { border-collapse: collapse; font-size: 0.95rem; }
.installments-table th, .installments-table td { padding: 0.35rem 1rem 0.35rem 0; text-align: left; }
.installments-table th { font-weight: 600; color: var(--color-heading); }
</style>
