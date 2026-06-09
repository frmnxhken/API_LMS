<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\SubmissionFile;

class SubmissionService
{
    public function upload(Submission $submission, $files): void
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $path = $file->store('submissions', 'public');

            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
