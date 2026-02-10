const API_BASE = (import.meta.env.VITE_API_URL as string) || 'http://127.0.0.1:8000/api'

export function getStorageBase(): string {
  const base = API_BASE.replace(/\/api\/?$/, '')
  return base || 'http://127.0.0.1:8000'
}

export function imageUrl(path: string): string {
  if (!path) return ''
  if (path.startsWith('http')) return path
  const base = getStorageBase()
  if (path.startsWith('storage/') || path.startsWith('/storage/')) {
    return path.startsWith('/') ? base + path : base + '/' + path
  }
  return `${base}/storage/${path}`
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const url = path.startsWith('http') ? path : `${API_BASE}${path}`
  const res = await fetch(url, {
    ...options,
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...options.headers },
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
}
