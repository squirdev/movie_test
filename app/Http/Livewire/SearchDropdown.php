<?php

namespace App\Http\Livewire;

use App\Models\RentMovie;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use App\Models\Movie;
use Auth;
use Illuminate\Support\Facades\Http;

class SearchDropdown extends Component
{
    public $search = '';

    public function render()
    {
        $searchResults = [];
        if(strlen($this->search) >= 2) {
            $movie = new Movie();
            $movie = $movie->whereLike(['tag','title'],$this->search);
            $data = $movie->offset(0)->limit(10)->orderBy('created_at','desc')->get();
            $searchResults = $data;
        }

        return view('livewire.search-dropdown', [
            'searchResults' => collect($searchResults)->take(7)
        ]);
    }
}
