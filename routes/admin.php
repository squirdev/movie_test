<?php

 Route::get('/',function(){
     return redirect()->route('admin.movies.index');
 })->name('home');
 Route::post('/search','AdminController@search')->name('movies.search');
 Route::resource('movies','AdminController',[
     'only'=> ['index','create','store','update','destroy']
 ]);
 Route::post('/movies/{movie}/active','AdminController@activeToggle')->name('movies.activeToggle');
 Route::get('/movies/{movie}/edit',"AdminController@edit")->name('movies.edit');


