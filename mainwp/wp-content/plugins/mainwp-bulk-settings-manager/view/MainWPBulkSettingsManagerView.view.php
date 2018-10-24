<?php

class MainWPBulkSettingsManagerView {

	public static $plugin_translate = "bulk_settings_manager_extension";
	public static $render_selectbox_counter = 1;
	public static $id = 0;

	/**
	 * Display messages from controller
	 **/
	public static function display_messages() {
		if ( ! empty( MainWPBulkSettingsManager::$messages ) ) {
			?>
			<div class="updated">
				<?php
				foreach ( MainWPBulkSettingsManager::$messages as $message ) {
					echo '<p>' . esc_html( $message ) . '</p>';
				}
				?>
			</div>
			<?php
		}

		if ( ! empty( MainWPBulkSettingsManager::$error_messages ) ) {
			?>
			<div class="error">
				<?php
				foreach ( MainWPBulkSettingsManager::$error_messages as $message ) {
					echo '<p>' . esc_html( $message ) . '</p>';
				}
				?>
			</div>
			<?php
		}
	}

	/**
	 * @param $name
	 *
	 * @return string
	 *
	 * Some fields are tricky to use
	 * For example http://codex.wordpress.org/Changing_The_Site_URL
	 */
	public static function check_if_blacklisted_name( $name ) {
		$name = strtolower( trim( $name ) );
		if ( in_array( $name, array( 'siteurl', 'home' ) ) ) {
			return '<span style="color: #a00"><i class="fa fa-exclamation-circle"></i>' . __( 'Be carefull when using this name', self::$plugin_translate ) . '</span>';
		}
	}

	/**
	 * @param string $description
	 * @param string $name
	 * @param string $value
	 * @param string $type
	 *
	 * Render <input type="text">
	 */
	public static function render_text_field( $description = "", $name = "", $value = "", $type = "" ) {
		?>
		<div class='widget'>
			<div class="widget-top">
				<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#"></a></div>
				<div class="widget-title">
					<h4><?php _e( 'Text Field', self::$plugin_translate ); ?> <span class="in-widget-title"></span></h4>
				</div>
			</div>

			<div class="widget-inside">
				<div class="widget-content">
					<input type="hidden" name="field_type" value="text_field">

					<p>
						<label><?php _e( 'Description:', self::$plugin_translate ); ?></label>
						<input class="widefat bulk_settings_manager_description" name="text_field_description"
						       type="text"
						       value="<?php echo esc_attr( $description ); ?>"/>
					</p>

					<p>
						<label><?php _e( 'Name:', self::$plugin_translate ); ?></label>
						<input class="widefat" name="text_field_name" type="text"
						       value="<?php echo esc_attr( $name ); ?>"/>
						<?php echo self::check_if_blacklisted_name( $name ); ?>
					</p>

					<p>
						<label><?php _e( 'Value:', self::$plugin_translate ); ?></label>
						<input class="widefat" name="text_field_value" type="text"
						       value="<?php echo esc_attr( $value ); ?>"/>
					</p>

					<p>
						<label><?php _e( 'Type:', self::$plugin_translate ); ?></label>
						<select name="text_field_type">
							<option value="post">$_POST</option>
							<option value="get" <?php selected( $type, 'get' ); ?>>$_GET</option>
						</select>
					</p>
				</div>


				<div class="widget-control-actions">
					<div class="alignright">
						<a class="widget-control-remove" href="#remove" style="color: #a00;"><i
								class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?></a> |
						<a class="widget-control-close" href="#close"><i
								class="fa fa-times-circle"></i> <?php _e( 'Close', self::$plugin_translate ); ?></a>
					</div>

					<br class="clear"/>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * @param string $description
	 * @param string $name
	 * @param string $value
	 * @param string $type
	 *
	 * Render <textarea>
	 */
	public static function render_textarea_field( $description = "", $name = "", $value = "", $type = "" ) {
		?>
		<div class='widget'>
			<div class="widget-top">
				<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#"></a></div>
				<div class="widget-title">
					<h4><?php _e( 'Textarea Field', self::$plugin_translate ); ?> <span class="in-widget-title"></span>
					</h4>
				</div>
			</div>

			<div class="widget-inside">
				<div class="widget-content">
					<input type="hidden" name="field_type" value="textarea_field">

					<p>
						<label><?php _e( 'Description:', self::$plugin_translate ); ?></label>
						<input class="widefat bulk_settings_manager_description" name="textarea_field_description"
						       type="text"
						       value="<?php echo esc_attr( $description ); ?>"/>
					</p>

					<p>
						<label><?php _e( 'Name:', self::$plugin_translate ); ?></label>
						<input class="widefat" name="textarea_field_name" type="text"
						       value="<?php echo esc_attr( $name ); ?>"/>
						<?php echo self::check_if_blacklisted_name( $name ); ?>
					</p>

					<p>
						<label><?php _e( 'Value:', self::$plugin_translate ); ?></label>
						<textarea class="widefat"
						          name="textarea_field_value"><?php echo esc_textarea( $value ); ?></textarea>
					</p>

					<p>
						<label><?php _e( 'Type:', self::$plugin_translate ); ?></label>
						<select name="textarea_field_type">
							<option value="post">$_POST</option>
							<option value="get" <?php selected( $type, 'get' ); ?>>$_GET</option>
						</select>
					</p>
				</div>


				<div class="widget-control-actions">
					<div class="alignright">
						<a class="widget-control-remove" href="#remove" style="color: #a00;"><i
								class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?></a> |
						<a class="widget-control-close" href="#close"><i
								class="fa fa-times-circle"></i> <?php _e( 'Close', self::$plugin_translate ); ?></a>
					</div>

					<br class="clear"/>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $type
	 *
	 * Render <input type="submit">
	 */
	public static function render_submit_field( $name = "", $value = "", $type = "" ) {
		?>
		<div class='widget'>
			<div class="widget-top">
				<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#"></a></div>
				<div class="widget-title">
					<h4><?php _e( 'Submit Field', self::$plugin_translate ); ?> <span class="in-widget-title"></span>
					</h4>
				</div>
			</div>

			<div class="widget-inside">
				<div class="widget-content">
					<input type="hidden" name="field_type" value="submit_field">

					<p>
						<label><?php _e( 'Name:', self::$plugin_translate ); ?></label>
						<input class="widefat" name="submit_field_name" type="text"
						       value="<?php echo esc_attr( $name ); ?>"/>
					</p>

					<p>
						<label><?php _e( 'Value:', self::$plugin_translate ); ?></label>
						<input class="widefat" name="submit_field_value" type="text"
						       value="<?php echo esc_attr( $value ); ?>"/>
					</p>

					<p>
						<label><?php _e( 'Type:', self::$plugin_translate ); ?></label>
						<select name="submit_field_type">
							<option value="post">$_POST</option>
							<option value="get" <?php selected( $type, 'get' ); ?>>$_GET</option>
						</select>
					</p>
				</div>


				<div class="widget-control-actions">
					<div class="alignright">
						<a class="widget-control-remove" href="#remove" style="color: #a00;"><i
								class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?></a> |
						<a class="widget-control-close" href="#close"><i
								class="fa fa-times-circle"></i> <?php _e( 'Close', self::$plugin_translate ); ?></a>
					</div>

					<br class="clear"/>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * @param string $name
	 * @param string $url
	 *
	 * All keys need to have name and url
	 */
	public static function render_settings_field( $name = "", $url = "" ) {
		?>
		<div class='widget'>
			<div class="widget-top">
				<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#"></a></div>
				<div class="widget-title">
					<h4><?php _e( 'Key Settings', self::$plugin_translate ); ?><span class="in-widget-title"></span>
					</h4>
				</div>
			</div>

			<div class="widget-inside widget_settings">

				<div class="widget-content">
					<input type="hidden" name="field_type" value="settings_field">

					<p>
						<label><?php _e( 'Settings Name:', self::$plugin_translate ); ?> <input class="widefat"
						                                                                        name="settings_field_name"
						                                                                        type="text"
						                                                                        value="<?php echo esc_attr( $name ); ?>"/></label>
					</p>

					<p>
						<label><?php _e( 'Settings URL:', self::$plugin_translate ); ?> <input class="widefat"
						                                                                       name="settings_field_url"
						                                                                       type="text"
						                                                                       value="<?php echo esc_attr( $url ); ?>"/></label>
					</p>

					<p>
						<label><?php _e( 'New Key Ring:', self::$plugin_translate ); ?> <input class="widefat"
						                                                                       name="settings_field_keyring"
						                                                                       type="text"/></label>
					</p>

					<p>
						<select name="settings_field_keyring_select" multiple style="width: 100%;">
							<?php
							foreach ( MainWPBulkSettingsManagerDB::Instance()->get_key_rings_by_entry_id( ( $name == "" && $url == "" ) ? 0 : self::$id ) as $keyring ) {
								echo '<option value="' . esc_attr__( $keyring['id'] ) . '" ' . selected( 1, $keyring['checked'] ) . '>' . esc_html( $keyring['name'] ) . '</option>';
							}
							?>
						</select>
						<br/>
						<em><?php _e( 'Or select existing Key Ring', self::$plugin_translate ); ?></em>
					</p>


				</div>


				<div class="widget-control-actions">
					<div class="alignright">
						<a class="widget-control-close" href="#close"><i
								class="fa fa-times-circle"></i> <?php _e( 'Close', self::$plugin_translate ); ?></a>
					</div>
					<br class="clear"/>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * @param string $name
	 * @param string $arg
	 *
	 * Render all nonce fields ( so generated using wp_nonce_field)
	 */
	public static function render_nonce_field( $name = "", $arg = "" ) {
		?>
		<div class='widget'>
			<div class="widget-top">
				<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#"></a></div>
				<div class="widget-title">
					<h4><?php _e( 'Nonce', self::$plugin_translate ); ?><span class="in-widget-title"></span></h4>
				</div>
			</div>

			<div class="widget-inside">

				<div class="widget-content">
					<input type="hidden" name="field_type" value="nonce_field">

					<p>
						<label><?php _e( 'Nonce name:', self::$plugin_translate ); ?> <input class="widefat"
						                                                                     name="nonce_field_name"
						                                                                     type="text"
						                                                                     value="<?php echo esc_attr( $name ); ?>"/></label>
					</p>

					<p>
						<label><?php _e( 'Optional Query Arg:', self::$plugin_translate ); ?> <input class="widefat"
						                                                                             name="nonce_field_arg"
						                                                                             type="text"
						                                                                             value="<?php echo esc_attr( $arg ); ?>"/></label>
					</p>
				</div>


				<div class="widget-control-actions">
					<div class="alignright">
						<a class="widget-control-remove" href="#remove" style="color: #a00;"><i
								class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?></a> |
						<a class="widget-control-close" href="#close"><i
								class="fa fa-times-circle"></i> <?php _e( 'Close', self::$plugin_translate ); ?></a>
					</div>
					<br class="clear"/>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * @param string $ok
	 * @param string $fail
	 *
	 * Sometimes we want to check if some text exist in response
	 */
	public static function render_search_field( $ok = "", $fail = "" ) {
		?>
		<div class='widget'>
			<div class="widget-top">
				<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#"></a></div>
				<div class="widget-title">
					<h4><?php _e( 'Search Text', self::$plugin_translate ); ?><span class="in-widget-title"></span></h4>
				</div>
			</div>

			<div class="widget-inside">

				<div class="widget-content">
					<input type="hidden" name="field_type" value="search_field">

					<p>
						<label><?php _e( 'OK Text:', self::$plugin_translate ); ?> <input class="widefat"
						                                                                  name="search_field_ok"
						                                                                  type="text"
						                                                                  value="<?php echo esc_attr( $ok ); ?>"/></label>
					</p>

					<p>
						<label><?php _e( 'Fail Text:', self::$plugin_translate ); ?> <input class="widefat"
						                                                                    name="search_field_fail"
						                                                                    type="text"
						                                                                    value="<?php echo esc_attr( $fail ); ?>"/></label>
					</p>
				</div>


				<div class="widget-control-actions">
					<div class="alignright">
						<a class="widget-control-remove" href="#remove" style="color: #a00;"><i
								class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?></a> |
						<a class="widget-control-close" href="#close"><i
								class="fa fa-times-circle"></i> <?php _e( 'Close', self::$plugin_translate ); ?></a>
					</div>
					<br class="clear"/>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * @param string $description
	 * @param string $name
	 * @param string $type
	 * @param string $type_send
	 * @param array $fields
	 *
	 * Render <select> field
	 */
	public static function render_selectbox_field( $description = "", $name = "", $type = "", $type_send = "", $fields = array() ) {
		?>
		<div class='widget'>
			<div class="widget-top">
				<div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#"></a></div>
				<div class="widget-title">
					<?php
					if ( $type == 'checkbox' ):
						?>
						<h4><?php _e( 'Check Box', self::$plugin_translate ); ?> <span class="in-widget-title"></span>
						</h4>
						<?php
					else:
						?>
						<h4><?php _e( 'Select Box / Radio Box', self::$plugin_translate ); ?> <span
								class="in-widget-title"></span></h4>
						<?php
					endif;
					?>
				</div>
			</div>

			<div class="widget-inside">

				<div class="widget-content">
					<input type="hidden" name="field_type" value="selectbox_field">

					<p>
						<label><?php _e( 'Description:', self::$plugin_translate ); ?></label>
						<input class="widefat bulk_settings_manager_description" name="selectbox_field_description"
						       type="text"
						       value="<?php echo esc_attr( $description ); ?>"/>
					</p>

					<p>
						<label><?php _e( 'Name:', self::$plugin_translate ); ?></label>
						<input class="widefat" name="selectbox_field_name" type="text"
						       value="<?php echo esc_attr( $name ); ?>"/>
					</p>

					<input type="hidden" name="selectbox_field_type"
					       value="<?php echo( $type == 'checkbox' ? 'checkbox' : 'radio' ); ?>">

					<p>
						<label><?php _e( 'Type:', self::$plugin_translate ); ?></label>
						<select name="selectbox_field_type_send">
							<option value="post">$_POST</option>
							<option value="get" <?php selected( $type_send, 'get' ); ?>>$_GET</option>
						</select>
					</p>

					<table
						class="<?php echo( $type == 'checkbox' ? 'selectbox_field_table' : 'radio_field_table' ); ?>">
						<thead>
						<tr>
							<td></td>
							<td></td>
							<td><?php _e( 'Label', self::$plugin_translate ); ?></td>
							<td><?php _e( 'Value', self::$plugin_translate ); ?></td>
							<td></td>
						</tr>
						</thead>
						<tbody>
						<?php
						if ( empty( $fields ) ) {
							?>
							<tr>
								<td><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/last.png'; ?>"></td>
								<td>
									<input type="<?php echo( $type == 'checkbox' ? 'checkbox' : 'radio' ); ?>"
									       name="fake_radio_name_replacement"
									       class="<?php echo( $type == 'checkbox' ? 'selectbox_field_checkbox_click' : 'selectbox_field_radio_click' ); ?>">
									<input type="hidden" name="selectbox_field_checkbox" value="0">
								</td>
								<td><input type="text" name="selectbox_field_label"></td>
								<td><input type="text" name="selectbox_field_value"></td>
								<td><a style="cursor: pointer;"
								       class="<?php echo( $type == 'checkbox' ? 'selectbox_field_add_click' : 'radio_field_add_click' ); ?>"><i
											class="fa fa-plus-circle"></i></a> <a style="cursor: pointer;"
								                                                  class="selectbox_field_remove_click"><i
											class="fa fa-minus-circle"></i></a></td>
							</tr>
							<?php
						}

						foreach ( $fields as $field ):
							?>
							<tr>
								<td><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/last.png'; ?>"></td>
								<td>

									<input
										type="<?php echo( $type == 'checkbox' ? 'checkbox' : 'radio' ); ?>" <?php checked( $field['selectbox_field_checkbox'], 1 ); ?>
										name="fake_radio_name_<?php echo (int) self::$render_selectbox_counter; ?>"
										class="<?php echo( $type == 'checkbox' ? 'selectbox_field_checkbox_click' : 'selectbox_field_radio_click' ); ?>">

									<input type="hidden" name="selectbox_field_checkbox"
									       value="<?php echo( $field['selectbox_field_checkbox'] == 1 ? 1 : 0 ); ?>">
								</td>
								<td><input type="text" name="selectbox_field_label"
								           value="<?php echo esc_attr( $field['selectbox_field_label'] ); ?>"></td>
								<td><input type="text" name="selectbox_field_value"
								           value="<?php echo esc_attr( $field['selectbox_field_value'] ); ?>"></td>
								<td><a href=""
								       rel="<?php echo (int) self::$render_selectbox_counter; ?>"
								       class="<?php echo( $type == 'checkbox' ? 'selectbox_field_add_click' : 'radio_field_add_click' ); ?>"><i
											class="fa fa-plus-circle"></i></a> <a href=""
								                                                  class="selectbox_field_remove_click"><i
											class="fa fa-minus-circle"></i></a></td>
							</tr>
							<?php
						endforeach;
						++ self::$render_selectbox_counter;
						?>
						</tbody>

					</table>
				</div>

				<div class="widget-control-actions">
					<div class="alignright">
						<a class="widget-control-remove" href="#remove" style="color: #a00;"><i
								class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?></a> |
						<a class="widget-control-close" href="#close"><i
								class="fa fa-times-circle"></i> <?php _e( 'Close', self::$plugin_translate ); ?></a>
					</div>
					<br class="clear"/>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Render wp-admin/admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager
	 */
	public static function render_view() {
		self::display_messages();
		add_thickbox();

		self::$id = ( isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0 );
		if ( self::$id > 0 ) {
			$entry = MainWPBulkSettingsManagerDB::Instance()->get_entry_by_id( self::$id );
		}

		self::render_qsg();
		?>

		<div class="error" id="bulk_settings_manager_error" style="display: none;"></div>
		<div class="updated" id="bulk_settings_manager_message" style="display: none;"></div>
		<div id="bulk_settings_manager_preview" style="display: none;"></div>
		<noscript><?php _e( 'Please enable JavaScript.', self::$plugin_translate ); ?></noscript>

		<div id="ngBulkSettingsManagerId" ng-app="ngBulkSettingsManagerApp" style="display: none;">

			<div ng-controller="ngBulkSettingsManagerController">
				<?php
				if ( isset( $_GET['just_imported'] ) ):
					?>
					<div
						class="updated">
						<p><?php _e( 'Your Key has been made. Please review the key fields below to verify everything imported correctly.' ); ?>
							<input type="button" ng-click="use_this_key(<?php echo self::$id; ?>)"
							       class="button button-primary" value="<?php _e( 'Use This Key Now' ); ?>"></p>
					</div>
					<?php
				endif;
				?>

				<div class="mainwp-backwpup-tabs"
				     ng-init="<?php echo( self::$id > 0 ? "tab='edit'" : "tab='keyring'" ); ?>;">
					<a style="margin-right: -3px; cursor: pointer;" class="mainwp_action left"
					   ng-class="{mainwp_action_down:is_selected('keyring')}"
					   ng-click="select_tab('keyring')">
						<?php _e( 'Key Rings', self::$plugin_translate ); ?>
					</a>

					<a style="margin-right: -3px; cursor: pointer;" class="mainwp_action mid"
					   ng-class="{mainwp_action_down:is_selected('dashboard')}"
					   ng-click="select_tab('dashboard')">
						<?php _e( 'Single Keys', self::$plugin_translate ); ?>
					</a>

					<a style="margin-right: -3px; cursor: pointer;" class="mainwp_action mid"
					   ng-class="{mainwp_action_down:is_selected('add')}"
					   ng-click="select_tab('add')"><?php _e( 'Create New Key', self::$plugin_translate ); ?></a>

					<a style="margin-right: -3px; cursor: pointer;" class="mainwp_action mid"
					   ng-class="{mainwp_action_down:is_selected('history')}"
					   ng-click="select_tab('history')"><?php _e( 'History', self::$plugin_translate ); ?></a>

					<a style="margin-right: -3px; cursor: pointer;" class="mainwp_action mid"
					   ng-class="{mainwp_action_down:is_selected('settings')}"
					   ng-click="select_tab('settings')"><?php _e( 'Settings', self::$plugin_translate ); ?></a>

					<a style="margin-right: -3px; background: #7fb100 !important; color: #fff !important; cursor: pointer;"
					   class="mainwp_action right"
					   ng-class="{mainwp_action_down:is_selected('import')}"
					   ng-click="select_tab('import')"><?php _e( 'Import Keys from Key Maker', self::$plugin_translate ); ?></a>
				</div>

				<span ng-show="is_selected('edit')">
				<?php
				if ( self::$id > 0 ):
					_e( 'You are here:', self::$plugin_translate );
					?>
					<a href="<?php echo admin_url( 'admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager' ); ?>">
						<?php
						_e( 'Keys', self::$plugin_translate );
						?>
					</a>
					> <?php echo( ! empty( $entry ) ? ' ' . esc_html( $entry->name ) : '' ); ?>
					<?php
				endif;
				?>
				</span>

				<div ng-show="is_selected('keyring')">
					<?php do_action( 'mainwp_select_sites_box', __( "Select Sites", 'mainwp' ), 'checkbox', true, true, 'mainwp_select_sites_box_right mainwp_select_sites_keyring', "", array(), array() ); ?>

					<div class="postbox" style="width: calc(100% - 280px) !important; float: left;">
						<h3 class="mainwp_box_title">
							<i class="fa fa-cog"></i> <?php _e( 'Keyrings', self::$plugin_translate ); ?>
							<span style="float: right;"><a href="javascript:void(0)" class="reload_keyring_button"
							                               ng-click="reload_keyring()" style="text-decoration: none;"><i
										class="fa fa-refresh"></i> <?php _e( 'Reload Table', self::$plugin_translate ); ?>
								</a></span>
						</h3>

						<div class="inside">
							<div class="mainwp_info-box-blue">
								<?php _e( 'This section is for your grouped keys (Key Rings). Here, Keys can be run individually or all the Keys on a Key Ring can be run at the same time.', self::$plugin_translate ); ?>
							</div>

							Filter: <input type="text" ng-model="keyring_filter['name']"/><br/><br/>

							<div tasty-table bind-resource-callback="get_keyring" bind-init="init_get_keyring"
							     bind-filters="keyring_filter" bind-theme="keyring_theme"
							     bind-reload="reload_keyring_callback">
								<table class="wp-list-table widefat table">
									<thead tasty-thead not-sort-by="['checkbox', 'keys', 'actions']"></thead>
									<tbody>
									<tr ng-if="rows.length==0">
										<td>Loading</td>
									</tr>
									<tr ng-repeat-start="d in rows">
										<td ng-show="d.id">
											<input class="bulk_settings_manager_keyring_checkbox" type="checkbox"
											       ng-model="scope_checkbox_keyring[d.id]"
											       ng-click="checkbox_keyring_toggle(d.id)" value="{{ d.id }}">
										</td>
										<td>
											<span ng-show="d.id">
												<a ng-hide="scope_display_edit_keyring[d.id]" style="cursor: pointer;"
												   ng-click="toggle_keyring(d.id)">{{ d.name }}</a>
											</span>
											<span ng-hide="d.id">
												{{ d.name }}
											</span>
											<input ng-show="scope_display_edit_keyring[d.id]" type="text"
											       ng-model="scope_edit_keyring[d.id]">
										</td>

										<td ng-show="d.id">
											<a ng-hide="scope_toggled_keyring[d.id]" style="cursor: pointer;"
											   ng-click="toggle_keyring(d.id)"><i
													class="fa fa-eye"></i> <?php _e( 'Show Keys', 'mainwp' ); ?></a>
											<a ng-show="scope_toggled_keyring[d.id]" style="cursor: pointer;"
											   ng-click="toggle_keyring(d.id)"><i
													class="fa fa-eye-slash"></i> <?php _e( 'Hide Keys', 'mainwp' ); ?>
											</a>
										</td>

										<td ng-show="d.id">
											<a style="cursor: pointer;"
											   ng-show="d.id && !scope_display_edit_keyring[d.id]"
											   ng-click="enable_editing_keyring(d.id, d.name)"><i
													class="fa fa-pencil-square-o"></i> <?php _e( 'Edit', self::$plugin_translate ); ?>
											</a>
											<a style="cursor: pointer;"
											   ng-show="d.id && scope_display_edit_keyring[d.id]"
											   ng-click="enable_editing_keyring(d.id)"><i
													class="fa fa-pencil-square-o"></i> <?php _e( 'Save', self::$plugin_translate ); ?>
											</a>
											|
											<a style="cursor: pointer;"
											   ng-click="show_notes(d.id, d.name, 'keyring')"><i
													class="fa fa-pencil"></i> <?php _e( 'Notes', 'mainwp' ); ?></a>
											|
											<a ng-show="d.id" ng-click="delete_settings(d.id, 'keyring')"
											   style="cursor: pointer; color: #a00;"><i
													class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?>
											</a>
										</td>
									</tr>

									<tr ng-repeat-end>
										<td colspan="6" ng-show="scope_toggled_keyring[d.id]">
											<span ng-hide="scope_toggled_keyring_datas[d.id]">Loading ...</span>
											<table ng-show="scope_toggled_keyring_datas[d.id]"
											       class="wp-list-table widefat table">
												<tr>
													<td></td>
													<td>Name</td>
													<td>Url</td>
													<td>Edit</td>
													<td>Remove</td>
												</tr>
												<tr ng-repeat="dd in scope_toggled_keyring_datas[d.id]">
													<td><input ng-show="dd.id" ng-checked="scope_checkbox_keyring[d.id]"
													           type="checkbox"
													           class="bulk_settings_manager_checkbox_keyring_subkey"
													           value="{{ dd.id }}"></td>
													<td>{{ dd.name }}</td>
													<td>{{ dd.url }}</td>
													<td><a ng-show="dd.id"
													       href="<?php echo admin_url( 'admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager&id=' ); ?>{{ dd.id }}"><i
																class="fa fa-pencil-square-o"></i> <?php _e( 'Edit', self::$plugin_translate ); ?>
														</a></td>
													<td ng-show="dd.id>0"><a style="cursor: pointer; color: #a00;"
													                         ng-click="remove_from_keyring(d.id, dd.id)"><i
																class="fa fa-trash-o"></i> <?php _e( 'Remove from the Key Ring', self::$plugin_translate ); ?>
														</a></td>
												</tr>
											</table>
										</td>
									</tr>
									</tr>
									</tbody>
								</table>
								<div tasty-pagination template-url="custom_table_template_ring.html"></div>
							</div>

							<div
								style="width: 100%; text-align: right; border-top: 1px Dotted #e5e5e5; margin-top: 1em; padding-top: 1em;">
								<input type="button" class="button button-primary" ng-click="send_to_child('keyring')"
								       value="Save Key Ring to Selected Child Sites" style="margin-left: 5px;">
								<input type="button" class="button" ng-click="delete_settings_bulk('keyring')"
								       value="Delete Selected Key Rings">
							</div>
							<div style="clear: both;"></div>
						</div>
					</div>
					<div style="clear: both;"></div>
				</div>

				<div ng-show="is_selected('dashboard')">

					<?php do_action( 'mainwp_select_sites_box', __( "Select Sites", 'mainwp' ), 'checkbox', true, true, 'mainwp_select_sites_box_right mainwp_select_sites_key', "", array(), array() ); ?>

					<div class="postbox" style="width: calc(100% - 280px) !important; float: left;">
						<h3 class="mainwp_box_title"><i
								class="fa fa-cog"></i> <?php _e( 'Keys', self::$plugin_translate ); ?>
							<span style="float: right;"><a href="javascript:void(0)" ng-click="reload_key()"
							                               style="text-decoration: none;"><i
										class="fa fa-refresh"></i> <?php _e( 'Reload Table', self::$plugin_translate ); ?>
								</a></span>
						</h3>

						<div class="inside">
							<div class="mainwp_info-box-blue">
								<?php _e( 'This section is for your single keys. Single Keys can only be run one at a time.', self::$plugin_translate ); ?>
							</div>

							Filter: <input type="text" ng-model="key_filter['name']"/><br/><br/>

							<div tasty-table bind-resource-callback="get_key" bind-init="init_get_key"
							     bind-filters="key_filter" bind-theme="key_theme" bind-reload="reload_key_callback">

								<table class="wp-list-table widefat table">
									<thead tasty-thead not-sort-by="['checkbox', 'actions']"></thead>
									<tr ng-if="rows.length==0">
										<td>Loading</td>
									</tr>
									<tr ng-repeat="d in rows">
										<td ng-show="d.id">
											<input type="checkbox" class="bulk_settings_manager_checkbox"
											       value="{{ d.id }}">
										</td>
										<td>
											{{ d.name }}
										</td>

										<td ng-show="d.id">
											{{ d.url }}
										</td>

										<td ng-show="d.id">
											Created: {{ d.created_time }}<br/>
											Edited: {{ d.edited_time }}
										</td>

										<td ng-show="d.id">
											<a ng-show="d.id" ng-click="enable_editing(d.id)"
											   href="<?php echo admin_url( 'admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager&id=' ); ?>{{ d.id }}"><i
													class="fa fa-pencil-square-o"></i> <?php _e( 'Edit', self::$plugin_translate ); ?>
											</a>
											|
											<a ng-show="d.id" ng-click="export_settings([d.id])"
											   style="cursor: pointer;"><i
													class="fa fa-upload"></i> <?php _e( 'Export', self::$plugin_translate ); ?>
											</a>
											|
											<a style="cursor: pointer;" ng-click="show_notes(d.id, d.name, 'entry')"><i
													class="fa fa-pencil"></i> <?php _e( 'Notes', 'mainwp' ); ?></a>
											|
											<a ng-show="d.id" ng-click="delete_settings(d.id, 'key')"
											   style="cursor: pointer; color: #a00;"><i
													class="fa fa-trash-o"></i> <?php _e( 'Delete', self::$plugin_translate ); ?>
											</a>
										</td>
									</tr>
								</table>
								<div tasty-pagination template-url="custom_table_template_key.html"></div>
							</div>

							<div
								style="width: 100%; text-align: right; border-top: 1px Dotted #e5e5e5; margin-top: 1em; padding-top: 1em;">
								<input type="button" class="button button-primary" ng-click="send_to_child('key')"
								       value="Save Key to Selected Child Sites" style="margin-left: 5px;">
								<input type="button" class="button" ng-click="delete_settings_bulk('key')"
								       value="Delete Selected Keys">
								<input type="button" class="button" ng-click="export_bulk()"
								       value="Export Selected Keys">
							</div>
							<div style="clear: both;"></div>
						</div>
					</div>
					<div style="clear: both;"></div>
				</div>
				<?php
				if ( self::$id > 0 ):
					?>
					<div ng-show="is_selected('edit')">
						<?php
						if ( empty( $entry ) ):
							?>
							<div
								class="error"><?php _e( 'This entry does not exist', self::$plugin_translate ); ?></div>
							<?php
						else:
							$args = json_decode( $entry->settings, true );
							if ( ! isset( $args['all_args'] ) ) {
								echo __( 'Missing all_args', self::$plugin_translate );

								return;
							}
							$args = $args['all_args'];
							?>
							<input type="hidden" id="mainwp_bulk_settings_manager_edit_id"
							       value="<?php echo esc_attr( self::$id ); ?>">

							<div class="mainwp-widget-liquid-right widget-liquid-right">
								<div>
									<div class="available-widgets widgets-holder-wrap inside">
										<div class="widget-holder">
											<div class="widget-list">
												<?php
												self::render_nonce_field();
												self::render_text_field();
												self::render_textarea_field();
												self::render_submit_field();
												self::render_selectbox_field( "", "", "checkbox", "" );
												self::render_search_field();
												self::render_selectbox_field( "", "", "radio", "" );
												?>
											</div>
										</div>
									</div>
								</div>
							</div>

							<form method="post" action="" id="mainwp_bulk_settings_manager_edit_form">
								<div class="widget-liquid-left mainwp-widget-liquid-left">
									<div class="single-sidebar">
										<div class="sidebars-column-1">
											<div class="widgets-holder-wrap inside">
												<?php
												if ( isset( $args[0] ) && isset( $args[0]['field_type'] ) && $args[0]['field_type'] == 'settings_field' ) {
													self::render_settings_field( $args[0]['settings_field_name'], $args[0]['settings_field_url'] );
												} else {
													echo 'Data Missmatch';

													return;
												}
												?>

												<div class="widgets-sortables" style="min-height: 100px"
												     id="left_widgets_list_<?php echo self::$id; ?>">
													<?php
													for ( $i = 1; $i < count( $args ); ++ $i ) {
														switch ( $args[ $i ]['field_type'] ) {
															case 'text_field':
																self::render_text_field( $args[ $i ]['text_field_description'], $args[ $i ]['text_field_name'], $args[ $i ]['text_field_value'], $args[ $i ]['text_field_type'] );
																break;

															case 'textarea_field':
																self::render_textarea_field( $args[ $i ]['textarea_field_description'], $args[ $i ]['textarea_field_name'], $args[ $i ]['textarea_field_value'], $args[ $i ]['textarea_field_type'] );
																break;

															case 'nonce_field':
																self::render_nonce_field( $args[ $i ]['nonce_field_name'], $args[ $i ]['nonce_field_arg'] );
																break;

															case 'submit_field':
																self::render_submit_field( $args[ $i ]['submit_field_name'], $args[ $i ]['submit_field_value'], $args[ $i ]['submit_field_type'] );
																break;

															case 'search_field':
																self::render_search_field( $args[ $i ]['search_field_ok'], $args[ $i ]['search_field_fail'] );
																break;

															case 'selectbox_field':
																self::render_selectbox_field( $args[ $i ]['selectbox_field_description'], $args[ $i ]['selectbox_field_name'], $args[ $i ]['selectbox_field_type'], $args[ $i ]['selectbox_field_type_send'], $args[ $i ]['fields'] );
																break;

															default:
																echo 'Invalid field type';
														}
													}
													?>
												</div>
											</div>
										</div>
										<br/>
										<input type="button" class="left button button-primary" name="sending"
										       value="Save Key" id="mainwp_bulk_settings_manager_edit_button">
										<input type="button" class="left button" value="Cancel"
										       ng-click="cancel_editing(<?php echo self::$id; ?>)">
										<input type="button" class="left button" value="Remove All Fields"
										       ng-click="remove_all_fields(<?php echo self::$id; ?>)">
										<input type="button" class="left button" value="Reset All Fields"
										       ng-click="reset_all_fields(<?php echo self::$id; ?>)">
									</div>
								</div>
							</form>

							<div class="widgets-chooser">
								<ul class="widgets-chooser-sidebars"></ul>
								<div class="widgets-chooser-actions">
								</div>
							</div>

							<?php
						endif;
						?>
					</div>
					<?php
				endif;
				?>
				<div ng-show="is_selected('add')">
					<input type="hidden" id="mainwp_bulk_settings_manager_add_new_id" value="0">

					<div
						class="mainwp_info-box"><?php _e( 'Making Keys by yourself can be tricky and may lead to unwanted issues. It is recommended to let the Key Maker plugin auto-create Keys for you. <a href="http://docs.mainwp.com/create-a-single-key/" target="_blank">Learn more</a>.', self::$plugin_translate ); ?></div>
					<div class="widget-liquid-right mainwp-widget-liquid-right">
						<div>
							<div class="available-widgets widgets-holder-wrap inside">
								<div><h2><?php _e( 'Available Fields', self::$plugin_translate ); ?></h2></div>
								<div class="sidebar-description"><p
										class="description"><?php _e( 'Use available fields to create your key. You can select a field by dragging it to the Key Fields section.', self::$plugin_translate ); ?></p>
								</div>
								<div class="widget-holder">
									<div class="widget-list">
										<?php
										self::render_nonce_field();
										self::render_text_field();
										self::render_textarea_field();
										self::render_submit_field();
										self::render_search_field();
										self::render_selectbox_field( "", "", "checkbox", "" );
										self::render_selectbox_field( "", "", "radio", "" );
										?>
									</div>
								</div>
							</div>
						</div>
					</div>

					<form method="post" action="" id="mainwp_bulk_settings_manager_add_new_form">
						<div class="widget-liquid-left mainwp-widget-liquid-left">
							<div class="single-sidebar">
								<div class="sidebars-column-1">
									<div class="widgets-holder-wrap inside">
										<div><h2><?php _e( 'Key Fields', self::$plugin_translate ); ?></h2></div>
										<div class="sidebar-description"><p
												class="description"><?php _e( 'Insert key fields by dragging them from the Available Fields section.', self::$plugin_translate ); ?></p>
										</div>
										<?php
										self::render_settings_field();
										?>

										<div class="widgets-sortables" style="min-height: 100px"
										     id="left_widgets_list_0">

										</div>
									</div>
								</div>
								<br/>
								<input type="button" class="left button button-primary" name="sending" value="Save Key"
								       id="mainwp_bulk_settings_manager_add_new_button">
								<input type="button" class="left button" value="Remove All Fields"
								       ng-click="remove_all_fields(0)">
								<input type="button" class="left button" value="Reset All Fields"
								       ng-click="reset_all_fields(0)">
							</div>
						</div>
					</form>

					<div class="widgets-chooser">
						<ul class="widgets-chooser-sidebars"></ul>
						<div class="widgets-chooser-actions">
						</div>
					</div>
				</div>

				<div ng-show="is_selected('history')">
					<div class="postbox">
						<h3 class="mainwp_box_title">
							<i class="fa fa-cog"></i> <?php _e( 'History', self::$plugin_translate ); ?>
							<span style="float: right;"><a href="javascrip:void(0)" ng-click="reload_history()"
							                               style="text-decoration: none;"><i
										class="fa fa-refresh"></i> <?php _e( 'Reload History', self::$plugin_translate ); ?>
								</a></span>
						</h3>

						<div class="inside">
							<div tasty-table bind-resource-callback="get_history" bind-init="init_get_history"
							     bind-theme="history_theme" bind-reload="reload_history_callback">

								<table class="wp-list-table widefat table">
									<thead tasty-thead not-sort-by="['actions']"></thead>
									<tr ng-if="rows.length==0">
										<td>Loading</td>
									</tr>
									<tr ng-repeat="d in rows">
										<td>
											{{ d.name }}
										</td>

										<td>
											{{ d.url }}
										</td>

										<td ng-show="d.id">
											Submission time: {{ d.created_time }}
										</td>

										<td ng-show="d.id">
											<a href="/?TB_inline&width=1200&height=auto&inlineId=bulk_settings_manager_preview"
											   style="text-decoration: none;" class="thickbox"
											   ng-click="preview(d.id, 1, d.secret)"><i class="fa fa-eye"></i> Review
												Changes</a>
											|
											<a href="/?TB_inline&width=1200&height=auto&inlineId=bulk_settings_manager_preview"
											   style="text-decoration: none;" class="thickbox"
											   ng-click="preview(d.id, 0, d.secret)"><i class="fa fa-eye"></i> Review
												Parameters</a>
										</td>
									</tr>
								</table>
								<div tasty-pagination template-url="custom_table_template_history.html"></div>
							</div>
						</div>
					</div>
				</div>

				<div ng-show="is_selected('settings')">
					<div class="postbox">
						<h3 class="mainwp_box_title"><i
								class="fa fa-cog"></i> <?php _e( 'Settings', self::$plugin_translate ); ?></h3>

						<div class="inside">
							<table class="form-table">
								<tbody>
								<tr>
									<th scope="row"><?php _e( 'Delay', self::$plugin_translate ); ?></th>
									<td>
										<input type="text" id="mainwp_bulk_settings_manager_interval"
										       value="<?php echo esc_attr( get_option( 'mainwp_bulk_settings_manager_interval', 5 ) ); ?>">
										<input type="submit" class="left button button-primary" value="Change"
										       ng-click="change_settings('interval')">
										<br>
										<em><?php _e( 'Allows you to set time delay between two submissions. For example, if you set delay to 5 seconds, and submit a key to 3 child sites, after the Key has been submitted to the first site, the extension will wait for 5 seconds before it proceeds to the next child site. This option helps you to reduce server load.', self::$plugin_translate ); ?></em>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Clear History', self::$plugin_translate ); ?></th>
									<td>
										<input type="submit" class="left button button-primary" value="Clear"
										       ng-click="change_settings('history')">
										<br>
										<em><?php _e( 'By clicking the button, you will delete all history data from the Bulk Settings Manager extension.', self::$plugin_translate ); ?></em>
									</td>
								</tr>

								<?php
								if ( defined( "MAINWP_BOILERPLATE_PLUGIN_FILE" ) ):
									?>
									<tr>
										<th scope="row"><?php _e( 'Use MainWP Boilerplate', self::$plugin_translate ); ?></th>
										<td>
											<div class="mainwp-checkbox">
												<input ng-click="change_settings('boilerplate')" type="checkbox"
												       name="mainwp_options_wp_cron"
												       id="mainwp_bulk_settings_manager_boilerplate_checkbox" <?php echo checked( get_option( 'mainwp_bulk_settings_manager_use_boilerplate', 0 ), 1 ); ?> >
												<label for="mainwp_bulk_settings_manager_boilerplate_checkbox"></label>
											</div>
										</td>
									</tr>
									<?php
								else:
									?>
									<tr>
										<th scope="row"><?php _e( 'Use MainWP Boilerplate', self::$plugin_translate ); ?></th>
										<td>
											<div>
												<?php _e( 'Bulk Settings Manager Extension integrates with the MainWP Boilerplate Extension. If the extension is installed and activated, you will be able to use boilerplate tokens in key fields.', self::$plugin_translate ); ?>
												<br>
												<a href="https://mainwp.com/extension/boilerplate/"
												   target="_blank"><?php _e( 'Order the MainWP Boilerplate Extension here!', self::$plugin_translate ); ?></a>
											</div>
										</td>
									</tr>
									<?php
								endif;

								if ( defined( "MAINWP_SPINNER_PLUGIN_FILE" ) ):
									?>
									<tr>
										<th scope="row"><?php _e( 'Use MainWP Spinner', self::$plugin_translate ); ?></th>
										<td>
											<div class="mainwp-checkbox">
												<input ng-click="change_settings('spinner')" type="checkbox"
												       name="mainwp_options_wp_cron"
												       id="mainwp_bulk_settings_manager_spinner_checkbox" <?php echo checked( get_option( 'mainwp_bulk_settings_manager_use_spinner', 0 ), 1 ); ?> >
												<label for="mainwp_bulk_settings_manager_spinner_checkbox"></label>
											</div>
										</td>
									</tr>
									<?php
								else:
									?>
									<tr>
										<th scope="row"><?php _e( 'Use MainWP Spinner', self::$plugin_translate ); ?></th>
										<td>
											<div>
												<?php _e( 'Bulk Settings Manager Extension integrates with the MainWP Spinner Extension. If the extension is installed and activated, you will be able to use spun values in key fields.', self::$plugin_translate ); ?>
												<br>
												<a href="https://mainwp.com/extension/spinner/"
												   target="_blank"><?php _e( 'Order the MainWP Spinner Extension here!', self::$plugin_translate ); ?></a>
											</div>
										</td>
									</tr>
									<?php
								endif;
								?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div ng-show="is_selected('import')">
					<div class="postbox">
						<h3 class="mainwp_box_title"><i
								class="fa fa-download"></i> <?php _e( 'Make a Key from MainWP Key Maker', self::$plugin_translate ); ?>
						</h3>

						<div class="inside">
							<table class="form-table">
								<tbody>
								<form method="post"
								      action="<?php echo admin_url( 'admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager' ); ?>"
								      enctype="multipart/form-data">
									<tr>
										<th scope="row">
											<?php _e( 'Key Name', self::$plugin_translate ); ?>
										</th>
										<td>
											<input type="text" name="import_name">
										</td>
									</tr>
									<tr>
										<th scope="row">
											<?php _e( 'Key Code', self::$plugin_translate ); ?>
										</th>
										<td>
											<textarea name="import" rows="10" cols="60"></textarea>
											<?php wp_nonce_field( MainWPBulkSettingsManager::$nonce_token . 'import' ); ?>
											<input type="hidden" name="import_text" value="1"><br/>
											<em><?php _e( 'Paste the code created by the MainWP Key Maker plugin to have your key auto-built', self::$plugin_translate ); ?></em><br/><br/>
											<input type="submit"
											       class="left button button-hero button-primary mainwp-upgrade-button"
											       value="Make the Key">
										</td>
									</tr>
								</form>
								</tbody>
							</table>
						</div>
					</div>
					<div class="postbox">
						<h3 class="mainwp_box_title"><i
								class="fa fa-download"></i> <?php _e( 'Import Keys from File', self::$plugin_translate ); ?>
						</h3>

						<div class="inside">
							<table class="form-table">
								<tbody>
								<form method="post"
								      action="<?php echo admin_url( 'admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager' ); ?>"
								      enctype="multipart/form-data">
									<tr>
										<th scope="row">
											<?php _e( 'Upload File', self::$plugin_translate ); ?>
										</th>
										<td>
											<input type="file" name="import">
											<?php wp_nonce_field( MainWPBulkSettingsManager::$nonce_token . 'import' ); ?>
											<input type="hidden" name="import_file" value="1">
											<input type="submit" class="left button button-primary" value="Import Key">
										</td>
									</tr>
								</form>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div id="mainwp_notes_overlay" class="mainwp_overlay"></div>
				<div id="mainwp_notes" class="mainwp_popup">
					<a id="mainwp_notes_closeX" class="mainwp_closeX" style="display: inline; "></a>

					<div id="mainwp_notes_title" class="mainwp_popup_title">{{ notes_title }}</span>
					</div>
					<div>
						<textarea style="width: 580px !important; height: 300px;" ng-model="notes_content"></textarea>
					</div>
					<form>
						<div style="float: right">{{ notes_status }}</div>
						<input type="button" class="button cont" ng-click="save_notes()" value="Save Note"/>
						<input type="button" class="button cont" ng-click="hide_notes()" value="Close"/>
					</form>
				</div>

				<div ng-show="is_selected('dashboard')||is_selected('keyring')">
					<div id="syncing_div" class="postbox" style="display: none;">
						<h3 class="mainwp_box_title"><?php _e( 'Syncing to Child sites...', self::$plugin_translate ); ?></h3>

						<h3 class="mainwp_box_title"><?php _e( 'Delay:', self::$plugin_translate ); ?> {{
							scope_syncing_delay }}</h3>

						<div class="inside">
							<div id="syncing_progress"></div>
							<span id="syncing_progress_text"><span id="syncing_current"></span> / <span
									id="syncing_total"></span> <?php _e( 'updated', self::$plugin_translate ); ?></span><br/><br/>

							<div style="border-bottom: 1px Dotted #e5e5e5;"></div>
							<div id="syncing_message">

							</div>
						</div>
					</div>
				</div>

				<?php
				self::render_pager( 'ring', __( 'Key Rings per page', self::$plugin_translate ) );
				self::render_pager( 'key', __( 'Items per page', self::$plugin_translate ) );
				self::render_pager( 'history', __( 'Records per page', self::$plugin_translate ) );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Custom pager for ng-table
	 **/
	public static function render_pager( $id, $name ) {
		?>
		<script type="text/ng-template" id="custom_table_template_<?php echo esc_attr( $id ); ?>.html">
			<div class="pager" id="pager">
				<a ng-click="page.get(1)" href=""><img
						src="<?php echo plugins_url( 'images/first.png', dirname( __FILE__ ) ); ?>"
						class="first"></a>
				<a ng-click="page.get(pagination.page-1)" href=""><img
						src="<?php echo plugins_url( 'images/prev.png', dirname( __FILE__ ) ); ?>"
						class="prev"></a>

				<input value="Page {{pagination.page}} of {{pagination.pages}}, {{pagination.size}} rows" type="text"
				       class="pagedisplay">

				<a ng-click="page.get(pagination.page+1)" href=""><img
						src="<?php echo plugins_url( 'images/next.png', dirname( __FILE__ ) ); ?>"
						class="next"></a>
				<a ng-click="page.get(pagination.pages)" href=""><img
						src="<?php echo plugins_url( 'images/last.png', dirname( __FILE__ ) ); ?>"
						class="last"></a>

				<span>&nbsp;&nbsp;<?php _e( 'Show:', self::$plugin_translate ); ?> </span>
				<select class="pagesize" ng-init="bulkSettingsManagerPageSelect=10"
				        ng-model="bulkSettingsManagerPageSelect"
				        ng-change="page.setCount(bulkSettingsManagerPageSelect)">
					<option value="10">10</option>
					<option value="25">25</option>
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="1000000000">All</option>
				</select>
				<span><?php echo esc_html( $name ); ?></span>
			</div>
			<div class="clear"></div>
		</script>
		<?php
	}

	/**
	 * Render tutorial
	 */
	public static function render_qsg() {
		$plugin_data       = get_plugin_data( MAINWP_BULK_SETTINGS_EXT_PLUGIN_FILE, false );
		$description       = $plugin_data['Description'];
		$extraHeaders      = array( 'DocumentationURI' => 'Documentation URI' );
		$file_data         = get_file_data( MAINWP_BULK_SETTINGS_EXT_PLUGIN_FILE, $extraHeaders );
		$documentation_url = $file_data['DocumentationURI'];
		?>
		<div class="mainwp_ext_info_box" id="cs-pth-notice-box">
			<div class="mainwp-ext-description"><?php echo $description; ?></div>
			<br/>
			<b><?php echo __( 'Need Help?' ); ?></b> <?php echo __( 'Review the Extension' ); ?> <a
				href="<?php echo $documentation_url; ?>" target="_blank"><i
					class="fa fa-book"></i> <?php echo __( 'Documentation' ); ?></a>.
			<a href="#" id="mainwp_bulk_settings_manager_quick_start_guide"><i
					class="fa fa-info-circle"></i> <?php _e( 'Show Quick Start Guide', 'mainwp' ); ?></a>
		</div>
		<div class="mainwp_ext_info_box" id="mainwp_skl_tips"
		     style="color: #333!important; text-shadow: none!important;">
            <span><a href="#" class="mainwp_show_tut" number="1"><i
			            class="fa fa-book"></i> <?php _e( 'Create a Single Key', self::$plugin_translate ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a
		            href="#" class="mainwp_show_tut" number="2"><i
			            class="fa fa-book"></i> <?php _e( 'Add Key to a Key Ring', self::$plugin_translate ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a
		            href="#" class="mainwp_show_tut" number="3"><i
			            class="fa fa-book"></i> <?php _e( 'Import Keys', self::$plugin_translate ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a
		            href="#" class="mainwp_show_tut" number="4"><i
			            class="fa fa-book"></i> <?php _e( 'Export Keys', self::$plugin_translate ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a
		            href="#" class="mainwp_show_tut" number="5"><i
			            class="fa fa-book"></i> <?php _e( 'Settings', self::$plugin_translate ) ?></a>
            </span><span><a href="#" id="mainwp_bulk_settings_manager_tips_dismiss" style="float: right;"><i
						class="fa fa-times-circle"></i> <?php _e( 'Dismiss', 'mainwp' ); ?></a></span>

			<div class="clear"></div>
			<div id="mainwp_skl_tuts">
				<div class="mainwp_skl_tut" number="1">
					<h3>Create a Single Key</h3>

					<p>The Bulk Settings Manager Extension works in conjunction with the all new MainWP Key Maker plugin
						to
						record your form settings and make a “key”. Once you have the “key”, you save it into your
						Bulk Settings Manager Extension. From this point you can submit your settings to child sites or
						if
						needed, you can update your settings and submit to child sites after that.</p>
					<ol>
						<li>Install the MainWP Key Maker plugin on a site that has a Theme or Plugin with settings you
							want share across your Child Sites.
						</li>
						<li>Go to a settings page of plugin, theme or one of the WordPress settings pages that you want
							to copy to all your Child sites and Submit that page so the form is passed. This allows Key
							Maker to record your submission and make the “key”;
						</li>
						<li>Press the “MainWP Key Maker” button listed at the top of your screen and select
							“Post-Submission Request” field and copy to clipboard;
						</li>
						<li>Go to your MainWP Dashboard Bulk Settings Manager Extension and Select Import Keys from Key
							Maker;
						</li>
						<li>Paste that copied code in the Key Code field, give your Key a name you’ll remember and click
							the Make the Key button;
						</li>
						<li>After you make the Key, you will be redirected to the Edit Key sceen, where you can verify
							the form fields look correct for you.
						</li>
						<li>Once it’s verified, click the Save Key button.</li>
						<li>That is it! Your key is ready to be submitted to your Child Sites.</li>
					</ol>
				</div>
				<div class="mainwp_skl_tut" number="2">
					<h3>Add Key to a Key Ring</h3>

					<p>Key Ring feature allows you to group your Keys and submit them all at once. It comes pretty handy
						in case one Plugin or Theme has multiple settings forms. After you create all Keys for a plugin
						or theme, you can group them in a single Key Rings that allows you to mange them more
						efficiently.</p>
					<ol>
						<li>Go to the MainWP > Extensions > Bulk Settings Manager > Single Keys page,</li>
						<li>Locate the Key that you want to add to a Key Ring and click the Edit link,</li>
						<li>On the Edit screen, in the Key Settings box, you can find the Key Ring options,</li>
						<li>You can crate a new Key Ring for your Key,</li>
						<li>Or assign it to a previously made Key Rings.</li>
						<li>When you are done, click the Save Key button on the bottom of the page</li>
					</ol>
				</div>
				<div class="mainwp_skl_tut" number="3">
					<h3>Import Keys</h3>

					<p>MainWP Bulk Settings Manager Extension allows you to import Keys in two ways:</p>
					<h4>Make (Import) a Key from MainWP Key Maker</h4>

					<p>Go to the Import Keys from Key Maker tab</p>
					<ol>
						<li>In the Key Name field, enter a name for your Key</li>
						<li>In the Key Code field, paste the code you have copied from the MainWP Key Maker plugin</li>
						<li>Click the Make the Key button.</li>
					</ol>
					<p>After the Key is made (imported) you will be redirected to the Edit Key screen were you need to
						verify that all fields and values have been imported correctly. Once you are done, click the
						Save Key button.</p>
					<h4>Import Keys from File</h4>

					<p>Go to the Import Keys from Key Maker tab</p>
					<ol>
						<li>If you have wanted Keys exported as a TXT document, you can import them to your Bulk
							Settings Manager
							extension simply by uploading the file in the Import Keys from File box.
						</li>
						<li>Upload the file and click the Import Keys button.</li>
					</ol>
				</div>
				<div class="mainwp_skl_tut" number="4">
					<h3>Export Keys</h3>
					<h4>Export Single Key</h4>
					<ol>
						<li>Go to the MainWP > Extensions > Bulk Settings Manager > Single Keys page,</li>
						<li>Locate the wanted Key in the table,</li>
						<li>Click the Export link.</li>
					</ol>
					<h4>Export Multiple Keys</h4>
					<ol>
						<li>Go to the MainWP > Extensions > Bulk Settings Manager > Single Keys page,</li>
						<li>Select wanted Keys,</li>
						<li>Click the Export Selected Keys button.</li>
					</ol>
				</div>
				<div class="mainwp_skl_tut" number="5">
					<h3>Bulk Settings Manager Key Settings</h3>

					<p><strong>Delay</strong> – allows you to set time delay between two submissions. For example, if
						you set delay to 5 seconds, and submit a key to 3 child sites, after the Key has been submitted
						to the first site, the extension will wait for 5 seconds before it proceeds to the next child
						site. This option helps you to reduce server load.</p>

					<p><strong>Clear History</strong> – by clicking the button, you will delete all history data from
						the Bulk Settings Manager extension.</p>

					<p><strong>Use Spinner</strong> – if enabled, the Bulk Settings Manager extension will be able to
						use spun
						text (values) in the following format {value1|value2}. Note that this option works only in case
						the MainWP Spinner Extension has been installed and activated on your MainWP Dashboard.</p>

					<p><strong>Use Boilerplate</strong> – if enabled, you will be able to use Boilerplate tokens as key
						field values. Note that this option works only in case the MainWP Boilerplate Extension has been
						installed and activated on your MainWP Dashboard.</p>
				</div>
			</div>
		</div>
		<?php
	}


}
