<?php

use App\Http\Controllers\API\AcademicYearController;
use App\Http\Controllers\API\Admin\AcademicYearController as AdminAcademicYearController;
use App\Models\User;
use App\Http\Controllers\API\AssesmentController;
use App\Http\Controllers\API\AssignmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClassSubjectController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\SubmissionController;
use App\Http\Controllers\API\Admin\SchoolClassController;
use App\Http\Controllers\API\Admin\StudentController;
use App\Http\Controllers\API\Admin\SubjectController;
use App\Http\Controllers\API\Admin\TeacherController;
use App\Http\Controllers\API\Admin\TeachingAssignmentController;
use App\Http\Controllers\API\ExamAssignmentController;
use App\Http\Controllers\API\ExamAttemptController;
use App\Http\Controllers\API\ExamController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/user', function () {
    $users = User::get();
    return response()->json($users);
});

Route::get('/files/{filename}', function ($filename) {
    return response()->file(storage_path("app/public/posts/" . $filename));
});

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/change-password', [UserController::class, 'changePassword']);
    Route::post('/user/change-photo', [UserController::class, 'changePhoto']);
    /*
    | TEACHER + STUDENT
    */
    Route::middleware('role:teacher,student')->group(function () {
        Route::get('/class', [ClassSubjectController::class, 'index']);
        Route::middleware('memberClass')->group(function () {
            Route::prefix('/class/{id_class_subject}')->group(function () {
                Route::get('/', [ClassSubjectController::class, 'activity']);
                Route::get('/member', [ClassSubjectController::class, 'memberClass']);
                Route::get('/assignment', [AssignmentController::class, 'assignmentClass'])->name('listAssigments');
                Route::get('/post/{id_post}', [PostController::class, "detailPost"]);
                Route::get('/post/{id_post}/comment', [CommentController::class, "index"]);
                Route::post('/post/{id_post}/comment', [CommentController::class, "store"]);
                Route::get('/exam', [ExamAssignmentController::class, 'index']);
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
    Route::middleware('role:student')->group(function () {
        Route::post('/class/{id_class_subject}/post/{id_post}/submission', [SubmissionController::class, "store"]);
        Route::get('/assignment', [AssignmentController::class, 'index']);
    });

    /*
    | TEACHER
    */
    Route::middleware('role:teacher')->group(function () {
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

            Route::apiResource('/exam', ExamAssignmentController::class)->only(['store', 'update', 'destroy']);
        });

        Route::apiResource('/exam', ExamController::class);
        Route::prefix('/exam')->group(function () {
            Route::get('{exam}/question', [QuestionController::class, "index"]);
            Route::post('{exam}/question', [QuestionController::class, "store"]);
            Route::put('/question/{question}', [QuestionController::class, 'update']);
            Route::delete('/question/{question}', [QuestionController::class, 'destroy']);
        });

        Route::get('/submission/{id_file}/download')->name('submissionDownload');
    });
});

Route::prefix("/admin")->group(function () {
    Route::put("/academic/{academicYear}/activate", [AdminAcademicYearController::class, 'activate']);
    Route::apiResource("/academic", AdminAcademicYearController::class)->parameters(["academic" => "academicYear"]);
    Route::apiResource("/class", SchoolClassController::class)->parameters(["class" => "schoolClass"]);
    Route::apiResource("/subject", SubjectController::class);
    Route::post("/student/import", [StudentController::class, "import"]);
    Route::get("/student/export", [StudentController::class, "export"]);
    Route::apiResource("/student", StudentController::class);
    Route::post("/teacher/import", [TeacherController::class, "import"]);
    Route::get("/teacher/export", [TeacherController::class, "export"]);
    Route::apiResource("/teacher", TeacherController::class);
    Route::apiResource("/teaching-assignment", TeachingAssignmentController::class)->parameters([
        'teaching-assignment' => 'classSubject'
    ]);
});
