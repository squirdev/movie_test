<?php


namespace App\Library;


use Illuminate\Support\Facades\Http;

class IMDB
{
    private $apiKey;
    private $imdbUrl;
    public function __construct($url,$_apikey)
    {
        $this->apiKey = $_apikey;
        $this->imdbUrl = $url;
    }

    public function seedMovie(){
        $imdbIds=[
            'tt0068162','tt0264330','tt1484065',
            'tt1669078','tt0307911','tt0356319',
            'tt3407278','tt0286565','tt15237160',
            'tt1459820','tt4879050','tt23181916',
            'tt18815122','tt21399834','tt5218910',
            'tt5354114','tt5526478','tt5791360',
            'tt6031646','tt7657398','tt11776314',
            'tt12414044','tt14316180','tt15176324',
            'tt1610295','tt4431128','tt4787488'];
//        $imdbIds=[
//            'tt0068162','tt0264330','tt1484065'];
        $responses =[];
            if(is_array($imdbIds) && count($imdbIds)>0){
                foreach ($imdbIds as $imdbId){

                    $response = $this->fullMovieById($imdbId);
                    if($response['Response']){
                        $responses[] = $response;
                    }
                }
            }

            return $responses;
    }
    public function fullMovieById($id){
        $response = Http::get($this->imdbUrl,[
            'apikey'=>$this->apiKey,
            'i'=>$id,
            'plot'=>"short"
        ])->json();

        return $response;
    }
    public function fullMovieByTitle($name,$tag="movie",$page=1){
        $response = Http::get($this->imdbUrl,[
            'apikey'=>$this->apiKey,
            't'=>$name,
            'type'=>$tag,
            'page'=>$page,
            'plot'=>"short"
        ])->json();
        return $response;
    }

    public function searchMovie($search,$name="aaa",$tag="movie",$page=3){
        $response = Http::get($this->imdbUrl,[
            'apikey'=>$this->apiKey,
            's'=>$search,
            't'=>$name,
            'type'=>$tag,
            'page'=>$page,
            'plot'=>"short"
        ])->json();
        return $response;
    }
}
