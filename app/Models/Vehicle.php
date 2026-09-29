<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'nomor_polisi', 'merk', 'model', 'tahun',
        'tipe_mesin', 'transmisi', 'warna', 'nomor_rangka', 'catatan',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->merk} {$this->model} ({$this->tahun}) - {$this->nomor_polisi}";
    }

    public function getSpecializationNeededAttribute(): string
    {
        return match($this->tipe_mesin) {
            '2_tak'   => 'mekanik_2_tak',
            '4_tak'   => 'mekanik_4_tak',
            'listrik' => 'mekanik_kelistrikan',
            default   => 'mekanik_4_tak',
        };
    }
}
