<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomizationGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'required',
        'multiple',
        'max_selections',
        'min_selections',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'required'       => 'boolean',
        'multiple'       => 'boolean',
        'is_active'      => 'boolean',
        'max_selections' => 'integer',
        'min_selections' => 'integer',
        'sort_order'     => 'integer',
    ];

    public function options()
    {
        return $this->hasMany(CustomizationOption::class)
                    ->orderBy('sort_order');
    }

    public function activeOptions()
    {
        return $this->hasMany(CustomizationOption::class)
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_customization_groups')
                    ->withPivot('sort_order');
    }
}