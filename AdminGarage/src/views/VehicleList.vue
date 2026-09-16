<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { Vehicle, VehicleForm, Paginated } from '@/types/vehicle'
import MakePicker from '@/components/MakePicker.vue'
import { makeLogoSrc } from '@/data/makeLogos'

const route = useRoute()
const router = useRouter()
const vehicles = ref<Vehicle[]>([])
const loading = ref(true)
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const statusFilter = ref((route.query.status as string) || '')
const makeFilter = ref((route.query.make as string) || '')
const testDriveFilter = ref((route.query.test_drive as string) || '')

// Modal state
const modalOpen = ref(false)
const selectedVehicle = ref<Vehicle | null>(null)
const carouselIndex = ref(0)
const editingStatus = ref(false)
const newStatus = ref('')
const actionLoading = ref(false)
const selectedTerm = ref<string | null>(null)

const sortedInstallments = computed(() => {
  const opts = selectedVehicle.value?.financing_options
  if (!opts || !Object.keys(opts).length) return []
  return Object.entries(opts).sort(([a], [b]) => {
    const numA = parseInt(a.replace(/\D/g, ''), 10) || 0
    const numB = parseInt(b.replace(/\D/g, ''), 10) || 0
    return numA - numB
  })
})

// Confirmation modal state
const confirmModalOpen = ref(false)
const confirmModalTitle = ref('')
const confirmModalMessage = ref('')
const confirmModalType = ref<'status' | 'delete' | 'edit'>('status')
const confirmModalLoading = ref(false)

const STATUS_OPTIONS = [
  { value: 'available', label: 'Available' },
  { value: 'sold', label: 'Sold' },
  { value: 'reserved', label: 'Reserved' },
  { value: 'coming', label: 'Coming' },
]

const FILTER_STATUS_OPTIONS = [
  { value: '', label: 'All statuses' },
  { value: 'available', label: 'Available' },
  { value: 'reserved', label: 'Reserved' },
  { value: 'coming', label: 'Coming' },
  { value: 'sold', label: 'Sold' },
]

const makePickerOpen = ref(false)
const hasActiveFilters = computed(() => Boolean(statusFilter.value || makeFilter.value || testDriveFilter.value))
const selectedMakeLabel = computed(() => makeFilter.value || 'All makes')

function clearFilters() {
  statusFilter.value = ''
  makeFilter.value = ''
  testDriveFilter.value = ''
}

function openMakePicker() {
  makePickerOpen.value = true
}

function closeMakePicker() {
  makePickerOpen.value = false
}

function pickMake(value: string) {
  makeFilter.value = value
  closeMakePicker()
}

function primaryImage(v: Vehicle) {
  const img = v.images?.find((i) => i.is_primary) || v.images?.[0]
  const path = img?.image_path || ''
  return path ? imageUrl(path, img?.image_url) : ''
}

function sortedImages(v: Vehicle) {
  const imgs = v.images ?? []
  return [...imgs].sort((a, b) => a.position - b.position)
}

function imageSrc(path: string, resolvedUrl?: string | null) {
  return path ? imageUrl(path, resolvedUrl) : ''
}

function carouselPrev() {
  const imgs = selectedVehicle.value ? sortedImages(selectedVehicle.value) : []
  if (imgs.length <= 1) return
  carouselIndex.value = (carouselIndex.value - 1 + imgs.length) % imgs.length
}

function carouselNext() {
  const imgs = selectedVehicle.value ? sortedImages(selectedVehicle.value) : []
  if (imgs.length <= 1) return
  carouselIndex.value = (carouselIndex.value + 1) % imgs.length
}

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'php', maximumFractionDigits: 0 }).format(n)
}

function formatTerm(key: string) {
  const n = key.replace(/\D/g, '')
  if (key.includes('year') && !key.includes('years')) return n ? `${n} year` : key
  return n ? `${n} years` : key.replace(/_/g, ' ')
}

function syncUrl(page: number) {
  const q: Record<string, string> = {}
  if (page > 1) q.page = String(page)
  if (statusFilter.value) q.status = statusFilter.value
  if (makeFilter.value) q.make = makeFilter.value
  if (testDriveFilter.value) q.test_drive = testDriveFilter.value
  router.replace({ path: route.path, query: q })
}

async function load(page = 1) {
  loading.value = true
  try {
    const params: Record<string, string | number | boolean> = { page, per_page: 10 }
    if (statusFilter.value) params.status = statusFilter.value
    if (makeFilter.value) params.make = makeFilter.value
    if (testDriveFilter.value === '1') params.can_test_drive = true
    if (testDriveFilter.value === '0') params.can_test_drive = false
    const res = await api.admin.getVehicles(params) as Paginated<Vehicle>
    vehicles.value = Array.isArray(res?.data) ? res.data : []
    pagination.value = { current_page: res.current_page, last_page: res.last_page, per_page: res.per_page, total: res.total }
    syncUrl(page)
  } catch (e) {
    console.error(e)
    vehicles.value = []
  } finally {
    loading.value = false
  }
}

function openModal(vehicle: Vehicle) {
  selectedVehicle.value = vehicle
  carouselIndex.value = 0
  newStatus.value = vehicle.status
  editingStatus.value = false
  selectedTerm.value = null
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  selectedVehicle.value = null
  editingStatus.value = false
}

function openEditConfirmation() {
  if (!selectedVehicle.value) return
  router.push(`/vehicles/${selectedVehicle.value.id}`)
}

function openStatusChangeConfirmation() {
  if (!selectedVehicle.value) return
  confirmModalTitle.value = 'Confirm Status Change'
  confirmModalMessage.value = `Are you sure you want to change the status to "${newStatus.value}"?`
  confirmModalType.value = 'status'
  confirmModalOpen.value = true
}

function openDeleteConfirmation() {
  if (!selectedVehicle.value) return
  confirmModalTitle.value = 'Confirm Delete'
  confirmModalMessage.value = `Are you sure you want to delete "${selectedVehicle.value.title}"? This action cannot be undone.`
  confirmModalType.value = 'delete'
  confirmModalOpen.value = true
}

function closeConfirmModal() {
  confirmModalOpen.value = false
  confirmModalLoading.value = false
}

async function handleConfirm() {
  if (confirmModalType.value === 'status') {
    await executeStatusChange()
  } else if (confirmModalType.value === 'delete') {
    await executeDelete()
  }
}

function vehicleToForm(v: Vehicle): VehicleForm {
  return {
    title: v.title,
    status: v.status,
    make: v.make,
    price: v.price ?? 0,
    can_test_drive: v.can_test_drive !== false,
    details_and_financing: v.details_and_financing || '',
    images:
      v.images?.map((i) => ({
        image_path: i.image_path,
        image_url: i.image_url,
        position: i.position,
        is_primary: i.is_primary,
      })) ?? [],
  }
}

async function executeStatusChange() {
  if (!selectedVehicle.value) return
  
  confirmModalLoading.value = true
  try {
    const formData: VehicleForm = { ...vehicleToForm(selectedVehicle.value), status: newStatus.value }
    await api.admin.updateVehicle(selectedVehicle.value.id, formData)
    
    // Update local data
    const index = vehicles.value.findIndex(v => v.id === selectedVehicle.value!.id)
    const item = index !== -1 ? vehicles.value[index] : undefined
    if (item) {
      item.status = newStatus.value
      selectedVehicle.value.status = newStatus.value
    }
    
    editingStatus.value = false
    closeConfirmModal()
    
    // Show success message
    showSuccessMessage('Status updated successfully!')
  } catch (e) {
    console.error(e)
    showErrorMessage('Failed to update status')
    closeConfirmModal()
  } finally {
    confirmModalLoading.value = false
  }
}

async function executeDelete() {
  if (!selectedVehicle.value) return
  
  confirmModalLoading.value = true
  try {
    await api.admin.deleteVehicle(selectedVehicle.value.id)
    closeConfirmModal()
    closeModal()
    await load(pagination.value.current_page)
    
    // Show success message
    showSuccessMessage('Vehicle deleted successfully!')
  } catch (e) {
    console.error(e)
    showErrorMessage('Failed to delete vehicle')
    closeConfirmModal()
  } finally {
    confirmModalLoading.value = false
  }
}

// Toast notification state
const toastVisible = ref(false)
const toastMessage = ref('')
const toastType = ref<'success' | 'error'>('success')

function showSuccessMessage(message: string) {
  toastMessage.value = message
  toastType.value = 'success'
  toastVisible.value = true
  setTimeout(() => {
    toastVisible.value = false
  }, 3000)
}

function showErrorMessage(message: string) {
  toastMessage.value = message
  toastType.value = 'error'
  toastVisible.value = true
  setTimeout(() => {
    toastVisible.value = false
  }, 3000)
}

watch([statusFilter, makeFilter, testDriveFilter], () => {
  load(1)
})

onMounted(() => {
  const page = Math.max(1, parseInt(route.query.page as string, 10) || 1)
  load(page)
})
</script>

<template>
  <div class="admin-list">
    <div class="admin-header">
      <h1>Vehicles</h1>
      <button type="button" class="btn btn-primary" @click="router.push('/vehicles/new')">Add vehicle</button>
    </div>
    <div class="filters">
      <div class="filter-row">
        <span class="filter-label">Status</span>
        <div class="chip-scroll">
          <button
            v-for="opt in FILTER_STATUS_OPTIONS"
            :key="opt.value"
            type="button"
            class="chip"
            :class="{ active: statusFilter === opt.value, 'chip-available': opt.value === 'available' }"
            :aria-pressed="statusFilter === opt.value"
            @click="statusFilter = opt.value"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <div class="filter-row">
        <span class="filter-label">Make</span>
        <button
          type="button"
          class="chip make-trigger"
          :class="{ active: Boolean(makeFilter) }"
          :aria-expanded="makePickerOpen"
          aria-haspopup="dialog"
          @click="openMakePicker"
        >
          <img
            v-if="makeFilter && makeLogoSrc(makeFilter)"
            :src="makeLogoSrc(makeFilter)"
            alt=""
            class="chip-logo"
          />
          <span>{{ selectedMakeLabel }}</span>
          <svg class="make-trigger-caret" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
      </div>

      <div class="filter-foot">
        <div class="filter-foot-left">
          <span class="result-count">
            {{ pagination.total }} {{ pagination.total === 1 ? 'vehicle' : 'vehicles' }}
          </span>
          <button v-if="hasActiveFilters" type="button" class="clear-btn" @click="clearFilters">
            Clear filters
          </button>
        </div>
        <div class="td-filter">
          <span class="td-filter-label">Can test drive:</span>
          <span class="td-side" :class="{ active: testDriveFilter === '0' }">No</span>
          <button
            type="button"
            class="td-switch"
            :class="{ on: testDriveFilter === '1' }"
            role="switch"
            :aria-checked="testDriveFilter === '1'"
            aria-label="Can test drive"
            @click="testDriveFilter = testDriveFilter === '1' ? '0' : '1'"
          >
            <span class="td-knob"></span>
          </button>
          <span class="td-side" :class="{ active: testDriveFilter === '1' }">Yes</span>
        </div>
      </div>
    </div>
    <div v-if="loading && vehicles.length === 0" class="loading">Loading…</div>
    <div v-else class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Status</th>
            <th>Test drive</th>
            <th>Price</th>
            <th>Views</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="v in vehicles" :key="v.id" @click="openModal(v)" class="clickable-row">
            <td>
              <div class="cell-img">
                <img v-if="primaryImage(v)" :src="primaryImage(v)" :alt="v.title" />
                <span v-else>—</span>
              </div>
            </td>
            <td>{{ v.title }} </td>
            <td><span class="badge" :class="v.status">{{ v.status }}</span></td>
            <td>
              <span class="badge" :class="v.can_test_drive === false ? 'coming' : 'td-yes'">
                {{ v.can_test_drive === false ? 'No' : 'Yes' }}
              </span>
            </td>
            <td>{{ formatPrice(v.price) }}</td>
            <td>{{ v.views_count ?? 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="pagination">
      <button type="button" class="btn" :disabled="pagination.current_page <= 1" @click="load(pagination.current_page - 1)">Previous</button>
      <span class="page-num">Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} total)</span>
      <button type="button" class="btn" :disabled="pagination.current_page >= pagination.last_page" @click="load(pagination.current_page + 1)">Next</button>
    </div>

    <!-- Vehicle Details Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="modalOpen && selectedVehicle" class="modal-overlay" @click.self="closeModal">
          <div class="modal-box" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedVehicle.title || selectedVehicle.make }}</h2>
              <button class="modal-close" @click="closeModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>

            <div class="modal-body">
              <div class="modal-carousel">
                <template v-if="sortedImages(selectedVehicle).length">
                  <button
                    v-if="sortedImages(selectedVehicle).length > 1"
                    type="button"
                    class="carousel-btn carousel-prev"
                    aria-label="Previous image"
                    @click="carouselPrev"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                  </button>
                  <div class="carousel-main">
                    <img
                      :src="imageSrc(sortedImages(selectedVehicle)[carouselIndex]?.image_path ?? '', sortedImages(selectedVehicle)[carouselIndex]?.image_url)"
                      :alt="`${selectedVehicle.title} - image ${carouselIndex + 1}`"
                    />
                  </div>
                  <button
                    v-if="sortedImages(selectedVehicle).length > 1"
                    type="button"
                    class="carousel-btn carousel-next"
                    aria-label="Next image"
                    @click="carouselNext"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                  </button>
                  <span v-if="sortedImages(selectedVehicle).length > 1" class="carousel-counter">{{ carouselIndex + 1 }} / {{ sortedImages(selectedVehicle).length }}</span>
                </template>
                <div v-else class="no-image">No images</div>
              </div>

              <div class="modal-details">
                <div class="detail-row">
                  <span class="detail-label">Title:</span>
                  <span class="detail-value">{{ selectedVehicle.title }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Status:</span>
                  <div class="status-edit">
                    <span v-if="!editingStatus" class="badge" :class="selectedVehicle.status">{{ selectedVehicle.status }}</span>
                    <select v-else v-model="newStatus" class="status-select">
                      <option v-for="opt in STATUS_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <button v-if="!editingStatus" type="button" class="btn btn-xs" @click="editingStatus = true">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                    </button>
                    <template v-else>
                      <button type="button" class="btn btn-xs btn-primary" @click="openStatusChangeConfirmation" :disabled="actionLoading">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </button>
                      <button type="button" class="btn btn-xs" @click="editingStatus = false; newStatus = selectedVehicle.status">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <line x1="18" y1="6" x2="6" y2="18"></line>
                          <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                      </button>
                    </template>
                  </div>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Price:</span>
                  <span class="detail-value price">{{ formatPrice(selectedVehicle.price) }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Can test drive:</span>
                  <span class="detail-value">{{ selectedVehicle.can_test_drive === false ? 'No' : 'Yes' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Views:</span>
                  <span class="detail-value">{{ selectedVehicle.views_count ?? 0 }}</span>
                </div>
                <div v-if="selectedVehicle.details_and_financing" class="detail-block">
                  <span class="detail-label">Details & Financing</span>
                  <div class="detail-text">{{ selectedVehicle.details_and_financing }}</div>
                </div>
              </div>

              <div class="modal-actions">
                <button type="button" class="btn btn-action btn-edit" @click="openEditConfirmation" :disabled="actionLoading">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                  <span>Edit Vehicle</span>
                </button>
                <button type="button" class="btn btn-action btn-delete" @click="openDeleteConfirmation" :disabled="actionLoading">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                  </svg>
                  <span>Delete Vehicle</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Confirmation Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="confirmModalOpen" class="modal-overlay confirm-overlay" @click.self="closeConfirmModal">
          <div class="confirm-modal" @click.stop>
            <div class="confirm-icon" :class="confirmModalType">
              <svg v-if="confirmModalType === 'delete'" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
            </div>
            <h3 class="confirm-title">{{ confirmModalTitle }}</h3>
            <p class="confirm-message">{{ confirmModalMessage }}</p>
            <div class="confirm-actions">
              <button type="button" class="btn btn-cancel" @click="closeConfirmModal" :disabled="confirmModalLoading">
                Cancel
              </button>
              <button type="button" class="btn btn-confirm" :class="{ 'btn-danger': confirmModalType === 'delete' }" @click="handleConfirm" :disabled="confirmModalLoading">
                <span v-if="!confirmModalLoading">{{ confirmModalType === 'delete' ? 'Delete' : 'Confirm' }}</span>
                <span v-else>Processing...</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toastVisible" class="toast" :class="toastType">
          <svg v-if="toastType === 'success'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          <span>{{ toastMessage }}</span>
        </div>
      </Transition>
    </Teleport>

    <MakePicker
      :open="makePickerOpen"
      :selected="makeFilter"
      show-all
      @close="closeMakePicker"
      @pick="pickMake"
    />
  </div>
</template>

<style scoped>
.admin-list { max-width: 1200px; margin: 0 auto; padding: 2rem 0; width: 100%; }
@media (min-width: 769px) {
  .admin-list { max-width: none; }
}
.admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.admin-header h1 { font-size: 2rem; margin: 0; color: var(--color-heading); font-weight: 700; }
.filters {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  margin-bottom: 2rem;
  padding: 0.85rem 1rem;
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 12px;
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

.chip.active.chip-available {
  background: linear-gradient(135deg, #ea580c, #f97316);
  box-shadow: 0 3px 12px rgba(234, 88, 12, 0.35);
}

.make-trigger {
  padding-right: 0.75rem;
}

.make-trigger-caret {
  opacity: 0.7;
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

.td-side.active { color: var(--color-heading); }

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

.modal-close-btn {
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
.btn { padding: 0.625rem 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; transition: all 0.3s ease; font-weight: 500; }
.btn:hover { border-color: var(--red-primary); color: var(--red-primary); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: linear-gradient(135deg, var(--red-primary), var(--red-light)); color: #fff; border-color: transparent; font-weight: 600; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(216, 31, 38, 0.4); color: #000; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
.btn-danger { background: var(--red-dark); color: #fff; border-color: transparent; }
.btn-danger:hover { background: var(--red-deep); color: #fff; }
.loading { text-align: center; padding: 3rem; color: var(--color-text); font-size: 1.1rem; }
.table-wrap { overflow-x: auto; border: 1px solid var(--color-border); border-radius: 12px; background: var(--color-background-soft); }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 1rem 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border); }
.admin-table th { background: var(--color-background-mute); font-weight: 700; color: var(--color-heading); }
.admin-table tbody tr { transition: background-color 0.2s ease; }
.admin-table tbody tr.clickable-row { cursor: pointer; }
.admin-table tbody tr.clickable-row:hover { background: rgba(216, 31, 38, 0.1); }
.cell-img { width: 70px; height: 50px; }
.cell-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-border); }
.badge { padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
/* Statuses are ranked by emphasis rather than hue, to stay inside red/black/white */
.badge.available { background: linear-gradient(135deg, #ea580c, #f97316); color: #fff; border: 1px solid transparent; }
.badge.td-yes { background: linear-gradient(135deg, var(--red-primary), var(--red-light)); color: #fff; border: 1px solid transparent; }
.badge.reserved { background: var(--color-accent-soft); color: var(--color-accent-text); border: 1px solid var(--color-border-hover); }
.badge.coming { background: transparent; color: var(--color-text-muted); border: 1px dashed var(--color-border); }
.badge.sold { background: var(--color-background-mute); color: var(--color-text-muted); border: 1px solid var(--color-card-border); }
.badge.draft { background: var(--color-background-mute); color: var(--color-text-muted); border: 1px solid var(--color-card-border); }
.pagination { display: flex; align-items: center; justify-content: center; gap: 1.5rem; margin-top: 2rem; padding: 1.5rem 0; }
.page-num { font-size: 1rem; color: var(--color-text); font-weight: 500; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
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
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--color-border);
}

.modal-header h2 {
  font-size: 1.5rem;
  margin: 0;
  color: var(--color-heading);
  font-weight: 700;
}

.modal-close {
  background: transparent;
  border: none;
  color: var(--color-text);
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.modal-close:hover {
  background: rgba(216, 31, 38, 0.1);
  color: var(--red-primary);
}

.modal-body {
  padding: 2rem;
}

.modal-carousel {
  position: relative;
  width: 100%;
  aspect-ratio: 16/10;
  border-radius: 12px;
  overflow: hidden;
  background: var(--color-background-mute);
  border: 1px solid var(--color-border);
  margin-bottom: 2rem;
}

.carousel-main {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.carousel-main img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  background: var(--color-background-soft);
  color: var(--color-text);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  transition: all 0.2s;
}

.carousel-btn:hover {
  background: var(--red-primary);
  color: #fff;
  border-color: var(--red-primary);
}

.carousel-prev { left: 0.5rem; }
.carousel-next { right: 0.5rem; }

.carousel-counter {
  position: absolute;
  bottom: 0.5rem;
  left: 50%;
  transform: translateX(-50%);
  padding: 0.25rem 0.75rem;
  background: rgba(0,0,0,0.6);
  border-radius: 999px;
  font-size: 0.85rem;
  color: #fff;
}

.modal-carousel .no-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
  font-size: 1.1rem;
}

.detail-block {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0.75rem;
  background: var(--color-background-mute);
  border-radius: 8px;
}

.detail-text {
  white-space: pre-wrap;
  word-break: break-word;
  color: var(--color-text);
  font-size: 0.95rem;
  line-height: 1.6;
}

.modal-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: var(--color-background-mute);
  border-radius: 8px;
}

.detail-label {
  font-weight: 600;
  color: var(--color-heading);
  min-width: 120px;
}

.detail-value {
  color: var(--color-text);
  flex: 1;
}

.detail-value.price {
  color: var(--red-primary);
  font-weight: 700;
  font-size: 1.25rem;
}

.detail-value .neg {
  font-weight: normal;
  opacity: 0.8;
  font-size: 0.9rem;
  color: var(--color-text-muted);
}

.modal-financing {
  padding: 1.5rem 0;
  border-top: 1px solid var(--color-border);
}

.modal-financing h3 {
  font-size: 1.1rem;
  margin: 0 0 1rem;
  color: var(--red-primary);
  font-weight: 700;
}

.modal-financing p {
  margin: 0;
  font-size: 0.95rem;
}

.modal-installments {
  margin-top: 1rem;
}

.modal-installments h4 {
  font-size: 1rem;
  margin: 0 0 1rem;
  color: var(--color-text);
  font-weight: 600;
}

.term-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.term-btn {
  padding: 0.75rem 1.5rem;
  border: 2px solid var(--color-border);
  border-radius: 10px;
  background: var(--color-background);
  color: var(--color-text);
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.term-btn:hover {
  border-color: var(--red-primary);
  background: rgba(216, 31, 38, 0.05);
  transform: translateY(-2px);
}

.term-btn.active {
  border-color: var(--red-primary);
  background: rgba(216, 31, 38, 0.15);
  color: var(--red-primary);
  box-shadow: 0 4px 12px rgba(216, 31, 38, 0.2);
}

.monthly-amount {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, rgba(216, 31, 38, 0.1), rgba(240, 53, 61, 0.05));
  border-radius: 10px;
  border: 2px solid var(--red-primary);
}

.amount-label {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  margin-bottom: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.amount-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--red-primary);
}

.status-edit {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.status-select {
  padding: 0.375rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  background: var(--color-background);
  color: var(--color-text);
  font-size: 0.875rem;
}

.btn-xs {
  padding: 0.375rem 0.5rem;
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border);
}

.btn-action {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
}

.btn-action svg {
  flex-shrink: 0;
}

.btn-edit {
  background: linear-gradient(135deg, var(--red-primary), var(--red-light));
  color: #fff;
  border-color: transparent;
}

.btn-edit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(216, 31, 38, 0.4);
  color: #000;
}

.btn-delete {
  background: var(--red-dark);
  color: #fff;
  border-color: transparent;
}

.btn-delete:hover {
  background: var(--red-deep);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(163, 20, 26, 0.45);
}

.btn-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-box,
.modal-leave-active .modal-box {
  transition: transform 0.3s ease;
}

.modal-enter-from .modal-box,
.modal-leave-to .modal-box {
  transform: scale(0.95);
}

/* Responsive */
@media (max-width: 768px) {
  .admin-list {
    max-width: none;
    padding: 0;
  }

  .admin-header {
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .admin-header h1 {
    font-size: 1.25rem;
  }

  .filters {
    padding: 0.65rem 0.75rem;
    margin-bottom: 0.75rem;
    gap: 0.6rem;
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

  .table-wrap {
    margin: 0 -0.25rem;
    border-radius: 8px;
  }

  .admin-table th,
  .admin-table td {
    padding: 0.5rem 0.5rem;
    font-size: 0.85rem;
  }

  .cell-img {
    width: 50px;
    height: 36px;
  }

  .pagination {
    margin-top: 0.75rem;
    padding: 0.5rem 0;
    gap: 0.75rem;
  }

  .modal-box {
    max-width: 100%;
    margin: 0.25rem;
  }

  .modal-header {
    padding: 0.75rem 1rem;
  }

  .modal-header h2 {
    font-size: 1.1rem;
  }

  .modal-body {
    padding: 1rem;
  }

  .modal-carousel {
    aspect-ratio: 4/3;
  }

  .carousel-btn {
    width: 36px;
    height: 36px;
  }

  .carousel-prev { left: 0.25rem; }
  .carousel-next { right: 0.25rem; }

  .modal-actions {
    flex-direction: column;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .detail-label {
    min-width: auto;
  }
}

/* Confirmation Modal */
.confirm-overlay {
  z-index: 3000;
}

.confirm-modal {
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2rem;
  max-width: 450px;
  width: 90%;
  text-align: center;
}

.confirm-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-icon.delete {
  background: rgba(163, 20, 26, 0.12);
  color: var(--red-dark);
}

.confirm-icon.status {
  background: rgba(216, 31, 38, 0.1);
  color: var(--red-primary);
}

.confirm-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-heading);
  margin: 0 0 1rem;
}

.confirm-message {
  font-size: 1rem;
  color: var(--color-text);
  margin: 0 0 2rem;
  line-height: 1.6;
}

.confirm-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-cancel {
  flex: 1;
  padding: 0.875rem 1.5rem;
  background: var(--color-background-mute);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-cancel:hover {
  border-color: var(--red-primary);
  color: var(--red-primary);
}

.btn-confirm {
  flex: 1;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, var(--red-primary), var(--red-light));
  color: #fff;
  border-color: transparent;
  font-weight: 600;
}

.btn-confirm.btn-danger {
  background: var(--red-dark);
  color: #fff;
}

.btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(216, 31, 38, 0.4);
}

.btn-confirm.btn-danger:hover {
  background: var(--red-deep);
  box-shadow: 0 6px 20px rgba(163, 20, 26, 0.45);
}

.btn-confirm:disabled,
.btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Toast Notification */
.toast {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 4000;
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  min-width: 300px;
}

/* Success stays neutral with a red accent bar; errors take the loud solid red */
.toast.success {
  border-color: var(--color-card-border);
  border-left: 4px solid var(--red-primary);
  background: var(--color-background-soft);
  color: var(--color-heading);
}

.toast.error {
  border-color: transparent;
  background: var(--red-dark);
  color: #fff;
}

.toast svg {
  flex-shrink: 0;
}

.toast span {
  font-weight: 500;
  font-size: 0.95rem;
}

/* Toast transition */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100px);
}

@media (max-width: 768px) {
  .confirm-modal {
    padding: 1.5rem;
    max-width: 90%;
  }

  .confirm-icon {
    width: 60px;
    height: 60px;
  }

  .confirm-icon svg {
    width: 36px;
    height: 36px;
  }

  .confirm-title {
    font-size: 1.25rem;
  }

  .confirm-message {
    font-size: 0.95rem;
  }

  .confirm-actions {
    flex-direction: column;
  }

  .toast {
    top: 1rem;
    right: 1rem;
    left: 1rem;
    min-width: auto;
  }
}
</style>
