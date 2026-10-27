<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Religion;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Lookup rows required before a candidate can create a profile.
     */
    public function run(): void
    {
        $countries = [
            ['id' => 'PAK', 'name' => 'Pakistan', 'nationality' => 'Pakistani'],
            ['id' => 'GBR', 'name' => 'United Kingdom', 'nationality' => 'British'],
            ['id' => 'USA', 'name' => 'United States', 'nationality' => 'American'],
            ['id' => 'ARE', 'name' => 'United Arab Emirates', 'nationality' => 'Emirati'],
            ['id' => 'CAN', 'name' => 'Canada', 'nationality' => 'Canadian'],
        ];

        foreach ($countries as $country) {
            Country::query()->firstOrCreate(['id' => $country['id']], $country);
        }

        $religions = [
            ['id' => 1, 'name' => 'Islam'],
            ['id' => 2, 'name' => 'Christianity'],
            ['id' => 3, 'name' => 'Hinduism'],
            ['id' => 4, 'name' => 'Sikhism'],
            ['id' => 5, 'name' => 'Other'],
        ];

        foreach ($religions as $religion) {
            Religion::query()->firstOrCreate(['id' => $religion['id']], $religion);
        }
    }
}
