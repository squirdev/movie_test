<?php namespace DigiPig\MovieDB;

use Illuminate\Support\ServiceProvider;

class MovieDBServiceProvider extends ServiceProvider {


	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
        require __DIR__ . '/Http/routes.php';
	    $this->loadViewsFrom(__DIR__ . '/views', 'moviedb');

         $this->publishes([
        __DIR__ . '/views' => resource_path('views/vendor/moviedb'),
    ]);

	}


	/**
	 * Register the application services.
	 *
	 * @return MovieDB
	 */
	public function register()
	{
		$this->app->bind('moviedb', function($app){
            return new MovieDB;
        });
	}

}
