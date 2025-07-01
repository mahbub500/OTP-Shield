<?php
namespace Codexpert\Otp_Shield\App;

use Codexpert\Plugin\Base;
use Codexpert\Plugin\Metabox;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author Codexpert <hi@codexpert.io>
 */
class Admin extends Base {

	public $plugin;
	
	public $slug;

	public $name;

	public $version;

	/**
	 * Constructor function
	 */
	public function __construct() {
		$this->plugin	= OTP_SHIELD;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	/**
	 * Internationalization
	 */
	public function i18n() {
		load_plugin_textdomain( 'otp-shield', false, OTP_SHIELD_DIR . '/languages/' );
	}

	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'OTP_SHIELD_DEBUG' ) && OTP_SHIELD_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", OTP_SHIELD_FILE ), '', $this->version, 'all' );
		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", OTP_SHIELD_FILE ), [ 'jquery' ], $this->version, true );

	    wp_enqueue_script( "{$this->slug}-react", plugins_url( 'spa/admin/build/index.js', OTP_SHIELD_FILE ), [ 'wp-element' ], '1.0.0', true );

	    $localized = [
	    	'homeurl'		=> get_bloginfo( 'url' ),
	    	'adminurl'		=> admin_url(),
	    	'asseturl'		=> OTP_SHIELD_ASSETS,
	    	'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
	    	'_wpnonce'		=> wp_create_nonce(),
	    	'api_base'		=> get_rest_url(),
	    	'rest_nonce'	=> wp_create_nonce( 'wp_rest' ),
	    ];
	    
	    wp_localize_script( $this->slug, 'OTP_SHIELD', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function admin_menu() {

		add_menu_page(
			__( 'OTP Shield', 'otp-shield' ),
			__( 'OTP Shield', 'otp-shield' ),
			'manage_options',
			'otp-shield',
			function(){},
			'dashicons-wordpress',
			25
		);

		add_submenu_page(
			'otp-shield',
			__( 'Help', 'otp-shield' ),
			__( 'Help', 'otp-shield' ),
			'manage_options',
			'otp-shield-help',
			function() {
				printf( '<div id="otp-shield_help"><p>%s</p></div>', __( 'Loading..', 'otp-shield' ) );
			}
		);

		add_submenu_page(
			'otp-shield',
			__( 'License', 'otp-shield' ),
			__( 'License', 'otp-shield' ),
			'manage_options',
			'otp-shield-license',
			function() {
				printf( '<div id="otp-shield_license"><p>%s</p></div>', __( 'Loading..', 'otp-shield' ) );
			}
		);
	}

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'admin.php' );

		$new_links = [
			'settings'	=> sprintf( '<a href="%1$s">' . __( 'Settings', 'otp-shield' ) . '</a>', add_query_arg( 'page', $this->slug, $this->admin_url ) )
		];
		
		return array_merge( $new_links, $links );
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		
		if ( $this->plugin['basename'] === $plugin_file ) {
			$plugin_meta['help'] = '<a href="https://help.codexpert.io/" target="_blank" class="cx-help">' . __( 'Help', 'otp-shield' ) . '</a>';
		}

		return $plugin_meta;
	}

	public function update_cache( $post_id, $post, $update ) {
		wp_cache_delete( "otp_{$post->post_type}", 'otp' );
	}

	public function footer_text( $text ) {
		if( get_current_screen()->parent_base != $this->slug ) return $text;

		return sprintf( __( 'If you like <strong>%1$s</strong>, please <a href="%2$s" target="_blank">leave us a %3$s rating</a> on WordPress.org! It\'d motivate and inspire us to make the plugin even better!', 'otp-shield' ), $this->name, "https://wordpress.org/support/plugin/{$this->slug}/reviews/?filter=5#new-post", '⭐⭐⭐⭐⭐' );
	}

	public function modal() {
		echo '
		<div id="otp-shield-modal" style="display: none">
			<img id="otp-shield-modal-loader" src="' . esc_attr( OTP_SHIELD_ASSETS . '/img/loader.gif' ) . '" />
		</div>';
	}
}