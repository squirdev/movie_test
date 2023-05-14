<?php

use Illuminate\Support\Facades\Route;

Route::get('/',function(){
    return redirect('login');
});
Route::get('/home', 'HomeController@index')->name('home');
