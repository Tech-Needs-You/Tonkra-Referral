<?php

namespace Tonkra\Referral\Database\Seeders;

use Illuminate\Database\Seeder;
use Tonkra\Referral\Database\Seeders\ReferralEmailTemplateSeeder;

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
