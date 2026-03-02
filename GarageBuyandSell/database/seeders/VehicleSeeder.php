<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * VehicleSeeder — 4 vehicles per make, all available.
 * Schema: id, title, slug, status, make, price, details_and_financing.
 * No images — add manually. Run: php artisan db:seed --class=VehicleSeeder
 */
class VehicleSeeder extends Seeder
{
    /** @see vehicleOptions.ts MAKES */
    private const MAKES = [
        'Audi', 'BMW', 'Chevrolet', 'Ford',
        'GMC', 'Honda', 'Hyundai', 'Jeep', 'Kia', 'Lexus', 'Mazda',
        'Mercedes-Benz', 'Mini', 'Mitsubishi', 'Subaru', 'Tesla',
        'Toyota',
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
                $downPayment = round((float) rand(50000, min(300000, (int) ($price * 0.5))), -3);
                $detailsAndFinancing = $this->buildDetailsAndFinancing($price, $downPayment);

                Vehicle::create([
                    'id' => Str::uuid()->toString(),
                    'title' => $title,
                    'slug' => $slug,
                    'status' => 'available',
                    'make' => $make,
                    'price' => $price,
                    'details_and_financing' => $detailsAndFinancing,
                ]);
            }
        }
    }

    private function buildDetailsAndFinancing(float $price, float $downPayment): string
    {
        $financed = $price - $downPayment;
        $lines = [
            "Down Payment: ₱" . number_format($downPayment),
            "Financed Amount: ₱" . number_format($financed),
            "",
            "Monthly options:",
        ];
        foreach ([1, 2, 3, 4, 5] as $years) {
            $months = $years * 12;
            $monthly = (int) round($financed / $months, -2);
            $monthly = max(10000, min(150000, $monthly));
            $lines[] = "  {$years} year(s): ₱" . number_format($monthly) . "/mo";
        }
        return implode("\n", $lines);
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

}
