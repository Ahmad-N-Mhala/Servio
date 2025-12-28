<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'United Arab Emirates',
                'code' => 'AE',
                'currency' => 'AED',
                'dial_code' => '+971',
                'rate' => 1.0,
                'states' => ['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah']
            ],
            [
                'name' => 'Saudi Arabia',
                'code' => 'SA',
                'currency' => 'SAR',
                'dial_code' => '+966',
                'rate' => 1.02,
                'states' => ['Riyadh', 'Makkah', 'Madinah', 'Eastern Province', 'Asir', 'Tabuk', 'Hail', 'Northern Borders', 'Jazan', 'Najran', 'Al Bahah', 'Al Jawf', 'Al Qassim']
            ],
            [
                'name' => 'Qatar',
                'code' => 'QA',
                'currency' => 'QAR',
                'dial_code' => '+974',
                'rate' => 1.0,
                'states' => ['Doha', 'Al Rayyan', 'Al Daayen', 'Umm Salal', 'Al Khor', 'Al Wakrah', 'Al Shamal', 'Al Sheehaniya']
            ],
            [
                'name' => 'Kuwait',
                'code' => 'KW',
                'currency' => 'KWD',
                'dial_code' => '+965',
                'rate' => 0.083,
                'states' => ['Al Asimah', 'Hawalli', 'Farwaniya', 'Ahmadi', 'Jahra', 'Mubarak Al-Kabeer']
            ],
            [
                'name' => 'Bahrain',
                'code' => 'BH',
                'currency' => 'BHD',
                'dial_code' => '+973',
                'rate' => 0.10,
                'states' => ['Capital', 'Muharraq', 'Northern', 'Southern']
            ],
            [
                'name' => 'Oman',
                'code' => 'OM',
                'currency' => 'OMR',
                'dial_code' => '+968',
                'rate' => 0.10,
                'states' => ['Muscat', 'Dhofar', 'Musandam', 'Buraimi', 'Dakhiliyah', 'North Batinah', 'South Batinah', 'North Sharqiyah', 'South Sharqiyah', 'Dhahirah', 'Wusta']
            ],
            [
                'name' => 'Indonesia',
                'code' => 'ID',
                'currency' => 'IDR',
                'dial_code' => '+62',
                'rate' => 4300,
                'states' => ['Jakarta', 'Bali', 'West Java', 'Central Java', 'East Java', 'Banten', 'Yogyakarta', 'North Sumatra', 'South Sulawesi']
            ]
        ];

        foreach ($countries as $country) {
            \App\Models\Country::updateOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}
