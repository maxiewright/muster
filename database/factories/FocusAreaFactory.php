<?php

namespace Database\Factories;

use App\Models\FocusArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FocusAreaFactory extends Factory
{
    protected $model = FocusArea::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
