<?php

use App\Http\Controllers\API\Admin\AcademicCalendarController;
use App\Http\Controllers\API\Admin\AcademicYearController as AdminAcademicYearController;
use App\Http\Controllers\API\Admin\AttendanceReportController;
use App\Http\Controllers\API\Admin\PromotionController;
use App\Http\Controllers\API\AssesmentController;
use App\Http\Controllers\API\AssignmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClassSubjectController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\SubmissionController;
use App\Http\Controllers\API\Admin\SchoolClassController;
use App\Http\Controllers\API\Admin\SettingController;
use App\Http\Controllers\API\Admin\StudentController;
use App\Http\Controllers\API\Admin\StudentEnrollmentController;
use App\Http\Controllers\API\Admin\SubjectController;
use App\Http\Controllers\API\Admin\TeacherController;
use App\Http\Controllers\API\Admin\TeachingAssignmentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\ExamAssignmentController;
use App\Http\Controllers\API\ExamAttemptController;
use App\Http\Controllers\API\ExamController;
use App\Http\Controllers\API\FileStreamController;
use App\Http\Controllers\API\GradeController;
use App\Http\Controllers\API\QuestionBankController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\StatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WeightSumController;
use Illuminate\Support\Facades\Route;



Route::get('/files/{path}', [FileStreamController::class, "stream"])->where('path', '.*');;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/academic-years/current', [AdminAcademicYearController::class, 'current']);
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/change-password', [UserController::class, 'changePassword']);
    Route::post('/user/change-photo', [UserController::class, 'changePhoto']);
    /*
    | TEACHER + STUDENT
    */
    Route::middleware(['role:teacher,student', 'academicYear:active'])->group(function () {
        Route::get('/class', [ClassSubjectController::class, 'index']);
        Route::middleware('memberClass')->group(function () {
            Route::prefix('/class/{id_class_subject}')->group(function () {
                Route::get('/', [ClassSubjectController::class, 'activity']);
                Route::get('/member', [ClassSubjectController::class, 'memberClass']);
                Route::get('/assignment', [AssignmentController::class, 'assignmentClass'])->name('listAssigments');
                Route::get('/post/{id_post}', [PostController::class, "detailPost"]);
                Route::get('/post/{id_post}/comment', [CommentController::class, "index"]);
                Route::post('/post/{id_post}/comment', [CommentController::class, "store"]);
                Route::get('/exam', [ExamController::class, 'index']);
                Route::middleware('role:student')->group(function () {
                    Route::put('/exam/{exam}/start', [ExamAttemptController::class, 'store']);
                    Route::get('/exam/{exam}/detail', [ExamAttemptController::class, 'index']);
                    Route::get('/exam/{exam}/question', [ExamAttemptController::class, 'show']);
                    Route::put('/exam/{exam}/submit', [ExamAttemptController::class, 'attempt']);
                });
            });
        });
    });

    /*
    | STUDENT
    */
    Route::middleware(['role:student', 'academicYear:active'])->group(function () {
        Route::post('/class/{id_class_subject}/post/{id_post}/submission', [SubmissionController::class, "store"]);
        Route::get('/assignment', [AssignmentController::class, 'index']);
        Route::get('/attendance', [AttendanceController::class, 'show']);
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::get('/grade/history', [GradeController::class, 'history']);
        Route::get('/grade/{id_class_subject}', [GradeController::class, 'historySubject']);
    });

    /*
    | TEACHER
    */
    Route::middleware(['role:teacher', 'academicYear:active'])->group(function () {
        Route::get('/archive', [ClassSubjectController::class, 'archive']);
        Route::get('/question-bank/list/{id_class_subject}', [QuestionBankController::class, "list"]);
        Route::delete('/file/{id_post}', [PostController::class, "deletePostFile"])->name('deletePostFile');
        Route::prefix('/class/{id_class_subject}')->group(function () {
            // MATERIAL MANAGE
            Route::prefix('/material')->group(function () {
                Route::post('/', [PostController::class, "store"])->name('storeMaterial');
                Route::post('/{id_post}', [PostController::class, "update"])->name('updateMaterial');
            });

            // DELETE POST 
            Route::delete('/post/{id_post}', [PostController::class, "delete"])->name('deletePost');

            // ASSIGNMENT MANAGE
            Route::prefix('/assignment')->group(function () {
                Route::post('/', [PostController::class, "store"])->name('storeAssignment');
                Route::post('/{id_post}', [PostController::class, "update"])->name('updateAssignment');
            });

            // ASSESMENT
            Route::prefix('/assesment')->group(function () {
                Route::get('/{id_post}', [AssesmentController::class, "index"])->name('assesments');
                Route::get('/{id_submission}/submission', [AssesmentController::class, "show"])->name('submissionDetail');
                Route::put('/{id_submission}/submission', [AssesmentController::class, "update"])->name('signAssesment');
            });

            Route::get('/weight-score', [WeightSumController::class, "show"]);
            Route::put('/weight-score', [WeightSumController::class, "update"]);
            Route::apiResource('/exam', ExamController::class)->only(['store', 'update', 'destroy', 'show']);
            Route::get('/report', [GradeController::class, "index"]);
            Route::get('/report/export', [GradeController::class, "export"]);
            Route::get('/report/{id_student}', [GradeController::class, "show"]);
        });

        Route::apiResource('/question-bank', QuestionBankController::class);
        Route::prefix('/question-bank')->group(function () {
            Route::get('{questionBank}/question', [QuestionController::class, "index"]);
            Route::post('{questionBank}/question', [QuestionController::class, "store"]);
            Route::put('/question/{question}', [QuestionController::class, 'update']);
            Route::delete('/question/{question}', [QuestionController::class, 'destroy']);
        });

        Route::get('/submission/{id_file}/download')->name('submissionDownload');
    });

    Route::prefix("/admin")->group(function () {
        Route::get("/academic", [AdminAcademicYearController::class, 'index']);
        Route::get("/subject/list", [SubjectController::class, "list"]);
        Route::middleware(['role:admin'])->group(function () {
            Route::get("/stat", [StatController::class, "statAdmin"]);
            Route::put('/academic/{academicYear}/status', [AdminAcademicYearController::class, 'handleStatus']);
            Route::put("/academic/{academicYear}/activate", [AdminAcademicYearController::class, 'activate']);
            Route::apiResource("/academic", AdminAcademicYearController::class)->parameters(["academic" => "academicYear"])->except(['index']);;
            Route::get("/calendar", [AcademicCalendarController::class, 'index']);
            Route::get("/calendar/weekly", [AcademicCalendarController::class, 'weekly']);
            Route::put("/calendar/{academicCalendar}", [AcademicCalendarController::class, "update"]);
            Route::get("/class/list", [SchoolClassController::class, "list"]);
            Route::apiResource("/class", SchoolClassController::class)->parameters(["class" => "schoolClass"]);
            Route::apiResource("/subject", SubjectController::class);
            Route::post("/student/import", [StudentController::class, "import"]);
            Route::get("/student/export", [StudentController::class, "export"]);
            Route::post("/student/{student}/reset-password", [StudentController::class, "resetPassword"]);
            Route::apiResource("/student", StudentController::class);
            Route::apiResource('student-enrollment', StudentEnrollmentController::class);
            Route::post("/teacher/import", [TeacherController::class, "import"]);
            Route::get("/teacher/list", [TeacherController::class, "list"]);
            Route::get("/teacher/export", [TeacherController::class, "export"]);
            Route::post("/teacher/{teacher}/reset-password", [TeacherController::class, "resetPassword"]);
            Route::apiResource("/teacher", TeacherController::class);
            Route::apiResource("/teaching-assignment", TeachingAssignmentController::class)->parameters(['teaching-assignment' => 'classSubject']);
            Route::put('/attendance', [AttendanceController::class, 'upsertStatus']);
            Route::get('/attendance-report/summary', [AttendanceReportController::class, 'summary']);
            Route::get('/attendance-report/today', [AttendanceReportController::class, 'today']);
            Route::get('/attendance-report/history', [AttendanceReportController::class, 'history']);
            Route::post('/attendance-report/export', [AttendanceReportController::class, 'export']);
            Route::get('/setting', [SettingController::class, 'show']);
            Route::post('/setting', [SettingController::class, 'upsert']);
            Route::get('/promotion', [PromotionController::class, 'index']);
            Route::post('/promotion', [PromotionController::class, 'store']);
        });
    });
});
