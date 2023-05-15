<?php


Route::group(['prefix'=>'movies'],function(){
    Route::get('/', 'MoviesController@index')->name('index');
    Route::get('/show/{movie}', 'MoviesController@show')->name('show');
    Route::post('rent','MoviesController@rent')->name('rent');
});
