<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UmkmSubmission extends Model
{
    use HasFactory;

    protected $table = 'umkm_submissions';

    protected $fillable = [
        'nama_pengusul',
        'email_pengusul',
        'nama_umkm',
        'jam_buka',
        'no_telfon',
        'alamat_lengkap',
        'deskripsi',
        'foto_umkm',
        'id_kategori',
        'latitude',
        'longitude',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'latitude' => 'decimal:15',
        'longitude' => 'decimal:15',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function menuSubmissions(): HasMany
    {
        return $this->hasMany(MenuSubmission::class, 'umkm_submission_id');
    }
}
