<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Pantry\bookmark;
use Pantry\User;

/**
 * @extends Factory<bookmark>
 */
class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->title(),
            'image' => '',
            'url' => fake()->url(),
            'description' => fake()->text(),
            'public' => false,
            'created_at' => fake()->time(),
            'updated_at' => now(),
            'user_id' => User::all()->random()->id,
        ];
    }
}
