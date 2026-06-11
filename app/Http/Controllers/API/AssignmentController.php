<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Services\AssignmentService;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{

    public function __construct(protected AssignmentService $service) {}

    public function index()
    {
        $user = Auth::user();
        $posts = $this->service->getAllAssignments($user);

        return AssignmentResource::collection($posts);
    }

    public function assignmentClass($id_class_subject)
    {
        $posts = $this->service->getByClass($id_class_subject);
        return response()->json($posts);
    }
}
