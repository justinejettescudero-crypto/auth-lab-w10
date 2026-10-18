<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $ana = User::factory()->create([
            'name' => 'Ana Instructor',
            'email' => 'ana@example.com',
            'role' => 'instructor',
        ]);

        $ben = User::factory()->create([
            'name' => 'Ben Instructor',
            'email' => 'ben@example.com',
            'role' => 'instructor',
        ]);

        $carla = User::factory()->create([
            'name' => 'Carla Student',
            'email' => 'carla@example.com',
            'role' => 'student',
        ]);

        $anaCourses = collect([
            Course::factory()->for($ana, 'owner')->create([
                'code' => 'WEB101',
                'title' => 'Web Development Fundamentals',
                'units' => 3,
            ]),
            Course::factory()->for($ana, 'owner')->create([
                'code' => 'WEB202',
                'title' => 'Front-End Development with JavaScript',
                'units' => 3,
            ]),
        ]);

        Course::factory()
            ->inactive()
            ->for($ana, 'owner')
            ->create([
                'code' => 'MOB301',
                'title' => 'Android Mobile App Development',
                'units' => 3,
            ]);

        Course::factory()->for($ben, 'owner')->create([
            'code' => 'WEB302',
            'title' => 'Back-End Web Development with Laravel',
            'units' => 3,
        ]);

        Course::factory()->for($ben, 'owner')->create([
            'code' => 'DB201',
            'title' => 'Database Design and MySQL',
            'units' => 3,
        ]);

        Course::factory()->for($ben, 'owner')->create([
            'code' => 'MOB302',
            'title' => 'Cross-Platform Mobile Development',
            'units' => 3,
        ]);

        $anaCourses->first()
            ->students()
            ->attach($carla);
    }
}
