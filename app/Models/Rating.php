<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'rating';
    protected $primaryKey = 'id_rating';

    protected $fillable = [
        'nama_pengulas',
        'nilai_rating',
        'komentar',
        'id_umkm',
    ];

    public function umkm(){
        return $this->belongsTo(Umkm::class, 'id_umkm', 'id_umkm');
    }
}
