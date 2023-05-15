<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Plan;
use App\Models\RentMovie;
use App\Repositories\Contracts\MovieRentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use DigiPig\MovieDB;
use Auth;
class MoviesController extends Controller
{
    /**
     *
     * @var MovieRentRepository $movieRent;
     *
     * */
    protected  $movieRent;
    public function __construct(MovieRentRepository $movieRent)
    {
        $this->movieRent = $movieRent;
    }

    public function index(Request $request){

       $tag = $request->input('tag');
       $name =$request->input('name');
       $limit = $request->input('length',10);
       $curPage = $request->input('page');

       $start = (intval($curPage)-1)*intval($limit);

       $movie = new Movie();

       if(isset($tag)){
           $movie = $movie->whereLike(['tag'],$tag);
       }
       if(isset($name)){
           $movie = $movie->whereLike('title',$name);
       }

       $data = $movie->offset($start)->limit($limit)->orderBy('created_at','desc')->get();

       $rented_movie_ids = RentMovie::where('user_id',Auth::user()->id)->get()->pluck('id');

        return view('index');
    }

    public function rent(Request  $request){
        try{
            $data = $request->except('_token');
            $rules = [
                'movie_id'  =>['required']
            ];
            $v = Validator::make($data,$rules);
            if($v->fails()){
                return redirect()->back()->with(['status'=>"error"])->withErrors($v->errors())->withInput(['movie_id']);
            }
            $input['movie_id'] = $request->input('movie_id');
            $result=$this->movieRent->store($input);

            if(isset($result->getData()->status)){
                return redirect()->back()->with(['status'=>'error','message'=>$result->getData()->message]);
            }

            return redirect()->route('movies.index')->with(['status'=>"success",'message'=>"rent is success"]);
        }catch(\Exception $e){
            return redirect()->back()->with(['status'=>"error",'message'=>"Something went wrong"]);
        }
    }

    public function show(Movie  $movie){
        $rent_movie = null;
        if($movie)
        $rent_movie = RentMovie::where('user_id',Auth::user()->id)->where('movie_id',$movie->id)->first();

        $now  = Carbon::now();
        $check_rented = false;
        if($rent_movie){
            $check_rented = true;
        }
        $check_rentable = false;
        if(!$check_rented && $now>=$movie->rent_start && $now<=$movie->rent_end ){
            if(Auth::user()->subscription->plan->name == Plan::TYPE_PREMIUM){ // user is premium all is avaliable
                $check_rentable = true;
            }else{
                if($movie->plan->name != Plan::TYPE_PREMIUM){ //if user is basic plan , only basic plan is availble;
                    $check_rentable = true;
                }
            }
        }

        return view('show',['movie' => $movie,'rented'=>$check_rented,'rentable'=>$check_rentable]);
    }


}
