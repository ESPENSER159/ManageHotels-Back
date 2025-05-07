<?php

namespace Database\Seeders;

use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypesSeeder extends Seeder
{
    public function run()
    {
        $roomTypes = [
            ['name' => 'Estándar'],
            ['name' => 'Junior'],
            ['name' => 'Suite']
        ];
        
        foreach ($roomTypes as $type) {
            \App\Models\RoomType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
