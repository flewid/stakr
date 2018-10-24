<?php
if ( ! empty( $urls ) ) {
		$info = '';
	if ( count( $urls ) > 0 ) {
			// monitor color info
			$info .= '<div class="mainwp-row-top">';
			echo Advanced_Uptime_Monitor_Extension::get_install()->get_colors_info();
			$info .= '<div class="clear"></div>';
			$info .= '</div>';

			echo $info;
	}
		$info = '<div class="mainwp-row aum_monitor-header">';
		$info .= '<div class="mainwp-mid-col">';
		$info .= 'Last 24 Hours Status<br />';
		$current_gmt_time = time(); // timezone independent (=UTC).
		$hour = (int) date( 'H', $current_gmt_time + $log_gmt_offset * 60 * 60 );
	if ( 24 == $hour ) {
		for ( $i = 0; $i < $hour; $i++ ) {
				$info .= '<div class="aum_time">' . $i . '</div>'; }
	} else {
			$begin_hour = $hour + 1;
		for ( $i = $begin_hour; $i < 24; $i++ ) {
				$info .= '<div class="aum_time">' . $i . '</div>';
		}
		for ( $i = 0; $i <= $hour; $i++ ) {
				$info .= '<div class="aum_time">' . $i . '</div>';
		}
	}
		$info .= '</div>';
		$info .= '<div class="mainwp-left-col"><br />Status</div>';
		$info .= '<div class="mainwp-right-col"></div>';
		$info .= '<div class="clear"></div>';
		$info .= '</div>';
		echo $info;

		$monitor_statuses = array(
			0 => 'paused',
			1 => 'down',
			2 => 'up',
			3 => 'monitor_on',
			4 => 'monitor_off',
			99 => 'paused',
			98 => 'started',
		);
		foreach ( $urls as $url ) {
				?>
                <div class="mainwp-row aum_mainwp-monitor">
					<div class="aum_mainwp-monitor-name"><?php echo ( ! empty( $url->url_friendly_name ) ? $url->url_friendly_name . ' - ' . $url->url_address : $url->url_address); ?></div>
                    <div class="mainwp-mid-col aum_monitors_list">
						<?php
						$first = true;
						$status = '';

						$total_events = count( $stats[ $url->url_uptime_monitor_id ] );
						$i = 0;
						foreach ( $stats[ $url->url_uptime_monitor_id ] as $event ) {
								$i++;
							if ( ! $first ) { // avoid display the begging status
									$class = $monitor_statuses[ $log_type ] ? $monitor_statuses[ $log_type ] : 'not_checked';
								if ( $event->status_bar_length <= 0 ) {
										$style = 'style = "width: 2px; margin-right: -2px;"';
								} else {
										$style = 'style = "width: ' . $event->status_bar_length . '%"';
								}
								if ( $i == $total_events ) {
											$last_event = ' last_event="' . $class . '"';
											$class .= ' last_event';
								} else {
										$last_event = '';
								}

									$status = '<div class="event_fill ' . $class . '"' . $style . $last_event . '></div>' . $status;
							} else {
									$first = false;
							}
								$log_type = $event->type;
						}
						echo $status;
						//echo '<div class="aum_diagram_overlay"></div>';
						?>              
                    </div>                    
					<div class="mainwp-left-col"><?php echo Advanced_Uptime_Monitor_Extension::get_install()->render_status_bar( $url->monitor_status, $url->monitor_alltimeuptimeratio ); ?></div>
					<div class="mainwp-right-col url_actions" url_id="<?php echo $url->url_id ?>">                                       
						<?php if ( $url->monitor_status == 0 ) { ?>           
								<div onclick="urm_status_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'url_sp' ); ?>')" class="aum_action_link start"><i class="fa fa-play fa-lg"></i></div>
				<?php } else { ?>
								<div onclick="urm_status_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'url_sp' ); ?>')" class="aum_action_link pause"><i class="fa fa-pause fa-lg"></i></div>
								<?php } ?>
						<div  onclick="urm_stats_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'statistics_table' ); ?>')" class="aum_action_link stats_link"><i class="fa fa-bar-chart fa-lg"></i></div>
						<div  onclick="urm_edit_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'update_url' ); ?>')" class="aum_action_link url_edit_link"><i class="fa fa-pencil-square-o fa-lg"></i></div>
                        <div  onclick=" if (!confirm('Are you sure to delele selected item?'))
                                                    return;
				                                urm_dashboard_delete_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'delete_monitor' ); ?>')" class="aum_action_link url_delete_link"><i class="fa fa-trash-o fa-lg"></i></div>

                    </div>
                </div>
				<?php
		}
} else {
	if ( ! isset( $site_id ) ) {
			?>
			<p>No monitors displaying! <a href="admin.php?page=Extensions-Advanced-Uptime-Monitor-Extension" style="float: right">Add Monitor</a></p>			<?php
	} else {
			echo 'Monitor has not been created for the child site. Click <a href="admin.php?page=Extensions-Advanced-Uptime-Monitor-Extension" title="Create monitor">here</a> to create one.';
	}
}
