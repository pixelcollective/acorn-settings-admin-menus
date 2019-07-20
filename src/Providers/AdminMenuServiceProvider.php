<?php

namespace TinyPixel\AdminMenu\Providers;

use \TinyPixel\AdminMenu\AdminMenu;
use \TinyPixel\AdminMenu\AdminBar;
use \TinyPixel\AdminMenu\OptionsPages;
use \Illuminate\Support\Collection;

use function \Roots\config_path;

use \Roots\Acorn\ServiceProvider;

/**
 * Admin menu services provider
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   0.0.1
 */
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

        $this->app->singleton('wordpress.options-pages', function () {
            return (new OptionsPages($this->app))->init();
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

        $this->config = (object) [
            'menu' => Collection::make($this->app['config']->get('admin-menu.admin_menu')),
            'bar' => Collection::make($this->app['config']->get('admin-menu.admin_bar')),
            'optionsPages' => Collection::make($this->app['config']->get('admin-menu.options_pages')),
        ];

        $this->services = (object) [
            'menu' => $this->app->make('wordpress.admin-menu'),
            'bar'  => $this->app->make('wordpress.admin-bar'),
            'optionsPages' => $this->app->make('wordpress.options-pages'),
        ];

        $this->services->adminMenu->init($this->config->menu);
        $this->services->adminBar->init($this->config->bar);

        $this->config->optionsPages->each(function ($page) {
            $this->services->optionsPages->addPage(...$page);
        });
    }
}
