<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        return response()->json($this->ticketService->listarTickets($request->all()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'titulo' => 'required|string|max:255',
            'prioridad' => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
            'descripcion' => 'nullable|string',
        ]);

        $ticket = $this->ticketService->crearTicket($validated);

        return response()->json($ticket, 201);
    }

    public function update(Request $request, $id)
    {
        $ticket = $this->ticketService->actualizarTicket($id, $request->all());

        return response()->json($ticket);
    }

    public function destroy($id)
    {
        $this->ticketService->eliminarTicket($id);

        return response()->json(['message' => 'Ticket eliminado']);
    }
}
