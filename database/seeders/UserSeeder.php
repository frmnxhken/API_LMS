<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /*
        | ADMIN
        */

        $adminId = DB::table('users')->insertGetId([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'photo' => 'default.png',
            'role' => 'admin'
        ]);


        /*
        | TEACHERS
        */

        $teachers = [
            ['name' => 'Budi Santoso', 'nip' => '19890101', 'phone' => '081234567801'],
            ['name' => 'Ahmad Fauzi', 'nip' => '19890102', 'phone' => '081234567802'],
            ['name' => 'Siti Rahmawati', 'nip' => '19890103', 'phone' => '081234567803'],
            ['name' => 'Dewi Lestari', 'nip' => '19890104', 'phone' => '081234567804'],
            ['name' => 'Rudi Hartono', 'nip' => '19890105', 'phone' => '081234567805'],
            ['name' => 'Andi Pratama', 'nip' => '19890106', 'phone' => '081234567806'],
            ['name' => 'Rina Kurniawati', 'nip' => '19890107', 'phone' => '081234567807'],
            ['name' => 'Taufik Hidayat', 'nip' => '19890108', 'phone' => '081234567808'],
        ];

        foreach ($teachers as $t) {

            $firstName = strtolower(explode(' ', $t['name'])[0]);
            $username = $firstName . '_' . $t['nip'];

            $userId = DB::table('users')->insertGetId([
                'name' => $t['name'],
                'username' => $username,
                'password' => Hash::make('tes123'),
                'photo' => 'default.png',
                'role' => 'teacher'
            ]);

            DB::table('teachers')->insert([
                'user_id' => $userId,
                'nip' => $t['nip'],
                'phone' => $t['phone']
            ]);
        }


        /*
        | STUDENTS
        */

        //     $students = [
        //         ['name' => 'Rizky Maulana', 'nis' => '2301001'],
        //         ['name' => 'Dimas Saputra', 'nis' => '2301002'],
        //         ['name' => 'Fajar Nugroho', 'nis' => '2301003'],
        //         ['name' => 'Iqbal Ramadhan', 'nis' => '2301004'],
        //         ['name' => 'Agus Setiawan', 'nis' => '2301005'],
        //         ['name' => 'Nanda Putri', 'nis' => '2301006'],
        //         ['name' => 'Putri Ayu Lestari', 'nis' => '2301007'],
        //         ['name' => 'Rahmat Hidayat', 'nis' => '2301008'],
        //         ['name' => 'Yoga Pratama', 'nis' => '2301009'],
        //         ['name' => 'Bagus Kurniawan', 'nis' => '2301010'],
        //         ['name' => 'Mochammad Arif', 'nis' => '2301011'],
        //         ['name' => 'Salsa Nabila', 'nis' => '2301012'],
        //         ['name' => 'Dewi Anggraini', 'nis' => '2301013'],
        //         ['name' => 'Nabila Zahra', 'nis' => '2301014'],
        //         ['name' => 'Aldi Saputra', 'nis' => '2301015'],
        //     ];

        //     foreach ($students as $s) {

        //         $firstName = strtolower(explode(' ', $s['name'])[0]);
        //         $username = $firstName . '_' . $s['nis'];

        //         $userId = DB::table('users')->insertGetId([
        //             'name' => $s['name'],
        //             'username' => $username,
        //             'password' => Hash::make('tes123'),
        //             'photo' => 'default.png',
        //             'role' => 'student'
        //         ]);

        //         DB::table('students')->insert([
        //             'user_id' => $userId,
        //             'nis' => $s['nis']
        //         ]);
        //     }
    }
}
