<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name'=>'Wallets',     'name_ar'=>'محافظ',    'slug'=>'wallets',     'sort_order'=>1],
            ['name'=>'Bags',        'name_ar'=>'حقائب',    'slug'=>'bags',        'sort_order'=>2],
            ['name'=>'Belts',       'name_ar'=>'أحزمة',    'slug'=>'belts',       'sort_order'=>3],
            ['name'=>'Accessories', 'name_ar'=>'إكسسوار',  'slug'=>'accessories', 'sort_order'=>4],
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c);
        }
    }
}
