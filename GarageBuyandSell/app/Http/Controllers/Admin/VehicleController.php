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
        if ($request->has('can_test_drive') && $request->input('can_test_drive') !== '') {
            $query->where(
                'can_test_drive',
                filter_var($request->input('can_test_drive'), FILTER_VALIDATE_BOOLEAN)
            );
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
        $vehicle->title = $validated['title'];
        $vehicle->slug = Str::slug($validated['title']) . '-' . substr($vehicle->id, 0, 8);
        $vehicle->status = $validated['status'] ?? 'available';
        $vehicle->make = $validated['make'];
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

        $totalValueAll = 0;
        $totalValueAvailable = 0;
        $totalValueSold = 0;
        $totalValueReserved = 0;
        $totalValueComing = 0;
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

        $vehicle->title = $validated['title'];
        $vehicle->slug = $validated['slug'] ?? (Str::slug($validated['title']) . '-' . substr($vehicle->id, 0, 8));
        $vehicle->status = $validated['status'] ?? $vehicle->status;
        $vehicle->make = $validated['make'];
        $this->fillVehicle($vehicle, $validated);
        $vehicle->save();

        $this->syncImages($vehicle, $request->input('images', []));

        $vehicle->load('images');
        return $vehicle;
    }

    /**
     * Upload an image file; returns storage path for use in vehicle images (S3 key or public disk path).
     * Uses S3 when bucket and credentials are configured, otherwise public disk.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ], [
            'image.required' => 'No image file was uploaded.',
            'image.image' => 'File must be an image (JPEG, PNG, GIF, WebP).',
            'image.mimes' => 'Image must be JPEG, PNG, GIF, or WebP.',
            'image.max' => 'Image must be under 10 MB. If uploads fail, increase upload_max_filesize in php.ini.',
        ]);
        $file = $request->file('image');
        $useS3 = filled(config('filesystems.disks.s3.bucket'))
            && filled(config('filesystems.disks.s3.key'))
            && filled(config('filesystems.disks.s3.secret'));

        if ($useS3) {
            try {
                $path = $file->store('vehicles', 's3');
                $displayUrl = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));

                return response()->json([
                    'path' => $path,
                    'display_url' => $displayUrl,
                ]);
            } catch (\Throwable $e) {
                if (str_contains($e->getMessage(), 'SSL certificate') || str_contains($e->getMessage(), 'cURL error 60')) {
                    $path = $file->store('vehicles', 'public');

                    return response()->json(['path' => $path]);
                }
                throw $e;
            }
        }

        $path = $file->store('vehicles', 'public');

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
            'make' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'can_test_drive' => 'sometimes|boolean',
            'details_and_financing' => 'nullable|string|max:65535',
            'images' => 'nullable|array',
            'images.*.image_path' => 'required_with:images|string|max:255',
            'images.*.position' => 'nullable|integer|min:1|max:10',
            'images.*.is_primary' => 'nullable|boolean',
        ]);
    }

    private function fillVehicle(Vehicle $vehicle, array $data): void
    {
        $vehicle->price = (int) ($data['price'] ?? 0);
        $vehicle->can_test_drive = array_key_exists('can_test_drive', $data)
            ? (bool) $data['can_test_drive']
            : true;
        $vehicle->details_and_financing = $data['details_and_financing'] ?? null;
    }

    private function syncImages(Vehicle $vehicle, array $images): void
    {
        $vehicle->images()->delete();

        foreach (array_values($images) as $i => $img) {
            $position = (int) ($img['position'] ?? $i + 1);
            $position = max(1, min(50, $position));
            $path = $img['image_path'] ?? '';
            if (str_starts_with($path, 'http')) {
                // Keep full URL for legacy/external storage
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
