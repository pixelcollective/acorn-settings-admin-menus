<?php

namespace TinyPixel\Settings\Providers;

use \TinyPixel\Settings\AdminMenu;
use \TinyPixel\Settings\AdminBar;
use \TinyPixel\Settings\OptionsPages;

use \Illuminate\Support\Collection;

use function \Roots\config_path;
use \Roots\Acorn\ServiceProvider;

/**
 * Admin menu services provider
 *
 * @author Kelly Mears <kelly@tinypixel.dev>
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
        $config = __DIR__ . '/../config/admin-menu.php';

        $this->publishes([$config => config_path('wordpress/admin-menu.php')]);

        $this->settings = (object) [
            'menu'    => Collection::make($this->app['config']->get('wordpress.admin-menu.admin_menu')),
            'bar'     => Collection::make($this->app['config']->get('wordpress.admin-menu.admin_bar')),
            'options' => Collection::make($this->app['config']->get('wordpress.admin-menu.options_pages')),
        ];

        $this->services = (object) [
            'menu'    => $this->app->make('wordpress.admin-menu'),
            'bar'     => $this->app->make('wordpress.admin-bar'),
            'options' => $this->app->make('wordpress.options-pages'),
        ];

        $this->services->menu->init($this->settings->menu);
        $this->services->bar->init($this->settings->bar);

        $this->settings->options->each(function ($page) {
            $this->services->options->addPage(...$page);
        });
    }
}
