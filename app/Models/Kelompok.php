<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelompok extends Model
{
    use HasFactory;

    protected $table = 'kelompok';
    protected $primaryKey = 'id_kelompok';

    protected $fillable = [
        'nama_kelompok',
    ];

    public function kategori(){
        return $this->belongsTo(Kelompok::class, 'id_kelompok', 'id_kelompok');
    }
    public function kategoris()
    {
        return $this->hasMany(Kategori::class, 'id_kelompok', 'id_kelompok');
    }
}
