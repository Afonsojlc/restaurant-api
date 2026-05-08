<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // GET /api/reservations (Admin vê todas, Cliente vê apenas as suas)
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Reservation::with(['user', 'dishes']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('reserved_at')->paginate(10));
    }

    // GET /api/reservations/{id}
    public function show(Request $request, Reservation $reservation)
    {
        $this->checkAccess($request->user(), $reservation);
        return response()->json($reservation->load(['user', 'dishes']));
    }

    // POST /api/reservations - Cliente cria reserva
    public function store(Request $request)
    {
        $data = $request->validate([
            'reserved_at' => 'required|date|after:now',
            'guests' => 'required|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
            'dishes' => 'nullable|array',
            'dishes.*.id' => 'exists:dishes,id',
            'dishes.*.quantity' => 'integer|min:1',
        ]);

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'reserved_at' => $data['reserved_at'],
            'guests' => $data['guests'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        // Associa pratos com quantidade (tabela pivot)
        if (!empty($data['dishes'])) {
            $pivot = collect($data['dishes'])->mapWithKeys(fn($d) => [
                $d['id'] => ['quantity' => $d['quantity'] ?? 1]
            ]);
            $reservation->dishes()->sync($pivot);
        }

        return response()->json($reservation->load(['user', 'dishes']), 201);
    }

    // PUT /api/reservations/{id} - Cliente edita (só se pending)
    public function update(Request $request, Reservation $reservation)
    {
        $this->checkAccess($request->user(), $reservation);

        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'So e possivel editar reservas pendentes.'], 422);
        }

        $data = $request->validate([
            'reserved_at' => 'sometimes|date|after:now',
            'guests' => 'sometimes|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
            'dishes' => 'nullable|array',
            'dishes.*.id' => 'exists:dishes,id',
            'dishes.*.quantity' => 'integer|min:1',
        ]);

        $reservation->update(collect($data)->except('dishes')->toArray());

        if (isset($data['dishes'])) {
            $pivot = collect($data['dishes'])->mapWithKeys(fn($d) => [
                $d['id'] => ['quantity' => $d['quantity'] ?? 1]
            ]);
            $reservation->dishes()->sync($pivot);
        }

        return response()->json($reservation->load(['user', 'dishes']));
    }

    // PATCH /api/reservations/{id}/cancel - Cliente cancela a sua reserva
    public function cancel(Request $request, Reservation $reservation)
    {
        $this->checkAccess($request->user(), $reservation);

        if ($reservation->status === 'cancelled') {
            return response()->json(['message' => 'Reserva ja cancelada.'], 422);
        }

        $reservation->update(['status' => 'cancelled']);
        
        return response()->json(['message' => 'Reserva cancelada.', 'reservation' => $reservation]);
    }

    // PATCH /api/reservations/{id}/status - Admin altera estado
    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled',
        ]);

        $reservation->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Estado actualizado.',
            'reservation' => $reservation->load(['user', 'dishes']),
        ]);
    }

    // DELETE /api/reservations/{id} - Admin apaga
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(['message' => 'Reserva eliminada.']);
    }

    // Método privado: verifica se o utilizador pode aceder a esta reserva
    private function checkAccess($user, Reservation $reservation): void
    {
        if (!$user->isAdmin() && $reservation->user_id !== $user->id) {
            abort(403, 'Nao tens permissao para aceder a esta reserva.');
        }
    }
}