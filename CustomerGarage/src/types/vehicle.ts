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
  year: number
  make: string
  model: string
  vehicle_type: string
  category: string | null
  transmission: string
  fuel_type: string | null
  color: string | null
  door_count: number | null
  seat_capacity: number | null
  mileage: number | null
  grade: string | null
  price: number
  is_negotiable: boolean
  down_payment: number | null
  dp_all_in: boolean
  financing_options: Record<string, number> | null
  views_count: number
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
