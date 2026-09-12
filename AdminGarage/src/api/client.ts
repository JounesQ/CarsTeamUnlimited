const API_BASE = (import.meta.env.VITE_API_URL as string) || 'http://127.0.0.1:8000/api'

export function getAuthHeaders(): Record<string, string> {
  const token = typeof window !== 'undefined' ? localStorage.getItem('admin_token') : null
  const headers: Record<string, string> = { Accept: 'application/json' }
  if (token) headers['Authorization'] = `Bearer ${token}`
  return headers
}

export function getStorageBase(): string {
  const base = API_BASE.replace(/\/api\/?$/, '')
  return base || 'http://127.0.0.1:8000'
}

export function imageUrl(path: string, resolvedUrl?: string | null): string {
  if (resolvedUrl) return resolvedUrl
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `${getStorageBase()}/storage/${path}`
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const url = path.startsWith('http') ? path : `${API_BASE}${path}`
  const authHeaders = getAuthHeaders()
  const res = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...authHeaders,
      ...(options.headers as Record<string, string>),
    },
  })
  if (!res.ok) {
    if (res.status === 401) {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_user')
      const base = (import.meta.env.BASE_URL || '/').replace(/\/$/, '') || ''
      const loginPath = (base && !base.includes('localhost') && !base.includes(':')) ? `${base}/login` : '/login'
      const loginPathClean = loginPath.replace(/\/+/g, '/')
      const path = window.location.pathname + window.location.search
      const isSafePath = path.startsWith('/') && !path.includes('://') && !path.includes('localhost') && !path.includes(':')
      const redirect = encodeURIComponent(isSafePath ? path : '/')
      window.location.href = `${window.location.origin}${loginPathClean}?redirect=${redirect}`
      throw new Error('Session expired. Please log in again.')
    }
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

function prepareBody(form: import('@/types/vehicle').VehicleForm) {
  const images = form.images?.length
    ? form.images
        .filter((img) => img.image_path.trim())
        .map((img, index) => ({
          image_path: img.image_path,
          position: index + 1,
          is_primary: img.is_primary,
        }))
    : []

  return {
    title: form.title,
    status: form.status,
    make: form.make,
    price: form.price,
    can_test_drive: form.can_test_drive,
    details_and_financing: form.details_and_financing?.trim() || null,
    images,
  }
}

export interface VehicleStats {
  total: number
  available: number
  sold: number
  reserved: number
  coming: number
  total_value: number
  total_value_all: number
  total_value_sold: number
  total_value_reserved: number
  total_value_coming: number
  by_make: { make: string; count: number }[]
}

export const api = {
  admin: {
    getStats() {
      return request<VehicleStats>('/admin/vehicles/stats')
    },
    getVehicles(params?: { page?: number; per_page?: number; status?: string; make?: string; can_test_drive?: boolean }) {
      const sp = new URLSearchParams()
      if (params?.page) sp.set('page', String(params.page))
      if (params?.per_page) sp.set('per_page', String(params.per_page))
      if (params?.status) sp.set('status', params.status)
      if (params?.make) sp.set('make', params.make)
      if (params?.can_test_drive != null) sp.set('can_test_drive', params.can_test_drive ? '1' : '0')
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
      return fetch(url, { method: 'POST', body: form, headers: getAuthHeaders() }).then(async (res) => {
        if (res.status === 401) {
          localStorage.removeItem('admin_token')
          localStorage.removeItem('admin_user')
          const base = (import.meta.env.BASE_URL || '/').replace(/\/$/, '') || ''
          const loginPath = (base && !base.includes('localhost') && !base.includes(':')) ? `${base}/login` : '/login'
          const loginPathClean = loginPath.replace(/\/+/g, '/')
          window.location.href = `${window.location.origin}${loginPathClean}`
          throw new Error('Session expired. Please log in again.')
        }
        if (!res.ok) {
          const text = await res.text()
          let err: string
          try {
            const j = JSON.parse(text)
            const imgErr = j.errors?.image
            err = Array.isArray(imgErr) ? imgErr[0] : imgErr || j.message || JSON.stringify(j.errors || j)
          } catch {
            err = text || res.statusText
          }
          throw new Error(err)
        }
        return res.json() as Promise<{ path: string; display_url?: string }>
      })
    },
  },
  auth: {
    login(username: string, password: string) {
      return request<{ token: string; user: { id: string; name: string; username: string } }>('/admin/login', {
        method: 'POST',
        body: JSON.stringify({ username, password }),
      })
    },
    logout() {
      return request<{ message: string }>('/admin/logout', { method: 'POST' })
    },
    me() {
      return request<{ user: { id: string; name: string; username: string } }>('/admin/me')
    },
  },
}
