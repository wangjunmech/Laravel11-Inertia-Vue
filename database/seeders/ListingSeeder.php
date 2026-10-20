<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //运行php artisan db:seed时会自动生成指定的数据
        \App\Models\Listing::factory()->count(20)->create();
    }
}
