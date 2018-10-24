<?php

class MainWP_Sucuri_DB {

	private $mainwp_sucuri_db_version = '1.3';
	//Singleton
	private static $instance = null;
	private $table_prefix;

	static function get_instance() {
		if ( null == MainWP_Sucuri_DB::$instance ) {
			MainWP_Sucuri_DB::$instance = new MainWP_Sucuri_DB();
		}
		return MainWP_Sucuri_DB::$instance;
	}

	//Constructor
	function __construct() {
		global $wpdb;
		$this->table_prefix = $wpdb->prefix . 'mainwp_';
	}

	function table_name( $suffix ) {
		return $this->table_prefix . $suffix;
	}

	//Support old & new versions of wordpress (3.9+)
	public static function use_mysqli() {
		/** @var $wpdb wpdb */
		if ( ! function_exists( 'mysqli_connect' ) ) {
			return false; }

		global $wpdb;
		return ($wpdb->dbh instanceof mysqli);
	}

	//Installs new DB
	function install() {
		global $wpdb;
		$currentVersion = get_site_option( 'mainwp_sucuri_db_version' );
		if ( $currentVersion == $this->mainwp_sucuri_db_version ) {
			return; }
		$charset_collate = $wpdb->get_charset_collate();
		$sql = array();

		$tbl = 'CREATE TABLE `' . $this->table_name( 'sucuri' ) . '` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`site_url` text NOT NULL,
`lastscan` int(11) NOT NULL DEFAULT 0,
`remind` varchar(10) NOT NULL,
`lastremind` int(11) NOT NULL DEFAULT 0';
		if ( '' == $currentVersion ) {
			$tbl .= ',
PRIMARY KEY  (`id`)  '; }
		$tbl .= ') ' . $charset_collate;
		$sql[] = $tbl;

		$tbl = 'CREATE TABLE `' . $this->table_name( 'sucuri_report' ) . '` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`site_url` text NOT NULL,
`timescan` int(11) NOT NULL,
`data` text NOT NULL';
		if ( '' == $currentVersion ) {
			$tbl .= ',
PRIMARY KEY  (`id`)  '; }
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
		update_option( 'mainwp_sucuri_db_version', $this->mainwp_sucuri_db_version );
	}

	public function update_sucuri( $sucuri ) {
		/** @var $wpdb wpdb */
		global $wpdb;
		$id = false;
		if ( isset( $sucuri['id'] ) ) {
			$id = intval( $sucuri['id'] ); }

		if ( $id ) {
			if ( $wpdb->update( $this->table_name( 'sucuri' ), $sucuri, array( 'id' => intval( $id ) ) ) ) {
				return $this->get_sucuri_by( 'id', $id ); }
		} else {
			if ( $wpdb->insert( $this->table_name( 'sucuri' ), $sucuri ) ) {
				return $this->get_sucuri_by( 'id', $wpdb->insert_id );
			}
		}
		return false;
	}

	public function get_sucuri_by( $by = 'id', $value = null ) {
		global $wpdb;
		if ( empty( $value ) ) {
			return false; }
		if ( 'id' == $by ) {
			$sql = $wpdb->prepare( 'SELECT * FROM ' . $this->table_name( 'sucuri' ) . ' WHERE `id` = %d ', $value );
			return $wpdb->get_row( $sql );
		} else if ( 'site_url' == $by ) {
			$sql = $wpdb->prepare( 'SELECT * FROM ' . $this->table_name( 'sucuri' ) . " WHERE `site_url` = '%s' ", $value );
			return $wpdb->get_row( $sql );
		}
		return false;
	}

	public function remove_sucuri( $sId ) {
		/** @var $wpdb wpdb */
		global $wpdb;
		if ( $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $this->table_name( 'sucuri' ) . ' WHERE id = %d', $sId ) ) ) {
			return true; }
		return false;
	}

	public function save_report( $report ) {
		/** @var $wpdb wpdb */
		global $wpdb;
		$id = false;
		if ( isset( $report['id'] ) ) {
			$id = $report['id']; }

		if ( $id ) {
			if ( $wpdb->update( $this->table_name( 'sucuri_report' ), $report, array( 'id' => intval( $id ) ) ) ) {
				return $this->get_report_by( 'id', $id ); }
		} else {
			if ( $wpdb->insert( $this->table_name( 'sucuri_report' ), $report ) ) {
				return $this->get_report_by( 'id', $wpdb->insert_id );
			}
		}
		return false;
	}

	public function get_report_by( $by = 'id', $value = null ) {
		global $wpdb;
		if ( empty( $value ) ) {
			return false; }
		if ( 'id' == $by ) {
			$sql = $wpdb->prepare( 'SELECT * FROM ' . $this->table_name( 'sucuri_report' ) . ' WHERE `id` = %d ', $value );
			$sucuri = $wpdb->get_row( $sql );
			return $sucuri;
		} else if ( 'site_url' == $by ) {
			$sql = $wpdb->prepare( 'SELECT * FROM ' . $this->table_name( 'sucuri_report' ) . " WHERE `site_url` = '%s' ORDER BY timescan DESC", $value );
			$sucuri = $wpdb->get_results( $sql );
			return $sucuri;
		} else if ( 'timescan' == $by ) {
			$sql = $wpdb->prepare( 'SELECT * FROM ' . $this->table_name( 'sucuri_report' ) . ' WHERE `timescan` = %d LIMIT 1', $value );
			$sucuri = $wpdb->get_row( $sql );
			return $sucuri;
		}
		return false;
	}

	public function remove_report_by( $by = 'id', $value ) {
		/** @var $wpdb wpdb */
		global $wpdb;
		if ( 'id' == $by ) {
			if ( $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $this->table_name( 'sucuri_report' ) . ' WHERE id = %d', $value ) ) ) {
				return true; }
		} else if ( 'site_url' == $by ) {
			if ( $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $this->table_name( 'sucuri_report' ) . ' WHERE site_url = %s', $value ) ) ) {
				return true; }
		}
		return false;
	}

	protected function escape( $data ) {
		/** @var $wpdb wpdb */
		global $wpdb;

		if ( function_exists( 'esc_sql' ) ) {
			return esc_sql( $data ); } else { 			return $wpdb->escape( $data ); }
	}

	public function query( $sql ) {
		if ( null == $sql ) {
			return false; }

		/** @var $wpdb wpdb */
		global $wpdb;
		$result = @self::_query( $sql, $wpdb->dbh );

		if ( ! $result || (@self::num_rows( $result ) == 0) ) {
			return false; }
		return $result;
	}

	public static function _query( $query, $link ) {
		if ( self::use_mysqli() ) {
			return mysqli_query( $link, $query );
		} else {
			return mysql_query( $query, $link );
		}
	}

	public static function fetch_object( $result ) {
		if ( self::use_mysqli() ) {
			return mysqli_fetch_object( $result );
		} else {
			return mysql_fetch_object( $result );
		}
	}

	public static function free_result( $result ) {
		if ( self::use_mysqli() ) {
			return mysqli_free_result( $result );
		} else {
			return mysql_free_result( $result );
		}
	}

	public static function data_seek( $result, $offset ) {
		if ( self::use_mysqli() ) {
			return mysqli_data_seek( $result, $offset );
		} else {
			return mysql_data_seek( $result, $offset );
		}
	}

	public static function fetch_array( $result, $result_type = null ) {
		if ( self::use_mysqli() ) {
			return mysqli_fetch_array( $result, (null == $result_type ? MYSQLI_BOTH : $result_type) );
		} else {
			return mysql_fetch_array( $result, (null == $result_type ? MYSQL_BOTH : $result_type) );
		}
	}

	public static function num_rows( $result ) {
		if ( self::use_mysqli() ) {
			return mysqli_num_rows( $result );
		} else {
			return mysql_num_rows( $result );
		}
	}

	public static function is_result( $result ) {
		if ( self::use_mysqli() ) {
			return ($result instanceof mysqli_result);
		} else {
			return is_resource( $result );
		}
	}

	public function get_results_result( $sql ) {
		if ( null == $sql ) {
			return null; }
		/** @var $wpdb wpdb */
		global $wpdb;
		return $wpdb->get_results( $sql, OBJECT_K );
	}
}
