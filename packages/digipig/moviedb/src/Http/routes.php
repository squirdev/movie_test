<?php 
Route::get('/moviedb/{imdb}', '\DigiPig\MovieDB\Http\MovieDBController@getMovie');
 