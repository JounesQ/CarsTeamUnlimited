export const MAKES = [
  'Audi', 'BAIC', 'BMW', 'BYD', 'Chevrolet', 'Changan', 'Ford',
  'GAC', 'Geely', 'GMC', 'Honda', 'Hyundai', 'Isuzu', 'Jeep', 'Kia', 'Lexus', 'Mazda',
  'Mini', 'Mitsubishi', 'Nissan', 'Subaru', 'Suzuki', 'Tesla',
  'Toyota', 'Other',
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
  'Automatic', 'Manual', 'Semi-Automatic', 'CVT', 'Dual-Clutch', 'Single-Speed', 'Other',
] as const