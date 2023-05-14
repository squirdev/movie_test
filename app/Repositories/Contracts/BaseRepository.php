<?php


namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface BaseRepository
{
    /**
     * @return Builder
     */
    public function query();

    /**
     * @param      $query
     * @param null $callback
     *
     */
    public function search($query, $callback = null);

    /**
     * @param array $columns
     *
     * @return Builder
     */
    public function select(array $columns = ['*']);

    /**
     * @param array $attributes
     *
     * @return Model
     */
    public function make(array $attributes = []);
}
