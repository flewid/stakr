<?php

class MainWP_Sucuri {

	public static $instance = null;
	public static $nonce_token = 'mainwp-sucuri-extension-';

	static function get_instance() {
		if ( null == MainWP_Sucuri::$instance ) {
			MainWP_Sucuri::$instance = new MainWP_Sucuri(); }
		return MainWP_Sucuri::$instance;
	}

	public function __construct() {

	}

	public function init() {
		add_action( 'wp_ajax_mainwp_sucuri_security_scan', array( $this, 'ajax_sucuri_scan' ) );
		add_action( 'wp_ajax_mainwp_sucuri_delete_report', array( $this, 'delete_report' ) );
		add_action( 'wp_ajax_mainwp_sucuri_show_report', array( $this, 'show_report' ) );
		add_action( 'wp_ajax_mainwp_sucuri_change_remind', array( $this, 'ajax_change_remind' ) );
		add_action( 'wp_ajax_mainwp_sucuri_sslverify_certificate', array( $this, 'ajax_save_ssl_verify' ) );
		add_action( 'mainwp_sucuri_extension_cronsecurityscan_notification', array( 'MainWP_Sucuri', 'cronsecurityscan_notification' ) );
		add_filter( 'mainwp_sucuri_scan_data', array( $this, 'sucuri_scan_data' ) );

		add_action( 'mainwp-sucuriscan-sites', 'MainWP_Sucuri::render' );

		$useWPCron = (get_option( 'mainwp_wp_cron' ) === false) || (get_option( 'mainwp_wp_cron' ) == 1);
		if ( ($sched = wp_next_scheduled( 'mainwp_sucuri_extension_cronsecurityscan_notification' )) == false ) {
			if ( $useWPCron ) {
				wp_schedule_event( time(), 'daily', 'mainwp_sucuri_extension_cronsecurityscan_notification' ); }
		} else {
			if ( ! $useWPCron ) {
				wp_unschedule_event( $sched, 'mainwp_sucuri_extension_cronsecurityscan_notification' ); }
		}
	}

	static function cronsecurityscan_notification() {
		global $mainWPSucuriExtensionActivator;
		$websites = apply_filters( 'mainwp-getsites', $mainWPSucuriExtensionActivator->get_child_file(), $mainWPSucuriExtensionActivator->get_child_key(), null );
		if ( is_array( $websites ) && count( $websites ) > 0 ) {
			foreach ( $websites as $site ) {
				if ( $sucuri = MainWP_Sucuri_DB::get_instance()->get_sucuri_by( 'site_url', $site['url'] ) ) {
					if ( self::check_remind( $sucuri ) ) {
						self::send_remind_email( $site, $sucuri ); }
				} else {
					$sucuri = array( 'site_url' => $site['url'], 'remind' => 'never' );
					MainWP_Sucuri_DB::get_instance()->update_sucuri( $sucuri );
				}
			}
		}
	}

	static function check_remind( $sucuri ) {
		$lasttime = $sucuri->lastscan;
		$remind = $sucuri->remind;
		$last_remind = $sucuri->lastremind;
		$send_email = false;
		switch ( $remind ) {
			case 'never':
				return false;
				break;
			case 'day':
				if ( time() > $lasttime + 24 * 3600 ) {
					if ( 0 == $last_remind || time() > $last_remind + 24 * 3600 ) {
						return true; }
				}
				break;
			case 'week':
				if ( time() > $lasttime + 7 * 24 * 3600 ) {
					if ( 0 == $last_remind || time() > $last_remind + 7 * 24 * 3600 ) {
						return true; }
				}
				break;
			case 'month':
				if ( time() > strtotime( '+1 month', $lasttime ) ) {
					if ( 0 == $last_remind || time() > strtotime( '+1 month', $last_remind ) ) {
						return true; }
				}
				break;
		}
		return false;
	}

	static function send_remind_email( $site, $sucuri ) {
		global $mainWPSucuriExtensionActivator;
		$lastscan = $sucuri->lastscan;
		$remind = $sucuri->remind;
		$email = apply_filters( 'mainwp_getnotificationemail', $mainWPSucuriExtensionActivator->get_child_file(), $mainWPSucuriExtensionActivator->get_child_key() );
		if ( ! empty( $site ) && ! empty( $email ) ) {
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			$last_time = 'N/A';
			$day_number = 0;
			if ( ! empty( $lastscan ) ) {
				$last_time = date( $date_format, $lastscan ) . ' ' . date( $time_format, $lastscan );
				$day_number = ceil( (time() - $lastscan) / (24 * 60 * 60) );
				$day_number = $day_number . (($day_number > 1) ? ' days' : ' day');
			} else {
				$lastremind = $sucuri->lastremind;
				if ( $lastremind > 0 ) {
					$day_number = ceil( (time() - $lastremind) / (24 * 60 * 60) );
					$day_number = $day_number . (($day_number > 1) ? ' days' : ' day');
				}
			}

			$mail = '<p>MainWP Security Scan Notification</p>';
			$mail .= '<p>Your site: <a href="' . $site['url'] . '">' . $site['url'] . '</a> has not been Scanned over ' . (0 !== $day_number ? $day_number : '1 ' . $remind) . '</p>';
			$mail .= '<p>Last time of Scan: ' . $last_time . '</p>';
			$mail .= '<p>Please perform a security scan from your MainWP Dashboard.</p>';
			if ( wp_mail( $email, 'MainWP - Security Scan Notification', $mail, array( 'From: "' . get_option( 'admin_email' ) . '" <' . get_option( 'admin_email' ) . '>', 'content-type: text/html' ) ) ) {
				$sucuri = array( 'id' => $sucuri->id, 'lastremind' => time() );
				MainWP_Sucuri_DB::get_instance()->update_sucuri( $sucuri );
				return true;
			}
		}
		return false;
	}

	public static function render( $website = null ) {
		global $mainWPSucuriExtensionActivator;

		if ( ! $website ) {
			?>
			<div class="mainwp_info-box-red"><?php _e( 'Error: Site not found.', 'mainwp' ); ?></div>
			<?php
			return;
		}

		//echo '<h2>' . $website->name . ' ' . __('Security Scan Report', 'mainwp') . '</h2>';

		$sucuri = MainWP_Sucuri_DB::get_instance()->get_sucuri_by( 'site_url', $website->url );
		if ( is_object( $sucuri ) ) {
			$remind = $sucuri->remind;
			$sucuri_id = $sucuri->id;
		} else {
			$remind = 'never';
			$sucuri_id = 0;
		}
		?>       
		<input type="hidden" name="mainwp_sucuri_site_id" value="<?php echo $website->id; ?>"/>          
		<input type="hidden" name="mainwp_sucuri_id" value="<?php echo $sucuri_id; ?>"/> 
		<input type="hidden" name="mainwp_sucuri_scan_nonce" value="<?php echo wp_create_nonce( MainWP_Sucuri::$nonce_token . 'sucuri_scan' ); ?>"/>
		<input type="hidden" name="mainwp_sucuri_delete_report_nonce" value="<?php echo wp_create_nonce( MainWP_Sucuri::$nonce_token . 'delete_report' ); ?>"/>
		<input type="hidden" name="mainwp_sucuri_show_report_nonce" value="<?php echo wp_create_nonce( MainWP_Sucuri::$nonce_token . 'show_report' ); ?>"/>
		<input type="hidden" name="mainwp_sucuri_change_remind_nonce" value="<?php echo wp_create_nonce( MainWP_Sucuri::$nonce_token . 'change_remind' ); ?>"/>

		<?php //self::sucuri_qsg();  ?>
		<div class="mainwp_sucuri_report_content_box"> 
			<div class="scr-inside">
				<div class="mainwp_info-box-red"><?php _e( 'Note: The Notifications feature uses cron functions in order to work correctly. If you are experiencing issues having the feature trigger please review this <a href="http://docs.mainwp.com/backups-scheduled-events-occurring/" target="_blank">help document.</a>', 'mainwp' ); ?></div>
				<div class="scr-content">                    
					<p><?php _e( 'Remind me if i don\'t scan my child site for' ); ?> 
						<select name="mainwp_sucuri_remind_scan" id="mainwp_sucuri_remind_scan">
							<option value="never" <?php echo (empty( $remind ) || 'never' === $remind ) ? 'selected' : '' ?>><?php _e( 'Never' ) ?></option>
							<option value="day" <?php echo ('day' === $remind) ? 'selected' : '' ?>><?php _e( '1 Day' ) ?></option>
							<option value="week" <?php echo ('week' === $remind) ? 'selected' : '' ?>><?php _e( '1 Week' ) ?></option>
							<option value="month" <?php echo ('month' === $remind) ? 'selected' : '' ?>><?php _e( '1 Month' ) ?></option>
						</select> 
						<span id="mainwp_sucuri_remind_change_status"></span>
					</p> 
				</div>
				<hr />
				<div class="scr-content">  
					<p>                        
						<?php
						$apisslverify = get_option( 'mainwp_security_sslVerifyCertificate' );
						if ( defined( 'OPENSSL_VERSION_NUMBER' ) && (OPENSSL_VERSION_NUMBER <= 0x009080bf) && (false === $apisslverify) ) {
							$apisslverify = 0;
							MainWPUtility::update_option( 'mainwp_security_sslVerifyCertificate', $apisslverify );
						}
						$_selected_1 = ((false === $apisslverify) || (1 == $apisslverify)) ? 'selected' : '';
						$_selected_0 = empty( $_selected_1 ) ? 'selected' : '';
						?>
					<div class="mainwp_sucuri_logo"><a href="http://affl.sucuri.net/?affl=b5221d72b72a22a47202712d41a40fd9" target="_blank" title="Sucuri"><img src="<?php echo plugins_url( 'images/sucuri_logo.png', dirname( __FILE__ ) ); ?>"/></a></div>
					<label><?php _e( 'Verify certificate', 'mainwp-sucuri-extension' ); ?> <?php do_action( 'mainwp_renderToolTip', __( 'Verify the SSL certificate. This should be disabled if you are using out of date or self signed certificates..', 'mainwp-sucuri-extension' ) ); ?></label>                       
					<span>
						<select name="mainwp_security_sslVerifyCertificate" id="mainwp_sucuri_verify_certificate" style="width: 100px;">
							<option value="0" <?php echo $_selected_0; ?> ><?php _e( 'No', 'mainwp-sucuri-extension' ); ?></option>
							<option value="1" <?php echo $_selected_1; ?> ><?php _e( 'Yes', 'mainwp-sucuri-extension' ); ?></option>                                               
						</select>&nbsp;
						<span class="sucuri_sslverify_loading">                         
							<i class="fa fa-spinner fa-pulse" style="display: none;"></i><span class="status hidden"></span>
						</span>                         
					</span>
					<a href="#" id="mainwp-sucuri-run-scan" class="button-hero button mainwp-upgrade-button" title="<?php _e( 'Run Security Scan' ); ?>"><?php _e( 'Run Security Scan' ); ?></a>                        
					</p>
					<div class="clearfix"></div>                    
					<scan id="mwp_sucuri_scan_status"></scan>
				</div>
			</div>
		</div>       
		<?php
		$saved_reports = MainWP_Sucuri_DB::get_instance()->get_report_by( 'site_url', $website->url );
		?>
		<div class="mainwp_sucuri_report_content_box"> 
			<div class="handlediv"><br /></div>            
			<h3><?php _e( 'Saved Security Reports' ); ?></h3>
			<div class="scr-inside">     
				<div class="scr-content">
					<?php
					if ( is_array( $saved_reports ) && count( $saved_reports ) > 0 ) {
						$date_format = get_option( 'date_format' );
						$time_format = get_option( 'time_format' );
						foreach ( $saved_reports as $report ) {
							?>       
							<div class="scr-inside-box closed">
								<div class="mainwp-sucuri-saved-report-list-item"><?php echo date( $date_format, $report->timescan ) . ' ' . date( $time_format, $report->timescan ) . ' - ' . __( 'Security Report' ); ?>
									&nbsp;<span class="mainwp-sucuri-report-action-status"></span> 
									<div class="mainwp-sucuri-report-loading right"><img class="hidden" src="<?php echo plugins_url( 'images/loader.gif', dirname( __FILE__ ) ); ?>"/></div>
									<div class="scr-row-actions">                                     
										<a href="#" class="mainwp-sucuri-saved-report-show" report-id="<?php echo $report->id; ?>"><?php _e( 'Show' ); ?></a> | <span><a href="#" class="mainwp-sucuri-saved-report-delete" report-id="<?php echo $report->id; ?>"><?php _e( 'Delete' ); ?></a>
									</div>   
								</div>    
								<div class="scr-report-content"></div>                                
							</div>
							<?php
						}
					} else {
						_e( 'No saved Security Reports.' );
					}
					?>
				</div>
			</div>
		</div>    
		<div id="mainwp-sucuri-security-scan-result"></div>    
		<?php
	}

	public static function get_link( $str ) {
		$str = trim( $str );
		if ( preg_match( '/^https?\:\/\/.*$/i', $str ) ) {
			return '<a href="' . $str . '" target="_blank">' . $str . '</a>'; } else { 			return $str; }
	}

	function ajax_change_remind() {
		$this->ajax_check_permissions( 'change_remind' );

		$sucuri_id = intval( $_POST['sucuriId'] );
		$remind = $_POST['remind'];
		if ( empty( $sucuri_id ) ) {
			die( 'FAIL' ); }

		$sucuri = array(
		'id' => $sucuri_id,
			'remind' => $remind,
		);
		if ( MainWP_Sucuri_DB::get_instance()->update_sucuri( $sucuri ) ) {
			die( 'SUCCESS' ); }
		die( 'FAIL' );
	}

	public static function ajax_save_ssl_verify() {
		update_option( 'mainwp_security_sslVerifyCertificate', intval( $_POST['security_sslverify'] ) );
		die( json_encode( array( 'saved' => 1 ) ) );
	}

	function delete_report() {
		$this->ajax_check_permissions( 'delete_report' );

		$report_id = intval( $_POST['reportId'] );
		if ( empty( $report_id ) ) {
			die( 'FAIL' ); }
		if ( MainWP_Sucuri_DB::get_instance()->remove_report_by( 'id', $report_id ) ) {
			die( 'SUCCESS' ); }
		die( 'FAIL' );
	}

	function show_report() {
		global $mainWPSucuriExtensionActivator;

		$this->ajax_check_permissions( 'show_report' );

		$report_id = intval( $_POST['reportId'] );
		$website_id = intval( $_POST['siteId'] );

		if ( empty( $report_id ) || empty( $website_id ) ) {
			die( 'FAIL' ); }

		$website = apply_filters( 'mainwp-getsites', $mainWPSucuriExtensionActivator->get_child_file(), $mainWPSucuriExtensionActivator->get_child_key(), $website_id );
		if ( $website && is_array( $website ) ) {
			$website = current( $website );
		}

		if ( empty( $website ) ) {
			die( 'FAIL' ); }

		if ( $report = MainWP_Sucuri_DB::get_instance()->get_report_by( 'id', $report_id ) ) {
			$data = unserialize( $report->data );
			echo $this->display_report( $website, $data );
			die( '' );
		}
		die( 'FAIL' );
	}

	public function ajax_sucuri_scan() {
		global $mainWPSucuriExtensionActivator;
		$this->ajax_check_permissions( 'sucuri_scan' );

		$website_id = $_POST['siteId'];
		$website = apply_filters( 'mainwp-getsites', $mainWPSucuriExtensionActivator->get_child_file(), $mainWPSucuriExtensionActivator->get_child_key(), $website_id );
		if ( $website && is_array( $website ) ) {
			$website = current( $website );
		}

		if ( empty( $website ) ) {
			?>
			<div class="mainwp_info-box-red"><?php _e( 'Error: Site not found.', 'mainwp' ); ?></div>
			<?php
			die();
		}

		$time_scan = time();
		$sucuri = array(
		'id' => intval( $_POST['sucuriId'] ),
			'site_url' => $website['url'],
			'lastscan' => $time_scan,
			'lastremind' => 0,
		);

		MainWP_Sucuri_DB::get_instance()->update_sucuri( $sucuri );
		$apisslverify = get_option( 'mainwp_security_sslVerifyCertificate', true );
		$scan_url = 'http://sitecheck.sucuri.net/scanner/?serialized&clear&mainwp&scan=' . $website['url'];
		$results = wp_remote_get( $scan_url, array( 'timeout' => 180, 'sslverify' => $apisslverify ) );
		$scan_result = $scan_status = '';
		if ( is_wp_error( $results ) ) {
			if ( 1 == $apisslverify ) {
				update_option( 'mainwp_security_sslVerifyCertificate', 0 );
				die( 'retry_action' );
			} else {
				$scan_status = 'failed';
				$scan_result = __( 'Error retrieving the scan report' );
				?>
				<div class="postbox">
					<h3><?php echo $scan_result; ?></h3>
					<div class="inside">
						<?php print_r( $results ); ?>
					</div>
				</div>            
				<?php
			}
		} else if ( preg_match( '/^ERROR:/', $results['body'] ) ) {
			$scan_status = 'failed';
			$scan_result = $results['body'];
			echo '<div class="mainwp_info-box-red">' . $scan_result . '</div>';
		} else {
			$report = array(
			'data' => $results['body'],
				'site_url' => $website['url'],
				'timescan' => $time_scan,
			);

			MainWP_Sucuri_DB::get_instance()->save_report( $report );
			$data = unserialize( $results['body'] );
			if ( ! is_array( $data ) ) {
				$scan_status = 'failed';
				$code = '';
				if ( is_array( $results ) && isset( $results['response'] ) ) {
					$code = ': code ' . $results['response']['code'];
				}
				echo '<div class="mainwp_info-box-red">' . __( 'Error Scan' ) . $code . '</div>';
			} else {
				$scan_result = $data;
				$scan_status = 'success';
				$this->display_report( $website, $data );
			}
		}
		do_action( 'mainwp_sucuri_scan_done', $website_id, $scan_status, $scan_result );
		die();
	}

	function sucuri_scan_data( $timescan ) {
		return MainWP_Sucuri_DB::get_instance()->get_report_by( 'timescan', $timescan );
	}

	protected function ajax_check_permissions( $action, $json = false ) {
		if ( has_filter( 'mainwp_currentusercan' ) ) {
			if ( ! mainwp_current_user_can( 'extension', 'mainwp-sucuri-extension' ) ) {
				$output = mainwp_do_not_have_permissions( 'MainWP Sucuri Extension ' . $action, ! $json );
				if ( $json ) {
					echo json_encode( array( 'error' => $output ) );
				}
				die();
			}
		} else {
			if ( ! current_user_can( 'manage_options' ) ) {
				$output = mainwp_do_not_have_permissions( 'MainWP Sucuri Extension ' . $action, ! $json );
				if ( $json ) {
					echo json_encode( array( 'error' => $output ) );
				}
				die();
			}
		}

		if ( ! isset( $_REQUEST['wp_nonce'] ) || ! wp_verify_nonce( $_REQUEST['wp_nonce'], MainWP_Sucuri::$nonce_token . $action ) ) {
			echo $json ? json_encode( array( 'error' => 'Error: Wrong or expired request' ) ) : 'Error: Wrong or expired request';
			die();
		}
	}

	function display_report( $website, $data ) {
		$blacklisted = isset( $data['BLACKLIST']['WARN'] ) ? true : false;
		$malware_exists = isset( $data['MALWARE']['WARN'] ) ? true : false;
		$system_error = isset( $data['SYSTEM']['ERROR'] ) ? true : false;
		//print_r($data);
		$status = array();
		if ( $blacklisted ) {
			$status[] = 'Site Blacklisted'; }
		if ( $malware_exists ) {
			$status[] = 'Site With Warnings'; }
		?>       
		<div class="mainwp_sucuri_report_content_box"> 
			<div class="scr-inside">    
				<div class="scr-content">
					<div class="mainwp_sucuri_logo"><a href="http://affl.sucuri.net/?affl=b5221d72b72a22a47202712d41a40fd9" target="_blank" title="Sucuri"><img src="<?php echo plugins_url( 'images/sucuri_logo.png', dirname( __FILE__ ) ); ?>"/></a></div>
					<p><strong><?php _e( 'Website' ); ?>:</strong> <a href="<?php echo admin_url( 'admin.php?page=managesites&dashboard=' . $website['id'] ) ?>" title="<?php _e( 'Dashboard' ); ?>"><?php echo $website['name'] ?></a> - <a href="<?php echo $website['url']; ?>" target="_blank" title="<?php _e( 'Open' ); ?>"><?php echo $website['url']; ?></a></p>
					<p><strong><?php _e( 'Status' ); ?>:</strong> <span class="<?php echo count( $status ) > 0 ? 'red' : 'green'; ?>"><?php echo count( $status ) > 0 ? implode( ', ', $status ) : 'Verified Clear'; ?></span></p>
					<p><strong><?php _e( 'Webtrust' ); ?>:</strong> <span class="<?php echo $blacklisted ? 'red' : 'green'; ?>"><?php echo $blacklisted ? 'Site Blacklisted' : 'Trusted'; ?></span></p>
				</div>
			</div>
		</div>

		<div class="mainwp_sucuri_report_content_box">                                
			<div class="handlediv"><br /></div>            
			<h3><?php _e( 'Security Scan Report' ); ?> <?php echo ($malware_exists || $system_error) ? '' : '(<span class="green">' . __( 'No Threats Found' ) . '</span>)'; ?></h3>
			<div class="scr-inside">     
				<div class="scr-content">
					<?php if ( ! $malware_exists && ! $system_error ) { ?>            
						<label>Blacklisted:</label> <span class="scr-status">NO</span><br>
						<label>Malware:</label> <span class="scr-status">NO</span><br>
						<label>Malicious javascript:</label> <span class="scr-status">NO</span><br>
						<label>Malicious iframes:</label> <span class="scr-status">NO</span><br>
						<label>Drive-By Downloads:</label> <span class="scr-status">NO</span><br>
						<label>Anomaly detection:</label> <span class="scr-status">NO</span><br>
						<label>IE-only attacks:</label> <span class="scr-status">NO</span><br>
						<label>Suspicious redirections:</label> <span class="scr-status">NO</span><br>
						<label>Blackhat SEO Spam:</label> <span class="scr-status">NO</span><br>
						<label>Spam:</label> <span class="scr-status">NO</span><br>
					<?php } else if ( $malware_exists ) { ?>
						<?php
						foreach ( $data['MALWARE']['WARN'] as $malware ) {
							?>
							<p><span class="ui-state-error"><span class="ui-icon ui-icon-alert"></span></span>                         
								<?php
								if ( ! is_array( $malware ) ) {
									echo htmlspecialchars( $malware );
								} else {
									$mwdetails = explode( "\n", htmlspecialchars( $malware[1] ) );
									$mwdetails = explode( 'Details:', substr( $mwdetails[0], 1 ) );
									echo htmlspecialchars( $malware[0] ) . "\n<br />";
									echo $mwdetails[0] . ' - <a href="' . trim( $mwdetails[1] ) . '">' . __( 'Details' ) . '</a>.';
								}
								?></p><?php
						}
							?>
						<?php } else if ( $system_error ) { ?>
							<?php
							foreach ( $data['SYSTEM']['ERROR'] as $error ) {
								?>
							<span class="ui-state-error"><span class="ui-icon ui-icon-alert"></span></span>                
							<?php
							if ( ! is_array( $error ) ) {
								echo htmlspecialchars( $error );
							} else {
								echo htmlspecialchars( $error[0] ) . "<br />\n";
							}
							}
						?>
					<?php } ?>
					<?php
					$scan_site = isset( $data['SCAN']['SITE'] ) ? htmlspecialchars( $data['SCAN']['SITE'][0] ) : '';
					$domain = isset( $data['SCAN']['DOMAIN'] ) ? htmlspecialchars( $data['SCAN']['DOMAIN'][0] ) : '';
					$ip = isset( $data['SCAN']['IP'] ) ? htmlspecialchars( $data['SCAN']['IP'][0] ) : '';
					?>
				</div>
				<div class="scr-inside-box closed">
					<a href="#" class="handlelnk"><?php _e( 'Show' ); ?></a>
					<h3><?php _e( 'Web Server Details' ); ?></h3>            
					<div class="scr-content">
						<?php
						echo __( 'Scan for:' ) . ' <strong>' . $scan_site . '</strong><br />';
						echo __( 'Hostname:' ) . ' <strong>' . $domain . '</strong><br />';
						echo __( 'IP address:' ) . ' <strong>' . $ip . '</strong><br />';
						echo '<br />';
						echo '<strong>' . __( 'System Details:' ) . '</strong>' . '<br />';
						$sys_noti = isset( $data['SYSTEM']['NOTICE'] ) ? $data['SYSTEM']['NOTICE'] : '';
						if ( $sys_noti ) {
							if ( ! is_array( $sys_noti ) ) {
								echo htmlspecialchars( $sys_noti );
							} else {
								foreach ( $sys_noti as $noti ) {
									echo htmlspecialchars( $noti ) . '<br />';
								}
							}
							echo '<br />';
						}

						if ( isset( $data['WEBAPP']['INFO'] ) ) {
							echo '<strong>' . __( 'Web application details:' ) . '</strong>' . '<br />';
							$webapp_info = isset( $data['WEBAPP']['INFO'] ) ? $data['WEBAPP']['INFO'] : '';
							if ( $webapp_info ) {
								if ( ! is_array( $webapp_info ) ) {
									echo htmlspecialchars( $webapp_info );
								} else {
									foreach ( $webapp_info as $info ) {
										if ( ! is_array( $info ) ) {
											echo htmlspecialchars( $info );
										} else {
											echo $info[0] . ' - ' . self::get_link( $info[1] );
										}
									}
								}
							}
							echo '<br />';
							echo '<br />';
						}

						if ( isset( $data['WEBAPP']['VERSION'] ) || isset( $data['WEBAPP']['NOTICE'] ) ) {
							echo '<strong>' . __( 'Web application version:' ) . '</strong>' . '<br />';
							if ( ! is_array( $data['WEBAPP']['VERSION'] ) ) {
								echo htmlspecialchars( $data['WEBAPP']['VERSION'] );
							} else if ( is_array( $data['WEBAPP']['VERSION'] ) && count( $data['WEBAPP']['VERSION'] ) > 0 ) {
								foreach ( $data['WEBAPP']['VERSION'] as $ver ) {
									echo htmlspecialchars( $ver ) . '<br />';
								}
							}

							if ( ! is_array( $data['WEBAPP']['NOTICE'] ) ) {
								echo htmlspecialchars( $data['WEBAPP']['NOTICE'] );
							} else if ( is_array( $data['WEBAPP']['NOTICE'] ) && count( $data['WEBAPP']['NOTICE'] ) > 0 ) {
								foreach ( $data['WEBAPP']['NOTICE'] as $noti ) {
									echo $noti . '<br />';
								}
							}
						}
						?>                    
					</div>                    
				</div>  

				<?php if ( is_array( $data['LINKS']['URL'] ) && count( $data['LINKS']['URL'] ) > 0 ) { ?>
					<div class="scr-inside-box closed">
						<a href="#" class="handlelnk"><?php _e( 'Show' ); ?></a>
						<h3><?php _e( 'List of URL Scanned' ); ?></h3>            
						<div class="scr-content">
							<?php
							foreach ( $data['LINKS']['URL'] as $jsres ) {
								echo '<p>' . $jsres . '</p>';
							}
							?>                    
						</div>                    
					</div>  
				<?php } ?>
				<?php if ( is_array( $data['LINKS']['JSLOCAL'] ) && count( $data['LINKS']['JSLOCAL'] ) > 0 ) { ?>
					<div class="scr-inside-box closed">
						<a href="#" class="handlelnk"><?php _e( 'Show' ); ?></a>
						<h3><?php _e( 'List of Javascripts Included' ); ?></h3>            
						<div class="scr-content">
							<?php
							foreach ( $data['LINKS']['JSLOCAL'] as $jsres ) {
								echo '<p>' . $jsres . '</p>';
							}
							?>                        
						</div>                    
					</div>  
				<?php } ?>                 
				<?php if ( is_array( $data['LINKS']['JSEXTERNAL'] ) && count( $data['LINKS']['JSEXTERNAL'] ) > 0 ) { ?>
					<div class="scr-inside-box closed">
						<a href="#" class="handlelnk"><?php _e( 'Show' ); ?></a>
						<h3><?php _e( 'List of External Javascripts Included' ); ?></h3>            
						<div class="scr-content">
							<?php
							foreach ( $data['LINKS']['JSEXTERNAL'] as $jsres ) {
								echo '<p>' . $jsres . '</p>';
							}
							?>                        
						</div>                    
					</div>  
				<?php } ?>   
				<?php if ( is_array( $data['LINKS']['IFRAME'] ) && count( $data['LINKS']['IFRAME'] ) > 0 ) { ?>
					<div class="scr-inside-box closed">
						<a href="#" class="handlelnk"><?php _e( 'Show' ); ?></a>
						<h3><?php _e( 'List of iframes Included' ); ?></h3>            
						<div class="scr-content">
							<?php
							foreach ( $data['LINKS']['IFRAME'] as $jsres ) {
								echo '<p>' . $jsres . '</p>';
							}
							?>                        
						</div>                    
					</div>  
				<?php } ?>                    
				<br />
			</div>
		</div>

		<div class="mainwp_sucuri_report_content_box"> 
			<h3><?php _e( 'Blacklisting Status' ); ?></h3>            
			<div class="scr-inside">
				<div class="scr-content">
					<?php
					foreach ( array(
					'INFO' => 'CLEAN',
					'WARN' => 'WARNING',
					) as $type => $group_title ) {
						if ( isset( $data['BLACKLIST'][ $type ] ) ) {
							foreach ( $data['BLACKLIST'][ $type ] as $blres ) {
								$report_site = htmlspecialchars( $blres[0] );
								$report_url = htmlspecialchars( $blres[1] );
								$info = "{$report_site} - <a href='{$report_url}' target='_blank'>" . __( 'Reference' ) . '</a>';
								if ( $type == 'INFO' ) {
									$icon = '<span class="ui-state-highlight"><span class="ui-icon ui-icon-info"></span></span>';
								} else {
									$icon = '<span class="ui-state-error"><span class="ui-icon ui-icon-alert"></span></span>';
								}
								echo '<p>' . $icon . $info . '</p>';
							}
						}
					}
					?>
				</div>
			</div>
			<br />
		</div>
		<?php
	}

	public static function sucuri_qsg() {
		$plugin_data = get_plugin_data( MAINWP_SUCURI_PLUGIN_FILE, false );
		$description = $plugin_data['Description'];
		$extraHeaders = array( 'DocumentationURI' => 'Documentation URI' );
		$file_data = get_file_data( MAINWP_SUCURI_PLUGIN_FILE, $extraHeaders );
		$documentation_url = $file_data['DocumentationURI'];
		?>
		<div  class="mainwp_ext_info_box" id="sr-pth-notice-box">
			<div class="mainwp-ext-description"><?php echo $description; ?></div><br/>
			<b><?php echo __( 'Need Help?' ); ?></b> <?php echo __( 'Review the Extension' ); ?> <a href="<?php echo $documentation_url; ?>" target="_blank"><i class="fa fa-book"></i> <?php echo __( 'Documentation' ); ?></a>. 
			<a href="#" id="mainwp-sr-quick-start-guide"><i class="fa fa-info-circle"></i> <?php _e( 'Show Quick Start Guide', 'mainwp' ); ?></a></div>
		<div  class="mainwp_ext_info_box" id="mainwp-sr-tips" style="color: #333!important; text-shadow: none!important;">
			<span><a href="#" class="mainwp-show-tut" number="1"><i class="fa fa-book"></i> <?php _e( 'Security Scan', 'mainwp' ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-tut"  number="2"><i class="fa fa-book"></i> <?php _e( 'Security Scan Notifications', 'mainwp' ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-tut"  number="3"><i class="fa fa-book"></i> <?php _e( 'Saving Security Scan Reports', 'mainwp' ) ?></a></span><span><a href="#" id="mainwp-sr-tips-dismiss" style="float: right;"><i class="fa fa-times-circle"></i> <?php _e( 'Dismiss', 'mainwp' ); ?></a></span>
			<div class="clear"></div>
			<div id="mainwp-sr-tuts">
				<div class="mainwp-sr-tut" number="1">
					<h3>Security Scan</h3>
					<p>The MainWP Sucuri Extension uses Sucuri's proprietary SiteCheck Tool to scan your sites. Sucuri provides web-based malware scanning of your web sites using the latest in fingerprinting technology allowing you to determine if your web applications are out of date, exploited with malware, or even blacklisted by popular search engines.</p>
					<ol>
						<li>Locate the Security Scan button and click it!</li><br/>
						<img src="http://docs.mainwp.com/wp-content/uploads/2014/04/run-security-scan-1024x95.png" style="max-width: 100%;">
						<li>Wait for a few seconds and review the Security Scan Report page.</li>
					</ol>
				</div>
				<div class="mainwp-sr-tut"  number="2">
					<h3>Security Scan Notifications</h3>
					<p>The MainWP Sucuri Extension provides you the ability to set email notification reminders so there will never be an instance when you forget to scan your child sites. You will need to set the reminders frequency for each of your child sites individually.</p>
					<p>Use the provided drop-down list to set the time interval.</p>
					<img src="http://docs.mainwp.com/wp-content/uploads/2014/05/notifications-1024x127.png" style="max-width: 100%;">
					<p>Your selection will be saved automatically. Email notifications will be sent to the email address you have saved in the MainWP Settings page under the Notification Email option.</p>
				</div>
				<div class="mainwp-sr-tut"  number="3">
					<h3>Saving Security Scan Reports</h3>
					<p>The MainWP Sucuri Extension saves all of your scan reports. If you need to review your past scans, locate the Saved Security Reports box in the Security Scan Page.</p>
					<p>In the list of saved reports, locate the report you wish to review and click the Show link</p>
					<img src="http://docs.mainwp.com/wp-content/uploads/2014/05/show-link-1024x203.png" style="max-width: 100%;">
					<p>If you no longer need a report, you can delete it by clicking the Delete link!</p>
				</div>
			</div>
		</div>
		<?php
	}
}
