<?php

    namespace Tonkra\Referral\Database\Seeders;

use Illuminate\Database\Seeder;
use Tonkra\Referral\Database\Seeders\ReferralEmailTemplateSeeder;

    class DatabaseSeeder extends Seeder
    {
        /**
         * Seed the application's database.
         *
         * @return void
         */
        public function run()
        {
            // $this->call(AppConfigSeeder::class);
            // $this->call(Countries::class);
            // $this->call(LanguageSeeder::class);
            // $this->call(UserSeeder::class);
            // $this->call(CurrenciesSeeder::class);
            $this->call(ReferralEmailTemplateSeeder::class);
            // $this->call(PaymentMethodsSeeder::class);
            // $this->call(PlanSeeder::class);
            // $this->call(SenderIdPlanSeeder::class);
            //  $this->call(BlacklistSeeder::class);
            //  $this->call(KeywordsSeeder::class);
            //  $this->call(PhoneNumberSeeder::class);
            //  $this->call(SenderIDSeeder::class);
            //  $this->call(SendingServerSeeder::class);
            //  $this->call(SpamWordSeeder::class);


        }

    }
