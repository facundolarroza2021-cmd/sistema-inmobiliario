<?php

namespace App\Http\Controllers;

use App\Services\CuotaService;
use Illuminate\Http\Request;

class CuotaController extends Controller
{
    protected $cuotaService;

    public function __construct(CuotaService $cuotaService)
    {
        $this->cuotaService = $cuotaService;
    }

    public function index()
    {
        return response()->json($this->cuotaService->listarCuotas());
    }
}