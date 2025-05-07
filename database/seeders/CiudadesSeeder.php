<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ciudad;

class CiudadesSeeder extends Seeder
{
    public function run()
    {
        $ciudades = [
            'Cartagena',
            'Santa Marta',
            'San Andrés',
            'Bogotá',
            'Medellín'
        ];

        foreach ($ciudades as $ciudad) {
            Ciudad::create(['nombre' => $ciudad]);
        }
    }
}