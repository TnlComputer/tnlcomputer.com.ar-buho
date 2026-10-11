<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public const TYPES = ['contacto' => 'Contacto', 'service' => 'Service', 'pedido' => 'Pedido'];

    protected $fillable = ['type', 'name', 'email', 'phone', 'subject', 'body', 'items', 'total', 'ip', 'locale', 'read_at'];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
        'read_at' => 'datetime',
    ];
}
