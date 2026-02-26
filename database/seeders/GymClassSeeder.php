<?php

namespace Database\Seeders;

use App\Models\GymClass;
use Illuminate\Database\Seeder;

class GymClassSeeder extends Seeder
{
    /**
     * Seed the application's gym classes.
     */
    public function run(): void
    {
        $classes = [
            ['class_name' => 'Cardio', 'class_day' => 'Monday', 'class_time' => '08:00'],
            ['class_name' => 'Boxing', 'class_day' => 'Tuesday', 'class_time' => '09:00'],
            ['class_name' => 'Booty', 'class_day' => 'Wednesday', 'class_time' => '10:00'],
            ['class_name' => 'Core', 'class_day' => 'Thursday', 'class_time' => '11:00'],
            ['class_name' => 'Zumba', 'class_day' => 'Friday', 'class_time' => '12:00'],
        ];

        foreach ($classes as $class) {
            GymClass::updateOrCreate(
                ['class_name' => $class['class_name'], 'class_day' => $class['class_day']],
                $class
            );
        }
    }
}
