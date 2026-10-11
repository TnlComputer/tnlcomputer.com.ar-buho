<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use Translatable;

    protected $fillable = [
        'title', 'title_en', 'description', 'description_en', 'kind', 'kind_en', 'tags', 'image', 'demo_url', 'code_url', 'year', 'featured', 'active', 'sort',
    ];

    protected $casts = [
        'tags' => 'array',
        'featured' => 'boolean',
        'active' => 'boolean',
    ];

    /** Ruta relativa a public/ (img/portfolio/... o uploads/proyectos/...). */
    public function imageUrl(): ?string
    {
        return $this->image ? asset(implode('/', array_map('rawurlencode', explode('/', $this->image)))) : null;
    }
}
