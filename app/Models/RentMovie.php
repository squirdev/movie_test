<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentMovie extends Model
{
    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($rent_movie) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $rent_movie->uid     = $uid;
        });
    }
}
