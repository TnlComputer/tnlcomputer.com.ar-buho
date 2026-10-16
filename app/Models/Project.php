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

    /** Ruta relativa a public/ (img/portfolio/... o uploads/proyectos/...). Si hay una versión .webp al lado, usa esa (pesa mucho menos). */
    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }
        $path = $this->image;
        $webp = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);
        if ($webp !== $path && is_file(public_path($webp))) {
            $path = $webp;
        }

        return asset(implode('/', array_map('rawurlencode', explode('/', $path))));
    }
}
