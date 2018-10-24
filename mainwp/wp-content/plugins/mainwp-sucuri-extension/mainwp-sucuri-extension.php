<?php
/*
  Plugin Name: MainWP Sucuri Extension
  Plugin URI: http://extensions.mainwp.com
  Description: MainWP Sucuri Extension enables you to scan your child sites for various types of malware, spam injections, website errors, and much more. Requires the MainWP Dashboard.
  Version: 0.2.0
  Author: MainWP
  Author URI: http://mainwp.com
  Documentation URI: http://docs.mainwp.com/category/mainwp-extensions/mainwp-sucuri-extension/
  Support Forum URI: https://mainwp.com/forum/forumdisplay.php?93-Sucuri-Extension
  Icon URI: http://extensions.mainwp.com/wp-content/uploads/2014/03/mainwp-sucuri-extension.png
 */


if ( ! defined( 'MAINWP_SUCURI_PLUGIN_FILE' ) ) {
	define( 'MAINWP_SUCURI_PLUGIN_FILE', __FILE__ );
}

class MainWP_Sucuri_Extension {

	public static $instance = null;
	public $plugin_handle = 'mainwp-sucuri-extension';
	protected $plugin_url;
	public $plugin_slug;

	static function get_instance() {
		if ( null == MainWP_Sucuri_Extension::$instance ) {
			MainWP_Sucuri_Extension::$instance = new MainWP_Sucuri_Extension(); }
		return MainWP_Sucuri_Extension::$instance;
	}

	public function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_slug = plugin_basename( __FILE__ );
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
		//add_filter('mainwp_managesites_column_url', array(&$this, 'managesites_column_url'), 10, 2);
		//add_filter('mainwp-getsubpages-sites', array(&$this, 'add_subpages_manage_sites'), 10, 1);
		MainWP_Sucuri_DB::get_instance()->install();
		MainWP_Sucuri::get_instance()->init();
	}

	public function init() {

	}

	//    function add_subpages_manage_sites($subPage) {
	//        $subPage[] = array('title' => __("Security Scan Report",'mainwp'),
	//                           'slug' => "SecurityScan",
	//                           'menu_hidden' => true,
	//                            'callback' => array('MainWP_Sucuri', 'render'));
	//        return $subPage;
	//    }
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( $this->plugin_slug != $plugin_file ) {
			return $plugin_meta; }

		$plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for updates now</a>';
		return $plugin_meta;
	}

	//    public function managesites_column_url($actions, $websiteid) {
	//        $actions['sucuri'] = sprintf('<a href="admin.php?page=ManageSitesSecurityScan&websiteid=%1$s">' . __('Security Scan', 'mainwp') . '</a>', $websiteid);
	//        return $actions;
	//    }

	public function admin_init() {
		//if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'ManageSitesSecurityScan')
		//{
		wp_enqueue_style( 'mainwp-securi-extension', $this->plugin_url . 'css/mainwp-sucuri.css' );
		wp_enqueue_script( 'mainwp-securi-extension', $this->plugin_url . 'js/mainwp-sucuri.js' );
		//}
	}
}

function mainwp_sucuri_extension_autoload( $class_name ) {
	$allowedLoadingTypes = array( 'class' );
	$class_name = str_replace( '_', '-', strtolower( $class_name ) );
	foreach ( $allowedLoadingTypes as $allowedLoadingType ) {
		$class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . $allowedLoadingType . DIRECTORY_SEPARATOR . $class_name . '.' . $allowedLoadingType . '.php';
		if ( file_exists( $class_file ) ) {
			require_once( $class_file );
		}
	}
}

if ( function_exists( 'spl_autoload_register' ) ) {
	spl_autoload_register( 'mainwp_sucuri_extension_autoload' );
} else {

	function __autoload( $class_name ) {
		mainwp_sucuri_extension_autoload( $class_name );
	}
}


register_activation_hook( __FILE__, 'mainwp_sucuri_extension_activate' );
register_deactivation_hook( __FILE__, 'mainwp_sucuri_extension_deactivate' );

function mainwp_sucuri_extension_activate() {
	update_option( 'mainwp_sucuri_extension_activated', 'yes' );
	$extensionActivator = new MainWP_Sucuri_Extension_Activator();
	$extensionActivator->activate();
}

function mainwp_sucuri_extension_deactivate() {
	$extensionActivator = new MainWP_Sucuri_Extension_Activator();
	$extensionActivator->deactivate();
}

class MainWP_Sucuri_Extension_Activator {

	protected $mainwpMainActivated = false;
	protected $childEnabled = false;
	protected $childKey = false;
	protected $childFile;
	protected $plugin_handle = 'mainwp-sucuri-extension';
	protected $product_id = 'MainWP Sucuri Extension';
	protected $software_version = '0.2.0';

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

	function admin_init() {
		if ( get_option( 'mainwp_sucuri_extension_activated' ) == 'yes' ) {
			delete_option( 'mainwp_sucuri_extension_activated' );
			wp_redirect( admin_url( 'admin.php?page=Extensions' ) );
			return;
		}
	}

	function get_this_extension( $pArray ) {
		$pArray[] = array( 'plugin' => __FILE__, 'api' => $this->plugin_handle, 'mainwp' => true, 'callback' => array( &$this, 'settings' ), 'apiManager' => true );
		return $pArray;
	}

	function settings() {
		do_action( 'mainwp-pageheader-extensions', __FILE__ );
		if ( $this->childEnabled ) {
			MainWP_Sucuri::sucuri_qsg();
			?><div class="mainwp_info-box-yellow"><?php _e( 'This extension does not have the settings page. To use this extension use the "Security Scan" link in the <a href="admin.php?page=managesites" title="Manage Sites">Manage Sites</a> table. In case you need help, please review this <a href="http://docs.mainwp.com/category/mainwp-extensions/mainwp-sucuri-extension" target="_blank">documentation</a>.' ); ?></div><?php
		} else {
			?><div class="mainwp_info-box-yellow"><strong><?php _e( 'The Extension has to be enabled to change the settings.' ); ?></strong></div><?php
		}
		do_action( 'mainwp-pagefooter-extensions', __FILE__ );
	}

	function activate_this_plugin() {
		$this->mainwpMainActivated = apply_filters( 'mainwp-activated-check', $this->mainwpMainActivated );

		$this->childEnabled = apply_filters( 'mainwp-extension-enabled-check', __FILE__ );
		if ( ! $this->childEnabled ) {
			return; }

		$this->childEnabled = apply_filters( 'mainwp-extension-enabled-check', __FILE__ );
		if ( ! $this->childEnabled ) {
			return; }

		$this->childKey = $this->childEnabled['key'];

		if ( function_exists( 'mainwp_current_user_can' ) && ! mainwp_current_user_can( 'extension', 'mainwp-sucuri-extension' ) ) {
			return; }

		new MainWP_Sucuri_Extension();
	}

	function mainwp_error_notice() {
		global $current_screen;
		if ( $current_screen->parent_base == 'plugins' && $this->mainwpMainActivated == false ) {
			echo '<div class="error"><p>MainWP Sucuri Extension ' . __( 'requires <a href="http://mainwp.com/" target="_blank">MainWP</a> Plugin to be activated in order to work. Please install and activate <a href="http://mainwp.com/" target="_blank">MainWP</a> first.' ) . '</p></div>';
		}
	}

	public function get_child_key() {
		return $this->childKey;
	}

	public function get_child_file() {
		return $this->childFile;
	}

	public function is_enabled() {
		return $this->childFile ? true : false;
	}

	public function update_option( $option_name, $option_value ) {
		$success = add_option( $option_name, $option_value, '', 'no' );

		if ( ! $success ) {
			$success = update_option( $option_name, $option_value );
		}

		return $success;
	}

	public function activate() {
		$options = array(
		'product_id' => $this->product_id,
			'activated_key' => 'Deactivated',
			'instance_id' => apply_filters( 'mainwp-extensions-apigeneratepassword', 12, false ),
			'software_version' => $this->software_version,
		);
		$this->update_option( $this->plugin_handle . '_APIManAdder', $options );
	}

	public function deactivate() {
		$this->update_option( $this->plugin_handle . '_APIManAdder', '' );
	}
}

$mainWPSucuriExtensionActivator = new MainWP_Sucuri_Extension_Activator();
