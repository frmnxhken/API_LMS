<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = [
            'Andi',
            'Budi',
            'Rina',
            'Siti',
            'Dewi',
            'Agus',
            'Fajar',
            'Rizki',
            'Aulia',
            'Rahmat',
            'Dimas',
            'Nabila',
            'Yoga',
            'Lutfi',
            'Fikri',
            'Anisa',
            'Nanda',
            'Yusuf',
            'Putri',
            'Ilham'
        ];

        $faker = fake();

        $password = Hash::make('12345678');

        $now = now();

        $startId = DB::table('users')->max('id') + 1;

        for ($i = 0; $i < 200; $i++) {

            $firstName = $firstNames[array_rand($firstNames)];

            $nis = 240000 + $i;

            $users[] = [
                'name' => $firstName . ' ' . $faker->lastName(),
                'username' => strtolower($firstName) . '_' . $nis,
                'password' => $password,
                'photo' => 'default.png',
                'role' => 'student',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $students[] = [
                'user_id' => $startId + $i,
                'nis' => $nis,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($users, $students) {
            DB::table('users')->insert($users);
            DB::table('students')->insert($students);
        });
    }
}
