<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\RentMovie;
use App\Repositories\Contracts\MovieRentRepository;
use Illuminate\Http\Request;
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

        return view('index'/*, [
            'movies' => $data,
            'rented_movie_ids' => $rented_movie_ids,
        ]*/);
    }

    public function rent(Request  $request){
        try{
            $data = $request->except('_token');
            $rules = [
                'movie_id'  =>['required']
            ];
            $v = Validator::make($data,$rules);
            if($v>fails()){
                return redirect()->back()->with(['status'=>"false"])->withErrors($v->errors())->withInput(['movie_id']);
            }
            $input['movie_id'] = $request->input('movie_id');
            $rent=$this->movieRent->store($input);
            return redirect()->route('movies.show')->with(['status'=>true,'message'=>"rent is success"]);
        }catch(\Exception $e){
            return redirect()->back()->with(['status'=>"false",'message'=>"Something went wrong"]);
        }
    }

    public function show($id){
        $movie = Movie::find($id);
        return view('show',['movie' => $movie]);
    }


}
