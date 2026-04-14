<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
        'slug_kategori',
        'id_kelompok',
    ];

    public function kelompok(){
        return $this->hasOne(Kelompok::class, 'id_kelompok', 'id_kelompok');
    }

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'id_kategori', 'id_kategori');
    }
}
