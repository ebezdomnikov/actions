<?php namespace Actions;


use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


class ActionsServiceProvider extends ServiceProvider
{
	/**
	 * @var string
	 */
	protected $namespace = 'Actions\Controllers';

	/**
	 * Bootstrap any application services.
	 *
	 * @param Router $router
	 */
	public function boot(Router $router)
	{
		parent::boot($router);
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// Load Ratchet Service Provider
		$this->app->register(\Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class);

		$this->app->singleton('Actions', function() {
			return new \Actions\Factory\Actions();
		});

		$this->app->singleton('Actions.CollectionFactory', function() {
			return new \Actions\Factory\CollectionFactory();
		});

		$this->app->singleton('Actions.ModificatorsFactory', function() {
			return new \Actions\Factory\ModificatorsFactory();
		});

		$this->app->singleton('Actions.RemoteLogic', function() {
			return new \Actions\Factory\RemoteLogic();
		});

        $this->app->singleton('Actions.ItemFactory', function() {
            return new \Actions\Factory\ItemFactory();
        });

	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => $this->namespace], function ($router) {
			require __DIR__ . '/routes.php';
		});
	}
}
