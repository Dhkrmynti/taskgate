<?php

namespace Database\Factories;

use App\Models\ProjectBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectBatch>
 */
class ProjectBatchFactory extends Factory
{
    protected $model = ProjectBatch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => 'TGIDSP-' . now()->format('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'project_name' => "Batch " . $this->faker->sentence(2),
            'customer' => $this->faker->company(),
            'branch' => $this->faker->city(),
            'fase' => 'ogp_procurement',
        ];
    }
}
