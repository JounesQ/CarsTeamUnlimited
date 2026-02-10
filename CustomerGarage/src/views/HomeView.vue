<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<template>
  <main class="home">
    <h1>Garage Buy & Sell</h1>
    <p>Find your next vehicle. Browse our available listings.</p>
    <button type="button" class="cta" @click="router.push('/vehicles')">Browse vehicles</button>
  </main>
</template>

<style scoped>
.home { text-align: center; padding: 3rem 1rem; }
.home h1 { font-size: 2rem; margin-bottom: 0.5rem; color: var(--color-heading); }
.home p { margin-bottom: 1.5rem; color: var(--color-text); }
.cta {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  border: none;
  border-radius: 8px;
  background: hsla(160, 100%, 37%, 1);
  color: white;
  cursor: pointer;
}
.cta:hover { opacity: 0.9; }
</style>
