<?php

namespace Tonkra\Referral;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tonkra\Referral\Http\Middleware\ReferralRedirectMiddleware;
use Tonkra\Referral\Repositories\Contracts\ReferralAccountRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralBaseRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralSubscriptionRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralUserRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralAccountRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralBaseRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralSubscriptionRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralUserRepository;

class ReferralServiceProvider extends ServiceProvider
{
	public function boot()
	{
		// Load routes from package
		$this->loadRoutesFrom(__DIR__ . '/routes/web.php');

		// Load views from package
		$this->loadViewsFrom(__DIR__ . '/resources/views', 'referral');

		// Load translations from package
		$this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'referral');

		// Publish config file
		$this->publishes([
			__DIR__ . '/config/referral.php' => config_path('referral.php'),
		], 'config');

		// Publish Migrations
		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/database/migrations/' => database_path('migrations'),
			], 'referral-migrations');

			// Publish Seeders
			$this->publishes([
				__DIR__ . '/database/seeders/' => database_path('seeders'),
			], 'referral-seeders');
		}

		// Run Migrations and Seeder Automatically (Optional)
		$this->runMigrations();
		$this->runSeeder();

		// Load Migrations
		$this->loadMigrationsFrom(__DIR__ . '/database/migrations');

		// Register middleware
		$this->registerMiddleware();
	}

	public function register()
	{
		// Merge default configuration
		$this->mergeConfigFrom(__DIR__ . '/config/referral.php', 'referral');

		$this->app->singleton('referral-settings', function () {
			return new ReferralSettings();
		});

		$this->bindRepositoryContracts();
	}

	protected function registerMiddleware()
	{
		$kernel = $this->app->make(Kernel::class);
		$kernel->pushMiddleware(ReferralRedirectMiddleware::class);
	}

	protected function bindRepositoryContracts()
	{

		$this->app->bind(
			ReferralUserRepository::class,
			EloquentReferralUserRepository::class
		);

		$this->app->bind(
			ReferralBaseRepository::class,
			EloquentReferralBaseRepository::class
		);

		$this->app->bind(
			ReferralAccountRepository::class,
			EloquentReferralAccountRepository::class
		);

		$this->app->bind(
			ReferralSubscriptionRepository::class,
			EloquentReferralSubscriptionRepository::class
		);
	}

	/**
	 * Automatically run the package migrations.
	 */
	protected function runMigrations()
	{
		$migrationPath = __DIR__ . '\\database\\migrations';

		// Check if migration files exist before running
		if (File::exists($migrationPath)) {
			Artisan::call('migrate', [
				'--path' => str_replace(base_path(), '', $migrationPath),
				'--force' => true
			]);
		}
	}

	/**
	 * Run the Referral Database Seeder.
	 */
	protected function runSeeder()
	{
		return Artisan::call('db:seed', ['--class' => 'Tonkra\\Referral\\Database\\Seeders\\ReferralDatabaseSeeder']);
	}
}
