<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GeneralStatus;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'description' => fake()->sentence(),
            'is_active' => GeneralStatus::ACTIVE,
        ];
    }
}
