<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\WeightSumRequest;
use App\Models\WeightSumScore;

class WeightSumController extends Controller
{
    public function show($id_class_subject)
    {
        $weight = WeightSumScore::where('class_subject_id', $id_class_subject)->first();
        return response()->json($weight);
    }

    public function update(WeightSumRequest $request, $id_class_subject)
    {
        $data = $request->validated();

        $this->validateTotal($data);

        $normalized = $this->normalizeWeights($data);
        WeightSumScore::where('class_subject_id', $id_class_subject)->update($normalized);

        return response()->json(['message' => 'success']);
    }

    private function validateTotal(array $data): void
    {
        $total = array_sum($data);

        if ($total !== 100) {
            abort(422, 'Total bobot harus berjumlah 100');
        }
    }

    private function normalizeWeights(array $data): array
    {
        return [
            'assignment_weight' => $data['assignment_weight'] / 100,
            'daily_weight'      => $data['daily_weight'] / 100,
            'uts_weight'        => $data['uts_weight'] / 100,
            'uas_weight'        => $data['uas_weight'] / 100,
        ];
    }
}
