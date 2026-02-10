<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { Vehicle, Paginated } from '@/types/vehicle'
import { MAKES, VEHICLE_TYPES, CATEGORIES, FUEL_TYPES } from '@/data/vehicleOptions'

const route = useRoute()
const vehicles = ref<Vehicle[]>([])
const loading = ref(true)
const pagination = ref({ current_page: 1, last_page: 1, per_page: 12, total: 0 })
const make = ref((route.query.make as string) || '')
const vehicleType = ref((route.query.vehicle_type as string) || '')
const category = ref((route.query.category as string) || '')
const fuelType = ref((route.query.fuel_type as string) || '')
const year = ref(route.query.year ? Number(route.query.year) : undefined as number | undefined)
const minPrice = ref(route.query.min_price ? Number(route.query.min_price) : undefined as number | undefined)
const maxPrice = ref(route.query.max_price ? Number(route.query.max_price) : undefined as number | undefined)

const modalOpen = ref(false)
const modalVehicle = ref<Vehicle | null>(null)
const modalLoading = ref(false)
const modalError = ref('')

const carouselIndex = ref(0)
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
  modalOpen.value = true
  modalVehicle.value = null
  modalError.value = ''
  modalLoading.value = true
  carouselIndex.value = 0
  try {
    modalVehicle.value = await api.getVehicle(id)
    // start carousel on primary image if exists
    const primaryIdx = orderedImages.value.findIndex((i) => i.is_primary)
    if (primaryIdx >= 0) carouselIndex.value = primaryIdx
  } catch (e) {
    modalError.value = e instanceof Error ? e.message : 'Failed to load vehicle'
  } finally {
    modalLoading.value = false
  }
}

function closeModal() {
  modalOpen.value = false
  modalVehicle.value = null
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
    pagination.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total }
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
  window.history.replaceState({}, '', `${window.location.pathname}?${new URLSearchParams(q).toString()}`)
  load(1)
}

onMounted(() => load(1))
watch(() => route.query, (q) => {
  make.value = (q.make as string) || ''
  vehicleType.value = (q.vehicle_type as string) || ''
  category.value = (q.category as string) || ''
  fuelType.value = (q.fuel_type as string) || ''
  year.value = q.year ? Number(q.year) : undefined
  minPrice.value = q.min_price ? Number(q.min_price) : undefined
  maxPrice.value = q.max_price ? Number(q.max_price) : undefined
  load(1)
})
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
      <li v-for="v in vehicles" :key="v.id" class="card" @click="openModal(v.id)">
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
    <div v-if="!loading && vehicles.length === 0" class="empty">No vehicles found.</div>
    <div class="pagination">
      <button type="button" class="btn" :disabled="pagination.current_page <= 1" @click="load(pagination.current_page - 1)">Previous</button>
      <span class="page-num">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      <button type="button" class="btn" :disabled="pagination.current_page >= pagination.last_page" @click="load(pagination.current_page + 1)">Next</button>
    </div>

    <!-- Vehicle detail modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
          <div class="modal-box" @click.stop>
            <div v-if="modalLoading" class="modal-loading">Loading…</div>
            <p v-else-if="modalError" class="modal-error">{{ modalError }}</p>
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
                  <table class="installments-table">
                    <thead><tr><th>Term</th><th>Monthly</th></tr></thead>
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
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.vehicle-list { padding: 1rem 0; max-width: 1100px; margin: 0 auto; }
h1 { font-size: 1.75rem; margin-bottom: 1rem; color: var(--color-heading); }
.filters { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; }
.filter-inp { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-background); color: var(--color-text); width: 120px; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: hsla(160, 100%, 37%, 1); color: white; border-color: transparent; }
.loading, .empty { text-align: center; padding: 2rem; color: var(--color-text); }
.grid { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem; }
.card { border: 1px solid var(--color-border); border-radius: 10px; overflow: hidden; cursor: pointer; transition: box-shadow 0.2s, transform 0.2s; background: var(--color-background-soft); }
.card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-2px); }
.card-img-wrap { aspect-ratio: 16/10; background: var(--color-background-mute); }
.card-img { width: 100%; height: 100%; object-fit: cover; }
.card-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--color-text); font-size: 0.9rem; }
.card-body { padding: 1rem; }
.card-title { font-size: 1rem; margin: 0 0 0.25rem; color: var(--color-heading); }
.card-price { font-size: 1.1rem; font-weight: 600; margin: 0 0 0.25rem; color: hsla(160, 100%, 37%, 1); }
.card-meta { font-size: 0.85rem; margin: 0; color: var(--color-text); opacity: 0.9; }
.pagination { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 2rem; }
.page-num { font-size: 0.9rem; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}
.modal-box {
  position: relative;
  background: var(--color-background);
  border-radius: 12px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  overflow-x: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.modal-box::-webkit-scrollbar {
  display: none;
}
.modal-loading, .modal-error { padding: 2rem; text-align: center; }
.modal-error { color: #c00; }
.modal-header { padding: 1.25rem 1.5rem 0; }
.modal-header h2 { font-size: 1.35rem; margin: 0 0 0.25rem; color: var(--color-heading); }
.modal-price { font-size: 1.15rem; font-weight: 600; margin: 0; }
.neg { font-weight: normal; opacity: 0.9; }
.modal-gallery { padding: 1rem 1.5rem; border-radius: 10px; }
.modal-main-img {
  position: relative;
  border-radius: 8px;
  background: var(--color-background-mute);
  max-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
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
  width: 2.25rem;
  height: 2.25rem;
  border: 1px solid var(--color-border);
  background: rgba(255, 255, 255, 0.85);
  color: #111;
  border-radius: 999px;
  cursor: pointer;
  font-size: 1.5rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}
.carousel-btn.prev { left: 0.5rem; }
.carousel-btn.next { right: 0.5rem; }
@media (prefers-color-scheme: dark) {
  .carousel-btn { background: rgba(0, 0, 0, 0.55); color: #fff; }
}
.modal-thumbs { display: flex; gap: 0.5rem; margin-top: 0.75rem; overflow-x: auto; padding-bottom: 0.25rem; }
.thumb-btn { border: 2px solid transparent; padding: 0; background: transparent; border-radius: 8px; cursor: pointer; }
.thumb-btn.active { border-color: hsla(160, 100%, 37%, 1); }
.modal-thumb { width: 76px; height: 52px; object-fit: cover; border-radius: 6px; display: block; }
.modal-specs, .modal-financing { padding: 0 1.5rem 1rem; }
.modal-specs h3, .modal-financing h3 { font-size: 1rem; margin: 0 0 0.5rem; color: var(--color-heading); }
.modal-specs dl, .modal-financing p { margin: 0; font-size: 0.9rem; }
.modal-specs dl { display: grid; grid-template-columns: auto 1fr; gap: 0.2rem 1.5rem; }
.modal-specs dt { color: var(--color-text); opacity: 0.85; }
.modal-installments { margin-top: 0.5rem; }
.modal-installments h4 { font-size: 0.95rem; margin: 0 0 0.35rem; }
.installments-table { border-collapse: collapse; font-size: 0.9rem; }
.installments-table th, .installments-table td { padding: 0.25rem 0.75rem 0.25rem 0; text-align: left; }
.installments-table th { font-weight: 600; color: var(--color-heading); }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-box, .modal-leave-active .modal-box { transition: transform 0.2s ease; }
.modal-enter-from .modal-box, .modal-leave-to .modal-box { transform: scale(0.95); }
</style>
