<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'slug',
        'status',
        'make',
        'price',
        'details_and_financing',
        'views_count',
    ];

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }
}

