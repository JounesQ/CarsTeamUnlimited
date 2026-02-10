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
  if (form.value.images.length < 4) {
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

async function save() {
  saveError.value = ''
  if (!form.value.title?.trim() || !form.value.make?.trim() || !form.value.model?.trim() || form.value.price < 0) {
    saveError.value = 'Please fill required fields (title, make, model, price).'
    return
  }
  loading.value = true
  try {
    const images = form.value.images.filter((i) => i.image_path.trim())
    const payload = { ...form.value, images }
    if (isEdit.value) {
      await api.admin.updateVehicle(route.params.id as string, payload)
    } else {
      await api.admin.createVehicle(payload)
    }
    router.push('/vehicles')
  } catch (e) {
    saveError.value = e instanceof Error ? e.message : 'Save failed'
  } finally {
    loading.value = false
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
    <form @submit.prevent="save" class="form">
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
        <h3>Images (upload photo, max 4)</h3>
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
        <button v-if="form.images.length < 4" type="button" class="btn btn-sm" @click="addImage">Add image slot</button>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading">{{ loading ? 'Saving…' : 'Save' }}</button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.admin-form { max-width: 700px; margin: 0 auto; padding: 1rem 0; }
.form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.form-header h1 { font-size: 1.5rem; margin: 0; color: var(--color-heading); }
.btn { padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-background-mute); color: var(--color-text); cursor: pointer; }
.btn-primary { background: hsla(160, 100%, 37%, 1); color: white; border-color: transparent; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
.error { color: #c00; margin-bottom: 1rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
.field { display: flex; flex-direction: column; gap: 0.25rem; }
.field label { font-size: 0.9rem; font-weight: 500; }
.field input, .field select { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-background); color: var(--color-text); }
.field.checkbox { flex-direction: row; align-items: center; }
.field.checkbox label { display: flex; align-items: center; gap: 0.5rem; }
.financing-section { margin-bottom: 1.5rem; }
.financing-section h3 { font-size: 1rem; margin: 0 0 0.25rem; }
.hint { font-size: 0.85rem; opacity: 0.9; margin: 0 0 0.5rem; }
.installment-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.75rem; }
.images-section { margin-bottom: 1.5rem; }
.images-section h3 { font-size: 1rem; margin: 0 0 0.75rem; }
.image-slot { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-background-mute); }
.image-preview { width: 140px; height: 95px; flex-shrink: 0; border-radius: 6px; overflow: hidden; background: var(--color-background); border: 1px solid var(--color-border); }
.image-preview img { width: 100%; height: 100%; object-fit: cover; }
.preview-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; color: var(--color-text); opacity: 0.7; }
.image-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; flex: 1; }
.upload-btn { cursor: pointer; padding: 0.4rem 0.75rem; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-background); color: var(--color-text); font-size: 0.9rem; }
.upload-btn input { display: none; }
.form-actions { margin-top: 1rem; }
</style>
