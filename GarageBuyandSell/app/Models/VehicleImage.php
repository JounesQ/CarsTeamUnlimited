<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'image_path',
        'position',
        'is_primary',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Full URL for display (e.g. in server-side views). API returns raw path; frontend builds URL. */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
