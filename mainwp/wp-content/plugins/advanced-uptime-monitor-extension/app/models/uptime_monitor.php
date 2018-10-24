<?php

class UptimeMonitor extends MvcModel {

	public $primary_key = 'monitor_id';
	var $per_page = 100;
	var $table = '{prefix}aum_monitors';
	var $current_user_id = 0;
	// do not need validate monitor_name, monitor_type, monitor_not_email
	var $validate = array(
		//                                                                  'monitor_name' => array(
		//                                              'rule' => 'not_empty',
		//                                              'message' => 'Please enter monitor name'),
		'monitor_api_key' => array(
			'rule' => 'not_empty',
			'message' => 'Please enter monitor API key',
		),
			//                        'monitor_not_email' => array(
			//                                              'rule' => 'email',
			//                                              'message' => 'Please enter valid notification email address'),
			//                        'monitor_type' => array(
			//                                              'rule' => 'numeric',
			//                                              'message' => 'Please choose monitor type(s)')
	);

	public function __construct() {
		$this->current_user_id = get_current_user_id();
		parent::__construct();
	}

	function save_user_main_api_key( $api_key ) {
		// one user only have one main_api_key
		//$this->delete_all(array('user_id' => $this->current_user_id));
		//$UUR = new UptimeUrl();
		//$UUR->delete_all(array('conditions' => array('user_id' => $this->current_user_id)));
		$data = array(
			'user_id' => $this->current_user_id,
			'monitor_api_key' => $api_key,
		);
		return $this->create( $data );
	}

	function get_user_main_api_key() {
		$result = $this->get_user_main_monitor();
		if ( false !== $result && $result->monitor_api_key ) {
			return $result->monitor_api_key;
		}
		return false;
	}

	function get_user_main_monitor( $user_id = 0 ) {
		$result = $this->find_one( array( 'conditions' => array( 'user_id' => $this->current_user_id ) ) );
		if ( ! empty( $result ) ) {
			return $result;
		}
		return false;
	}
}
