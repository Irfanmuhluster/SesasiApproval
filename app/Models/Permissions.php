<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'judul', 'isi', 'detail', 'status', 'komentar_verifikator'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
