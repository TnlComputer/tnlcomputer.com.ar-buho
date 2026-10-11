<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostingPlan extends Model
{
    public const PANELS = ['directadmin' => 'DirectAdmin', 'cpanel' => 'cPanel'];

    protected $fillable = [
        'name', 'panel', 'domains', 'disk', 'transfer', 'price_monthly', 'price_semester',
        'price_annual', 'price_biannual', 'highlighted', 'active', 'sort',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_semester' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'price_biannual' => 'decimal:2',
        'highlighted' => 'boolean',
        'active' => 'boolean',
    ];

    public function panelName(): string
    {
        return self::PANELS[$this->panel] ?? $this->panel;
    }
}
