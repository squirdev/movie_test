<?php


namespace App\Repositories\Eloquent;




use App\Models\Movie;
use App\Models\Plan;
use App\Models\RentMovie;
use App\Models\User;
use App\Repositories\Contracts\AccountRepository;
use App\Repositories\Contracts\MovieRentRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Auth;

class EloquentRentMovieRepository extends EloquentBaseRepository implements MovieRentRepository
{
    /**
     * EloquentUserRepository constructor.
     *
     * @param  RentMovie  $rent
     *
     * @internal param \Illuminate\Contracts\Config\Repository $config
     */
    protected $account;
    public function __construct(RentMovie $rent ,AccountRepository $account)
    {
        parent::__construct($rent);
        $this->account = $account;
    }


    /**
     * @param  array  $input
     *
     * @return RentMovie
     * @throws Exception
     *
     * @throws Throwable
     */
    public function store(array $input)
    {
        $data = $this->checkPermission($input);

        if(isset($data->getData()->status)){
            return response()->json(['status'=>"error",'message'=>$data->getData()->message]);
        }

        $user = Auth::user();

        $movie = Movie::find($input['movie_id']);

        if(!$movie){
            return response()->json(['status'=>'error','message'=>"There is no Movie"]);
        }

        $input['start_at'] = Carbon::now();
        $input['end_at'] = $movie->rent_end;

        $rent_movie = $this->make(Arr::only($input,['start_at','end_at']));

        $rent_movie ->user_id = $user->id;
        $rent_movie->movie_id = $input['movie_id'];

        DB::transaction(function() use ($input,$user,$movie,$rent_movie){

                $user->credit = $user->credit - $movie->rent_price;
                $user->save();
                $rent_movie->save();
        });
        return response()->json(['data'=>$rent_movie]);
    }

    /**
     * @param array $input
     * @return JsonResponse
     * */
    public function checkPermission($input){
        $user = Auth::user();
//        check movie id
        if(!isset($input['movie_id'])){
            return response()->json(['status'=>false,'message'=>'There is no movie id']);
        }

        $movie = Movie::find($input['movie_id']);
//        check movie recod
        if(!$movie){
            return response()->json(['status'=>false,'message'=>'There is no movie']);
        }

//        check credit
        if($user->credit < 0 || $movie->rent_price >$user->credit ){
            return response()->json(['status'=>false,'message'=>'not enough credit']);
        }

//
////            check rent period
//        $now = Carbon::now();
//        if(!($now>= $movie->rent_start && $now <=$movie->rent_end)){
//            return response()->json(['status'=>false,'message'=>'period is passed']);
//        }
//        check premium access

        if($movie->plan->name == Plan::TYPE_PREMIUM){
            if(!$this->account->isPremium()){ //

                return response()->json(['status'=>false,'message'=>'user is not premium']);
            }
        }
        return response()->json([]);
    }
}
