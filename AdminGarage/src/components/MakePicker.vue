<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { MAKES } from '@/data/vehicleOptions'
import { makeLogoSrc } from '@/data/makeLogos'

const props = withDefaults(
  defineProps<{
    open: boolean
    selected: string
    showAll?: boolean
  }>(),
  { showAll: false },
)

const emit = defineEmits<{
  close: []
  pick: [value: string]
}>()

function onKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape' && props.open) emit('close')
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="modal-overlay make-picker-overlay"
        role="dialog"
        aria-modal="true"
        aria-label="Choose a make"
        @click.self="emit('close')"
      >
        <div class="make-picker" @click.stop>
          <div class="make-picker-head">
            <h2>Choose a make</h2>
            <button type="button" class="modal-close-btn" aria-label="Close" @click="emit('close')">×</button>
          </div>
          <div class="make-picker-grid">
            <button
              v-if="showAll"
              type="button"
              class="make-pick"
              :class="{ active: selected === '' }"
              @click="emit('pick', '')"
            >
              <span class="make-pick-logo make-pick-logo-empty">All</span>
              <span>All makes</span>
            </button>
            <button
              v-for="m in MAKES"
              :key="m"
              type="button"
              class="make-pick"
              :class="{ active: selected === m }"
              @click="emit('pick', m)"
            >
              <img
                v-if="makeLogoSrc(m)"
                :src="makeLogoSrc(m)"
                :alt="m"
                class="make-pick-logo"
              />
              <span v-else class="make-pick-logo make-pick-logo-empty">{{ m.slice(0, 1) }}</span>
              <span>{{ m }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2100;
  padding: 1rem;
}

.make-picker-overlay {
  align-items: flex-end;
}

.make-picker {
  width: 100%;
  max-width: 720px;
  max-height: min(80vh, 640px);
  overflow: auto;
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 16px 16px 0 0;
  padding: 1rem 1rem 1.25rem;
  box-shadow: 0 -12px 40px rgba(0, 0, 0, 0.35);
}

.make-picker-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.85rem;
}

.make-picker-head h2 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--color-heading);
}

.modal-close-btn {
  position: static;
  width: 2rem;
  height: 2rem;
  padding: 0;
  border: 2px solid var(--color-border);
  border-radius: 50%;
  background: var(--color-background-mute);
  color: var(--color-text);
  font-size: 1.25rem;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close-btn:hover {
  border-color: var(--red-primary);
  color: var(--red-primary);
  background: rgba(216, 31, 38, 0.1);
}

.make-picker-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.55rem;
}

.make-pick {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 0.25rem;
  border-radius: 12px;
  border: 1px solid var(--color-border);
  background: var(--color-background);
  color: var(--color-text);
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  text-align: center;
}

.make-pick:hover {
  border-color: var(--red-primary);
  color: var(--red-primary);
}

.make-pick.active {
  border-color: transparent;
  background: linear-gradient(135deg, #d81f26 0%, #f0353d 100%);
  color: #fff;
}

.make-pick-logo {
  width: 3.1rem;
  height: 3.1rem;
  object-fit: contain;
}

.make-pick-logo-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: var(--color-background-mute);
  font-size: 0.85rem;
  font-weight: 800;
}

.make-pick.active .make-pick-logo-empty {
  background: rgba(255, 255, 255, 0.18);
  color: #fff;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

@media (min-width: 769px) {
  .make-picker-overlay {
    align-items: center;
  }
  .make-picker {
    border-radius: 16px;
    max-height: min(76vh, 620px);
  }
}

@media (max-width: 768px) {
  .make-picker-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>
