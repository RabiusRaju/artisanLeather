<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code'=>'OMR','symbol'=>'OMR','name'=>'Omani Rial',   'name_ar'=>'ريال عُماني',   'rate'=>1,    'decimals'=>3,'sort_order'=>1],
            ['code'=>'AED','symbol'=>'AED','name'=>'UAE Dirham',    'name_ar'=>'درهم إماراتي',  'rate'=>9.64, 'decimals'=>2,'sort_order'=>2],
            ['code'=>'SAR','symbol'=>'SAR','name'=>'Saudi Riyal',   'name_ar'=>'ريال سعودي',   'rate'=>9.64, 'decimals'=>2,'sort_order'=>3],
            ['code'=>'KWD','symbol'=>'KWD','name'=>'Kuwaiti Dinar', 'name_ar'=>'دينار كويتي',  'rate'=>0.80, 'decimals'=>3,'sort_order'=>4],
            ['code'=>'USD','symbol'=>'$',  'name'=>'US Dollar',      'name_ar'=>'دولار أمريكي', 'rate'=>2.60, 'decimals'=>2,'sort_order'=>5],
            ['code'=>'GBP','symbol'=>'£',  'name'=>'British Pound', 'name_ar'=>'جنيه إسترليني','rate'=>2.05, 'decimals'=>2,'sort_order'=>6],
            ['code'=>'EUR','symbol'=>'€',  'name'=>'Euro',           'name_ar'=>'يورو',          'rate'=>2.38, 'decimals'=>2,'sort_order'=>7],
        ];

        foreach ($currencies as $c) {
            Currency::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
