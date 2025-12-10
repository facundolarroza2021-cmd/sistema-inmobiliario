<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use Illuminate\Http\Request;

class CuotaController extends Controller
{
    public function index()
    {
        return Cuota::with(['contrato.inquilino', 'contrato.propiedad', 'pagos'])
                    ->orderBy('id', 'desc')
                    ->get();
    }
}