<?php

namespace App\Rules;

use App\Models\Accommodation;
use App\Models\RoomType;
use Illuminate\Contracts\Validation\Rule;

class ValidRoomAccommodation implements Rule
{
    protected $roomTypeId;

    public function __construct($roomTypeId)
    {
        $this->roomTypeId = $roomTypeId;
    }

    public function passes($attribute, $value)
    {
        $roomType = RoomType::find($this->roomTypeId);
        $accommodation = Accommodation::find($value);

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

    public function message()
    {
        return 'La combinación de tipo de habitación y acomodación no es válida.';
    }
}