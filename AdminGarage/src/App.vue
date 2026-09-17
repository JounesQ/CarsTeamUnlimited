<script setup lang="ts">
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useTheme } from '@/composables/useTheme'
import { clearAuth, authUser } from '@/stores/auth'
import { api } from '@/api/client'

const { theme, toggleTheme } = useTheme()
const route = useRoute()
const isLoginPage = () => route.path === '/login'
const customerSiteUrl = (
  (import.meta.env.VITE_CUSTOMER_SITE_URL as string)
  || (import.meta.env.DEV ? 'http://localhost:5173' : 'https://carsteamunlimited.com')
).replace(/\/$/, '')

async function logout() {
  try {
    await api.auth.logout()
  } catch {
    /* ignore */
  }
  clearAuth()
  const base = (import.meta.env.BASE_URL || '/').replace(/\/$/, '') || ''
  const loginPath = (base && !base.includes('localhost') && !base.includes(':')) ? `${base}/login` : '/login'
  window.location.href = `${window.location.origin}${loginPath.replace(/\/+/g, '/')}`
}
</script>

<template>
  <div class="app-wrap">
    <header v-if="!isLoginPage()" class="site-header">
      <RouterLink to="/vehicles" class="logo">
        <img src="/ctu-logo.svg" alt="Cars Team Unlimited" class="logo-img" />
        <div class="logo-text">
          <span class="logo-brand red-outline">CARS TEAM UNLIMITED</span>
          <span class="logo-subtitle">Admin Panel</span>
        </div>
      </RouterLink>
      <nav class="nav">
        <RouterLink to="/vehicles" class="nav-link">Vehicles</RouterLink>
        <RouterLink to="/stats" class="nav-link">Statistics</RouterLink>
        <RouterLink to="/vehicles/new" class="nav-link">Add Vehicle</RouterLink>
        <a
          class="nav-link nav-link-external"
          :href="customerSiteUrl"
          target="_blank"
          rel="noopener noreferrer"
          title="Open customer site"
        >
          View site
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
            <polyline points="15 3 21 3 21 9"></polyline>
            <line x1="10" y1="14" x2="21" y2="3"></line>
          </svg>
        </a>
        <span v-if="authUser" class="user-badge">{{ authUser.username }}</span>
        <button @click="logout" class="btn-logout" title="Logout">Logout</button>
        <button @click="toggleTheme" class="theme-toggle" :title="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'">
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
    </header>
    <main class="main" :class="{ 'main-login': isLoginPage() }">
      <RouterView />
    </main>
    <footer v-if="!isLoginPage()" class="site-footer">
      <p>&copy; 2026 Cars Team Unlimited Admin. All rights reserved.</p>
    </footer>
  </div>
</template>

<style scoped>
.app-wrap {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--color-background);
}

/* Header */
.site-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 3rem;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-background-soft);
  transition: all 0.3s ease;
}
@media (min-width: 769px) {
  .site-header {
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 1.25rem 0.5rem;
  }
}

.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  transition: transform 0.3s ease;
}

.logo:hover {
  transform: scale(1.02);
}

.logo-img {
  width: 48px;
  height: 48px;
  object-fit: contain;
  background: transparent;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.logo-brand {
  font-weight: 900;
  font-size: 1.15rem;
  letter-spacing: 1px;
}

.logo-subtitle {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
  letter-spacing: 0.5px;
  text-transform: uppercase;
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
  background: linear-gradient(90deg, var(--red-primary), var(--red-light));
  transform: translateX(-50%);
  transition: width 0.3s ease;
}

.nav-link:hover {
  color: var(--red-primary);
}

.nav-link:hover::after {
  width: 60%;
}

.nav-link.router-link-active {
  color: var(--red-primary);
  background: rgba(216, 31, 38, 0.1);
}

.nav-link.router-link-active::after {
  width: 60%;
}

.nav-link-external {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.user-badge {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin-right: 0.5rem;
}

.btn-logout {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  color: var(--color-text-muted);
  cursor: pointer;
  transition: all 0.3s ease;
  margin-right: 0.5rem;
}

.btn-logout:hover {
  border-color: #f56565;
  color: #f56565;
}

.theme-toggle {
  padding: 0.625rem;
  background: rgba(216, 31, 38, 0.1);
  border: 1px solid rgba(216, 31, 38, 0.3);
  border-radius: 8px;
  color: var(--red-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  margin-left: 0.5rem;
}

.theme-toggle:hover {
  background: rgba(216, 31, 38, 0.2);
  border-color: var(--red-primary);
  transform: scale(1.05);
}

.theme-toggle svg {
  display: block;
}

.main {
  flex: 1;
  padding: 2rem 1rem;
  min-height: calc(100vh - 180px);
  overflow: auto;
}

.main.main-login {
  min-height: 100vh;
  padding: 2rem 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}
@media (min-width: 769px) {
  .main {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

/* Footer */
.site-footer {
  padding: 1.5rem 2rem;
  background: var(--color-background-soft);
  border-top: 1px solid var(--color-border);
  text-align: center;
  color: var(--color-text-muted);
  font-size: 0.875rem;
}
@media (min-width: 769px) {
  .site-footer {
    padding: 1.5rem 0.5rem;
  }
}

/* Responsive - full screen on small displays */
@media (max-width: 768px) {
  .site-header {
    padding: 0.5rem 0.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .logo-brand {
    font-size: 0.9rem;
  }

  .logo-img {
    width: 36px;
    height: 36px;
  }

  .logo-text {
    gap: 0;
  }

  .nav {
    width: 100%;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.25rem;
  }

  .nav-link {
    padding: 0.4rem 0.75rem;
    font-size: 0.85rem;
  }

  .main {
    padding: 0.5rem 0.25rem;
    min-height: calc(100vh - 140px);
  }

  .site-footer {
    padding: 0.5rem 0.5rem;
    font-size: 0.75rem;
  }
}
</style>
