/**
 * Composable for cached API calls with stale-while-revalidate pattern
 * 
 * Pattern:
 * 1. Return cached data immediately if available (stale is ok)
 * 2. Fetch fresh data in background
 * 3. Update cache and UI when fresh data arrives
 * 4. If fetch fails, keep using cached data (offline resilience)
 */

import { ref, type Ref } from 'vue'
import { api } from '@/api/client'
import { getCache, setCache, hasFreshCache } from '@/services/vehicleCache'
import type { Vehicle, Paginated } from '@/types/vehicle'

interface CachedVehiclesOptions {
  params?: {
    page?: number
    per_page?: number
    make?: string
    year?: number
    min_price?: number
    max_price?: number
    vehicle_type?: string
    category?: string
    fuel_type?: string
  }
  onError?: (error: Error) => void
}

/**
 * Fetch vehicles with caching support
 * Returns cached data immediately if available, then updates in background
 */
export function useCachedVehicles() {
  const data = ref<Paginated<Vehicle> | null>(null) as Ref<Paginated<Vehicle> | null>
  const loading = ref(false)
  const isStale = ref(false) // true when showing cached data while fetching fresh
  const error = ref<string | null>(null)
  
  async function load(options: CachedVehiclesOptions = {}) {
    const endpoint = 'vehicles'
    const { params, onError } = options
    
    // Step 1: Check cache and return immediately if available
    const cached = getCache<Paginated<Vehicle>>(endpoint, params)
    if (cached) {
      data.value = cached
      isStale.value = !hasFreshCache(endpoint, params)
      // Don't show loading spinner when we have cached data
      loading.value = false
    } else {
      // No cache - show loading state
      loading.value = true
      isStale.value = false
    }
    
    // Step 2: Fetch fresh data in background
    try {
      const fresh = await api.getVehicles(params)
      
      // Step 3: Update cache
      setCache(endpoint, fresh, params)
      
      // Step 4: Update UI with fresh data
      data.value = fresh
      isStale.value = false
      error.value = null
    } catch (err) {
      // Step 5: If fetch fails but we have cache, keep using it (offline support)
      if (cached) {
        console.warn('API fetch failed, using cached data:', err)
        // Keep showing cached data, don't throw error
        error.value = null
      } else {
        // No cache available and fetch failed - this is a real error
        // The API client already transforms network errors to friendly messages
        const errorMessage = err instanceof Error ? err.message : 'Our server is currently under maintenance. Please try again later.'
        error.value = errorMessage
        if (onError) {
          onError(err instanceof Error ? err : new Error(errorMessage))
        }
      }
    } finally {
      loading.value = false
    }
  }
  
  return {
    data,
    loading,
    isStale, // indicates when showing cached data while revalidating
    error,
    load,
  }
}

/**
 * Fetch single vehicle **with caching** for offline-ready detail views.
 *
 * Behavior:
 * - If cached detail exists → return it immediately (works offline).
 * - In background, try to fetch fresh detail and update cache (stale-while-revalidate).
 * - If nothing cached and API fetch fails → throw error (same as before).
 */
export async function getCachedVehicle(id: string): Promise<Vehicle> {
  const endpoint = 'vehicle_detail'
  const params = { id }

  // Step 1: Try to read from cache first for instant/offline load
  const cached = getCache<Vehicle>(endpoint, params)
  if (cached) {
    // Fire-and-forget background refresh to keep cache fresh
    api.getVehicle(id)
      .then((fresh) => {
        try {
          setCache(endpoint, fresh, params)
        } catch (err) {
          console.warn('Failed to update vehicle detail cache:', err)
        }
      })
      .catch((err) => {
        // Network/API error - keep using cached data silently
        console.warn('Failed to refresh vehicle detail, using cached data:', err)
      })

    return cached
  }

  // Step 2: No cache available - fetch from API (online-only path)
  const fresh = await api.getVehicle(id)

  // Step 3: Save to cache for future offline use
  setCache(endpoint, fresh, params)

  return fresh
}
