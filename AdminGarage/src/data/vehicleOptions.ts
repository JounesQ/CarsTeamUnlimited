export const MAKES = [
   'Mitsubishi', 'Toyota','Suzuki', 'Nissan', 'Hyundai',
   'Honda'
] as const

export const VEHICLE_TYPES = [
  { value: 'car', label: 'Car' },
  { value: 'suv', label: 'SUV' },
  { value: 'truck', label: 'Truck' },
  { value: 'van', label: 'Van' },
  { value: 'other', label: 'Other' },
] as const

export const CATEGORIES = [
  'Sedan', 'Hatchback', 'Coupe', 'Convertible', 'Wagon', 'SUV', 'Crossover', 'Pickup Truck',
  'Van', 'Minivan', 'Sports', 'Luxury', 'Compact', 'Midsize', 'Full-size', 'Other',
] as const

export const FUEL_TYPES = [
  'Petrol', 'Diesel','Gasoline', 'Electric (EV)', 'Hybrid (Electric-Gasoline)',  'Other',
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