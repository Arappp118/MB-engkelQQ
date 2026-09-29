<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category', 'name', 'description', 'price',
        'unit', 'stock', 'is_sparepart', 'is_active',
    ];

    protected $casts = [
        'price'       => 'float',
        'stock'       => 'integer',
        'is_sparepart'=> 'boolean',
        'is_active'   => 'boolean',
    ];

    public function serviceOrderItems()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForSpecialization($query, string $specialization)
    {
        $categoryMap = [
            'mekanik_2_tak'       => ['2_tak', 'umum', 'sparepart'],
            'mekanik_4_tak'       => ['4_tak', 'umum', 'sparepart'],
            'mekanik_kelistrikan' => ['kelistrikan', 'umum', 'sparepart'],
        ];
        $categories = $categoryMap[$specialization] ?? ['umum'];
        return $query->whereIn('category', $categories);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            '2_tak'       => 'Mesin 2 Tak',
            '4_tak'       => 'Mesin 4 Tak',
            'kelistrikan' => 'Kelistrikan',
            'umum'        => 'Umum',
            'sparepart'   => 'Sparepart',
            default       => $this->category,
        };
    }
}
