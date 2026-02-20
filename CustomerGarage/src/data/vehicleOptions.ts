export const MAKES = [
  'Audi', 'BMW', 'Chevrolet', 'Ford',
  'GMC', 'Honda', 'Hyundai',  'Jeep', 'Kia', 'Lexus',  'Mazda',
   'Mini', 'Mitsubishi', 'Nissan',   'Subaru', 'Tesla',
  'Toyota',  'Other',
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
