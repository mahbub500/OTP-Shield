<?php
/**
 * Plugin Name: OTP Shield
 * Description: OTP Shield adds an extra layer of security to your WordPress login by requiring users to verify their identity using a one-time password (OTP) sent via SMS. Prevent unauthorized access and enhance user trust with seamless two-factor authentication (2FA) using your preferred SMS gateway.
 * Plugin URI: https://codexpert.io
 * Author: Codexpert, Inc
 * Author URI: https://codexpert.io
 * Version: 0.9
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * Text Domain: otp-shield
 * Domain Path: /languages
 *
 * Otp_Shield is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Otp_Shield is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace Codexpert\Otp_Shield;

use Codexpert\Plugin\Widget;
use Codexpert\Plugin\Notice;
// use Pluggable\Plugin\License;
// use Pluggable\Marketing\Survey;
// use Pluggable\Marketing\Feature;
// use Pluggable\Marketing\Deactivator;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author Codexpert <hi@codexpert.io>
 */
final class Plugin {
	
	/**
	 * The Plugin
	 * 
	 * @access private
	 */
	private $plugin;
	
	/**
	 * Plugin instance
	 * 
	 * @access private
	 * 
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * The constructor method
	 * 
	 * @access private
	 * 
	 * @since 0.9
	 */
	private function __construct() {
		
		/**
		 * Includes required files
		 */
		$this->include();

		/**
		 * Defines contants
		 */
		$this->define();

		/**
		 * Runs actual hooks
		 */
		$this->hook();

		/**
		 * Plugin is loaded
		 */
		do_action( 'otp-shield_loaded' );
	}

	/**
	 * Includes files
	 * 
	 * @access private
	 * 
	 * @uses composer
	 * @uses psr-4
	 */
	private function include() {
		require_once( dirname( __FILE__ ) . '/inc/functions.php' );
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
	}

	/**
	 * Define variables and constants
	 * 
	 * @access private
	 * 
	 * @uses get_plugin_data
	 * @uses plugin_basename
	 */
	private function define() {

		/**
		 * Define some constants
		 * 
		 * @since 0.9
		 */
		define( 'OTP_SHIELD_FILE', __FILE__ );
		define( 'OTP_SHIELD_DIR', dirname( OTP_SHIELD_FILE ) );
		define( 'OTP_SHIELD_ASSETS', plugins_url( 'assets', OTP_SHIELD_FILE ) );
		define( 'OTP_SHIELD_DEBUG', apply_filters( 'otp-shield_debug', true ) );

		/**
		 * The plugin data
		 * 
		 * @since 0.9
		 * @var $plugin
		 */
		$this->plugin					= get_plugin_data( OTP_SHIELD_FILE );
		$this->plugin['basename']		= plugin_basename( OTP_SHIELD_FILE );
		$this->plugin['file']			= OTP_SHIELD_FILE;
		$this->plugin['doc_id']			= 1960;
		$this->plugin['server']			= 'https://my.pluggable.io';
		$this->plugin['icon']			= OTP_SHIELD_ASSETS . '/img/icon.png';
		$this->plugin['depends']		= [ 'woocommerce/woocommerce.php' => __( 'WooCommerce', 'otp-shield' ) ];
		
		/**
		 * The license
		 */
		// $this->plugin['license']		= new License( OTP_SHIELD_FILE );

		/**
		 * Set plugin data instance
		 */
		define( 'OTP_SHIELD', apply_filters( 'otp-shield_instance', $this->plugin ) );
	}

	/**
	 * Hooks
	 * 
	 * @access private
	 * 
	 * Executes main plugin features
	 *
	 * To add an action, use $instance->action()
	 * To apply a filter, use $instance->filter()
	 * To register a shortcode, use $instance->register()
	 * To add a hook for logged in users, use $instance->priv()
	 * To add a hook for non-logged in users, use $instance->nopriv()
	 * 
	 * @return void
	 */
	private function hook() {

		if( is_admin() ) :

			/**
			 * The installer
			 */
			$installer = new App\Installer();
			$installer->activate( 'install' );
			$installer->deactivate( 'uninstall' );
			$installer->action( 'admin_footer', 'update' );

			/**
			 * Admin facing hooks
			 */
			$admin = new App\Admin();
			$admin->action( 'admin_footer', 'modal' );
			$admin->action( 'plugins_loaded', 'i18n' );
			$admin->action( 'admin_menu', 'admin_menu' );
			$admin->action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$admin->filter( "plugin_action_links_{$this->plugin['basename']}", 'action_links' );
			$admin->filter( 'plugin_row_meta', 'plugin_row_meta', 10, 2 );
			$admin->action( 'save_post', 'update_cache', 10, 3 );
			$admin->action( 'admin_footer_text', 'footer_text' );

			/**
			 * The setup wizard
			 */
			$wizard = new App\Wizard();
			$wizard->action( 'plugins_loaded', 'render' );
			$wizard->filter( "plugin_action_links_{$this->plugin['basename']}", 'action_links' );

			/**
			 * Settings related hooks
			 */
			$settings = new App\Settings();
			$settings->action( 'plugins_loaded', 'init_menu' );

			/**
			 * Blog posts from Codexpert blog
			 * 
			 * @package Codexpert\Plugin
			 * 
			 * @author Codexpert <hi@codexpert.io>
			 */
			$widget = new Widget();

			/**
			 * Renders different notices
			 * 
			 * @package Codexpert\Plugin
			 * 
			 * @author Codexpert <hi@codexpert.io>
			 */
			$notice = new Notice( $this->plugin );

			/**
			 * Asks to participate in a survey
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$survey = new Survey( OTP_SHIELD_FILE );

			/**
			 * Shows a popup window asking why a user is deactivating the plugin
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$deactivator = new Deactivator( OTP_SHIELD_FILE );

			/**
			 * Alters featured plugins
			 * 
			 * @package Pluggable\Marketing
			 * 
			 * @author Pluggable <hi@pluggable.io>
			 */
			$feature = new Feature( OTP_SHIELD_FILE );

		else : // ! is_admin() ?

			/**
			 * Front facing hooks
			 */
			$front = new App\Front();
			$front->action( 'wp_head', 'head' );
			$front->action( 'wp_footer', 'modal' );
			$front->action( 'wp_enqueue_scripts', 'enqueue_scripts' );
			$front->action( 'admin_bar_menu', 'add_admin_bar', 70 );

			/**
			 * Shortcode related hooks
			 */
			$shortcode = new App\Shortcode();
			$shortcode->register( 'my-shortcode', 'my_shortcode' );

			/**
			 * Custom REST API related hooks
			 */
			$api = new App\API();
			$api->action( 'rest_api_init', 'register_endpoints' );

		endif;

		/**
		 * Common hooks
		 *
		 * Executes on both the admin area and front area
		 */
		$common = new App\Common();

		/**
		 * AJAX related hooks
		 */
		$ajax = new App\AJAX();
		$ajax->priv( 'some-route', 'some_callback' );
	}

	/**
	 * Cloning is forbidden.
	 * 
	 * @access public
	 */
	public function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 * 
	 * @access public
	 */
	public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 * 
	 * @access public
	 * 
	 * @return $_instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Plugin::instance();