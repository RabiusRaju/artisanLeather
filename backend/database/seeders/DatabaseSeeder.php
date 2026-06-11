<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            BrandSeeder::class,
            SiteContentSeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
