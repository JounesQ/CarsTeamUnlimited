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
  try {
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
  } catch (error) {
    // Replace browser's network error messages with a friendly message
    if (error instanceof Error) {
      const msg = error.message.toLowerCase()
      if (msg.includes('failed to fetch') || msg.includes('network') || msg.includes('fetch')) {
        throw new Error('Unable to connect to server. We are currently under maintenance. Please try again later.')
      }
    }
    throw error
  }
}

export const api = {
  getVehicles(params?: {
    page?: number
    per_page?: number
    make?: string
    min_price?: number
    max_price?: number
  }) {
    const sp = new URLSearchParams()
    if (params?.page) sp.set('page', String(params.page))
    if (params?.per_page) sp.set('per_page', String(params.per_page))
    if (params?.make) sp.set('make', params.make)
    if (params?.min_price != null) sp.set('min_price', String(params.min_price))
    if (params?.max_price != null) sp.set('max_price', String(params.max_price))
    const q = sp.toString()
    return request<import('@/types/vehicle').Paginated<import('@/types/vehicle').Vehicle>>('/vehicles' + (q ? `?${q}` : ''))
  },
  getVehicle(id: string) {
    return request<import('@/types/vehicle').Vehicle>(`/vehicles/${id}`)
  },
}
