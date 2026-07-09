<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [10, 11, 12];
        $majors = ['RPL', 'DKV'];
        $sections = [1, 2, 3];

        $now = now();
        $classes = [];

        foreach ($levels as $level) {
            foreach ($majors as $major) {
                foreach ($sections as $section) {
                    $classes[] = [
                        'level' => $level,
                        'major' => $major,
                        'section' => $section,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        DB::table('school_classes')->insert($classes);
    }
}
