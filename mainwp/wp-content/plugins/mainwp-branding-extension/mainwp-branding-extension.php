<?php
/*
Plugin Name: MainWP Branding Extension
Plugin URI: http://extensions.mainwp.com
Description: The MainWP Branding extension allows you to alter the details of the MianWP Child Plugin to reflect your companies brand or completely hide the plugin from the installed plugins list.
Version: 1.0
Author: MainWP
Author URI: http://mainwp.com
Support Forum URI: https://mainwp.com/forum/forumdisplay.php?94-Branding
Documentation URI: http://docs.mainwp.com/category/mainwp-extensions/mainwp-branding-extension/
Icon URI: http://extensions.mainwp.com/wp-content/uploads/2014/03/mainwp-child-pllugin-branding-extension.png
*/

if ( ! defined( 'MAINWP_BRANDING_PLUGIN_FILE' ) ) {
	define( 'MAINWP_BRANDING_PLUGIN_FILE', __FILE__ );
}

class MainWP_Branding_Extension {
	public static $instance = null;
	public $plugin_handle = 'mainwp-branding-extension';
	public $plugin_slug;
	protected $plugin_url;

	public function __construct() {

		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->plugin_slug = plugin_basename( __FILE__ );

		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
		MainWP_Branding_DB::get_instance()->install();
		MainWP_Branding::get_instance()->init();
	}

	static function get_instance() {

		if ( null == MainWP_Branding_Extension::$instance  ) {
			MainWP_Branding_Extension::$instance = new MainWP_Branding_Extension();
		}

		return MainWP_Branding_Extension::$instance;
	}

	public function init() {

	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( $this->plugin_slug != $plugin_file ) {
			return $plugin_meta;
		}
		$plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for updates now</a>';

		return $plugin_meta;
	}

	public function admin_init() {

		wp_enqueue_style( 'mainwp-branding-extension', $this->plugin_url . 'css/mainwp-branding.css' );
		wp_enqueue_script( 'mainwp-branding-extension', $this->plugin_url . 'js/mainwp-branding.js' );
	}
}


function mainwp_branding_extension_autoload( $class_name ) {

	$allowedLoadingTypes = array( 'class', 'page' );
	$class_name = str_replace( '_', '-', strtolower( $class_name ) );
	foreach ( $allowedLoadingTypes as $allowedLoadingType ) {
		$class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . $allowedLoadingType . DIRECTORY_SEPARATOR . $class_name . '.' . $allowedLoadingType . '.php';
		if ( file_exists( $class_file ) ) {
			require_once( $class_file );
		}
	}
}

if ( function_exists( 'spl_autoload_register' ) ) {
	spl_autoload_register( 'mainwp_branding_extension_autoload' );
} else {
	function __autoload( $class_name ) {

		mainwp_branding_extension_autoload( $class_name );
	}
}


register_activation_hook( __FILE__, 'mainwp_branding_extension_activate' );
register_deactivation_hook( __FILE__, 'mainwp_branding_extension_deactivate' );

function mainwp_branding_extension_activate() {
	update_option( 'mainwp_branding_extension_activated', 'yes' );
	$extensionActivator = new MainWP_Branding_Extension_Activator();
	$extensionActivator->activate();
}

function mainwp_branding_extension_deactivate() {
	$extensionActivator = new MainWP_Branding_Extension_Activator();
	$extensionActivator->deactivate();
}

class MainWP_Branding_Extension_Activator {
	protected $mainwpMainActivated = false;
	protected $childEnabled = false;
	protected $childKey = false;
	protected $childFile;
	protected $plugin_handle = 'mainwp-branding-extension';
	protected $product_id = 'MainWP Branding Extension';
	protected $software_version = '1.0';

	public function __construct() {

		$this->childFile = __FILE__;
		add_filter( 'mainwp-getextensions', array( &$this, 'get_this_extension' ) );
		$this->mainwpMainActivated = apply_filters( 'mainwp-activated-check', false );

		if ( $this->mainwpMainActivated !== false ) {
			$this->activate_this_plugin();
		} else {
			add_action( 'mainwp-activated', array( &$this, 'activate_this_plugin' ) );
		}
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_notices', array( &$this, 'mainwp_error_notice' ) );
	}

	function activate_this_plugin() {

		$this->mainwpMainActivated = apply_filters( 'mainwp-activated-check', $this->mainwpMainActivated );

		$this->childEnabled = apply_filters( 'mainwp-extension-enabled-check', __FILE__ );
		if ( ! $this->childEnabled ) {
			return;
		}

		$this->childEnabled = apply_filters( 'mainwp-extension-enabled-check', __FILE__ );
		if ( ! $this->childEnabled ) {
			return;
		}

		$this->childKey = $this->childEnabled['key'];

		if ( function_exists( 'mainwp_current_user_can' ) && ! mainwp_current_user_can( 'extension', 'mainwp-branding-extension' ) ) {
			return;
		}
		new MainWP_Branding_Extension();
	}

	function admin_init() {
		if ( get_option( 'mainwp_branding_extension_activated' ) == 'yes' ) {
			delete_option( 'mainwp_branding_extension_activated' );
			wp_redirect( admin_url( 'admin.php?page=Extensions' ) );

			return;
		}
	}

	function get_this_extension( $pArray ) {

		$pArray[] = array(
		'plugin'     => __FILE__,
		                   'api'        => $this->plugin_handle,
		                   'mainwp'     => true,
		                   'callback'   => array( &$this, 'settings' ),
		                   'apiManager' => true,
		);

		return $pArray;
	}

	function settings() {

		do_action( 'mainwp-pageheader-extensions', __FILE__ );
		if ( $this->childEnabled ) {
			MainWP_Branding::render();
		} else {
			?>
			<div class="mainwp_info-box-yellow">
			<strong><?php _e( 'The Extension has to be enabled to change the settings.' ); ?></strong></div><?php
		}
		do_action( 'mainwp-pagefooter-extensions', __FILE__ );
	}

	function mainwp_error_notice() {

		global $current_screen;
		if ( $current_screen->parent_base == 'plugins' && $this->mainwpMainActivated == false ) {
			echo '<div class="error"><p>MainWP Branding Extension ' . __( 'requires <a href="http://mainwp.com/" target="_blank">MainWP</a> Plugin to be activated in order to work. Please install and activate <a href="http://mainwp.com/" target="_blank">MainWP</a> first.' ) . '</p></div>';
		}
	}

	public function get_child_key() {

		return $this->childKey;
	}

	public function get_child_file() {

		return $this->childFile;
	}

	public function activate() {
		$options = array(
			'product_id'       => $this->product_id,
			'activated_key'    => 'Deactivated',
			'instance_id'      => apply_filters( 'mainwp-extensions-apigeneratepassword', 12, false ),
			'software_version' => $this->software_version,
		);
		$this->update_option( $this->plugin_handle . '_APIManAdder', $options );
	}

	public function update_option( $option_name, $option_value ) {

		$success = add_option( $option_name, $option_value, '', 'no' );

		if ( ! $success ) {
			$success = update_option( $option_name, $option_value );
		}

		return $success;
	}

	public function deactivate() {
		$this->update_option( $this->plugin_handle . '_APIManAdder', '' );
	}
}

$mainWPBrandingExtensionActivator = new MainWP_Branding_Extension_Activator();
