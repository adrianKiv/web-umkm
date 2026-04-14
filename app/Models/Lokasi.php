<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasi';
    protected $primaryKey = 'id_lokasi';

    protected $fillable = [
        'latitude',
        'longitude',
    ];

    public function umkm(){
        return $this->belongsTo(Umkm::class, 'id_lokasi', 'id_lokasi');
    }
}
