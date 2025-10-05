<?php

namespace Database\Seeders;

use App\Models\Interest;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class InterestsSeeder extends Seeder
{
    public function run()
    {
        
        $serviceTypes = ServiceType::all();
        
        if ($serviceTypes->isEmpty()) {
            $this->command->error('No service types found. Please run ServiceTypesSeeder first.');
            return;
        }

        $interests = [
            [
                'name' => 'Barber',
                'image_link' => 'https://res.cloudinary.com/dantj20mr/image/upload/v1756761195/WhatsApp_Image_2025-09-01_at_21.01.20_1a960684_rx0uau.jpg',
            ],
            [
                'name' => 'Makeup Artist',
                'image_link' => 'https://res.cloudinary.com/dantj20mr/image/upload/v1756761290/WhatsApp_Image_2025-09-01_at_21.01.20_25c05b94_bzsmjg.jpg',
            ],
            [
                'name' => 'Nail Technician',
                'image_link' => 'https://res.cloudinary.com/dantj20mr/image/upload/v1756761342/WhatsApp_Image_2025-09-01_at_21.15.30_894756aa_anjnuu.jpg',
            ],
            [
                'name' => 'Tattoo Artist',
                'image_link' => 'https://res.cloudinary.com/dantj20mr/image/upload/v1756761394/WhatsApp_Image_2025-09-01_at_21.01.20_c568e91e_ei2eip.jpg',
            ],
        ];

        foreach ($interests as $interestData) {
            $serviceType = $serviceTypes->where('name', $interestData['name'])->first();
            
            if (!$serviceType) {
                $this->command->warn("Service type '{$interestData['name']}' not found. Skipping interest.");
                continue;
            }

            Interest::create([
                'name' => $interestData['name'],
                'image_link' => $interestData['image_link'],
                'service_type_id' => $serviceType->id,
            ]);

            $this->command->info("Created interest: {$interestData['name']}");
        }
    }
}