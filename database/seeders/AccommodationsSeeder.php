<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use Illuminate\Database\Seeder;

class AccommodationsSeeder extends Seeder
{
    public function run()
    {
        Accommodation::create(['name' => 'Sencilla']);
        Accommodation::create(['name' => 'Doble']);
        Accommodation::create(['name' => 'Triple']);
        Accommodation::create(['name' => 'Cuádruple']);
    }
}
