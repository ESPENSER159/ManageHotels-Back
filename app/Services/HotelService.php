<?php

namespace App\Services;

use App\DTOs\HotelHabitacionDTO;
use App\Models\Hotel;
use App\Models\Habitacion;
use Illuminate\Support\Collection;

class HotelService
{
    public function getAllHotels(): Collection
    {
        return Hotel::with('ciudad', 'habitaciones.tipoHabitacion', 'habitaciones.acomodacion')->get();
    }

    public function createHotel(array $data): Hotel
    {
        return Hotel::create($data);
    }

    public function getHotelWithHabitaciones(Hotel $hotel): HotelHabitacionDTO
    {
        $hotel->load('ciudad', 'habitaciones.tipoHabitacion', 'habitaciones.acomodacion');
        
        return new HotelHabitacionDTO(
            $hotel->toArray(),
            $hotel->habitaciones->toArray(),
            $hotel->numero_habitaciones,
            $hotel->total_habitaciones
        );
    }

    public function updateHotel(Hotel $hotel, array $data): Hotel
    {
        $hotel->update($data);
        return $hotel;
    }

    public function deleteHotel(Hotel $hotel): void
    {
        $hotel->habitaciones()->delete();
        $hotel->delete();
    }
}