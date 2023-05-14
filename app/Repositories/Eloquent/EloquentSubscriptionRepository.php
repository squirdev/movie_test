<?php


namespace App\Repositories\Eloquent;

use App\Exceptions\GeneralException;
use App\Models\Invoices;
use App\Models\PaymentMethods;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\SubscriptionTransaction;
use App\Models\User;
use App\Repositories\Contracts\SubscriptionRepository;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Stripe\Stripe;
use Session;
use Throwable;
class EloquentSubscriptionRepository extends EloquentBaseRepository implements SubscriptionRepository
{
    /**
     * EloquentSubscriptionRepository constructor.
     *
     * @param  Subscription  $subscription
     */
    public function __construct(Subscription $subscription)
    {
        parent::__construct($subscription);
    }

    /**
     * @param  array  $input
     *
     * @return JsonResponse
     * @throws GeneralException
     */
    public function store(array $input): JsonResponse
    {
        $plan = Plan::find($input['plan_id']);
        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => __('locale.subscription.plan_not_found'),
            ]);
        }
        $user = User::where('status', true)->find($input['user_id']);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => __('locale.subscription.customer_not_found'),
            ]);
        }
        if ($user->activeSubscription()) {
            $user->activeSubscription()->cancelNow();
        }

        if ($user->subscription) {
            $subscription = $user->subscription;
        } else {
            $subscription = new Subscription();
            $subscription->user_id = $user->id;
            $subscription->start_at = Carbon::now();
        }


        $subscription->status = Subscription::STATUS_ACTIVE;
        $subscription->plan_id = $plan->getBillableId();
        $subscription->end_period_last_days = $input['end_period_last_days'];
        $subscription->current_period_ends_at = $subscription->getPeriodEndsAt(Carbon::now());
        $subscription->end_at = null;
        $subscription->end_by = null;
        if (!$this->save($subscription)) {
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }

        // add transaction
        $subscription->addTransaction(SubscriptionTransaction::TYPE_SUBSCRIBE, [
            'end_at' => $subscription->end_at,
            'current_period_ends_at' => $subscription->current_period_ends_at,
            'status' => SubscriptionTransaction::STATUS_SUCCESS,
            'title' => trans('locale.subscription.subscribed_to_plan', ['plan' => $subscription->plan->getBillableName()]),
            'amount' => $subscription->plan->getBillableFormattedPrice(),
        ]);

        // add log
        $subscription->addLog(SubscriptionLog::TYPE_ADMIN_PLAN_ASSIGNED, [
            'plan' => $subscription->plan->getBillableName(),
            'price' => $subscription->plan->getBillableFormattedPrice(),
        ]);

        if ($user->credit == null || $user->credit == '-1' || $subscription->plan->getOption('max') == '-1') {
            $user->credit = $subscription->plan->getOption('max');
        } else {
            if ($subscription->plan->getOption('add_previous_balance') == 'yes') {
                $user->credit += $subscription->plan->getOption('max');
            } else {
                $user->credit = $subscription->plan->getOption('max');
            }
        }
        $user->save();
        return response()->json([
            'status'  => 'success',
            'message' => __('locale.subscription.subscription_successfully_added'),
        ]);
    }

    /**
     * @param  Subscription  $subscription
     *
     * @return bool
     */
    private function save(Subscription $subscription): bool
    {
        if ( ! $subscription->save()) {
            return false;
        }

        return true;
    }

    public function renew(Subscription $subscription)
    {
        // TODO: Implement renew() method.
    }

    /**
     * approve pending subscription
     *
     * @param  Subscription  $subscription
     *
     * @return bool
     */
    public function approvePending(Subscription $subscription)
    {
        //set active subscription
        $subscription->setActive();
        $subscription->addTransaction(SubscriptionTransaction::TYPE_SUBSCRIBE, [
            'end_at'                 => $subscription->end_at,
            'current_period_ends_at' => $subscription->current_period_ends_at,
            'status'                 => SubscriptionTransaction::STATUS_SUCCESS,
            'title'                  => trans('locale.subscription.subscribed_to_plan', ['plan' => $subscription->plan->getBillableName()]),
            'amount'                 => $subscription->plan->getBillableFormattedPrice(),
        ]);

        // add log
        $subscription->addLog(SubscriptionLog::TYPE_ADMIN_APPROVED, [
            'plan'  => $subscription->plan->getBillableName(),
            'price' => $subscription->plan->getBillableFormattedPrice(),
        ]);
        sleep(1);
        // add log
        $subscription->addLog(SubscriptionLog::TYPE_SUBSCRIBED, [
            'plan'  => $subscription->plan->getBillableName(),
            'price' => $subscription->plan->getBillableFormattedPrice(),
        ]);

        $invoice = Invoices::whereLike(['transaction_id'], $subscription->uid)->first();

        if ($invoice) {
            $invoice->update([
                'status'         => 'paid',
                'transaction_id' => $subscription->uid,
            ]);
            $user = User::find($invoice->user_id);

            if($user->credit == null ||$user->credit =='-1' || $subscription->plan->getOptions('max') =="-1"){
                $user->credit = $subscription->plan->getOption('max');
            }else{
                if ($subscription->plan->getOption('add_previous_balance') == 'yes') {
                    $user->credit += $subscription->plan->getOption('max');
                } else {
                    $user->credit = $subscription->plan->getOption('max');
                }
            }
            $user->save();

        }
        return true;
    }

    /**
     * reject pending subscription with reason
     *
     * @param  Subscription  $subscription
     * @param  array  $input
     *
     * @return bool
     */

    public function rejectPending(Subscription $subscription, array $input): bool
    {
        $subscription->setEnded(auth()->user()->id);

        $subscription->addLog(SubscriptionLog::TYPE_ADMIN_REJECTED, [
            'plan'   => $subscription->plan->getBillableName(),
            'price'  => $subscription->plan->getBillableFormattedPrice(),
            'reason' => $input['reason'],
        ]);

        return true;
    }

    public function changePlan(Subscription $subscription)
    {
        // TODO: Implement changePlan() method.
    }

    /**
     * @param  Subscription  $subscription
     *
     * @return bool
     * @throws Exception|Throwable
     *
     */
    public function destroy(Subscription $subscription): bool
    {
        if ( ! $subscription->delete()) {
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }

        return true;
    }

    /**
     * @param  array  $ids
     *
     * @return mixed
     * @throws Exception|Throwable
     *
     */
    public function batchApprove(array $ids): bool
    {
        DB::transaction(function () use ($ids) {
            if ($this->query()->whereIn('uid', $ids)
                ->update(['status' => true])
            ) {
                return true;
            }

            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        });

        return true;
    }

    /**
     * @param  array  $ids
     *
     * @return mixed
     * @throws Exception|Throwable
     *
     */
    public function batchCancel(array $ids): bool
    {
        DB::transaction(function () use ($ids) {
            if ($this->query()->whereIn('uid', $ids)->update([
                'status'                 => 'ended',
                'end_by'                 => Auth::user()->id,
                'current_period_ends_at' => Carbon::now(),
                'end_at'                 => Carbon::now(),
            ])) {
                return true;
            }

            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        });

        return true;
    }

    /**
     * pay payment
     *
     * @param  Plan  $plan
     * @param  Subscription  $subscription
     * @param  array  $input
     *
     * @return JsonResponse
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function payPayment(Plan $plan, Subscription $subscription, array $input)
    {
        $paymentMethod = PaymentMethods::where('status', true)->where('type', $input['payment_methods'])->first();
        if ($paymentMethod) {
            $credentials = json_decode($paymentMethod->options);
            $item_name = __('locale.subscription.payment_for_plan').' '.$plan->name;
            switch ($paymentMethod->type) {
                case PaymentMethods::TYPE_STRIPE:

                    $publishable_key = $credentials->publishable_key;
                    $secret_key      = $credentials->secret_key;

                    Stripe::setApiKey($secret_key);

                    try {
                        $checkout_session = \Stripe\Checkout\Session::create([
                            'payment_method_types' => ['card'],
                            'customer_email'       => $input['email'],
                            'line_items'           => [[
                                'price_data' => [
                                    'currency'     => $plan->currency->code,
                                    'unit_amount'  => $plan->price * 100,
                                    'product_data' => [
                                        'name' => $item_name,
                                    ],
                                ],
                                'quantity'   => 1,
                            ]],
                            'mode'                 => 'payment',
                            'success_url'          => route('customer.subscriptions.payment_success', $plan->uid),
                            'cancel_url'           => route('customer.subscriptions.payment_cancel', $plan->uid),
                        ]);

                        if ( ! empty($checkout_session->id)) {
                            Session::put('payment_method', $paymentMethod->type);
                            Session::put('session_id', $checkout_session->id);
                        }

                        return response()->json([
                            'status'          => 'success',
                            'session_id'      => $checkout_session->id,
                            'publishable_key' => $publishable_key,
                        ]);

                    } catch (Exception $exception) {

                        return response()->json([
                            'status'  => 'error',
                            'message' => $exception->getMessage(),
                        ]);

                    }
            }

            return response()->json([
                'status'  => 'error',
                'message' => __('locale.payment_gateways.not_found'),
            ]);
        }
        return response()->json([
            'status'  => 'error',
            'message' => __('locale.payment_gateways.not_found'),
        ]);
    }

    public function freeSubscription(Plan $plan){
        $paymentMethod = PaymentMethods::where('type', 'offline_payment')->first();
        if ($paymentMethod) {
            $invoice = Invoices::create([
                'user_id'        => Auth::user()->id,
                'currency_id'    => $plan->currency_id,
                'payment_method' => $paymentMethod->id,
                'amount'         => $plan->price,
                'type'           => Invoices::TYPE_SUBSCRIPTION,
                'description'    => __('locale.subscription.payment_for_plan').' '.$plan->name,
                'transaction_id' => $plan->uid,
                'status'         => Invoices::STATUS_PAID,
            ]);

            if ($invoice) {
                if (Auth::user()->activeSubscription()) {
                    Auth::user()->activeSubscription()->cancelNow();
                }

                if (Auth::user()->subscription) {
                    $subscription = Auth::user()->subscription;
                } else {
                    $subscription           = new Subscription();
                    $subscription->user_id  = Auth::user()->id;
                    $subscription->start_at = Carbon::now();
                }

                $subscription->status                 = Subscription::STATUS_ACTIVE;
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
                    'status'                 => SubscriptionTransaction::STATUS_SUCCESS,
                    'title'                  => trans('locale.subscription.subscribed_to_plan', ['plan' => $subscription->plan->getBillableName()]),
                    'amount'                 => $subscription->plan->getBillableFormattedPrice(),
                ]);

                // add log
                $subscription->addLog(SubscriptionLog::TYPE_ADMIN_PLAN_ASSIGNED, [
                    'plan'  => $subscription->plan->getBillableName(),
                    'price' => $subscription->plan->getBillableFormattedPrice(),
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => __('locale.payment_gateways.payment_successfully_made'),
                ]);

            }
            return response()->json([
                'status'  => 'error',
                'message' => __('locale.exceptions.something_went_wrong'),
            ]);

        }

        return response()->json([
            'status'  => 'error',
            'message' => __('locale.payment_gateways.not_found'),
        ]);
    }


    /**
     * pay payment
     *
     * @param  Plan  $plan
     * @param  array  $input
     * @param $user
     *
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @throws Exception
     */

    public function payRegisterPayment(Plan $plan, array $input, $user)
    {
        $paymentMethod = PaymentMethods::where('status', true)->where('type', $input['payment_methods'])->first();
        if ($paymentMethod) {
            $credentials = json_decode($paymentMethod->options);

            $item_name = __('locale.subscription.payment_for_plan').' '.$plan->name;

            switch ($paymentMethod->type) {
                case PaymentMethods::TYPE_STRIPE:

                    $publishable_key = $credentials->publishable_key;
                    $secret_key      = $credentials->secret_key;

                    Stripe::setApiKey($secret_key);

                    try {
                        $checkout_session = \Stripe\Checkout\Session::create([
                            'payment_method_types' => ['card'],
                            'customer_email'       => $input['email'],
                            'line_items'           => [[
                                'price_data' => [
                                    'currency'     => $plan->currency->code,
                                    'unit_amount'  => $plan->price * 100,
                                    'product_data' => [
                                        'name' => $item_name,
                                    ],
                                ],
                                'quantity'   => 1,
                            ]],
                            'mode'                 => 'payment',
                            "cancel_url"           => route('user.registers.payment_cancel', $user->uid),
                            'success_url'          => route('user.registers.payment_success', ['user' => $user->uid, 'plan' => $plan->uid, 'payment_method' => $paymentMethod->uid]),
                        ]);

                        if ( ! empty($checkout_session->id)) {
                            Session::put('payment_method', $paymentMethod->type);
                            Session::put('session_id', $checkout_session->id);
                        }

                        return response()->json([
                            'status'          => 'success',
                            'session_id'      => $checkout_session->id,
                            'publishable_key' => $publishable_key,
                        ]);

                    } catch (Exception $exception) {

                        return response()->json([
                            'status'  => 'error',
                            'message' => $exception->getMessage(),
                        ]);

                    }

            }
            return response()->json([
                'status'  => 'error',
                'message' => __('locale.payment_gateways.not_found'),
            ]);
        }
        return response()->json([
            'status'  => 'error',
            'message' => __('locale.payment_gateways.not_found'),
        ]);
    }
}
