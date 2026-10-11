<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = ['title', 'issuer', 'issued_at', 'file', 'featured'];

    protected $casts = [
        'issued_at' => 'date',
        'featured' => 'boolean',
    ];

    public function fileUrl(): ?string
    {
        return $this->file ? asset(implode('/', array_map('rawurlencode', explode('/', $this->file)))) : null;
    }
}
