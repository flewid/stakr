<?php
class UptimeRobotMonitorsLoader extends MvcPluginLoader {
	var $db_version = '1.0';
	//var $tables = array('monitors' => 'wp_aum_monitors', 'urls' => 'wp_aum_urls', 'stats' => 'wp_aum_stats');
			var $tables = array();
	function activate() {
		 global $wpdb;
							 // to fix table prefix bug
		 $this->tables = array( 'monitors' => $wpdb->prefix . 'aum_monitors', 'urls' => $wpdb->prefix . 'aum_urls', 'stats' => $wpdb->prefix . 'aum_stats' );
								  // This call needs to be made to activate this app within WP MVC

		$this->activate_app( __FILE__ );

		// Perform any databases modifications related to plugin activation here, if necessary
		require_once ABSPATH.'wp-admin/includes/upgrade.php';

		add_option( 'uptime_robot_monitors_db_version', $this->db_version );

		// Use dbDelta() to create the tables for the app here
		// $sql = '';
		// dbDelta($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS `'.$this->tables['monitors']."` (
              `monitor_id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `monitor_name` varchar(100) NOT NULL,
              `monitor_type` int(5) NOT NULL,
              `monitor_api_key` varchar(100) NOT NULL,
              `monitor_not_email` varchar(100) NOT NULL,
              `monitor_main` tinyint(1) NOT NULL DEFAULT '1',
              `monitor_reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`monitor_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=65 ;";
		dbDelta( $sql );
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$this->tables['urls']."` (
  `url_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `monitor_id` int(11) NOT NULL,
  `url_uptime_monitor_id` varchar(100) NOT NULL,
  `url_api_key` varchar(100) NOT NULL,
  `url_friendly_name` varchar(100) NOT NULL,
  `url_address` varchar(100) NOT NULL,
  `url_not_email` varchar(100) NOT NULL,
  `url_monitor_type` tinyint(4) NOT NULL,
  `url_monitor_subtype` tinyint(4) NOT NULL,
  `url_monitor_keywordtype` tinyint(4) NOT NULL,
  `url_monitor_keywordvalue` text NOT NULL,
  `dashboard` tinyint(1) NOT NULL DEFAULT '0',  
  `url_reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`url_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;";
		dbDelta( $sql );
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$this->tables['stats']."` (
              `event_id` int(11) NOT NULL AUTO_INCREMENT,
              `monitor_id` int(11) NOT NULL,
              `type` tinyint(3) NOT NULL DEFAULT '0',
              `monitor_type` tinyint(4) NOT NULL DEFAULT '-1',
              `event_datetime_gmt` datetime NOT NULL,
              PRIMARY KEY (`event_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=126 ;";
		dbDelta( $sql );
	}
	function deactivate() {

		// This call needs to be made to deactivate this app within WP MVC

		$this->deactivate_app( __FILE__ );

		// Perform any databases modifications related to plugin deactivation here, if necessary

	}
}
?>
