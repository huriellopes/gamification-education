<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Institution;
use Illuminate\Support\Str;

class InstitutionObserver
{
    /**
     * Define razão social e slug único antes de criar a instituição.
     */
    public function creating(Institution $institution): void
    {
        if (empty($institution->razao_social)) {
            $institution->razao_social = $institution->name . ' Ltda';
        }

        if (empty($institution->slug)) {
            $institution->slug = Str::slug($institution->name) ?: 'inst-' . Str::random(6);

            $originalSlug = $institution->slug;
            $count = 1;

            while (Institution::where('slug', $institution->slug)->exists()) {
                $institution->slug = $originalSlug . '-' . $count++;
            }
        }
    }
}
