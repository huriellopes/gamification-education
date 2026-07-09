<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GeneralStatus;
use Database\Factories\InstitutionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

/**
 * @property GeneralStatus $is_active
 */
#[Fillable(['name', 'description', 'is_active', 'razao_social', 'cnpj', 'slug', 'address', 'phones'])]
class Institution extends Model implements AuditableContract
{
    /** @use HasFactory<InstitutionFactory> */
    use Auditable, HasFactory, KeepsDeletedModels;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
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
