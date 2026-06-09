<?php

namespace App\Exports;

use App\Models\AcademicCalendar;
use App\Models\Attendance;
use App\Models\StudentEnrollment;
use Maatwebsite\Excel\Concerns\FromArray;

class AttendanceMonthlyExport implements FromArray
{
    public function __construct(
        private int $academicYearId,
        private int $schoolClassId,
        private int $month
    ) {}

    public function array(): array
    {
        $rows = [];

        $schoolDays = AcademicCalendar::query()
            ->where('academic_year_id', $this->academicYearId)
            ->whereMonth('date', $this->month)
            ->where('is_school_day', true)
            ->orderBy('date')
            ->get();

        $enrollments = StudentEnrollment::query()
            ->with([
                'student.user'
            ])
            ->where('academic_year_id', $this->academicYearId)
            ->where('school_class_id', $this->schoolClassId)
            ->get();

        $studentIds = $enrollments
            ->pluck('student_id');

        $attendances = Attendance::query()
            ->where('academic_year_id', $this->academicYearId)
            ->whereMonth('date', $this->month)
            ->whereIn('student_id', $studentIds)
            ->get();

        $attendanceMap = [];

        foreach ($attendances as $attendance) {
            $attendanceMap[$attendance->student_id][$attendance->date]
                = $attendance->status;
        }

        $header = [
            'No',
            'Nama',
            'NIS',
        ];

        foreach ($schoolDays as $day) {
            $header[] = date('d', strtotime($day->date));
        }

        $header[] = 'H';
        $header[] = 'T';
        $header[] = 'S';
        $header[] = 'I';
        $header[] = 'A';

        $rows[] = $header;

        foreach ($enrollments as $index => $enrollment) {

            $student = $enrollment->student;

            $present = 0;
            $late = 0;
            $sick = 0;
            $permission = 0;
            $alpha = 0;

            $row = [
                $index + 1,
                $student->user->name,
                $student->nis,
            ];

            foreach ($schoolDays as $day) {

                $date = $day->date;

                $status =
                    $attendanceMap[$student->id][$date]
                    ?? 'alpha';

                switch ($status) {

                    case 'present':
                        $row[] = 'H';
                        $present++;
                        break;

                    case 'late':
                        $row[] = 'T';
                        $late++;
                        break;

                    case 'sick':
                        $row[] = 'S';
                        $sick++;
                        break;

                    case 'permission':
                        $row[] = 'I';
                        $permission++;
                        break;

                    default:
                        $row[] = 'A';
                        $alpha++;
                        break;
                }
            }

            $row[] = $present;
            $row[] = $late;
            $row[] = $sick;
            $row[] = $permission;
            $row[] = $alpha;

            $rows[] = $row;
        }

        return $rows;
    }
}
