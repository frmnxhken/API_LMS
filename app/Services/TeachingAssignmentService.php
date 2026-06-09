<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\WeightSumScore;
use Illuminate\Support\Facades\DB;

class TeachingAssignmentService
{
    public function __construct(private GradeService $gradeService) {}

    public function getTeachingAssignments($request)
    {
        $query = ClassSubject::query()->with(["teacher.user", "schoolClass", "subject"]);

        if ($request->filled("school_class")) {
            $query->where("school_class_id", $request->school_class);
        }

        if ($request->filled("subject")) {
            $query->where("subject_id", $request->subject);
        }

        return $query;
    }

    public function create(array $data): ClassSubject
    {
        return DB::transaction(function () use ($data) {
            $data['academic_year_id'] = AcademicYear::activeId();
            $classSubject = ClassSubject::create($data);
            $this->gradeService->generateForClassSubject($classSubject->id);
            WeightSumScore::create(["class_subject_id" => $classSubject->id]);
            return $classSubject;
        });
    }

    public function update(ClassSubject $classSubject, array $data): void
    {
        DB::transaction(function () use ($classSubject, $data) {
            $oldClassId = $classSubject->school_class_id;
            $classSubject->update($data);

            if (
                isset($data['school_class_id']) &&
                $oldClassId !== (int) $data['school_class_id']
            ) {

                Grade::where('class_subject_id', $classSubject->id)->delete();
                $this->gradeService->generateForClassSubject($classSubject->id);
            }
        });
    }
}
