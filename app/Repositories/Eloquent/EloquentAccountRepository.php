<?php


namespace App\Repositories\Eloquent;

use App\Models\Plan;
use App\Models\User;
use App\Repositories\Contracts\AccountRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use App\Exceptions\GeneralException;
use Carbon\Carbon;
use App\Models\PaymentMethods;
use Auth;

class EloquentAccountRepository extends EloquentBaseRepository implements AccountRepository
{
    /**
     * @var UserRepository
     */
    protected  $users;

    /**
     * EloquentUserRepository constructor.
     *
     * @param  User  $user
     * @param  UserRepository  $users
     *
     * @internal param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(User $user, UserRepository $users)
    {
        parent::__construct($user);
        $this->users = $users;
    }

    /**
     * @param  array  $input
     *
     * @return User
     * @throws Exception
     *
     * @throws Throwable
     */

    public function register(array $input): User
    {
        $user = $this->users->store([
            'name'  => $input['first_name'],
            'email'       => $input['email'],
            'password'    => $input['password'],
            'status'      => true,
        ], true);

        Auth::login($user, true);

        return $user;
    }
/**
 * @params
 * @return boolean
 * */
    public function isPremium(){
        $user = Auth::user();
        if($user&&$user->subscription){
            $plan = $user->subscription->plan;
            if($plan && $plan->name == Plan::TYPE_PREMIUM){
                return true;
            }
        }

        return false;
    }






    /**
     *
     * get user data
     *
     * @param $provider
     * @param $data
     *
     * @return User
     * @throws GeneralException
     */
    public function findOrCreateSocial($provider, $data): User
    {
        // Email can be not provided, so set default provider email.
        $user_email = $data->getEmail() ?: $data->getId()."@".$provider.".com";

        // Get user with this email or create new one.
        /** @var User $user */
        $user = $this->users->query()->whereEmail($user_email)->first();

        if ($data->getName()) {
            $name = $data->getName();
        }

        if ( ! $user) {
            $user = $this->users->store([
                'name'        => $name,
                'email'       => $user_email,
                'status'      => true,
            ], true);
        }
        if ($user) {
            $user->image       = $data->getAvatar();
            $user->save();
        }
        return $user;
    }

    /**
     * @param  Authenticatable  $user
     * @param $name
     *
     * @return bool
     */
    public function hasPermission(Authenticatable $user, $name):bool
    {

        /** @var User $user */
        // First user is always super admin and cannot be deleted
        if ($user->id === 1) {
            return true;
        }
        $permissions = Session::get('permissions');

        if ($permissions == null && $user->is_customer) {
            $permissions = collect(json_decode($user->customer->permissions, true));
        }

        if ($permissions->isEmpty()) {
            return false;
        }

        return $permissions->contains($name);
    }
    /**
     * @param  array  $input
     *
     * @return JsonResponse
     *
     */
    public function update(array $input): JsonResponse
    {
        $user = auth()->user();
        $user->fill(Arr::only($input, ['name', 'email', 'password']));
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => __('locale.customer.profile_was_successfully_updated'),
        ]);
    }

    /**
     * @return mixed
     * @throws GeneralException|Exception
     *
     */
    public function delete(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->is_super_admin) {
            throw new GeneralException(__('exceptions.backend.users.first_user_cannot_be_destroyed'));
        }

        if ( ! $user->delete()) {
            throw new GeneralException(__('exceptions.frontend.user.delete_account'));
        }

        return true;
    }

    /**
     * @param  Authenticatable  $user
     *
     * @return Authenticatable
     * @throws GeneralException
     */

    public function redirectAfterLogin(Authenticatable $user): Authenticatable
    {
        $user->last_access_at = Carbon::now();


        if ($user->is_admin === true) {
            $user->active_portal = 'admin';
            session(['permissions' => $user->getPermissions()]);
        } else {
            $user->active_portal = 'customer';
            $permissions         = collect(json_decode($user->customer->permissions, true));
            session(['permissions' => $permissions]);
        }

        if ( ! $user->save()) {
            throw new GeneralException('Something went wrong. Please try again.');
        }
        return $user;
    }


    /**
     * @param  array  $input
     *
     * @return JsonResponse
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function payPayment(array $input): JsonResponse{
        $paymentMethod = PaymentMethods::where('status', true)->where('type', $input['payment_methods'])->first();

        if ($paymentMethod) {
            $credentials = json_decode($paymentMethod->options);
            $item_name     = 'Top up sms unit';

            $price         = $input['sms_unit'] * auth()->user()->customer->subscription->plan->getOption('per_unit_price');
            $currency_code = auth()->user()->customer->subscription->plan->currency->code;
            switch ($paymentMethod->type) {
                case PaymentMethods::TYPE_PAYPAL:
                    if ($credentials->environment == 'sandbox') {
                        $environment = new SandboxEnvironment($credentials->client_id, $credentials->secret);
                    } else {
                        $environment = new ProductionEnvironment($credentials->client_id, $credentials->secret);
                    }

                    $client = new PayPalHttpClient($environment);

                    $request = new OrdersCreateRequest();

                    $request->prefer('return=representation');
                    $request->body = [
                        "intent"              => "CAPTURE",
                        "purchase_units"      => [[
                            "reference_id" => auth()->user()->id.'_'.$input['sms_unit'],
                            'description'  => $item_name,
                            "amount"       => [
                                "value"         => $price,
                                "currency_code" => $currency_code,
                            ],
                        ]],
                        "application_context" => [
                            'brand_name' => config('app.name'),
                            'locale'     => config('app.locale'),
                            "cancel_url" => route('customer.top_up.payment_cancel'),
                            "return_url" => route('customer.top_up.payment_success', ['user_id' => auth()->user()->id, 'sms_unit' => $input['sms_unit']]),
                        ],
                    ];
                    try {
                        $response = $client->execute($request);

                        if (isset($response->result->links)) {
                            foreach ($response->result->links as $link) {
                                if ($link->rel == 'approve') {
                                    $redirect_url = $link->href;
                                    break;
                                }
                            }
                        }

                        if (isset($redirect_url)) {
                            if ( ! empty($response->result->id)) {
                                Session::put('payment_method', $paymentMethod->type);
                                Session::put('paypal_payment_id', $response->result->id);
                                Session::put('price', $price);
                            }

                            return response()->json([
                                'status'       => 'success',
                                'redirect_url' => $redirect_url,
                            ]);
                        }

                        return response()->json([
                            'status'  => 'error',
                            'message' => __('locale.exceptions.something_went_wrong'),
                        ]);


                    } catch (Exception $exception) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => $exception->getMessage(),
                        ]);
                    }


            }
        }

    }
}
