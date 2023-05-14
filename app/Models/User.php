<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin'    => 'boolean',
        'is_customer' => 'boolean',
        'status'      => 'boolean',
    ];


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

    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

        });
    }

    /**
     * Check if user has admin account.
     */
    public function isAdmin(): bool
    {
        return 1 == $this->is_admin;
    }

    /**
     * Check if user has admin account.
     */
    public function isCustomer(): bool
    {
        return 1 == $this->is_customer;
    }

    /*
    *  Display User Name
    */
    public function displayName(): string
    {
        return $this->name;
    }

    /**
     * Upload and resize avatar.
     *
     * @param $file
     *
     * @return string
     */
    public function uploadImage($file): string
    {
        $path        = 'app/profile/';
        $upload_path = storage_path($path);

        if ( ! file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $filename = 'avatar-'.$this->id.'.'.$file->getClientOriginalExtension();

        // save to server
        $file->move($upload_path, $filename);

        // create thumbnails
        $img = Image::make($upload_path.$filename);

        $img->fit(120, 120, function ($c) {
            $c->aspectRatio();
            $c->upsize();
        })->save($upload_path.$filename.'.thumb.jpg');

        return $path.$filename;
    }

    /**
     * Get image thumb path.
     *
     * @return string
     *
     */
    public function imagePath(): string
    {
        if ( ! empty($this->image) && ! empty($this->id)) {
            return storage_path($this->image).'.thumb.jpg';
        } else {
            return '';
        }
    }

    /**
     * Get image thumb path.
     *
     * @return void
     */
    public function removeImage()
    {
        if ( ! empty($this->image) && ! empty($this->id)) {
            $path = storage_path($this->image);
            if (is_file($path)) {
                unlink($path);
            }
            if (is_file($path.'.thumb.jpg')) {
                unlink($path.'.thumb.jpg');
            }
        }
    }

    public function getCanEditAttribute(): bool
    {
        return 1 === auth()->id();
    }

    public function getCanDeleteAttribute(): bool
    {
        return $this->id !== auth()->id() && (Gate::check('delete customer'));
    }

    public function getIsSuperAdminAttribute(): bool
    {
        return 1 === $this->id;
    }

    /**
     * one-to-one relations with subscription.
     *
     * @return HasOne
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class,'user_id','id')->orderBy('created_at','desc');
    }

    /**
     *
     * ont-to-many relations with rent movie
     * @return HasMany
     * */

    public function rentMovies():HasMany
    {
        return $this->hasMany(RentMovie::class,'user_id','id')->orderBy('created_at','desc');
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasRole($name): bool
    {
        return $this->roles->contains('name', $name);
    }

    /**
     * @return Collection
     */

    public function getPermissions(): Collection
    {
        $permissions = [];

        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                if ( ! in_array($permission, $permissions, true)) {
                    $permissions[] = $permission;
                }
            }
        }

        return collect($permissions);
    }

    /**
     * get route key by uid
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uid';
    }

}
