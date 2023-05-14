<?php namespace DigiPig\MovieDB\Http;

use Illuminate\Routing\Controller as BaseController;

class MovieDBController extends BaseController
{

    /**
     * MovieDBController::getMovie()
     *
     * @param int $IMDBID
     * @return
     */
    public function getMovie($IMDBID)
    {
        $MovieObject = \MovieDB::getMovie($IMDBID);

        return view('moviedb::movie', $MovieObject);
    }
}
