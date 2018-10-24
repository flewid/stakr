<?php

class UptimeStats extends MvcModel {

	public $primary_key = 'event_id';
	var $per_page = 10;
	var $table = '{prefix}aum_stats';

	function get_event( $condition = 1 ) {

	}

	function get_last_event( $conditions = array() ) {
		$event = $this->find_one( array( 'order' => 'event_datetime_gmt DESC', 'conditions' => $conditions ) );
		return $event;
	}

	// retrieve events: monitor is on or off (not url status events)
	function get_events( $conditions = '1' ) {
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'aum_stats WHERE ' . $conditions . ' ORDER BY event_datetime_gmt ASC, monitor_id ASC';
		$events = $wpdb->get_results( $sql );
		return $events;
	}
}
