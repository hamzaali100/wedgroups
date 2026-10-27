<?php

namespace App\Support;

use App\Models\Candidate;
use Carbon\CarbonImmutable;

class CandidateProfileData
{
    /**
     * @return array<string, mixed>
     */
    public static function owner(Candidate $candidate): array
    {
        return [
            'display_name' => $candidate->display_name,
            'real_name' => $candidate->real_name,
            'profile_status' => $candidate->profile_status,
            'profile_visibility' => $candidate->profile_visibility,
            'review_status' => $candidate->review_status,
            'review_label' => Candidate::REVIEW_STATUSES[$candidate->review_status] ?? $candidate->review_status,
            'rejection_reason' => $candidate->rejection_reason,
            'sections' => [
                self::section('Personal', 'candidate.profile.personal.edit', [
                    self::item('Real name (private)', $candidate->real_name),
                    self::item('Display name', $candidate->display_name),
                    self::item('Born', self::born($candidate)),
                    self::item('Gender', self::label(Candidate::GENDERS, $candidate->gender)),
                    self::item('Height', $candidate->height.' cm'),
                    self::item('Marital status', self::label(Candidate::MARITAL_STATUSES, $candidate->marital_status)),
                    self::item('Religion', $candidate->religion?->name),
                    self::item('Sect', $candidate->sect?->name),
                    self::item('Ethnicity', $candidate->ethnicityRecord?->name),
                    self::item('Ethnicity details', $candidate->ethnicity),
                    self::item('Caste', $candidate->caste),
                    self::item('Nationality', $candidate->nationalities->pluck('name')->join(', ')),
                    self::item('Country', $candidate->country?->name),
                    self::item('State', $candidate->state?->name),
                    self::item('City', $candidate->city?->name),
                    self::item('Residence (private)', $candidate->residence_details),
                    self::item('Grew up in', $candidate->grewUpIn?->name),
                    self::item('Has children', self::yesNo($candidate->has_kids)),
                    self::item('Wants children', self::label(Candidate::WANTS_CHILDREN, $candidate->wants_children)),
                    self::item('Willing to move abroad', self::yesNo($candidate->willing_to_move_abroad)),
                    self::item('Other details', $candidate->other_details),
                    self::item('Disabilities (private)', $candidate->disabilities),
                    self::item('Phone (private)', $candidate->phone_number),
                    self::item('Contact person (private)', $candidate->contact_person),
                    self::item('Contact relationship (private)', $candidate->contact_relationship),
                    self::item('Contact phone (private)', $candidate->contact_phone_number),
                    self::item('Contact email (private)', $candidate->contact_email),
                ]),
                self::section('Education', 'candidate.profile.education.edit', [
                    self::item('Education', self::label(Candidate::EDUCATION_LEVELS, $candidate->education_level)),
                    self::item('Universities', $candidate->universities_names),
                    self::item('Schools', $candidate->schools_names),
                ]),
                self::section('Career', 'candidate.profile.career.edit', [
                    self::item('Occupation', $candidate->occupation?->name),
                    self::item('Monthly income', $candidate->monthly_income),
                    self::item('Career details', $candidate->career_details),
                    self::item('LinkedIn (private)', $candidate->linkedin),
                ]),
                self::section('Family', 'candidate.profile.family.edit', [
                    self::item('Siblings', (string) $candidate->total_siblings),
                    self::item('Siblings description', $candidate->siblings_description),
                    self::item("Father's occupation", $candidate->father_occupation),
                    self::item("Mother's occupation", $candidate->mother_occupation),
                    self::item('Family details', $candidate->family_details),
                    self::item('Parent contact (private)', $candidate->parent_contact_number),
                ]),
                self::section('Partner requirements', 'candidate.profile.partner.edit', [
                    self::item('Age range', self::range($candidate->partner_age_min, $candidate->partner_age_max)),
                    self::item('Height range (cm)', self::range($candidate->partner_height_min, $candidate->partner_height_max)),
                    self::item('Details', $candidate->partner_requirement_details),
                ]),
            ],
        ];
    }

    /**
     * Fields another group member is allowed to see. Contact and identity fields stay off this payload.
     *
     * @return array<string, mixed>
     */
    public static function publicView(Candidate $candidate): array
    {
        return [
            'id' => $candidate->user_id,
            'display_name' => $candidate->display_name,
            'sections' => [
                self::section('Personal', null, [
                    self::item('Name', $candidate->display_name),
                    self::item('Age', self::age($candidate).' years'),
                    self::item('Gender', self::label(Candidate::GENDERS, $candidate->gender)),
                    self::item('Height', $candidate->height.' cm'),
                    self::item('Marital status', self::label(Candidate::MARITAL_STATUSES, $candidate->marital_status)),
                    self::item('Religion', $candidate->religion?->name),
                    self::item('Sect', $candidate->sect?->name),
                    self::item('Ethnicity', $candidate->ethnicityRecord?->name),
                    self::item('Caste', $candidate->caste),
                    self::item('Nationality', $candidate->nationalities->pluck('name')->join(', ')),
                    self::item('Lives in', collect([$candidate->city?->name, $candidate->state?->name, $candidate->country?->name])->filter()->implode(', ')),
                    self::item('Grew up in', $candidate->grewUpIn?->name),
                    self::item('Has children', self::yesNo($candidate->has_kids)),
                    self::item('Wants children', self::label(Candidate::WANTS_CHILDREN, $candidate->wants_children)),
                    self::item('Willing to move abroad', self::yesNo($candidate->willing_to_move_abroad)),
                    self::item('Other details', $candidate->other_details),
                ]),
                self::section('Education', null, [
                    self::item('Education', self::label(Candidate::EDUCATION_LEVELS, $candidate->education_level)),
                    self::item('Universities', $candidate->universities_names),
                    self::item('Schools', $candidate->schools_names),
                ]),
                self::section('Career', null, [
                    self::item('Occupation', $candidate->occupation?->name),
                    self::item('Monthly income', $candidate->monthly_income),
                    self::item('Career details', $candidate->career_details),
                ]),
                self::section('Family', null, [
                    self::item('Siblings', (string) $candidate->total_siblings),
                    self::item('Siblings description', $candidate->siblings_description),
                    self::item("Father's occupation", $candidate->father_occupation),
                    self::item("Mother's occupation", $candidate->mother_occupation),
                    self::item('Family details', $candidate->family_details),
                ]),
                self::section('Looking for', null, [
                    self::item('Age range', self::range($candidate->partner_age_min, $candidate->partner_age_max)),
                    self::item('Height range (cm)', self::range($candidate->partner_height_min, $candidate->partner_height_max)),
                    self::item('Details', $candidate->partner_requirement_details),
                ]),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function card(Candidate $candidate): array
    {
        return [
            'id' => $candidate->user_id,
            'display_name' => $candidate->display_name,
            'age' => self::age($candidate),
            'height' => $candidate->height,
            'gender' => self::label(Candidate::GENDERS, $candidate->gender),
            'marital_status' => self::label(Candidate::MARITAL_STATUSES, $candidate->marital_status),
            'location' => collect([$candidate->city?->name, $candidate->country?->name])->filter()->implode(', '),
            'education' => self::label(Candidate::EDUCATION_LEVELS, $candidate->education_level),
        ];
    }

    public static function age(Candidate $candidate): int
    {
        return CarbonImmutable::create($candidate->year_of_birth, $candidate->month_of_birth, 1)->age;
    }

    /**
     * @param  array<int, array{label: string, value: ?string}>  $items
     * @return array<string, mixed>
     */
    private static function section(string $title, ?string $route, array $items): array
    {
        return [
            'title' => $title,
            'edit_url' => $route === null ? null : route($route),
            'items' => $items,
        ];
    }

    /**
     * @return array{label: string, value: ?string}
     */
    private static function item(string $label, mixed $value): array
    {
        $text = $value === null ? null : trim((string) $value);

        return [
            'label' => $label,
            'value' => $text === '' ? null : $text,
        ];
    }

    /**
     * @param  array<string, string>  $labels
     */
    private static function label(array $labels, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $labels[$value] ?? $value;
    }

    private static function yesNo(?bool $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? 'Yes' : 'No';
    }

    private static function born(Candidate $candidate): string
    {
        $month = CarbonImmutable::create(2000, $candidate->month_of_birth, 1)->format('F');

        return $month.' '.$candidate->year_of_birth.' ('.self::age($candidate).')';
    }

    private static function range(mixed $min, mixed $max): ?string
    {
        if ($min === null && $max === null) {
            return null;
        }

        return ($min ?? 'any').' – '.($max ?? 'any');
    }
}
