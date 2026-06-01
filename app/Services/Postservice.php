<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostFile;
use App\Models\Student;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function detail(string $idClassSubject, string $idPost)
    {
        $user = Auth::user();
        $query = Post::where("class_subject_id", $idClassSubject);

        if ($user->role === "student") {
            $query->with([
                "post_files",
                "submissions" => function ($q) use ($user) {
                    $q->where("student_id", $user->student->id);
                },
                "submissions.submission_files"
            ]);
        } else {
            $query->with(["post_files"]);
        }

        return $query->findOrFail($idPost);
    }

    public function create(array $data, string $idClassSubject)
    {
        $due = $data["type"] === "material" ? null : $data["due"];
        $post = Post::create([
            "title" => $data["title"],
            "content" => $data["content"],
            "type" => $data["type"],
            "due" => $due,
            "class_subject_id" => $idClassSubject
        ]);

        if (isset($data["files"])) {
            $this->uploadFiles($post, $data["files"]);
        }

        if ($data["type"] === "assignment") {
            $this->generateSubmission($post, $idClassSubject);
        }

        return $post;
    }

    public function update(Post $post, array $data)
    {
        $due = $post->type === "material" ? null : $data["due"];
        $post->update([
            "title" => $data["title"],
            "content" => $data["content"],
            "due" => $due,
        ]);

        if (isset($data["files"])) {
            $this->uploadFiles($post, $data["files"]);
        }

        return $post;
    }

    public function delete(Post $post)
    {
        foreach ($post->post_files as $file) {
            if (
                $file->file_path &&
                Storage::disk('public')->exists($file->file_path)
            ) {
                Storage::disk('public')->delete($file->file_path);
            }
        }

        $post->post_files()->delete();
        $post->delete();
    }

    protected function generateSubmission(Post $post, $idClassSubject): void
    {
        $students = Student::whereHas(
            'enrollments.schoolClass.classSubjects',
            function ($q) use ($idClassSubject) {
                $q->where('class_subjects.id', $idClassSubject);
            }
        )->get();

        foreach ($students as $student) {
            Submission::create([
                "post_id" => $post->id,
                "student_id" => $student->id,
                "status" => "pending",
                "score" => 0,
            ]);
        }
    }

    protected function uploadFiles(Post $post, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('posts', 'public');
            PostFile::create([
                'post_id' => $post->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
            ]);
        }
    }

    public function deleteFile(PostFile $file): void
    {
        if (
            $file->file_path && Storage::exists($file->file_path)
        ) {
            Storage::delete($file->file_path);
        }

        $file->delete();
    }
}
