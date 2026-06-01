<?php

namespace App\Http\Middleware;

use App\Models\ClassSubject;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberClass
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $idClassSubject = $request->route('id_class_subject');

        $classSubject = ClassSubject::with('schoolClass.enrollments')->find($idClassSubject);

        if (!$classSubject) {
            return response()->json(['message' => 'Class subject tidak ditemukan'], 404);
        }

        if ($user->role === 'teacher' && $classSubject->teacher_id === $user->teacher->id) {
            return $next($request);
        }

        if (
            $user->role === 'student' &&
            $classSubject->schoolClass->enrollments->contains('student_id', $user->student->id)
        ) {
            return $next($request);
        }

        return response()->json(['message' => 'Anda tidak memiliki akses ke kelas ini'], 403);
    }
}
