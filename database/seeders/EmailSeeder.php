<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emails = [
            [
                'email' => 'subhanzaheer2003@gmail.com',
                'app_password' => 'sgjimihpvfjnrqtq',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'usamajalal17@gmail.com',
                'app_password' => 'qphfmismzgpwuijb',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'stressjp99@gmail.com',
                'app_password' => 'pblfzseyngepqfjr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'mujeeb.subhani90@gmail.com',
                'app_password' => 'pyqcyxmzatgjhkal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'tahawaqas8@gmail.com',
                'app_password' => 'cjoehdqshlbghdgc',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('email')->insert($emails);
    }
}
