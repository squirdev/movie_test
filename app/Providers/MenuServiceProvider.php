<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $menu=[


                [
                    'url' => '/admin/movies',
                    'icon'=>'folder',
                    'name'=>'Movies',
                ],
//                [
//                    'url' => '#',
//                    'icon'=>'support',
//                    'name'=>'Support',
//                ],

        ];
        $verticalMenuData = json_decode(json_encode($menu));

        // Share all menuData to all the views
        \View::share('menuData', [$verticalMenuData, $verticalMenuData]);
    }
}
