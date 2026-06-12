<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'user_activities';
    protected $primaryKey = 'id_user_activities';
    public $timestamps = true;

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_user',
        'id_session',
        'id_kategori',
        'interaction_type',
    ];

    protected $casts = [
        'id_user' => 'integer',
        'id_kategori' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}
