<?php

Route::group(
    ['namespace' => 'Auth'],
    function(){
        Route::get('login', 'LoginController@showLoginForm')->name('login');
        Route::post('login', 'LoginController@login');
        Route::any('logout', 'LoginController@logout')->name('logout');
        Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
        Route::post('register', 'RegisterController@register');
});
