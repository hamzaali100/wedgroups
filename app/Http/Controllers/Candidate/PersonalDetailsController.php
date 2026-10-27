<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdatePersonalDetailsRequest;
use App\Models\Candidate;
use App\Models\City;
use App\Models\Country;
use App\Models\Ethnicity;
use App\Models\Religion;
use App\Models\Sect;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PersonalDetailsController extends Controller
{
    public function edit(Request $request): Response
    {
        $candidate = $request->user()->candidate;

        return Inertia::render('Candidate/Profile/Personal', [
            'details' => $this->details($candidate),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name', 'nationality']),
            'religions' => Religion::query()->orderBy('name')->get(['id', 'name']),
            'states' => State::query()->where('country_id', $candidate->country_id)->orderBy('name')->get(['id', 'name']),
            'cities' => $candidate->state_id
                ? City::query()->where('state_id', $candidate->state_id)->orderBy('name')->get(['id', 'name'])
                : [],
            'sects' => Sect::query()->where('religion_id', $candidate->religion_id)->orderBy('name')->get(['id', 'name']),
            'ethnicities' => Ethnicity::query()->where('country_id', $candidate->country_id)->orderBy('name')->get(['id', 'name']),
            'genders' => Candidate::GENDERS,
            'maritalStatuses' => Candidate::MARITAL_STATUSES,
            'wantsChildren' => Candidate::WANTS_CHILDREN,
        ]);
    }

    public function update(UpdatePersonalDetailsRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $candidate = $request->user()->candidate;
            $candidate->update(collect($request->validated())->except('nationality_ids')->all());

            $nationalities = [];
            foreach ($request->validated('nationality_ids') ?? [] as $countryId) {
                $nationalities[$countryId] = ['created_at' => now()];
            }

            $candidate->nationalities()->sync($nationalities);
        });

        return redirect()
            ->route('candidate.profile.show')
            ->with('success', 'Personal details updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function details(Candidate $candidate): array
    {
        return [
            'real_name' => $candidate->real_name,
            'display_name' => $candidate->display_name,
            'year_of_birth' => $candidate->year_of_birth,
            'month_of_birth' => $candidate->month_of_birth,
            'gender' => $candidate->gender,
            'height' => $candidate->height,
            'marital_status' => $candidate->marital_status,
            'religion_id' => $candidate->religion_id,
            'sect_id' => $candidate->sect_id ?? '',
            'ethnicity_id' => $candidate->ethnicity_id ?? '',
            'ethnicity' => $candidate->ethnicity ?? '',
            'caste' => $candidate->caste ?? '',
            'other_details' => $candidate->other_details ?? '',
            'disabilities' => $candidate->disabilities ?? '',
            'country_id' => $candidate->country_id,
            'state_id' => $candidate->state_id ?? '',
            'city_id' => $candidate->city_id ?? '',
            'residence_details' => $candidate->residence_details ?? '',
            'grew_up_in' => $candidate->grew_up_in ?? '',
            'nationality_ids' => $candidate->nationalities()->pluck('id')->all(),
            'has_kids' => $this->nullableBoolean($candidate->has_kids),
            'wants_children' => $candidate->wants_children ?? '',
            'willing_to_move_abroad' => $this->nullableBoolean($candidate->willing_to_move_abroad),
            'phone_number' => $candidate->phone_number ?? '',
            'contact_person' => $candidate->contact_person ?? '',
            'contact_relationship' => $candidate->contact_relationship ?? '',
            'contact_phone_number' => $candidate->contact_phone_number ?? '',
            'contact_email' => $candidate->contact_email ?? '',
        ];
    }

    private function nullableBoolean(?bool $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? '1' : '0';
    }
}
