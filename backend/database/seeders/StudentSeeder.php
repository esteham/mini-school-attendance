<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Classes and sections
        $classes = ['9', '10'];
        $sections = ['A', 'B', 'C'];

        foreach ($classes as $class) {
            foreach ($sections as $section) {
                // Create 10 students for each class and section
                Student::factory(10)->create([
                    'class' => $class,
                    'section' => $section,
                ]);
            }
        }
    }
}
