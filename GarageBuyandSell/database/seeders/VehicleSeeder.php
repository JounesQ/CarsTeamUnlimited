<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * VehicleSeeder — sample cars for the storefront, no photos.
 * Makes match CustomerGarage/AdminGarage src/data/vehicleOptions.ts.
 * Run: php artisan db:seed --class=VehicleSeeder
 */
class VehicleSeeder extends Seeder
{
    /** @see CustomerGarage/src/data/vehicleOptions.ts MAKES */
    private const MAKES = [
        'Mitsubishi',
        'Toyota',
        'Suzuki',
        'Nissan',
        'Hyundai',
        'Honda',
    ];

    public function run(): void
    {
        Vehicle::query()->delete();

        foreach ($this->inventory() as $row) {
            $price = $row['price'];
            $downPayment = (int) round($price * 0.3, -3);

            Vehicle::create([
                'id' => Str::uuid()->toString(),
                'title' => $row['title'],
                'slug' => Str::slug($row['title']).'-'.Str::random(6),
                'status' => 'available',
                'make' => $row['make'],
                'price' => $price,
                'can_test_drive' => true,
                'details_and_financing' => $this->buildDetailsAndFinancing($price, $downPayment),
            ]);
        }
    }

    /**
     * Ten cars per make. Models are common PH units; prices are sample only.
     *
     * @return list<array{make: string, title: string, price: int}>
     */
    private function inventory(): array
    {
        return [
            ['make' => 'Mitsubishi', 'title' => '2021 Mitsubishi Mirage G4 GLS', 'price' => 548000],
            ['make' => 'Mitsubishi', 'title' => '2020 Mitsubishi Xpander GLS', 'price' => 798000],
            ['make' => 'Mitsubishi', 'title' => '2019 Mitsubishi Montero Sport GT', 'price' => 1288000],
            ['make' => 'Mitsubishi', 'title' => '2018 Mitsubishi Strada GLS 4x2', 'price' => 868000],
            ['make' => 'Mitsubishi', 'title' => '2022 Mitsubishi Xpander Cross', 'price' => 948000],
            ['make' => 'Mitsubishi', 'title' => '2017 Mitsubishi Adventure GLS Sport', 'price' => 428000],
            ['make' => 'Mitsubishi', 'title' => '2023 Mitsubishi Mirage GLX', 'price' => 618000],
            ['make' => 'Mitsubishi', 'title' => '2016 Mitsubishi Pajero GLS 4x4', 'price' => 1188000],
            ['make' => 'Mitsubishi', 'title' => '2021 Mitsubishi L300 Exceed', 'price' => 698000],
            ['make' => 'Mitsubishi', 'title' => '2019 Mitsubishi Outlander 2.4 GLS', 'price' => 1088000],

            ['make' => 'Toyota', 'title' => '2022 Toyota Vios 1.3 E', 'price' => 628000],
            ['make' => 'Toyota', 'title' => '2021 Toyota Innova 2.8 E', 'price' => 1098000],
            ['make' => 'Toyota', 'title' => '2020 Toyota Fortuner 2.4 G', 'price' => 1488000],
            ['make' => 'Toyota', 'title' => '2019 Toyota Hilux 2.4 G 4x2', 'price' => 998000],
            ['make' => 'Toyota', 'title' => '2023 Toyota Raize 1.0 Turbo G', 'price' => 778000],
            ['make' => 'Toyota', 'title' => '2018 Toyota Avanza 1.5 G', 'price' => 548000],
            ['make' => 'Toyota', 'title' => '2021 Toyota Rush 1.5 G', 'price' => 848000],
            ['make' => 'Toyota', 'title' => '2017 Toyota Wigo 1.0 G', 'price' => 398000],
            ['make' => 'Toyota', 'title' => '2020 Toyota Corolla Altis 1.6 V', 'price' => 848000],
            ['make' => 'Toyota', 'title' => '2019 Toyota Camry 2.5 V', 'price' => 1288000],

            ['make' => 'Suzuki', 'title' => '2023 Suzuki Swift GL', 'price' => 598000],
            ['make' => 'Suzuki', 'title' => '2022 Suzuki Ertiga GL', 'price' => 748000],
            ['make' => 'Suzuki', 'title' => '2021 Suzuki XL7 GLX', 'price' => 868000],
            ['make' => 'Suzuki', 'title' => '2020 Suzuki Jimny GLX', 'price' => 998000],
            ['make' => 'Suzuki', 'title' => '2019 Suzuki Ciaz GL', 'price' => 548000],
            ['make' => 'Suzuki', 'title' => '2022 Suzuki Vitara GLX', 'price' => 898000],
            ['make' => 'Suzuki', 'title' => '2018 Suzuki Celerio GL', 'price' => 368000],
            ['make' => 'Suzuki', 'title' => '2021 Suzuki S-Presso GL', 'price' => 428000],
            ['make' => 'Suzuki', 'title' => '2023 Suzuki Dzire GL', 'price' => 618000],
            ['make' => 'Suzuki', 'title' => '2020 Suzuki Carry Utility Van', 'price' => 498000],

            ['make' => 'Nissan', 'title' => '2022 Nissan Almera VL', 'price' => 678000],
            ['make' => 'Nissan', 'title' => '2021 Nissan Terra VE', 'price' => 1388000],
            ['make' => 'Nissan', 'title' => '2020 Nissan Navara VL 4x2', 'price' => 1088000],
            ['make' => 'Nissan', 'title' => '2019 Nissan Livina VL', 'price' => 628000],
            ['make' => 'Nissan', 'title' => '2023 Nissan Kicks e-Power VL', 'price' => 1098000],
            ['make' => 'Nissan', 'title' => '2018 Nissan Juke 1.6 CVT', 'price' => 548000],
            ['make' => 'Nissan', 'title' => '2017 Nissan X-Trail 2.0 4x2', 'price' => 798000],
            ['make' => 'Nissan', 'title' => '2021 Nissan Urvan NV350', 'price' => 998000],
            ['make' => 'Nissan', 'title' => '2016 Nissan Patrol Super Safari', 'price' => 1688000],
            ['make' => 'Nissan', 'title' => '2020 Nissan Sylphy 1.6 VL', 'price' => 698000],

            ['make' => 'Hyundai', 'title' => '2022 Hyundai Accent 1.4 GL', 'price' => 548000],
            ['make' => 'Hyundai', 'title' => '2021 Hyundai Tucson 2.0 GLS', 'price' => 1188000],
            ['make' => 'Hyundai', 'title' => '2020 Hyundai Santa Fe 2.2 CRDi', 'price' => 1588000],
            ['make' => 'Hyundai', 'title' => '2023 Hyundai Staria Premium', 'price' => 1988000],
            ['make' => 'Hyundai', 'title' => '2022 Hyundai Creta 1.5 GLS', 'price' => 898000],
            ['make' => 'Hyundai', 'title' => '2019 Hyundai Elantra 1.6 GL', 'price' => 628000],
            ['make' => 'Hyundai', 'title' => '2018 Hyundai Reina 1.4 GL', 'price' => 398000],
            ['make' => 'Hyundai', 'title' => '2021 Hyundai Palisade 2.2 GLS', 'price' => 2188000],
            ['make' => 'Hyundai', 'title' => '2020 Hyundai Kona 2.0 GLS', 'price' => 848000],
            ['make' => 'Hyundai', 'title' => '2017 Hyundai H-100 Shuttle', 'price' => 548000],

            ['make' => 'Honda', 'title' => '2022 Honda City 1.5 V', 'price' => 748000],
            ['make' => 'Honda', 'title' => '2021 Honda Civic 1.5 RS', 'price' => 1188000],
            ['make' => 'Honda', 'title' => '2020 Honda CR-V 1.6 S', 'price' => 1288000],
            ['make' => 'Honda', 'title' => '2019 Honda BR-V 1.5 S', 'price' => 698000],
            ['make' => 'Honda', 'title' => '2023 Honda HR-V 1.5 S', 'price' => 1098000],
            ['make' => 'Honda', 'title' => '2018 Honda Jazz 1.5 V', 'price' => 548000],
            ['make' => 'Honda', 'title' => '2021 Honda Brio 1.2 RS', 'price' => 548000],
            ['make' => 'Honda', 'title' => '2017 Honda Mobilio 1.5 RS', 'price' => 498000],
            ['make' => 'Honda', 'title' => '2020 Honda Accord 1.5 Turbo', 'price' => 1388000],
            ['make' => 'Honda', 'title' => '2019 Honda Odyssey EX-V', 'price' => 1588000],
        ];
    }

    private function buildDetailsAndFinancing(int $price, int $downPayment): string
    {
        $financed = max(0, $price - $downPayment);
        $lines = [
            'Down Payment: ₱'.number_format($downPayment),
            'Financed Amount: ₱'.number_format($financed),
            '',
            'Monthly options:',
        ];

        foreach ([1, 2, 3, 4, 5] as $years) {
            $months = $years * 12;
            $monthly = (int) round($financed / $months, -2);
            $monthly = max(8000, min(150000, $monthly));
            $lines[] = "  {$years} year(s): ₱".number_format($monthly).'/mo';
        }

        return implode("\n", $lines);
    }
}
