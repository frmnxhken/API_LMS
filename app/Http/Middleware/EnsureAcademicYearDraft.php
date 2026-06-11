<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class EnsureAcademicYearDraft
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $academicYear = AcademicYear::active();

        if (!$academicYear) {
            return response()->json([
                'code' => 'ACADEMIC_YEAR_NOT_FOUND',
                'message' => 'Academic year not found',
                'status' => null
            ], 403);
        }

        if (!$academicYear || $academicYear->status !== 'draft') {
            return response()->json([
                'message' =>  Str::upper('ACADEMIC_YEAR_' . $academicYear->status),
                'status'  => $academicYear->status ?? 'not_found'
            ], 403);
        }

        return $next($request);
    }
}
