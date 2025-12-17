<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AplicarAjusteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Asumiendo que solo roles administrativos pueden indexar
        return true; 
    }

    /**
     * Define las reglas de validación para la aplicación masiva del ajuste.
     */
    public function rules(): array
    {
        return [
            // Validación para la aplicación masiva
            'contratos_ids' => 'required|array|min:1',
            'contratos_ids.*' => 'required|integer|exists:contratos,id',
            
            // Validación de los datos de ajuste
            'tipoAjuste' => ['required', Rule::in(['porcentaje', 'monto_fijo'])],
            'valorAjuste' => 'required|numeric|min:0.01', 
            
            'fechaAplicacion' => 'required|date_format:Y-m-d', 
        ];
    }

    public function messages(): array
    {
        return [
            'contratos_ids.required' => 'Debe seleccionar al menos un contrato para indexar.',
            'contratos_ids.min' => 'Debe seleccionar al menos un contrato para indexar.',
            'contratos_ids.*.exists' => 'Uno o más IDs de contrato seleccionados no son válidos.',
            'valorAjuste.min' => 'El valor de ajuste debe ser positivo.',
            'fechaAplicacion.required' => 'La fecha de aplicación es obligatoria.',
        ];
    }
}