<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcademicYear
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, string $allowedStates = 'draft')
    {
        $academic = AcademicYear::active();

        if (!$academic) {
            return response()->json([
                'code' => 'ACADEMIC_YEAR_NOT_FOUND',
                'message' => 'invalid academic',
            ], 403);
        }

        $allowed = explode(',', $allowedStates);

        if (!in_array($academic->status, $allowed)) {
            return response()->json([
                'code' => 'ACADEMIC_YEAR_' . strtoupper($academic->status),
                'message' => "invalid academic",
                'status' => $academic->status
            ], 403);
        }

        $request->attributes->set('academic_year', $academic);
        return $next($request);
    }
}
