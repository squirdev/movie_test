<?php

namespace DigiPig\MovieDB;

use Illuminate\Support\Facades\Facade;

class MovieDBFacade extends Facade
{
    protected static function getFacadeAccessor() { 
        return 'moviedb';
    }
}
