# Client-Side Caching Implementation

## Overview

The customer frontend implements client-side caching for vehicle listings to provide:

- **Instant loading** on repeat visits
- **Offline support** - displays cached data when API is unavailable
- **Background refresh** - automatically updates stale data
- **Reduced API load** - fewer unnecessary requests

## Architecture

### Stale-While-Revalidate Pattern

The implementation follows the SWR (Stale-While-Revalidate) pattern:

1. **First Visit**: Fetch from API, show loading spinner, cache the response
2. **Subsequent Visits**: 
   - Return cached data immediately (no loading delay)
   - Fetch fresh data in the background
   - Update cache and UI when fresh data arrives
3. **Offline/API Down**: Continue displaying cached data

### Components

#### 1. Cache Service (`src/services/vehicleCache.ts`)

Low-level caching layer using localStorage:

- **Storage**: Uses browser localStorage for persistence
- **Keys**: Generates unique keys per endpoint + parameters
- **TTL**: 5-minute freshness window (configurable)
- **Versioning**: Cache version to handle schema changes
- **Error handling**: Graceful fallback if localStorage is full/disabled

Key functions:
```typescript
getCache<T>(endpoint, params?) // Retrieve cached data
setCache<T>(endpoint, data, params?) // Store data in cache
hasFreshCache(endpoint, params?) // Check if cache is still fresh
clearVehicleCache() // Clear all vehicle cache
```

#### 2. Cached API Composable (`src/composables/useCachedApi.ts`)

High-level API for components:

```typescript
const { data, loading, isStale, error, load } = useCachedVehicles()

await load({
  params: { page: 1, make: 'Toyota' },
  onError: (err) => console.error(err)
})
```

**Returns:**
- `data` - The vehicle data (from cache or fresh)
- `loading` - True only when no cache exists and fetching
- `isStale` - True when showing cached data while refreshing
- `error` - Error message (only if fetch fails AND no cache)
- `load` - Function to fetch with given parameters

#### 3. Integration (`src/views/VehicleList.vue`)

The VehicleList component uses the cached API:

- Loads cached data instantly
- Shows cache indicator when refreshing in background
- Falls back to cached data if API is unavailable

## Cache Behavior

### When is data cached?

- All vehicle listing API calls (`/vehicles`)
- Includes pagination and filter parameters
- Each unique parameter combination is cached separately

### When is cache invalidated?

- After 5 minutes (TTL expired)
- When cache version changes
- When manually cleared (see below)

### What is NOT cached?

- Individual vehicle details (modal view)
- Admin frontend API calls
- Image assets (uses normal browser cache)

## Manual Cache Management

### Clear Cache (Browser Console)

```javascript
// Import and use in browser console
localStorage.clear() // Clear all localStorage (including cache)

// Or specifically clear vehicle cache:
Object.keys(localStorage)
  .filter(k => k.startsWith('garage_vehicle_cache'))
  .forEach(k => localStorage.removeItem(k))
```

### Check Cache Statistics

Add this to any component to inspect cache:

```typescript
import { getCacheStats } from '@/services/vehicleCache'

console.log(getCacheStats())
// Returns: { count, totalSize, entries }
```

## Configuration

### Adjust Cache TTL

Edit `src/services/vehicleCache.ts`:

```typescript
const CACHE_TTL_MS = 5 * 60 * 1000 // Current: 5 minutes
// Change to:
const CACHE_TTL_MS = 10 * 60 * 1000 // 10 minutes
const CACHE_TTL_MS = 30 * 60 * 1000 // 30 minutes
const CACHE_TTL_MS = 60 * 60 * 1000 // 1 hour
```

### Change Cache Version

Increment to invalidate all existing cache:

```typescript
const CACHE_VERSION = 1 // Increment when data structure changes
```

## Browser Compatibility

- **localStorage required**: Works in all modern browsers
- **Graceful degradation**: Falls back to regular API calls if localStorage is unavailable
- **Storage limits**: Typical 5-10MB per origin (sufficient for vehicle data)

## Testing

### Test Cache Hit

1. Visit vehicle listings page
2. Open DevTools → Network tab
3. Refresh page
4. Should see instant UI render, then background API call

### Test Offline Mode

1. Visit vehicle listings page (cache populated)
2. Open DevTools → Network tab
3. Enable "Offline" mode
4. Refresh page
5. Should still display cached vehicle listings

### Test Cache Refresh

1. Clear cache (localStorage)
2. Visit listings page
3. Check Network tab - should show API call
4. Refresh page
5. UI should appear instantly (from cache)
6. Network tab shows background refresh call

## Production Considerations

### Pros
- Significantly faster perceived loading time
- Reduced API load and bandwidth
- Offline resilience
- Better user experience

### Cons
- Slight storage usage (typically <1MB)
- Potential stale data for TTL duration
- Cache invalidation complexity (handled automatically)

### Monitoring

Consider adding analytics to track:
- Cache hit rate
- Stale data refresh rate
- Offline fallback usage

## Troubleshooting

### Cache not working?

1. Check browser localStorage is enabled
2. Check console for cache errors
3. Verify cache stats: `getCacheStats()`
4. Check cache version matches

### Seeing stale data?

- Reduce TTL in `vehicleCache.ts`
- Clear cache manually
- Cache automatically refreshes in background

### Too much storage used?

- Reduce TTL to expire data sooner
- Implement cache size limit (if needed)
- Clear old entries automatically

## Admin Frontend

**Important**: Caching is NOT applied to the admin frontend (`AdminGarage`).

Admin operations require real-time data and use different API endpoints:
- Admin: `/api/admin/vehicles` (no cache)
- Customer: `/api/vehicles` (cached)
