<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @method static where(string $string, string $uid)
 *
 * @property  string names
 *
 * */

class Cast extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'names'
    ];

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($cast) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $cast->uid     = $uid;
        });
    }

    /**
     * Find item by uid.
     *
     * @param $uid
     *
     * @return object
     */
    public static function findByUid($uid): object
    {
        return self::where('uid', $uid)->first();
    }

    /**
     * Get names.
     *
     * @return array
     */
    public function getNames()
    {
        if ( ! $this->names) {
            return [];
        }

        return explode(',', $this->names);
    }

    /**
     * Get names.
     *
     * @return array
     */
    public function updateNames($data)
    {
        $_data = implode(',',$data);
        $this->names = $_data;
        $this->save();
    }

    /**
     * movie associations
     *
     * @return BelogsTo
     *
     * */

    public function movie(){
        return $this->belongsTo(Movie::class,'movie_id','id');
    }


}
