<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHabitacionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tipo_habitacion_id' => [
                'required',
                'exists:tipos_habitacion,id',
            ],
            'acomodacion_id' => [
                'required',
                'exists:acomodaciones,id',
                function ($attribute, $value, $fail) {
                    $tipoHabitacionId = $this->input('tipo_habitacion_id');
                    
                    // Validar según las reglas de negocio
                    if ($tipoHabitacionId == 1 && !in_array($value, [1, 2])) { // Estándar: Sencilla(1) o Doble(2)
                        $fail('Para habitación Estándar solo se permiten acomodaciones Sencilla o Doble.');
                    }
                    
                    if ($tipoHabitacionId == 2 && !in_array($value, [3, 4])) { // Junior: Triple(3) o Cuádruple(4)
                        $fail('Para habitación Junior solo se permiten acomodaciones Triple o Cuádruple.');
                    }
                    
                    if ($tipoHabitacionId == 3 && !in_array($value, [1, 2, 3])) { // Suite: Sencilla(1), Doble(2) o Triple(3)
                        $fail('Para habitación Suite solo se permiten acomodaciones Sencilla, Doble o Triple.');
                    }
                },
            ],
            'cantidad' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $hotel = $this->route('hotel');
                    $totalActual = $hotel->total_habitaciones;
                    $nuevoTotal = $totalActual + $value;
                    
                    if ($nuevoTotal > $hotel->numero_habitaciones) {
                        $fail("La cantidad excede el número máximo de habitaciones para este hotel. Máximo disponible: " . ($hotel->numero_habitaciones - $totalActual));
                    }
                },
            ],
        ];
    }
}