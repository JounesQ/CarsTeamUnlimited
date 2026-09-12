<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * List vehicles (public: available, reserved, coming).
     */
    public function index(Request $request)
    {
        $statuses = ['available', 'reserved', 'coming'];
        if ($request->filled('status') && in_array($request->status, $statuses, true)) {
            $statuses = [$request->status];
        }
        $query = Vehicle::with('images')
            ->whereIn('status', $statuses)
            ->orderByDesc('created_at');

        if ($request->filled('make')) {
            $query->where('make', 'like', '%' . $request->make . '%');
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->has('can_test_drive') && $request->input('can_test_drive') !== '') {
            $query->where(
                'can_test_drive',
                filter_var($request->input('can_test_drive'), FILTER_VALIDATE_BOOLEAN)
            );
        }

        $perPage = max(1, min(500, (int) $request->get('per_page', 500)));
        return $query->paginate($perPage);
    }

    /**
     * Show a single vehicle (public).
     * Increments views_count when viewed from CustomerGarage.
     */
    public function show(string $id)
    {
        $vehicle = Vehicle::with('images')
            ->whereIn('status', ['available', 'reserved', 'coming'])
            ->findOrFail($id);
        $vehicle->increment('views_count');
        $vehicle->load('images');
        return $vehicle;
    }
}
