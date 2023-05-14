<?php

namespace App\Http\Livewire;

use App\Models\Movie;
use Livewire\Component;
use Livewire\WithPagination;
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

        $data=$movie->orderBy('created_at','desc')->paginate(10);



        return view('livewire.search-movie',['searchResult'=>$data]);
    }
}
