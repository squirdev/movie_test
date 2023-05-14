<?php

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\User;
class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currency_data = [
            [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'US Dollar',
                'code'    => 'USD',
                'format'  => '${PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'EURO',
                'code'    => 'EUR',
                'format'  => '€{PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'British Pound',
                'code'    => 'GBP',
                'format'  => '£{PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Japanese Yen',
                'code'    => 'JPY',
                'format'  => '¥{PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Russian Ruble',
                'code'    => 'RUB',
                'format'  => '‎₽{PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Vietnam Dong',
                'code'    => 'VND',
                'format'  => '{PRICE}₫',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Brazilian Real',
                'code'    => 'BRL',
                'format'  => '‎R${PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Bangladeshi Taka',
                'code'    => 'BDT',
                'format'  => '‎৳{PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Canadian Dollar',
                'code'    => 'CAD',
                'format'  => '‎C${PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Indian rupee',
                'code'    => 'INR',
                'format'  => '‎₹{PRICE}',
                'status'  => true,
            ], [
                'uid'     => uniqid(),
                'user_id' => 1,
                'name'    => 'Nigerian Naira',
                'code'    => 'CBN',
                'format'  => '‎₦{PRICE}',
                'status'  => true,
            ],
        ];



        // -------------------------------start create admin ----------------------------------
        // Default password
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $defaultPassword = app()->environment('production') ? Str::random() : '12345678';
        User::create([
            'first_name'        => 'Super',
            'last_name'         => 'Admin',
            'image'             => null,
            'email'             => 'developer@gmail.com',
            'password'          => bcrypt($defaultPassword),
            'status'            => true,
            'is_admin'          => true,
            'email_verified_at' => now(),
        ])->save();


//        -------------------------------------end create admin --------------------------------
        foreach ($currency_data as $data) {
            Currency::create($data)->save();
        }
    }
}
