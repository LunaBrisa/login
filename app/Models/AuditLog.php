<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'accion',
        'email',
        'ip',
        'descripcion'
    ];

    /**
     * Usuario relacionado
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}