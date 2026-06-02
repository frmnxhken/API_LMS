<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeachingAssignmentRequest;
use App\Http\Requests\UpdateTeachingAssignmentRequest;
use App\Http\Resources\TeachingAssignmentResource;
use App\Models\ClassSubject;
use App\Services\TeachingAssignmentService;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function __construct(protected TeachingAssignmentService $service) {}

    public function index(Request $request)
    {
        $query = ClassSubject::query()->with(['teacher.user', 'schoolClass', 'subject']);

        if ($request->filled("school_class")) {
            $query->where("school_class_id", $request->school_class);
        }

        if ($request->filled("subject")) {
            $query->where("subject_id", $request->subject);
        }

        return TeachingAssignmentResource::collection($query->paginate(10));
    }

    public function store(StoreTeachingAssignmentRequest $request)
    {
        $this->service->create($request->validated());
        return response()->json(['message' => 'success'], 201);
    }

    public function show(ClassSubject $classSubject)
    {
        return response()->json($classSubject);
    }

    public function update(UpdateTeachingAssignmentRequest $request, ClassSubject $classSubject)
    {
        $this->service->update($classSubject, $request->validated());
        return response()->json(['message' => 'success']);
    }

    public function destroy(ClassSubject $classSubject)
    {
        $this->service->delete($classSubject);
        return response()->json(['message' => 'success']);
    }
}
