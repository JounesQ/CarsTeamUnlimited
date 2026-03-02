export interface VehicleImage {
  id: number
  vehicle_id: string
  image_path: string
  position: number
  is_primary: boolean
}

export interface Vehicle {
  id: string
  title: string
  slug: string
  status: string
  make: string
  price: number
  details_and_financing: string | null
  created_at: string
  updated_at: string
  images?: VehicleImage[]
}

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
