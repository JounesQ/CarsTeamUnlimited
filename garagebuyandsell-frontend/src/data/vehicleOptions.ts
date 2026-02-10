export const MAKES = [
  'Acura', 'Audi', 'BMW', 'Buick', 'Cadillac', 'Chevrolet', 'Chrysler', 'Dodge', 'Ford',
  'GMC', 'Honda', 'Hyundai', 'Infiniti', 'Jeep', 'Kia', 'Lexus', 'Lincoln', 'Mazda',
  'Mercedes-Benz', 'Mini', 'Mitsubishi', 'Nissan', 'Porsche', 'Ram', 'Subaru', 'Tesla',
  'Toyota', 'Volkswagen', 'Volvo', 'Other',
] as const

export const VEHICLE_TYPES = [
  { value: 'car', label: 'Car' },
  { value: 'suv', label: 'SUV' },
  { value: 'truck', label: 'Truck' },
  { value: 'van', label: 'Van' },
  { value: 'motorcycle', label: 'Motorcycle' },
  { value: 'other', label: 'Other' },
] as const

export const CATEGORIES = [
  'Sedan', 'Hatchback', 'Coupe', 'Convertible', 'Wagon', 'SUV', 'Crossover', 'Pickup Truck',
  'Van', 'Minivan', 'Sports', 'Luxury', 'Compact', 'Midsize', 'Full-size', 'Other',
] as const

export const FUEL_TYPES = [
  'Petrol', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid', 'LPG', 'CNG', 'Other',
] as const

export const TRANSMISSIONS = [
  { value: 'automatic', label: 'Automatic' },
  { value: 'manual', label: 'Manual' },
  { value: 'semi-automatic', label: 'Semi-Automatic' },
  { value: 'cvt', label: 'CVT' },
  { value: 'dual-clutch', label: 'Dual-Clutch' },
  { value: 'single-speed', label: 'Single-Speed' },
  { value: 'other', label: 'Other' },
] as const
