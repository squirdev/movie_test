<?php


namespace App\Repositories\Contracts;

use App\Models\Movie;

interface MovieRepository extends  BaseRepository
{
    /**
     * @param array $input
     * @param  bool  $confirmed
     *
     * @return mixed
     */
    public function store(array $input, bool $confirmed = false);

    /**
     * @param Movie  $movie
     * @param array $input
     *
     * @return mixed
     */
    public function update(Movie $movie, array $input);

    /**
     * @param Movie $movie
     *
     * @return mixed
     */
    public function destroy(Movie $movie);

}
