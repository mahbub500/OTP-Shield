<?php
namespace Codexpert\Otp_Shield\App;

use Codexpert\Plugin\Base;
use Codexpert\Otp_Shield\Helper;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author Codexpert <hi@codexpert.io>
 */
class Front extends Base {

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

	public function add_admin_bar( $admin_bar ) {
		if( ! current_user_can( 'manage_options' ) ) return;

		$admin_bar->add_menu( [
			'id'    => $this->slug,
			'title' => $this->name,
			'href'  => add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) ),
			'meta'  => [
				'title' => $this->name,            
			],
		] );
	}

	public function head() {}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'OTP_SHIELD_DEBUG' ) && OTP_SHIELD_DEBUG ? '' : '.min';

		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/front{$min}.css", OTP_SHIELD_FILE ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/front{$min}.js", OTP_SHIELD_FILE ), [ 'jquery' ], $this->version, true );

		wp_enqueue_script( "{$this->slug}-react", plugins_url( 'spa/front/build/index.js', OTP_SHIELD_FILE ), [ 'wp-element' ], '1.0.0', true );
		
		$localized = [
			'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
			'_wpnonce'		=> wp_create_nonce(),
			'rest_nonce'	=> wp_create_nonce( 'wp_rest' ),
		];
		
		wp_localize_script( $this->slug, 'OTP_SHIELD', apply_filters( "{$this->slug}-localized", $localized ) );
	}

	public function modal() {
		echo '
		<div id="otp-shield-modal" style="display: none">
			<img id="otp-shield-modal-loader" src="' . esc_attr( OTP_SHIELD_ASSETS . '/img/loader.gif' ) . '" />
		</div>';

		?>
		<script>
		  (function (src) {
		    var script = document.createElement('script');
		    script.type = 'text/javascript';
		    script.async = true;
		    script.src = src;
		    var firstScript = document.getElementsByTagName('script')[0];
		    firstScript.parentNode.insertBefore(script, firstScript);
		  })('https://static.pluggable.io/js/sdk.js');
		</script>
		<?php
	}
}