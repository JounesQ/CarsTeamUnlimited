<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { VehicleForm } from '@/types/vehicle'
import { MAKES, VEHICLE_TYPES, CATEGORIES, FUEL_TYPES, TRANSMISSIONS } from '@/data/vehicleOptions'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => route.params.id && route.params.id !== 'new')
const loading = ref(false)
const saveError = ref('')
const uploadingIndex = ref<number | null>(null)

// Confirmation modal state
const confirmModalOpen = ref(false)
const confirmModalLoading = ref(false)

const form = ref<VehicleForm>({
  title: '',
  status: 'available',
  year: new Date().getFullYear(),
  make: '',
  model: '',
  vehicle_type: 'car',
  category: '',
  transmission: 'automatic',
  fuel_type: '',
  color: '',
  door_count: '',
  seat_capacity: '',
  mileage: '',
  grade: '',
  price: 0,
  is_negotiable: true,
  down_payment: '',
  dp_all_in: true,
  financing_options: {},
  images: [{ image_path: '', position: 1, is_primary: true }],
})

const INSTALLMENT_TERMS = [
  { key: '1_year', label: '1 year' },
  { key: '2_years', label: '2 years' },
  { key: '3_years', label: '3 years' },
  { key: '4_years', label: '4 years' },
  { key: '5_years', label: '5 years' },
] as const

function displayUrl(path: string) {
  return path ? imageUrl(path) : ''
}

function addImage() {
  if (form.value.images.length < 10) {
    form.value.images.push({ image_path: '', position: form.value.images.length + 1, is_primary: false })
  }
}

function removeImage(index: number) {
  form.value.images.splice(index, 1)
  form.value.images.forEach((img, i) => {
    img.position = i + 1
    img.is_primary = i === 0
  })
}

async function onFilePicked(index: number, event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  if (!file.type.startsWith('image/')) {
    saveError.value = 'Please select an image file (JPEG, PNG, GIF, WebP).'
    return
  }
  uploadingIndex.value = index
  saveError.value = ''
  try {
    const { path } = await api.admin.uploadImage(file)
    const img = form.value.images[index]
    if (img) img.image_path = path
  } catch (e) {
    saveError.value = e instanceof Error ? e.message : 'Upload failed'
  } finally {
    uploadingIndex.value = null
    input.value = ''
  }
}

async function loadVehicle() {
  const id = route.params.id as string
  if (!id || id === 'new') return
  try {
    const v = await api.admin.getVehicle(id)
    form.value = {
      title: v.title,
      slug: v.slug,
      status: v.status,
      year: v.year,
      make: v.make,
      model: v.model,
      vehicle_type: v.vehicle_type,
      category: v.category || '',
      transmission: v.transmission,
      fuel_type: v.fuel_type || '',
      color: v.color || '',
      door_count: v.door_count ?? '',
      seat_capacity: v.seat_capacity ?? '',
      mileage: v.mileage ?? '',
      grade: v.grade || '',
      price: v.price,
      is_negotiable: v.is_negotiable,
      down_payment: v.down_payment ?? '',
      dp_all_in: v.dp_all_in,
      financing_options: v.financing_options || {},
      images: v.images?.length
        ? v.images.map((i) => ({ image_path: i.image_path, position: i.position, is_primary: i.is_primary }))
        : [{ image_path: '', position: 1, is_primary: true }],
    }
  } catch (e) {
    console.error(e)
    saveError.value = 'Failed to load vehicle'
  }
}

function openSaveConfirmation() {
  saveError.value = ''
  if (!form.value.title?.trim() || !form.value.make?.trim() || !form.value.model?.trim() || form.value.price < 0) {
    saveError.value = 'Please fill required fields (title, make, model, price).'
    return
  }
  confirmModalOpen.value = true
}

function closeConfirmModal() {
  confirmModalOpen.value = false
  confirmModalLoading.value = false
}

async function confirmSave() {
  confirmModalLoading.value = true
  try {
    const images = form.value.images.filter((i) => i.image_path.trim())
    const payload = { ...form.value, images }
    if (isEdit.value) {
      await api.admin.updateVehicle(route.params.id as string, payload)
    } else {
      await api.admin.createVehicle(payload)
    }
    closeConfirmModal()
    router.push('/vehicles')
  } catch (e) {
    saveError.value = e instanceof Error ? e.message : 'Save failed'
    closeConfirmModal()
  } finally {
    confirmModalLoading.value = false
  }
}

onMounted(() => loadVehicle())
</script>

<template>
  <div class="admin-form">
    <div class="form-header">
      <h1>{{ isEdit ? 'Edit vehicle' : 'Add vehicle' }}</h1>
      <button type="button" class="btn" @click="router.push('/vehicles')">← Back to list</button>
    </div>
    <p v-if="saveError" class="error">{{ saveError }}</p>
    <form @submit.prevent="openSaveConfirmation" class="form">
      <div class="form-grid">
        <div class="field">
          <label>Title *</label>
          <input v-model="form.title" type="text" required maxlength="200" />
        </div>
        <div class="field">
          <label>Status</label>
          <select v-model="form.status">
            <option value="available">Available</option>
            <option value="sold">Sold</option>
            <option value="reserved">Reserved</option>
            <option value="coming">Coming</option>
          </select>
        </div>
        <div class="field">
          <label>Year *</label>
          <input v-model.number="form.year" type="number" required min="1900" max="2100" />
        </div>
        <div class="field">
          <label>Make *</label>
          <select v-model="form.make" required>
            <option value="">Select make</option>
            <option v-for="m in MAKES" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>
        <div class="field">
          <label>Model *</label>
          <input v-model="form.model" type="text" required maxlength="50" />
        </div>
        <div class="field">
          <label>Vehicle type</label>
          <select v-model="form.vehicle_type">
            <option v-for="t in VEHICLE_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>
        <div class="field">
          <label>Category</label>
          <select v-model="form.category">
            <option value="">Select category</option>
            <option v-for="c in CATEGORIES" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
        <div class="field">
          <label>Transmission</label>
          <select v-model="form.transmission">
            <option v-for="t in TRANSMISSIONS" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>
        <div class="field">
          <label>Fuel type</label>
          <select v-model="form.fuel_type">
            <option value="">Select fuel type</option>
            <option v-for="f in FUEL_TYPES" :key="f" :value="f">{{ f }}</option>
          </select>
        </div>
        <div class="field">
          <label>Color</label>
          <input v-model="form.color" type="text" maxlength="50" />
        </div>
        <div class="field">
          <label>Doors</label>
          <input v-model.number="form.door_count" type="number" min="0" />
        </div>
        <div class="field">
          <label>Seats</label>
          <input v-model.number="form.seat_capacity" type="number" min="0" />
        </div>
        <div class="field">
          <label>Mileage</label>
          <input v-model.number="form.mileage" type="number" min="0" />
        </div>
        <div class="field">
          <label>Grade</label>
          <input v-model="form.grade" type="text" maxlength="50" />
        </div>
        <div class="field">
          <label>Price *</label>
          <input v-model.number="form.price" type="number" required min="0" step="0.01" />
        </div>
        <div class="field checkbox">
          <label><input v-model="form.is_negotiable" type="checkbox" /> Negotiable</label>
        </div>
        <div class="field">
          <label>Down payment</label>
          <input v-model.number="form.down_payment" type="number" min="0" step="0.01" />
        </div>
        <div class="field checkbox">
          <label><input v-model="form.dp_all_in" type="checkbox" /> DP all-in</label>
        </div>
      </div>
      <div class="financing-section">
        <h3>Monthly installments (optional)</h3>
        <p class="hint">Enter monthly amount per term. Leave empty to omit.</p>
        <div class="installment-grid">
          <div v-for="term in INSTALLMENT_TERMS" :key="term.key" class="field">
            <label>{{ term.label }}</label>
            <input v-model.number="form.financing_options[term.key]" type="number" min="0" step="0.01" placeholder="Monthly" />
          </div>
        </div>
      </div>
      <div class="images-section">
        <h3>Images (upload photo, max 10)</h3>
        <div v-for="(img, idx) in form.images" :key="idx" class="image-slot">
          <div class="image-preview">
            <img v-if="img.image_path" :src="displayUrl(img.image_path)" :alt="'Preview ' + (idx + 1)" />
            <span v-else class="preview-placeholder">No photo</span>
          </div>
          <div class="image-actions">
            <label class="upload-btn">
              <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" @change="onFilePicked(idx, $event)" />
              {{ uploadingIndex === idx ? 'Uploading…' : 'Upload photo' }}
            </label>
            <button v-if="form.images.length > 1" type="button" class="btn btn-sm" @click="removeImage(idx)">Remove</button>
          </div>
        </div>
        <button v-if="form.images.length < 10" type="button" class="btn btn-sm" @click="addImage">Add image slot</button>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-cancel-form" @click="router.push('/vehicles')" :disabled="loading">
          Cancel
        </button>
        <button type="submit" class="btn btn-primary" :disabled="loading">{{ loading ? 'Saving…' : 'Save' }}</button>
      </div>
    </form>

    <!-- Confirmation Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="confirmModalOpen" class="modal-overlay confirm-overlay" @click.self="closeConfirmModal">
          <div class="confirm-modal" @click.stop>
            <div class="confirm-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
            </div>
            <h3 class="confirm-title">{{ isEdit ? 'Confirm Update' : 'Confirm Save' }}</h3>
            <p class="confirm-message">
              {{ isEdit 
                ? `Are you sure you want to update "${form.title}"?` 
                : `Are you sure you want to create "${form.title}"?` 
              }}
            </p>
            <div class="confirm-actions">
              <button type="button" class="btn btn-cancel" @click="closeConfirmModal" :disabled="confirmModalLoading">
                Cancel
              </button>
              <button type="button" class="btn btn-confirm" @click="confirmSave" :disabled="confirmModalLoading">
                <span v-if="!confirmModalLoading">{{ isEdit ? 'Update' : 'Save' }}</span>
                <span v-else>Saving...</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.admin-form { max-width: 800px; margin: 0 auto; padding: 2rem 0; }
.form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.form-header h1 { font-size: 2rem; margin: 0; color: var(--color-heading); font-weight: 700; }
.btn { padding: 0.625rem 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; transition: all 0.3s ease; font-weight: 500; }
.btn:hover { border-color: var(--gold-primary); color: var(--gold-primary); }
.btn-primary { background: linear-gradient(135deg, var(--gold-primary), var(--gold-light)); color: #000; border-color: transparent; font-weight: 600; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4); color: #000; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
.error { color: #ff6b6b; margin-bottom: 1.5rem; padding: 1rem; background: rgba(255, 107, 107, 0.1); border: 1px solid rgba(255, 107, 107, 0.3); border-radius: 8px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2rem; }
.field { display: flex; flex-direction: column; gap: 0.5rem; }
.field label { font-size: 0.95rem; font-weight: 600; color: var(--color-heading); }
.field input, .field select { padding: 0.625rem 0.875rem; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-background); color: var(--color-text); transition: border-color 0.3s ease; }
.field input:focus, .field select:focus { outline: none; border-color: var(--gold-primary); }
.field.checkbox { flex-direction: row; align-items: center; }
.field.checkbox label { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; }
.financing-section { margin-bottom: 2rem; padding: 1.5rem; background: var(--color-background-soft); border: 1px solid var(--color-border); border-radius: 12px; }
.financing-section h3 { font-size: 1.15rem; margin: 0 0 0.5rem; color: var(--color-heading); font-weight: 700; }
.hint { font-size: 0.9rem; color: var(--color-text-muted); margin: 0 0 1rem; }
.installment-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.images-section { margin-bottom: 2rem; padding: 1.5rem; background: var(--color-background-soft); border: 1px solid var(--color-border); border-radius: 12px; }
.images-section h3 { font-size: 1.15rem; margin: 0 0 1rem; color: var(--color-heading); font-weight: 700; }
.image-slot { display: flex; align-items: flex-start; gap: 1.25rem; margin-bottom: 1.25rem; padding: 1rem; border: 1px solid var(--color-border); border-radius: 10px; background: var(--color-background-mute); transition: border-color 0.3s ease; }
.image-slot:hover { border-color: var(--gold-primary); }
.image-preview { width: 160px; height: 110px; flex-shrink: 0; border-radius: 8px; overflow: hidden; background: var(--color-background); border: 1px solid var(--color-border); }
.image-preview img { width: 100%; height: 100%; object-fit: cover; }
.preview-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; color: var(--color-text-muted); }
.image-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; flex: 1; }
.upload-btn { cursor: pointer; padding: 0.625rem 1rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background); color: var(--color-text); font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease; }
.upload-btn:hover { border-color: var(--gold-primary); color: var(--gold-primary); }
.upload-btn input { display: none; }
.form-actions { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--color-border); display: flex; gap: 1rem; justify-content: flex-end; }
.btn-cancel-form { background: var(--color-background-mute); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-cancel-form:hover { border-color: #dc3545; color: #dc3545; }

/* Confirmation Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
  padding: 1rem;
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

.btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
}

.btn-confirm:disabled,
.btn-cancel:disabled {
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

.modal-enter-active .confirm-modal,
.modal-leave-active .confirm-modal {
  transition: transform 0.3s ease;
}

.modal-enter-from .confirm-modal,
.modal-leave-to .confirm-modal {
  transform: scale(0.95);
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

  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
