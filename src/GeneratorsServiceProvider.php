<?php

namespace veyselsahin\l5generators;

use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//

	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{

		$this->registerScaffoldGenerator();

	}


	/**
	 * Register the make:scaffold generator.
	 */
	private function registerScaffoldGenerator()
	{
		$this->app->singleton('command.larascaf.scaffold', function ($app) {
			return $app['Veyselsahin\L5generators\Commands\ScaffoldMakeCommand'];
		});

		$this->commands('command.larascaf.scaffold');
	}


}