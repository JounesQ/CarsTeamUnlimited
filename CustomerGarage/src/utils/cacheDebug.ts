/**
 * Cache debugging utilities
 * Exposed on window for easy browser console access
 */

import { getCacheStats, clearVehicleCache } from '@/services/vehicleCache'

export interface CacheDebugTools {
  /**
   * Get cache statistics
   */
  stats: () => void
  
  /**
   * Clear all vehicle cache
   */
  clear: () => void
  
  /**
   * List all cache entries
   */
  list: () => void
  
  /**
   * Show help message
   */
  help: () => void
}

export function createCacheDebugTools(): CacheDebugTools {
  return {
    stats() {
      const stats = getCacheStats()
      if (!stats) {
        console.log('No cache statistics available')
        return
      }
      
      console.group('📊 Vehicle Cache Statistics')
      console.log(`Total entries: ${stats.count}`)
      console.log(`Total size: ${(stats.totalSize / 1024).toFixed(2)} KB`)
      console.log('\nEntries:')
      stats.entries.forEach((entry: any) => {
        const ageMin = Math.floor(entry.age / 60000)
        const ageSec = Math.floor((entry.age % 60000) / 1000)
        console.log(`  ${entry.fresh ? '✓' : '✗'} ${entry.key}`)
        console.log(`    Age: ${ageMin}m ${ageSec}s | Size: ${(entry.size / 1024).toFixed(2)} KB`)
      })
      console.groupEnd()
    },
    
    clear() {
      const confirm = window.confirm('Clear all vehicle cache? This will force a fresh API fetch.')
      if (!confirm) return
      
      clearVehicleCache()
      console.log('✓ Cache cleared. Refresh the page to see changes.')
    },
    
    list() {
      const keys = Object.keys(localStorage).filter(k => k.startsWith('garage_vehicle_cache'))
      if (keys.length === 0) {
        console.log('No cache entries found')
        return
      }
      
      console.group(`📦 Cache Entries (${keys.length})`)
      keys.forEach(key => {
        try {
          const data = localStorage.getItem(key)
          if (data) {
            const parsed = JSON.parse(data)
            const age = Date.now() - parsed.timestamp
            const ageMin = Math.floor(age / 60000)
            console.log(`${key}:`, {
              timestamp: new Date(parsed.timestamp).toLocaleString(),
              age: `${ageMin}m ago`,
              dataSize: `${(data.length / 1024).toFixed(2)} KB`,
              params: parsed.params
            })
          }
        } catch (e) {
          console.log(`${key}: Error parsing`)
        }
      })
      console.groupEnd()
    },
    
    help() {
      console.group('🔧 Cache Debug Tools')
      console.log('Available commands (use vehicleCache.xxx):')
      console.log('  stats()  - Show cache statistics')
      console.log('  list()   - List all cache entries')
      console.log('  clear()  - Clear all vehicle cache')
      console.log('  help()   - Show this help message')
      console.log('\nExample: vehicleCache.stats()')
      console.groupEnd()
    }
  }
}

// Expose to window in development mode
if (import.meta.env.DEV) {
  (window as any).vehicleCache = createCacheDebugTools()
  console.log('🔧 Cache debug tools loaded. Type "vehicleCache.help()" for commands.')
}
