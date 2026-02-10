<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="app-wrap">
    <header class="site-header">
      <RouterLink to="/vehicles" class="logo">Garage Admin</RouterLink>
      <nav>
        <RouterLink to="/vehicles">Vehicles</RouterLink>
        <RouterLink to="/vehicles/new">Add vehicle</RouterLink>
      </nav>
    </header>
    <main class="main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.app-wrap { min-height: 100vh; display: flex; flex-direction: column; }
.site-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1rem 2rem; border-bottom: 1px solid var(--color-border);
  background: var(--color-background-soft);
}
.logo { font-weight: 700; font-size: 1.25rem; color: var(--color-heading); text-decoration: none; }
.logo:hover { color: hsla(160, 100%, 37%, 1); }
nav { display: flex; gap: 1rem; }
nav a { padding: 0.5rem 0.75rem; text-decoration: none; color: var(--color-text); border-radius: 6px; }
nav a:hover { background: var(--color-background-mute); }
nav a.router-link-active { color: hsla(160, 100%, 37%, 1); font-weight: 500; }
.main { flex: 1; padding: 0 1rem 2rem; }
</style>
