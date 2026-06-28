<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\StorageUrl;

class MenuSubmission extends Model
{
    use HasFactory;

    public const FOTO_DAFTAR_MENU_SENTINEL = '__FOTO_DAFTAR_MENU__';

    protected $table = 'menu_submissions';

    protected $fillable = [
        'umkm_submission_id',
        'id_umkm',
        'nama_pengusul',
        'email_pengusul',
        'nama_menu',
        'harga_menu',
        'foto_menu',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'harga_menu' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function umkmSubmission(): BelongsTo
    {
        return $this->belongsTo(UmkmSubmission::class, 'umkm_submission_id');
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class, 'id_umkm', 'id_umkm');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
