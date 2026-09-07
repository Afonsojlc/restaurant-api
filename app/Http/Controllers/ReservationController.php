<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // GET /api/reservations (Admins see all reservations; Customers see only their own)
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Reservation::with(['user', 'dishes']);

        // Scope to current customer if not an administrator
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Optional filter by reservation status (pending, confirmed, cancelled)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('reserved_at')->paginate(10));
    }

    // GET /api/reservations/{id} — Retrieve reservation details
    public function show(Request $request, Reservation $reservation)
    {
        $this->checkAccess($request->user(), $reservation);
        return response()->json($reservation->load(['user', 'dishes']));
    }

    // POST /api/reservations — Customer creates a new table reservation
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

        // Attach pre-ordered dishes with quantities via pivot table (reservation_dish)
        if (!empty($data['dishes'])) {
            $pivot = collect($data['dishes'])->mapWithKeys(fn($d) => [
                $d['id'] => ['quantity' => $d['quantity'] ?? 1]
            ]);
            $reservation->dishes()->sync($pivot);
        }

        return response()->json($reservation->load(['user', 'dishes']), 201);
    }

    // PUT /api/reservations/{id} — Customer modifies reservation (Only allowed if 'pending')
    public function update(Request $request, Reservation $reservation)
    {
        $this->checkAccess($request->user(), $reservation);

        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'Only pending reservations can be edited.'], 422);
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

    // PATCH /api/reservations/{id}/cancel — Customer cancels their reservation
    public function cancel(Request $request, Reservation $reservation)
    {
        $this->checkAccess($request->user(), $reservation);

        if ($reservation->status === 'cancelled') {
            return response()->json(['message' => 'Reservation is already cancelled.'], 422);
        }

        $reservation->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Reservation cancelled successfully.', 'reservation' => $reservation]);
    }

    // PATCH /api/reservations/{id}/status — Restaurant Admin confirms or rejects reservation
    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled',
        ]);

        $reservation->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Reservation status updated successfully.',
            'reservation' => $reservation->load(['user', 'dishes']),
        ]);
    }

    // DELETE /api/reservations/{id} — Admin removes reservation record
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(['message' => 'Reservation removed successfully.']);
    }

    // Guard helper: Enforce that users can only access their own reservations unless they are Admin
    private function checkAccess($user, Reservation $reservation): void
    {
        if (!$user->isAdmin() && $reservation->user_id !== $user->id) {
            abort(403, 'Unauthorized: You do not have permission to access this reservation.');
        }
    }
}
