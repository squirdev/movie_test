<?php


namespace App\Repositories\Contracts;

use App\Models\Movie;
use App\Models\RentMovie;
use App\Models\User;

interface MovieRentRepository extends BaseRepository
{
    /**
     * @param  array  $input
     *
     * @return mixed
     */
    public function store(array $input);

    /**
     * @param  RentMovie  $rent
     * @param  array  $input
     *
     *
     * @return mixed
     */

}
