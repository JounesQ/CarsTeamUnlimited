<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * List all vehicles (admin).
     */
    public function index(Request $request)
    {
        $query = Vehicle::with('images')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('make')) {
            $query->where('make', 'like', '%' . $request->make . '%');
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));
        return $query->paginate($perPage);
    }

    /**
     * Store a new vehicle.
     */
    public function store(Request $request)
    {
        $validated = $this->validateVehicle($request);

        $vehicle = new Vehicle();
        $vehicle->id = (string) Str::uuid();
        $vehicle->slug = Str::slug($validated['title']) . '-' . substr($vehicle->id, 0, 8);
        $this->fillVehicle($vehicle, $validated);
        $vehicle->save();

        $this->syncImages($vehicle, $request->input('images', []));

        $vehicle->load('images');
        return response()->json($vehicle, 201);
    }

    /**
     * Get vehicle statistics for admin dashboard.
     */
    public function stats()
    {
        $total = Vehicle::count();
        $byStatus = Vehicle::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $available = (int) ($byStatus['available'] ?? 0);
        $sold = (int) ($byStatus['sold'] ?? 0);
        $reserved = (int) ($byStatus['reserved'] ?? 0);
        $coming = (int) ($byStatus['coming'] ?? 0);

        $totalValueAll = (float) Vehicle::sum('price');
        $totalValueAvailable = (float) Vehicle::where('status', 'available')->sum('price');
        $totalValueSold = (float) Vehicle::where('status', 'sold')->sum('price');
        $totalValueReserved = (float) Vehicle::where('status', 'reserved')->sum('price');
        $totalValueComing = (float) Vehicle::where('status', 'coming')->sum('price');
        $byMake = Vehicle::selectRaw('make, count(*) as count')
            ->groupBy('make')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['make' => $r->make, 'count' => (int) $r->count])
            ->toArray();

        return response()->json([
            'total' => $total,
            'available' => $available,
            'sold' => $sold,
            'reserved' => $reserved,
            'coming' => $coming,
            'total_value' => $totalValueAvailable,
            'total_value_all' => $totalValueAll,
            'total_value_sold' => $totalValueSold,
            'total_value_reserved' => $totalValueReserved,
            'total_value_coming' => $totalValueComing,
            'by_make' => $byMake,
        ]);
    }

    /**
     * Show a single vehicle (admin).
     */
    public function show(string $id)
    {
        return Vehicle::with('images')->findOrFail($id);
    }

    /**
     * Update a vehicle.
     */
    public function update(Request $request, string $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $validated = $this->validateVehicle($request, $vehicle);

        $vehicle->slug = $validated['slug'] ?? (Str::slug($validated['title']) . '-' . substr($vehicle->id, 0, 8));
        $this->fillVehicle($vehicle, $validated);
        $vehicle->save();

        $this->syncImages($vehicle, $request->input('images', []));

        $vehicle->load('images');
        return $vehicle;
    }

    /**
     * Upload an image file; returns storage path or full URL for use in vehicle images.
     * Uses Supabase when configured, otherwise public disk.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        $file = $request->file('image');
        $disk = config('filesystems.disks.supabase.key') ? 'supabase' : 'public';
        $path = $file->store('vehicles', $disk);

        if ($disk === 'supabase') {
            $path = Storage::disk('supabase')->getAdapter()->getPublicUrl($path);
        }

        return response()->json(['path' => $path]);
    }

    /**
     * Delete a vehicle.
     */
    public function destroy(string $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        return response()->json(null, 204);
    }

    private function validateVehicle(Request $request, ?Vehicle $vehicle = null): array
    {
        $slugRule = Rule::unique('vehicles', 'slug');
        if ($vehicle) {
            $slugRule->ignore($vehicle->id);
        }

        return $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200|' . $slugRule,
            'status' => 'nullable|string|in:available,sold,reserved,coming',
            'year' => 'required|integer|min:1900|max:2100',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'vehicle_type' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:30',
            'fuel_type' => 'nullable|string|max:30',
            'color' => 'nullable|string|max:50',
            'door_count' => 'nullable|integer|min:0',
            'seat_capacity' => 'nullable|integer|min:0',
            'mileage' => 'nullable|integer|min:0',
            'grade' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'is_negotiable' => 'nullable|boolean',
            'down_payment' => 'nullable|numeric|min:0',
            'dp_all_in' => 'nullable|boolean',
            'financing_options' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*.image_path' => 'required_with:images|string|max:255',
            'images.*.position' => 'nullable|integer|min:1|max:10',
            'images.*.is_primary' => 'nullable|boolean',
        ]);
    }

    private function fillVehicle(Vehicle $vehicle, array $data): void
    {
        $vehicle->title = $data['title'];
        $vehicle->status = $data['status'] ?? 'available';
        $vehicle->year = (int) $data['year'];
        $vehicle->make = $data['make'];
        $vehicle->model = $data['model'];
        $vehicle->vehicle_type = $data['vehicle_type'] ?? 'car';
        $vehicle->category = $data['category'] ?? null;
        $vehicle->transmission = $data['transmission'] ?? 'automatic';
        $vehicle->fuel_type = $data['fuel_type'] ?? null;
        $vehicle->color = $data['color'] ?? null;
        $vehicle->door_count = isset($data['door_count']) ? (int) $data['door_count'] : null;
        $vehicle->seat_capacity = isset($data['seat_capacity']) ? (int) $data['seat_capacity'] : null;
        $vehicle->mileage = isset($data['mileage']) ? (int) $data['mileage'] : null;
        $vehicle->grade = $data['grade'] ?? null;
        $vehicle->price = (float) $data['price'];
        $vehicle->is_negotiable = $data['is_negotiable'] ?? true;
        $vehicle->down_payment = isset($data['down_payment']) ? (float) $data['down_payment'] : null;
        $vehicle->dp_all_in = $data['dp_all_in'] ?? true;
        $vehicle->financing_options = $data['financing_options'] ?? null;
    }

    private function syncImages(Vehicle $vehicle, array $images): void
    {
        $vehicle->images()->delete();

        foreach (array_values($images) as $i => $img) {
            $position = (int) ($img['position'] ?? $i + 1);
            $position = max(1, min(50, $position));
            $path = $img['image_path'] ?? '';
            // If frontend sends full URL (Supabase or external), store as-is
            if (str_starts_with($path, 'http')) {
                // Keep full URL for Supabase/external storage
            } elseif (str_contains($path, '/storage/')) {
                // Laravel public disk: extract path after /storage/
                $path = substr($path, strpos($path, '/storage/') + strlen('/storage/'));
            }
            if ($path === '') {
                continue;
            }
            $vehicle->images()->create([
                'vehicle_id' => $vehicle->id,
                'image_path' => $path,
                'position' => $position,
                'is_primary' => (bool) ($img['is_primary'] ?? ($i === 0)),
            ]);
        }
    }
}
