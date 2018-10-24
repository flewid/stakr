<?php
if ( ! empty( $this->option['uptime_robot_message'] ) ) {
	?>
	<div id="errorbox-uptime" class="error">
		<p><?php echo esc_html( $this->option['uptime_robot_message'] ); ?></p>
	</div>
	<?php
}
if ( $saved ) {
	if ( isset( $_POST['submit_btn'] ) ) {
		?>
		<div id="infobox-uptime" class="updated">
			<p><?php echo 'Your settings have been saved.'; ?></p>
		</div>
		<?php
	} else {
		?>
		<div id="infobox-uptime" class="updated">
			<p><?php echo 'Uptime Robot data has been reloaded.'; ?></p>
		</div>
		<?php
	}
}

?>
<form method="POST" action="admin.php?page=Extensions-Advanced-Uptime-Monitor-Extension" id="mainwp-settings-page-form">
	<div class="postbox">
		<h3 class="mainwp_box_title"><span>Uptime Robot API Key Settings</span></h3>

		<div class="inside">
			<?php
			if ( get_option( 'mainwp_aum_requires_reload_monitors' ) == 'yes' ) {
				echo '<div class="mainwp_info-box-yellow">' . __( 'Requires click on Save Settings to reload new added monitor(s).' ) . '</div>';
			}
			$api_key_aum = $this->option['api_key'];
			?>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">Uptime Robot main API Key</th>
					<td>
						<input type="text" name="api_key" size="35"
						       value="<?php echo esc_attr( $api_key_aum ); ?>"/><br/>
						<a href="http://uptimerobot.com/" target="_blank" style="font-size: 10px">Sign Up for API Key
							&#8594;</a>
					</td>
				</tr>
				<tr>
					<th scope="row">Monitor Notification default Email:</th>
					<td name="notification_default_email">
						<?php
						if ( is_array( $this->option['list_notification_contact'] ) && count( $this->option['list_notification_contact'] ) > 0 ) {
							?>
							<select name="select_default_noti_contact">
								<?php
								foreach ( $this->option['list_notification_contact'] as $key => $val ) {
									if ( $this->option['uptime_default_notification_contact_id'] == $key ) {
										echo '<option value="' . esc_attr( $key ) . '" selected="selected">' . esc_html( $val ) . '</option>';
									} else {
										echo '<option value="' . esc_attr( $key ) . '" >' . esc_html( $val ) . '</option>';
									}
								}
								?>
							</select>
							<?php
						} else {
							echo 'No items found! Check your Uptime Robot Settings.';
						}
						?>
					</td>
				</tr>

				</tbody>
			</table>
			<div class="mainwp_info-box"><strong>Note: </strong><i>By pressing "Save Settings" below you will reload all
					Uptime Robot monitor sites and notification contacts assigned with this main API Key.</i></div>
		</div>
	</div>

	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( $this->plugin_handle . '-option' ) ?>"/>
	<input type="hidden" name="aum_submit" value="1"/>

	<p class="submit">
		<input type="submit" name="submit_btn" class="button-primary" value="Save Settings"/>
		<input type="button" name="aum_monitor_reload" <?php echo empty( $api_key_aum ) ? 'disabled="disabled"' : ''; ?>
		       id="aum_monitor_reload" class="button" value="Reload Uptime Robot Data"/>
	</p>
</form>

<div class="postbox" id="aum_form_monitor_urls" style="display: block;">
	<h3 class="mainwp_box_title"><span>Advanced Uptime Monitor</span></h3>

	<div class="inside">
		<?php
		// monitor color info
		$info = '<div class="mainwp-row-top">';
		echo $this->get_colors_info();
		$info .= '<div class="clear"></div>';
		$info .= '</div>';
		echo $info;

		$get_page = isset( $_GET['get_page'] ) && $_GET['get_page'] > 0 ? $_GET['get_page'] : 1;
		if ( ! empty( $this->option['api_key'] ) ) {
			?>
			<div class="clear"></div>
			<div id="aum_mainwp_uptime_monitor_loading">
				<i class="fa fa-spinner fa-5x fa-pulse"></i>
			</div>
			<script type="text/javascript">

				jQuery(document).ready(function () {
					jQuery.ajax({
						url: ajaxurl,
						type: "POST",
						data: {
							action: 'admin_uptime_monitors_option_page',
							get_page: '<?php echo isset( $get_page ) ? esc_attr( $get_page ) : 1; ?>',
							wp_nonce: '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token.'option_page' ); ?>'
						},
						error: function () {
							jQuery('#aum_mainwp_uptime_monitor_loading').hide();
							jQuery('#aum_mainwp_widget_uptime_monitor_option_page').html('Request Timed Out - Try Again Later').fadeIn(2000);
							jQuery('a.aum_add_new_url_button').css('display', 'none');

						},
						success: function (response) {
							jQuery('#aum_mainwp_uptime_monitor_loading').hide();
							jQuery('#aum_mainwp_widget_uptime_monitor_option_page').html(response).fadeIn(2000);
						},
						timeout: 20000
					});
				});
			</script>
			<?php
		} else {
			if ( ! empty( $api_key_aum ) ) {
				echo '<div class="mainwp_info-box-yellow">No items found</div>';
				echo '<div class="mainwp_info-box-yellow">If you are experiencing issues with the monitors displaying, try deactivating and re-activating the Extension directly from the <a href="plugins.php" >WordPress Plugins screen.</a></div>';
			}
		}
		?>
		<div id="aum_mainwp_widget_uptime_monitor_option_page" class="monitors"></div>
		<?php
		if ( ! empty( $api_key_aum ) ) {
			//if ($this->option['api_key_status'] != 'invalid' && !empty($this->option['api_key_status'])) {
			// to fix bug no load MVC (from hook)
			require_once MVC_PLUGIN_PATH . 'app/models/uptime_monitor.php';
			$UM = new UptimeMonitor();
			$mo = $UM->get_user_main_monitor();
			?>
			<p>
				<a onclick="urm_add_new_monitor_button(this, event,<?php echo intval( $mo->monitor_id ); ?>, '<?php echo wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'url_form' ); ?>')"
				   class="aum_add_new_url_button button-primary">Add Monitor</a>
			</p>
			<?php
			// }
		}
		?>
	</div>
</div>
