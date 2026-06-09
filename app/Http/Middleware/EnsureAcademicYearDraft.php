<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        if (!$academicYear || $academicYear->status !== 'draft') {
            return response()->json([
                'message' => 'Tahun ajaran tidak dapat diubah.'
            ], 403);
        }

        return $next($request);
    }
}
