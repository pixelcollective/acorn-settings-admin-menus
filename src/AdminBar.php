<?php

namespace TinyPixel\Settings;

use \WP_Admin_Bar;
use function \add_action;

use \Illuminate\Support\Collection;

use \Roots\Acorn\Application;

/**
 * Admin bar
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   0.0.1
 */
class AdminBar
{
    /**
     * Admin bar nodes
     *
     * @var array
     */
    public $nodes = [
        'updates',
        'comments',
        'new-content',
        'wp-logo',
        'site-name',
        'my-account',
        'search',
        'customize',
        'wp-logo',
    ];

    /**
     * Filepath to file which contains CSS to hide admin bar
     *
     * @var string
     */
    public $css = __DIR__ . '/templates/killadminbar.php';


    /**
     * Construct
     *
     * @param \Roots\Acorn\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Initializes class
     *
     * @param \Illuminate\Support\Collection $config
     * @return void
     */
    public function init(Collection $config)
    {
        $this->settings  = $config;
        $this->nodes     = Collection::make($this->nodes);
        $this->menuItems = Collection::make($this->settings->get('menu_items'));
        $this->enabled   = $this->settings->get('enabled');

        if ($this->enabled == false) {
            add_action('admin_bar_menu', [$this, 'removeAllAdminBarItems'], 999);
            add_action('admin_print_styles-index.php', [$this, 'killBar']);
            add_action('admin_print_styles-profile.php', [$this, 'killBar']);
        } else {
            add_action('admin_bar_menu', [$this, 'removeAdminBarItems'], 999);
        }
    }

    /**
     * Removes items from the admin bar
     *
     * @param  \WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function removeAdminBarItems(WP_Admin_Bar $wp_admin_bar)
    {
        $this->adminBar = $wp_admin_bar;

        $this->menuItems->each(function ($value, $item) {
            if ($value !== true) {
                $this->adminBar->remove_node($item);
            }
        });
    }

    /**
     * Removes all items from the admin bar
     *
     * @param  \WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function removeAllAdminBarItems(WP_Admin_Bar $wp_admin_bar)
    {
        $this->adminBar = $wp_admin_bar;

        $this->nodes->each(function ($node) {
            $this->adminBar->remove_node($node);
        });
    }

    /**
     * Prints CSS to hide admin bar
     *
     * @return void
     */
    public function killBar()
    {
        if (file_exists($this->css)) {
            print file_get_contents($this->css);
        }
    }
}
