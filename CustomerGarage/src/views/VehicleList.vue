<script setup lang="ts">
import { ref, onMounted, watch, computed, inject } from 'vue'
import { useRoute } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { Vehicle } from '@/types/vehicle'
import { MAKES, VEHICLE_TYPES, CATEGORIES, FUEL_TYPES } from '@/data/vehicleOptions'
import { useCachedVehicles } from '@/composables/useCachedApi'

const route = useRoute()
const cachedVehicles = useCachedVehicles()
const vehicles = ref<Vehicle[]>([])
const make = ref((route.query.make as string) || '')
const vehicleType = ref((route.query.vehicle_type as string) || '')
const category = ref((route.query.category as string) || '')
const fuelType = ref((route.query.fuel_type as string) || '')
const year = ref(route.query.year ? Number(route.query.year) : undefined as number | undefined)
const minPrice = ref(route.query.min_price ? Number(route.query.min_price) : undefined as number | undefined)
const maxPrice = ref(route.query.max_price ? Number(route.query.max_price) : undefined as number | undefined)

// Inject mobile filters visibility from parent
const showMobileFilters = inject<any>('showMobileFilters', ref(false))

const modalOpen = ref(false)
const modalVehicle = ref<Vehicle | null>(null)
const modalError = ref('')

const carouselIndex = ref(0)
const selectedTerm = ref<string | null>(null)

// Group vehicles by make
const vehiclesByMake = computed(() => {
  const grouped: Record<string, Vehicle[]> = {}
  
  vehicles.value.forEach((vehicle) => {
    const makeName = vehicle.make || 'Other'
    if (!grouped[makeName]) {
      grouped[makeName] = []
    }
    grouped[makeName].push(vehicle)
  })
  
  // Sort makes alphabetically
  return Object.keys(grouped)
    .sort()
    .map((makeName) => ({
      make: makeName,
      vehicles: grouped[makeName],
    }))
})

const orderedImages = computed(() => {
  const imgs = modalVehicle.value?.images || []
  return [...imgs].sort((a, b) => {
    const priA = a.is_primary ? 1 : 0
    const priB = b.is_primary ? 1 : 0
    if (priA !== priB) return priB - priA
    return (a.position ?? 0) - (b.position ?? 0)
  })
})
const currentImageUrl = computed(() => {
  const img = orderedImages.value[carouselIndex.value]
  const path = img?.image_path || ''
  return path ? imageUrl(path) : ''
})
function setCarouselIndex(i: number) {
  if (!orderedImages.value.length) return
  const max = orderedImages.value.length - 1
  carouselIndex.value = Math.max(0, Math.min(max, i))
}
function nextImage() {
  if (orderedImages.value.length <= 1) return
  setCarouselIndex((carouselIndex.value + 1) % orderedImages.value.length)
}
function prevImage() {
  if (orderedImages.value.length <= 1) return
  setCarouselIndex((carouselIndex.value - 1 + orderedImages.value.length) % orderedImages.value.length)
}

const sortedInstallments = computed(() => {
  const opts = modalVehicle.value?.financing_options
  if (!opts || !Object.keys(opts).length) return []
  return Object.entries(opts).sort(([a], [b]) => {
    const numA = parseInt(a.replace(/\D/g, ''), 10) || 0
    const numB = parseInt(b.replace(/\D/g, ''), 10) || 0
    return numA - numB
  })
})

function primaryImageForCard(v: Vehicle) {
  const img = v.images?.find((i) => i.is_primary) || v.images?.[0]
  return img?.image_path ? imageUrl(img.image_path) : ''
}

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'php', maximumFractionDigits: 0 }).format(n)
}

function formatTerm(key: string) {
  const n = key.replace(/\D/g, '')
  if (key.includes('year') && !key.includes('years')) return n ? `${n} year` : key
  return n ? `${n} years` : key.replace(/_/g, ' ')
}

async function openModal(id: string) {
  modalError.value = ''
  carouselIndex.value = 0
  selectedTerm.value = null
  try {
    // Fetch data first before opening modal
    const vehicleData = await api.getVehicle(id)
    modalVehicle.value = vehicleData
    modalOpen.value = true
    // start carousel on primary image if exists
    const primaryIdx = orderedImages.value.findIndex((i) => i.is_primary)
    if (primaryIdx >= 0) carouselIndex.value = primaryIdx
    // Set first term as default if financing options exist
    if (vehicleData.financing_options && Object.keys(vehicleData.financing_options).length > 0) {
      selectedTerm.value = sortedInstallments.value[0]?.[0] || null
    }
  } catch (e) {
    modalError.value = e instanceof Error ? e.message : 'Failed to load vehicle'
    modalVehicle.value = null
    modalOpen.value = true // Show modal with error
  }
}

function closeModal() {
  modalOpen.value = false
  modalVehicle.value = null
  selectedTerm.value = null
}

async function load() {
  const params: Record<string, string | number> = {}
  if (make.value) params.make = make.value
  if (vehicleType.value) params.vehicle_type = vehicleType.value
  if (category.value) params.category = category.value
  if (fuelType.value) params.fuel_type = fuelType.value

  
  await cachedVehicles.load({
    params,
    onError: (e) => {
      console.error('Failed to load vehicles:', e)
    }
  })
  
  // Update local state from cached result
  if (cachedVehicles.data.value) {
    vehicles.value = cachedVehicles.data.value.data
  } else if (cachedVehicles.error.value) {
    vehicles.value = []
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
  window.history.replaceState({}, '', `${window.location.pathname}?${new URLSearchParams(q).toString()}`)
  load()
}

onMounted(() => load())
watch(() => route.query, (q) => {
  make.value = (q.make as string) || ''
  vehicleType.value = (q.vehicle_type as string) || ''
  category.value = (q.category as string) || ''
  fuelType.value = (q.fuel_type as string) || ''
  year.value = q.year ? Number(q.year) : undefined
  minPrice.value = q.min_price ? Number(q.min_price) : undefined
  maxPrice.value = q.max_price ? Number(q.max_price) : undefined
  load()
})
</script>

<template>
  <div class="vehicle-list">
    <h1>Vehicles for sale</h1>
    <div class="filters" :class="{ 'mobile-filters-open': showMobileFilters }">
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
     
      <button type="button" class="btn btn-primary" @click="search">Search</button>
    </div>
    <div v-if="cachedVehicles.loading.value" class="loading">Loading…</div>
    <div v-else-if="cachedVehicles.isStale.value" class="cache-indicator">
      Showing cached data • Refreshing in background…
    </div>
    
    <!-- Grouped by Make -->
    <div v-if="!cachedVehicles.loading.value && vehicles.length > 0" class="vehicles-by-make">
      <div v-for="group in vehiclesByMake" :key="group.make" class="make-section">
        <h2 class="make-title">{{ group.make }}</h2>
        <ul class="grid">
          <li v-for="v in group.vehicles" :key="v.id" class="card" @click="openModal(v.id)">
            <div class="card-img-wrap">
              <img v-if="primaryImageForCard(v)" :src="primaryImageForCard(v)" :alt="v.title" class="card-img" loading="lazy" />
              <div v-else class="card-img-placeholder">No image</div>
            </div>
            <div class="card-body">
              <h3 class="card-title">{{ v.year }} {{ v.make }} {{ v.model }}</h3>
              <p class="card-price">{{ formatPrice(v.price) }}</p>
            </div>
          </li>
        </ul>
      </div>
    </div>
    
    <div v-if="!cachedVehicles.loading.value && vehicles.length === 0" class="empty">
      {{ cachedVehicles.error.value || 'No vehicles found.' }}
    </div>

    <!-- Vehicle detail modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
          <div class="modal-box" @click.stop>
            <p v-if="modalError" class="modal-error">{{ modalError }}</p>
            <template v-else-if="modalVehicle">
              <div class="modal-header">
                <h2>{{ modalVehicle.year }} {{ modalVehicle.make }} {{ modalVehicle.model }}</h2>
                <p class="modal-price">{{ formatPrice(modalVehicle.price) }} <span v-if="modalVehicle.is_negotiable" class="neg">(negotiable)</span></p>
              </div>
              <div class="modal-gallery">
                <div class="modal-main-img">
                  <img v-if="currentImageUrl" :src="currentImageUrl" :alt="modalVehicle.title" />
                  <div v-else class="no-img">No image</div>
                  <button
                    v-if="orderedImages.length > 1"
                    type="button"
                    class="carousel-btn prev"
                    aria-label="Previous photo"
                    @click="prevImage"
                  >
                    ‹
                  </button>
                  <button
                    v-if="orderedImages.length > 1"
                    type="button"
                    class="carousel-btn next"
                    aria-label="Next photo"
                    @click="nextImage"
                  >
                    ›
                  </button>
                </div>
                <div v-if="orderedImages.length > 1" class="modal-thumbs">
                  <button
                    v-for="(img, idx) in orderedImages"
                    :key="img.id"
                    type="button"
                    class="thumb-btn"
                    :class="{ active: idx === carouselIndex }"
                    @click="setCarouselIndex(idx)"
                    :aria-label="`View photo ${idx + 1}`"
                  >
                    <img :src="imageUrl(img.image_path)" :alt="`${modalVehicle.title} ${img.position}`" class="modal-thumb" />
                  </button>
                </div>
              </div>
              <div class="modal-specs">
                <h3>Details</h3>
                <dl>
                  <dt>Year</dt><dd>{{ modalVehicle.year }}</dd>
                  <dt>Make / Model</dt><dd>{{ modalVehicle.make }} {{ modalVehicle.model }}</dd>
                  <dt>Transmission</dt><dd>{{ modalVehicle.transmission }}</dd>
                  <dt>Fuel</dt><dd>{{ modalVehicle.fuel_type || '—' }}</dd>
                  <dt>Color</dt><dd>{{ modalVehicle.color || '—' }}</dd>
                  <dt>Mileage</dt><dd>{{ modalVehicle.mileage != null ? modalVehicle.mileage.toLocaleString() + ' mi' : '—' }}</dd>
                  <dt>Doors</dt><dd>{{ modalVehicle.door_count ?? '—' }}</dd>
                  <dt>Seats</dt><dd>{{ modalVehicle.seat_capacity ?? '—' }}</dd>
                  <dt>Views</dt><dd>{{ modalVehicle.views_count }}</dd>
                </dl>
              </div>
              <div v-if="modalVehicle.down_payment != null || (modalVehicle.financing_options && Object.keys(modalVehicle.financing_options).length)" class="modal-financing">
                <h3>Financing</h3>
                <p v-if="modalVehicle.down_payment != null">Down payment: {{ formatPrice(modalVehicle.down_payment) }} <span v-if="modalVehicle.dp_all_in">(all-in)</span></p>
                <div v-if="modalVehicle.financing_options && Object.keys(modalVehicle.financing_options).length" class="modal-installments">
                  <h4>Monthly installments</h4>
                  <div class="term-buttons">
                    <button
                      v-for="[key, monthly] in sortedInstallments"
                      :key="key"
                      type="button"
                      class="term-btn"
                      :class="{ active: selectedTerm === key }"
                      @click="selectedTerm = key"
                    >
                      {{ formatTerm(key) }}
                    </button>
                  </div>
                  <div v-if="selectedTerm" class="monthly-amount">
                    <span class="amount-label">Monthly payment:</span>
                    <span class="amount-value">{{ formatPrice(modalVehicle.financing_options[selectedTerm]) }}</span>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.vehicle-list { padding: 2rem 1rem; max-width: 1200px; margin: 0 auto; min-height: 100vh; }
h1 { font-size: 2rem; margin-bottom: 1.5rem; color: var(--color-heading); font-weight: 700; }
.filters { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 2rem; padding: 1.5rem; background: var(--color-background-soft); border: 1px solid var(--color-border); border-radius: 12px; }
.filter-inp { padding: 0.625rem 0.875rem; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-background); color: var(--color-text); width: 140px; transition: border-color 0.3s ease; }
.filter-inp:focus { outline: none; border-color: var(--gold-primary); }
.btn { padding: 0.625rem 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; transition: all 0.3s ease; font-weight: 500; }
.btn:hover { border-color: var(--gold-primary); color: var(--gold-primary); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: linear-gradient(135deg, var(--gold-primary), var(--gold-light)); color: #000; border-color: transparent; font-weight: 600; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4); }
.loading, .empty { text-align: center; padding: 3rem; color: var(--color-text); font-size: 1.1rem; }
.cache-indicator { text-align: center; padding: 0.75rem; font-size: 0.85rem; color: var(--gold-primary); background: rgba(212, 175, 55, 0.1); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 8px; margin-bottom: 1.5rem; }

/* Vehicles grouped by make */
.vehicles-by-make { display: flex; flex-direction: column; gap: 3rem; }
.make-section { display: flex; flex-direction: column; }
.make-title {
  font-size: 1.75rem;
  font-weight: 700;
  text-align: center;
  color: var(--gold-primary);
  margin: 0 0 2rem;
  padding: 1rem 0;
  position: relative;
}
.make-title::before,
.make-title::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 100px;
  height: 2px;
  background: linear-gradient(to right, transparent, var(--gold-primary));
}
.make-title::before {
  right: calc(50% + 120px);
  background: linear-gradient(to left, var(--gold-primary), transparent);
}
.make-title::after {
  left: calc(50% + 120px);
}

.grid { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.card { border: 1px solid var(--color-border); border-radius: 12px; overflow: hidden; cursor: pointer; transition: all 0.3s ease; background: var(--color-background-soft); }
.card:hover { box-shadow: 0 8px 24px rgba(212, 175, 55, 0.2); transform: translateY(-4px); border-color: rgba(212, 175, 55, 0.4); }
.card-img-wrap { aspect-ratio: 16/10; background: var(--color-background-mute); }
.card-img { width: 100%; height: 100%; object-fit: cover; }
.card-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 0.9rem; }
.card-body { padding: 1.25rem; }
.card-title { font-size: 1.05rem; margin: 0 0 0.5rem; color: var(--color-text); font-weight: 600; }
.card-price { font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--gold-primary); }
.card-meta { font-size: 0.85rem; margin: 0; color: var(--color-text-muted); }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}
.modal-box {
  position: relative;
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  overflow-x: hidden;
  box-shadow: 0 20px 60px rgba(212, 175, 55, 0.2);
  scrollbar-width: thin;
  scrollbar-color: var(--black-lighter) var(--black-soft);
}
.modal-box::-webkit-scrollbar {
  width: 8px;
}
.modal-box::-webkit-scrollbar-track {
  background: var(--black-soft);
}
.modal-box::-webkit-scrollbar-thumb {
  background: var(--black-lighter);
  border-radius: 4px;
}
.modal-box::-webkit-scrollbar-thumb:hover {
  background: rgba(212, 175, 55, 0.3);
}
.modal-error { padding: 2rem; text-align: center; color: #ff6b6b; }
.modal-header { padding: 1.5rem 2rem 1rem; border-bottom: 1px solid var(--color-border); }
.modal-header h2 { font-size: 1.5rem; margin: 0 0 0.5rem; color: var(--color-text); font-weight: 700; }
.modal-price { font-size: 1.35rem; font-weight: 700; margin: 0; color: var(--gold-primary); }
.neg { font-weight: normal; opacity: 0.8; font-size: 0.9rem; color: var(--color-text-muted); }
.modal-gallery { padding: 1.5rem 2rem; }
.modal-main-img {
  position: relative;
  border-radius: 12px;
  background: var(--color-background-mute);
  border: 1px solid var(--color-border);
  max-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.modal-main-img img,
.modal-main-img .no-img {
  max-width: 100%;
  max-height: 60vh;
  width: auto;
  height: auto;
  object-fit: contain;
}
.no-img { display: flex; align-items: center; justify-content: center; color: var(--color-text); }
.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 2.5rem;
  height: 2.5rem;
  border: 2px solid var(--gold-primary);
  background: rgba(0, 0, 0, 0.75);
  color: var(--gold-primary);
  border-radius: 999px;
  cursor: pointer;
  font-size: 1.5rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(4px);
}
.carousel-btn:hover {
  background: rgba(212, 175, 55, 0.2);
  transform: translateY(-50%) scale(1.1);
}
.carousel-btn.prev { left: 1rem; }
.carousel-btn.next { right: 1rem; }
.modal-thumbs { display: flex; gap: 0.75rem; margin-top: 1rem; overflow-x: auto; padding-bottom: 0.5rem; }
.thumb-btn { border: 2px solid transparent; padding: 0; background: transparent; border-radius: 10px; cursor: pointer; transition: all 0.3s ease; }
.thumb-btn:hover { border-color: rgba(212, 175, 55, 0.5); }
.thumb-btn.active { border-color: var(--gold-primary); }
.modal-thumb { width: 80px; height: 56px; object-fit: cover; border-radius: 8px; display: block; }
.modal-specs, .modal-financing { padding: 1.5rem 2rem; border-top: 1px solid var(--color-border); }
.modal-specs h3, .modal-financing h3 { font-size: 1.1rem; margin: 0 0 1rem; color: var(--gold-primary); font-weight: 700; }
.modal-specs dl, .modal-financing p { margin: 0; font-size: 0.95rem; }
.modal-specs dl { display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 2rem; }
.modal-specs dt { color: var(--color-text-muted); font-weight: 500; }
.modal-specs dd { color: var(--color-text); }
.modal-installments { margin-top: 1rem; }
.modal-installments h4 { font-size: 1rem; margin: 0 0 1rem; color: var(--color-text); font-weight: 600; }
.term-buttons { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; }
.term-btn { padding: 0.75rem 1.5rem; border: 2px solid var(--color-border); border-radius: 10px; background: var(--color-background); color: var(--color-text); cursor: pointer; font-size: 0.95rem; font-weight: 600; transition: all 0.3s ease; }
.term-btn:hover { border-color: var(--gold-primary); background: rgba(212, 175, 55, 0.05); transform: translateY(-2px); }
.term-btn.active { border-color: var(--gold-primary); background: rgba(212, 175, 55, 0.15); color: var(--gold-primary); box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2); }
.monthly-amount { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(244, 208, 63, 0.05)); border-radius: 12px; border: 2px solid var(--gold-primary); }
.amount-label { font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; }
.amount-value { font-size: 2rem; font-weight: 900; color: var(--gold-primary); text-shadow: 0 0 20px rgba(212, 175, 55, 0.3); }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-box, .modal-leave-active .modal-box { transition: transform 0.2s ease; }
.modal-enter-from .modal-box, .modal-leave-to .modal-box { transform: scale(0.95); }

/* Mobile Responsive */
@media (max-width: 768px) {
  .vehicle-list {
    padding: 1rem 0.75rem 0 0.75rem;
  }

  h1 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    padding: 0 0.5rem;
  }

  /* Hide filters by default on mobile */
  .filters {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 70px;
    z-index: 2000;
    background: rgba(0, 0, 0, 0.98);
    margin: 0;
    padding: 1.5rem;
    border-radius: 0;
    border: none;
    overflow-y: auto;
    flex-direction: column;
    gap: 1rem;
  }

  /* Show filters when toggled */
  .filters.mobile-filters-open {
    display: flex;
  }

  .filter-inp {
    width: 100%;
  }

  .btn-primary {
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
  }

  .cache-indicator {
    margin: 0 0.5rem 1rem;
    font-size: 0.75rem;
    padding: 0.5rem;
  }

  /* Make sections on mobile */
  .vehicles-by-make {
    gap: 2rem;
  }

  .make-section {
    margin-bottom: 1rem;
  }

  .make-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding: 0.75rem 0;
  }

  .make-title::before,
  .make-title::after {
    display: none;
  }

  /* 2 Column Grid for Mobile */
  .grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    padding: 0 0.5rem;
  }

  .card {
    border-radius: 10px;
  }

  .card-img-wrap {
    aspect-ratio: 1;
  }

  .card-body {
    padding: 0.75rem;
  }

  .card-title {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    line-height: 1.3;
  }

  .card-price {
    font-size: 1rem;
  }

  .loading,
  .empty {
    padding: 2rem 1rem;
    font-size: 1rem;
  }

  /* Modal adjustments for mobile */
  .modal-overlay {
    padding: 0.5rem;
  }

  .modal-box {
    max-width: 100%;
    max-height: 95vh;
  }

  .modal-header {
    padding: 1rem 1.25rem 0.75rem;
  }

  .modal-header h2 {
    font-size: 1.15rem;
  }

  .modal-price {
    font-size: 1.15rem;
  }

  .modal-gallery {
    padding: 1rem 1.25rem;
  }

  .modal-main-img {
    max-height: 50vh;
  }

  .carousel-btn {
    width: 2rem;
    height: 2rem;
    font-size: 1.25rem;
  }

  .carousel-btn.prev {
    left: 0.5rem;
  }

  .carousel-btn.next {
    right: 0.5rem;
  }

  .modal-thumb {
    width: 60px;
    height: 42px;
  }

  .modal-specs,
  .modal-financing {
    padding: 1rem 1.25rem;
  }

  .modal-specs h3,
  .modal-financing h3 {
    font-size: 1rem;
  }

  .modal-specs dl {
    gap: 0.35rem 1rem;
    font-size: 0.875rem;
  }

  .term-buttons {
    gap: 0.5rem;
  }

  .term-btn {
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
  }

  .monthly-amount {
    padding: 1.25rem;
  }

  .amount-value {
    font-size: 1.75rem;
  }
}

/* Extra small screens */
@media (max-width: 400px) {
  .make-title {
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
  }

  .grid {
    gap: 0.5rem;
  }

  .card-title {
    font-size: 0.8rem;
  }

  .card-price {
    font-size: 0.95rem;
  }

  .card-body {
    padding: 0.5rem;
  }
}
</style>
