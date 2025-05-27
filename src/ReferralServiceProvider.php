<?php

namespace Tonkra\Referral;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Tonkra\Referral\Http\Middleware\ReferralRedirectMiddleware;
use Tonkra\Referral\Providers\ReferralMenuServiceProvider;
use Tonkra\Referral\Providers\ReferralPermissionServiceProvider;
use Tonkra\Referral\Repositories\Contracts\ReferralBaseRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralUserRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralAccountRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralCustomerRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralSettingsRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralBaseRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralUserRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralSubscriptionRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralAnnouncementsRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralAccountRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralCustomerRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralSettingsRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralSubscriptionRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralAnnouncementsRepository;

class ReferralServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->registerConfig();
		$this->registerMigrations();
		$this->registerViews();
		$this->registerTranslations();
		$this->registerRoutes();
		$this->registerEvents();
		$this->registerBladeDirectives();
		$this->registerMiddleware(); 
	}

	public function register()
	{
		// Merge default configuration
		$this->mergeConfigFrom(__DIR__ . '/config/referral.php', 'referral');

		$this->app->singleton('referral-settings', function () {
			return new ReferralSettings();
		});

		// // Dynamically register other Service Providers
		$this->app->register(ReferralMenuServiceProvider::class);
		$this->app->register(ReferralPermissionServiceProvider::class);

		$this->bindRepositoryContracts();
	}

	protected function registerConfig()
	{
		$this->publishes([__DIR__ . '/config/referral.php' => config_path('referral.php')], 'referral-config');
	}

	protected function registerMigrations()
	{
		// $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
		if ($this->app->runningInConsole()) {
			// Publish Migrations
			$this->publishes([__DIR__ . '/database/migrations/' => database_path('migrations')], 'referral-migrations');

			// Publish Seeders
			$this->publishes([__DIR__ . '/database/seeders/' => database_path('seeders')], 'referral-seeders');
		}
	}

	protected function registerViews()
	{
		$this->loadViewsFrom(__DIR__ . '/resources/views', 'referral');

		$this->publishes([__DIR__ . '/resources/views' => resource_path('views/tonkra/referral')], 'referral-views');
	}

	protected function registerTranslations()
	{
		// Load translations from package
		$this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'referral');

		// Get all language folders from package
		$langPath = __DIR__ . '/resources/lang';
		$langFolders = glob($langPath . '/*', GLOB_ONLYDIR);

		$publishPaths = [];

		foreach ($langFolders as $folder) {
			$language = basename($folder);
			$sourceFile = $folder . '/locale.php';
			$destinationFile = resource_path("lang/{$language}/referral.php");

			if (file_exists($sourceFile)) {
				$publishPaths[$sourceFile] = $destinationFile;
			}
		}

		$this->publishes($publishPaths, 'referral-lang');
	}

	protected function registerRoutes()
	{
		$this->loadRoutesFrom(__DIR__ . '/routes/web.php', 'referral');
		// Route::group($this->routeConfiguration(), function () {
		// 	$this->loadRoutesFrom(__DIR__ . '/routes/web.php');
		// });
	}

	protected function routeConfiguration()
	{
		return [
			'prefix' => 'admin/referral',
			'middleware' => ['web', 'auth'],
		];
	}

	protected function registerEvents()
	{
		// We'll add event listeners later
	}

	protected function registerBladeDirectives()
	{
		Blade::directive('referralScripts', function () {
			return "<?php echo \$__env->make('referral::scripts')->render(); ?>";
		});
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

		$this->app->bind(
			ReferralSettingsRepository::class,
			EloquentReferralSettingsRepository::class
		);

		$this->app->bind(
			ReferralCustomerRepository::class,
			EloquentReferralCustomerRepository::class
		);

		$this->app->bind(
			ReferralAnnouncementsRepository::class,
			EloquentReferralAnnouncementsRepository::class
		);
	}
}
