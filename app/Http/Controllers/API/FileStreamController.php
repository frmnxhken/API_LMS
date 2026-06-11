<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class FileStreamController extends Controller
{
    public function stream($path)
    {
        $file = storage_path('app/public/' . $path);
        abort_unless(file_exists($file), 404);
        return response()->file($file);
    }
}
