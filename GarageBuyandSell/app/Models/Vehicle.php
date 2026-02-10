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
        'year',
        'make',
        'model',
        'vehicle_type',
        'category',
        'transmission',
        'fuel_type',
        'color',
        'door_count',
        'seat_capacity',
        'mileage',
        'grade',
        'price',
        'is_negotiable',
        'down_payment',
        'dp_all_in',
        'financing_options',
        'views_count',
    ];

    protected $casts = [
        'is_negotiable' => 'boolean',
        'dp_all_in' => 'boolean',
        'financing_options' => 'array',
    ];

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }
}

