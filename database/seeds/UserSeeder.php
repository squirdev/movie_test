<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\SubscriptionTransaction;
use App\Models\Plan;
use Carbon\Carbon;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Default password
        $defaultPassword = app()->environment('production') ? Str::random() : '12345678';
        $this->command->getOutput()->writeln("<info>Default password:</info> $defaultPassword");
        $user                    = new User();
        $subscription            = new Subscription();
        $subscriptionTransaction = new SubscriptionTransaction();
        $subscriptionLogs        = new SubscriptionLog();
        $plans                   = new Plan();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $plans->truncate();
        $subscription->truncate();
        $subscriptionLogs->truncate();
        $subscriptionTransaction->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');



//        -------------------------------------start create plans -------------------------------
        $plans =[
            [
                'user_id'              => 1,
                'currency_id'          => 1,
                'name'                 => 'Basic',
                'description'          => 'basic plan',
                'price'                => '10',
                'billing_cycle'        => 'monthly',
                'frequency_amount'     => 1,
                'frequency_unit'       => 'month',
                'options'              => '{"max":"100","list_max":"5","subscriber_max":"500","subscriber_per_list_max":"100","segment_per_list_max":"3","billing_cycle":"monthly","sending_limit":"50000_per_hour","sending_quota":"100","sending_quota_time":"1","sending_quota_time_unit":"hour","max_process":"1","unsubscribe_url_required":"yes","create_sending_server":"no","sending_servers_max":"5","list_import":"yes","list_export":"yes","api_access":"no","create_sub_account":"no","delete_sms_history":"no","add_previous_balance":"no"}',
                'status'               => true,
                'tax_billing_required' => false,
            ],
            [
                'user_id'              => 1,
                'currency_id'          => 1,
                'name'                 => 'Premium',
                'description'          => 'Premium plan',
                'price'                => '49',
                'billing_cycle'        => 'custom',
                'frequency_amount'     => 6,
                'frequency_unit'       => 'month',
                'is_popular'           => true,
                'options'              => '{"max":"10000","list_max":"-1","subscriber_max":"-1","subscriber_per_list_max":"-1","segment_per_list_max":"-1","billing_cycle":"monthly","sending_limit":"10000_per_hour","sending_quota":"1000","sending_quota_time":"1","sending_quota_time_unit":"hour","max_process":"2","unsubscribe_url_required":"yes","create_sending_server":"yes","sending_servers_max":"5","list_import":"yes","list_export":"yes","api_access":"yes","create_sub_account":"yes","delete_sms_history":"yes","add_previous_balance":"yes"}',
                'status'               => true,
                'tax_billing_required' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan)->save();
        }
//        --------------------------------------- end create plans -------------------------------------

// ----------------------------------------------- start create basic user ------------------------------
        $basic_user = $user->create([
            'first_name'    => 'Basic',
            'last_name'     => 'User',
            'image'         => null,
            'email'         => 'basic@gmail.com',
            'password'          => bcrypt($defaultPassword),
            'status'            => true,
            'is_admin'          => false,
            'credit'            => 100,
            'email_verified_at' => now(),
        ]);
        $basic_user->save();

        $basic_plan = Plan::basicPlan();
        $basic_subscription = $subscription->create([
            'user_id'                    =>  $basic_user->id,
            'start_at'                   =>  Carbon::now(),
            'status'                     =>  Subscription::STATUS_ACTIVE,
            'plan_id'                    =>  $basic_plan->id,
            'end_period_last_days'       =>  '10',
            'end_at'                     =>  null,
            'end_by'                     =>  null,
            'payment_method_id'          =>  null
        ]);
        $basic_subscription->current_period_ends_at  = $basic_subscription->getPeriodEndsAt(Carbon::now());
        $basic_subscription->save();
        $basic_subscription->addTransaction(SubscriptionTransaction::TYPE_SUBSCRIBE,[
            'status'                     =>  SubscriptionTransaction::STATUS_SUCCESS,
            'title'                      =>  'Basic Subscription',
            'amount'                     =>  $basic_subscription->plan->getBillableFormattedPrice()
        ]);
        $basic_subscription->addLog(SubscriptionLog::TYPE_ADMIN_PLAN_ASSIGNED,[
            'plan'                       =>  $basic_subscription->plan->getBillableName(),
            'price'                      =>  $basic_subscription->plan->getBillableFormattedPrice()
        ]);

//        --------------------------------- end create basic user ----------------------------------------------------------


//        --------------------------------- create premium user ------------------------------------------------------------
        $premium_user = $user->create([
            'first_name'    => 'Premium',
            'last_name'     => 'User',
            'image'         => null,
            'email'         => 'premium@gmail.com',
            'password'          => bcrypt($defaultPassword),
            'status'            => true,
            'is_admin'          => false,
            'credit'            => 100,
            'email_verified_at' => now(),
        ]);
        $premium_user->save();

        $premium_plan = Plan::preminumPlan();
        $premium_subscription = $subscription->create([
            'user_id'                    =>  $premium_user->id,
            'start_at'                   =>  Carbon::now(),
            'status'                     =>  Subscription::STATUS_ACTIVE,
            'plan_id'                    =>  $premium_plan->id,
            'end_period_last_days'       =>  '10',
            'end_at'                     =>  null,
            'end_by'                     =>  null,
            'payment_method_id'          =>  null
        ]);
        $premium_subscription->current_period_ends_at  = $premium_subscription->getPeriodEndsAt(Carbon::now());
        $premium_subscription->save();
        $premium_subscription->addTransaction(SubscriptionTransaction::TYPE_SUBSCRIBE,[
            'status'                     =>  SubscriptionTransaction::STATUS_SUCCESS,
            'title'                      =>  'Premium Subscription',
            'amount'                     =>  $premium_subscription->plan->getBillableFormattedPrice()
        ]);
        $premium_subscription->addLog(SubscriptionLog::TYPE_ADMIN_PLAN_ASSIGNED,[
            'plan'                       =>  $premium_subscription->plan->getBillableName(),
            'price'                      =>  $premium_subscription->plan->getBillableFormattedPrice()
        ]);


    }

}
