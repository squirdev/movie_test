<?php


namespace App\Repositories\Contracts;

use App\Models\Cast;

/**
 * Interface CastRepository.
 */
interface CastRepository extends BaseRepository
{

    /**
     * @param array $input
     *
     * @return mixed
     */
    public function store(array $input);

    /**
     * @param Cast $cast
     * @param array $input
     *
     * @return mixed
     */
    public function update(Cast $cast, array $input);


    /**
     * @param Cast $cast
     *
     * @return mixed
     */
    public function destroy(Cast $cast);



}
