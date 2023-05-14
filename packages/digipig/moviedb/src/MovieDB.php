<?php namespace DigiPig\MovieDB;
 
class MovieDB {
 
    private $plot = 'short';
    private $tomatoes = 'true';
    private $type = 'movie'; 
  
  
    /**
     * MovieDB::getMovie()
     * 
     * @param int $IMDBID
     * @return Array
        (
            [Title] => string
            [Year] => string
            [Rated] => string
            [Released] => string
            [Runtime] => string
            [Genre] => string
            [Director] => string
            [Writer] => string
            [Actors] => string
            [Plot] => string
            [Language] => string
            [Country] => string
            [Awards] => string
            [Poster] => string
            [Metascore] => int
            [imdbRating] => int
            [imdbVotes] => int
            [imdbID] => string
            [Type] => string
            [tomatoMeter] => int
            [tomatoImage] => string
            [tomatoRating] => int
            [tomatoReviews] => int
            [tomatoFresh] => int
            [tomatoRotten] => int
            [tomatoConsensus] => string
            [tomatoUserMeter] => int
            [tomatoUserRating] => int
            [tomatoUserReviews] => int
            [tomatoURL] => string
            [DVD] => string
            [BoxOffice] => string
            [Production] => string
            [Website] => string
            [Response] => boolean
        )
     */
     
    function getMovie($IMDBID) { 
       return $this->getCURL('http://www.omdbapi.com/?i='.$IMDBID.'&plot='.$this->plot.'&r=json&type='.$this->type.'&tomatoes='.$this->tomatoes);
    }
    
 
    /**
     * MovieDB::getCURL()
     * 
     * @param mixed $CURLOPT_URL
     * @return
     */
    function getCURL($CURLOPT_URL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $CURLOPT_URL);
        $result = curl_exec($ch);
        curl_close($ch);    
        return json_decode($result,true);         
    }
}