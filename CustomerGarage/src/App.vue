<script setup lang="ts">
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { computed, ref, provide } from 'vue'

const route = useRoute()
const isHomePage = computed(() => route.path === '/')
const isVehiclesPage = computed(() => route.path === '/vehicles')
const isAboutPage = computed(() => route.path === '/about')

const showMobileFooter = computed(() => isHomePage.value || isAboutPage.value)

const showMobileFilters = ref(false)
provide('showMobileFilters', showMobileFilters)

const toggleMobileFilters = () => {
  showMobileFilters.value = !showMobileFilters.value
}
</script>

<template>
  <div class="app-wrap">
    <header class="site-header" :class="{ 'header-transparent': isHomePage }">
      <RouterLink to="/" class="logo">
        <img src="/p7-logo.png?v=3" alt="P7 Garahe Gallery" class="logo-img" />
        <span class="logo-text">P7 GARAHE GALLERY</span>
      </RouterLink>
      <nav class="nav desktop-nav">
        <RouterLink to="/" class="nav-link">Home</RouterLink>
        <RouterLink to="/vehicles" class="nav-link">Vehicles</RouterLink>
        <RouterLink to="/about" class="nav-link">About</RouterLink>
      </nav>
    </header>
    <main class="main">
      <RouterView />
    </main>
    
    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
      <RouterLink to="/" class="mobile-nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span>Home</span>
      </RouterLink>
      <RouterLink to="/vehicles" class="mobile-nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="3" width="15" height="13"></rect>
          <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
          <circle cx="5.5" cy="18.5" r="2.5"></circle>
          <circle cx="18.5" cy="18.5" r="2.5"></circle>
        </svg>
        <span>Vehicles</span>
      </RouterLink>
      <button v-if="isVehiclesPage" @click="toggleMobileFilters" class="mobile-nav-item mobile-filter-btn" :class="{ active: showMobileFilters }">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
        </svg>
        <span>Filters</span>
      </button>
      <RouterLink v-else to="/about" class="mobile-nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <span>About</span>
      </RouterLink>
    </nav>
    
    <footer class="site-footer desktop-footer">
      <p>&copy; 2026 P7 Garahe Gallery. All rights reserved.</p>
    </footer>
    <footer v-if="showMobileFooter" class="site-footer mobile-footer">
      <p>&copy; 2026 P7 Garahe Gallery. All rights reserved.</p>
    </footer>
  </div>
</template>

<style scoped>
.app-wrap {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #000;
}

/* Header */
.site-header {
  position: relative;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 3rem;
  background: rgba(0, 0, 0, 0.95);
  border-bottom: 1px solid rgba(212, 175, 55, 0.2);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.header-transparent {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  background: transparent;
  border-bottom: 1px solid rgba(212, 175, 55, 0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  transition: transform 0.3s ease;
}

.logo:hover {
  transform: scale(1.05);
}

.logo-img {
  width: 48px;
  height: 48px;
  object-fit: contain;
  background: transparent;
}

.logo-text {
  font-weight: 900;
  font-size: 1.25rem;
  background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 1px;
}

.nav {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.nav-link {
  padding: 0.625rem 1.25rem;
  text-decoration: none;
  color: #e0e0e0;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #d4af37, #f4d03f);
  transform: translateX(-50%);
  transition: width 0.3s ease;
}

.nav-link:hover {
  color: #d4af37;
}

.nav-link:hover::after {
  width: 60%;
}

.nav-link.router-link-active {
  color: #d4af37;
  background: rgba(212, 175, 55, 0.1);
}

.nav-link.router-link-active::after {
  width: 60%;
}

.main {
  flex: 1;
  padding: 0;
}

/* Mobile Bottom Navigation */
.mobile-bottom-nav {
  display: none;
}

/* Footer */
.site-footer {
  background: #0a0a0a;
  border-top: 1px solid rgba(212, 175, 55, 0.2);
  padding: 1.5rem 2rem;
  text-align: center;
  color: #888;
  font-size: 0.9rem;
}

.site-footer p {
  margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .app-wrap {
    padding-bottom: 70px;
  }

  .site-header {
    padding: 1rem 1.5rem;
    justify-content: center;
  }

  .logo {
    gap: 0.75rem;
  }

  .logo-text {
    font-size: 1.35rem;
  }

  .logo-img {
    width: 52px;
    height: 52px;
  }

  /* Hide desktop navigation on mobile */
  .desktop-nav {
    display: none;
  }

  /* Hide desktop footer on mobile */
  .desktop-footer {
    display: none;
  }
  
  .mobile-footer {
    display: block;
    padding: 1rem 1.25rem 0.75rem;
    font-size: 0.8rem;
  }

  .main {
    padding-bottom: 0;
  }

  /* Mobile Bottom Navigation */
  .mobile-bottom-nav {
    display: flex;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.98);
    border-top: 1px solid rgba(212, 175, 55, 0.3);
    padding: 0.5rem;
    z-index: 1000;
    backdrop-filter: blur(10px);
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.5);
  }

  .mobile-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    padding: 0.5rem;
    text-decoration: none;
    color: #b0b0b0;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 0.75rem;
    font-weight: 500;
    background: transparent;
    border: none;
    cursor: pointer;
  }

  .mobile-nav-item svg {
    width: 24px;
    height: 24px;
  }

  .mobile-nav-item:active {
    transform: scale(0.95);
  }

  .mobile-nav-item.router-link-active,
  .mobile-nav-item.active {
    color: var(--gold-primary);
    background: rgba(212, 175, 55, 0.1);
  }

  .mobile-nav-item:not(.router-link-active):not(.active):active {
    background: rgba(212, 175, 55, 0.05);
  }
}
</style>
