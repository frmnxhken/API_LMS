<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentEnrollmentRequest;
use App\Http\Resources\StudentCollection;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\GradeService;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
    public function __construct(
        private GradeService $gradeService,
        private StudentEnrollmentService $service
    ) {}

    public function index(Request $request)
    {
        $search = $request->search;

        $students = Student::with("user")
            ->whereDoesntHave("enrollments")
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where("nis", "like", "%{$search}%")
                        ->orWhereHas("user", function ($user) use ($search) {
                            $user->where("name", "like", "%{$search}%");
                        });
                });
            })
            ->paginate(10);

        return new StudentCollection($students);
    }

    public function store(StudentEnrollmentRequest $request)
    {
        DB::transaction(function () use ($request) {
            foreach ($request->student_ids as $studentId) {

                $exists = StudentEnrollment::where('student_id', $studentId)->exists();

                if ($exists) abort(422, "students are already enrolled in another class.");

                StudentEnrollment::create([
                    'student_id'      => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'academic_year_id' => AcademicYear::activeId(),
                ]);

                $this->gradeService->generateForStudent(
                    $studentId,
                    $request->school_class_id
                );
            }
        });


        return response()->json(["message" => "Students enrolled successfully"]);
    }

    public function show($classId)
    {
        $meta = SchoolClass::where("id", $classId)->first();
        $data = StudentEnrollment::with("student.user")->where("school_class_id", $classId)->get();
        return response()->json(["meta" => $meta, "data" => $data]);
    }

    public function update(Request $request, StudentEnrollment $studentEnrollment)
    {
        $studentEnrollment->update(["school_class_id" => $request->school_class_id]);
        return response()->json(["message" => "Student moved successfully"]);
    }

    public function destroy(StudentEnrollment $studentEnrollment)
    {
        $this->service->remove($studentEnrollment);
        return response()->json(["message" => "Student removed successfully"]);
    }
}
