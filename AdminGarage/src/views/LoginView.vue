<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '@/api/client'
import { setAuth } from '@/stores/auth'

const route = useRoute()
const router = useRouter()

onMounted(() => {
  const rawRedirect = (route.query.redirect as string) || ''
  const isBadRedirect = rawRedirect.startsWith('/') && (rawRedirect.includes('localhost') || rawRedirect.includes(':'))
  if (isBadRedirect) {
    router.replace({ path: '/login', query: {} })
  }
})
const username = ref('')
const password = ref('')
const showPassword = ref(false)
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  if (!username.value.trim() || !password.value) {
    error.value = 'Please enter username and password.'
    return
  }
  loading.value = true
  try {
    const res = await api.auth.login(username.value.trim(), password.value)
    setAuth(res.token, res.user)
    await nextTick()
    const rawRedirect = (route.query.redirect as string) || '/'
    const isSafePath = rawRedirect.startsWith('/') && !rawRedirect.includes('://') && !rawRedirect.includes('localhost') && !rawRedirect.includes(':')
    const redirect = isSafePath ? rawRedirect : '/'
    await router.replace(redirect)
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-card">
      <div class="login-header">
        <img src="/ctu-logo.svg" alt="Cars Team Unlimited" class="login-logo" />
        <h1>Admin Login</h1>
        <p class="login-subtitle red-outline">CARS TEAM UNLIMITED</p>
      </div>
      <form @submit.prevent="submit" class="login-form">
        <p v-if="error" class="login-error">{{ error }}</p>
        <div class="field">
          <label for="username">Username</label>
          <input
            id="username"
            v-model="username"
            type="text"
            autocomplete="username"
            placeholder="Enter username"
            :disabled="loading"
          />
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="password-wrap">
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              placeholder="Enter password"
              :disabled="loading"
            />
            <button
              type="button"
              class="password-toggle"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
              :disabled="loading"
              @click="showPassword = !showPassword"
            >
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-login" :disabled="loading">
          {{ loading ? 'Signing in…' : 'Sign In' }}
        </button>
      </form>
  </div>
</template>

<style scoped>
.login-card {
  width: 100%;
  max-width: 400px;
  padding: 2.5rem;
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(216, 31, 38, 0.15);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
  margin-bottom: 1rem;
}

.login-header h1 {
  font-size: 1.75rem;
  margin: 0 0 0.25rem;
  color: var(--color-heading);
  font-weight: 700;
}

.login-subtitle {
  font-size: 0.85rem;
  font-weight: 800;
  letter-spacing: 2px;
  margin: 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.login-error {
  padding: 0.75rem 1rem;
  background: rgba(245, 101, 101, 0.15);
  border: 1px solid rgba(245, 101, 101, 0.4);
  border-radius: 8px;
  color: #f56565;
  font-size: 0.9rem;
  margin: 0;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.field label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-heading);
}

.field input {
  padding: 0.75rem 1rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: var(--color-background);
  color: var(--color-text);
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.field input:focus {
  outline: none;
  border-color: var(--red-primary);
}

.field input::placeholder {
  color: var(--color-text-muted);
}

.field input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.password-wrap {
  position: relative;
}

.password-wrap input {
  width: 100%;
  padding-right: 2.75rem;
  box-sizing: border-box;
}

.password-toggle {
  position: absolute;
  top: 50%;
  right: 0.65rem;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  border: 0;
  background: transparent;
  color: var(--color-text-muted);
  cursor: pointer;
}

.password-toggle:hover:not(:disabled) {
  color: var(--red-primary);
}

.password-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-login {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, var(--red-primary), var(--red-light));
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.5rem;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(216, 31, 38, 0.4);
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}
</style>
