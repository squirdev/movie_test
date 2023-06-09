<?php


namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepository extends BaseRepository
{
    /**
     * @param array $input
     * @param  bool  $confirmed
     *
     * @return mixed
     */
    public function store(array $input, bool $confirmed = false);

    /**
     * @param User  $user
     * @param array $input
     *
     * @return mixed
     */
    public function update(User $user, array $input);

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function destroy(User $user);

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function impersonate(User $user);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function batchDestroy(array $ids);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function batchEnable(array $ids);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function batchDisable(array $ids);


}
