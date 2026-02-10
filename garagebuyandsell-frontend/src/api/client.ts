const API_BASE = (import.meta.env.VITE_API_URL as string) || 'http://127.0.0.1:8000/api'

/** Base URL of the Laravel app (no /api) for storage URLs */
export function getStorageBase(): string {
  const base = API_BASE.replace(/\/api\/?$/, '')
  return base || 'http://127.0.0.1:8000'
}

/** Resolve image path to full URL for display */
export function imageUrl(path: string): string {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `${getStorageBase()}/storage/${path}`
}

async function request<T>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const url = path.startsWith('http') ? path : `${API_BASE}${path}`
  const res = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...options.headers,
    },
  })
  if (!res.ok) {
    const text = await res.text()
    let err: string
    try {
      const j = JSON.parse(text)
      err = j.message || JSON.stringify(j.errors || j)
    } catch {
      err = text || res.statusText
    }
    throw new Error(err)
  }
  if (res.status === 204) return undefined as T
  return res.json() as Promise<T>
}

export const api = {
  // Public
  getVehicles(params?: {
    page?: number
    per_page?: number
    make?: string
    year?: number
    min_price?: number
    max_price?: number
    vehicle_type?: string
    category?: string
    fuel_type?: string
  }) {
    const sp = new URLSearchParams()
    if (params?.page) sp.set('page', String(params.page))
    if (params?.per_page) sp.set('per_page', String(params.per_page))
    if (params?.make) sp.set('make', params.make)
    if (params?.year) sp.set('year', String(params.year))
    if (params?.min_price != null) sp.set('min_price', String(params.min_price))
    if (params?.max_price != null) sp.set('max_price', String(params.max_price))
    if (params?.vehicle_type) sp.set('vehicle_type', params.vehicle_type)
    if (params?.category) sp.set('category', params.category)
    if (params?.fuel_type) sp.set('fuel_type', params.fuel_type)
    const q = sp.toString()
    return request<import('@/types/vehicle').Paginated<import('@/types/vehicle').Vehicle>>('/vehicles' + (q ? `?${q}` : ''))
  },
  getVehicle(id: string) {
    return request<import('@/types/vehicle').Vehicle>(`/vehicles/${id}`)
  },

  // Admin
  admin: {
    getVehicles(params?: { page?: number; per_page?: number; status?: string; make?: string }) {
      const sp = new URLSearchParams()
      if (params?.page) sp.set('page', String(params.page))
      if (params?.per_page) sp.set('per_page', String(params.per_page))
      if (params?.status) sp.set('status', params.status)
      if (params?.make) sp.set('make', params.make)
      const q = sp.toString()
      return request<import('@/types/vehicle').Paginated<import('@/types/vehicle').Vehicle>>('/admin/vehicles' + (q ? `?${q}` : ''))
    },
    getVehicle(id: string) {
      return request<import('@/types/vehicle').Vehicle>(`/admin/vehicles/${id}`)
    },
    createVehicle(body: import('@/types/vehicle').VehicleForm) {
      return request<import('@/types/vehicle').Vehicle>('/admin/vehicles', {
        method: 'POST',
        body: JSON.stringify(prepareBody(body)),
      })
    },
    updateVehicle(id: string, body: import('@/types/vehicle').VehicleForm) {
      return request<import('@/types/vehicle').Vehicle>(`/admin/vehicles/${id}`, {
        method: 'PUT',
        body: JSON.stringify(prepareBody(body)),
      })
    },
    deleteVehicle(id: string) {
      return request<void>(`/admin/vehicles/${id}`, { method: 'DELETE' })
    },
    uploadImage(file: File) {
      const url = `${API_BASE}/admin/vehicles/upload-image`
      const form = new FormData()
      form.append('image', file)
      return fetch(url, {
        method: 'POST',
        body: form,
        headers: { Accept: 'application/json' },
      }).then(async (res) => {
        if (!res.ok) {
          const text = await res.text()
          let err: string
          try {
            const j = JSON.parse(text)
            err = j.message || JSON.stringify(j.errors || j)
          } catch {
            err = text || res.statusText
          }
          throw new Error(err)
        }
        return res.json() as Promise<{ path: string }>
      })
    },
  },
}

function prepareBody(form: import('@/types/vehicle').VehicleForm) {
  const raw = form.financing_options && typeof form.financing_options === 'object' ? form.financing_options : {}
  const financing_options = Object.fromEntries(
    Object.entries(raw).filter(
      ([, v]) => typeof v === 'number' && !Number.isNaN(v) && v > 0
    )
  )
  return {
    ...form,
    door_count: form.door_count === '' ? null : form.door_count,
    seat_capacity: form.seat_capacity === '' ? null : form.seat_capacity,
    mileage: form.mileage === '' ? null : form.mileage,
    down_payment: form.down_payment === '' ? null : form.down_payment,
    category: form.category || null,
    fuel_type: form.fuel_type || null,
    color: form.color || null,
    grade: form.grade || null,
    financing_options: Object.keys(financing_options).length ? financing_options : null,
    images: form.images?.length ? form.images : [],
  }
}
