<?php

namespace TinyPixel\AdminMenu;

use function \add_action;
use function \remove_menu_page;
use function \remove_submenu_page;
use function \current_user_can;

use \Roots\Acorn\Application;
use \Illuminate\Support\Collection;
use \TinyPixel\Support\WordPress\Admin\Traits;

/**
 * Admin menu
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   0.0.1
 */
class AdminMenu
{
    /**
     * @var $menuSlugs
     */
    use Traits\MenuSlugs;

    /**
     * Filepath to file which contains CSS to hide admin menu
     *
     * @var string
     */
    public $css = __DIR__ . '/templates/killadminmenu.php';


    /**
     * Construct
     *
     * @param \Roots\Acorn\Application
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Initializes admin menu class
     *
     * @param \Illuminate\Support\Collection $config
     * @return void
     */
    public function init(Collection $config)
    {
        $this->settings = $config;
        $this->env = $this->app['config']->get('app.env');

        if (isset($this->settings['enabled'])
        && $this->settings['enabled'] == false) {
            add_action('admin_print_styles-index.php', [$this, 'killMenu']);
            add_action('admin_print_styles-profile.php', [$this, 'killMenu']);
        }

        add_action('admin_menu', [$this, 'processAdminMenu'], 999);
    }

    /**
     * Processes admin menu items
     *
     * @return void
     */
    public function processAdminMenu()
    {
        collect($this->settings['menu_items'])->each(function ($setting, $menuItem) {
            $global      = $setting['enabled'][0];
            $environment = $setting['enabled'][1];
            $capability  = $setting['enabled'][2];

            /**
             * Remove menu items
             *
             * ...if global is set but isn't set to display
             */
            if (isset($global) && $global !== "display") {
                remove_menu_page($this->menuSlugs[$menuItem]['menuItem']);

            /**
             * ...if environment is set but isn't set to display
             */
            } elseif (!empty($environment) && !$this->clear($environment)) {
                remove_menu_page($this->menuSlugs[$menuItem]['menuItem']);

            /**
             *  ...or, current user capabilities are set but user does not have them
             */
            } elseif (!empty($capability) && !$this->clear($capability)) {
                remove_menu_page($this->menuSlugs[$menuItem]['menuItem']);

            /**
             * Otherwise, the parent menu doesn't need to be hidden
             * and it makes sense to continue checking its children for validity
             */
            } else {
                $this->processSubMenuItems($setting, $menuItem);
            }
        });
    }

    /**
     * Processes submenu items for a given parent menu item
     *
     * @param  $setting
     * @param  $menuItem
     * @return void
     */
    public function processSubMenuItems($setting, $menuItem)
    {
        collect($setting['sub_menu_items'])->each(function ($subMenuSetting, $subMenuItem) use ($menuItem) {
            $global      = $subMenuSetting[0];
            $environment = $subMenuSetting[1];
            $capability  = $subMenuSetting[2];

            /**
             * Remove menu items
             *
             * ...if global is set but isn't set to display
             */
            if (isset($global) && $global !== 'display') {
                $this->removeSubMenuItem($menuItem, $subMenuItem);

            /**
             * ...if environment is set but isn't set to display
             */
            } elseif (!empty($environment && !$this->clear($environment))) {
                $this->removeSubMenuItem($menuItem, $subMenuItem);

            /**
             *  ...or, current user capabilities are set but user does not have them
             */
            } elseif (!empty($capability) && !$this->clear($capability)) {
                $this->removeSubMenuItem($menuItem, $subMenuItem);
            }
        });
    }

    /**
     * Removes a submenu item from menu
     *
     * @param  string $menuItem
     * @param  string $subMenuItem
     * @return void
     */
    public function removeSubMenuItem($menuItem, $subMenuItem)
    {
        remove_submenu_page(
            $this->menuSlugs[$menuItem]['subMenuItems'][$subMenuItem][0],
            $this->menuSlugs[$menuItem]['subMenuItems'][$subMenuItem][1]
        );
    }

    /**
     * Validates menu items
     *
     * @param string $item menu item setting
     */
    public function clear($item)
    {
        if (isset($item)) {
            /**
             * Return true if globally visible
             */
            if ($item == 'display') {
                return true;

            /**
             * Return true if env is set and a match
             */
            } elseif (isset($item[$this->env])) {
                return true;

            /**
             * Return true if capability type is set and a match
             */
            } elseif (!empty($item) && current_user_can(...$item)) {
                return true;
            }

            return false;
        }
    }

    /**
     * Prints CSS to set visibility of menu to none
     *
     * @return void
     */
    public function killMenu()
    {
        if (file_exists($this->css)) {
            print file_get_contents($this->css);
        }
    }
}
