<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Project::generateNextIdentifier(),
            'project_name' => $this->faker->sentence(3),
            'customer' => $this->faker->company(),
            'branch' => $this->faker->city(),
            'fase' => 'start',
            'start_project' => now(),
            'end_project' => now()->addDays(30),
        ];
    }
}
