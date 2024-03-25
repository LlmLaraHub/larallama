<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'encrypted:json',
    ];

    public function isActive()
    {
        return $this->is_active;
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            // Check if this is the first key being created
            if (static::count() === 0) {
                $model->is_active = true;
            }

            if ($model->isActive()) {
                static::whereActive()->update(['is_active' => false]);
            }
        });
    }

    public function scopeWhereActive($query)
    {
        return $query->where('is_active', true);
    }
}
