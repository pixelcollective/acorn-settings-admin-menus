<?php

namespace TinyPixel\AdminMenu\Providers;

use \TinyPixel\AdminMenu\AdminMenu;
use \TinyPixel\AdminMenu\AdminBar;
use \Illuminate\Support\Collection;

use function \Roots\config_path;

use \Roots\Acorn\ServiceProvider;

class AdminMenuServiceProvider extends ServiceProvider
{
    /**
      * Register any application services.
      *
      * @return void
      */
    public function register()
    {
        $this->app->singleton('wordpress.admin-menu', function () {
            return new AdminMenu($this->app);
        });

        $this->app->singleton('wordpress.admin-bar', function () {
            return new AdminBar($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/admin-menu.php' => config_path('admin-menu.php')]);

        $this->app->make('wordpress.admin-menu')->configureMenu(Collection::make(
            $this->app['config']->get('admin-menu.admin_menu')
        ));

        $this->app->make('wordpress.admin-bar')->configureBar(Collection::make(
            $this->app['config']->get('admin-menu.admin_bar')
        ));
    }
}
