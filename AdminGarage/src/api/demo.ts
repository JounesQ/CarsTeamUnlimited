import { DEMO_TOKEN } from '@/stores/auth'
import type { Paginated, Vehicle, VehicleForm } from '@/types/vehicle'

const STORAGE_KEY = 'admin_demo_vehicles'

export function isDemoSession() {
  return typeof window !== 'undefined' && localStorage.getItem('admin_token') === DEMO_TOKEN
}

function nowIso() {
  return new Date().toISOString()
}

function sampleVehicles(): Vehicle[] {
  const created = nowIso()
  return [
    {
      id: 'demo-honda-civic',
      title: '2022 Honda Civic RS',
      slug: '2022-honda-civic-rs',
      status: 'available',
      year: 2022,
      make: 'Honda',
      model: 'Civic RS',
      vehicle_type: 'Sedan',
      category: null,
      transmission: 'Automatic',
      fuel_type: 'Gasoline',
      price: 980000,
      can_test_drive: true,
      is_negotiable: true,
      down_payment: null,
      dp_all_in: false,
      details_and_financing: 'Demo unit for Vercel testing. Financing available.',
      financing_options: { '36 months': 28500, '48 months': 22800, '60 months': 19500 },
      views_count: 12,
      created_at: created,
      updated_at: created,
      images: [],
    },
    {
      id: 'demo-toyota-vios',
      title: '2021 Toyota Vios G',
      slug: '2021-toyota-vios-g',
      status: 'available',
      year: 2021,
      make: 'Toyota',
      model: 'Vios G',
      vehicle_type: 'Sedan',
      category: null,
      transmission: 'Automatic',
      fuel_type: 'Gasoline',
      price: 620000,
      can_test_drive: true,
      is_negotiable: true,
      down_payment: null,
      dp_all_in: false,
      details_and_financing: 'Demo unit. Bank financing in as fast as 1 day.',
      financing_options: { '36 months': 18900, '60 months': 12800 },
      views_count: 8,
      created_at: created,
      updated_at: created,
      images: [],
    },
    {
      id: 'demo-ford-ranger',
      title: '2020 Ford Ranger Wildtrak',
      slug: '2020-ford-ranger-wildtrak',
      status: 'reserved',
      year: 2020,
      make: 'Ford',
      model: 'Ranger Wildtrak',
      vehicle_type: 'Pickup',
      category: null,
      transmission: 'Automatic',
      fuel_type: 'Diesel',
      price: 1280000,
      can_test_drive: false,
      is_negotiable: true,
      down_payment: null,
      dp_all_in: false,
      details_and_financing: 'Reserved demo listing.',
      financing_options: null,
      views_count: 21,
      created_at: created,
      updated_at: created,
      images: [],
    },
    {
      id: 'demo-mitsubishi-xpander',
      title: '2019 Mitsubishi Xpander GLS',
      slug: '2019-mitsubishi-xpander-gls',
      status: 'sold',
      year: 2019,
      make: 'Mitsubishi',
      model: 'Xpander GLS',
      vehicle_type: 'MPV',
      category: null,
      transmission: 'Automatic',
      fuel_type: 'Gasoline',
      price: 740000,
      can_test_drive: false,
      is_negotiable: false,
      down_payment: null,
      dp_all_in: false,
      details_and_financing: 'Sold demo listing.',
      financing_options: null,
      views_count: 30,
      created_at: created,
      updated_at: created,
      images: [],
    },
  ]
}

function loadVehicles(): Vehicle[] {
  const raw = localStorage.getItem(STORAGE_KEY)
  if (!raw) {
    const seeded = sampleVehicles()
    localStorage.setItem(STORAGE_KEY, JSON.stringify(seeded))
    return seeded
  }
  try {
    const parsed = JSON.parse(raw) as Vehicle[]
    return Array.isArray(parsed) ? parsed : sampleVehicles()
  } catch {
    const seeded = sampleVehicles()
    localStorage.setItem(STORAGE_KEY, JSON.stringify(seeded))
    return seeded
  }
}

function saveVehicles(list: Vehicle[]) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(list))
}

function slugify(title: string) {
  return title
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
}

function vehicleFromForm(form: VehicleForm, existing?: Vehicle): Vehicle {
  const id = existing?.id || crypto.randomUUID()
  const images = (form.images || [])
    .filter((img) => img.image_path.trim())
    .map((img, index) => ({
      id: index + 1,
      vehicle_id: id,
      image_path: img.image_path,
      image_url: img.image_url || img.image_path,
      position: index + 1,
      is_primary: index === 0,
    }))

  return {
    id,
    title: form.title,
    slug: existing?.slug || slugify(form.title) || id,
    status: form.status,
    year: existing?.year || new Date().getFullYear(),
    make: form.make,
    model: existing?.model || form.title,
    vehicle_type: existing?.vehicle_type || 'Sedan',
    category: existing?.category ?? null,
    transmission: existing?.transmission || 'Automatic',
    fuel_type: existing?.fuel_type ?? null,
    price: Number(form.price) || 0,
    can_test_drive: form.can_test_drive !== false,
    is_negotiable: existing?.is_negotiable ?? true,
    down_payment: existing?.down_payment ?? null,
    dp_all_in: existing?.dp_all_in ?? false,
    details_and_financing: form.details_and_financing?.trim() || null,
    financing_options: existing?.financing_options ?? null,
    views_count: existing?.views_count ?? 0,
    created_at: existing?.created_at || nowIso(),
    updated_at: nowIso(),
    images,
  }
}

export const demoAdmin = {
  getStats() {
    const list = loadVehicles()
    const byStatus = (status: string) => list.filter((v) => v.status === status)
    const sum = (rows: Vehicle[]) => rows.reduce((n, v) => n + (Number(v.price) || 0), 0)
    const makeCounts = new Map<string, number>()
    for (const v of list) {
      makeCounts.set(v.make, (makeCounts.get(v.make) || 0) + 1)
    }
    const by_make = [...makeCounts.entries()]
      .map(([make, count]) => ({ make, count }))
      .sort((a, b) => b.count - a.count)
      .slice(0, 10)

    return Promise.resolve({
      total: list.length,
      available: byStatus('available').length,
      sold: byStatus('sold').length,
      reserved: byStatus('reserved').length,
      coming: byStatus('coming').length,
      total_value: sum(byStatus('available')),
      total_value_all: sum(list),
      total_value_sold: sum(byStatus('sold')),
      total_value_reserved: sum(byStatus('reserved')),
      total_value_coming: sum(byStatus('coming')),
      by_make,
    })
  },

  getVehicles(params?: {
    page?: number
    per_page?: number
    status?: string
    make?: string
    can_test_drive?: boolean
  }): Promise<Paginated<Vehicle>> {
    let list = loadVehicles()
    if (params?.status) list = list.filter((v) => v.status === params.status)
    if (params?.make) list = list.filter((v) => v.make === params.make)
    if (params?.can_test_drive != null) {
      list = list.filter((v) => v.can_test_drive === params.can_test_drive)
    }
    const page = params?.page || 1
    const perPage = params?.per_page || 15
    const start = (page - 1) * perPage
    return Promise.resolve({
      data: list.slice(start, start + perPage),
      current_page: page,
      last_page: Math.max(1, Math.ceil(list.length / perPage) || 1),
      per_page: perPage,
      total: list.length,
    })
  },

  getVehicle(id: string): Promise<Vehicle> {
    const found = loadVehicles().find((v) => v.id === id)
    if (!found) return Promise.reject(new Error('Vehicle not found'))
    return Promise.resolve(found)
  },

  createVehicle(form: VehicleForm): Promise<Vehicle> {
    const list = loadVehicles()
    const created = vehicleFromForm(form)
    list.unshift(created)
    saveVehicles(list)
    return Promise.resolve(created)
  },

  updateVehicle(id: string, form: VehicleForm): Promise<Vehicle> {
    const list = loadVehicles()
    const index = list.findIndex((v) => v.id === id)
    if (index < 0) return Promise.reject(new Error('Vehicle not found'))
    const updated = vehicleFromForm(form, list[index])
    list[index] = updated
    saveVehicles(list)
    return Promise.resolve(updated)
  },

  deleteVehicle(id: string): Promise<void> {
    saveVehicles(loadVehicles().filter((v) => v.id !== id))
    return Promise.resolve()
  },

  uploadImage(file: File): Promise<{ path: string; display_url?: string }> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader()
      reader.onload = () => {
        const dataUrl = String(reader.result || '')
        resolve({ path: dataUrl, display_url: dataUrl })
      }
      reader.onerror = () => reject(new Error('Could not read image'))
      reader.readAsDataURL(file)
    })
  },
}

export const demoAuth = {
  logout() {
    return Promise.resolve({ message: 'Logged out' })
  },
  me() {
    const raw = localStorage.getItem('admin_user')
    const user = raw
      ? (JSON.parse(raw) as { id: string; name: string; username: string })
      : { id: 'demo', name: 'Demo Admin', username: 'admin' }
    return Promise.resolve({ user })
  },
}
