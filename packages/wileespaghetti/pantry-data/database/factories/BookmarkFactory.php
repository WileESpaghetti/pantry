<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Pantry\bookmark;

/**
 * @extends Factory<bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->title(),
            'image' => '',
            'url' => fake()->url(),
            'description' => fake()->description(),
            'public' => false,
            'created_at' => fake()->time(),
            'modified_at' => now(),
        ];
    }
}
