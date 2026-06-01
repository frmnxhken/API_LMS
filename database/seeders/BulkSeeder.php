<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class BulkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $defaultPassword = Hash::make('tes_123');
        $activeAcademicYearId = AcademicYear::activeId();
        $now = now();

        // ==========================================
        // TAHAP 0: BUAT FAKE SCHOOL CLASSES
        // ==========================================
        $this->command->info("Sedang membuat data kelas palsu...");

        $levels = [10, 11, 12];
        $majors = ['RPL', 'TKJ', 'MM', 'TABUS', 'TKR']; // Sesuaikan dengan jurusan di SMK kamu

        $classesToInsert = [];
        foreach ($levels as $level) {
            foreach ($majors as $major) {
                $classesToInsert[] = [
                    'level'      => $level,
                    'major'      => $major,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Truncate/kosongkan dulu tabel kelas jika ingin datanya bersih setiap di-seed (opsional)
        // DB::table('school_classes')->truncate(); 

        DB::table('school_classes')->insert($classesToInsert);

        // Ambil semua ID kelas yang baru saja kita masukkan ke dalam array
        $schoolClassIds = DB::table('school_classes')->pluck('id')->toArray();

        // ==========================================
        // TAHAP 1: GENERATE DATA SISWA DI MEMORI
        // ==========================================
        $this->command->info("Sedang men-generate 10,000 data siswa di memori...");
        $rows = [];
        $baseNis = 2301000001;

        for ($i = 0; $i < 10000; $i++) {
            $rows[] = [
                'nama' => $faker->name,
                'nis'  => (string)($baseNis + $i)
            ];
        }

        // ==========================================
        // TAHAP 2: BULK INSERT TRANSACTION
        // ==========================================
        $this->command->info("Memulai proses bulk insert ke database...");

        DB::transaction(function () use ($rows, $defaultPassword, $activeAcademicYearId, $now, $schoolClassIds) {

            $chunks = array_chunk($rows, 1000);

            foreach ($chunks as $chunk) {
                $usersToInsert = [];
                $currentBatchRows = [];

                // 2.1. Siapkan data User
                foreach ($chunk as $row) {
                    $firstName = Str::of($row['nama'])->before(' ')->lower();
                    $firstName = preg_replace('/[^a-zA-Z0-9]/', '', $firstName);
                    $username  = "{$firstName}_{$row['nis']}";

                    $usersToInsert[] = [
                        'name'       => $row['nama'],
                        'username'   => $username,
                        'password'   => $defaultPassword,
                        'role'       => 'student',
                        'photo'      => 'default.png',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $currentBatchRows[$username] = $row;
                }

                DB::table('users')->insert($usersToInsert);

                // Ambil kembali id user yang baru di-insert
                $insertedUsers = User::whereIn('username', array_keys($currentBatchRows))
                    ->get(['id', 'username'])
                    ->keyBy('username');

                $studentsToInsert = [];
                $studentNisToUserMap = [];

                // 2.2. Siapkan data Student
                foreach ($currentBatchRows as $username => $row) {
                    $user = $insertedUsers->get($username);

                    if ($user) {
                        $studentsToInsert[] = [
                            'user_id'    => $user->id,
                            'nis'        => $row['nis'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $studentNisToUserMap[$row['nis']] = $user->id;
                    }
                }

                DB::table('students')->insert($studentsToInsert);

                // Ambil id student yang baru di-insert untuk relasi Enrollment
                $insertedStudents = Student::whereIn('user_id', array_values($studentNisToUserMap))
                    ->get(['id', 'user_id', 'nis'])
                    ->keyBy('nis');

                $enrollmentsToInsert = [];

                // 2.3. Siapkan data Enrollment
                foreach ($chunk as $row) {
                    $student = $insertedStudents->get($row['nis']);

                    if ($student) {
                        // KUNCINYA DI SINI: Siswa di-assign ke kelas secara acak (random) dari kelas yang tersedia
                        $randomClassId = $schoolClassIds[array_rand($schoolClassIds)];

                        $enrollmentsToInsert[] = [
                            'student_id'       => $student->id,
                            'school_class_id'  => $randomClassId,
                            'academic_year_id' => 1,
                            'created_at'       => $now,
                            'updated_at'       => $now,
                        ];
                    }
                }

                DB::table('student_enrollments')->insert($enrollmentsToInsert);
            }
        });

        $this->command->info("Bulk seeding sukses!");
        $this->command->info("Total: 15 Kelas baru dibuat.");
        $this->command->info("Total: 10,000 Siswa disebar secara acak ke dalam kelas.");
    }
}
