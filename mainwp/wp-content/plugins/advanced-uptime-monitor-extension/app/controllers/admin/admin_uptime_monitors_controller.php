<?php

class AdminUptimeMonitorsController extends MvcAdminController {

	var $default_columns = array( 'monitor_id', 'monitor_name' );
	var $monitor_types = array( '1' => 'HTTP(s)', '2' => 'Keyword Checking', '3' => 'Ping', '4' => 'TCP Ports' );
	var $log_statuses = array(
		0 => 'not_available',
		1 => 'down',
		2 => 'up',
		99 => 'paused',
		98 => 'started',
	);
	var $monitor_statuses = array(
		0 => 'paused',
		1 => 'down',
		2 => 'up',
		3 => 'monitor_on',
		4 => 'monitor_off',
		99 => 'paused',
		98 => 'started',
	);
	var $monitor_ports = array(
		'1' => '80',
		'2' => '443',
		'3' => '21',
		'4' => '25',
		'5' => '110',
		'6' => '143',
	);
	var $diagram_width = 450;
	public static $nonce_token = 'mainwp-aum-extension-';

	public function index() {
		$this->load_model( 'UptimeMonitor' );
		$this->load_model( 'UptimeStats' );
		$this->load_model( 'UptimeUrl' );
		if ( 'yes' != get_option( 'mainwp_aum_requires_reload_monitors' ) ) {
			$this->check_unavailable_url_monitors();
		}

		$objects = $this->UptimeMonitor->find( array( 'conditions' => array( 'user_id' => get_current_user_id() ) ) );

		$this->set( 'objects', $objects );
	}

	public function meta_box() {
		$this->ajax_check_permissions( 'meta_box' );

		$this->diagram_width = 360;
		if ( isset( $_POST['site_id'] ) && $_POST['site_id'] ) {
			$site_id = $_POST['site_id'];
			$conds = array();
		} else {
			$site_id = null;
			$conds = array( 'dashboard' => 1 );
		}
		$this->get_monitors( $conds, $site_id );
		$this->render_view( 'admin/meta_box', array( 'layout' => 'ajax_clean' ) );
	}

	public function option_page() {
		$this->ajax_check_permissions( 'option_page' );
		$this->diagram_width = 510;
		$this->get_monitors();
		$this->render_view( 'admin/option_page', array( 'layout' => 'ajax_clean' ) );
	}

	public function get_monitors( $conditions = array(), $site_id = null ) {

		if ( get_option( 'mainwp_aum_requires_reload_monitors' ) != 'yes' ) {
			$this->check_unavailable_url_monitors();
		}
		$conds = array( 'user_id' => get_current_user_id() );
		if ( $site_id ) {
			$this->set( 'site_id', $site_id );
			global $mainwpAdvancedUptimeMonitorExtensionActivator;
			$website = apply_filters( 'mainwp-getsites', $mainwpAdvancedUptimeMonitorExtensionActivator->get_child_file(), $mainwpAdvancedUptimeMonitorExtensionActivator->get_child_key(), $site_id );
			if ( $website && is_array( $website ) ) {
				$website = current( $website );
			}
			if ( empty( $website ) || ! isset( $website['url'] ) ) {
				$this->flash( 'notice', 'Error site data!' );
				return false;
			}
			$conds['url_address'] = array( $website['url'], rtrim( $website['url'], '/' ) );
			// $conds['url_address'] = $website['url'];
		}
		$this->load_model( 'UptimeUrl' );
		$this->load_model( 'UptimeMonitor' );
		$this->load_model( 'UptimeStats' );
		$main_monitor = $this->UptimeMonitor->get_user_main_monitor();
		if ( empty( $main_monitor ) ) {
			$this->flash( 'notice', 'Please set Uptime Robot API first!' );
			return false;
		}
		$main_api_key = $main_monitor->monitor_api_key;
		$monitor_id = $main_monitor->monitor_id;
		$off = $this->get_ur_gmt_offset_time( $main_api_key );
		if ( false !== $off ) {
			$gmt_offset = $off['offset_time'];
		} else {
			$gmt_offset = 0;
		}
		$conds = array_merge( $conds, $conditions );
		$total = $this->UptimeUrl->count( array( 'conditions' => $conds ) );
		$get_page = isset( $_POST['get_page'] ) && $_POST['get_page'] > 0 ? $_POST['get_page'] : 1;
		// print_r($conds);
		$urls = $this->UptimeUrl->find(array(
			'conditions' => $conds,
			'per_page' => 50,
			'page' => $get_page,
		));
		$monitor_urls = array();
		$mo_ids = array();
		$stats = array();
		if ( $total > 0 ) {
			foreach ( $urls as $url ) {
				$mo_ids[] = intval( $url->url_uptime_monitor_id );
				$monitor_urls[ $url->url_uptime_monitor_id ] = $url;
				if ( count( $mo_ids ) >= 50 ) {
					break;
				}
			}
			// statistics
			$result = Advanced_Uptime_Monitor_Extension::get_install()->get_uptime_monitors( $main_api_key, $mo_ids, 1, 1, 1 );
			if ( false === $result ) {
				$this->flash( 'notice', 'Statitics data empty!' );
				// return;
			} else {
				$time_limit = 60 * 60 * 24 * 1;
				$current_unix_gmt_time = time();
				$unix_gmt_time_start = $current_unix_gmt_time - $time_limit;

				$width_last_status = array(); // save width of last status to fix width of status bar equal

				if ( is_array( $result->monitors->monitor ) && count( $result->monitors->monitor ) > 0 ) {
					foreach ( $result->monitors->monitor as $monitor ) {
						if ( ! in_array( $monitor->id, $mo_ids ) ) {
							unset( $monitor_urls[ $monitor->id ] );
							continue;
						}
						// getting last event recorded to database
						$last_existing_event = $this->UptimeStats->get_last_event( array( 'monitor_id' => $monitor->id ) );
						$unix_gmt_time_last_event = 0;
						if ( ! empty( $last_existing_event ) ) {
							$unix_gmt_time_last_event = strtotime( $last_existing_event->event_datetime_gmt );
						}
						$monitor_urls[ $monitor->id ]->monitor_status = $monitor->status;
						$monitor_urls[ $monitor->id ]->monitor_alltimeuptimeratio = $monitor->alltimeuptimeratio;

						// searching for the last event before happend befor last 24 hours;
						if ( is_array( $monitor->log ) ) {
							foreach ( $monitor->log as $index => $log ) {
								$datetime = new DateTime( $log->datetime );
								$datetime = $datetime->format( 'Y-m-d H:i:s' );
								$unix_gmt_time_log = strtotime( $datetime ) - $gmt_offset * 60 * 60;
								// storing event to database;
								if ( $unix_gmt_time_last_event < $unix_gmt_time_log ) {
									$data['UptimeStats'] = array(
										'monitor_id' => $monitor->id,
										'type' => $log->type,
										'event_datetime_gmt' => date( 'Y-m-d H:i:s', $unix_gmt_time_log ),
									);
									$this->UptimeStats->create( $data );
								}
							}
						}

						$start_log = $this->UptimeStats->get_last_event( array( 'monitor_id' => $monitor->id, 'event_datetime_gmt <=' => date( 'Y-m-d H:i:s', $unix_gmt_time_start ) ) );

						/* Fix bug warning  Creating default object from empty value in > PHP 5.4 ? */
						if ( ! is_object( $stats[ $monitor->id ][0] ) ) {
							$stats[ $monitor->id ][0] = new stdClass();
						}

						if ( ! empty( $start_log ) ) {
							$stats[ $monitor->id ][0]->type = $start_log->type;
						} else {
							$stats[ $monitor->id ][0]->type = 0; // data not available
						} $stats[ $monitor->id ][0]->event_datetime_gmt = date( 'Y-m-d H:i:s', $unix_gmt_time_start );
						$stats[ $monitor->id ][0]->status_bar_length = 0;
						$width_last_status[ $monitor->id ] = 100; // $this->diagram_width;
					}
				}

				if ( count( $mo_ids ) == 0 ) {
					$mo_ids = array( 0 ); // to fix bug
				}
				// getting stats from db, for simplified sorting
				$stats_from_db = $this->UptimeStats->get_events( 'event_datetime_gmt >= "' . date( 'Y-m-d H:i:s', $unix_gmt_time_start ) . '" AND monitor_type="-1" AND monitor_id IN (' . implode( $mo_ids, ',' ) . ')' );
				// print_r($stats_from_db);
				// exit();
				// print_r($stats);
				// $seconds_in_one_px = $time_limit/$this->diagram_width;
				foreach ( $stats_from_db as $event ) {
					if ( empty( $event ) || empty( $event->monitor_id ) || empty( $stats[ $event->monitor_id ] ) ) {
						continue;
					}
					$count = count( $stats[ $event->monitor_id ] );
					// echo $event->event_datetime_gmt . "=========" . $stats[$event->monitor_id][$count - 1]->event_datetime_gmt . "<br />";
					$real_val = (strtotime( $event->event_datetime_gmt ) - strtotime( $stats[ $event->monitor_id ][ $count - 1 ]->event_datetime_gmt )) / $time_limit;
					$status_display_length = floor( 100 * $real_val );
					// $unix_time_length = strtotime($event->event_datetime_gmt) - strtotime($stats[$event->monitor_id][$count - 1]->event_datetime_gmt);
					// $status_display_length = (int)($unix_time_length / $seconds_in_one_px);
					$stats[ $event->monitor_id ][ $count ] = (object) array(
								'type' => $event->type,
								'event_datetime_gmt' => $event->event_datetime_gmt,
								'status_bar_length' => $status_display_length,
					);
					$width_last_status[ $event->monitor_id ] -= $status_display_length;
				}
				// print_r($stats);
				// Set the last status
				foreach ( $stats as $mo_id => $mo_stats ) {
					$count = count( $mo_stats );
					// $real_val = ($current_unix_gmt_time - strtotime($stats[$mo_id][$count - 1]->event_datetime_gmt))/ $time_limit;
					$stats[ $mo_id ][ $count ] = (object) array(
								'type' => 0,
								'event_datetime_gmt' => date( 'Y-m-d H:i:s', $current_unix_gmt_time ),
								'status_bar_length' => $width_last_status[ $mo_id ],
					);
				}
			}
		}
		// statistics end
		$this->set( 'stats', $stats );
		$this->set( 'get_page', $get_page );
		$this->set( 'total', $total );
		$this->set( 'monitor_id', $monitor_id );
		$this->set( 'urls', $monitor_urls );
		$this->set( 'log_gmt_offset', $gmt_offset );
		return true;
	}

	public function show() {
		$this->set_object();
	}

	public function add() {
		$this->set_object();
		if ( ! empty( $this->params ) && ! empty( $this->params['data']['UptimeMonitor'] ) ) {

			if ( $this->UrMonitor->save( $this->params['data'] ) ) {
				$this->flash( 'notice', 'Monitor successfully added 2!' );
			}
		}
	}

	function monitor_form() {
		$this->ajax_check_permissions( 'monitor_form' );

		$this->load_model( 'UptimeMonitor' );
		$this->set( 'monitor_saved', false );
		if ( ! empty( $this->params ) && ! empty( $this->params['data']['UptimeMonitor'] ) ) {
			// creating string of  types to insert into monitor_types field
			$this->params['data']['UptimeMonitor']['user_id'] = get_current_user_id();

			// first of all, before validating other fields and storing, checking if uptimerobot api key exists
			$UR = new UptimeRobot( '' );
			$UR->set_format( 'json' );
			$UR->set_api_key( $this->params['data']['UptimeMonitor']['monitor_api_key'] );

			try {
				$result = $UR->get_monitors( array(), 0, 1 );
				// place this one first
				while ( strpos( $result, ',,' ) !== false ) {
					$result = str_replace( array( ',,' ), ',', $result ); // fix json
				}
				$result = str_replace( ',]', ']', $result ); // fix json
				$result = str_replace( '[,', '[', $result ); // fix json

				$result = json_decode( $result );
			} catch (Exception $ex) {
				$this->flash( 'error', $ex->getMessage() );
			}

			if ( $result && ($result->stat == 'ok' || $result->id == '212') ) {  // if key exists, but connected account probably has no monitors(error 212)
				if ( ! isset( $this->params['data']['UptimeMonitor']['monitor_id'] ) ) {
					if ( $this->UptimeMonitor->save( $this->params['data'] ) ) {
						$this->flash( 'notice', 'Monitor successfully added!' );
						$this->set( 'monitor_saved', true );
						$this->set( 'monitor_id', $this->UptimeMonitor->insert_id );
					} else {
						$this->flash( 'error', $this->UptimeMonitor->validation_error_html );
					}
				} else {
					if ( $this->UptimeMonitor->update( $this->params['data']['UptimeMonitor']['monitor_id'], $this->params['data']['UptimeMonitor'], array( 'UptimeMonitor.user_id' => get_current_user_id() ) ) ) {
						$this->flash( 'notice', 'Monitor successfully modified!' );
						$this->set( 'monitor_saved', true );
						$this->set( 'monitor_id', $this->UptimeMonitor->insert_id );
					} else {
						$this->flash( 'error', $this->UptimeMonitor->validation_error_html );
					}
				}

				if ( ! $this->UptimeMonitor->validation_error_html ) {
					if ( $result->id != '212' ) {  // there are monitors to import?
						$this->load_model( 'UptimeUrl' );
						$this->params['data']['UptimeUrl']['monitor_id'] = isset( $this->params['data']['UptimeMonitor']['monitor_id'] ) ? $this->params['data']['UptimeMonitor']['monitor_id'] : $this->UptimeMonitor->insert_id;
						foreach ( $result->monitors->monitor as $monitor ) {
							$this->params['data']['UptimeUrl']['url_uptime_monitor_id'] = $monitor->id;
							$this->params['data']['UptimeUrl']['user_id'] = get_current_user_id();
							$this->params['data']['UptimeUrl']['url_api_key'] = '';
							$this->params['data']['UptimeUrl']['url_friendly_name'] = $monitor->friendlyname;
							$this->params['data']['UptimeUrl']['url_address'] = $monitor->url;
							$this->params['data']['UptimeUrl']['url_monitor_type'] = $monitor->type;
							$this->params['data']['UptimeUrl']['url_monitor_subtype'] = $monitor->subtype;
							$this->params['data']['UptimeUrl']['url_monitor_keywordtype'] = $monitor->keywordtype;
							$this->params['data']['UptimeUrl']['url_monitor_keywordvalue'] = $monitor->keywordvalue;
							// $this->params['data']['UptimeUrl']['url_not_email'] = $monitor-> alertcontact;
							$alert_contacts = array();
							for ( $i = 0; $i < count( $monitor->alertcontact ); $i++ ) {
								$alert_contacts[ $i ] = $monitor->alertcontact[ $i ]->id;
							}
							$this->params['data']['UptimeUrl']['url_not_email'] = $alert_contacts;
							$this->UptimeUrl->save( $this->params['data'] );
						}
					}
				}
			} else {
				$this->flash( 'error', $result->message );
			}
		}
		$this->model = $this->UptimeMonitor;
		/*
          $monitors = $this->UptimeMonitor->find(array('selects' => array('monitor_id', 'monitor_name', 'monitor_type', 'monitor_api_key', 'monitor_not_email', 'create')));
          $this->set('monitors', $monitors);
          $this->params['id'] = '-1';
          $this->verify_id_param();
         */
		$this->render_view( 'admin/monitor_form', array( 'layout' => 'ajax_popup' ) );
	}

	function delete_monitor() {
		$this->ajax_check_permissions( 'delete_monitor' );

		$this->load_model( 'UptimeMonitor' );
		$this->UptimeMonitor->delete( $this->params['monitor_id'] );
		die( 'success' );
	}

	function update_monitor() {
		$this->load_model( 'UptimeMonitor' );
		$this->params['data']['UptimeMonitor'] = $this->UptimeMonitor->find_by_id( $this->params['monitor_id'] );
		$this->render_view( 'admin/monitor_form', array( 'layout' => 'ajax_popup' ) );
	}

	function url_form() {
		$this->ajax_check_permissions( 'url_form' );

		$this->load_model( 'UptimeUrl' );
		$this->set( 'url_saved', false );
		$this->set( 'title', $this->params['title'] );
		if ( ! empty( $this->params ) && ! empty( $this->params['data']['UptimeUrl'] ) ) {
			// to fix bug display friend name after create new monitor
			if ( ! isset( $this->params['checkbox_show_select'] ) ) {
				$this->params['data']['UptimeUrl']['url_friendly_name'] = $this->params['url_friendly_name_textbox'];
			}
			$monitor = $this->UptimeMonitor->find_one( array(
				'selects'    => array(
					'monitor_id',
					'monitor_name',
					'monitor_type',
					'monitor_api_key',
					'monitor_not_email',
				),
				'conditions' => array(
					'user_id' => get_current_user_id(),
					//'monitor_id' => $this->params['data']['UptimeUrl']['monitor_id']
				),
			) );

			$aum_api_key = "";
			if ($monitor)
				$aum_api_key = $monitor->monitor_api_key;
			else {
				$aum_api_key = Advanced_Uptime_Monitor_Extension::get_install()->get_option('api_key');
			}

			$url_saved = false;

			if (!empty($aum_api_key)) {
				$this->params['data']['UptimeUrl']['user_id'] = get_current_user_id();
				if ( ! isset( $this->params['data']['UptimeUrl']['url_id'] ) ) {
					$this->params['data']['UptimeUrl']['user_id'] = get_current_user_id();
					$url_saved                                    = $this->UptimeUrl->save( $this->params['data'] );
				} else {
					$url_bak   = $this->UptimeUrl->find_by_id( $this->params['data']['UptimeUrl']['url_id'] );
					$url_saved = $this->UptimeUrl->update( $this->params['data']['UptimeUrl']['url_id'], $this->params['data']['UptimeUrl'], array( 'UptimeUrl.user_id' => get_current_user_id() ) );
				}
			}

			if ( $url_saved ) {
				// creating string of  types to insert into monitor_types field
				$this->load_model( 'UptimeMonitor' );
				$UR = new UptimeRobot( '' );
				$UR->set_format( 'json' );
				$UR->set_api_key( $aum_api_key );

				//error_log("===============" . print_r($this->params, true));
				// $UR->set_api_key(!empty($this->params['data']['UptimeUrl']['url_api_key'])?$this->params['data']['UptimeUrl']['url_api_key']:$monitor->monitor_api_key);

				try {
					$params = array(
						'name' => $this->params['data']['UptimeUrl']['url_friendly_name'],
						'uri' => $this->params['data']['UptimeUrl']['url_address'],
						'type' => $this->params['data']['UptimeUrl']['url_monitor_type'],
					);
					if ( ! empty( $this->params['monitor_contacts_notification'] ) ) {
						$params['monitorAlertContacts'] = $this->params['monitor_contacts_notification'];
					}
					if ( '2' == $params['type'] && isset( $this->params['data']['UptimeUrl']['url_monitor_keywordtype'] ) ) {
						$params['keyword_type'] = $this->params['data']['UptimeUrl']['url_monitor_keywordtype'];
						$params['keyword_value'] = $this->params['data']['UptimeUrl']['url_monitor_keywordvalue'];
					}

					if ( '4' == $params['type'] && isset( $this->params['data']['UptimeUrl']['url_monitor_subtype'] ) ) {
						$params['subtype'] = $this->params['data']['UptimeUrl']['url_monitor_subtype'];
						$params['port'] = $this->monitor_ports[ $this->params['data']['UptimeUrl']['url_monitor_subtype'] ];
					}
					// to fix bug
					try {
						if ( ! isset( $this->params['data']['UptimeUrl']['url_id'] ) ) {
							$result = $UR->new_monitor( $params );
						} else {
							$url = $this->UptimeUrl->find_by_id( $this->params['data']['UptimeUrl']['url_id'] );
							$result = $UR->edit_monitor( $url->url_uptime_monitor_id, $params );
						}
						$result = json_decode( $result );
					} catch (Exception $ex) {
						throw $ex;
					}
					if ( $result->stat == 'ok' ) {
						if ( ! isset( $this->params['data']['UptimeUrl']['url_id'] ) ) {
							$this->flash( 'notice', 'URL successfully added!' );
							update_option( 'mainwp_aum_requires_reload_monitors', 'yes' );
						} else {
							$this->flash( 'notice', 'URL successfully modified!' );
						}
						$uptime_monitor_id = $result->monitor->id;
						$this->params['data']['UptimeUrl']['url_id'] = isset( $this->params['data']['UptimeUrl']['url_id'] ) ? $this->params['data']['UptimeUrl']['url_id'] : $this->UptimeUrl->insert_id;
						$this->params['data']['UptimeUrl']['url_uptime_monitor_id'] = $uptime_monitor_id;
						$this->params['data']['UptimeUrl']['monitor_id'] = $monitor->monitor_id;
						$this->UptimeUrl->save( $this->params['data'] );

						$this->set( 'url_saved', true );
						$this->set( 'url_id', $this->UptimeUrl->insert_id );
					} else {
						if ( $result ) {
							$this->flash( 'error', $result->message );
						} else {
							$this->flash( 'error', 'Uptime Robot error.' );
						}
						if ( ! isset( $this->params['data']['UptimeUrl']['url_id'] ) ) {
							$this->UptimeUrl->delete( $this->UptimeUrl->insert_id );
						} else {
							$url_bak = (array) $url_bak;
							foreach ( $url_bak as $field => $value ) {
								if ( strpos( $field, 'url_' ) === false ) {
									unset( $url_bak[ $field ] );
								}
							}
							$res = $this->UptimeUrl->save( array( 'UptimeUrl' => $url_bak ) );
						}
					}
				} catch (Exception $ex) {
					switch ( $ex->getCode() ) {
						case 1:
							echo esc_html( $ex->getMessage() );
							break;
						case 2:
							$this->flash( 'error', 'You should specify API key' );
							break;
						case 3:
							$this->flash( 'error', 'Error' );
							break;
						default:
							echo esc_html( $ex->getCode() . ': ' . $ex->getMessage() );
					}
				}
			} else {
				$this->flash( 'error', $this->UptimeUrl->validation_error_html );
			}
		}

		$this->model = $this->UptimeUrl;
		$this->render_view( 'admin/url_form', array( 'layout' => 'ajax_popup' ) );
	}

	function delete_url() {
		global $wpdb;
		$this->load_model( 'UptimeUrl' );
		$monitor_api_key = $wpdb->get_var('SELECT m.monitor_api_key FROM ' . $wpdb->prefix . 'aum_monitors m
															INNER JOIN ' . $wpdb->prefix . 'aum_urls u ON u.monitor_id=m.monitor_id
															WHERE u.url_id="' . $this->params['url_id'] . '"');
		$url = $this->UptimeUrl->find_by_id( $this->params['url_id'] );

		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( ! empty( $url->url_api_key ) ? $url->url_api_key : $monitor_api_key );
		$response = $UR->delete_monitor( $url->url_uptime_monitor_id );
		$response = json_decode( $response );

		$this->UptimeUrl->delete( $this->params['url_id'] );
		if ( $response && $response->stat == 'ok' ) {
			die( 'success' );
		} else {
			die( 'success' );
		}
	}

	function display_dashboard() {

		global $wpdb;
		$this->ajax_check_permissions( 'display_dashboard' );

		$result = $wpdb->update(
			$wpdb->prefix . 'aum_urls', array( 'dashboard' => $this->params['dashboard'] ), array( 'url_id' => $this->params['url_id'] ), array( '%d' ), array( '%s' )
		);

		if ( $result > 0 ) {
			die( 'success' );
		} else {
			die( 'success' );
		}
	}

	public function get_urls() {
		$this->load_model( 'UptimeUrl' );
		$this->load_model( 'UptimeMonitor' );
		$this->load_model( 'UptimeStats' );
		$monitor = $this->UptimeMonitor->find_one( array( 'conditions' => array( 'monitor_id' => $this->params['monitor_id'] ) ) );
		$stats = array();

		$urls = $this->UptimeUrl->find( array( 'conditions' => array( 'monitor_id' => $this->params['monitor_id'] ) ) );
		// statistics
		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $monitor->monitor_api_key );
		$result = $UR->get_monitors( array(), 1, 0, 1 );
		// place this one first
		while ( strpos( $result, ',,' ) !== false ) {
			$result = str_replace( array( ',,' ), ',', $result ); // fix json
		}

		$result = str_replace( ',]', ']', $result ); // fix json
		$result = str_replace( '[,', '[', $result ); // fix json
		$result = json_decode( $result );
		if ( $this->params['what'] == 'meta_box' ) {
			$this->diagram_width = 210;
		}
		$points = array();
		$time_limit = 60 * 60 * 24 * 1;
		$uptime_ratio_arr = array();
		foreach ( $result->monitors->monitor as $monitor ) {
			$uptime_ratio_arr[ $monitor->id ] = $monitor->alltimeuptimeratio;
			// searching for the last event before happend befor last 24 hours;
			$stats[ $monitor->id ][0] = new StdClass();
			$period_precedent_date = 0;

			if ( is_array( $monitor->log ) ) {
				foreach ( $monitor->log as $index => $log ) {
					$datetime = new DateTime( $log->datetime );
					$datetime = $datetime->format( 'Y-m-d H:i:s' );
					if ( strtotime( $datetime ) >= time() - $time_limit ) {
						continue;
					}
					if ( strtotime( $datetime ) > $period_precedent_date ) {
						$stats[ $monitor->id ][0]->datetime = $datetime;
						$stats[ $monitor->id ][0]->type = $log->type;
						$stats[ $monitor->id ][0]->point_pos = 0;
						$period_precedent_date = strtotime( $datetime );
					}
				}
			}

			if ( ! $stats[ $monitor->id ][0]->datetime ) {
				$stats[ $monitor->id ][0]->datetime = date( 'Y-m-d H:i:s', time() - $time_limit - 1 );
				$stats[ $monitor->id ][0]->type = null;
				$stats[ $monitor->id ][0]->point_pos = 0;
			}

			// getting last event recorded to database
			$last_existing_event = $this->UptimeStats->get_last_event( array( 'monitor_id' => $monitor->id ) );
			// echo "Monitor '".$monitor->friendlyname."'<br/>";
			if ( is_array( $monitor->log ) ) {
				foreach ( $monitor->log as $index => $log ) {
					$datetime = new DateTime( $log->datetime );
					$datetime = $datetime->format( 'Y-m-d H:i:s' );

					// storing event to database;
					if ( is_null( $last_existing_event ) || strtotime( $last_existing_event->event_datetime_gmt ) < strtotime( $datetime ) ) {
						$data['UptimeStats'] = array(
							'monitor_id' => $monitor->id,
							'type' => $log->type,
							'event_datetime_gmt' => $datetime,
						);
						$this->UptimeStats->create( $data );
					}

					// event happened earlier than 24 hours ago?
					if ( strtotime( $datetime ) < time() - $time_limit ) {
						continue;
					}
				}
			}
			// echo "<br/>";
		}
		// var_dump($stats);exit;
		// getting stats from db, for simplified sorting
		$stats_from_db = $this->UptimeStats->get_events( 'event_datetime_gmt>="' . date( 'Y-m-d H:i:s', time() - $time_limit ) . '" AND monitor_type="-1"' );
		foreach ( $stats_from_db as $index => $event ) {
			$seconds_in_one_px = $time_limit / $this->diagram_width;
			$stats[ $event->monitor_id ][ $index + 1 ]->datetime = $event->event_datetime_gmt;
			$stats[ $event->monitor_id ][ $index + 1 ]->type = $event->type;
			$stats[ $event->monitor_id ][ $index + 1 ]->point_pos = (int) (($time_limit - (time() - strtotime( $event->event_datetime_gmt ))) / $seconds_in_one_px);
			$stats[ $event->monitor_id ][ $index + 1 ]->test = ($time_limit - (time() - strtotime( $event->event_datetime_gmt ))) / $seconds_in_one_px;
		}
		// statistics end
		// checking if urls monitors are on or off & adding uptime ratio to each url
		foreach ( $urls as $index => $url ) {
			// getting current monitor state: on or off
			$last_url_event = $this->UptimeStats->get_last_event( array( 'monitor_id' => $url->url_uptime_monitor_id ) );
			if ( $last_url_event->monitor_type != -1 ) { // if last event is monitor state event, not url status
				$urls[ $index ]->monitor_type = $last_url_event->monitor_type;
			}
			if ( ! isset( $urls[ $index ]->monitor_type ) ) {
				$urls[ $index ]->monitor_type = 1; // not checked yet
			}          // getting uptime ratio for displaying
			if ( isset( $uptime_ratio_arr[ $url->url_uptime_monitor_id ] ) ) {
				$urls[ $index ]->uptime_ratio = $uptime_ratio_arr[ $url->url_uptime_monitor_id ];
			}
		}
		$this->set( 'urls', $urls );
		$this->set( 'stats', $stats );
		$this->set( 'diagram_width', $this->diagram_width );
		$this->render_view( 'admin/get_urls', array( 'layout' => 'ajax_clean' ) );
	}

	function add_url() {
		$this->load_model( 'UptimeUrl' );

		if ( $this->UptimeUrl->save( $this->params ) ) {
			$this->flash( 'notice', 'URL successfully added!' );
			update_option( 'mainwp_aum_requires_reload_monitors', 'yes' );
		} else {
			$this->flash( 'error', $this->UptimeMonitor->validation_error_html );
		}

		$urls = $this->UptimeUrl->find( array( 'conditions' => array( 'monitor_id' => $this->params['monitor_id'] ) ) );
		$this->set( 'urls', $urls );
		$this->render_view( 'admin/get_urls', array( 'layout' => 'ajax_clean' ) );
	}

	function get_alert_contact_url( $api_key, $monitor_url_id ) {

		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $api_key );
		$monitors = array( (string) $monitor_url_id );
		try {
			$result = $UR->get_monitors( $monitors, 1, 1 );
			// place this one first
			while ( strpos( $result, ',,' ) !== false ) {
				$result = str_replace( array( ',,' ), ',', $result ); // fix json
			}
			$result = str_replace( ',]', ']', $result ); // fix json
			$result = str_replace( '[,', '[', $result ); // fix json

			$result = json_decode( $result );
			if ( $result->stat == 'fail' ) {
				// die("fail");
				return array();
			}
		} catch (Exception $ex) {

			$this->flash( 'error', $ex->getMessage() );
		}
		$list_contact_url = array();

		$number_contacts = count( $result->monitors->monitor[0]->alertcontact );
		if ( is_array( $result->monitors->monitor ) && count( $result->monitors->monitor ) > 0 ) {
			for ( $i = 0; $i < $number_contacts; $i++ ) {
				$list_contact_url[ $i ] = $result->monitors->monitor[0]->alertcontact[ $i ]->id;
			}
		}
		return $list_contact_url;
	}

	function update_url() {
		$this->ajax_check_permissions( 'update_url' );

		$this->set( 'title', $this->params['title'] );
		$this->load_model( 'UptimeUrl' );
		$this->load_model( 'UptimeMonitor' );
		$this->model = $this->UptimeUrl;
		$this->params['data']['UptimeUrl'] = $this->UptimeUrl->find_by_id( $this->params['url_id'] );
		$monitor = $this->UptimeMonitor->find_by_id( $this->params['data']['UptimeUrl']->monitor_id );
		$this->set( 'monitor', $monitor );
		$list_contact_url = $this->get_alert_contact_url( $monitor->monitor_api_key, $this->params['data']['UptimeUrl']->url_uptime_monitor_id );
		$this->params['data']['UptimeUrl']->url_not_email = $list_contact_url;
		$this->render_view( 'admin/url_form', array( 'layout' => 'ajax_popup' ) );
	}

	function url_start() {
		$this->ajax_check_permissions( 'url_sp' );

		$this->load_model( 'UptimeUrl' );
		$this->load_model( 'UptimeMonitor' );
		$this->load_model( 'UptimeStats' );

		$url = $this->UptimeUrl->find_by_id( $this->params['url_id'] );
		$monitor = $this->UptimeMonitor->find_one(array(
			'conditions' => array(
				'user_id' => get_current_user_id(),
				'monitor_id' => $url->monitor_id,
			),
		));
		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $monitor->monitor_api_key );
		$params = array(
			'status' => 1,
		);
		$result = $UR->edit_monitor( $url->url_uptime_monitor_id, $params );
		$result = json_decode( $result );
		if ( $result->stat == 'ok' ) {
			$data['UptimeStats'] = array( 'monitor_id' => $url->url_uptime_monitor_id, 'monitor_type' => '1', 'event_datetime_gmt' => date( 'Y-m-d H:i:s' ) );
			if ( $this->UptimeStats->save( $data ) ) {
				die( 'success' );
			} else {
				die( 'db error' );
			}
		} else {
			die( esc_html( $result->message ) );
		}
	}

	function url_pause() {
		$this->ajax_check_permissions( 'url_sp' );

		$this->load_model( 'UptimeUrl' );
		$this->load_model( 'UptimeMonitor' );
		$this->load_model( 'UptimeStats' );

		$url = $this->UptimeUrl->find_by_id( $this->params['url_id'] );
		$monitor = $this->UptimeMonitor->find_one(array(
			'conditions' => array(
				'user_id' => get_current_user_id(),
				'monitor_id' => $url->monitor_id,
			),
		));
		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$UR->set_api_key( $monitor->monitor_api_key );
		$params = array(
			'status' => 0,
		);
		$result = $UR->edit_monitor( $url->url_uptime_monitor_id, $params );
		$result = json_decode( $result );
		if ( $result->stat == 'ok' ) {
			$data['UptimeStats'] = array( 'monitor_id' => $url->url_uptime_monitor_id, 'monitor_type' => '0', 'event_datetime_gmt' => date( 'Y-m-d H:i:s' ) );
			if ( $this->UptimeStats->save( $data ) ) {
				die( 'success' );
			} else {
				die( 'db error' );
			}
		} else {
			die( esc_html( $result->message ) );
		}
	}

	function statistics_table() {
		$this->ajax_check_permissions( 'statistics_table' );

		$this->set( 'title', $this->params['title'] );
		$this->load_model( 'UptimeUrl' );
		$this->load_model( 'UptimeStats' );
		$url = $this->UptimeUrl->find_by_id( $this->params['url_id'], array( 'conditions' => array( 'user_id' => get_current_user_id() ) ) );
		if ( ! $url ) {
			$this->flash( 'error', 'URL not found!' );
			$this->render_view( 'admin/error', array( 'layout' => 'ajax_popup' ) );
			exit;
		}
		$this->load_model( 'UptimeUrl' );
		$stats_contditions = array( 'monitor_id' => $url->url_uptime_monitor_id );
		$stats = $this->UptimeStats->find(array(
			'conditions' => $stats_contditions,
			'order' => 'event_datetime_gmt DESC',
			'page' => isset( $this->params['stats_page'] ) && (int) $this->params['stats_page'] > 0 ? $this->params['stats_page'] : 1,
			'per_page' => 10,
		));

		$this->set( 'stats_cnt', $this->UptimeStats->count( array( 'conditions' => $stats_contditions ) ) );
		$this->set( 'stats_page', isset( $this->params['stats_page'] ) ? $this->params['stats_page'] : 1 );
		$this->set( 'url', $url );
		$this->set( 'monitor_types', $this->monitor_types );
		$this->set( 'monitor_statuses', $this->monitor_statuses );
		$this->set( 'stats', $stats );
		$this->render_view( 'admin/statistics_table', array( 'layout' => 'ajax_popup' ) );
	}

	private function check_unavailable_url_monitors() {
		$this->load_model( 'UptimeMonitor' );
		$this->load_model( 'UptimeUrl' );
		$this->load_model( 'UptimeStats' );
		if ( ! (get_current_user_id()) ) {
			return;
		}

		$monitors = $this->UptimeMonitor->find( array( 'conditions' => array( 'user_id' => get_current_user_id() ) ) );

		$UR = new UptimeRobot( '' );
		$UR->set_format( 'json' );
		$urm_url_monitors_ids = array();
		$valid = false;
		foreach ( $monitors as $monitor ) {
			$UR->set_api_key( $monitor->monitor_api_key );
			$results = $UR->get_all_monitors();
			// error_log(print_r($results, true));
			if ( ! $valid ) {
				if ( is_array( $results ) && count( $results ) > 0 ) {
					$result = current( $results ); // check first one only
					// $result = json_decode($result);
					if ( $result->stat == 'fail' ) {
						$this->flash( 'notice', $result->message );
						break;
					} else {
						$valid = true;
					}
				}
			}
			if ( is_array( $results ) && count( $results ) > 0 ) {
				foreach ( $results as $result ) {
					if ( $result->stat == 'ok' ) {
						foreach ( $result->monitors->monitor as $url_monitor ) {
							$urm_url_monitors_ids[] = $url_monitor->id;
						}
					}
				}
			}
		}
		if ( ! $valid ) {
			$this->flash( 'error', __( 'Unable to load Uptime monitor data', 'advanced-uptime-monitor-extension' ) );
			return;
		}

		$urls = $this->UptimeUrl->find( array( 'conditions' => array( 'user_id' => get_current_user_id() ) ) );

		$unavailable_urls = array();
		foreach ( $urls as $url ) {
			if ( ! in_array( $url->url_uptime_monitor_id, $urm_url_monitors_ids ) ) {
				$unavailable_urls[] = $url->url_friendly_name;
				$this->UptimeUrl->delete( $url->url_id );
			}
		}

		if ( ! empty( $unavailable_urls ) ) {
			$this->flash( 'notice', 'Following unavailable urls removed from DB:<br/>' . implode( ',<br/>', $unavailable_urls ) );
		}
	}

	function get_ur_gmt_offset_time( $api_key, $ur = null ) {

		$url = 'http://api.uptimerobot.com/getMonitors?apiKey=' . $api_key . '&format=json&showTimezone=1';
		$file_contents = Advanced_Uptime_Monitor_Extension::get_install()->get_file_content_url( $url );
		$results = substr( $file_contents, strlen( 'jsonUptimeRobotApi()' ) - 1, strlen( $file_contents ) - strlen( 'jsonUptimeRobotApi()' ) );
		$content = json_decode( $results );
		if ( $content->stat = 'ok' ) {
			return $this->get_gmt_offset_time( $content->timezone );
		} else {
			return false;
		}
	}

	function get_gmt_offset_time( $time_zone ) {
		if ( '+720' == $time_zone ) {
			return array( 'offset_time' => 12, 'text' => 'Eniwetok, Kwajalein' );
		}
		if ( '-660' == $time_zone ) {
			return array( 'offset_time' => -11, 'text' => 'Midway Island, Samoa' );
		}
		if ( '-600' == $time_zone ) {
			return array( 'offset_time' => -10, 'text' => 'Hawaii' );
		}
		if ( '-480' == $time_zone ) {
			return array( 'offset_time' => -8, 'text' => 'Alaska' );
		}
		if ( '-420' == $time_zone ) {
			return array( 'offset_time' => -7, 'text' => 'Pacific Time (US &amp; Canada)' );
		}
		if ( '-360' == $time_zone ) {
			return array( 'offset_time' => -6, 'text' => 'Mountain Time (US &amp; Canada) or Central Time (US &amp; Canada), Mexico City' );
		}
		if ( '-300' == $time_zone ) {
			return array( 'offset_time' => -5, 'text' => 'Eastern Time (US &amp; Canada), Bogota, Lima' );
		}
		if ( '-240' == $time_zone ) {
			return array( 'offset_time' => -4, 'text' => 'Atlantic Time (Canada), La Paz' );
		}
		if ( '-150' == $time_zone ) {
			return array( 'offset_time' => -2.5, 'text' => 'Newfoundland' );
		}
		if ( '-180' == $time_zone ) {
			return array( 'offset_time' => -3, 'text' => 'Brazil, Buenos Aires, Georgetown' );
		}
		if ( '-120' == $time_zone ) {
			return array( 'offset_time' => -2, 'text' => 'Mid-Atlantic' );
		}
		if ( '-60' == $time_zone ) {
			return array( 'offset_time' => -1, 'text' => 'Azores, Cape Verde Islands' );
		}
		if ( '+60' == $time_zone ) {
			return array( 'offset_time' => 1, 'text' => 'Western Europe Time, London, Lisbon, Casablanca' );
		}
		if ( '+120' == $time_zone ) {
			return array( 'offset_time' => 2, 'text' => 'Brussels, Copenhagen, Madrid, Paris' );
		}
		if ( '+180' == $time_zone ) {
			return array( 'offset_time' => 3, 'text' => 'Istanbul, Kaliningrad, Athens ,Baghdad, Riyadh' );
		}
		if ( '+270' == $time_zone ) {
			return array( 'offset_time' => 4.5, 'text' => 'Tehran , Kabul' );
		}
		if ( '+300' == $time_zone ) {
			return array( 'offset_time' => 5, 'text' => 'Abu Dhabi, Muscat, Baku, Tbilisi, Ekaterinburg, Islamabad, Karachi, Tashkent' );
		}
		if ( '+330' == $time_zone ) {
			return array( 'offset_time' => 5.5, 'text' => 'Bombay, Calcutta, Madras, New Delhi' );
		}
		if ( '+345' == $time_zone ) {
			return array( 'offset_time' => 5.75, 'text' => 'Kathmandu' );
		}
		if ( '+360' == $time_zone ) {
			return array( 'offset_time' => 6, 'text' => 'Almaty, Dhaka, Colombo' );
		}
		if ( '+420' == $time_zone ) {
			return array( 'offset_time' => 7, 'text' => 'Bangkok, Hanoi, Jakarta' );
		}
		if ( '+480' == $time_zone ) {
			return array( 'offset_time' => 8, 'text' => 'Beijing, Perth, Singapore, Hong Kong' );
		}
		if ( '+540' == $time_zone ) {
			return array( 'offset_time' => 9, 'text' => 'Tokyo, Seoul, Osaka, Sapporo, Yakutsk' );
		}
		if ( '+630' == $time_zone ) {
			return array( 'offset_time' => 10.5, 'text' => 'Adelaide, Darwin' );
		}
		if ( '+600' == $time_zone ) {
			return array( 'offset_time' => 10, 'text' => 'Eastern Australia, Guam, Vladivostok' );
		}
		if ( '+720' == $time_zone ) {
			return array( 'offset_time' => 12, 'text' => 'Magadan, Solomon Islands, New Caledonia' );
		}
		if ( '+780' == $time_zone ) {
			return array( 'offset_time' => 13, 'text' => 'Auckland, Wellington, Fiji, Kamchatka ,New Zealand Daylight Time, Tonga' );
		}
		if ( '+825' == $time_zone ) {
			return array( 'offset_time' => 13.75, 'text' => 'Chatham Islands' );
		}
		if ( '-270' == $time_zone ) {
			return array( 'offset_time' => -4.5, 'text' => 'Caracas-Venezuela' );
		}
		if ( '+120' == $time_zone ) {
			return array( 'offset_time' => 2, 'text' => 'South African Standard Time' );
		}
		if ( '+240' == $time_zone ) {
			return array( 'offset_time' => 4, 'text' => 'Moscow, St. Petersburg' );
		}
		if ( '+0' == $time_zone ) {
			return array( 'offset_time' => 0, 'text' => 'GMT' );
		}
		if ( '+660' == $time_zone ) {
			return array( 'offset_time' => 11, 'text' => 'Sydney, Melbourne' );
		}
	}

	protected function ajax_check_permissions( $action, $json = false ) {
		if ( has_filter( 'mainwp_currentusercan' ) ) {
			if ( ! mainwp_current_user_can( 'extension', 'advanced-uptime-monitor-extension' ) ) {
				$output = mainwp_do_not_have_permissions( 'Advanced Uptime Monitor Extension ' . $action, ! $json );
				if ( $json ) {
					echo json_encode( array( 'error' => $output ) );
				}
				die();
			}
		} else {
			if ( ! current_user_can( 'manage_options' ) ) {
				$output = mainwp_do_not_have_permissions( 'Advanced Uptime Monitor Extension ' . $action, ! $json );
				if ( $json ) {
					echo json_encode( array( 'error' => $output ) );
				}
				die();
			}
		}

		if ( ! isset( $_REQUEST['wp_nonce'] ) || ! wp_verify_nonce( $_REQUEST['wp_nonce'], AdminUptimeMonitorsController::$nonce_token . $action ) ) {
			echo $json ? json_encode( array( 'error' => 'Error: Wrong or expired request' ) ) : 'Error: Wrong or expired request';
			die();
		}
	}
}
