<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Umkm extends Model
{
    use HasFactory;

    protected $table = 'umkm';
    protected $primaryKey = 'id_umkm';

    protected $fillable = [
        'nama_umkm',
        'slug_umkm',
        'jam_buka',
        'no_telfon',
        'alamat_lengkap',
        'deskripsi',
        'foto_umkm',
        'id_lokasi',
        'id_kategori',
        'osm_id',
        'source',
        'total_klik'
    ];

    public function lokasi(){
        return $this->hasOne(Lokasi::class, 'id_lokasi', 'id_lokasi');
    }

    public function kategori(){
        return $this->hasOne(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function rating(){
        return $this->hasMany(Rating::class, 'id_umkm', 'id_umkm');
    }

    public function menu(){
        return $this->hasMany(Menu::class, 'id_umkm', 'id_umkm');
    }

    public function menuSubmissions(){
        return $this->hasMany(MenuSubmission::class, 'id_umkm', 'id_umkm');
    }

    public function getFotoUmkmUrlAttribute(): string
    {
        if (!$this->foto_umkm || $this->foto_umkm === '-') {
            return asset('images/default-umkm.svg');
        }

        if (str_starts_with($this->foto_umkm, 'http://') || str_starts_with($this->foto_umkm, 'https://')) {
            return $this->foto_umkm;
        }

        return Storage::url($this->foto_umkm);
    }
}
