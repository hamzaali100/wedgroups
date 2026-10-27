<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'real_name' => fake()->name(),
            'display_name' => fake()->firstName(),
            'year_of_birth' => 1995,
            'month_of_birth' => 6,
            'gender' => 'female',
            'height' => 165,
            'marital_status' => 'never_married',
            'religion_id' => 1,
            'country_id' => 'PAK',
            'education_level' => 'bachelors_degree',
            'profile_visibility' => 'visible',
            'profile_status' => 'active',
        ];
    }
}
