<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
    /**
     * Lista los tickets aplicando filtros dinámicos.
     */
    public function listarTickets(array $filtros)
    {
        $query = Ticket::with(['propiedad', 'inquilino']);

        if (isset($filtros['estado']) && $filtros['estado'] !== 'TODOS') {
            $query->where('estado', $filtros['estado']);
        }
        if (isset($filtros['prioridad'])) {
            $query->where('prioridad', $filtros['prioridad']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function crearTicket(array $datos): Ticket
    {
        return Ticket::create($datos);
    }

    public function actualizarTicket(int $id, array $datos): Ticket
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'estado' => $datos['estado'] ?? $ticket->estado,
            'prioridad' => $datos['prioridad'] ?? $ticket->prioridad,
            'descripcion' => $datos['descripcion'] ?? $ticket->descripcion,
        ]);

        return $ticket;
    }

    public function eliminarTicket(int $id): void
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();
    }
}
