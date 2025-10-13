<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Thailand',
                'code' => 'TH',
                'currency_code' => 'THB',
                'currency_symbol' => '฿',
                'timezone' => 'Asia/Bangkok',
                'is_active' => true,
            ],
            [
                'name' => 'Portugal',
                'code' => 'PT',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'timezone' => 'Europe/Lisbon',
                'is_active' => true,
            ],
            [
                'name' => 'Spain',
                'code' => 'ES',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'timezone' => 'Europe/Madrid',
                'is_active' => true,
            ],
            [
                'name' => 'Mexico',
                'code' => 'MX',
                'currency_code' => 'MXN',
                'currency_symbol' => '$',
                'timezone' => 'America/Mexico_City',
                'is_active' => true,
            ],
            [
                'name' => 'Colombia',
                'code' => 'CO',
                'currency_code' => 'COP',
                'currency_symbol' => '$',
                'timezone' => 'America/Bogota',
                'is_active' => true,
            ],
            [
                'name' => 'Estonia',
                'code' => 'EE',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'timezone' => 'Europe/Tallinn',
                'is_active' => true,
            ],
            [
                'name' => 'Georgia',
                'code' => 'GE',
                'currency_code' => 'GEL',
                'currency_symbol' => '₾',
                'timezone' => 'Asia/Tbilisi',
                'is_active' => true,
            ],
            [
                'name' => 'Romania',
                'code' => 'RO',
                'currency_code' => 'RON',
                'currency_symbol' => 'lei',
                'timezone' => 'Europe/Bucharest',
                'is_active' => true,
            ],
            [
                'name' => 'Czech Republic',
                'code' => 'CZ',
                'currency_code' => 'CZK',
                'currency_symbol' => 'Kč',
                'timezone' => 'Europe/Prague',
                'is_active' => true,
            ],
            [
                'name' => 'Poland',
                'code' => 'PL',
                'currency_code' => 'PLN',
                'currency_symbol' => 'zł',
                'timezone' => 'Europe/Warsaw',
                'is_active' => true,
            ],
            [
                'name' => 'Indonesia',
                'code' => 'ID',
                'currency_code' => 'IDR',
                'currency_symbol' => 'Rp',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ],
            [
                'name' => 'Vietnam',
                'code' => 'VN',
                'currency_code' => 'VND',
                'currency_symbol' => '₫',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'is_active' => true,
            ],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}