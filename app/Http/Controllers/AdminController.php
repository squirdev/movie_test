<?php

namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\Movie;
use App\Models\Plan;
use App\Repositories\Contracts\CastRepository;
use App\Repositories\Contracts\MovieRentRepository;
use App\Repositories\Contracts\MovieRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class AdminController extends Controller
{
    /**
     * @var MovieRentRepository $movie_rent
     * @var MovieRepository $movies
     * @var CastRepository $casts
     *
     * */

    protected $movie_rent;
    protected $movies;
    protected $casts;

    public function __construct(MovieRentRepository $movie_rent,MovieRepository $movies,CastRepository $casts)
    {
        $this->movie_rent = $movie_rent;
        $this->movies = $movies;
        $this->casts = $casts;
    }

    /**
     * @return Application|Factory|View
     * @throws AuthorizationException
     */

    public function index()
    {
        $movies = Movie::offset(0)->limit(10)->orderBy('created_at','desc')->with('plan')->get();
        return view('admin/home');

    }
    /**
     * edit existing movie
     *
     * @param  Movie  $movie
     *
     * @return ApplicationAlias|Factory|RedirectResponse|View
     * @throws AuthorizationException
     */
    public function edit(Movie $movie){
        $movie = $movie->toArray();
        if ( ! is_array($movie)) {
            return redirect()->route('admin.movies.index')->with([
                'status'  => 'error',
                'message' => "movie not found",
            ]);
        }
        $plans = Plan::orderBy('id','desc')->get();
        $cast = Cast::find($movie['cast_id']);

        return view('admin.edit',['movie'=>$movie,'cast'=>$cast,"plans"=>$plans]);

    }
    /**
     * view all customers
     *
     * @param  Request  $request
     *
     * @return void
     * @throws AuthorizationException
     */

    public function search(Request $request): void{
        $search = $request->input('search.value');
        $limit = $request->input('length');
        $start = $request->input('start');



        $movie = new Movie();
        $totalData = Movie::count();
        $totalFiltered = $totalData;
        if(!empty($search)){
            $movie = $movie->whereLike(['tag','title'],$search);
            $totalFiltered = Movie::whereLike(['tag','title'],$search)->count();
        }
        $data = $movie->offset($start)->limit($limit)->orderBy('created_at','desc')->get();

        $result=[];

        if(!empty($data)){
            foreach($data as $item){
                $edit = route('admin.movies.edit',$item->id);
                if($item->status){
                    $status = 'checked';
                }else{
                    $status ='';
                }

                if($item->plan->name == Plan::TYPE_PREMIUM){
                    $color="primary";
                    $type = Plan::TYPE_PREMIUM;
                }else{
                    $color="info";
                    $type = Plan::TYPE_BASIC;
                }

                $nestedData['responsive_id']    =   '';
                $nestedData['uid']              =   $item->uid;
                $nestedData['title']            =   $item->title;
                $nestedData['id']               =   $item->id;
                $nestedData['poster']           =   "<img width='50' height='50' src='$item->poster'/>";
                $nestedData['cast']             =   $item->cast->names;
                $nestedData['price']            =   $item->rent_price;
                $nestedData['rent_start']       =   $item->rent_start;
                $nestedData['rent_end']         =   $item->rent_end;
                $nestedData['type']          = "<span class='badge py-2 text-uppercase bg-$color'>{$type}</span>";
                $nestedData['status']           =  "<div class='form-check form-switch form-check-primary'>
                <input type='checkbox' class='form-check-input get_status' id='status_$item->id' data-id='$item->id' name='status' $status>
                <label class='form-check-label' for='status_$item->id'>
                  <span class='switch-icon-left'><i data-feather='check'></i> </span>
                  <span class='switch-icon-right'><i data-feather='x'></i> </span>
                </label>
              </div>";
                $nestedData['edit'] = $edit;
                $result[]   =$nestedData;
            }
            $json_data = [
                "draw"            => intval($request->input('draw')),
                'recordsTotal'  => $totalData,
                'recordsFiltered'   => $totalFiltered,
                'data'      => $result
            ];

            echo json_encode($json_data);

            exit();
        }
    }

    /**
     * Create New Movie
     *
     * @param $type
     *
     * @return ApplicationAlias|Factory|View
     *
     * @throws AuthorizationException
     */

    public function create(){
        $plans = Plan::orderBy('id','desc')->get();
        return view('admin.edit',['plans'=>$plans]);
    }

    /**
     * change move status
     *
     * @param  Movie  $movie
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public  function activeToggle(Movie $movie){

        $result=$movie->update(['status' => !$movie->status ]);


        return response()->json([
            'status'  => 'success',
            'message' => "Status change is success!",
        ]);
    }


    public function store(Request $request){
        $rules = [
            'title'         =>  ['required','string', 'min:1'],
            'plan'          =>  ['required','numeric'],
            'rent_price'    =>  ['required','string' ],
            'rent_start'    =>  ['required','string','min:1'],
            'rent_end'      =>  ['required','string','min:1'],
            'tag'           =>  ['required','string' ,'min:1'],
            'poster'        =>  ['required','string',"min:1"],
            'release_year'  =>  ['required',"string"],
            'status'        =>  ['required']
        ];
        $data = $request->except('token');
        $v = Validator::make($data,$rules);
        if($v->fails()){
            return redirect()->back()->withErrors($v->errors())->withInput(['id','names','plan','rent_start','rent_end','tag','poster','release_year','status']);
        }

        try{
            if( isset($data['status']) && $data['status']=="on"){
                $data['status']= true;
            }else{
                $data['status'] = false;
            }
            $this->movies->store($data);
            return redirect()->route('admin.movies.index')->with(['status'=>'success','message'=>"create movie is sussccess"]);
        }catch(\Exception $error){
            return redirect()->back()->with(['status'=>"error",'message'=>'something went wrong'])->withInput(['names','plan','rent_start','rent_end','tag','poster','release_year']);
        }

    }
    public function update(Request $request,Movie $movie){
        $rules = [
            'title'         =>  ['required','string'],
            'plan'          =>  ['required','numeric'],
            'rent_price'    =>  ['required','numeric'],
            'rent_start'    =>  ['required','string'],
            'rent_end'      =>  ['required','string'],
            'tag'           =>  ['required','string'],
            'poster'        =>  ['required','string'],
            'release_year'  =>  ['required','string'],
            'status'        =>  ['required']
        ];

        $data = $request->except('token');
        $v = Validator::make($data,$rules);
        if($v->fails()){
            return redirect()->back()->withErrors($v->errors())->withInput(['id','names','plan','rent_start','rent_end','tag','poster','release_year','status']);
        }

        try{
            if( isset($data['status']) && $data['status']=="on"){
                $data['status']= true;
            }else{
                $data['status'] = false;
            }
            $movie=$this->movies->update($movie,$data);
            return redirect()->route('admin.movies.index')->with(['status'=>'success','message'=>"update is sussccess"]);
        }catch(\Exception $error){
            return redirect()->back()->with(['status'=>false,'message'=>'please input data correctly'])->withInput(['id','names','plan','rent_start','rent_end','tag','poster','release_year','status']);
        }

    }

    public function destroy(Movie $movie){
            $this->movies->destroy($movie);
            return response()->json([
                'status'  => 'success',
                'message' => 'delete is susscess',
            ]);
    }



}
