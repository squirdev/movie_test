<?php


namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\CastRepository;
use App\Models\Cast;
use Illuminate\Support\Arr;
class EloquentCastRepository extends EloquentBaseRepository implements CastRepository
{

    /**
     * @var CastRepository
     */
    protected  $casts;

    /**
     * EloquentCastRepository constructor.
     *
     * @param  Cast  $cast

     */
    public function __construct(
         Cast $cast
    ) {
        parent::__construct($cast);
    }

    /**
     * @param  array  $input
     * @param  bool  $confirmed
     *
     * @return Cast
     * @throws GeneralException
     * @throws Exception
     *
     */
    public function store(array $input)
    {
        if(!isset($input['cast'])){
            $input['cast'] = null;
        }
        $data['names'] = $input['cast'];
        $cast = $this->make(Arr::only($data,['names']));

        if(! $this->save($cast)){
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }
        return $cast;
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

    public function update(Cast $cast, array $input)
    {
        if(isset($input['names'])){
            if(is_array($input['names']) && count($input['names'])>0){
                $input['names'] = implode(',',$input['names']);
            }else{
                $input['names'] = null;
            }
        }else{
            $input['names'] = null;
        }
        if(!$cast->update($input)){
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }
        return $cast;
    }

    /**
     * @param  Cast  $cast
     *
     * @return bool|null
     * @throws Exception|Throwable
     *
     */
    public function destroy(Cast $cast){
        if(!$cast->delete()){
            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }
        return true;
    }

    private function save(Cast $cast){
        if(!$cast->save()){
            return false;
        }
        return true;
    }
}
