<?php


namespace App\Repositories\Eloquent;

use App\Models\Plan;
use App\Repositories\Contracts\MovieRepository;
use App\Repositories\Contracts\CastRepository;
use App\Models\Movie;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class EloquentMovieRepository extends EloquentBaseRepository implements MovieRepository
{

    /**
     * @var CastRepository
     */
    protected $casts;

    /**
     * EloquentMovieRepository constructor.
     *
     * @param  Movie  $movie
     * @param  CastRepository  $cast
     *
     */
    public function __construct(
        Movie $movie,
        CastRepository $casts

    ) {
        parent::__construct($movie);
       $this->casts = $casts;
    }

    /**
     * @param  array  $input
     * @param  bool  $confirmed
     *
     * @return Movie
     * @throws GeneralException
     * @throws Exception
     *
     */
    public function store(array $input, bool $confirmed = false)
    {
        if(!isset($input['cast'])){
            $input['cast'] = null;
        }
        $cast = $this->casts->store($input);

        if(!isset($input['plan'])){
            $input['plan'] = Plan::basicPlan()->id;
        }

        $movie = $this->make(Arr::only($input,['title','release_year','tag','poster','rent_start','rent_end','rent_price','imdbID','stramingId','stream_url','status']));

        $movie->cast_id = $cast->id;
        $movie->plan_id = $input['plan'];

        if(!$movie->save()){
          throw new GeneralException(__('locale.exceptions.something_went_wrong'));
         }
        return $movie;
    }

    /**
     * @param  Cast  $cast
     * @param  array  $input
     *
     * @return Cast
     * @throws Exception|Throwable
     *
     * @throws Exception
     */

    public function update(Movie $movie, array $input)
    {
        if(isset($input['cast'])){
            $cast = $movie->cast;
            $cast->names = $input['cast'];
            if(!$cast->save()){
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }
        }


        if(! $movie->update($input)){
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }
        if(isset($input['plan'])){
            $movie->plan_id = $input['plan'];
            if(!$movie->save()){
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }
        }
        return $movie;
    }

    public function destroy(Movie $movie)
    {
        if($movie->cast){
            $this->casts->destroy($movie->cast);
        }
        if(!$movie->delete()){
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }
        return true;
    }
}
