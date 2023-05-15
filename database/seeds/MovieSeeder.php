<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Library\IMDB;
use App\Models\Movie;
use App\Models\Cast;
class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imdb_url = "https://www.omdbapi.com";
        $apiKey = config('services.omdb.token');
        $imdb = new IMDB($imdb_url,$apiKey);
        $movie_datas =$imdb->seedMovie();

        $movies =new Movie();
        $casts = new Cast();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $movies->truncate();
            $casts->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $premiumKeys = [1,3,5,6,8];
        foreach ($movie_datas as $key=>$data){
            $plan_id=1;
            $rent_price =10;
            $rent_start = \Illuminate\Support\Carbon::now();
            $rent_end = \Illuminate\Support\Carbon::now()->addDays(1);

            if(in_array($key,$premiumKeys)){
                $rent_price =20;
                $plan_id =2;
            }

            $cast_name = $data['Actors'];
            if($cast_name == 'N/A'){
                $cast_name = null;
            }
            $cast = $casts->create([
                'names'    => $cast_name
            ]);
            $movie=$movies->create([
                'title'           =>  $data['Title'],
               'plan_id'          =>  $plan_id,
               'cast_id'          =>  $cast->id,
               'release_year'     =>  $data['Year'],
                'tag'             =>  Movie::TAG_MOVIE,
                'poster'          =>  $data['Poster'],
                'rent_start'     =>  $rent_start,
                'rent_end'      =>$rent_end,
                'rent_price'      =>  $rent_price,
                'imdbID'          =>  $data['imdbID'],
                'stramingId'     =>  null,
                'stream_url'      =>  null,
                'status'          => true,
            ]);
            $movie->save();
        }
    }
}
