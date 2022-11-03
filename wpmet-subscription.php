<?php 

/**
 * Plugin Name: Wpmet Subscription
 * Description: Wpmet Subscription Manager is a WooCommerce Subscription System.
 * Version: 1.0
 * Author: Wpmet
 * Author URI: http://wpmet.com
 *
 * WC requires at least: 3.0
 * WC tested up to: 6.7.0
 *
 * Copyright: © 2022 wpmet.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wpment-subscription
 * Domain Path: /languages
 */

use Wpmet\WpmetSubscription\Admin;
use Wpmet\WpmetSubscription\Frontend\Frontend;
use Wpmet\WpmetSubscription\Setup\Setup;

 if (! defined('ABSPATH' ) ) {
    die;
 }

 require_once __DIR__ . '/vendor/autoload.php';


 final class WpmetSubscription {
   /**
     * plugin version
     *
     * @var string
     */
    const VERSION = '1.0';

    public function __construct() {

        $this->define_constants();
        $this->initPluginDependencies();

        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('plugins_loaded', [$this, 'initPlugin']);
    }

    /**
	 * initializes a singleton instance
	 */
	public static function init() {
		
		static $instance = false;
		new Setup();

		if ( !$instance ) {
			$instance = new self();
		}

		return $instance;
	}

    public function initPlugin() {
        if (is_admin()) {
            new Admin();
			new Frontend();
        }
    }

    /**
	 * Do stuff upon plugin activation
	 * 
	 * @return void
	 */
	public function activate() {
	}

    /**
     * Plugin Dependencies Check
     *
     * @return void
     */
    private function initPluginDependencies()
    {
        add_action('init', array($this, 'preventHeaderSentProblem'), 1);
        add_action('admin_notices', array($this, 'woocommerceDependencyNotices'));
    }

    /**
	 * Output a admin notice when plugin dependencies not met.
	 */
	public function woocommerceDependencyNotices() {
		$return = $this->wcPluginCheck( true );

		if ( true !== $return && current_user_can( 'activate_plugins' ) ) {
			$dependency_notice = $return;
			printf( '<div class="error"><p>%s</p></div>', wp_kses_post( $dependency_notice ) );
		}
	}

    	/**
	 * Check whether the plugin dependencies met.
	 * 
	 * @return bool|string True on Success
	 */
	private function wcPluginCheck( $return_dep_notice = false ) {
		$return = false;

		if ( is_multisite() && is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_wc_active = true;
		} else if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_wc_active = true;
		} else {
			$is_wc_active = false;
		}

		// WC check.
		if ( ! $is_wc_active ) {
			if ( $return_dep_notice ) {
				$return = 'Wpmet Subscription plugin requires WooCommerce Plugin should be Active !!!';
			}

			return $return;
		}

		return true;
	}

    /**
	 * remove header problem while plugin activates
	 */
	public function preventHeaderSentProblem() {
		ob_start();
	}

    /**
	 * define required constants
	 * 
	 * @return void
	 */
	public function define_constants() {
		define( 'MS_VERSION', self::VERSION );
        define( 'MS_FILE', __FILE__ );
		define( 'MS_PATH', __DIR__ );
		define( 'MS_URL', plugins_url( '', MS_FILE ) );
		define( 'MS_ASSETS', MS_URL . '/assets' );
	}
 }

/**
 * Initializes the main plugin
 *
 * @return \HelloWorldPlugin
 *
 */
function manage_subscription_plugin()
{
    return WpmetSubscription::init();
}

manage_subscription_plugin();
