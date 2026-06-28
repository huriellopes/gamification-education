<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GeneralStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

/**
 * @property GeneralStatus $is_active
 */
#[Fillable(['name', 'description', 'is_active', 'razao_social', 'cnpj', 'slug', 'address', 'phones'])]
class Institution extends Model
{
    use KeepsDeletedModels;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    protected static function booted()
    {
        static::creating(function ($institution) {
            if (empty($institution->razao_social)) {
                $institution->razao_social = $institution->name . ' Ltda';
            }

            if (empty($institution->slug)) {
                $institution->slug = Str::slug($institution->name) ?: 'inst-' . Str::random(6);

                $originalSlug = $institution->slug;
                $count = 1;

                while (static::where('slug', $institution->slug)->exists()) {
                    $institution->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => GeneralStatus::class,
            'address' => 'array',
            'phones' => 'array',
        ];
    }
}
