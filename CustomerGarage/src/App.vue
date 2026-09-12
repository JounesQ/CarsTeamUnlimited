<script setup lang="ts">
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { computed } from 'vue'
import { useTheme } from '@/composables/useTheme'

const { theme, toggleTheme } = useTheme()

const route = useRoute()
const isHomePage = computed(() => route.path === '/')
const isAboutPage = computed(() => route.path === '/about')

const showMobileFooter = computed(() => isHomePage.value || isAboutPage.value)
</script>

<template>
  <div class="app-wrap">
    <header class="site-header" :class="{ 'header-transparent': isHomePage }">
      <RouterLink to="/" class="logo">
        <img src="/ctu-logo.svg" alt="Cars Team Unlimited" class="logo-img" />
        <span class="logo-text red-outline">CARS TEAM UNLIMITED</span>
      </RouterLink>
      <nav class="nav desktop-nav">
        <RouterLink to="/" class="nav-link">Home</RouterLink>
        <RouterLink to="/vehicles" class="nav-link">Vehicles</RouterLink>
        <RouterLink to="/about" class="nav-link">About</RouterLink>
        <button
          type="button"
          class="theme-toggle"
          :title="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
          :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
          @click="toggleTheme"
        >
          <svg v-if="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
          </svg>
        </button>
      </nav>
      <button
        type="button"
        class="theme-toggle mobile-theme-toggle"
        :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        @click="toggleTheme"
      >
        <svg v-if="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>
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
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 8l1.3-2.6A1 1 0 0 1 9.2 5h5.6a1 1 0 0 1 .9.4L17 8"></path>
          <path d="M3 13l2-5h14l2 5"></path>
          <path d="M3 13h18v4a1 1 0 0 1-1 1h-1"></path>
          <circle cx="7.5" cy="17.5" r="1.5"></circle>
          <circle cx="16.5" cy="17.5" r="1.5"></circle>
        </svg>
        <span>Vehicles</span>
      </RouterLink>
      <RouterLink to="/about" class="mobile-nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <span>About</span>
      </RouterLink>
    </nav>
    
    <footer class="site-footer desktop-footer">
      <p>&copy; 2026 Cars Team Unlimited. All rights reserved.</p>
    </footer>
    <footer v-if="showMobileFooter" class="site-footer mobile-footer">
      <p>&copy; 2026 Cars Team Unlimited. All rights reserved.</p>
    </footer>
  </div>
</template>

<style scoped>
.app-wrap {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--color-background);
  transition: background-color 0.3s ease;
}

/* Header */
.site-header {
  position: relative;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 3rem;
  background: var(--color-header-bg);
  border-bottom: 1px solid rgba(216, 31, 38, 0.2);
  backdrop-filter: blur(10px);
  transition: background-color 0.3s ease, border-color 0.3s ease;
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
  color: var(--color-text);
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
  background: linear-gradient(90deg, #d81f26, #f0353d);
  transform: translateX(-50%);
  transition: width 0.3s ease;
}

.nav-link:hover {
  color: #d81f26;
}

.nav-link:hover::after {
  width: 60%;
}

.nav-link.router-link-active {
  color: #d81f26;
  background: rgba(216, 31, 38, 0.1);
}

.nav-link.router-link-active::after {
  width: 60%;
}

.theme-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  margin-left: 0.5rem;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  background: var(--color-accent-soft);
  color: var(--color-accent);
  cursor: pointer;
  transition: all 0.3s ease;
}

.theme-toggle:hover {
  background: var(--red-primary);
  border-color: var(--red-primary);
  color: var(--white-pure);
  transform: rotate(15deg);
}

.mobile-theme-toggle {
  display: none;
}
@media (min-width: 769px) {
  .site-header {
    position: sticky;
    top: 0;
    padding: 1rem 2.5rem;
  }
  .site-header.header-transparent {
    position: sticky;
  }
}

.main {
  flex: 1;
  padding-right: 1rem;
  padding-left: 1rem;
}

/* Mobile Bottom Navigation */
.mobile-bottom-nav {
  display: none;
}

/* Footer */
.site-footer {
  background: var(--color-footer-bg);
  border-top: 1px solid rgba(216, 31, 38, 0.2);
  padding: 1.5rem 2rem;
  text-align: center;
  color: var(--color-text-muted);
  font-size: 0.9rem;
  transition: background-color 0.3s ease, color 0.3s ease;
}
@media (min-width: 769px) {
  .site-footer {
    padding: 1.5rem 0.5rem;
  }
}

.site-footer p {
  margin: 0;
}

/* Hide mobile footer on desktop (only show inside media query) */
.mobile-footer {
  display: none;
}

/* Responsive */
@media (max-width: 768px) {
  .app-wrap {
    padding-bottom: calc(64px + env(safe-area-inset-bottom, 0px));
  }

  .site-header {
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 0.40rem 0.5rem;
    justify-content: center;
  }
  .site-header.header-transparent {
    position: sticky;
  }

  .logo {
    gap: 0.5rem;
  }

  .logo-text {
    font-size: 1.50rem;
  }

  .logo-img {
    width: 40px;
    height: 40px;
  }

  /* Hide desktop navigation on mobile */
  .desktop-nav {
    display: none;
  }

  /* Logo stays optically centred, so the toggle is pinned rather than inline */
  .mobile-theme-toggle {
    display: flex;
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    margin-left: 0;
  }

  .mobile-theme-toggle:hover {
    transform: translateY(-50%);
  }

  /* Hide desktop footer on mobile */
  .desktop-footer {
    display: none;
  }
  
  .mobile-footer {
    display: block;
    padding: 0.5rem 0.5rem 0.5rem;
    font-size: 0.75rem;
  }

  .main {
    padding-bottom: 0;
  }

  /* Mobile Bottom Navigation */
  /* Floating pill bar, detached from the screen edge */
  .mobile-bottom-nav {
    display: flex;
    gap: 0.15rem;
    position: fixed;
    bottom: calc(0.5rem + env(safe-area-inset-bottom, 0px));
    left: 1.25rem;
    right: 1.25rem;
    background: var(--color-header-bg);
    border: 1px solid var(--color-card-border);
    border-radius: 999px;
    padding: 0.2rem;
    z-index: 1000;
    backdrop-filter: blur(14px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.24);
  }

  .mobile-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.1rem;
    padding: 0.28rem 0.15rem;
    text-decoration: none;
    color: var(--color-text-muted);
    border-radius: 999px;
    transition: color 0.25s ease, background 0.25s ease, transform 0.2s ease;
    font-size: 0.58rem;
    font-weight: 600;
    letter-spacing: 0.2px;
    background: transparent;
    border: none;
    cursor: pointer;
  }

  .mobile-nav-item svg {
    width: 16px;
    height: 16px;
  }

  .mobile-nav-item:active {
    transform: scale(0.94);
  }

  .mobile-nav-item.router-link-active {
    color: var(--white-pure);
    background: linear-gradient(135deg, #d81f26 0%, #f0353d 100%);
    box-shadow: 0 4px 14px rgba(216, 31, 38, 0.45);
  }
}
</style>
