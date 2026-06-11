<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\WeightSumRequest;
use App\Models\WeightSumScore;

class WeightSumController extends Controller
{
    public function show($id_class_subject)
    {
        return response()->json(
            WeightSumScore::where("class_subject_id", $id_class_subject)->first()
        );
    }

    public function update(WeightSumRequest $request, $id_class_subject)
    {
        $total = $request->assignment_weight + $request->daily_weight + $request->uts_weight + $request->uas_weight;

        if ($total !== 100) {
            return response()->json(['message' => 'Total bobot harus berjumlah 100'], 422);
        }

        WeightSumScore::where('class_subject_id', $id_class_subject)->update([
            'assignment_weight' => $request->assignment_weight / 100,
            'daily_weight' => $request->daily_weight / 100,
            'uts_weight' => $request->uts_weight / 100,
            'uas_weight' => $request->uas_weight / 100,
        ]);

        return response()->json([
            'message' => 'Bobot berhasil diperbarui',
        ]);
    }
}
