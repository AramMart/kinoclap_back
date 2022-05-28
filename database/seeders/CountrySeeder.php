<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            [
                'name_am' => 'Հայաստան',
                'name_ru' => 'Армения',
                'name_en' => 'Armenia',
                'phone_code' => 374
            ],
            [
                'name_am' => 'Ռուսաստան',
                'name_ru' => 'Россия',
                'name_en' => 'Russian',
                'phone_code' => 7
            ]
        ];

        foreach ($countries as $country){
            Country::firstOrCreate($country);
        }

    }
}
