<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeachingAssignmentRequest;
use App\Http\Requests\UpdateTeachingAssignmentRequest;
use App\Http\Resources\TeachingAssignmentResource;
use App\Models\ClassSubject;
use App\Services\TeachingAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TeachingAssignmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware("academicYearDraft", only: ["update", "destroy"]),
        ];
    }

    public function __construct(protected TeachingAssignmentService $service) {}

    public function index(Request $request)
    {
        $query = $this->service->getTeachingAssignments($request);
        return TeachingAssignmentResource::collection($query->paginate(10));
    }

    public function store(StoreTeachingAssignmentRequest $request)
    {
        $this->service->create($request->validated());
        return response()->json(["message" => "success"], 201);
    }

    public function show(ClassSubject $classSubject)
    {
        return response()->json($classSubject);
    }

    public function update(UpdateTeachingAssignmentRequest $request, ClassSubject $classSubject)
    {
        $this->service->update($classSubject, $request->validated());
        return response()->json(["message" => "success"]);
    }

    public function destroy(ClassSubject $classSubject)
    {
        $classSubject->delete();
        return response()->json(["message" => "success"]);
    }
}
