<?php

class UptimeUrl extends MvcModel {

	public $primary_key = 'url_id';
	public $just_insertupdate_id;
	var $table = '{prefix}aum_urls';
	var $validate = array(
		'url_friendly_name' => array(
			'rule' => 'not_empty',
			'message' => 'Please enter Site Name',
		),
		'url_address' => array(
			'rule' => 'not_empty',
			'message' => 'Please enter Site URL',
		),
		'not_not_email' => array(
			'rule' => 'email',
			'message' => 'Please enter valid notification email address',
		),
	);

	function after_save( $object ) {
		if ( ! empty( $object ) ) {
			$this->just_insertupdate_id = $object->{$this->primary_key};
		} else {
			$this->just_insertupdate_id = null;
		}
	}

	function save_uptime_monitors( $monitor_id, $urls_monitor ) {
		if ( empty( $monitor_id ) ) {
			return false;
		}
		if ( ! is_array( $urls_monitor ) && count( $urls_monitor ) < 1 ) {
			return false;
		}
		$saved_ids = array();
		foreach ( $urls_monitor as $monitor ) {
			$current = $this->find(array(
				'conditions' => array(
					'user_id' => get_current_user_id(),
					'url_uptime_monitor_id' => $monitor->id,
					'monitor_id' => $monitor_id,
				),
			));
			if ( ! empty( $current ) ) {
				$this->params['data']['UptimeUrl']['url_id'] = $current[0]->url_id;
			}
			$this->params['data']['UptimeUrl']['user_id'] = get_current_user_id();
			$this->params['data']['UptimeUrl']['monitor_id'] = $monitor_id;
			$this->params['data']['UptimeUrl']['url_uptime_monitor_id'] = $monitor->id;
			$this->params['data']['UptimeUrl']['url_friendly_name'] = $monitor->friendlyname;
			$this->params['data']['UptimeUrl']['url_address'] = $monitor->url;
			$this->params['data']['UptimeUrl']['url_monitor_type'] = $monitor->type;
			$this->params['data']['UptimeUrl']['url_monitor_subtype'] = $monitor->subtype;
			$this->params['data']['UptimeUrl']['url_monitor_keywordtype'] = $monitor->keywordtype;
			$this->params['data']['UptimeUrl']['url_monitor_keywordvalue'] = $monitor->keywordvalue;
			$this->save( $this->params['data'] );
			if ( ! empty( $this->just_insertupdate_id ) ) {
				$saved_ids[] = $this->just_insertupdate_id;
			}
		}
		return $saved_ids;
	}
}
