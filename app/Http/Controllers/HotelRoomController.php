<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\RoomType;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HotelRoomController extends Controller
{
    private function validateAccommodation($roomTypeId, $accommodationId)
    {
        $roomType = RoomType::find($roomTypeId);
        $accommodation = Accommodation::find($accommodationId);

        if (!$roomType || !$accommodation) {
            return false;
        }

        switch ($roomType->name) {
            case 'Estándar':
                return in_array($accommodation->name, ['Sencilla', 'Doble']);
            case 'Junior':
                return in_array($accommodation->name, ['Triple', 'Cuádruple']);
            case 'Suite':
                return in_array($accommodation->name, ['Sencilla', 'Doble', 'Triple']);
            default:
                return false;
        }
    }

    public function index($hotelId)
    {
        $hotel = Hotel::find($hotelId);

        if (!$hotel) {
            return response()->json(['message' => 'Hotel no encontrado'], 404);
        }

        return response()->json($hotel->rooms()->with('roomType', 'accommodation')->get());
    }

    public function store(Request $request, $hotelId)
    {
        $hotel = Hotel::find($hotelId);

        if (!$hotel) {
            return response()->json(['message' => 'Hotel no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'accommodation_id' => 'required|exists:accommodations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Validar combinación tipo-acomodación
        if (!$this->validateAccommodation($request->room_type_id, $request->accommodation_id)) {
            return response()->json(['message' => 'Combinación de tipo de habitación y acomodación no válida'], 400);
        }

        // Validar que no exista ya esta combinación para el hotel
        $existingRoom = HotelRoom::where('hotel_id', $hotelId)
            ->where('room_type_id', $request->room_type_id)
            ->where('accommodation_id', $request->accommodation_id)
            ->first();

        if ($existingRoom) {
            return response()->json(['message' => 'Esta combinación de tipo y acomodación ya existe para este hotel'], 400);
        }

        // Validar que no se exceda el máximo de habitaciones
        $totalRooms = $hotel->totalRooms() + $request->quantity;
        if ($totalRooms > $hotel->max_rooms) {
            return response()->json([
                'message' => 'La cantidad de habitaciones excede el máximo permitido para este hotel',
                'max_rooms' => $hotel->max_rooms,
                'current_total' => $hotel->totalRooms(),
                'attempting_to_add' => $request->quantity
            ], 400);
        }

        $room = new HotelRoom($validator->validated());
        $room->hotel_id = $hotelId;
        $room->save();

        return response()->json($room->load('roomType', 'accommodation'), 201);
    }

    public function update(Request $request, $hotelId, $roomId)
    {
        $hotel = Hotel::find($hotelId);
        $room = HotelRoom::where('hotel_id', $hotelId)->where('id', $roomId)->first();

        if (!$hotel || !$room) {
            return response()->json(['message' => 'Hotel o habitación no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'sometimes|required|exists:room_types,id',
            'accommodation_id' => 'sometimes|required|exists:accommodations,id',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Validar combinación tipo-acomodación si se están actualizando
        if ($request->has('room_type_id') || $request->has('accommodation_id')) {
            $roomTypeId = $request->room_type_id ?? $room->room_type_id;
            $accommodationId = $request->accommodation_id ?? $room->accommodation_id;

            if (!$this->validateAccommodation($roomTypeId, $accommodationId)) {
                return response()->json(['message' => 'Combinación de tipo de habitación y acomodación no válida'], 400);
            }
        }

        // Validar que no exista ya esta combinación para el hotel (excepto para este mismo registro)
        if ($request->has('room_type_id') || $request->has('accommodation_id')) {
            $roomTypeId = $request->room_type_id ?? $room->room_type_id;
            $accommodationId = $request->accommodation_id ?? $room->accommodation_id;

            $existingRoom = HotelRoom::where('hotel_id', $hotelId)
                ->where('room_type_id', $roomTypeId)
                ->where('accommodation_id', $accommodationId)
                ->where('id', '!=', $roomId)
                ->first();

            if ($existingRoom) {
                return response()->json(['message' => 'Esta combinación de tipo y acomodación ya existe para este hotel'], 400);
            }
        }

        // Validar que no se exceda el máximo de habitaciones si se está actualizando la cantidad
        if ($request->has('quantity')) {
            $totalRooms = $hotel->totalRooms() - $room->quantity + $request->quantity;
            if ($totalRooms > $hotel->max_rooms) {
                return response()->json([
                    'message' => 'La cantidad de habitaciones excede el máximo permitido para este hotel',
                    'max_rooms' => $hotel->max_rooms,
                    'current_total' => $hotel->totalRooms(),
                    'attempting_to_set' => $request->quantity
                ], 400);
            }
        }

        $room->update($validator->validated());

        return response()->json($room->load('roomType', 'accommodation'));
    }

    public function destroy($hotelId, $roomId)
    {
        $room = HotelRoom::where('hotel_id', $hotelId)->where('id', $roomId)->first();

        if (!$room) {
            return response()->json(['message' => 'Habitación no encontrada'], 404);
        }

        $room->delete();

        return response()->json(['message' => 'Habitación eliminada correctamente']);
    }
}