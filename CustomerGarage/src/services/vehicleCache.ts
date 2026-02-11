/**
 * Client-side vehicle data cache for customer frontend
 * Uses localStorage with stale-while-revalidate pattern
 */

const CACHE_KEY = 'garage_vehicle_cache'
const CACHE_VERSION = 1
const CACHE_TTL_MS = 5 * 60 * 1000 // 5 minutes - consider data fresh for this duration

interface CacheEntry<T> {
  version: number
  data: T
  timestamp: number
  params: string // stringified params for cache key differentiation
}

interface CacheStore {
  [key: string]: CacheEntry<any>
}

/**
 * Generate a cache key for a specific API call with parameters
 */
function getCacheKey(endpoint: string, params?: Record<string, any>): string {
  const paramKey = params ? JSON.stringify(params) : ''
  return `${CACHE_KEY}_${endpoint}_${paramKey}`
}

/**
 * Check if cached data is still fresh (within TTL)
 */
function isFresh(timestamp: number): boolean {
  return Date.now() - timestamp < CACHE_TTL_MS
}

/**
 * Get cached data if available
 */
export function getCache<T>(endpoint: string, params?: Record<string, any>): T | null {
  try {
    const key = getCacheKey(endpoint, params)
    const cached = localStorage.getItem(key)
    
    if (!cached) return null
    
    const entry: CacheEntry<T> = JSON.parse(cached)
    
    // Check version to handle schema changes
    if (entry.version !== CACHE_VERSION) {
      localStorage.removeItem(key)
      return null
    }
    
    return entry.data
  } catch (error) {
    console.warn('Cache read error:', error)
    return null
  }
}

/**
 * Check if cached data exists and is still fresh
 */
export function hasFreshCache(endpoint: string, params?: Record<string, any>): boolean {
  try {
    const key = getCacheKey(endpoint, params)
    const cached = localStorage.getItem(key)
    
    if (!cached) return false
    
    const entry: CacheEntry<any> = JSON.parse(cached)
    
    return entry.version === CACHE_VERSION && isFresh(entry.timestamp)
  } catch {
    return false
  }
}

/**
 * Save data to cache
 */
export function setCache<T>(endpoint: string, data: T, params?: Record<string, any>): void {
  try {
    const key = getCacheKey(endpoint, params)
    const entry: CacheEntry<T> = {
      version: CACHE_VERSION,
      data,
      timestamp: Date.now(),
      params: params ? JSON.stringify(params) : '',
    }
    
    localStorage.setItem(key, JSON.stringify(entry))
  } catch (error) {
    // localStorage might be full or disabled
    console.warn('Cache write error:', error)
  }
}

/**
 * Clear all vehicle cache entries
 */
export function clearVehicleCache(): void {
  try {
    const keys = Object.keys(localStorage)
    keys.forEach((key) => {
      if (key.startsWith(CACHE_KEY)) {
        localStorage.removeItem(key)
      }
    })
  } catch (error) {
    console.warn('Cache clear error:', error)
  }
}

/**
 * Get cache statistics for debugging
 */
export function getCacheStats() {
  try {
    const keys = Object.keys(localStorage).filter((k) => k.startsWith(CACHE_KEY))
    const entries = keys.map((key) => {
      const cached = localStorage.getItem(key)
      if (!cached) return null
      const entry = JSON.parse(cached)
      return {
        key,
        age: Date.now() - entry.timestamp,
        fresh: isFresh(entry.timestamp),
        size: cached.length,
      }
    }).filter(Boolean)
    
    return {
      count: entries.length,
      totalSize: entries.reduce((sum, e) => sum + (e?.size || 0), 0),
      entries,
    }
  } catch {
    return null
  }
}
