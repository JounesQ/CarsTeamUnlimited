<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api, imageUrl } from '@/api/client'
import type { VehicleForm } from '@/types/vehicle'
import { MAKES } from '@/data/vehicleOptions'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => route.params.id && route.params.id !== 'new')
const loading = ref(false)
const saveError = ref('')
const uploadingIndex = ref<number | null>(null)
const uploadingCount = ref(0)

// Drag state for reordering
const draggedIndex = ref<number | null>(null)

// Confirmation modal state
const confirmModalOpen = ref(false)
const confirmModalLoading = ref(false)

const form = ref<VehicleForm>({
  title: '',
  status: 'available',
  make: '',
  price: 0,
  details_and_financing: '',
  images: [],
})

function displayUrl(path: string) {
  return path ? imageUrl(path) : ''
}

function removeImage(index: number) {
  form.value.images.splice(index, 1)
  reorderPositions()
}

function reorderPositions() {
  form.value.images.forEach((img, i) => {
    img.position = i + 1
    img.is_primary = i === 0
  })
}

async function onFilesPicked(event: Event) {
  const input = event.target as HTMLInputElement
  const files = input.files
  if (!files?.length) return
  const imageFiles = Array.from(files).filter((f) => f.type.startsWith('image/'))
  if (imageFiles.length === 0) {
    saveError.value = 'Please select image files (JPEG, PNG, GIF, WebP).'
    return
  }
  const remaining = 10 - form.value.images.length
  const toAdd = Math.min(imageFiles.length, remaining)
  if (toAdd === 0) {
    saveError.value = 'Maximum 10 images allowed.'
    input.value = ''
    return
  }
  saveError.value = ''
  uploadingCount.value = toAdd
  const failed: string[] = []
  for (let i = 0; i < toAdd; i++) {
    try {
      const { path } = await api.admin.uploadImage(imageFiles[i])
      form.value.images.push({
        image_path: path,
        position: form.value.images.length + 1,
        is_primary: form.value.images.length === 0,
      })
      reorderPositions()
    } catch (e) {
      const name = imageFiles[i].name
      const msg = e instanceof Error ? e.message : 'Upload failed'
      failed.push(`${name}: ${msg}`)
    }
  }
  uploadingCount.value = 0
  input.value = ''
  if (failed.length > 0) {
    saveError.value = failed.length === toAdd
      ? `All uploads failed. ${failed[0]}`
      : `${failed.length} failed: ${failed.slice(0, 2).join('; ')}${failed.length > 2 ? '...' : ''}`
  }
}

function onDragStart(event: DragEvent, index: number) {
  draggedIndex.value = index
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', String(index))
  }
}

function onDragOver(event: DragEvent) {
  event.preventDefault()
  if (event.dataTransfer) event.dataTransfer.dropEffect = 'move'
}

function onDrop(event: DragEvent, dropIndex: number) {
  event.preventDefault()
  const from = draggedIndex.value
  if (from == null || from === dropIndex) {
    draggedIndex.value = null
    return
  }
  const imgs = [...form.value.images]
  const [removed] = imgs.splice(from, 1)
  imgs.splice(dropIndex, 0, removed)
  form.value.images = imgs
  reorderPositions()
  draggedIndex.value = null
}

function onDragEnd() {
  draggedIndex.value = null
}

async function loadVehicle() {
  const id = route.params.id as string
  if (!id || id === 'new') return
  try {
    const v = await api.admin.getVehicle(id)
    form.value = {
      title: v.title,
      status: v.status,
      make: v.make,
      price: v.price ?? 0,
      details_and_financing: v.details_and_financing || '',
      images: v.images?.length
        ? [...v.images]
            .sort((a, b) => a.position - b.position)
            .map((i) => ({ image_path: i.image_path, position: i.position, is_primary: i.is_primary }))
        : [],
    }
  } catch (e) {
    console.error(e)
    saveError.value = 'Failed to load vehicle'
  }
}

function openSaveConfirmation() {
  saveError.value = ''
  if (!form.value.title?.trim() || !form.value.make?.trim() || form.value.price < 0) {
    saveError.value = 'Please fill required fields (title, make, price).'
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
          <label>Make *</label>
          <select v-model="form.make" required>
            <option value="">Select make</option>
            <option v-for="m in MAKES" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>
        <div class="field">
          <label>Price *</label>
          <input v-model.number="form.price" type="number" required min="0" step="1" placeholder="0" />
        </div>
      </div>
      <div class="details-section">
        <h3>Details & Financing</h3>
        <p class="hint">Paste specs, features, and financing info here.</p>
        <textarea
          v-model="form.details_and_financing"
          rows="6"
          placeholder="Paste vehicle details, specs, and financing options here..."
        ></textarea>
      </div>
      <div class="images-section">
        <h3>Images (max 10)</h3>
        <p class="hint">Click "Upload photo" to select multiple images at once. Drag to reorder.</p>
        <label class="upload-btn upload-btn-primary" :class="{ 'uploading': uploadingCount > 0 }">
          <input
            type="file"
            accept="image/jpeg,image/png,image/gif,image/webp"
            multiple
            :disabled="uploadingCount > 0"
            @change="onFilesPicked"
          />
          {{ uploadingCount > 0 ? `Uploading ${uploadingCount}…` : 'Upload photo' }}
        </label>
        <div class="images-grid">
          <div
            v-for="(img, idx) in form.images"
            :key="img.image_path + idx"
            class="image-slot"
            :class="{ 'dragging': draggedIndex === idx }"
            draggable="true"
            @dragstart="onDragStart($event, idx)"
            @dragover="onDragOver"
            @drop="onDrop($event, idx)"
            @dragend="onDragEnd"
          >
            <div class="image-preview">
              <img :src="displayUrl(img.image_path)" :alt="'Preview ' + (idx + 1)" />
            </div>
            <div class="image-actions">
              <span class="drag-handle" title="Drag to reorder">⋮⋮</span>
              <button type="button" class="btn btn-sm" @click="removeImage(idx)">Remove</button>
            </div>
          </div>
        </div>
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
                : `Are you sure you want to add "${form.title}"?` 
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
.admin-form { max-width: 800px; margin: 0 auto; padding: 2rem 0; width: 70%; }
@media (min-width: 769px) {
  .admin-form { max-width: none; }
}
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
.details-section { margin-bottom: 2rem; padding: 1.5rem; background: var(--color-background-soft); border: 1px solid var(--color-border); border-radius: 12px; }
.details-section h3 { font-size: 1.15rem; margin: 0 0 0.5rem; color: var(--color-heading); font-weight: 700; }
.hint { font-size: 0.9rem; color: var(--color-text-muted); margin: 0 0 1rem; }
.details-section textarea { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-background); color: var(--color-text); font-family: inherit; font-size: 0.95rem; resize: vertical; min-height: 120px; }
.details-section textarea:focus { outline: none; border-color: var(--gold-primary); }
.images-section { margin-bottom: 2rem; padding: 1.5rem; background: var(--color-background-soft); border: 1px solid var(--color-border); border-radius: 12px; }
.images-section h3 { font-size: 1.15rem; margin: 0 0 0.5rem; color: var(--color-heading); font-weight: 700; }
.images-section .hint { margin: 0 0 1rem; }
.upload-btn-primary { display: inline-block; margin-bottom: 1rem; padding: 0.75rem 1.5rem; font-size: 1rem; }
.images-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem; }
.image-slot { display: flex; flex-direction: column; align-items: stretch; padding: 0.5rem; border: 2px solid var(--color-border); border-radius: 10px; background: var(--color-background-mute); transition: all 0.2s ease; cursor: grab; }
.image-slot:hover { border-color: var(--gold-primary); }
.image-slot.dragging { opacity: 0.5; cursor: grabbing; }
.image-slot:active { cursor: grabbing; }
.image-preview { width: 100%; aspect-ratio: 4/3; border-radius: 8px; overflow: hidden; background: var(--color-background); border: 1px solid var(--color-border); }
.image-preview img { width: 100%; height: 100%; object-fit: cover; pointer-events: none; }
.image-actions { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-top: 0.5rem; }
.drag-handle { font-size: 1rem; color: var(--color-text-muted); cursor: grab; user-select: none; }
.drag-handle:active { cursor: grabbing; }
.upload-btn { cursor: pointer; padding: 0.625rem 1rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background); color: var(--color-text); font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease; }
.upload-btn:hover { border-color: var(--gold-primary); color: var(--gold-primary); }
.upload-btn input { display: none; }
.upload-btn.uploading { opacity: 0.7; pointer-events: none; }
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
  .admin-form {
    max-width: none;
    padding: 0;
  }

  .form-header {
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .form-header h1 {
    font-size: 1.25rem;
  }

  .form-header .btn {
    width: 100%;
  }

  .form-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  .details-section {
    padding: 0.75rem;
    margin-bottom: 1rem;
  }

  .images-section {
    padding: 0.75rem;
    margin-bottom: 1rem;
  }

  .images-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .image-slot {
    padding: 0.5rem;
  }

  .form-actions {
    margin-top: 1rem;
    padding-top: 1rem;
  }

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
