<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostFile;
use App\Models\Student;
use App\Models\Submission;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function create($classId, $type, $data)
    {
        $this->guardType($type);

        return DB::transaction(function () use ($classId, $type, $data) {
            $post = Post::create([
                'class_subject_id' => $classId,
                'title'            => $data['title'],
                'content'          => $data['content'],
                'type'             => $type,
                'due'              => $this->resolveDue($type, $data),
            ]);

            if (isset($data['files']) && is_array($data['files']) && count($data['files']) > 0) {
                $this->uploadFiles($post, $data['files']);
            }

            if ($type === 'assignment') {
                $this->generateSubmission($post, $classId);
            }

            return $post;
        });
    }

    public function update($classId, $postId, $type, $data)
    {
        $this->guardType($type);

        $post = Post::where('class_subject_id', $classId)->where('id', $postId)->firstOrFail();

        return DB::transaction(function () use ($post, $type, $data) {
            $post->update([
                'title'     => $data['title'],
                'content'   => $data['content'],
                'due'       => $this->resolveDue($type, $data),
            ]);

            if (isset($data['files']) && is_array($data['files']) && count($data['files']) > 0) {
                $this->uploadFiles($post, $data['files']);
            }

            return $post;
        });
    }

    public function delete($classId, $postId)
    {
        $post = Post::with(['post_files', 'submissions'])
            ->where('class_subject_id', $classId)->where('id', $postId)->firstOrFail();

        DB::transaction(function () use ($post, $classId) {
            $this->deleteUnusedFiles($post->post_files);

            if ($post->type === 'assignment') {
                foreach ($post->submissions as $submission) {
                    if (method_exists($submission, 'submission_files')) {
                        foreach ($submission->submission_files as $file) {
                            if (Storage::exists($file->file_path)) {
                                Storage::delete($file->file_path);
                            }
                            $file->delete();
                        }
                    }

                    Grade::where('student_id', $submission->student_id)
                        ->where('class_subject_id', $classId)
                        ->decrement('assignment_total_score', $submission->score);
                }
            }

            $post->submissions()->delete();
            $post->delete();
        });
    }

    private function uploadFiles(Post $post, array $files)
    {
        foreach ($files as $file) {
            $path = $file->store('posts', 'public');
            PostFile::create([
                'post_id'       => $post->id,
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'extension'     => $file->getClientOriginalExtension(),
                'size'          => $file->getSize(),
            ]);
        }
    }

    private function deleteUnusedFiles($post_files)
    {
        foreach ($post_files as $file) {
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
            }

            $file->delete();
        }
    }

    private function generateSubmission(Post $post, $classId)
    {
        $students = Student::whereHas(
            'enrollments.schoolClass.classSubjects',
            function ($q) use ($classId) {
                $q->where('class_subjects.id', $classId);
            }
        )->get();

        foreach ($students as $student) {
            Submission::create([
                'post_id'    => $post->id,
                'student_id' => $student->id,
                'status'     => 'pending',
                'score'      => 0,
            ]);
        }
    }

    private function resolveDue($type, $data)
    {
        return $type === 'material' ? null : ($data['due'] ?? null);
    }

    private function guardType($type)
    {
        if (!in_array($type, ['material', 'assignment'])) {
            abort(403, 'Invalid post type');
        }
    }
}
