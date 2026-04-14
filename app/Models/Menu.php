<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';
    protected $primaryKey = 'id_menu';

    protected $fillable = [
        'nama_menu',
        'harga_menu',
        'foto_menu',
        'id_umkm',
    ];
    public function umkm(){
        return $this->belongsTo(Umkm::class, 'id_umkm', 'id_umkm');
    }

    public function getFotoMenuUrlAttribute(): string
    {
        if (!$this->foto_menu || $this->foto_menu === '-') {
            return asset('images/default-menu.svg');
        }

        if (str_starts_with($this->foto_menu, 'http://') || str_starts_with($this->foto_menu, 'https://')) {
            return $this->foto_menu;
        }

        return Storage::url($this->foto_menu);
    }
}
