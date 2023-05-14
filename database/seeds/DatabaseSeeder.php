<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CurrenciesSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PaymentMethodsSeeder::class);
        $this->call(MovieSeeder::class);
    }
}
