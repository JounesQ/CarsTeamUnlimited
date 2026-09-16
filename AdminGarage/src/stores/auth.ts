import { ref, computed } from 'vue'

const TOKEN_KEY = 'admin_token'
const USER_KEY = 'admin_user'

const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
const storedUser = localStorage.getItem(USER_KEY)
const user = ref<{ id: string; name: string; username: string } | null>(
  storedUser ? JSON.parse(storedUser) : null
)

export const isAuthenticated = computed(() => !!token.value)

export function setAuth(t: string, u: { id: string; name: string; username: string }) {
  token.value = t
  user.value = u
  localStorage.setItem(TOKEN_KEY, t)
  localStorage.setItem(USER_KEY, JSON.stringify(u))
}

export function clearAuth() {
  token.value = null
  user.value = null
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

export function getToken() {
  return token.value
}

export function getAuthUser() {
  return user.value
}

export { user as authUser }
