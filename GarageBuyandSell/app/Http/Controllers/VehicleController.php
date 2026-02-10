<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * List vehicles (public, available only).
     */
    public function index(Request $request)
    {
        $query = Vehicle::with('images')->where('status', 'available')->orderByDesc('created_at');

        if ($request->filled('make')) {
            $query->where('make', 'like', '%' . $request->make . '%');
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        $perPage = max(1, min(50, (int) $request->get('per_page', 12)));
        return $query->paginate($perPage);
    }

    /**
     * Show a single vehicle (public).
     */
    public function show(string $id)
    {
        $vehicle = Vehicle::with('images')->where('status', 'available')->findOrFail($id);
        $vehicle->increment('views_count');
        $vehicle->load('images');
        return $vehicle;
    }
}
