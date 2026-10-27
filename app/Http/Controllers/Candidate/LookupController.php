<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Ethnicity;
use App\Models\Sect;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function states(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_id' => ['required', 'string', 'size:3', 'exists:countries,id'],
        ]);

        return response()->json(
            State::query()->where('country_id', $data['country_id'])->orderBy('name')->get(['id', 'name'])
        );
    }

    public function cities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'state_id' => ['required', 'string', 'max:7', 'exists:states,id'],
        ]);

        return response()->json(
            City::query()->where('state_id', $data['state_id'])->orderBy('name')->get(['id', 'name'])
        );
    }

    public function sects(Request $request): JsonResponse
    {
        $data = $request->validate([
            'religion_id' => ['required', 'integer', 'exists:religions,id'],
        ]);

        return response()->json(
            Sect::query()->where('religion_id', $data['religion_id'])->orderBy('name')->get(['id', 'name'])
        );
    }

    public function ethnicities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_id' => ['required', 'string', 'size:3', 'exists:countries,id'],
        ]);

        return response()->json(
            Ethnicity::query()->where('country_id', $data['country_id'])->orderBy('name')->get(['id', 'name'])
        );
    }
}
