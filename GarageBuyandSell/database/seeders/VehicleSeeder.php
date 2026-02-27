<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * VehicleSeeder — 6 vehicles per make, all available.
 * Options match CustomerGarage src/data/vehicleOptions.ts (no "Other").
 * All vehicles have down_payment and financing_options (1–5 years).
 * No images — add manually. Run: php artisan db:seed --class=VehicleSeeder
 */
class VehicleSeeder extends Seeder
{
    /** @see vehicleOptions.ts MAKES (lines 2–5, no Other) */
    private const MAKES = [
        'Audi', 'BMW', 'Chevrolet','Ford',
        'GMC', 'Honda', 'Hyundai', 'Jeep', 'Kia', 'Lexus', 'Mazda',
        'Mercedes-Benz', 'Mini', 'Mitsubishi', 'Subaru', 'Tesla',
        'Toyota',
    ];

    /** @see vehicleOptions.ts VEHICLE_TYPES (car, suv, truck, van, motorcycle only) */
    private const VEHICLE_TYPES = ['car', 'suv', 'truck', 'van'];

    /** @see vehicleOptions.ts CATEGORIES (no Other) */
    private const CATEGORIES = [
        'Sedan', 'Hatchback', 'Coupe', 'Convertible', 'Wagon', 'SUV', 'Crossover', 'Pickup Truck',
        'Van', 'Minivan', 'Sports', 'Luxury', 'Compact', 'Midsize', 'Full-size',
    ];

    /** @see vehicleOptions.ts FUEL_TYPES: Petrol, Diesel, Electric, Hybrid, Plug-in Hybrid, LPG, CNG */
    private const FUEL_TYPES = [
        'Petrol', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid',
    ];

    /** @see AdminGarage vehicleOptions.ts TRANSMISSIONS (value) */
    private const TRANSMISSIONS = [
        'automatic', 'manual', 'semi-automatic','dual-clutch', 'single-speed', 'other',
    ];

    private const COLORS = [
        'Black', 'White', 'Silver', 'Gray', 'Red', 'Blue', 'Brown', 'Green', 'Beige', 'Other',
    ];

    public function run(): void
    {
        $modelsByMake = $this->sampleModels();

        foreach (self::MAKES as $make) {
            $models = $modelsByMake[$make] ?? ['Model A', 'Model B', 'Model C', 'Model D', 'Model E', 'Model F'];

            for ($i = 1; $i <= 4; $i++) {
                $model = $models[array_rand($models)];
                $year = (int) rand(2015, 2025);
                $price = round(rand(300000, 2500000), -3);
                $title = "{$year} {$make} {$model}";
                $slug = Str::slug($title) . '-' . Str::random(6);
                $maxDp = (int) min(300000, $price * 0.5);
                $downPayment = round((float) rand(50000, max(50000, $maxDp)), -3);

                Vehicle::create([
                    'id' => Str::uuid()->toString(),
                    'title' => $title,
                    'slug' => $slug,
                    'status' => 'available',
                    'year' => $year,
                    'make' => $make,
                    'model' => $model,
                    'vehicle_type' => self::VEHICLE_TYPES[array_rand(self::VEHICLE_TYPES)],
                    'category' => self::CATEGORIES[array_rand(self::CATEGORIES)],
                    'transmission' => self::TRANSMISSIONS[array_rand(self::TRANSMISSIONS)],
                    'fuel_type' => self::FUEL_TYPES[array_rand(self::FUEL_TYPES)],
                    'color' => self::COLORS[array_rand(self::COLORS)],
                    'door_count' => rand(2, 5),
                    'seat_capacity' => rand(2, 8),
                    'mileage' => rand(5000, 150000),
                    'grade' => ['Base', 'Mid', 'Top', 'Sport', 'Limited'][array_rand(['Base', 'Mid', 'Top', 'Sport', 'Limited'])],
                    'price' => $price,
                    'is_negotiable' => (bool) rand(0, 1),
                    'down_payment' => $downPayment,
                    'dp_all_in' => (bool) rand(0, 1),
                    'financing_options' => $this->financingOptionsForPrice($price, $downPayment),
                    'views_count' => rand(0, 500),
                ]);
            }
        }
    }

    private function sampleModels(): array
    {
        return [
            'Acura' => ['ILX', 'TLX', 'MDX', 'RDX', 'Integra', 'NSX'],
            'Audi' => ['A3', 'A4', 'A6', 'Q3', 'Q5', 'Q7'],
            'BMW' => ['3 Series', '5 Series', 'X1', 'X3', 'X5'],
            'Buick' => ['Encore', 'Envision', 'Enclave', 'Regal', 'LaCrosse'],
            'Cadillac' => ['CT4', 'CT5', 'XT4', 'XT5', 'Escalade'],
            'Chevrolet' => ['Trailblazer', 'Colorado', 'Captiva', 'Spark', 'Trax'],
            'Chrysler' => ['Pacifica', '300', 'Voyager'],
            'Dodge' => ['Charger', 'Challenger', 'Durango', 'Hornet'],
            'Ford' => ['Ranger', 'Everest', 'Territory', 'EcoSport', 'Explorer'],
            'GMC' => ['Terrain', 'Acadia', 'Sierra', 'Yukon', 'Canyon'],
            'Honda' => ['City', 'Civic', 'Accord', 'CR-V', 'BR-V', 'HR-V', 'Brio'],
            'Hyundai' => ['Accent', 'Elantra', 'Tucson', 'Santa Fe', 'Creta', 'Staria'],
            'Infiniti' => ['Q50', 'Q60', 'QX50', 'QX60', 'QX80'],
            'Jeep' => ['Wrangler', 'Compass', 'Cherokee', 'Grand Cherokee', 'Gladiator'],
            'Kia' => ['Seltos', 'Sportage', 'Sorento', 'Stonic', 'Carnival'],
            'Lexus' => ['ES', 'IS', 'NX', 'RX', 'LX', 'UX'],
            'Lincoln' => ['Aviator', 'Nautilus', 'Corsair', 'Navigator'],
            'Mazda' => ['2', '3', '6', 'CX-3', 'CX-5', 'CX-30', 'BT-50'],
            'Mercedes-Benz' => ['A-Class', 'C-Class', 'E-Class', 'GLA', 'GLC', 'GLE'],
            'Mini' => ['Cooper', 'Countryman', 'Clubman', 'Convertible'],
            'Mitsubishi' => ['Mirage', 'Lancer', 'Xpander', 'Montero Sport', 'Strada', 'Pajero'],
            'Nissan' => ['Navara', 'Terra', 'Kicks', 'Almera', 'Livina', 'Patrol'],
            'Porsche' => ['Cayenne', 'Macan', 'Panamera', '911', 'Taycan'],
            'Ram' => ['1500', '2500', '3500', 'ProMaster'],
            'Subaru' => ['Outback', 'Forester', 'Crosstrek', 'WRX', 'Ascent'],
            'Tesla' => ['Model 3', 'Model Y', 'Model S', 'Model X', 'Cybertruck'],
            'Toyota' => ['Vios', 'Corolla', 'Camry', 'Fortuner', 'Hilux', 'Innova', 'Rush'],
            'Volkswagen' => ['Polo', 'Lavida', 'Tiguan', 'Teramont', 'Santana'],
            'Volvo' => ['S60', 'XC40', 'XC60', 'XC90', 'S90'],
        ];
    }

    /** Financing options: 1, 2, 3, 4, 5 years only. All vehicles get this. */
    private function financingOptionsForPrice(float $price, float $downPayment): array
    {
        $financed = $price - $downPayment;
        $options = [];
        foreach ([1, 2, 3, 4, 5] as $years) {
            $months = $years * 12;
            $monthly = (int) round($financed / $months, -2);
            $monthly = max(10000, min(150000, $monthly));
            $key = $years === 1 ? '1_year' : "{$years}_years";
            $options[$key] = $monthly;
        }
        return $options;
    }
}
