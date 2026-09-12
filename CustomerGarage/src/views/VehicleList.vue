<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { imageUrl } from '@/api/client'
import type { Vehicle } from '@/types/vehicle'
import { MAKES } from '@/data/vehicleOptions'
import { useCachedVehicles, getCachedVehicle } from '@/composables/useCachedApi'

const route = useRoute()
const cachedVehicles = useCachedVehicles()
const vehicles = ref<Vehicle[]>([])
const make = ref((route.query.make as string) || '')
const status = ref((route.query.status as string) || '')
const testDrive = ref((route.query.test_drive as string) || '')

const MAKE_LOGOS: Record<string, string> = {
  Honda: '/BrandLogo/honda logo.png',
  Mitsubishi: '/BrandLogo/mitsu logo.png',
  Hyundai: '/BrandLogo/hyundai-logo.png',
  Nissan: '/BrandLogo/nissan-logo.png',
  Suzuki: '/BrandLogo/suzuki-logo.png',
  Toyota: '/BrandLogo/toyota-logo.png',
  Changan: '/BrandLogo/changan-logo.png',
  Chery: '/BrandLogo/chery-logo.png',
  Chevrolet: '/BrandLogo/chevrolet-logo.png',
  Ford: '/BrandLogo/ford-logo.png',
  Foton: '/BrandLogo/foton-logo.png',
  GAC: '/BrandLogo/gac-logo.png',
  Geely: '/BrandLogo/geely-logo.png',
  JAC: '/BrandLogo/jac-logo.png',
  Kia: '/BrandLogo/kia-logo.png',
  Peugeot: '/BrandLogo/peugeot-logo.png',
  SsangYong: '/BrandLogo/ssangyong-logo.png',
  Subaru: '/BrandLogo/subaru-logo.png',

}

function makeLogoSrc(make: string) {
  const path = MAKE_LOGOS[make]
  return path ? encodeURI(path) : ''
}

const STATUS_OPTIONS = [
  { value: '', label: 'All statuses' },
  { value: 'available', label: 'Available' },
  { value: 'reserved', label: 'Reserved' },
  { value: 'coming', label: 'Coming' },
]

const hasActiveFilters = computed(() => Boolean(make.value || status.value || testDrive.value))

function clearFilters() {
  make.value = ''
  status.value = ''
  testDrive.value = ''
}

const modalOpen = ref(false)
const modalVehicle = ref<Vehicle | null>(null)
const modalError = ref('')
const listEl = ref<HTMLElement | null>(null)
const filtersEl = ref<HTMLElement | null>(null)
const filtersCollapsed = ref(false)
let filtersObserver: ResizeObserver | null = null
let lastScrollY = 0
let scrollTicking = false

function syncHeaderOffset() {
  const header = document.querySelector('.site-header') as HTMLElement | null
  listEl.value?.style.setProperty('--header-offset', `${header?.offsetHeight ?? 0}px`)
}

function syncFilterOffset() {
  syncHeaderOffset()
  const filterHeight = filtersCollapsed.value ? 0 : (filtersEl.value?.offsetHeight ?? 0)
  listEl.value?.style.setProperty('--filters-offset', `${filterHeight}px`)
}

function onPageScroll() {
  if (scrollTicking || modalOpen.value) return
  scrollTicking = true
  window.requestAnimationFrame(() => {
    const y = window.scrollY
    const delta = y - lastScrollY
    let next = filtersCollapsed.value
    if (y < 80) next = false
    else if (delta > 12) next = true
    else if (delta < -12) next = false
    if (next !== filtersCollapsed.value) {
      filtersCollapsed.value = next
      if (next) {
        listEl.value?.style.setProperty('--filters-offset', '0px')
      } else {
        window.setTimeout(() => {
          if (!filtersCollapsed.value) {
            const height = filtersEl.value?.offsetHeight ?? 0
            listEl.value?.style.setProperty('--filters-offset', `${height}px`)
          }
        }, 420)
      }
    }
    lastScrollY = y
    scrollTicking = false
  })
}

const carouselIndex = ref(0)

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
  return [...imgs].sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
})
const currentImageUrl = computed(() => {
  const img = orderedImages.value[carouselIndex.value]
  const path = img?.image_path || ''
  return path ? imageUrl(path, img?.image_url) : ''
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

function primaryImageForCard(v: Vehicle) {
  const img = v.images?.find((i) => i.is_primary) || v.images?.[0]
  return img?.image_path ? imageUrl(img.image_path, img.image_url) : ''
}

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'php', maximumFractionDigits: 0 }).format(n)
}

function formatStatus(status: string) {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

async function openModal(id: string) {
  modalError.value = ''
  carouselIndex.value = 0
  try {
    // Fetch data (with caching) before opening modal
    const vehicleData = await getCachedVehicle(id)
    modalVehicle.value = vehicleData
    modalOpen.value = true
    // Push history so browser Back closes modal instead of leaving the page
    const url = window.location.pathname + window.location.search
    window.history.pushState({ vehicleModal: true }, '', url)
    // start carousel on first image
    carouselIndex.value = 0
  } catch (e) {
    // The API client already transforms network errors to friendly messages
    modalError.value = e instanceof Error ? e.message : 'Unable to load vehicle details. Please try again later.'
    modalVehicle.value = null
    modalOpen.value = true // Show modal with error
  }
}

function closeModal() {
  modalOpen.value = false
  modalVehicle.value = null
}

// When user presses browser Back while modal is open, close modal and stay on VehicleList
function onPopState() {
  if (modalOpen.value) {
    closeModal()
  }
}

// Close via X or overlay: go back so the state we pushed is removed; popstate will then call closeModal()
function handleCloseModal() {
  window.history.back()
}

async function load() {
  const params: Record<string, string | number | boolean> = { per_page: 500 }
  if (make.value) params.make = make.value
  if (status.value) params.status = status.value
  if (testDrive.value === '1') params.can_test_drive = true
  if (testDrive.value === '0') params.can_test_drive = false

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

function applyFilters() {
  const q: Record<string, string> = {}
  if (make.value) q.make = make.value
  if (status.value) q.status = status.value
  if (testDrive.value) q.test_drive = testDrive.value
  const qs = new URLSearchParams(q).toString()
  window.history.replaceState({}, '', `${window.location.pathname}${qs ? `?${qs}` : ''}`)
  load()
}

onMounted(() => {
  load()
  window.addEventListener('popstate', onPopState)
  lastScrollY = window.scrollY
  syncFilterOffset()
  filtersObserver = new ResizeObserver(syncHeaderOffset)
  if (filtersEl.value) filtersObserver.observe(filtersEl.value)
  const header = document.querySelector('.site-header')
  if (header) filtersObserver.observe(header)
  window.addEventListener('scroll', onPageScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('popstate', onPopState)
  window.removeEventListener('scroll', onPageScroll)
  filtersObserver?.disconnect()
  filtersObserver = null
})

// Sync filters from URL (e.g. back/forward, direct link); applyFilters() will run via ref watch and load
watch(() => route.query, (q) => {
  make.value = (q.make as string) || ''
  status.value = (q.status as string) || ''
  testDrive.value = (q.test_drive as string) || ''
}, { immediate: false })

// Auto-search when any filter changes (no Search button click needed)
watch([make, status, testDrive], () => {
  applyFilters()
}, { deep: true })
</script>

<template>
  <div ref="listEl" class="vehicle-list">
    <h1>Vehicles for sale</h1>
    <div ref="filtersEl" class="filters" :class="{ collapsed: filtersCollapsed }">
      <div class="filter-row">
        <span class="filter-label">Status</span>
        <div class="chip-scroll">
          <button
            v-for="opt in STATUS_OPTIONS"
            :key="opt.value"
            type="button"
            class="chip"
            :class="{ active: status === opt.value }"
            :aria-pressed="status === opt.value"
            @click="status = opt.value"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <div class="filter-row">
        <span class="filter-label">Make</span>
        <div class="chip-scroll">
          <button
            type="button"
            class="chip"
            :class="{ active: make === '' }"
            :aria-pressed="make === ''"
            @click="make = ''"
          >
            All makes
          </button>
          <button
            v-for="m in MAKES"
            :key="m"
            type="button"
            class="chip"
            :class="{ active: make === m }"
            :aria-pressed="make === m"
            @click="make = m"
          >
            <img
              v-if="makeLogoSrc(m)"
              :src="makeLogoSrc(m)"
              alt=""
              class="chip-logo"
            />
            {{ m }}
          </button>
        </div>
      </div>

      <div class="filter-foot">
        <div class="filter-foot-left">
          <span class="result-count">
            {{ vehicles.length }} {{ vehicles.length === 1 ? 'vehicle' : 'vehicles' }}
          </span>
          <button v-if="hasActiveFilters" type="button" class="clear-btn" @click="clearFilters">
            Clear filters
          </button>
        </div>
        <div class="td-filter">
          <span class="td-filter-label">Can test drive:</span>
          <span class="td-side" :class="{ active: testDrive === '0' }">No</span>
          <button
            type="button"
            class="td-switch"
            :class="{ on: testDrive === '1' }"
            role="switch"
            :aria-checked="testDrive === '1'"
            aria-label="Can test drive"
            @click="testDrive = testDrive === '1' ? '0' : '1'"
          >
            <span class="td-knob"></span>
          </button>
          <span class="td-side" :class="{ active: testDrive === '1' }">Yes</span>
        </div>
      </div>
    </div>
    <!-- Skeleton loading (first load) -->
    <div v-if="cachedVehicles.loading.value && !cachedVehicles.data.value" class="vehicles-by-make skeleton-loading">
      <div class="make-section">
        <div class="make-title skeleton-title"></div>
        <ul class="grid">
          <li v-for="i in 8" :key="i" class="card skeleton-card">
            <div class="card-img-wrap skeleton-img"></div>
            <div class="card-body">
              <div class="skeleton-line skeleton-title-line"></div>
              <div class="card-footer">
                <div class="skeleton-line skeleton-price"></div>
                <div class="skeleton-badge"></div>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <div class="make-section">
        <div class="make-title skeleton-title"></div>
        <ul class="grid">
          <li v-for="i in 4" :key="'b' + i" class="card skeleton-card">
            <div class="card-img-wrap skeleton-img"></div>
            <div class="card-body">
              <div class="skeleton-line skeleton-title-line"></div>
              <div class="card-footer">
                <div class="skeleton-line skeleton-price"></div>
                <div class="skeleton-badge"></div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Grouped by Make -->
    <div v-if="!cachedVehicles.loading.value && vehicles.length > 0" class="vehicles-by-make">
      <div v-for="group in vehiclesByMake" :key="group.make" class="make-section">
        <h2 class="make-title">
          <span class="make-rule" aria-hidden="true"></span>
          <span class="make-title-inner">
            <img
              v-if="makeLogoSrc(group.make)"
              :src="makeLogoSrc(group.make)"
              :alt="`${group.make} logo`"
              class="make-logo"
            />
            <span>{{ group.make }}</span>
          </span>
          <span class="make-rule make-rule-end" aria-hidden="true"></span>
        </h2>
        <ul class="grid">
          <li v-for="v in group.vehicles" :key="v.id" class="card" @click="openModal(v.id)">
            <div class="card-img-wrap">
              <img v-if="primaryImageForCard(v)" :src="primaryImageForCard(v)" :alt="v.title" class="card-img" loading="lazy" />
              <div v-else class="card-img-placeholder">No image</div>
            </div>
            <div class="card-body">
              <h3 class="card-title">{{ v.title || v.make }}</h3>
              <div class="card-footer">
                <p class="card-price">{{ formatPrice(v.price) }}</p>
                <div class="card-tags">
                  <span class="td-pill" :class="v.can_test_drive === false ? 'no' : 'yes'">
                    {{ v.can_test_drive === false ? 'No test drive' : 'Test drive' }}
                  </span>
                  <span v-if="['available','reserved','coming'].includes(v.status)" class="status-badge" :class="v.status">{{ formatStatus(v.status) }}</span>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
    
    <div v-if="!cachedVehicles.loading.value && vehicles.length === 0" class="empty">
      {{ cachedVehicles.error.value || 'No Vehicle Available' }}
    </div>

    <!-- Vehicle detail modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="modalOpen" class="modal-overlay" @click.self="handleCloseModal">
          <div class="modal-box" @click.stop>
            <div class="modal-logo-bg" area-hidden="true"></div>
            <button type="button" class="modal-close-btn" area-label="Close" @click="handleCloseModal">×</button>
            <p v-if="modalError" class="modal-error">{{ modalError }}</p>
            <template v-else-if="modalVehicle">
              <div class="modal-header">
                <h2>{{ modalVehicle.title || modalVehicle.make }}</h2>
                <p class="modal-price">{{ formatPrice(modalVehicle.price) }}</p>
                <span class="td-pill" :class="modalVehicle.can_test_drive === false ? 'no' : 'yes'">
                  {{ modalVehicle.can_test_drive === false ? 'No test drive' : 'Available for test drive' }}
                </span>
              </div>
              <div class="modal-gallery">
                <div class="modal-main-img">
                  <img v-if="currentImageUrl" :src="currentImageUrl" :alt="modalVehicle.title" />
                  <div v-else class="no-img">No image</div>
                  <button
                    v-if="orderedImages.length > 1"
                    type="button"
                    class="carousel-btn prev"
                    area-label="Previous photo"
                    @click="prevImage"
                  >
                    ‹
                  </button>
                  <button
                    v-if="orderedImages.length > 1"
                    type="button"
                    class="carousel-btn next"
                    area-label="Next photo"
                    @click="nextImage"
                  >
                    ›
                  </button>
                </div>
                <div v-if="orderedImages.length > 1" class="modal-thumbs">
                  <button
                    v-for="(img, idx) in orderedImages"
                    :key="img.id ?? img.image_path ?? idx"
                    type="button"
                    class="thumb-btn"
                    :class="{ active: idx === carouselIndex }"
                    @click="setCarouselIndex(idx)"
                    :area-label="`View photo ${idx + 1}`"
                  >
                    <img :src="imageUrl(img.image_path, img.image_url)" :alt="`${modalVehicle.title} ${img.position}`" class="modal-thumb" />
                  </button>
                </div>
              </div>
              <div v-if="modalVehicle.details_and_financing" class="modal-specs">
                <div class="modal-details-logo-bg" area-hidden="true"></div>
                <h3>Details & Financing</h3>
                <div class="modal-detail-text">{{ modalVehicle.details_and_financing }}</div>
              </div>
            </template>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.vehicle-list {
  --header-offset: 0px;
  --filters-offset: 0px;
  padding: 2rem 0;
  margin: 0;
  max-width: 100%;
  min-height: 100vh;
}
h1 { font-size: 2rem; margin-bottom: 1.5rem; color: var(--color-heading); font-weight: 700; }
.filters {
  position: sticky;
  top: var(--header-offset);
  z-index: 60;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  margin: 0 -1rem 0;
  padding: 0.85rem 1rem;
  background: var(--color-header-bg);
  border: 0 solid var(--color-border);
  border-bottom-width: 1px;
  backdrop-filter: blur(14px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
  transform: translate3d(0, 0, 0);
  will-change: transform;
  transition: transform 0.42s cubic-bezier(0.22, 1, 0.36, 1);
}
.filters.collapsed {
  transform: translate3d(0, calc(-100% - 8px), 0);
  box-shadow: none;
  pointer-events: none;
}
@media (prefers-reduced-motion: reduce) {
  .filters {
    transition: none;
  }
}

.filter-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  min-width: 0;
}

.filter-label {
  flex-shrink: 0;
  width: 3.5rem;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--color-text-muted);
}

/* Chips overflow horizontally rather than wrapping, keeping the bar one row tall */
.chip-scroll {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
  -webkit-overflow-scrolling: touch;
  padding: 2px;
  margin: -2px;
}

.chip-scroll::-webkit-scrollbar { display: none; }

.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  flex-shrink: 0;
  padding: 0.45rem 1rem;
  border-radius: 999px;
  border: 1px solid var(--color-border);
  background: var(--color-background);
  color: var(--color-text);
  font-size: 0.85rem;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.25s ease;
}

.chip-logo {
  width: 1.1rem;
  height: 1.1rem;
  object-fit: contain;
  flex-shrink: 0;
}

.chip:hover {
  border-color: var(--red-primary);
  color: var(--red-primary);
}

.chip:focus-visible {
  outline: 2px solid var(--red-primary);
  outline-offset: 2px;
}

.chip.active {
  background: linear-gradient(135deg, #d81f26 0%, #f0353d 100%);
  border-color: transparent;
  color: #fff;
  font-weight: 600;
  box-shadow: 0 3px 12px rgba(216, 31, 38, 0.35);
}

.filter-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  padding-left: 4.35rem;
}

.filter-foot-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.td-filter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: auto;
}

.td-filter-label {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--color-text-muted);
  white-space: nowrap;
  margin-right: 0.25rem;
}

.td-side {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  color: var(--color-text-muted);
}

.td-side.active {
  color: var(--color-heading);
}

.td-switch {
  position: relative;
  width: 48px;
  height: 28px;
  padding: 0;
  border: 0;
  border-radius: 999px;
  background: var(--color-background-mute);
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.2s ease;
}

.td-switch.on {
  background: linear-gradient(135deg, var(--red-primary), var(--red-light));
}

.td-knob {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--white-pure);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.28);
  transition: left 0.2s ease;
}

.td-switch.on .td-knob {
  left: 22px;
}

.result-count {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  letter-spacing: 0.5px;
}

.clear-btn {
  padding: 0;
  border: 0;
  background: none;
  color: var(--red-primary);
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 3px;
  cursor: pointer;
}

.clear-btn:hover { color: var(--red-light); }
.btn { padding: 0.625rem 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; transition: all 0.3s ease; font-weight: 500; }
.btn:hover { border-color: var(--red-primary); color: var(--red-primary); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }

.loading, .empty { text-align: center; padding: 3rem 0; color: var(--color-text); font-size: 1.1rem; }

/* Skeleton loading */
.skeleton-loading .skeleton-card {
  cursor: default;
  pointer-events: none;
}
.skeleton-loading .skeleton-card:hover {
  transform: none;
  box-shadow: none;
}
.skeleton-img,
.skeleton-title,
.skeleton-line,
.skeleton-badge {
  background: linear-gradient(90deg, var(--color-background-mute) 25%, var(--color-border) 50%, var(--color-background-mute) 75%);
  background-size: 200% 100%;
  animation: skeleton-shimmer 1.2s ease-in-out infinite;
  border-radius: 6px;
}
.skeleton-title {
  height: 1.75rem;
  width: 140px;
  margin: 0 auto 2rem;
}
.skeleton-img {
  width: 100%;
  height: 100%;
  min-height: 100%;
}
.skeleton-title-line {
  height: 1.05rem;
  width: 85%;
  margin-bottom: 0.5rem;
}
.skeleton-price {
  height: 1.25rem;
  width: 5rem;
}
.skeleton-badge {
  height: 1.5rem;
  width: 4.5rem;
  border-radius: 6px;
}
@keyframes skeleton-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Vehicles grouped by make */
.vehicles-by-make { display: flex; flex-direction: column; gap: 3rem; }
.make-section { display: flex; flex-direction: column; }
.make-title {
  position: sticky;
  top: calc(var(--header-offset) + var(--filters-offset));
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  font-size: 1.75rem;
  font-weight: 700;
  text-align: center;
  color: var(--red-primary);
  -webkit-text-fill-color: var(--red-primary);
  -webkit-text-stroke: 0.04em var(--white-pure);
  paint-order: stroke fill;
  margin: 0 0 1.25rem;
  padding: 0;
  background: var(--color-background);
  box-shadow: 0 2px 0 var(--color-background);
}
.make-title-inner {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
}
.make-logo {
  height: 1.35em;
  width: auto;
  object-fit: contain;
}
.make-rule {
  width: 100px;
  height: 2px;
  background: linear-gradient(to left, var(--red-primary), transparent);
  flex-shrink: 0;
}
.make-rule-end {
  background: linear-gradient(to right, var(--red-primary), transparent);
}

.grid { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.card { border: 1px solid var(--color-border); border-radius: 12px; overflow: hidden; cursor: pointer; transition: all 0.3s ease; background: var(--color-background-soft); }
.card:hover { box-shadow: 0 8px 24px rgba(216, 31, 38, 0.2); transform: translateY(-4px); border-color: rgba(216, 31, 38, 0.4); }
.card-img-wrap { aspect-ratio: 16/10; background: var(--color-background-mute); }
.card-img { width: 100%; height: 100%; object-fit: cover; }
.card-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 0.9rem; }
.card-body { padding: 1.25rem; }
.card-footer { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
.card-tags { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; justify-content: flex-end; }
.td-pill {
  padding: 0.3rem 0.65rem;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  border-radius: 999px;
  white-space: nowrap;
}
.td-pill.yes {
  background: var(--color-accent-soft);
  color: var(--color-accent-text);
  border: 1px solid var(--color-border-hover);
}
.td-pill.no {
  background: var(--color-background-mute);
  color: var(--color-text-muted);
  border: 1px solid var(--color-card-border);
}
.status-badge { padding: 0.375rem 0.75rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 6px; white-space: nowrap; }
.status-badge.available { background: linear-gradient(135deg, var(--red-primary), var(--red-light)); color: #fff; }
/* Statuses are ranked by emphasis rather than hue, to stay inside red/black/white */
.status-badge.reserved { background: var(--color-accent-soft); color: var(--color-accent-text); border: 1px solid var(--color-border-hover); }
.status-badge.coming { background: var(--color-background-mute); color: var(--color-text-muted); border: 1px solid var(--color-card-border); }
.status-badge.sold { background: var(--color-background-mute); color: var(--color-text-muted); border: 1px solid var(--color-card-border); }
.card-title { font-size: 1.05rem; margin: 0 0 0.5rem; color: var(--color-text); font-weight: 600; }
.card-price { font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--red-primary); }
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
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  overflow-x: hidden;
  box-shadow: 0 20px 60px rgba(216, 31, 38, 0.2);
  scrollbar-width: thin;
  scrollbar-color: var(--color-background-mute) var(--color-background-soft);
}
.modal-logo-bg {
  display: none;
}
.modal-specs {
  overflow: hidden;
}
.modal-details-logo-bg {
  display: block;
  position: absolute;
  inset: 0;
  z-index: 0;
  background: url('/ctu-logo.svg') center center no-repeat;
  background-size: 100% auto;
  opacity: 0.02;
  pointer-events: none;
}
.modal-close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 10;
  width: 2.5rem;
  height: 2.5rem;
  padding: 0;
  border: 2px solid var(--color-border);
  border-radius: 50%;
  background: var(--color-background-mute);
  color: var(--color-text);
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}
.modal-close-btn:hover {
  border-color: var(--red-primary);
  color: var(--red-primary);
  background: rgba(216, 31, 38, 0.1);
}
.modal-box::-webkit-scrollbar {
  width: 8px;
}
.modal-box::-webkit-scrollbar-track {
  background: var(--color-background-soft);
}
.modal-box::-webkit-scrollbar-thumb {
  background: var(--color-background-mute);
  border-radius: 4px;
}
.modal-box::-webkit-scrollbar-thumb:hover {
  background: rgba(216, 31, 38, 0.3);
}
.modal-error { position: relative; z-index: 1; padding: 2rem; text-align: center; color: var(--color-accent-text); }
.modal-header { position: relative; z-index: 1; padding: 1.5rem 3.5rem 1rem 2rem; border-bottom: 1px solid var(--color-border); }
.modal-header h2 { font-size: 1.5rem; margin: 0 0 0.5rem; color: var(--color-text); font-weight: 700; }
.modal-header .td-pill { display: inline-block; margin-top: 0.65rem; }
.modal-price { font-size: 1.35rem; font-weight: 700; margin: 0; color: var(--red-primary); }
.neg { font-weight: normal; opacity: 0.8; font-size: 0.9rem; color: var(--color-text-muted); }
.modal-gallery { position: relative; z-index: 1; padding: 1.5rem 2rem; }
.modal-main-img {
  position: relative;
  width: 100%;
  aspect-ratio: 16/10;
  border-radius: 12px;
  background: var(--color-background-mute);
  border: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.modal-main-img img,
.modal-main-img .no-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.no-img { display: flex; align-items: center; justify-content: center; color: var(--color-text); }
.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 2.5rem;
  height: 2.5rem;
  border: 2px solid var(--red-primary);
  background: rgba(0, 0, 0, 0.75);
  color: var(--red-primary);
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
  background: rgba(216, 31, 38, 0.2);
  transform: translateY(-50%) scale(1.1);
}
.carousel-btn.prev { left: 1rem; }
.carousel-btn.next { right: 1rem; }
.modal-thumbs { display: flex; gap: 0.75rem; margin-top: 1rem; overflow-x: auto; padding-bottom: 0.5rem; }
.thumb-btn { border: 2px solid transparent; padding: 0; background: transparent; border-radius: 10px; cursor: pointer; transition: all 0.3s ease; }
.thumb-btn:hover { border-color: rgba(216, 31, 38, 0.5); }
.thumb-btn.active { border-color: var(--red-primary); }
.modal-thumb { width: 80px; height: 56px; object-fit: cover; border-radius: 8px; display: block; }
.modal-specs, .modal-financing { position: relative; z-index: 1; padding: 1.5rem 2rem; border-top: 1px solid var(--color-border); }
.modal-specs h3, .modal-financing h3 { font-size: 1.1rem; margin: 0 0 1rem; color: var(--red-primary); font-weight: 700; }
.modal-specs dl, .modal-financing p { margin: 0; font-size: 0.95rem; }
.modal-specs dl { display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 2rem; }
.modal-specs dt { color: var(--color-text-muted); font-weight: 500; }
.modal-specs dd { color: var(--color-text); }
.modal-detail-text { white-space: pre-wrap; word-break: break-word; color: var(--color-text); font-size: 0.95rem; line-height: 1.6; }
.modal-installments { margin-top: 1rem; }
.modal-installments h4 { font-size: 1rem; margin: 0 0 1rem; color: var(--color-text); font-weight: 600; }
.term-buttons { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; }
.term-btn { padding: 0.75rem 1.5rem; border: 2px solid var(--color-border); border-radius: 10px; background: var(--color-background); color: var(--color-text); cursor: pointer; font-size: 0.95rem; font-weight: 600; transition: all 0.3s ease; }
.term-btn:hover { border-color: var(--red-primary); background: rgba(216, 31, 38, 0.05); transform: translateY(-2px); }
.term-btn.active { border-color: var(--red-primary); background: rgba(216, 31, 38, 0.15); color: var(--red-primary); box-shadow: 0 4px 12px rgba(216, 31, 38, 0.2); }
.monthly-amount { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem 1.25rem; background: linear-gradient(135deg, rgba(216, 31, 38, 0.1), rgba(240, 53, 61, 0.05)); border-radius: 10px; border: 2px solid var(--red-primary); }
.amount-label { font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.5px; }
.amount-value { font-size: 1.35rem; font-weight: 900; color: var(--red-primary); }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-box, .modal-leave-active .modal-box { transition: transform 0.2s ease; }
.modal-enter-from .modal-box, .modal-leave-to .modal-box { transform: scale(0.95); }

/* Desktop: details content above logo */
@media (min-width: 769px) {
  .modal-specs h3,
  .modal-specs dl {
    position: relative;
    z-index: 1;
  }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .vehicle-list {
    padding: 0;
  }

  .filters {
    margin-left: -1rem;
    margin-right: -1rem;
    padding: 0.65rem 1rem 0.75rem;
    gap: 0.6rem;
  }

  h1 {
    font-size: 1.25rem;
    margin: 0 0 0.5rem;
    padding: 0;
  }

  /* Filters stay inline on mobile; the chip rows scroll instead of opening a panel */
  .filters {
    gap: 0.6rem;
    margin-bottom: 0;
    padding: 0.65rem 0;
  }

  .filter-row {
    gap: 0.6rem;
  }

  .filter-label {
    width: 2.9rem;
    font-size: 0.62rem;
    letter-spacing: 1px;
  }

  .chip {
    padding: 0.4rem 0.85rem;
    font-size: 0.8rem;
  }

  .filter-foot {
    padding-left: 0;
  }

  /* Make sections on mobile */
  .vehicles-by-make {
    gap: 1rem;
  }

  .make-section {
    margin-bottom: 0.5rem;
  }

  .make-title {
    font-size: 1.1rem;
    margin: 0 0 0.5rem;
    padding: 0;
    gap: 0.65rem;
  }

  .make-rule {
    width: 40px;
  }

  /* 2 Column Grid for Mobile */
  .grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    padding: 0;
  }

  .card {
    border-radius: 10px;
  }

  .card-img-wrap {
    aspect-ratio: 1;
  }

  .card-body {
    padding: 0.5rem;
  }

  .card-footer {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
  }

  .card-title {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    line-height: 1.3;
  }

  .card-price {
    font-size: 1rem;
  }

  .status-badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.6rem;
  }

  .loading,
  .empty {
    padding: 1rem 0;
    font-size: 1rem;
  }

  /* Modal adjustments for mobile */
  .modal-overlay {
    padding: 0.25rem;
  }

  .modal-box {
    max-width: 100%;
    max-height: 95vh;
  }

  .modal-header {
    padding: 0.75rem 2.5rem 0.5rem 0.75rem;
  }

  .modal-header h2 {
    font-size: 1.15rem;
  }

  .modal-price {
    font-size: 1.15rem;
  }

  .modal-gallery {
    padding: 0.5rem 0.5rem;
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
    padding: 0.75rem 0.5rem;
  }

  .modal-specs h3,
  .modal-financing h3 {
    font-size: 1rem;
  }

  .modal-specs h3,
  .modal-specs dl {
    position: relative;
    z-index: 1;
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
    padding: 0.75rem 1rem;
  }

  .amount-label {
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
  }

  .amount-value {
    font-size: 1.2rem;
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
