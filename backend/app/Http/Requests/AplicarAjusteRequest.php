<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AplicarAjusteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Asumiendo que solo roles administrativos pueden indexar
        return true; 
    }

    /**
     * Define las reglas de validación para el ajuste.
     */
    public function rules(): array
    {
        return [
            'contrato_id' => 'required|exists:contratos,id',
            'porcentaje' => 'required|numeric|min:0.01|max:100', 
            'fecha_aplicacion' => 'required|date_format:Y-m-d', 
            'motivo' => 'required|string|max:150', 
        ];
    }

    public function messages(): array
    {
        return [
            'porcentaje.min' => 'El porcentaje de aumento debe ser mayor a cero.',
            'fecha_aplicacion.required' => 'La fecha de aplicación es obligatoria.',
        ];
    }
}