<?php

class MainWP_Branding_DB {
	private static $instance = null;
	//Singleton
	private $mainwp_branding_db_version = '1.8';
	private $table_prefix;

	function __construct() {

		global $wpdb;
		$this->table_prefix = $wpdb->prefix . 'mainwp_';
	}

	//Constructor

	static function get_instance() {

		if ( null == MainWP_Branding_DB::$instance ) {
			MainWP_Branding_DB::$instance = new MainWP_Branding_DB();
		}

		return MainWP_Branding_DB::$instance;
	}

	public static function fetch_object( $result ) {

		if ( self::use_mysqli() ) {
			return mysqli_fetch_object( $result );
		} else {
			return mysql_fetch_object( $result );
		}
	}

	//Support old & new versions of wordpress (3.9+)

	public static function free_result( $result ) {

		if ( self::use_mysqli() ) {
			return mysqli_free_result( $result );
		} else {
			return mysql_free_result( $result );
		}
	}

	//Installs new DB

	public static function data_seek( $result, $offset ) {

		if ( self::use_mysqli() ) {
			return mysqli_data_seek( $result, $offset );
		} else {
			return mysql_data_seek( $result, $offset );
		}
	}

	public static function fetch_array( $result, $result_type = null ) {

		if ( self::use_mysqli() ) {
			return mysqli_fetch_array( $result, ( null == $result_type ? MYSQLI_BOTH : $result_type ) );
		} else {
			return mysql_fetch_array( $result, ( null == $result_type ? MYSQL_BOTH : $result_type ) );
		}
	}

	public static function is_result( $result ) {

		if ( self::use_mysqli() ) {
			return ( $result instanceof mysqli_result );
		} else {
			return is_resource( $result );
		}
	}

	function install() {

		global $wpdb;
		$currentVersion = get_site_option( 'mainwp_branding_db_version' );
		if ( $currentVersion == $this->mainwp_branding_db_version ) {
			return;
		}
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = array();

		$tbl = 'CREATE TABLE `' . $this->table_name( 'child_branding' ) . '` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`site_url` text NOT NULL,
`plugin_header` text NOT NULL,
`hide_child_plugin` tinyint(1) NOT NULL DEFAULT 0,
`disable_theme_plugin_change` tinyint(1) NOT NULL DEFAULT 0,
`show_support_button` tinyint(1) NOT NULL DEFAULT 0,
`support_email` text NOT NULL,
`support_message` text NOT NULL,
`remove_restore` tinyint(1) NOT NULL DEFAULT 0,
`remove_setting` tinyint(1) NOT NULL DEFAULT 0,
`remove_server_info` tinyint(1) NOT NULL DEFAULT 0,
`remove_wp_tools` tinyint(1) NOT NULL DEFAULT 0,
`remove_wp_setting` tinyint(1) NOT NULL DEFAULT 0,
`button_contact_label` varchar(64) NOT NULL DEFAULT "",
`send_email_message` varchar(512) NOT NULL DEFAULT "",
`extra_settings` text NOT NULL,
`override` tinyint(1) NOT NULL DEFAULT 0';
		if ( '' == $currentVersion ) {
			$tbl .= ',
PRIMARY KEY  (`id`)  ';
		}
		$tbl .= ') ' . $charset_collate;
		$sql[] = $tbl;

		error_reporting( 0 ); // make sure to disable any error output
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
		//        global $wpdb;
		//        echo $wpdb->last_error;
		//        exit();
		update_option( 'mainwp_branding_db_version', $this->mainwp_branding_db_version );
	}

	function table_name( $suffix ) {

		return $this->table_prefix . $suffix;
	}

	public function update_branding( $branding ) {

		/** @var $wpdb wpdb */
		global $wpdb;
		$id = $branding['id'];
		//print_r($branding);
		if ( $id ) {
			if ( $wpdb->update( $this->table_name( 'child_branding' ), $branding, array( 'id' => intval( $id ) ) ) ) {
				return $this->get_branding_by( 'id', $id );
			}
		} else if ( $wpdb->insert( $this->table_name( 'child_branding' ), $branding ) ) {
			return $this->get_branding_by( 'id', $wpdb->insert_id );
		}

		return false;
	}

	public function get_branding_by( $by = 'id', $value = null ) {
		global $wpdb;

		if ( empty( $by ) || empty( $value ) ) {
			return null;
		}

		$sql = '';
		if ( 'id' == $by ) {
			$sql = $wpdb->prepare( 'SELECT * FROM ' . $this->table_name( 'child_branding' ) . ' WHERE `id`=%d ', $value );
		} else if ( 'site_url' == $by ) {
			$sql = $wpdb->prepare( 'SELECT * FROM ' . $this->table_name( 'child_branding' ) . " WHERE `site_url` = '%s' ", $value );
		}

		$branding = null;
		if ( ! empty( $sql ) ) {
			$branding = $wpdb->get_row( $sql );
		}

		return $branding;
	}

	public function query( $sql ) {

		if ( null == $sql  ) {
			return false;
		}

		/** @var $wpdb wpdb */
		global $wpdb;
		$result = @self::_query( $sql, $wpdb->dbh );

		if ( ! $result || ( @self::num_rows( $result ) == 0 ) ) {
			return false;
		}

		return $result;
	}

	public static function _query( $query, $link ) {

		if ( self::use_mysqli() ) {
			return mysqli_query( $link, $query );
		} else {
			return mysql_query( $query, $link );
		}
	}

	public static function use_mysqli() {

		/** @var $wpdb wpdb */
		if ( ! function_exists( 'mysqli_connect' ) ) {
			return false;
		}

		global $wpdb;

		return ( $wpdb->dbh instanceof mysqli );
	}

	public static function num_rows( $result ) {

		if ( self::use_mysqli() ) {
			return mysqli_num_rows( $result );
		} else {
			return mysql_num_rows( $result );
		}
	}

	public function get_results_result( $sql ) {

		if ( null == $sql ) {
			return null;
		}
		/** @var $wpdb wpdb */
		global $wpdb;

		return $wpdb->get_results( $sql, OBJECT_K );
	}

	protected function escape( $data ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( function_exists( 'esc_sql' ) ) {
			return esc_sql( $data );
		} else {
			return $wpdb->escape( $data );
		}
	}
}
