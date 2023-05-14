<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{

    /**
        * The attributes that should be mutated to date.
        *
        * @var array
     */
    const TAG_MOVIE ='movie';
    const TAG_MUSIC ='music';
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable=[
        'title',
        'uid',
        'release_year',
        'tag',
        'poster',
        'rent_start',
        'rent_end',
        'rent_price',
        'imdbID',
        'status',
        'plan_id'
    ];
    /**
     *  The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'status'    => 'boolean',
        'release_year' => 'integer',
        'rent_start' => 'date',
        'rent_end'  =>  'date'
    ];

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($movie) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $movie->uid     = $uid;
        });
    }
    /**
     * one to one
     *
     *
     * */
    public function plan(){
        return $this->belongsTo(Plan::class,'plan_id','id');
    }

    /**
     * one to one relation ship with cast
     *
     * */

    public function cast(){
        return $this->belongsTo(Cast::class,'cast_id','id');
    }
}
