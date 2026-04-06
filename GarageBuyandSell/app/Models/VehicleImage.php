<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class VehicleImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'image_path',
        'position',
        'is_primary',
    ];

    protected $appends = ['image_url'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * URL for display: signed S3 URL when the object exists on the s3 disk, else public storage URL.
     * Use in Blade as {{ $image->image_url }} (same as Storage::disk('s3')->temporaryUrl(...) for private buckets).
     */
    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }
        if ($this->usesS3ForUrls() && Storage::disk('s3')->exists($this->image_path)) {
            return Storage::disk('s3')->temporaryUrl($this->image_path, now()->addMinutes(60));
        }

        return asset('storage/' . $this->image_path);
    }

    private function usesS3ForUrls(): bool
    {
        return filled(config('filesystems.disks.s3.bucket'))
            && filled(config('filesystems.disks.s3.key'))
            && filled(config('filesystems.disks.s3.secret'));
    }
}
