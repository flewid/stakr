<?php
/*
Plugin Name: MainWP Bulk Settings Manager Extension
Plugin URI: https://mainwp.com
Description: The Bulk Settings Manager Extension unlocks the world of WordPress directly from your MainWP Dashboard. With Bulk Settings Manager you can adjust your Child site settings for the WordPress Core and almost any WordPress Plugin or Theme.
Version: 1.3
Author: MainWP
Author URI: https://mainwp.com
Documentation URI: https://mainwp.com/help/category/mainwp-extensions/bulk-settings-manager/
*/

if ( ! defined( 'MAINWP_BULK_SETTINGS_EXT_PLUGIN_FILE' ) ) {
	define( 'MAINWP_BULK_SETTINGS_EXT_PLUGIN_FILE', __FILE__ );
}

function mainwp_bulk_settings_manager_activate() {
	$install = MainWPBulkSettingsManagerDB::Instance()->install();

	if ( $install !== true ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'Bulk Settings Manager Extension cannot be installed.' );
	} else {
		$extensionActivator = new mainpBulkSettingsManagerExtensionActivator();
		$extensionActivator->activate();

		update_option( 'mainwp_bulk_settings_manager_activated', 'yes' );
	}
}

function mainwp_bulk_settings_manager_deactivate() {

	$extensionActivator = new mainpBulkSettingsManagerExtensionActivator();
	$extensionActivator->deactivate();

	MainWPBulkSettingsManagerDB::Instance()->uninstall();
}

function mainwp_bulk_settings_manager_autoload( $class_name ) {
	$allowedLoadingTypes = array( 'class', 'view' );

	foreach ( $allowedLoadingTypes as $allowedLoadingType ) {
		$class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . $allowedLoadingType . DIRECTORY_SEPARATOR . $class_name . '.' . $allowedLoadingType . '.php';
		if ( file_exists( $class_file ) ) {
			require_once( $class_file );
		}
	}
}

if ( function_exists( 'spl_autoload_register' ) ) {
	spl_autoload_register( 'mainwp_bulk_settings_manager_autoload' );
} else {
	function __autoload( $class_name ) {
		mainwp_bulk_settings_manager_autoload( $class_name );
	}
}

register_activation_hook( __FILE__, 'mainwp_bulk_settings_manager_activate' );
register_deactivation_hook( __FILE__, 'mainwp_bulk_settings_manager_deactivate' );

class mainpBulkSettingsManagerExtensionActivator {
	protected $mainwp_main_activated = false;
	protected $child_enabled = false;
	protected $child_key = false;
	protected $child_file;
	protected $plugin_handle = "mainwp-bulk-settings-manager";
	protected $product_id = "MainWP Bulk Settings Manager";
	protected $software_version = "1.3";
	protected $plugin = null;
	public $plugin_dir;

	public function __construct() {
		$this->plugin_slug = plugin_basename( __FILE__ );
		$this->child_file  = __FILE__;
		add_filter( 'mainwp-getextensions', array( &$this, 'get_this_extension' ) );
		$this->mainwp_main_activated = apply_filters( 'mainwp-activated-check', false );

		if ( $this->mainwp_main_activated !== false ) {
			$this->activate_this_plugin();
		} else {
			add_action( 'mainwp-activated', array( &$this, 'activate_this_plugin' ) );
		}

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_notices', array( &$this, 'mainwp_error_notice' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * @param $plugin_meta
	 * @param $plugin_file
	 *
	 * @return array
	 *
	 * Add text inside wp-admin/plugins.php
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( $this->plugin_slug != $plugin_file ) {
			return $plugin_meta;
		}

		$slug = basename($plugin_file, ".php");
		$api_data = get_option( $slug. '_APIManAdder');		
		if (!is_array($api_data) || !isset($api_data['activated_key']) || $api_data['activated_key'] != 'Activated' || !isset($api_data['api_key']) || empty($api_data['api_key']) ) {
			return $plugin_meta;
		}
		
		$plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for updates now</a>';

		return $plugin_meta;
	}
	
	public function getChildKey() {
		return $this->child_key;
	}

	public function getChildFile() {
		return $this->child_file;
	}

	public function admin_init() {
		// Redirect after instalation
		if ( get_option( 'mainwp_bulk_settings_manager_activated' ) == 'yes' ) {
			delete_option( 'mainwp_bulk_settings_manager_activated' );
			wp_redirect( admin_url( 'admin.php?page=Extensions' ) );

			return;
		}
	}

	/**
	 * @param $pArray
	 *
	 * @return array
	 *
	 * Callback to function which take care of this extension
	 */
	public function get_this_extension( $pArray ) {
		$pArray[] = array(
			'plugin'     => __FILE__,
			'api'        => $this->plugin_handle,
			'mainwp'     => true,
			'callback'   => array( &$this, 'settings' ),
			'apiManager' => true
		);

		return $pArray;
	}

	/**
	 * Display settings page if plugin enabled
	 */
	function settings() {
		do_action( 'mainwp-pageheader-extensions', __FILE__ );		
		$this->plugin->settings();		
		do_action( 'mainwp-pagefooter-extensions', __FILE__ );
	}

	public function activate_this_plugin() {
		$this->mainwp_main_activated = apply_filters( 'mainwp-activated-check', $this->mainwp_main_activated );
		$this->child_enabled         = apply_filters( 'mainwp-extension-enabled-check', __FILE__ );
		$this->child_key = $this->child_enabled['key'];
		$this->plugin = new MainWPBulkSettingsManager();
	}

	/**
	 * @param $option_name
	 * @param $option_value
	 *
	 * @return bool
	 *
	 * Add plugin options
	 */
	public function update_option( $option_name, $option_value ) {
		$success = add_option( $option_name, $option_value, '', 'no' );

		if ( ! $success ) {
			$success = update_option( $option_name, $option_value );
		}

		return $success;
	}

	/**
	 * Display notice if MainWP is not installed
	 */
	public function mainwp_error_notice() {
		global $current_screen;
		if ( $current_screen->parent_base == 'plugins' && $this->mainwp_main_activated == false ) {
			echo '<div class="error"><p>Bulk Settings Manager Extension ' . __( 'requires <a href="http://mainwp.com/" target="_blank">MainWP Plugin</a> to be activated in order to work. Please install and activate <a href="http://mainwp.com/" target="_blank">MainWP Plugin</a> first.' ) . '</p></div>';
		}
	}

	/**
	 * Activate plugin
	 */
	public function activate() {
		$options = array(
			'product_id'       => $this->product_id,
			'activated_key'    => 'Deactivated',
			'instance_id'      => apply_filters( 'mainwp-extensions-apigeneratepassword', 12, false ),
			'software_version' => $this->software_version
		);

		$this->update_option( $this->plugin_handle . "_APIManAdder", $options );
	}

	/**
	 * Deactivate plugin
	 */
	public function deactivate() {
		$this->update_option( $this->plugin_handle . "_APIManAdder", '' );
	}

}

global $mainwpBulkSettingsManagerExtensionActivator;
$mainwpBulkSettingsManagerExtensionActivator = new mainpBulkSettingsManagerExtensionActivator();
  
