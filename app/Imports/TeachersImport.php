<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TeachersImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $firstName = Str::of($row['nama'])->before(' ')->lower();
            $username  = "{$firstName}_{$row['nip']}";

            $user = User::create([
                'name' => $row['nama'],
                'username' => $username,
                'password' => Hash::make($row['nip']),
                'photo' => 'default.png',
                'role' => 'teacher',
            ]);

            return $user->teacher()->create([
                'nip' => $row['nip'],
                'phone' => $row['telepon'] ?? "",
            ]);
        });
    }

    public function rules(): array
    {
        return [
            '*.nip' => 'required|unique:teachers,nip',
            '*.nama' => 'required',
            '*.telepon' => 'nullable|max:13',
        ];
    }
}
