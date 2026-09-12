/**
 * Theme composable for dark/light mode toggle
 */

import { ref, watch } from 'vue'

export type Theme = 'light' | 'dark'

const STORAGE_KEY = 'ctu-theme-preference'

const readStoredTheme = (): Theme => {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored === 'light' || stored === 'dark') {
      return stored
    }
  } catch {
    // Storage can throw in private browsing; fall through to the brand default.
  }
  return 'dark'
}

const applyTheme = (next: Theme) => {
  const root = document.documentElement
  root.setAttribute('data-theme', next)
  root.classList.remove('light', 'dark')
  root.classList.add(next)
}

// Kept at module scope so every caller shares one source of truth, and applied
// on import so the right palette is on <html> before the first paint.
const theme = ref<Theme>(readStoredTheme())
applyTheme(theme.value)

watch(theme, (next) => {
  try {
    localStorage.setItem(STORAGE_KEY, next)
  } catch {
    // The preference just won't persist if storage is unavailable.
  }
  applyTheme(next)
})

export function useTheme() {
  const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
  }

  const setTheme = (next: Theme) => {
    theme.value = next
  }

  return {
    theme,
    toggleTheme,
    setTheme,
    isDark: () => theme.value === 'dark',
    isLight: () => theme.value === 'light',
  }
}
