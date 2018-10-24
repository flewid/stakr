<?php
$this->display_flash();

if ( ! empty( $urls ) ) {
	?>
    <input type="hidden" id="mainwp_aum_extension_display_dashboard_nonce" value="<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'display_dashboard' ); ?>"/>
    <div class="monitor_action actions">
        <select name="monitor_action" >
            <option value="-1" selected="selected">Bulk Actions</option>
            <option value="display" class="hide-if-no-js">Display on Dashboard</option>
            <option value="hidden" class="hide-if-no-js">Hide on Dashboard</option>
            <option value="start" class="hide-if-no-js">Start</option>
            <option value="pause" class="hide-if-no-js">Pause</option>
            <option value="delete">Delete</option>
        </select>
        <input type="button" href="javascript:void(0)" onclick="urm_apply_check(this, event)" name="doaction" id="doaction" class="button" value="Apply">
    </div>                            
    <?php
	$info = '<div class="mainwp-row aum_monitor-header">';
	$info .= '<div class="mainwp-left-col">';
	$info .= '<div class = "cell aum_url_checkbox"><br />                           
                            <input type="checkbox" name="checkall" class = "url_checkall" id="url_checkall">
                       </div></div>';
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
	$info .= '<div class="mainwp-extra-col status"><br />Status</div>';
	$info .= '<div class="mainwp-extra-col display">Display on<br />Dashboard</div>';
	$info .= '<div class="mainwp-extra-col url_actions"><br /></div>';
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
	//get_option('gmt_offset')
	foreach ( $urls as $url ) {
		?>
        <div class="mainwp-row aum_mainwp-monitor">                                            
            <div class="aum_mainwp-monitor-name"><?php echo ( ! empty( $url->url_friendly_name ) ? $url->url_friendly_name . ' - ' . $url->url_address : $url->url_address); ?>                    
                <div id="loading_status" class="aum_mainwp_monitor_actions_loading" >
                    <i class="fa fa-spinner fa-pulse"></i>&nbsp;   
                </div> 
            </div>
            <div class="mainwp-left-col">
                <div class = "cell aum_url_checkbox">
                    <input type="checkbox" name="checkbox_url" class = "checkbox_url" id="checkbox_url">
                </div>                    
            </div>                    
            <div class="mainwp-mid-col aum_monitors_list">
                <?php
				$first = true;
				$status = '';
				if ( is_array( $stats ) ) {
					$total_events = count( $stats[ $url->url_uptime_monitor_id ] );
					$i = 0;
					if ( isset( $stats[ $url->url_uptime_monitor_id ] ) ) {
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

								$status = '<div class="event_fill ' . $class . '" ' . $style . $last_event . '></div>' . $status;
							} else {
								$first = false;
							}
							$log_type = $event->type;
						}
					}
				}
				if ( empty( $status ) ) {
					$status = '<div class="event_fill" style = "width: 2px; margin-right: -2px;"></div>';
				}
				echo $status;
				//echo '<div class="aum_diagram_overlay"></div>';
				if ( isset( $url->dashboard ) && ($url->dashboard) ) {
					$iconname = 'ok.png';
				} else {
					$iconname = 'nok.png';
				}
				?>
            </div> 

            <div class="mainwp-extra-col status"><?php if ( isset( $url->monitor_status ) && isset( $url->monitor_alltimeuptimeratio ) ) {
				echo Advanced_Uptime_Monitor_Extension::get_install()->render_status_bar( $url->monitor_status, $url->monitor_alltimeuptimeratio );
} else {
	echo '&nbsp;';
} ?></div>                        

            <?php
			if ( isset( $url->dashboard ) ) {
				if ( $url->dashboard ) {
					$iconname = 'ok.png';
				} else {
					$iconname = 'nok.png';
				}
				?>
                <div class="mainwp-extra-col display cell url_display">
                    <img src="<?php echo plugins_url( 'images/' . $iconname, __FILE__ ) ?>" class="monitor_status"  >               
                </div>    
                    <?php
			}
				?>                        
            <div class="mainwp-extra-col url_actions" url_id="<?php echo $url->url_id ?>">                                       
        <?php if ( isset( $url->monitor_status ) && ( $url->monitor_status == 0) ) { ?>           
                    <div onclick="urm_status_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'url_sp' ); ?>')" title="Start" class="aum_action_link start"><i class="fa fa-play fa-lg"></i></div>
        <?php } else { ?>
                    <div onclick="urm_status_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'url_sp' ); ?>')" title="Pause" class="aum_action_link pause"><i class="fa fa-pause fa-lg"></i></div>
        <?php } ?>
                <div  onclick="urm_stats_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'statistics_table' ); ?>')" title="Statistics" class="aum_action_link stats_link"><i class="fa fa-bar-chart fa-lg"></i></div>
                <div  onclick="urm_edit_monitor_button(this, event, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'update_url' ); ?>')" title="Edit" class="aum_action_link url_edit_link"><i class="fa fa-pencil-square-o fa-lg"></i></div>
                <div  onclick=" if (!confirm('Are you sure to delele selected item?'))
                                                    return;
                                                urm_delete_monitor_button(jQuery(this), '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'delete_monitor' ); ?>')" title="Delete" class="aum_action_link url_delete_link"><i class="fa fa-trash-o fa-lg"></i></div>
            </div>
        </div>            
        <?php
	}
	echo '<div class="mwp_aum_monitor_pager">';
	if ( $total > 50 ) {
		$count_page = round( $total / 50 );
		echo 'Pages:&nbsp;&nbsp;';
		for ( $i = 1; $i <= $count_page; $i++ ) {
			if ( $i != $get_page ) {
				echo '<a href="admin.php?page=Extensions-Advanced-Uptime-Monitor-Extension&get_page=' . $i . '">' . $i . '</a>&nbsp;&nbsp;';
			} else {
				echo '<strong>' . $i . '</strong>&nbsp;&nbsp;';
			}
		}
	}
	echo 'Total: ' . $total . ' monitors</div>';
} else {
	?>
    <div class="updated" id="message">
        <p>
            No Monitor has been created yet.
        </p>
    </div>
    <?php
}
?>    
<script>
    jQuery('input[name=checkall]').click(function () {


        if (jQuery(this).is(':checked'))
        {
            jQuery('input[name=checkbox_url]').each(function () {
                jQuery(this).attr('checked', 'checked');

            })
        }
        else
        {
            jQuery('input[name=checkbox_url]').each(function () {
                jQuery(this).removeAttr('checked');
            })
        }


    })
</script>
