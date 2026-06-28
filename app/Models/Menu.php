<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\StorageUrl;

class Menu extends Model
{
    use HasFactory;

    public const FOTO_DAFTAR_MENU_SENTINEL = '__FOTO_DAFTAR_MENU__';

    protected $table = 'menu';
    protected $primaryKey = 'id_menu';

    protected $fillable = [
        'nama_menu',
        'harga_menu',
        'foto_menu',
        'id_umkm',
        'source'
    ];
    public function umkm(){
        return $this->belongsTo(Umkm::class, 'id_umkm', 'id_umkm');
    }

    public function isFotoDaftarMenu(): bool
    {
        return $this->nama_menu === self::FOTO_DAFTAR_MENU_SENTINEL;
    }

    public function getIsFotoDaftarMenuAttribute(): bool
    {
        return $this->isFotoDaftarMenu();
    }

    public function getFotoMenuUrlAttribute(): string
    {
        if (!$this->foto_menu || $this->foto_menu === '-') {
            return asset('images/default-menu.svg');
        }

        if (str_starts_with($this->foto_menu, 'http://') || str_starts_with($this->foto_menu, 'https://')) {
            return $this->foto_menu;
        }

        return StorageUrl::resolve($this->foto_menu, 'images/default-menu.svg');
    }
}
