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
            ['class_name' => 'Cardio', 'class_day' => 'Monday'],
            ['class_name' => 'Boxing', 'class_day' => 'Tuesday'],
            ['class_name' => 'Booty', 'class_day' => 'Wednesday'],
            ['class_name' => 'Core', 'class_day' => 'Thursday'],
            ['class_name' => 'Zumba', 'class_day' => 'Friday'],
        ];

        foreach ($classes as $class) {
            GymClass::updateOrCreate(
                ['class_name' => $class['class_name'], 'class_day' => $class['class_day']],
                $class
            );
        }
    }
}
