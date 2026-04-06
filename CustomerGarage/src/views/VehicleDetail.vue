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
const carouselIndex = ref(0)

const orderedImages = computed(() => {
  const imgs = vehicle.value?.images || []
  return [...imgs].sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
})

const currentImageUrl = computed(() => {
  const img = orderedImages.value[carouselIndex.value]
  const path = img?.image_path || ''
  return path ? imageUrl(path, img?.image_url) : ''
})

function prevImage() {
  if (orderedImages.value.length <= 1) return
  carouselIndex.value = (carouselIndex.value - 1 + orderedImages.value.length) % orderedImages.value.length
}

function nextImage() {
  if (orderedImages.value.length <= 1) return
  carouselIndex.value = (carouselIndex.value + 1) % orderedImages.value.length
}

function formatPrice(n: number) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'php', maximumFractionDigits: 0 }).format(n)
}

onMounted(async () => {
  const id = route.params.id as string
  if (!id) { error.value = 'Invalid vehicle'; loading.value = false; return }
  try {
    vehicle.value = await api.getVehicle(id)
    carouselIndex.value = 0
  } catch (e) {
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
        <h1>{{ vehicle.title || vehicle.make }}</h1>
        <p class="price">{{ formatPrice(vehicle.price) }}</p>
      </div>
      <div class="gallery">
        <div class="main-img">
          <img v-if="currentImageUrl" :src="currentImageUrl" :alt="vehicle.title" />
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
        <span v-if="orderedImages.length > 1" class="carousel-counter">{{ carouselIndex + 1 }} / {{ orderedImages.length }}</span>
      </div>
      <div v-if="vehicle.details_and_financing" class="specs">
        <h1>Details & Financing</h1>
        <div class="detail-text">{{ vehicle.details_and_financing }}</div>
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
.gallery { margin-bottom: 2rem; border-radius: 10px; overflow: hidden; border: 1px solid var(--color-border); position: relative; }
.main-img { aspect-ratio: 16/10; background: var(--color-background-mute); position: relative; }
.main-img img, .main-img .no-img { width: 100%; height: 100%; object-fit: contain; }
.no-img { display: flex; align-items: center; justify-content: center; color: var(--color-text); }
.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  background: rgba(0,0,0,0.5);
  color: #fff;
  cursor: pointer;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}
.carousel-btn:hover { background: rgba(0,0,0,0.7); }
.carousel-btn.prev { left: 0.5rem; }
.carousel-btn.next { right: 0.5rem; }
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
  z-index: 2;
}
.specs { margin-top: 1.5rem; }
.specs h1 { font-size: 1.1rem; margin: 0 0 0.75rem; color: var(--color-heading); }
.detail-text { white-space: pre-wrap; word-break: break-word; color: var(--color-text); font-size: 0.95rem; line-height: 1.6; }
</style>
