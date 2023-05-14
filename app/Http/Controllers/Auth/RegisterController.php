<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Plan;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    public function register(Request $request)
    {
        $data = $request->except('_token');
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'plans'      => ['required'],
        ];

        $v = Validator::make($data, $rules);
        if ($v->fails()) {
            return redirect()->route('register')->withInput()->withErrors($v->errors());
        }
        $plan = Plan::find($data['plans']);
        $user = $this->account->register($data);
        $user->email_verified_at = Carbon::now();
        $user->save();
        $callback_data = $this->subscriptions->payRegisterPayment($plan, $data, $user);
        if (isset($callback_data->getData()->status)) {
            if ($callback_data->getData()->status == 'success') {
                if ($data['payment_methods'] == PaymentMethods::TYPE_STRIPE) {
                    return view('auth.payment.stripe', [
                        'session_id'      => $callback_data->getData()->session_id,
                        'publishable_key' => $callback_data->getData()->publishable_key,
                    ]);
                }

                $user->delete();

                return redirect()->route('register')->with([
                    'status'  => 'error',
                    'message' => $callback_data->getData()->message,
                ]);

            }
            $user->delete();
            return redirect()->route('register')->with([
                'status'  => 'error',
                'message' => __('locale.exceptions.something_went_wrong'),
            ]);
        }
    }

    public function showRegistrationForm()
    {
        $plans           = Plan::where('status', true)->where('show_in_customer', true)->cursor();
        $payment_methods = PaymentMethods::where('status', 1)->get();
        return view('/auth/register', [
            'plans'           => $plans,
            'payment_methods' => $payment_methods,
        ]);
    }
    public function PayOffline(Request $request){
        $paymentMethod = PaymentMethods::where('status', true)->where('type', 'offline_payment')->first();

        if ( ! $paymentMethod) {
            return redirect()->route('register')->with([
                'status'  => 'error',
                'message' => __('locale.payment_gateways.not_found'),
            ]);
        }

        $plan = Plan::findByUid($request->plan);
        $user = User::findByUid($request->user);
        $subscription                         = new Subscription();
        $subscription->user_id                = $user->id;
        $subscription->start_at               = Carbon::now();
        $subscription->status                 = Subscription::STATUS_NEW;
        $subscription->plan_id                = $plan->getBillableId();
        $subscription->end_period_last_days   = '10';
        $subscription->current_period_ends_at = $subscription->getPeriodEndsAt(Carbon::now());
        $subscription->end_at                 = null;
        $subscription->end_by                 = null;
        $subscription->payment_method_id      = $paymentMethod->id;

        $subscription->save();
        // add transaction
        $subscription->addTransaction(SubscriptionTransaction::TYPE_SUBSCRIBE, [
            'end_at'                 => $subscription->end_at,
            'current_period_ends_at' => $subscription->current_period_ends_at,
            'status'                 => SubscriptionTransaction::STATUS_PENDING,
            'title'                  => trans('locale.subscription.subscribed_to_plan', ['plan' => $subscription->plan->getBillableName()]),
            'amount'                 => $subscription->plan->getBillableFormattedPrice(),
        ]);

        // add log
        $subscription->addLog(SubscriptionLog::TYPE_CLAIMED, [
            'plan'  => $subscription->plan->getBillableName(),
            'price' => $subscription->plan->getBillableFormattedPrice(),
        ]);

        Invoices::create([
            'user_id'        => $user->id,
            'currency_id'    => $plan->currency_id,
            'payment_method' => $paymentMethod->id,
            'amount'         => $plan->price,
            'type'           => Invoices::TYPE_SUBSCRIPTION,
            'description'    => __('locale.subscription.payment_for_plan').' '.$plan->name,
            'transaction_id' => 'subscription|'.$subscription->uid,
            'status'         => Invoices::STATUS_PENDING,
        ]);

        return redirect()->route('user.home')->with([
            'status'  => 'success',
            'message' => __('locale.subscription.payment_is_being_verified'),
        ]);

    }
}
