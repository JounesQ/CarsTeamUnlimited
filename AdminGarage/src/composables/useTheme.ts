/**
 * Theme composable for dark/light mode toggle
 */

import { ref, onMounted, watch } from 'vue'

export type Theme = 'light' | 'dark'

const STORAGE_KEY = 'p7-theme-preference'

export function useTheme() {
  const theme = ref<Theme>('dark')

  // Initialize theme from localStorage or system preference
  const initTheme = () => {
    const stored = localStorage.getItem(STORAGE_KEY) as Theme | null
    
    if (stored) {
      theme.value = stored
    } else {
      // Default to dark theme for P7 Garahe Gallery
      theme.value = 'dark'
    }
    
    applyTheme(theme.value)
  }

  // Apply theme to document
  const applyTheme = (newTheme: Theme) => {
    document.documentElement.setAttribute('data-theme', newTheme)
    document.documentElement.classList.remove('light', 'dark')
    document.documentElement.classList.add(newTheme)
  }

  // Toggle between light and dark
  const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
  }

  // Set specific theme
  const setTheme = (newTheme: Theme) => {
    theme.value = newTheme
  }

  // Watch for theme changes and persist
  watch(theme, (newTheme) => {
    localStorage.setItem(STORAGE_KEY, newTheme)
    applyTheme(newTheme)
  })

  // Initialize on mount
  onMounted(() => {
    initTheme()
  })

  return {
    theme,
    toggleTheme,
    setTheme,
    isDark: () => theme.value === 'dark',
    isLight: () => theme.value === 'light',
  }
}
