<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypesSeeder extends Seeder
{
    public function run()
    {
        $serviceTypes = [
            'Barber',
            'Makeup Artist', 
            'Nail Technician',
            'Tattoo Artist'
        ];

        foreach ($serviceTypes as $type) {
            ServiceType::create(['name' => $type]);
        }
    }
}