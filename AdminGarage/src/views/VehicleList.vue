<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { Vehicle, VehicleForm, Paginated } from '@/types/vehicle'
import { MAKES } from '@/data/vehicleOptions'

const route = useRoute()
const router = useRouter()
const vehicles = ref<Vehicle[]>([])
const loading = ref(true)
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
const statusFilter = ref((route.query.status as string) || '')
const makeFilter = ref((route.query.make as string) || '')

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

function primaryImage(v: Vehicle) {
  const img = v.images?.find((i) => i.is_primary) || v.images?.[0]
  const path = img?.image_path || ''
  return path ? imageUrl(path) : ''
}

function sortedImages(v: Vehicle) {
  const imgs = v.images ?? []
  return [...imgs].sort((a, b) => a.position - b.position)
}

function imageSrc(path: string) {
  return path ? imageUrl(path) : ''
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
  router.replace({ path: route.path, query: q })
}

async function load(page = 1) {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page, per_page: 10 }
    if (statusFilter.value) params.status = statusFilter.value
    if (makeFilter.value) params.make = makeFilter.value
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
    details_and_financing: v.details_and_financing || '',
    images: v.images?.map((i) => ({ image_path: i.image_path, position: i.position, is_primary: i.is_primary })) ?? [],
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
      <select v-model="statusFilter" class="filter-inp">
        <option value="">All statuses</option>
        <option value="available">Available</option>
        <option value="sold">Sold</option>
        <option value="reserved">Reserved</option>
        <option value="coming">Coming</option>
      </select>
      <select v-model="makeFilter" class="filter-inp">
        <option value="">All makes</option>
        <option v-for="make in MAKES" :key="make" :value="make">{{ make }}</option>
      </select>
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
            <td>{{ formatPrice(v.price) }}</td>
            <td>{{ v.views_count }}</td>
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
                      :src="imageSrc(sortedImages(selectedVehicle)[carouselIndex]?.image_path ?? '')"
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
  </div>
</template>

<style scoped>
.admin-list { max-width: 1200px; margin: 0 auto; padding: 2rem 0; width: 100%; }
@media (min-width: 769px) {
  .admin-list { max-width: none; }
}
.admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.admin-header h1 { font-size: 2rem; margin: 0; color: var(--color-heading); font-weight: 700; }
.filters { display: flex; gap: 0.75rem; margin-bottom: 2rem; padding: 1.5rem; background: var(--color-background-soft); border: 1px solid var(--color-border); border-radius: 12px; flex-wrap: wrap; }
.filter-inp { padding: 0.625rem 0.875rem; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-background); color: var(--color-text); width: 140px; transition: border-color 0.3s ease; }
.filter-inp:focus { outline: none; border-color: var(--gold-primary); }
.btn { padding: 0.625rem 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; transition: all 0.3s ease; font-weight: 500; }
.btn:hover { border-color: var(--gold-primary); color: var(--gold-primary); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: linear-gradient(135deg, var(--gold-primary), var(--gold-light)); color: #000; border-color: transparent; font-weight: 600; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4); color: #000; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
.btn-danger { background: #dc3545; color: white; border-color: transparent; }
.btn-danger:hover { background: #c82333; color: white; }
.loading { text-align: center; padding: 3rem; color: var(--color-text); font-size: 1.1rem; }
.table-wrap { overflow-x: auto; border: 1px solid var(--color-border); border-radius: 12px; background: var(--color-background-soft); }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 1rem 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border); }
.admin-table th { background: var(--color-background-mute); font-weight: 700; color: var(--color-heading); }
.admin-table tbody tr { transition: background-color 0.2s ease; }
.admin-table tbody tr.clickable-row { cursor: pointer; }
.admin-table tbody tr.clickable-row:hover { background: rgba(212, 175, 55, 0.1); }
.cell-img { width: 70px; height: 50px; }
.cell-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-border); }
.badge { padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.badge.available { background: rgba(72, 187, 120, 0.2); color: #48bb78; border: 1px solid rgba(72, 187, 120, 0.4); }
.badge.sold { background: rgba(245, 101, 101, 0.2); color: #f56565; border: 1px solid rgba(245, 101, 101, 0.4); }
.badge.draft { background: rgba(212, 175, 55, 0.2); color: var(--gold-primary); border: 1px solid rgba(212, 175, 55, 0.4); }
.badge.reserved { background: rgba(66, 153, 225, 0.2); color: #4299e1; border: 1px solid rgba(66, 153, 225, 0.4); }
.badge.coming { background: rgba(159, 122, 234, 0.2); color: #9f7aea; border: 1px solid rgba(159, 122, 234, 0.4); }
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
  box-shadow: 0 20px 60px rgba(212, 175, 55, 0.2);
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
  background: rgba(212, 175, 55, 0.1);
  color: var(--gold-primary);
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
  background: var(--gold-primary);
  color: #000;
  border-color: var(--gold-primary);
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
  color: var(--gold-primary);
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
  color: var(--gold-primary);
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
  border-color: var(--gold-primary);
  background: rgba(212, 175, 55, 0.05);
  transform: translateY(-2px);
}

.term-btn.active {
  border-color: var(--gold-primary);
  background: rgba(212, 175, 55, 0.15);
  color: var(--gold-primary);
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
}

.monthly-amount {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(244, 208, 63, 0.05));
  border-radius: 10px;
  border: 2px solid var(--gold-primary);
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
  color: var(--gold-primary);
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
  background: linear-gradient(135deg, var(--gold-primary), var(--gold-light));
  color: #000;
  border-color: transparent;
}

.btn-edit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
  color: #000;
}

.btn-delete {
  background: #dc3545;
  color: white;
  border-color: transparent;
}

.btn-delete:hover {
  background: #c82333;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
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
    padding: 0.5rem 0;
    margin-bottom: 0.5rem;
    gap: 0.5rem;
    flex-direction: column;
  }

  .filter-inp {
    width: 100%;
    min-width: unset;
  }

  .filters .btn {
    width: 100%;
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
  box-shadow: 0 20px 60px rgba(212, 175, 55, 0.3);
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
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.confirm-icon.status {
  background: rgba(212, 175, 55, 0.1);
  color: var(--gold-primary);
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
  border-color: var(--gold-primary);
  color: var(--gold-primary);
}

.btn-confirm {
  flex: 1;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, var(--gold-primary), var(--gold-light));
  color: #000;
  border-color: transparent;
  font-weight: 600;
}

.btn-confirm.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
}

.btn-confirm.btn-danger:hover {
  background: #c82333;
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
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

.toast.success {
  border-color: #48bb78;
  background: rgba(72, 187, 120, 0.1);
  color: #48bb78;
}

.toast.error {
  border-color: #dc3545;
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
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
