<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReferralDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ReferralEmailTemplateSeeder::class);
    }
}
