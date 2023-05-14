<?php


namespace App\Repositories\Eloquent;

use App\Exceptions\Generalexception;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Contracts\RoleRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository extends EloquentBaseRepository implements UserRepository
{



    /**
     * EloquentUserRepository constructor.
     *
     * @param  User  $user
     * @param  RoleRepository  $roles
     * @param  Repository  $config
     */
    public function __construct(
        User $user


    ) {
        parent::__construct($user);

    }

    /**
     * @param  array  $input
     * @param  bool  $confirmed
     *
     * @return User
     * @throws GeneralException
     * @throws Exception
     *
     */
    public function store(array $input, bool $confirmed = false): User
    {
        /** @var User $user */
        $user = $this->make(Arr::only($input, ['name', 'email', 'status']));


        if (isset($input['is_customer'])) {
            $user->is_customer   = true;
            $user->active_portal = 'customer';
        }

        if (isset($input['is_admin'])) {
            $user->is_admin          = true;
            $user->active_portal     = 'admin';
            $user->email_verified_at = Carbon::now();
        } else {
            $user->is_admin = false;

            if ( ! config('account.verify_account')) {
                $user->email_verified_at = Carbon::now();
            }
        }

        if ( ! $this->save($user, $input)) {
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }
        if (isset($input['is_customer'])) {

        }
        return $user;
    }

    /**
     * @param  User  $user
     * @param  array  $input
     *
     * @return User
     * @throws Exception|Throwable
     *
     * @throws Exception
     */
    public function update(User $user, array $input): User
    {
        if ( ! $user->can_edit) {
            throw new Generalexception(__('locale.exceptions.something_went_wrong'));
        }
        $user->fill(Arr::except($input, 'password'));

//        if ($user->is_super_admin && ! $user->active) {
//            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
//        }
        return $user;
    }
    /**
     * @param  User  $user
     * @param  array  $input
     *
     * @return bool
     * @throws GeneralException
     *
     */
    private function save(User $user, array $input): bool
    {
        if (isset($input['password']) && ! empty($input['password'])) {
            $user->password = Hash::make($input['password']);
        }
        if ( ! $user->save()) {
            return false;
        }

        return true;
    }

    /**
     * @param  User  $user
     *
     * @return bool
     * @throws GeneralException
     */
    public function destroy(User $user): bool
    {
        if ( ! $user->delete()) {
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }
        return true;
    }

    /**
     * @param  User  $user
     *
     * @return RedirectResponse
     * @throws Exception
     *
     */
    public function impersonate(User $user)
    {
        $authenticatedUser = auth()->user();
        if ($authenticatedUser->id === $user->id
            || Session::get('admin_user_id') === $user->id
        ) {
            return redirect()->route('admin.home');
        }

        //Login user
        auth()->loginUsingId($user->id);
        return redirect('/');
    }

    /**
     * @param  array  $ids
     *
     * @return mixed
     * @throws Exception|Throwable
     *
     */
    public function batchDestroy(array $ids): bool
    {
        // This wont call eloquent events, change to destroy if needed
        foreach ($this->query()->whereIn('uid', $ids)->cursor() as $administrator) {
            RoleUser::where('user_id', $administrator->id)->delete();
            Customer::where('user_id', $administrator->id)->delete();
            $administrator->delete();
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
    public function batchEnable(array $ids): bool
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
    public function batchDisable(array $ids): bool
    {
        DB::transaction(function () use ($ids) {
            if ($this->query()->whereIn('uid', $ids)
                ->update(['status' => false])
            ) {
                return true;
            }

            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        });

        return true;
    }
}
