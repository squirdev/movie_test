<?php

namespace App\Http\Livewire;

use App\Models\Movie;
use App\Models\RentMovie;
use Livewire\Component;
use Livewire\WithPagination;
use Auth;
class SearchMovie extends Component
{
    use WithPagination;

//    protected $paginationTheme = 'bootstrap';
    public $search = '';
    public function render()
    {
        $movie = new Movie();
        if(strlen($this->search)>0){
            $movie = $movie->whereLike(['tag','title'],$this->search);
        }

        $data=$movie->orderBy('created_at','desc')->paginate(8);
        $rented_movie_ids = RentMovie::where('user_id',Auth::user()->id)->pluck('movie_id')->toArray();


        return view('livewire.search-movie',['searchResult'=>$data,'rented_ids'=>$rented_movie_ids]);
    }
}
