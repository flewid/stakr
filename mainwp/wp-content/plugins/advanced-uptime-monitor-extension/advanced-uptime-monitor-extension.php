<?php
/**
	Plugin Name: Advanced Uptime Monitor Extension
	Plugin URI:
	Description: MainWP Extension for real-time up time monitoring.
	Version: 2.1.1
	Author: MainWP
	Author URI: http://extensions.mainwp.com
	Documentation URI: http://docs.mainwp.com/category/mainwp-extensions/mainwp-advanced-uptime-monitor/
	Support Forum URI: https://mainwp.com/forum/forumdisplay.php?66-Advanced-Uptime-Monitor
	Icon URI: http://extensions.mainwp.com/wp-content/uploads/2013/06/Advanced-Uptime-Monitor-300x300.png
 */
if ( ! defined( 'MAINWP_AUM_PLUGIN_FILE' ) ) {
	define( 'MAINWP_AUM_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MVC_PLUGIN_PATH' ) ) {
	define( 'MVC_PLUGIN_PATH', dirname( __FILE__ ) . '/' );
}

class Advanced_Uptime_Monitor_Extension {

	/**
	 * @var instance
	 */
	private static $instance = null;

	/**
	 * @var plugin_name
	 */
	public $plugin_name = 'Advanced Uptime Monitor Extension';

	/**
	 * @var plugin_handle
	 */
	public $plugin_handle = 'advanced-uptime-monitor-extension';

	/**
	 * @var plugin_dir
	 */
	public $plugin_dir;

	/**
	 * @var plugin_url
	 */
	protected $plugin_url;

	/**
	 * @var plugin_admin
	 */
	protected $plugin_admin = '';

	/**
	 * @var option
	 */
	protected $option;

	/**
	 * @var option_handle
	 */
	protected $option_handle = 'advanced_uptime_monitor_extension';

	/**
	 * @var api_url
	 */
	private $api_url = 'http://api.uptimerobot.com/';

	/**
	 * @var message_info
	 */
	public $message_info = array();

	/**
	 * @var plugin_slug
	 */
	private $plugin_slug;

	static function get_install() {
		if ( null === self::$instance ) {
			self::$instance = new Advanced_Uptime_Monitor_Extension();
		}
		return self::$instance;
	}

	public function __construct() {
		global $wpdb;
		error_reporting( E_ALL ^ E_NOTICE );
		$this->plugin_dir = plugin_dir_path( __FILE__ );
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_slug = plugin_basename( __FILE__ );

		if ( is_admin() ) {
			// Load admin functionality
			require_once MVC_PLUGIN_PATH . 'core/loaders/mvc_admin_loader.php';
			$loader = new MvcAdminLoader();
			add_action( 'admin_init', array( $loader, 'admin_init' ) );
			add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
			add_action( 'admin_menu', array( $loader, 'add_menu_pages' ) );
			add_action( 'admin_menu', array( $loader, 'add_settings_pages' ) );
			add_action( 'plugins_loaded', array( $loader, 'add_admin_ajax_routes' ) );
			add_action( 'init', array( $loader, 'init' ) );
			add_action( 'widgets_init', array( $loader, 'register_widgets' ) );
			add_filter( 'post_type_link', array( $loader, 'filter_post_link' ), 10, 2 );
			// add_filter('mainwp_aum_get_data', array(&$this, 'aum_get_data'), 10, 3); //ok
		}

		add_filter( 'mainwp_aum_get_data', array( &$this, 'aum_get_data' ), 10, 3 ); // ok
		// Load global functionality
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		// add_action('init', array($loader, 'init'));
		// add_action('widgets_init', array($loader, 'register_widgets'));
		// add_filter('post_type_link', array($loader, 'filter_post_link'), 10, 2);
		require_once dirname( __FILE__ ) . '/app/classes/uptimerobot.class.php';

		add_action( 'admin_enqueue_scripts', array( $this, 'uptime_robot_monitors_enqueue_style' ) );
		add_action( 'admin_head', array( $this, 'uptime_robot_monitors_add_head' ) );

		$this->option = get_option( $this->option_handle );
	}

	function uptime_robot_monitors_enqueue_style() {
		wp_register_style( 'urm-admin', plugins_url( 'css/admin.css', __FILE__ ) );
		wp_enqueue_style( 'urm-admin' );
		wp_register_style( 'urm', plugins_url( 'css/style.css', __FILE__ ) );
		wp_enqueue_style( 'urm' );
		wp_register_script( 'urm_js', plugins_url( 'js/monitors.js', __FILE__ ) );
		wp_enqueue_script( 'urm_js' );
		$urm_plugin_url = plugins_url( '', __FILE__ );
		wp_localize_script( 'urm_js', 'urm_plugin_url', $urm_plugin_url );
	}

	function uptime_robot_monitors_add_head() {
		echo "<script>
                var siteurl = '" . esc_html( home_url() ) . "';\n
            </script>";
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

	}

	public function get_file_content_url( $url ) {
		if ( empty( $url ) ) {
			throw new Exception( 'Value not specified: url', 1 );
		}
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
		$file_contents = curl_exec( $ch );
		curl_close( $ch );
		return $file_contents;
	}

	public function option_page() {
		$saved = false;
		if ( isset( $_POST['aum_submit'] ) &&  isset( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $this->plugin_handle . '-option' ) ) {
			$saved = $this->save_option();
		}
		require $this->plugin_dir . '/includes/option_page.php';
	}

	function aum_get_data( $websiteid = null, $start_date = null, $end_date = null ) {
		$api_key = $this->option['api_key'];
		if ( empty( $api_key ) ) {
			return false;
		}

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			// to fix bug
			$user_id = get_option( 'mainwp_aum_api_current_user_id' );
		} else {
			$user_id = get_current_user_id();
		}

		require_once MVC_PLUGIN_PATH . 'core/loaders/mvc_admin_loader.php';
		$loader = new MvcAdminLoader();
		// to fix bug Fatal error: Class 'UptimeMonitor' not found
		require_once MVC_PLUGIN_PATH . 'app/models/uptime_monitor.php';
		require_once MVC_PLUGIN_PATH . 'app/models/uptime_url.php';

		$utime_mo = new UptimeMonitor();
		$monitor = $utime_mo->find_one(array(
			'selects' => array( 'monitor_id' ),
			'conditions' => array(
				'user_id' => $user_id,
				'monitor_api_key' => $api_key,
			),
		));
		if ( ! $monitor ) {
			return false;
		}

		global $mainwpAdvancedUptimeMonitorExtensionActivator;
		$website = apply_filters( 'mainwp-getsites', $mainwpAdvancedUptimeMonitorExtensionActivator->get_child_file(), $mainwpAdvancedUptimeMonitorExtensionActivator->get_child_key(), $websiteid );
		$url_site = '';
		if ( $website && is_array( $website ) ) {
			$website = current( $website );
			$url_site = $website['url'];
		}

		if ( empty( $url_site ) ) {
			return false;
		}

		$utime_url = new UptimeUrl();
		$url_upmo = $utime_url->find_one(array(
			'conditions' => array(
				'monitor_id' => $monitor->monitor_id,
				'url_address' => $url_site,
			),
		));
		if ( ! $url_upmo ) {
			// to fix bug
			$url_site = rtrim( $url_site, '/' );
			$url_upmo = $utime_url->find_one(array(
				'conditions' => array(
					'monitor_id' => $monitor->monitor_id,
					'url_address' => $url_site,
				),
			));
			if ( ! $url_upmo ) {
				return false;
			}
		}

		$url_upmo_ids = array( $url_upmo->url_uptime_monitor_id );

		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $api_key );
		$result = $UR->get_monitors( $url_upmo_ids, 0, 0, '7-15-30-45-60' );

		// Place this one first
		while ( strpos( $result, ',,' ) !== false ) {
			$result = str_replace( array( ',,' ), ',', $result ); // fix json
		}
		$result = str_replace( ',]', ']', $result ); // fix json
		$result = str_replace( '[,', '[', $result ); // fix json

		$result = json_decode( $result );

		if ( empty( $result ) || $result->stat == 'fail' ) {
			return false;
		}
		$return = array();

		if ( isset( $result->monitors->monitor ) && is_array( $result->monitors->monitor ) && count( $result->monitors->monitor ) > 0 ) {
			$monitor = $result->monitors->monitor[0];
			$return['aum.alltimeuptimeratio'] = $monitor->alltimeuptimeratio;
			list($up7, $up15, $up30, $up45, $up60) = explode( '-', $monitor->customuptimeratio );
			$return['aum.uptime7'] = $up7;
			$return['aum.uptime15'] = $up15;
			$return['aum.uptime30'] = $up30;
			$return['aum.uptime45'] = $up45;
			$return['aum.uptime60'] = $up60;
		}
		return $return;
	}

	public function get_list_notification_contact( $api_key ) {

		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $api_key );
		try {
			$result = $UR->get_contacts();
			// error_log($result);
			$result = json_decode( $result );

			if ( $result->stat == 'fail' ) {
				return array();
			}
		} catch (Exception $ex) {

			$this->flash( 'error', $ex->getMessage() );
			return array();
		}

		$number_contacts = count( $result->alertcontacts->alertcontact );
		$list_contact = array();
		for ( $i = 0; $i < $number_contacts; $i++ ) {
			if ( $result->alertcontacts->alertcontact[ $i ]->status == 2 ) {
				$list_contact[ $result->alertcontacts->alertcontact[ $i ]->id ] = $result->alertcontacts->alertcontact[ $i ]->value;
			}
		}
		return $list_contact;
	}

	public function save_option() {
		global $wpdb;

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $this->plugin_handle . '-option' ) ) {
			return false;
		}

		// to fix bug Fatal error: Class 'UptimeMonitor' not found
		require_once MVC_PLUGIN_PATH . 'app/models/uptime_monitor.php';
		require_once MVC_PLUGIN_PATH . 'app/models/uptime_url.php';

		if ( get_option( 'mainwp_aum_requires_reload_monitors' ) == 'yes' ) {
			update_option( 'mainwp_aum_requires_reload_monitors', '' );
		}

		$api_key = isset( $_POST['api_key'] ) ? trim( $_POST['api_key'] ) : '';
		// All is OK now
		if ( ! empty( $api_key ) ) {
			$count = 0;
			$results = $this->get_all_uptime_monitors( $api_key );
			// error_log("result=" . print_r($results, true));
			if ( is_array( $results ) && (count( $results ) > 0) ) {
				$UM = new UptimeMonitor();
				$UUR = new UptimeUrl();
				$monitor_id = 0;
				$current_UMs = $UM->find( array( 'conditions' => array( 'user_id' => get_current_user_id() ) ) );
				// error_log("current monitors");
				// error_log(print_r($current_UMs, true));
				if ( count( $current_UMs ) > 0 ) {
					foreach ( $current_UMs as $current_UM ) {
						// delete others, each user have only one monitor
						if ( $current_UM->monitor_api_key == $api_key && empty( $monitor_id ) ) {
							$monitor_id = $current_UM->monitor_id;
						} else {
							$UM->delete_all(array(
								'conditions' => array(
									'user_id' => get_current_user_id(),
									'monitor_id' => $current_UM->monitor_id,
								),
							));
							// delete monitor urls
							$UUR->delete_all(array(
								'conditions' => array(
									'user_id' => get_current_user_id(),
									'monitor_id' => $current_UM->monitor_id,
								),
							));
						}
					}
				}

				$current_uptime_monitor_ids = array();

				if ( $monitor_id ) {
					$current_UUR = $UUR->find(array(
						'conditions' => array(
							'user_id' => get_current_user_id(),
							'monitor_id' => $monitor_id,
						),
					));
					if ( is_array( $current_UUR ) && (count( $current_UUR ) > 0) ) {
						foreach ( $current_UUR as $mo ) {
							if ( ! empty( $mo->url_address ) ) {
								$current_uptime_monitor_ids[] = $mo->url_uptime_monitor_id;
							}
						}
					}
				} else {
					$monitor_id = $UM->save_user_main_api_key( $api_key );
				}

				$results_uptime_monitor_ids = array();
				if ( $monitor_id ) {
					foreach ( $results as $result ) {
						if ( is_object( $result ) ) {
							$UUR->save_uptime_monitors( $monitor_id, $result->monitors->monitor );
							foreach ( $result->monitors->monitor as $mo ) {
								$results_uptime_monitor_ids[] = $mo->id;
							}
						}
					}
				}

				$diff_ids = array_diff( $current_uptime_monitor_ids, $results_uptime_monitor_ids );

				if ( is_array( $diff_ids ) && count( $diff_ids ) > 0 ) {
					foreach ( $diff_ids as $mo_id ) {
						$UUR->delete_all( array( 'conditions' => array( 'user_id' => get_current_user_id(), 'url_uptime_monitor_id' => $mo_id ) ) );
					}
				}

			}
			$list_contact = $this->get_list_notification_contact( $api_key );
			// error_log(print_r($list_contact, true));
			$this->option['uptime_default_notification_contact_id'] = isset( $_POST['select_default_noti_contact'] ) ? wp_unslash( $_POST['select_default_noti_contact'] ) : 0;
			$this->option['list_notification_contact'] = $list_contact;
			$this->option['api_key'] = $api_key;
		} else {
			$UM = new UptimeMonitor();
			$UM->delete_all( array( 'conditions' => array( 'user_id' => get_current_user_id() ) ) );
			// delete monitor urls
			$UUR = new UptimeUrl();
			$UUR->delete_all( array( 'conditions' => array( 'user_id' => get_current_user_id() ) ) );
			$this->option['list_notification_contact'] = '';
			$this->option['api_key'] = '';
		}
		// echo $count;
		// All is OK now
		$this->option['saved_time'] = current_time( 'timestamp' );
		update_option( $this->option_handle, $this->option );
		update_option( 'mainwp_aum_api_current_user_id', get_current_user_id() );
		return true;
	}

	public function get_option( $key ) {
		if ( isset( $this->option[ $key ] ) ) {
			return $this->option[ $key ];
		}
		return '';
	}

	public function set_option( $key, $value ) {
		$this->option[ $key ] = $value;
		return update_option( $this->option_handle, $this->option );
	}

	function get_all_uptime_monitors( $api_key ) {
		if ( empty( $api_key ) ) {
			$this->set_option( 'uptime_robot_message', 'Please enter your Uptime monitor API key.' );
			return false;
		}
		$valid = false;
		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $api_key );
		$results = $UR->get_all_monitors();
		$this->set_option( 'uptime_robot_message', '' );

		if ( empty( $results ) ) {
			$this->set_option( 'uptime_robot_message', 'Uptime Robot error.' );
		}

		if ( is_array( $results ) && count( $results ) > 0 ) {
			$result = current( $results ); // check first one only
			// $result = json_decode($result);
			if ( $result->stat == 'fail' ) {
				$this->set_option( 'uptime_robot_message', $result->message );
			} else {
				$valid = true;
			}

			if ( isset( $result->id ) && ($result->id == 212) ) { // api have no monitors
				$this->set_option( 'uptime_robot_message', '' );
				$this->set_option( 'api_key_status', 'valid' );
				return array();
			}
		}
		if ( $valid ) {
			$this->set_option( 'api_key_status', 'valid' );
			if ( ! isset( $result->id ) || ($result->id != 212) ) {
				$this->set_option( 'uptime_robot_message', '' );
			}
		} else {
			$this->set_option( 'api_key_status', 'invalid' );
			return false;
		}

		return $results;
	}

	function get_uptime_monitors( $api_key, $monitor_ids = array(), $logs = 0, $alertContacts = 0, $uptimeRatio = null ) {
		if ( empty( $api_key ) ) {
			$this->set_option( 'uptime_robot_message', 'Please enter your Uptime monitor API key.' );
			return false;
		}

		$valid = false;
		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $api_key );
		$result = $UR->get_monitors( $monitor_ids, $logs, $alertContacts, $uptimeRatio );

		// place this one first
		while ( strpos( $result, ',,' ) !== false ) {
			$result = str_replace( array( ',,' ), ',', $result ); // fix json
		}
		$result = str_replace( ',]', ']', $result ); // fix json
		$result = str_replace( '[,', '[', $result ); // fix json
		// error_log("get_monitors=" . $result);
		// print_r($result);
		$result = json_decode( $result );
		$this->set_option( 'uptime_robot_message', '' );
		if ( empty( $result ) ) {
			$this->set_option( 'uptime_robot_message', 'Uptime Robot error' );
		} else if ( $result->stat == 'fail' ) {
			$this->set_option( 'uptime_robot_message', $result->message );
		} else {
			$valid = true;
		}

		if ( isset( $result->id ) && ($result->id == 212) ) { // api have no monitors
			$this->set_option( 'uptime_robot_message', '' );
			$this->set_option( 'api_key_status', 'valid' );
			return array();
		}

		if ( $valid ) {
			$this->set_option( 'api_key_status', 'valid' );
			if ( ! isset( $result->id ) || ($result->id != 212) ) {
				$this->set_option( 'uptime_robot_message', '' );
			}
		} else {
			$this->set_option( 'api_key_status', 'invalid' );
			return false;
		}

		return $result;
	}

	public function get_colors_info() {
		$info = '<div id="aum_monitor_color_info">
                        <ul>
                            <li><span class="aum_upm_color_info up">&nbsp;</span> &nbsp;Up</li>
                            <li><span class="aum_upm_color_info down">&nbsp;</span> &nbsp;Down</li>
                            <li><span class="aum_upm_color_info not_checked">&nbsp;</span> &nbsp;Not Checked Yet</li>
                            <li><span class="aum_upm_color_info started">&nbsp;</span> &nbsp;Seems Off (will re-check quickly)</li>
                            <li><span class="aum_upm_color_info paused">&nbsp;</span> &nbsp;Paused</li>
                        </ul>
                    </div>';
		return $info;
	}

	public function aum_metabox() {

		// to fix bug call function from hook may be not load MVC
		require_once MVC_PLUGIN_PATH . 'core/loaders/mvc_admin_loader.php';
		$site_id = isset( $_GET['dashboard'] ) ? wp_unslash( $_GET['dashboard'] ) : 0;
		?>
        <div id="aum_mainwp_widget_uptime_monitor_content">
            <div id="aum_mainwp_uptime_monitor_loading">
                <i class="fa fa-spinner fa-5x fa-pulse"></i>                        
            </div>
            <div id="aum_mainwp_widget_uptime_monitor_content_inner" class="monitors">
            </div>    
        </div>                
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: 'admin_uptime_monitors_meta_box',
                        site_id: '<?php echo esc_attr( $site_id ); ?>',
                        wp_nonce: '<?php echo esc_attr( wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'meta_box' ) ); ?>'
                    },
                    error: function () {
                        jQuery('#aum_mainwp_uptime_monitor_loading').hide();
                        jQuery('#aum_mainwp_widget_uptime_monitor_content_inner').html('Request Timed Out - Try Again Later').fadeIn(2000);
                    },
                    success: function (response) {
                        jQuery('#aum_mainwp_uptime_monitor_loading').hide();
                        jQuery('#aum_mainwp_widget_uptime_monitor_content_inner').html(response).fadeIn(2000);
                    },
                    timeout: 20000
                });
            });
        </script>
        <?php
	}

	public function render_status_bar( $status, $ratio ) {
		switch ( $status ) {
			case 0:
				$sta = '<div class="aum_upm_status paused">' . $ratio . '%</div>';
				break;
			case 1:
				$sta = '<div class="aum_upm_status not_checked">' . $ratio . '%</div>';
				break;
			case 2:
				$sta = '<div class="aum_upm_status up">' . $ratio . '%</div>';
				break;
			case 8:
				$sta = '<div class="aum_upm_status seems_down">' . $ratio . '%</div>';
				break;
			case 9:
				$sta = '<div class="aum_upm_status down">' . $ratio . '%</div>';
				break;
		}
		return $sta;
	}
}

function mainwp_uptime_robot_monitors_activate() {
	require_once MVC_PLUGIN_PATH . 'core/loaders/mvc_admin_loader.php';
	$loader = new MvcAdminLoader();
	update_option( 'mainwp_uptime_robot_monitors_activated', 'yes' );
	require_once dirname( __FILE__ ) . '/uptime_robot_monitors_loader.php';
	$loader = new UptimeRobotMonitorsLoader();
	$loader->activate();

	$extensionActivator = new AdvancedUptimeMonitorExtensionActivator();
	$extensionActivator->activate();
}

function mainwp_uptime_robot_monitors_deactivate() {
	require_once MVC_PLUGIN_PATH . 'core/loaders/mvc_admin_loader.php';
	$loader = new MvcAdminLoader();
	require_once dirname( __FILE__ ) . '/uptime_robot_monitors_loader.php';
	$loader = new UptimeRobotMonitorsLoader();
	$loader->deactivate();

	$extensionActivator = new AdvancedUptimeMonitorExtensionActivator();
	$extensionActivator->deactivate();
}

register_activation_hook( __FILE__, 'mainwp_uptime_robot_monitors_activate' );
register_deactivation_hook( __FILE__, 'mainwp_uptime_robot_monitors_deactivate' );

class AdvancedUptimeMonitorExtensionActivator {

	protected $mainwpMainActivated = false;
	protected $childEnabled = false;
	protected $childKey = false;
	protected $childFile;
	protected $plugin_handle = 'advanced-uptime-monitor-extension';
	protected $product_id = 'Advanced Uptime Monitor Extension';
	protected $software_version = '2.1.1';

	public function __construct() {
		$this->childFile = __FILE__;
		add_filter( 'mainwp-getextensions', array( &$this, 'get_this_extension' ) );
		$this->mainwpMainActivated = apply_filters( 'mainwp-activated-check', false );
		if ( $this->mainwpMainActivated !== false ) {
			$this->activate_this();
		} else {
			add_action( 'mainwp-activated', array( &$this, 'activate_this' ) );
		}
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_notices', array( &$this, 'mainwp_error_notice' ) );
	}

	public function get_child_key() {

		return $this->childKey;
	}

	public function get_child_file() {

		return $this->childFile;
	}

	function admin_init() {
		if ( get_option( 'mainwp_uptime_robot_monitors_activated' ) == 'yes' ) {
			delete_option( 'mainwp_uptime_robot_monitors_activated' );
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
			self::aum_qsg();
			Advanced_Uptime_Monitor_Extension::get_install()->option_page();
		} else {
			?><div class="mainwp_info-box-yellow"><strong>The Extension has to be enabled to change the settings.</strong></div><?php
		}
		do_action( 'mainwp-pagefooter-extensions', __FILE__ );
	}

	function get_metaboxes( $metaboxes ) {
		if ( ! $this->childEnabled ) {
			return $metaboxes;
		}

		if ( ! is_array( $metaboxes ) ) {
			$metaboxes = array();
		}
		$metaboxes[] = array( 'plugin' => $this->childFile, 'key' => $this->childKey, 'metabox_title' => 'Advanced Uptime Monitor', 'callback' => array( &$this, 'render_metabox' ) );
		return $metaboxes;
	}

	function render_metabox() {
		Advanced_Uptime_Monitor_Extension::get_install()->aum_metabox();
	}

	function activate_this() {
		$this->mainwpMainActivated = apply_filters( 'mainwp-activated-check', $this->mainwpMainActivated );

		$activated = apply_filters( 'mainwp-activated-sub-check', array( 'advanced-uptime-monitor-extension' ) );
		if ( ! isset( $activated['result'] ) || 'VALID' != $activated['result'] ) {
			return;
		}

		if ( function_exists( 'mainwp_current_user_can' ) && ! mainwp_current_user_can( 'extension', 'advanced-uptime-monitor-extension' ) ) {
			return;
		}
		// fix bug uptime monitor for no get ajax to create DB
		Advanced_Uptime_Monitor_Extension::get_install();
		add_filter( 'mainwp-getmetaboxes', array( &$this, 'get_metaboxes' ) );

		$this->childEnabled = apply_filters( 'mainwp-extension-enabled-check', __FILE__ );
		if ( ! $this->childEnabled ) {
			return;
		}
		$this->childKey = $this->childEnabled['key'];
		// do_action("mainwp-extensions-checkupdate");
	}

	public function update_option( $option_name, $option_value ) {
		$success = add_option( $option_name, $option_value, '', 'no' );

		if ( ! $success ) {
			$success = update_option( $option_name, $option_value );
		}

		return $success;
	}

	function mainwp_error_notice() {
		global $current_screen;
		if ( $current_screen->parent_base == 'plugins' && $this->mainwpMainActivated == false ) {
			echo '<div class="error"><p>Advanced Uptime Monitor Extension requires <a href="http://mainwp.com/" target="_blank">MainWP Plugin</a> to be activated in order to work. Please install and activate <a href="http://mainwp.com/" target="_blank">MainWP Plugin</a> first.</p></div>';
		}
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

	public static function aum_qsg() {
		$plugin_data = get_plugin_data( MAINWP_AUM_PLUGIN_FILE, false );
		$description = $plugin_data['Description'];
		$extraHeaders = array( 'DocumentationURI' => 'Documentation URI' );
		$file_data = get_file_data( MAINWP_AUM_PLUGIN_FILE, $extraHeaders );
		$documentation_url = $file_data['DocumentationURI'];
		?>
        <div  class="mainwp_ext_info_box" id="aum-pth-notice-box">
            <div class="mainwp-ext-description"><?php esc_html_e( $description ); ?></div><br/>
            <b><?php esc_html_e( 'Need Help?', 'advanced-uptime-monitor-extension' ); ?></b> <?php esc_html_e( 'Review the Extension', 'advanced-uptime-monitor-extension' ); ?> <a href="<?php echo esc_attr( $documentation_url ); ?>" target="_blank"><i class="fa fa-book"></i> <?php esc_html_e( 'Documentation', 'advanced-uptime-monitor-extension' ); ?></a>. 
            <a href="#" id="mainwp-aum-quick-start-guide"><i class="fa fa-info-circle"></i> <?php esc_html_e( 'Show Quick Start Guide', 'advanced-uptime-monitor-extension' ); ?></a></div>
        <div  class="mainwp_ext_info_box" id="mainwp-aum-tips" style="color: #333!important; text-shadow: none!important;">
            <span><a href="#" class="mainwp-show-tut" number="1"><i class="fa fa-book"></i> <?php esc_html_e( 'Get a Uptime Robot API Key', 'advanced-uptime-monitor-extension' ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-tut"  number="2"><i class="fa fa-book"></i> <?php esc_html_e( 'Quick Start', 'advanced-uptime-monitor-extension' ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-tut"  number="3"><i class="fa fa-book"></i> <?php esc_html_e( 'Display a Monitor in the Dashboard Widget', 'advanced-uptime-monitor-extension' ) ?></a></span><span><a href="#" id="mainwp-aum-tips-dismiss" style="float: right;"><i class="fa fa-times-circle"></i> <?php esc_html_e( 'Dismiss', 'advanced-uptime-monitor-extension' ); ?></a></span>
            <div class="clear"></div>
            <div id="mainwp-aum-tuts">
                <div class="mainwp-aum-tut" number="1">
                    <h3>Get a Uptime Robot API Key</h3>
                    <ol>
                        <li>To get your Uptime Robot API Key in order to use MainWP Advanced Uptime Monitor, visit Uptime Robot Webpage</li>
                        <li>Click the "Start Now" button</li>
                        <li>Fill up the registration form</li>
                        <li>Click the "Register" button</li>
                        <li>Check your e-mail and activate your account (follow the link in the email. You will get confirmation note)</li>
                        <li>After confirmation you are successfully registered, next you need to log in and generate an API Key</li>
                        <li>Click the "Sign-in" button</li>
                        <li>Type in your email address and password, and click the "Sign-In" button</li>
                        <li>You will be prompted to the "My Monitors" page,</li>
                        <li>Proceed to the "My Settings" page</li>
                        <li>Scroll down to the API Information area and click the "show details" link</li>
                        <li>Locate and click the "Click to create one." link</li>
                        <li>Your API Key will appear.</li>
                    </ol>
                </div>
                <div class="mainwp-aum-tut"  number="2">
                    <h3>Quick Start</h3>
                    <ol>
                        <li>Enter your Uptime Robot API Key</li>
                        <li>Click the Save Settings button (After setting your UptimeRobot API, your default notification email will be pulled from your UptimeRobot settings.)</li>
                        <li>To add a monitor, click the "Add Monitor" button</li>
                        <li>Popup window will appear, enter a Monitor Name, Website Url and choose The Monitor Type</li>
                        <li>Click the "Create" button</li>
                        <li>The monitor will appear in the list</li>
                    </ol>
                </div>
                <div class="mainwp-aum-tut"  number="3">
                    <h3>Display a Monitor in the Dashboard Widget</h3>
                    <ol>
                        <li>Select one or more monitors that you want to add to the dashboard widget</li>
                        <li>Select the "Display on Dashboard" option from the Bulk Actions list</li>
                        <li>Click the "Apply" button</li>
                        <li>The "Display on Dashboard" status column will be changed properly to indicate which monitors are added to the widget.</li>
                    </ol>
                </div>
            </div>
        </div>
        <?php
	}
}

$mainwpAdvancedUptimeMonitorExtensionActivator = new AdvancedUptimeMonitorExtensionActivator();

