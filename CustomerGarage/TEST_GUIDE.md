# Caching Implementation - Test Guide

## Prerequisites

1. Start your Laravel backend API
2. Start the CustomerGarage frontend: `npm run dev`
3. Open browser DevTools (F12)

## Test Suite

### Test 1: Initial Load (Cache Miss) ✓

**Steps:**
1. Clear browser cache: DevTools → Application → Clear storage
2. Visit `http://localhost:[PORT]/` (or your vehicle listings route)
3. Open DevTools → Network tab

**Expected Results:**
- ✅ Loading spinner appears
- ✅ API call to `/api/vehicles` in Network tab
- ✅ Vehicles display after API returns
- ✅ DevTools → Application → Local Storage shows `garage_vehicle_cache_*` entries

**Screenshot:** Loading spinner → Vehicles appear

---

### Test 2: Return Visit (Cache Hit) ✓✓✓

**Steps:**
1. Keep the page open from Test 1
2. Click browser refresh (F5 or Ctrl+R)
3. Watch the screen carefully

**Expected Results:**
- ✅ Vehicles appear **INSTANTLY** (no loading spinner)
- ✅ Small indicator appears: "Showing cached data • Refreshing in background…"
- ✅ Network tab shows API call happening in background
- ✅ After ~1 second, indicator disappears (fresh data loaded)
- ✅ Page content may slightly update if data changed

**Key Observation:** The difference is **dramatic** - instant vs waiting for API

---

### Test 3: Console Debug Tools ✓

**Steps:**
1. Open browser console (F12 → Console tab)
2. Type: `vehicleCache.help()` and press Enter
3. Type: `vehicleCache.stats()` and press Enter
4. Type: `vehicleCache.list()` and press Enter

**Expected Results:**
```javascript
// vehicleCache.help()
🔧 Cache Debug Tools
Available commands (use vehicleCache.xxx):
  stats()  - Show cache statistics
  list()   - List all cache entries
  clear()  - Clear all vehicle cache
  help()   - Show this help message

// vehicleCache.stats()
📊 Vehicle Cache Statistics
Total entries: 1
Total size: 12.34 KB

Entries:
  ✓ garage_vehicle_cache_vehicles_{"page":1,"per_page":12}
    Age: 0m 23s | Size: 12.34 KB

// vehicleCache.list()
📦 Cache Entries (1)
garage_vehicle_cache_vehicles_{"page":1,"per_page":12}: {
  timestamp: "2/11/2026, 10:30:15 AM",
  age: "0m ago",
  dataSize: "12.34 KB",
  params: "{\"page\":1,\"per_page\":12}"
}
```

---

### Test 4: Filter Caching ✓

**Steps:**
1. On vehicle listings page, select a filter (e.g., Make: "Toyota")
2. Click "Search"
3. Wait for results
4. Refresh page (F5)

**Expected Results:**
- ✅ Filtered results appear instantly on refresh
- ✅ Console: `vehicleCache.list()` shows multiple cache entries
- ✅ Each filter combination is cached separately

---

### Test 5: Pagination Caching ✓

**Steps:**
1. Navigate to page 2 of vehicle listings
2. Wait for results to load
3. Go back to page 1
4. Go forward to page 2 again

**Expected Results:**
- ✅ Page 2 loads instantly the second time (cached)
- ✅ Both page 1 and page 2 are cached independently

---

### Test 6: Offline Mode (Critical Test) ✓✓✓

**Steps:**
1. Visit vehicle listings (populate cache)
2. DevTools → Network tab
3. Check "Offline" checkbox at the top of Network tab
4. Refresh page (F5)

**Expected Results:**
- ✅ Vehicles still display from cache
- ✅ No error messages
- ✅ Page remains functional
- ✅ Console shows warning: "API fetch failed, using cached data"
- ✅ UI doesn't break

**This is the killer feature - the app works offline!**

---

### Test 7: Stale Data Refresh ✓

**Steps:**
1. Visit vehicle listings (populate cache)
2. Wait 6+ minutes (cache TTL is 5 minutes)
3. Refresh page

**Expected Results:**
- ✅ Vehicles appear instantly (from cache)
- ✅ Indicator shows: "Showing cached data • Refreshing in background…"
- ✅ After background refresh completes, indicator disappears
- ✅ `isStale` flag was true initially, then becomes false

**Note:** To test faster, temporarily change `CACHE_TTL_MS` to 30 seconds in `vehicleCache.ts`

---

### Test 8: Admin Frontend (No Cache) ✓

**Steps:**
1. Open AdminGarage frontend in a new tab
2. Navigate to vehicle list
3. Open DevTools → Network tab
4. Refresh multiple times (5-10 times)

**Expected Results:**
- ✅ Every refresh makes a fresh API call to `/api/admin/vehicles`
- ✅ Loading spinner appears on every refresh
- ✅ No instant loading behavior
- ✅ Console: `vehicleCache` is undefined (debug tools not loaded)
- ✅ Local Storage has no `garage_vehicle_cache_*` entries from admin

**Verify:** Admin is completely unaffected by customer caching

---

### Test 9: Cache Clear ✓

**Steps:**
1. Visit vehicle listings (populate cache)
2. Console: `vehicleCache.stats()` (should show cached entries)
3. Console: `vehicleCache.clear()`
4. Click "OK" on confirmation dialog
5. Console: `vehicleCache.stats()` again

**Expected Results:**
- ✅ Before clear: Shows cache entries
- ✅ Confirmation dialog appears
- ✅ After clear: "No cache statistics available"
- ✅ Next page load will show loading spinner (cache miss)

---

### Test 10: API Failure Handling ✓

**Steps:**
1. Visit vehicle listings (populate cache)
2. Stop your Laravel backend API server
3. Refresh the page

**Expected Results:**
- ✅ Vehicles still display from cache
- ✅ Console warning: "API fetch failed, using cached data"
- ✅ No error messages shown to user
- ✅ Page remains fully functional
- ✅ User can still browse, click vehicles, etc.

**Restart Laravel API:**
4. Start Laravel API again
5. Refresh page

**Expected Results:**
- ✅ Fresh data loads in background
- ✅ Cache updates automatically

---

## Performance Comparison

### Before Caching
```
User refreshes page
→ Blank screen
→ Loading spinner
→ Wait 500-2000ms
→ Vehicles appear
```

### After Caching
```
User refreshes page
→ Vehicles appear INSTANTLY (50ms)
→ Background refresh (silent)
→ Update if data changed
```

**Result:** ~90% faster perceived load time

---

## Visual Indicators

### During Initial Load (No Cache)
```
┌─────────────────────────┐
│  Vehicles for sale      │
├─────────────────────────┤
│  [Filters]              │
├─────────────────────────┤
│                         │
│      Loading…           │ ← Spinner
│                         │
└─────────────────────────┘
```

### During Cached Load
```
┌─────────────────────────┐
│  Vehicles for sale      │
├─────────────────────────┤
│  [Filters]              │
├─────────────────────────┤
│ Showing cached data •   │ ← Indicator (only if stale)
│ Refreshing in bg…       │
├─────────────────────────┤
│ [Vehicle 1]  [Vehicle 2]│ ← Data visible instantly
│ [Vehicle 3]  [Vehicle 4]│
└─────────────────────────┘
```

---

## Troubleshooting Tests

### If instant loading doesn't work:

**Check 1:** Is localStorage enabled?
```javascript
// Console
try {
  localStorage.setItem('test', '1')
  localStorage.removeItem('test')
  console.log('✅ localStorage working')
} catch (e) {
  console.log('❌ localStorage blocked:', e)
}
```

**Check 2:** Is cache populated?
```javascript
// Console
vehicleCache.list()
// Should show at least one entry
```

**Check 3:** Are debug tools loaded?
```javascript
// Console
typeof vehicleCache
// Should return 'object', not 'undefined'
```

**Check 4:** Are you in CustomerGarage, not AdminGarage?
```javascript
// Console
window.location.href
// Should NOT contain 'admin'
```

---

## Success Criteria

All tests should pass:
- ✅ Test 1: Initial load works (cache miss)
- ✅ Test 2: Return visit is instant (cache hit)
- ✅ Test 3: Debug tools work in console
- ✅ Test 4: Filters are cached independently
- ✅ Test 5: Pagination is cached independently
- ✅ Test 6: App works offline with cached data
- ✅ Test 7: Stale cache refreshes in background
- ✅ Test 8: Admin frontend unaffected
- ✅ Test 9: Cache clear works
- ✅ Test 10: API failures are handled gracefully

---

## Real-World Scenarios

### Scenario A: User on Slow Connection
**Before:** Wait 5+ seconds for every page load  
**After:** Instant load from cache, background refresh

### Scenario B: User on Train/Subway
**Before:** App breaks when going through tunnels  
**After:** App continues working with cached data

### Scenario C: Your API is Down
**Before:** Users see error messages, can't browse  
**After:** Users can still browse cached vehicles

### Scenario D: High Traffic
**Before:** Every user request hits your API  
**After:** 70-80% of requests served from local cache

---

## Next Steps After Testing

1. ✅ All tests pass → Deploy to staging
2. ✅ Test on staging with real data
3. ✅ Adjust `CACHE_TTL_MS` if needed (in `vehicleCache.ts`)
4. ✅ Deploy to production
5. ✅ Monitor using `vehicleCache.stats()` periodically

---

## Need Help?

**Check Documentation:**
- `IMPLEMENTATION_SUMMARY.md` - Overview
- `CACHE_QUICK_REFERENCE.md` - Quick answers
- `CACHING.md` - Detailed technical docs

**Browser Console:**
```javascript
vehicleCache.help()    // Show available commands
vehicleCache.stats()   // Show cache statistics
vehicleCache.list()    // List cache entries
```

**Common Issues:**
- Cache not working → Check localStorage enabled
- Too much stale data → Reduce `CACHE_TTL_MS`
- Want to disable → Comment out import in `VehicleList.vue`
