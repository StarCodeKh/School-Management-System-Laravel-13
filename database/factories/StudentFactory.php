<?php

namespace Database\Factories;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Religion;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admission_id' => 'ADM-'.fake()->unique()->numerify('#####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(Gender::cases()),
            'date_of_birth' => fake()->dateTimeBetween('-18 years', '-6 years'),
            'blood_group' => fake()->randomElement(BloodGroup::cases()),
            'religion' => fake()->randomElement(Religion::cases()),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->numerify('0########'),
            'section_id' => Section::factory(),
            // the class always matches the section's class
            'school_class_id' => fn (array $attributes) => Section::find($attributes['section_id'])->school_class_id,
            'roll' => (string) fake()->numberBetween(1, 60),
        ];
    }
}
