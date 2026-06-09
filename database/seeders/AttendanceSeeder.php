<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        Attendance::truncate();

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now();

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach (range(1, 20) as $studentId) {

            foreach ($period as $date) {

                // Skip weekend
                if ($date->isWeekend()) {
                    continue;
                }

                /**
                 * 10% tidak dibuat record
                 * => dianggap alpha
                 */
                if (fake()->boolean(10)) {
                    continue;
                }

                $status = fake()->randomElement([
                    'present',
                    'present',
                    'present',
                    'present',
                    'present',
                    'present',
                    'present',
                    'sick',
                    'permission',
                ]);

                Attendance::create([
                    'student_id' => $studentId,
                    'academic_year_id' => 1,
                    'date' => $date->toDateString(),

                    'arrival_time' => $status === 'present'
                        ? Carbon::createFromTime(
                            fake()->numberBetween(6, 7),
                            fake()->numberBetween(0, 59),
                            fake()->numberBetween(0, 59)
                        )->format('H:i:s')
                        : null,

                    'latitude' => $status === 'present'
                        ? fake()->latitude(-7.4, -7.0)
                        : null,

                    'longitude' => $status === 'present'
                        ? fake()->longitude(112.8, 113.8)
                        : null,

                    'status' => $status,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
