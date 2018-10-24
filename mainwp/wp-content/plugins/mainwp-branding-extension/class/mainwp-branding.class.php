<?php

class MainWP_Branding {
	public static $instance = null;
	public static $default_opts = array();
	protected $option_handle = 'mainwp_child_branding_options';
	protected $option;

	public function __construct() {
		$this->option       = get_option( $this->option_handle );
		self::$default_opts = array(
			'textbox_id'    => array(
				'mainwp_branding_plugin_name'           => '',
				'mainwp_branding_plugin_desc'           => '',
				'mainwp_branding_plugin_uri'            => '',
				'mainwp_branding_plugin_author'         => '',
				'mainwp_branding_plugin_author_uri'     => '',
				'mainwp_branding_site_generator'        => '',
				'mainwp_branding_site_generator_link'   => '',
				'mainwp_branding_texts_add_value'       => '',
				'mainwp_branding_texts_add_replace'     => '',
				'mainwp_branding_button_contact_label'  => __( 'Contact Support', 'mainwp-branding' ),
				'mainwp_branding_submit_button_title'   => __( 'Submit', 'mainwp-branding' ),
				'mainwp_branding_send_email_message'    => __( 'Your Message was successfully submitted', 'mainwp-branding' ),
				'mainwp_branding_message_return_sender' => __( 'Go back to previous page', 'mainwp-branding' ),
				'mainwp_branding_support_email'         => '',
			),
			'textbox_class' => array(
				'mainwp_branding_texts_value' => '',
				//"mainwp_branding_texts_replace" => ""
			),
			'textareas'     => array(
				'mainwp_branding_admin_css' => '',
				'mainwp_branding_login_css' => '',

			),
			'tinyMCEs'      => array(
				'mainwp_branding_global_footer'    => '',
				'mainwp_branding_dashboard_footer' => '',
				'mainwp_branding_support_message'  => __( 'Welcome to Support', 'mainwp-branding' ),
			),
			'checkboxes'    => array(
				'mainwp_branding_site_disable_wp_branding'        => false,
				'mainwp_branding_site_override'                   => false,
				'mainwp_branding_preserve_branding'               => false,
				'mainwp_branding_hide_child_plugin'               => false,
				'mainwp_branding_disable_change'                  => false,
				'mainwp_branding_remove_restore_clone'            => false,
				'mainwp_branding_remove_permalink'                => false,
				'mainwp_branding_remove_mainwp_setting'           => false,
				'mainwp_branding_remove_mainwp_server_info'       => false,
				'mainwp_branding_remove_wp_tools'                 => false,
				'mainwp_branding_remove_wp_setting'               => false,
				'mainwp_branding_delete_login_image'              => false,
				'mainwp_branding_delete_favico_image'             => false,
				'mainwp_branding_remove_widget_welcome'           => false,
				'mainwp_branding_remove_widget_glance'            => false,
				'mainwp_branding_remove_widget_activity'          => false,
				'mainwp_branding_remove_widget_quick'             => false,
				'mainwp_branding_remove_widget_news'              => false,
				'mainwp_branding_hide_nag_update'                 => false,
				'mainwp_branding_hide_screen_options'             => false,
				'mainwp_branding_hide_help_box'                   => false,
				'mainwp_branding_hide_metabox_post_excerpt'       => false,
				'mainwp_branding_hide_metabox_post_slug'          => false,
				'mainwp_branding_hide_metabox_post_tags'          => false,
				'mainwp_branding_hide_metabox_post_author'        => false,
				'mainwp_branding_hide_metabox_post_comments'      => false,
				'mainwp_branding_hide_metabox_post_revisions'     => false,
				'mainwp_branding_hide_metabox_post_discussion'    => false,
				'mainwp_branding_hide_metabox_post_categories'    => false,
				'mainwp_branding_hide_metabox_post_custom_fields' => false,
				'mainwp_branding_hide_metabox_post_trackbacks'    => false,
				'mainwp_branding_hide_metabox_page_custom_fields' => false,
				'mainwp_branding_hide_metabox_page_author'        => false,
				'mainwp_branding_hide_metabox_page_discussion'    => false,
				'mainwp_branding_hide_metabox_page_revisions'     => false,
				'mainwp_branding_hide_metabox_page_attributes'    => false,
				'mainwp_branding_hide_metabox_page_slug'          => false,
				'mainwp_branding_show_support_button'             => false,
				'mainwp_branding_button_in_top_admin_bar'         => true,
				'mainwp_branding_button_in_admin_menu'            => true,
			),
		);
	}

	public static function render() {

		$output = MainWP_Branding::handle_settings_post();
		$result = null;
		if ( false !== $output ) {
			?>
			<div id="ajax-information-zone" class="updated">
			<p><?php _e( 'Your settings have been saved.', 'mainwp-branding' ); ?></p></div> <?php
			if ( isset( $output['error'] ) && is_array( $output['error'] ) && count( $output['error'] ) > 0 ) {
				?>
				<div class="mainwp_error error"><?php echo implode( '<br />', $output['error'] ); ?></div>
				<?php
			}
			$result = self::apply_settings();
			if ( 'NOCHILDSITE' !== $result ) {
				return;
			}
		}

		if ( 'NOCHILDSITE' === $result ) {
			?>
			<div class="mainwp_info-box-yellow"><?php _e( 'No child sites found.', 'mainwp-branding' ); ?></div>
		<?php } ?>

		<form method="post" enctype="multipart/form-data" action="admin.php?page=Extensions-Mainwp-Branding-Extension"
		      id="mainwp-branding-settings-page-form">
			<?php
			self::render_settings();
			?>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button-primary"
				       value="<?php _e( 'Save Settings', 'mainwp-branding' ); ?>"/>
				<input type="button" class="button-primary mwp_branding_reset_btn"
				       value="<?php _e( 'Reset', 'mainwp' ); ?>"/>

			</p>
		</form>
		<?php
	}

	public static function apply_settings() {
		global $mainWPBrandingExtensionActivator;
		$dbwebsites = apply_filters( 'mainwp-getsites', $mainWPBrandingExtensionActivator->get_child_file(), $mainWPBrandingExtensionActivator->get_child_key(), null );
		if ( is_array( $dbwebsites ) && count( $dbwebsites ) > 0 ) {
			echo '<h3>' . __( 'Saving Settings to child sites..', 'mainwp-branding' ) . '</h3>';
			foreach ( $dbwebsites as $website ) {
				echo '<div><strong>' . stripslashes( $website['name'] ) . '</strong>: ';
				echo '<span class="mainwpBrandingSitesItem" siteid="' . $website['id'] . '" status="queue"><span class="status">Queue</span><br />';
				echo '<span class="detail"></span>';
				echo '</div><br />';
			}
			?>
			<div id="mainwp_branding_apply_setting_ajax_message_zone"
			     class="mainwp_info-box-yellow hidden"><?php _e( 'No child sites found.', 'mainwp-branding' ); ?></div>
			<script>
				jQuery(document).ready(function ($) {
					mainwp_branding_start_next();
				})
			</script>
			<?php
			return true;
		} else {
			return 'NOCHILDSITE';
		}
	}

	public function init() {
		add_action( 'wp_ajax_mainwp_branding_performbrandingchildplugin', array(
			$this,
			'perform_branding_child_plugin',
		) );
		add_action( 'mainwp-extension-sites-edit', array( &$this, 'branding_site_edit' ), 9, 1 );
		add_action( 'mainwp_update_site', array( &$this, 'branding_update_site' ), 8, 1 );
	}

	public function get_option( $key = null, $default = '' ) {
		if ( isset( $this->option[ $key ] ) ) {
			return $this->option[ $key ];
		}

		return $default;
	}

	public function set_option( $key, $value ) {
		$this->option[ $key ] = $value;

		return update_option( $this->option_handle, $this->option );
	}

	public function branding_site_edit( $website ) {
		self::render_settings( $website, true );
	}

	public static function render_settings( $website = null, $site_edit = false ) {

		$pluginName          = $pluginDesc = $pluginAuthor = $pluginAuthorURI = $pluginURI = $supportEmail = $supportMessage = '';
		$submitButtonTitle   = $returnMessage = $globalFooter = $dashboardFooter = '';
		$pluginHide          = $disableChanges = $showButton = $override = $branding_id = $removePermalinks = $preserve = 0;
		$site_branding       = null;
		$disableWPBranding   = $hideNag = $hideScreenOpts = $hideHelpBox = 0;
		$showButtonIn        = 1;
		$removeWidgetWelcome = $removeWidgetGlance = $removeWidgetAct = $removeWidgetQuick = $removeWidgetNews = 0;
		$siteGenerator       = $generatorLink = $adminCss = $loginCss = '';
		$textsReplace        = array();
		$hidePostExcerpt     = $hidePostSlug = $hidePostTags = $hidePostAuthor = $hidePostComments = $hidePostRevisions = $hidePostDiscussion = 0;
		$hidePostCategories  = $hidePostFields = $hidePostTrackbacks = 0;
		$hidePageFields      = $hidePageAuthor = $hidePageDiscussion = $hidePageRevisions = $hidePageAttributes = $hidePageSlug = 0;
		$removeRestore       = $removeSetting = $removeServerInfo = $removeWPTools = $removeWPSetting = $imageFavico = 0;
		if ( ! $site_edit ) {
			$pluginName          = self::get_instance()->get_option( 'child_plugin_name' );
			$pluginDesc          = self::get_instance()->get_option( 'child_plugin_desc' );
			$pluginAuthor        = self::get_instance()->get_option( 'child_plugin_author' );
			$pluginAuthorURI     = self::get_instance()->get_option( 'child_plugin_author_uri' );
			$pluginHide          = self::get_instance()->get_option( 'child_plugin_hide' );
			$disableChanges      = self::get_instance()->get_option( 'child_disable_change' );
			$showButton          = self::get_instance()->get_option( 'child_show_support_button' );
			$showButtonIn        = self::get_instance()->get_option( 'child_show_support_button_in' );
			$supportEmail        = self::get_instance()->get_option( 'child_support_email' );
			$supportMessage      = self::get_instance()->get_option( 'child_support_message' );
			$removeRestore       = self::get_instance()->get_option( 'child_remove_restore' );
			$removeSetting       = self::get_instance()->get_option( 'child_remove_setting' );
			$removeServerInfo    = self::get_instance()->get_option( 'child_remove_server_info' );
			$removeWPTools       = self::get_instance()->get_option( 'child_remove_wp_tools' );
			$removeWPSetting     = self::get_instance()->get_option( 'child_remove_wp_setting' );
			$pluginURI           = self::get_instance()->get_option( 'child_plugin_uri' );
			$buttonLabel         = self::get_instance()->get_option( 'child_button_contact_label' );
			$sendMessage         = self::get_instance()->get_option( 'child_send_email_message' );
			$submitButtonTitle   = self::get_instance()->get_option( 'child_submit_button_title' );
			$returnMessage       = self::get_instance()->get_option( 'child_message_return_sender' );
			$removePermalinks    = self::get_instance()->get_option( 'child_remove_permalink' );
			$globalFooter        = self::get_instance()->get_option( 'child_global_footer' );
			$dashboardFooter     = self::get_instance()->get_option( 'child_dashboard_footer' );
			$removeWidgetWelcome = self::get_instance()->get_option( 'child_remove_widget_welcome' );
			$removeWidgetGlance  = self::get_instance()->get_option( 'child_remove_widget_glance' );
			$removeWidgetAct     = self::get_instance()->get_option( 'child_remove_widget_activity' );
			$removeWidgetQuick   = self::get_instance()->get_option( 'child_remove_widget_quick' );
			$removeWidgetNews    = self::get_instance()->get_option( 'child_remove_widget_news' );
			$siteGenerator       = self::get_instance()->get_option( 'child_site_generator' );
			$generatorLink       = self::get_instance()->get_option( 'child_generator_link' );
			$adminCss            = self::get_instance()->get_option( 'child_admin_css' );
			$loginCss            = self::get_instance()->get_option( 'child_login_css' );
			$textsReplace        = self::get_instance()->get_option( 'child_texts_replace' );
			$imageLogin          = self::get_instance()->get_option( 'child_login_image' );
			$imageFavico         = self::get_instance()->get_option( 'child_favico_image' );
			$hideNag             = self::get_instance()->get_option( 'child_hide_nag', 0 );
			$hideScreenOpts      = self::get_instance()->get_option( 'child_hide_screen_opts', 0 );
			$hideHelpBox         = self::get_instance()->get_option( 'child_hide_help_box', 0 );

			$hidePostExcerpt  = self::get_instance()->get_option( 'child_hide_metabox_post_excerpt', 0 );
			$hidePostSlug     = self::get_instance()->get_option( 'child_hide_metabox_post_slug', 0 );
			$hidePostTags     = self::get_instance()->get_option( 'child_hide_metabox_post_tags', 0 );
			$hidePostAuthor   = self::get_instance()->get_option( 'child_hide_metabox_post_author', 0 );
			$hidePostComments = self::get_instance()->get_option( 'child_hide_metabox_post_comments', 0 );

			$hidePostRevisions  = self::get_instance()->get_option( 'child_hide_metabox_post_revisions', 0 );
			$hidePostDiscussion = self::get_instance()->get_option( 'child_hide_metabox_post_discussion', 0 );
			$hidePostCategories = self::get_instance()->get_option( 'child_hide_metabox_post_categories', 0 );
			$hidePostFields     = self::get_instance()->get_option( 'child_hide_metabox_post_custom_fields', 0 );
			$hidePostTrackbacks = self::get_instance()->get_option( 'child_hide_metabox_post_trackbacks', 0 );

			$hidePageFields     = self::get_instance()->get_option( 'child_hide_metabox_page_custom_fields', 0 );
			$hidePageAuthor     = self::get_instance()->get_option( 'child_hide_metabox_page_author', 0 );
			$hidePageDiscussion = self::get_instance()->get_option( 'child_hide_metabox_page_discussion', 0 );
			$hidePageRevisions  = self::get_instance()->get_option( 'child_hide_metabox_page_revisions', 0 );
			$hidePageAttributes = self::get_instance()->get_option( 'child_hide_metabox_page_attributes', 0 );
			$hidePageSlug       = self::get_instance()->get_option( 'child_hide_metabox_page_slug', 0 );

			$preserve = self::get_instance()->get_option( 'child_preserve_branding' );
		} else if ( is_object( $website ) ) {
			$site_branding = MainWP_Branding_DB::get_instance()->get_branding_by( 'site_url', $website->url );
		}

		if ( $site_edit && $site_branding ) {
			$header = unserialize( $site_branding->plugin_header );
			if ( is_array( $header ) ) {
				$pluginName      = $header['plugin_name'];
				$pluginDesc      = $header['plugin_desc'];
				$pluginAuthor    = $header['plugin_author'];
				$pluginAuthorURI = $header['author_uri'];
				$pluginURI       = $header['plugin_uri'];
			}
			$pluginHide       = $site_branding->hide_child_plugin;
			$disableChanges   = $site_branding->disable_theme_plugin_change;
			$showButton       = $site_branding->show_support_button;
			$supportEmail     = $site_branding->support_email;
			$supportMessage   = $site_branding->support_message;
			$removeRestore    = $site_branding->remove_restore;
			$removeSetting    = $site_branding->remove_setting;
			$removeServerInfo = $site_branding->remove_server_info;
			$removeWPTools    = $site_branding->remove_wp_tools;
			$removeWPSetting  = $site_branding->remove_wp_setting;
			$buttonLabel      = $site_branding->button_contact_label;
			$sendMessage      = $site_branding->send_email_message;
			$override         = $site_branding->override;
			$branding_id      = $site_branding->id;
			$extra_settings   = unserialize( $site_branding->extra_settings );
			if ( is_array( $extra_settings ) ) {
				$submitButtonTitle   = isset( $extra_settings['submit_button_title'] ) ? $extra_settings['submit_button_title'] : '';
				$returnMessage       = isset( $extra_settings['message_return_sender'] ) ? $extra_settings['message_return_sender'] : '';
				$removePermalinks    = isset( $extra_settings['remove_permalink'] ) ? $extra_settings['remove_permalink'] : 0;
				$showButtonIn        = isset( $extra_settings['show_button_in'] ) ? $extra_settings['show_button_in'] : 1;
				$disableWPBranding   = isset( $extra_settings['disable_wp_branding'] ) ? $extra_settings['disable_wp_branding'] : 0;
				$globalFooter        = isset( $extra_settings['global_footer'] ) ? $extra_settings['global_footer'] : '';
				$dashboardFooter     = isset( $extra_settings['dashboard_footer'] ) ? $extra_settings['dashboard_footer'] : '';
				$removeWidgetWelcome = isset( $extra_settings['remove_widget_welcome'] ) ? $extra_settings['remove_widget_welcome'] : 0;
				$removeWidgetGlance  = isset( $extra_settings['remove_widget_glance'] ) ? $extra_settings['remove_widget_glance'] : 0;
				$removeWidgetAct     = isset( $extra_settings['remove_widget_activity'] ) ? $extra_settings['remove_widget_activity'] : 0;
				$removeWidgetQuick   = isset( $extra_settings['remove_widget_quick'] ) ? $extra_settings['remove_widget_quick'] : 0;
				$removeWidgetNews    = isset( $extra_settings['remove_widget_news'] ) ? $extra_settings['remove_widget_news'] : 0;
				$siteGenerator       = isset( $extra_settings['site_generator'] ) ? $extra_settings['site_generator'] : '';
				$generatorLink       = isset( $extra_settings['generator_link'] ) ? $extra_settings['generator_link'] : '';
				$adminCss            = isset( $extra_settings['admin_css'] ) ? $extra_settings['admin_css'] : '';
				$loginCss            = isset( $extra_settings['login_css'] ) ? $extra_settings['login_css'] : '';
				$textsReplace        = isset( $extra_settings['texts_replace'] ) ? $extra_settings['texts_replace'] : array();
				$imageLogin          = isset( $extra_settings['login_image'] ) ? $extra_settings['login_image'] : '';
				$imageFavico         = isset( $extra_settings['favico_image'] ) ? $extra_settings['favico_image'] : '';
				$hideNag             = isset( $extra_settings['hide_nag'] ) ? $extra_settings['hide_nag'] : 0;
				$hideScreenOpts      = isset( $extra_settings['hide_screen_opts'] ) ? $extra_settings['hide_screen_opts'] : 0;
				$hideHelpBox         = isset( $extra_settings['hide_help_box'] ) ? $extra_settings['hide_help_box'] : 0;

				$hidePostExcerpt  = isset( $extra_settings['hide_metabox_post_excerpt'] ) ? $extra_settings['hide_metabox_post_excerpt'] : 0;
				$hidePostSlug     = isset( $extra_settings['hide_metabox_post_slug'] ) ? $extra_settings['hide_metabox_post_slug'] : 0;
				$hidePostTags     = isset( $extra_settings['hide_metabox_post_tags'] ) ? $extra_settings['hide_metabox_post_tags'] : 0;
				$hidePostAuthor   = isset( $extra_settings['hide_metabox_post_author'] ) ? $extra_settings['hide_metabox_post_author'] : 0;
				$hidePostComments = isset( $extra_settings['hide_metabox_post_comments'] ) ? $extra_settings['hide_metabox_post_comments'] : 0;

				$hidePostRevisions  = isset( $extra_settings['hide_metabox_post_revisions'] ) ? $extra_settings['hide_metabox_post_revisions'] : 0;
				$hidePostDiscussion = isset( $extra_settings['hide_metabox_post_discussion'] ) ? $extra_settings['hide_metabox_post_discussion'] : 0;
				$hidePostCategories = isset( $extra_settings['hide_metabox_post_categories'] ) ? $extra_settings['hide_metabox_post_categories'] : 0;
				$hidePostFields     = isset( $extra_settings['hide_metabox_post_custom_fields'] ) ? $extra_settings['hide_metabox_post_custom_fields'] : 0;
				$hidePostTrackbacks = isset( $extra_settings['hide_metabox_post_trackbacks'] ) ? $extra_settings['hide_metabox_post_trackbacks'] : 0;

				$hidePageFields     = isset( $extra_settings['hide_metabox_page_custom_fields'] ) ? $extra_settings['hide_metabox_page_custom_fields'] : 0;
				$hidePageAuthor     = isset( $extra_settings['hide_metabox_page_author'] ) ? $extra_settings['hide_metabox_page_author'] : 0;
				$hidePageDiscussion = isset( $extra_settings['hide_metabox_page_discussion'] ) ? $extra_settings['hide_metabox_page_discussion'] : 0;
				$hidePageRevisions  = isset( $extra_settings['hide_metabox_page_revisions'] ) ? $extra_settings['hide_metabox_page_revisions'] : 0;
				$hidePageAttributes = isset( $extra_settings['hide_metabox_page_attributes'] ) ? $extra_settings['hide_metabox_page_attributes'] : 0;
				$hidePageSlug       = isset( $extra_settings['hide_metabox_page_slug'] ) ? $extra_settings['hide_metabox_page_slug'] : 0;
				$preserve           = $extra_settings['preserve_branding'];
			}
		}

		if ( is_array( $textsReplace ) && count( $textsReplace ) > 0 ) {
			ksort( $textsReplace );
		} else {
			$textsReplace = array();
		}

		if ( empty( $buttonLabel ) ) {
			$buttonLabel = __( 'Contact Support', 'mainwp-branding' );
		}

		if ( empty( $supportMessage ) ) {
			$supportMessage = __( 'Welcome to Support', 'mainwp-branding' );
		}

		if ( empty( $submitButtonTitle ) ) {
			$submitButtonTitle = __( 'Submit', 'mainwp-branding' );
		}

		if ( empty( $sendMessage ) ) {
			$sendMessage = __( 'Your Message was successfully submitted', 'mainwp-branding' );
		}

		if ( empty( $returnMessage ) ) {
			$returnMessage = __( 'Go back to previous page', 'mainwp-branding' );
		}
		$preserve_desc = '<span class="description">If set to "Yes", in case your child site gets disconnected, your custom branding will stay preserved. If set to "No", in case your child site gets disconnected, custom branding will be disabled and the child site will be returned to original state.</span>';
		?>

		<?php
		$plugin_data       = get_plugin_data( MAINWP_BRANDING_PLUGIN_FILE, false );
		$description       = $plugin_data['Description'];
		$extraHeaders      = array( 'DocumentationURI' => 'Documentation URI' );
		$file_data         = get_file_data( MAINWP_BRANDING_PLUGIN_FILE, $extraHeaders );
		$documentation_url = $file_data['DocumentationURI'];
		?>
		<div class="mainwp_ext_info_box">
			<div class="mainwp-ext-description"><?php echo $description; ?></div>
			<br/>
			<b><?php echo __( 'Need Help?' ); ?></b> <?php echo __( 'Review the Extension' ); ?> <a
				href="<?php echo $documentation_url; ?>" target="_blank"><i
					class="fa fa-book"></i> <?php echo __( 'Documentation' ); ?></a>.
		</div>

		<?php if ( $site_edit ) {
			if ( get_option( 'mainwp_branding_need_to_update_site' ) && $website->id && $branding_id ) {
				delete_option( 'mainwp_branding_need_to_update_site' );
				?>
				<script>
					jQuery(document).ready(function ($) {
						mainwp_branding_update_specical_site(<?php echo $website->id . ', ' . $branding_id . ', ' . $override; ?>);
					})
				</script>
				<?php
			}
			?>
			<div class="postbox mainwp_branding_postbox" section="0">
				<div class="handlediv"><br/></div>
				<h3 class="mainwp_box_title"><span>Child Plugin Branding Settings</span></h3>

				<div class="inside">
					<div id="mainwp_branding_edit_site_ajax_message_zone" class="mainwp_info-box-yellow hidden"></div>
					<table class="form-table">
						<tbody>
						<tr>
							<th scope="row"><?php _e( 'Disable Wordpress Branding', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set to YES if you want to Disable Wordpress Branding on the Child Site.', 'mainwp-branding' ) ); ?></th>
							<td>
								<div class="mainwp-checkbox">
									<input type="checkbox" id="mainwp_branding_site_disable_wp_branding"
									       name="mainwp_branding_site_disable_wp_branding" <?php echo( 0 == $disableWPBranding ? '' : 'checked="checked"' ); ?>
									       value="1"/>
									<label for="mainwp_branding_site_disable_wp_branding"></label>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Override General Settings', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set to YES if you want to overwrite global branding options.', 'mainwp-branding' ) ); ?></th>
							<td>
								<div class="mainwp-checkbox">
									<input type="checkbox" id="mainwp_branding_site_override"
									       name="mainwp_branding_site_override" <?php echo( 0 == $override ? '' : 'checked="checked"' ); ?>
									       value="1"/>
									<label for="mainwp_branding_site_override"></label>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Reset Branding Options', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Click the Reset button to return branding options to default state. After resetting click the Save Settings button at the bottom of the page.', 'mainwp-branding' ) ); ?></th>
							<td>
								<input type="button" class="button-primary mwp_branding_reset_btn"
								       value="<?php _e( 'Reset', 'mainwp' ); ?>"/>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Preserve Branding if Child Site is Disconnected', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set to YES if you want to preserve Branding when Child Site is Disconnected.', 'mainwp-branding' ) ); ?></th>
							<td>
								<div class="mainwp-checkbox">
									<input type="checkbox" id="mainwp_branding_preserve_branding"
									       name="mainwp_branding_preserve_branding" <?php echo( 0 == $preserve ? '' : 'checked="checked"' ); ?>
									       value="1"/>
									<label for="mainwp_branding_preserve_branding"></label>
								</div>
								<?php echo $preserve_desc; ?>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>

			<input type="hidden" name="mainwp_branding_site_branding_id" id="mainwp_branding_site_branding_id"
			       value="<?php echo $branding_id; ?>"/>
		<?php } ?>
		<script type="text/javascript">
			var mainwpBrandingDefaultOpts = <?php echo json_encode( self::$default_opts ); ?>;
		</script>
		<?php if ( ! $site_edit ) { ?>
			<div class="postbox mainwp_branding_postbox" section="5">
				<div class="handlediv"><br/></div>
				<h3 class="mainwp_box_title"><span><?php _e( 'Branding Settings', 'mainwp-branding' ); ?></span></h3>

				<div class="inside">
					<table class="form-table">
						<tbody>
						<tr>
							<th scope="row">
								<?php _e( 'Preserve Branding if Child Site is Disconnected', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set to YES if you want to preserve Branding when Child Site is Disconnected.', 'mainwp-branding' ) ); ?></th>
							<td>
								<div class="mainwp-checkbox">
									<input type="checkbox" id="mainwp_branding_preserve_branding"
									       name="mainwp_branding_preserve_branding" <?php echo( 0 == $preserve ? '' : 'checked="checked"' ); ?>
									       value="1"/>
									<label for="mainwp_branding_preserve_branding"></label>
								</div>
								<?php echo $preserve_desc; ?>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		<?php } ?>

		<div class="postbox mainwp_branding_postbox" section="1">
			<div class="handlediv"><br/></div>
			<h3 class="mainwp_box_title"><span><?php _e( 'Branding Options', 'mainwp-branding' ); ?></span></h3>

			<div class="inside">
				<div
					class="mainwp_info-box"><?php _e( 'This section allows you to change the MainWP Child plugins name, description and more to match your branding strategy.  You can also visually hide the Child plugin all together so your client does not see it at all.', 'mainwp-branding' ); ?></div>
				<div class="mainwp-branding-tut-box mainwp_ext_info_box">
					<a href="#" class="mainwp-branding-tut-link"><i
							class="fa fa-info-circle"></i> <?php _e( 'Show Quick Start Guide', 'mainwp' ); ?></a>
					<span><a href="#" class="mainwp-branding-tut-dismiss" style="float: right; display: none;"><i
								class="fa fa-times-circle"></i> <?php _e( 'Dismiss', 'mainwp' ); ?></a></span>

					<div class="mainwp-branding-tut-content" style="display: none;">
						<h3>Rebrand MainWP Child Plugin</h3>

						<p>
						<ol>
							<li>Enter a custom Plugin Name;</li>
							<li>Enter a custom Plugin Description;</li>
							<li>Enter a custom Plugin URL;</li>
							<li>Enter a custom Plugin Author;</li>
							<li>Enter a custom Author Url;</li>
							<img src="http://docs.mainwp.com/wp-content/uploads/2014/04/branding-result.png"
							     style="wight: 100% !important;" alt="screenshot"/>
						</ol>
						</p>
						<p>If you want to return the default values, erase the Plugin Name and click the Save Settings
							button. This will return the plugins to the original state.</p>

						<h3>Hide MainWP Child Plugin From the Plugins List</h3>

						<p>
						<ol>
							<li>In the Child Plugin Branding Options area, the last option is Visually Hide Child
								Plugin. Set it to YES and click the Save Settings button at the bottom of the page.
							</li>
							<br/><br/>
							<img src="http://docs.mainwp.com/wp-content/uploads/2014/03/visually-hide.png"
							     style="wight: 100% !important;" alt="screenshot"/>
						</ol>
						</p>
					</div>
				</div>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><?php _e( 'Plugin Name', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a new plugin name for the MainWP Child plguin.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_plugin_name" id="mainwp_branding_plugin_name"
							       value="<?php echo esc_attr( stripslashes( $pluginName ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Plugin Description', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a new plugin description for the MainWP Child plguin.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_plugin_desc" id="mainwp_branding_plugin_desc"
							       value="<?php echo esc_attr( stripslashes( $pluginDesc ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Plugin URI', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a new plugin uri for the MainWP Child plguin.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_plugin_uri" id="mainwp_branding_plugin_uri"
							       value="<?php echo esc_attr( stripslashes( $pluginURI ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Plugin Author', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a new plugin author for the MainWP Child plguin.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_plugin_author" id="mainwp_branding_plugin_author"
							       value="<?php echo esc_attr( stripslashes( $pluginAuthor ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Author URL', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a new author URL for the MainWP Child plguin.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_plugin_author_uri"
							       id="mainwp_branding_plugin_author_uri"
							       value="<?php echo esc_attr( stripslashes( $pluginAuthorURI ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Visually Hide Child Plugin', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set to YES if you want to visually hide the MainWP Child plugin from the plugins list.', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_hide_child_plugin"
								       name="mainwp_branding_hide_child_plugin" <?php echo( 0 == $pluginHide ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_hide_child_plugin"></label>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="postbox mainwp_branding_postbox" section="2">
			<div class="handlediv"><br/></div>
			<h3 class="mainwp_box_title">
				<span><?php _e( 'Child Site Remove / Disable Functions', 'mainwp-branding' ); ?></span></h3>

			<div class="inside">
				<div
					class="mainwp_info-box"><?php _e( 'In this area you can remove or disable different functions and sections in the wp-admin of the Child site.', 'mainwp-branding' ); ?></div>
				<div class="mainwp-branding-tut-box mainwp_ext_info_box">
					<a href="#" class="mainwp-branding-tut-link"><i
							class="fa fa-info-circle"></i> <?php _e( 'Show Quick Start Guide', 'mainwp' ); ?></a>
					<span><a href="#" class="mainwp-branding-tut-dismiss" style="float: right; display: none;"><i
								class="fa fa-times-circle"></i> <?php _e( 'Dismiss', 'mainwp' ); ?></a></span>

					<div class="mainwp-branding-tut-content" style="display: none;">
						<h3>Remove/Disable Functions on Child Sites</h3>

						<p>
						<ol>
							<li>Disable Theme/Plugin Changes - disables all plugin and theme changes in child sites;
							</li>
							<li>Remove MainWP Restore (Clone) - removes the MainWP Restore (MainWP Clone) link from the
								WP Admin > Settings Menu;
							</li>
							<li>Remove MainWP Settings - removes the MainWP Settings link from the WP Admin > Settings
								Menu;
							</li>
							<li>Remove WP Tools - removes the Tools menu from the WordPress Admin Menu;</li>
							<li>Remove WP Settings - removes the Settings menu from the WordPress Admin Menu;</li>
						</ol>
						</p>
					</div>
				</div>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Disable Theme/Plugin Changes', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Disable all plugin and theme changes for your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_disable_change"
								       name="mainwp_branding_disable_change" <?php echo( 0 == $disableChanges ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_disable_change"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Remove MainWP Settings', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Remove the MainWP Settings menu from your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_remove_mainwp_setting"
								       name="mainwp_branding_remove_mainwp_setting" <?php echo( 0 == $removeSetting ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_remove_mainwp_setting"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Remove MainWP Server Information', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Remove the MainWP Server Information menu from your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_remove_mainwp_server_info"
								       name="mainwp_branding_remove_mainwp_server_info" <?php echo( 0 == $removeServerInfo ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_remove_mainwp_server_info"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Remove MainWP Restore (Clone)', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Remove the MaiNWP Restore (Clone) menu from your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_remove_restore_clone"
								       name="mainwp_branding_remove_restore_clone" <?php echo( 0 == $removeRestore ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_remove_restore_clone"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Remove WP Tools', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Remove the WP Tools menu from your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_remove_wp_tools"
								       name="mainwp_branding_remove_wp_tools" <?php echo( 0 == $removeWPTools ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_remove_wp_tools"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Remove WP Settings', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Remove the WP Settings menu from your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_remove_wp_setting"
								       name="mainwp_branding_remove_wp_setting" <?php echo( 0 == $removeWPSetting ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_remove_wp_setting"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Remove Permalinks Menu', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Remove the Permalinks Menu from your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_remove_permalink"
								       name="mainwp_branding_remove_permalink" <?php echo( 0 == $removePermalinks ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_remove_permalink"></label>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="postbox mainwp_branding_postbox" section="3">
			<div class="handlediv"><br/></div>
			<h3 class="mainwp_box_title"><span><?php _e( 'Wordpress Branding Options', 'mainwp-branding' ); ?></span>
			</h3>

			<div class="inside">
				<div
					class="mainwp_info-box"><?php _e( 'In this area you can customize WordPress Admin area of your child sites.', 'mainwp-branding' ); ?></div>
				<div class="mainwp-branding-tut-box mainwp_ext_info_box">
					<a href="#" class="mainwp-branding-tut-link"><i
							class="fa fa-info-circle"></i><?php _e( 'Show Quick Start Guide', 'mainwp' ); ?></a>
					<span><a href="#" class="mainwp-branding-tut-dismiss" style="float: right; display: none;"><i
								class="fa fa-times-circle"></i> <?php _e( 'Dismiss', 'mainwp' ); ?></a></span>

					<div class="mainwp-branding-tut-content" style="display: none;">
						<h3><?php _e( 'Wordpress Branding Options', 'mainwp-branding' ); ?></h3>

						<p>
						<ol>
							<li><strong>Custom Login Image</strong> - This option provides you the ability to replace
								the default WordPress logo(icon) on the login page of your child sites. To do that, go
								to the MainWP Branding Extension settings page, locate the WordPress Branding Options
								section and the first option in the section will enable you to do this.
							</li>
							<li><strong>Custom Favicon</strong> - This option provides you ability to replace default
								WordPress favicon on your child sites. To do that, go to the MainWP Branding Extension
								settings page, locate the WordPress Branding Options section and the second option in
								the section will enable you to do this.
							</li>
							<li><strong>Remove Dashboard Widgets</strong> - To hide/unhide the default WordPress widgets
								on the WordPress dashboard page, simply un-check the boxes(widgets) you want to hide and
								click the Save Settings Button.
							</li>
							<li><strong>Global Footer Content</strong> - This option provides you the ability to add
								custom content to your child sites Footer. Enter your content and click the Save
								Settings button. The content will appear at the bottom of all pages in the Front End of
								your child sites.
							</li>
							<li><strong>Dashboard Footer Content</strong> - This option provides you the ability to add
								custom content to your child sites Footer (Backend). Enter your content and click the
								Save Settings button. The content will appear at the bottom on the right side in the
								Admin area of your child sites.
							</li>
							<li><strong>Site Generator</strong> - This option allows you to tweak the Generator meta tag
								on your child sites.
							</li>
							<li><strong>Custom Admin CSS</strong> - This option enables you to add your custom css code
								which will be applied only on the Admin Area of your child sites.
							</li>
							<li><strong>Custom Login CSS</strong> - The custom CSS code used here will affect only the
								Login Page of your child sites.
							</li>
							<li><strong>Text Replace</strong> - This mechanism provides you the ability to replace any
								word or phrase on your child sites. Simply in the first (Find This Text) field enter a
								text you want to replace and in the second field (Replace With This) enter a new text.
								After you update settings, the extension will replace the text in the Admin area of your
								child sites. To replace another word or a phrase, click the Add link, this will generate
								a new set of fields and you can repeat the process. Also the extension will remember all
								of your tweaks, so you will be able to return child sites to there original condition
								whenever you want.
							</li>
						</ol>
						</p>
					</div>
				</div>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Custom Login Image', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Upload a custom Login Image (logo) for your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<?php
							if ( ! empty( $imageLogin ) ) {
								$imageurl = $imageLogin;
								?>
								<p><img class="brd_login_img" src="<?php echo esc_attr( $imageurl ); ?>"/></p>
								<p>
									<input type="checkbox" class="mainwp-checkbox2" value="1"
									       id="mainwp_branding_delete_login_image"
									       name="mainwp_branding_delete_login_image">
									<label class="mainwp-label2"
									       for="mainwp_branding_delete_login_image"><?php _e( 'Delete Image', 'mainwp' ); ?></label>
								</p><br/>
								<?php
							}
							?>
							<input type="file" name="mainwp_branding_login_image_file"
							       id="mainwp_branding_login_image_file" accept="image/*"/><br/>
							<span
								class="description"><?php _e( "Image must be 500KB maximum. It will be cropped to 310px wide and 70px tall.<br/>For best results  us an image of this site. Allowed formats: jpeg, gif and png.<br /> Note that animated gifs aren't going to be preserved." ) ?></span>

						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Custom Favico', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Upload a custom Favico (icon) for your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<?php
							if ( $imageFavico ) {
								$imageurl = $imageFavico;
								?>
								<p><img class="brd_favico_img" src="<?php echo esc_attr( $imageurl ); ?>"/></p>
								<p>
									<input type="checkbox" class="mainwp-checkbox2" value="1"
									       id="mainwp_branding_delete_favico_image"
									       name="mainwp_branding_delete_favico_image">
									<label class="mainwp-label2"
									       for="mainwp_branding_delete_favico_image"><?php _e( 'Delete Image', 'mainwp' ); ?></label>
								</p><br/>
								<?php
							}
							?>
							<input type="file" name="mainwp_branding_favico_file"
							       id="mainwp_branding_favico_file" accept="image/*"/><br/>
							<span
								class="description"><?php _e( "Image must be 500KB maximum. It will be cropped to 16px wide and 16px tall.<br/>For best results  us an image of this site. Allowed formats: jpeg, gif and png.<br /> Note that animated gifs aren't going to be preserved." ) ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Remove Dashboard Widgets', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Hide Dashboard Widgets from your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<ul class="mainwp_checkboxes mainwp_branding">
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $removeWidgetWelcome ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_remove_widget_welcome"
									       name="mainwp_branding_remove_widget_welcome">
									<label class="mainwp-label2"
									       for="mainwp_branding_remove_widget_welcome"><?php _e( 'Welcome', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $removeWidgetGlance ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_remove_widget_glance"
									       name="mainwp_branding_remove_widget_glance">
									<label class="mainwp-label2"
									       for="mainwp_branding_remove_widget_glance"><?php _e( 'At a Glance', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $removeWidgetAct ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_remove_widget_activity"
									       name="mainwp_branding_remove_widget_activity">
									<label class="mainwp-label2"
									       for="mainwp_branding_remove_widget_activity"><?php _e( 'Activity', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $removeWidgetQuick ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_remove_widget_quick"
									       name="mainwp_branding_remove_widget_quick">
									<label class="mainwp-label2"
									       for="mainwp_branding_remove_widget_quick"><?php _e( 'Quick Draft', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $removeWidgetNews ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_remove_widget_news"
									       name="mainwp_branding_remove_widget_news">
									<label class="mainwp-label2"
									       for="mainwp_branding_remove_widget_news"><?php _e( 'Wordpress News', 'mainwp' ); ?></label>
								</li>
							</ul>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php _e( 'Hide Nag Updates', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Hide the Nag Update for out of date versions of WordPress on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_hide_nag_update"
								       name="mainwp_branding_hide_nag_update" <?php echo( 0 == $hideNag ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_hide_nag_update"></label>
							</div>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php _e( 'Hide Screen Options', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Hide the Screen Options dropdown on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_hide_screen_options"
								       name="mainwp_branding_hide_screen_options" <?php echo( 0 == $hideScreenOpts ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_hide_screen_options"></label>
							</div>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php _e( 'Hide Help Box', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Hide the Help Box dropdown on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_hide_help_box"
								       name="mainwp_branding_hide_help_box" <?php echo( 0 == $hideHelpBox ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_hide_help_box"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Hide Post Meta Boxes', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Hide meta boxes from the Edit Post panel on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<ul class="mainwp_checkboxes mainwp_branding">
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostExcerpt ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_excerpt"
									       name="mainwp_branding_hide_metabox_post_excerpt">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_excerpt"><?php _e( 'Excerpt', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostSlug ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_slug"
									       name="mainwp_branding_hide_metabox_post_slug">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_slug"><?php _e( 'Slug', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostTags ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_tags"
									       name="mainwp_branding_hide_metabox_post_tags">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_tags"><?php _e( 'Tags', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostAuthor ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_author"
									       name="mainwp_branding_hide_metabox_post_author">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_author"><?php _e( 'Author', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostComments ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_comments"
									       name="mainwp_branding_hide_metabox_post_comments">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_comments"><?php _e( 'Comments', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostRevisions ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_revisions"
									       name="mainwp_branding_hide_metabox_post_revisions">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_revisions"><?php _e( 'Revisions', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostDiscussion ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_discussion"
									       name="mainwp_branding_hide_metabox_post_discussion">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_discussion"><?php _e( 'Discussion', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostCategories ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_categories"
									       name="mainwp_branding_hide_metabox_post_categories">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_categories"><?php _e( 'Categories', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostFields ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_custom_fields"
									       name="mainwp_branding_hide_metabox_post_custom_fields">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_custom_fields"><?php _e( 'Custom Fields', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePostTrackbacks ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_post_trackbacks"
									       name="mainwp_branding_hide_metabox_post_trackbacks">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_post_trackbacks"><?php _e( 'Send Trackbacks', 'mainwp' ); ?></label>
								</li>
							</ul>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php _e( 'Hide Page Meta Boxes', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Hide meta boxes from the Edit Page panel on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<ul class="mainwp_checkboxes mainwp_branding">
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePageFields ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_page_custom_fields"
									       name="mainwp_branding_hide_metabox_page_custom_fields">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_page_custom_fields"><?php _e( 'Custom Fields', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePageAuthor ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_page_author"
									       name="mainwp_branding_hide_metabox_page_author">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_page_author"><?php _e( 'Author ', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePageDiscussion ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_page_discussion"
									       name="mainwp_branding_hide_metabox_page_discussion">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_page_discussion"><?php _e( 'Discussion', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePageRevisions ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_page_revisions"
									       name="mainwp_branding_hide_metabox_page_revisions">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_page_revisions"><?php _e( 'Revisions', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePageAttributes ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_page_attributes"
									       name="mainwp_branding_hide_metabox_page_attributes">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_page_attributes"><?php _e( 'Page Attributes', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $hidePageSlug ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_hide_metabox_page_slug"
									       name="mainwp_branding_hide_metabox_page_slug">
									<label class="mainwp-label2"
									       for="mainwp_branding_hide_metabox_page_slug"><?php _e( 'Slug', 'mainwp' ); ?></label>
								</li>
							</ul>
						</td>
					</tr>


					<tr>
						<th scope="row">
							<?php _e( 'Global Footer Content', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set Global Footer Content on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<?php
							remove_editor_styles(); // stop custom theme styling interfering with the editor
							wp_editor( stripslashes( $globalFooter ), 'mainwp_branding_global_footer', array(
									'textarea_name' => 'mainwp_branding_global_footer',
									'textarea_rows' => 5,
									'teeny'         => true,
									'media_buttons' => false,
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Dashboard Footer Content', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set Dashboard Footer Content on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<?php
							remove_editor_styles(); // stop custom theme styling interfering with the editor
							wp_editor( stripslashes( $dashboardFooter ), 'mainwp_branding_dashboard_footer', array(
									'textarea_name' => 'mainwp_branding_dashboard_footer',
									'textarea_rows' => 5,
									'teeny'         => true,
									'media_buttons' => false,
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Site Generator Options', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Set Site Generator Options for your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-branding-table-td-cell">
								<input type="text" name="mainwp_branding_site_generator"
								       id="mainwp_branding_site_generator"
								       value="<?php echo esc_attr( stripslashes( $siteGenerator ) ); ?>"/><br/><?php _e( 'Generator Text' ); ?>
							</div>
							<div class="mainwp-branding-table-td-cell">
								<input type="text" name="mainwp_branding_site_generator_link"
								       id="mainwp_branding_site_generator_link"
								       value="<?php echo esc_attr( stripslashes( $generatorLink ) ); ?>"/><br/><?php _e( 'Generator Link' ); ?>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Custom Admin CSS', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Custom Admin area CSS for your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<textarea class="text" rows="8" cols="48" name="mainwp_branding_admin_css"
							          id="mainwp_branding_admin_css"><?php echo esc_textarea( stripslashes( $adminCss ) ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Custom Login CSS', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Custom Login page CSS for your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<textarea class="text" rows="8" cols="48" name="mainwp_branding_login_css"
							          id="mainwp_branding_login_css"><?php echo esc_textarea( stripslashes( $loginCss ) ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Text Replace', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Replace text on your child site(s).', 'mainwp-branding' ) ); ?></th>
						<td>
							<div id="mainwp-branding-text-replace-row-copy" class="hidden">
								<div class="mainwp-branding-text-replace-row">
									<div class="mainwp-branding-table-td-cell2">
										<input type="text" name="mainwp_branding_texts_value[]" value=""
										       class="mainwp_branding_texts_value"/>
									</div>
									<span class="brd-arr">&#10142;</span>

									<div class="mainwp-branding-table-td-cell2">
										<input type="text" name="mainwp_branding_texts_replace[]" value=""
										       class="mainwp_branding_texts_replace"/>
									</div>
									<div class="mainwp-branding-table-td-cell-opt">
										<a href="#" class="restore_text_replace"
										   title="<?php _e( 'Restore' ); ?>"><?php _e( 'Restore' ); ?></a> | <a href="#"
										                                                                        class="delete_text_replace"
										                                                                        title="<?php _e( 'Delete' ); ?>"><?php _e( 'Delete' ); ?></a>
									</div>
								</div>
							</div>

							<?php
							foreach ( $textsReplace as $text => $replace ) {
								?>
								<div class="mainwp-branding-text-replace-row">
									<div class="mainwp-branding-table-td-cell2">
										<input type="text" name="mainwp_branding_texts_value[]"
										       value="<?php echo esc_attr( stripslashes( $text ) ); ?>"
										       class="mainwp_branding_texts_value"/>
									</div>
									<span class="brd-arr">&#10142;</span>

									<div class="mainwp-branding-table-td-cell2">
										<input type="text" name="mainwp_branding_texts_replace[]"
										       value="<?php echo esc_attr( stripslashes( $replace ) ); ?>"
										       class="mainwp_branding_texts_replace"/>
									</div>
									<div class="mainwp-branding-table-td-cell-opt">
										<a href="#" class="delete_text_replace"
										   title="<?php _e( 'Delete' ); ?>"><?php _e( 'Delete' ); ?></a>
									</div>
								</div>
								<?php
							}
							?>
							<div class="mainwp-branding-text-replace-row">
								<div class="mainwp-branding-table-td-cell2">
									<input type="text" id="mainwp_branding_texts_add_value"
									       name="mainwp_branding_texts_add_value" value=""/><br/>
									<?php _e( 'Find This Text' ); ?>
								</div>
								<span class="brd-arr">&#10142;</span>

								<div class="mainwp-branding-table-td-cell2">
									<input type="text" id="mainwp_branding_texts_add_replace"
									       name="mainwp_branding_texts_add_replace" value=""/><br/>
									<?php _e( 'Replace With This' ); ?>
								</div>
								<div class="mainwp-branding-table-td-cell-opt">
									<a href="#" class="add_text_replace"
									   title="<?php _e( 'Add' ); ?>"><?php _e( 'Add' ); ?></a>
								</div>
							</div>
							<div id="mainwp-branding-texts-replace-ajax-zone"
							     class="mainwp_info-box-yellow hidden"></div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="postbox mainwp_branding_postbox" section="4">
			<div class="handlediv"><br/></div>
			<h3 class="mainwp_box_title"><span>Support Options</span></h3>

			<div class="inside">
				<div
					class="mainwp_info-box"><?php _e( 'Offer your clients an easy way to contact you when they need support. The Support button will appear on the top right of the WP Admin Bar next to their username.', 'mainwp-branding' ); ?></div>
				<div class="mainwp-branding-tut-box mainwp_ext_info_box">
					<a href="#" class="mainwp-branding-tut-link"><i
							class="fa fa-info-circle"></i> <?php _e( 'Show Quick Start Guide', 'mainwp' ); ?></a>
					<span><a href="#" class="mainwp-branding-tut-dismiss" style="float: right; display: none;"><i
								class="fa fa-times-circle"></i> <?php _e( 'Dismiss', 'mainwp' ); ?></a></span>

					<div class="mainwp-branding-tut-content" style="display: none;">
						<h3>Enable Conntact Support Feature</h3>

						<p>
						<ol>
							<li>Locate the Support Options area at the bottom of the page;</li>
							<li>Set the Show Support Button to YES;</li>
							<li>Enter a custom Contact Support Label (optional) - tweaks the Contact Support Button
								title;
							</li>
							<li>Intro Support Message (optional) - adds the Intro Paragraph to the Contact Form on child
								sites;
							</li>
							<li>Submit Button Title (optional) - tweaks the contact form submit button title;</li>
							<li>Successful Submission Message (optional) - tweaks the message that appears after
								successful form submission.
							</li>
							<li>Message to return sender to page they were on (optional) - tweaks the "Go Back" link
								that appears after successful submission.
							</li>
							<li>Enter an email your email address in the Support Email field;</li>
							<img src="http://docs.mainwp.com/wp-content/uploads/2014/04/support-tweaks.png"
							     style="wight: 100% !important;" alt="screenshot"/>
						</ol>
						</p>
					</div>
				</div>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Show Support Button', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enable the Support options on child site(s) and show the Contact Support button.', 'mainwp-branding' ) ); ?></th>
						<td>
							<div class="mainwp-checkbox">
								<input type="checkbox" id="mainwp_branding_show_support_button"
								       name="mainwp_branding_show_support_button" <?php echo( 0 == $showButton ? '' : 'checked="checked"' ); ?>
								       value="1"/>
								<label for="mainwp_branding_show_support_button"></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Show Button In', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Choose where you want to show the  Contact Support button, in the Top Admin Bar and/or Admin Menu.', 'mainwp-branding' ) ); ?></th>
						<td>
							<ul class="mainwp_checkboxes mainwp_branding">
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="1" <?php echo ( 1 == $showButtonIn || 3 == $showButtonIn ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_button_in_top_admin_bar"
									       name="mainwp_branding_button_in_top_admin_bar">
									<label class="mainwp-label2"
									       for="mainwp_branding_button_in_top_admin_bar"><?php _e( 'Top Admin Bar', 'mainwp' ); ?></label>
								</li>
								<li>
									<input type="checkbox" class="mainwp-checkbox2"
									       value="2" <?php echo ( 2 == $showButtonIn || 3 == $showButtonIn ) ? ' checked="checked" ' : '' ?>
									       id="mainwp_branding_button_in_admin_menu"
									       name="mainwp_branding_button_in_admin_menu">
									<label class="mainwp-label2"
									       for="mainwp_branding_button_in_admin_menu"><?php _e( 'Admin Menu', 'mainwp' ); ?></label>
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Contact Support Label', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a title for contact support button.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_button_contact_label"
							       id="mainwp_branding_button_contact_label"
							       value="<?php echo esc_attr( stripslashes( $buttonLabel ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Intro Support Message', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter header message for the contact support form.', 'mainwp-branding' ) ); ?></th>
						<td>
							<?php
							remove_editor_styles(); // stop custom theme styling interfering with the editor
							wp_editor( stripslashes( $supportMessage ), 'mainwp_branding_support_message', array(
									'textarea_name' => 'mainwp_branding_support_message',
									'textarea_rows' => 5,
									'teeny'         => true,
									'media_buttons' => false,
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Submit button title', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a title for submit button.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_submit_button_title"
							       id="mainwp_branding_submit_button_title"
							       value="<?php echo esc_attr( stripslashes( $submitButtonTitle ) ); ?>"/>
						</td>
					</tr>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Successful Submission Message', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter the Message that will be displayed after successful form submission.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_send_email_message"
							       id="mainwp_branding_send_email_message"
							       value="<?php echo esc_attr( stripslashes( $sendMessage ) ); ?>"/>
						</td>
					</tr>

					</tr>
					<tr>
						<th scope="row"><?php _e( 'Message to return sender to page they were on', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter a message to return sender to page they were on.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_message_return_sender"
							       id="mainwp_branding_message_return_sender"
							       value="<?php echo esc_attr( stripslashes( $returnMessage ) ); ?>"/>
						</td>
					</tr>

					</tr>
					<tr>
						<th scope="row"><?php _e( 'Support Email Address', 'mainwp-branding' ); ?><?php do_action( 'mainwp_renderToolTip', __( 'Enter an email address for support emails.', 'mainwp-branding' ) ); ?></th>
						<td>
							<input type="text" name="mainwp_branding_support_email" id="mainwp_branding_support_email"
							       value="<?php echo esc_attr( $supportEmail ); ?>"/> <?php _e( 'Note: Support form WILL NOT show up without an email address.', 'mainwp-branding' ); ?>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
		</div>

		<?php
	}

	static function get_instance() {

		if ( null == MainWP_Branding::$instance ) {
			MainWP_Branding::$instance = new MainWP_Branding();
		}

		return MainWP_Branding::$instance;
	}

	public function branding_update_site( $websiteId ) {
		global $wpdb, $mainWPBrandingExtensionActivator;
		if ( isset( $_POST['submit'] ) && $websiteId ) {
			$website = apply_filters( 'mainwp-getsites', $mainWPBrandingExtensionActivator->get_child_file(), $mainWPBrandingExtensionActivator->get_child_key(), $websiteId );
			if ( $website && is_array( $website ) ) {
				$website = current( $website );
			}

			if ( ! $website ) {
				return;
			}
			self::handle_settings_post( $website, true );
		}
	}

	public static function handle_settings_post( $website = null, $edit_site = false ) {

		if ( isset( $_POST['submit'] ) || $edit_site ) {
			$current_extra_settings = array();
			if ( $edit_site && is_array( $website ) ) {
				$site_branding = MainWP_Branding_DB::get_instance()->get_branding_by( 'site_url', $website['url'] );
				if ( is_object( $site_branding ) ) {
					$current_extra_settings = unserialize( $site_branding->extra_settings );
				}
			}
			$output = array();

			$preserve_branding = ( isset( $_POST['mainwp_branding_preserve_branding'] ) && ! empty( $_POST['mainwp_branding_preserve_branding'] ) ) ? $_POST['mainwp_branding_preserve_branding'] : 0;

			$plugin_name = '';
			if ( isset( $_POST['mainwp_branding_plugin_name'] ) && ! empty( $_POST['mainwp_branding_plugin_name'] ) ) {
				$plugin_name = sanitize_text_field( $_POST['mainwp_branding_plugin_name'] );
			}
			$plugin_desc = '';
			if ( isset( $_POST['mainwp_branding_plugin_desc'] ) && ! empty( $_POST['mainwp_branding_plugin_desc'] ) ) {
				$plugin_desc = sanitize_text_field( $_POST['mainwp_branding_plugin_desc'] );
			}
			$plugin_uri = '';
			if ( isset( $_POST['mainwp_branding_plugin_uri'] ) && ! empty( $_POST['mainwp_branding_plugin_uri'] ) ) {
				$plugin_uri = trim( $_POST['mainwp_branding_plugin_uri'] );
				if ( ! preg_match( '/^https?\:\/\/.*$/i', $plugin_uri ) ) {
					$plugin_uri = 'http://' . $plugin_uri;
				}
			}
			$plugin_author = '';
			if ( isset( $_POST['mainwp_branding_plugin_author'] ) && ! empty( $_POST['mainwp_branding_plugin_author'] ) ) {
				$plugin_author = sanitize_text_field( $_POST['mainwp_branding_plugin_author'] );
			}
			$plugin_author_uri = '';
			if ( isset( $_POST['mainwp_branding_plugin_author_uri'] ) && ! empty( $_POST['mainwp_branding_plugin_author_uri'] ) ) {
				$plugin_author_uri = trim( $_POST['mainwp_branding_plugin_author_uri'] );
				if ( ! preg_match( '/^https?\:\/\/.*$/i', $plugin_author_uri ) ) {
					$plugin_author_uri = 'http://' . $plugin_author_uri;
				}
			}
			$plugin_hide = 0;
			if ( isset( $_POST['mainwp_branding_hide_child_plugin'] ) && ! empty( $_POST['mainwp_branding_hide_child_plugin'] ) ) {
				$plugin_hide = $_POST['mainwp_branding_hide_child_plugin'];
			}
			$disable_change = 0;
			if ( isset( $_POST['mainwp_branding_disable_change'] ) && ! empty( $_POST['mainwp_branding_disable_change'] ) ) {
				$disable_change = $_POST['mainwp_branding_disable_change'];
			}
			$show_button = 0;
			if ( isset( $_POST['mainwp_branding_show_support_button'] ) && ! empty( $_POST['mainwp_branding_show_support_button'] ) ) {
				$show_button = intval( $_POST['mainwp_branding_show_support_button'] );
			}

			$show_button_in = ( isset( $_POST['mainwp_branding_button_in_top_admin_bar'] ) ? intval( $_POST['mainwp_branding_button_in_top_admin_bar'] ) : 0 ) + ( isset( $_POST['mainwp_branding_button_in_admin_menu'] ) ? intval( $_POST['mainwp_branding_button_in_admin_menu'] ) : 0 );

			$button_contact_label = '';
			if ( isset( $_POST['mainwp_branding_button_contact_label'] ) && ! empty( $_POST['mainwp_branding_button_contact_label'] ) ) {
				$button_contact_label = sanitize_text_field( $_POST['mainwp_branding_button_contact_label'] );
			}
			$send_email_message = '';
			if ( isset( $_POST['mainwp_branding_send_email_message'] ) && ! empty( $_POST['mainwp_branding_send_email_message'] ) ) {
				$send_email_message = sanitize_text_field( $_POST['mainwp_branding_send_email_message'] );
			}
			$support_email = '';
			if ( isset( $_POST['mainwp_branding_support_email'] ) && ! empty( $_POST['mainwp_branding_support_email'] ) ) {
				$support_email = trim( $_POST['mainwp_branding_support_email'] );
				if ( ! preg_match( '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $support_email ) ) {
					$support_email = '';
				}
			}
			$support_message = '';
			if ( isset( $_POST['mainwp_branding_support_message'] ) && ! empty( $_POST['mainwp_branding_support_message'] ) ) {
				$support_message = $_POST['mainwp_branding_support_message'];
			}

			$remove_restore = 0;
			if ( isset( $_POST['mainwp_branding_remove_restore_clone'] ) && ! empty( $_POST['mainwp_branding_remove_restore_clone'] ) ) {
				$remove_restore = intval( $_POST['mainwp_branding_remove_restore_clone'] );
			}
			$remove_setting = 0;
			if ( isset( $_POST['mainwp_branding_remove_mainwp_setting'] ) && ! empty( $_POST['mainwp_branding_remove_mainwp_setting'] ) ) {
				$remove_setting = intval( $_POST['mainwp_branding_remove_mainwp_setting'] );
			}
			$remove_server_info = 0;
			if ( isset( $_POST['mainwp_branding_remove_mainwp_server_info'] ) && ! empty( $_POST['mainwp_branding_remove_mainwp_server_info'] ) ) {
				$remove_server_info = intval( $_POST['mainwp_branding_remove_mainwp_server_info'] );
			}
			$remove_wp_tools = 0;
			if ( isset( $_POST['mainwp_branding_remove_wp_tools'] ) && ! empty( $_POST['mainwp_branding_remove_wp_tools'] ) ) {
				$remove_wp_tools = intval( $_POST['mainwp_branding_remove_wp_tools'] );
			}
			$remove_wp_setting = 0;
			if ( isset( $_POST['mainwp_branding_remove_wp_setting'] ) && ! empty( $_POST['mainwp_branding_remove_wp_setting'] ) ) {
				$remove_wp_setting = intval( $_POST['mainwp_branding_remove_wp_setting'] );
			}
			$remove_permalink = 0;
			if ( isset( $_POST['mainwp_branding_remove_permalink'] ) && ! empty( $_POST['mainwp_branding_remove_permalink'] ) ) {
				$remove_permalink = intval( $_POST['mainwp_branding_remove_permalink'] );
			}

			$message_return_sender = '';
			if ( isset( $_POST['mainwp_branding_message_return_sender'] ) && ! empty( $_POST['mainwp_branding_message_return_sender'] ) ) {
				$message_return_sender = sanitize_text_field( $_POST['mainwp_branding_message_return_sender'] );
			}

			$submit_button_title = '';
			if ( isset( $_POST['mainwp_branding_submit_button_title'] ) && ! empty( $_POST['mainwp_branding_submit_button_title'] ) ) {
				$submit_button_title = sanitize_text_field( $_POST['mainwp_branding_submit_button_title'] );
			}

			$global_footer = '';
			if ( isset( $_POST['mainwp_branding_global_footer'] ) && ! empty( $_POST['mainwp_branding_global_footer'] ) ) {
				$global_footer = $_POST['mainwp_branding_global_footer'];
			}

			$dashboard_footer = '';
			if ( isset( $_POST['mainwp_branding_dashboard_footer'] ) && ! empty( $_POST['mainwp_branding_dashboard_footer'] ) ) {
				$dashboard_footer = $_POST['mainwp_branding_dashboard_footer'];
			}

			$site_generator = isset( $_POST['mainwp_branding_site_generator'] ) ? trim( $_POST['mainwp_branding_site_generator'] ) : '';
			$generator_link = isset( $_POST['mainwp_branding_site_generator_link'] ) ? trim( $_POST['mainwp_branding_site_generator_link'] ) : '';
			if ( ! empty( $generator_link ) && ! preg_match( '/^https?\:\/\/.*$/i', $generator_link ) ) {
				$generator_link = 'http://' . $generator_link;
			}

			$remove_widget_welcome  = isset( $_POST['mainwp_branding_remove_widget_welcome'] ) ? intval( $_POST['mainwp_branding_remove_widget_welcome'] ) : 0;
			$remove_widget_glance   = isset( $_POST['mainwp_branding_remove_widget_glance'] ) ? intval( $_POST['mainwp_branding_remove_widget_glance'] ) : 0;
			$remove_widget_activity = isset( $_POST['mainwp_branding_remove_widget_activity'] ) ? intval( $_POST['mainwp_branding_remove_widget_activity'] ) : 0;
			$remove_widget_quick    = isset( $_POST['mainwp_branding_remove_widget_quick'] ) ? intval( $_POST['mainwp_branding_remove_widget_quick'] ) : 0;
			$remove_widget_news     = isset( $_POST['mainwp_branding_remove_widget_news'] ) ? intval( $_POST['mainwp_branding_remove_widget_news'] ) : 0;

			$admin_css     = isset( $_POST['mainwp_branding_admin_css'] ) ? trim( $_POST['mainwp_branding_admin_css'] ) : '';
			$login_css     = isset( $_POST['mainwp_branding_login_css'] ) ? trim( $_POST['mainwp_branding_login_css'] ) : '';
			$texts_replace = array();
			if ( isset( $_POST['mainwp_branding_texts_value'] ) && is_array( $_POST['mainwp_branding_texts_value'] ) && count( $_POST['mainwp_branding_texts_value'] ) > 0 ) {
				foreach ( $_POST['mainwp_branding_texts_value'] as $i => $value ) {
					$value   = trim( $value );
					$replace = isset( $_POST['mainwp_branding_texts_replace'][ $i ] ) ? trim( $_POST['mainwp_branding_texts_replace'][ $i ] ) : '';
					if ( ! empty( $value ) && ! empty( $replace ) ) {
						$texts_replace[ $value ] = $replace;
					}
				}
			}

			$value   = isset( $_POST['mainwp_branding_texts_add_value'] ) ? trim( $_POST['mainwp_branding_texts_add_value'] ) : '';
			$replace = isset( $_POST['mainwp_branding_texts_add_replace'] ) ? trim( $_POST['mainwp_branding_texts_add_replace'] ) : '';
			if ( ! empty( $value ) && ! empty( $replace ) ) {
				$texts_replace[ $value ] = $replace;
			}

			//            $branding_dir = apply_filters('mainwp_getspecificdir',"branding/images");
			//            if (!file_exists($branding_dir)) {
			//                @mkdir($branding_dir, 0777, true);
			//            }
			//            if (!file_exists($branding_dir . '/index.php'))
			//            {
			//                @touch($branding_dir . '/index.php');
			//            }
			$image_login = 'NOTCHANGE';
			if ( isset( $_POST['mainwp_branding_delete_login_image'] ) && '1' == $_POST['mainwp_branding_delete_login_image'] ) {
				$image_login = '';
			}
			if ( UPLOAD_ERR_OK == $_FILES['mainwp_branding_login_image_file']['error'] ) {
				$output = self::handle_upload_image( $_FILES['mainwp_branding_login_image_file'], 'login', 310, 70 );
				if ( is_array( $output ) && isset( $output['fileurl'] ) && ! empty( $output['fileurl'] ) ) {
					$image_login      = $output['fileurl'];
					$image_login_path = $output['filepath'];
				}
			}

			$image_favico      = 'NOTCHANGE';
			$image_favico_path = '';
			if ( isset( $_POST['mainwp_branding_delete_favico_image'] ) && '1' == $_POST['mainwp_branding_delete_favico_image'] ) {
				$image_favico = '';
			}

			if ( UPLOAD_ERR_OK == $_FILES['mainwp_branding_favico_file']['error'] ) {
				$output = self::handle_upload_image( $_FILES['mainwp_branding_favico_file'], 'favico', 16, 16 );
				if ( is_array( $output ) && isset( $output['fileurl'] ) && ! empty( $output['fileurl'] ) ) {
					$image_favico      = $output['fileurl'];
					$image_favico_path = $output['filepath'];
				}
			}

			$hide_nag_update     = isset( $_POST['mainwp_branding_hide_nag_update'] ) ? intval( $_POST['mainwp_branding_hide_nag_update'] ) : 0;
			$hide_screen_options = isset( $_POST['mainwp_branding_hide_screen_options'] ) ? intval( $_POST['mainwp_branding_hide_screen_options'] ) : 0;
			$hide_help_box       = isset( $_POST['mainwp_branding_hide_help_box'] ) ? intval( $_POST['mainwp_branding_hide_help_box'] ) : 0;

			$hide_metabox_post_excerpt  = isset( $_POST['mainwp_branding_hide_metabox_post_excerpt'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_excerpt'] ) : 0;
			$hide_metabox_post_slug     = isset( $_POST['mainwp_branding_hide_metabox_post_slug'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_slug'] ) : 0;
			$hide_metabox_post_tags     = isset( $_POST['mainwp_branding_hide_metabox_post_tags'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_tags'] ) : 0;
			$hide_metabox_post_author   = isset( $_POST['mainwp_branding_hide_metabox_post_author'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_author'] ) : 0;
			$hide_metabox_post_comments = isset( $_POST['mainwp_branding_hide_metabox_post_comments'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_comments'] ) : 0;

			$hide_metabox_post_revisions     = isset( $_POST['mainwp_branding_hide_metabox_post_revisions'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_revisions'] ) : 0;
			$hide_metabox_post_discussion    = isset( $_POST['mainwp_branding_hide_metabox_post_discussion'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_discussion'] ) : 0;
			$hide_metabox_post_categories    = isset( $_POST['mainwp_branding_hide_metabox_post_categories'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_categories'] ) : 0;
			$hide_metabox_post_custom_fields = isset( $_POST['mainwp_branding_hide_metabox_post_custom_fields'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_custom_fields'] ) : 0;
			$hide_metabox_post_trackbacks    = isset( $_POST['mainwp_branding_hide_metabox_post_trackbacks'] ) ? intval( $_POST['mainwp_branding_hide_metabox_post_trackbacks'] ) : 0;

			$hide_metabox_page_custom_fields = isset( $_POST['mainwp_branding_hide_metabox_page_custom_fields'] ) ? intval( $_POST['mainwp_branding_hide_metabox_page_custom_fields'] ) : 0;
			$hide_metabox_page_author        = isset( $_POST['mainwp_branding_hide_metabox_page_author'] ) ? intval( $_POST['mainwp_branding_hide_metabox_page_author'] ) : 0;
			$hide_metabox_page_discussion    = isset( $_POST['mainwp_branding_hide_metabox_page_discussion'] ) ? intval( $_POST['mainwp_branding_hide_metabox_page_discussion'] ) : 0;
			$hide_metabox_page_revisions     = isset( $_POST['mainwp_branding_hide_metabox_page_revisions'] ) ? intval( $_POST['mainwp_branding_hide_metabox_page_revisions'] ) : 0;
			$hide_metabox_page_attributes    = isset( $_POST['mainwp_branding_hide_metabox_page_attributes'] ) ? intval( $_POST['mainwp_branding_hide_metabox_page_attributes'] ) : 0;
			$hide_metabox_page_slug          = isset( $_POST['mainwp_branding_hide_metabox_page_slug'] ) ? intval( $_POST['mainwp_branding_hide_metabox_page_slug'] ) : 0;

			if ( isset( $_POST['submit'] ) && ! $edit_site ) {
				self::get_instance()->set_option( 'child_plugin_name', $plugin_name );
				self::get_instance()->set_option( 'child_plugin_desc', $plugin_desc );
				self::get_instance()->set_option( 'child_plugin_author', $plugin_author );
				self::get_instance()->set_option( 'child_plugin_author_uri', $plugin_author_uri );
				self::get_instance()->set_option( 'child_plugin_uri', $plugin_uri );
				self::get_instance()->set_option( 'child_plugin_hide', $plugin_hide );
				self::get_instance()->set_option( 'child_disable_change', $disable_change );
				self::get_instance()->set_option( 'child_show_support_button', $show_button );
				self::get_instance()->set_option( 'child_show_support_button_in', $show_button_in );
				self::get_instance()->set_option( 'child_support_email', $support_email );
				self::get_instance()->set_option( 'child_support_message', $support_message );
				self::get_instance()->set_option( 'child_remove_restore', $remove_restore );
				self::get_instance()->set_option( 'child_remove_setting', $remove_setting );
				self::get_instance()->set_option( 'child_remove_server_info', $remove_server_info );
				self::get_instance()->set_option( 'child_remove_wp_tools', $remove_wp_tools );
				self::get_instance()->set_option( 'child_remove_wp_setting', $remove_wp_setting );
				self::get_instance()->set_option( 'child_remove_permalink', $remove_permalink );
				self::get_instance()->set_option( 'child_button_contact_label', $button_contact_label );
				self::get_instance()->set_option( 'child_send_email_message', $send_email_message );
				self::get_instance()->set_option( 'child_message_return_sender', $message_return_sender );
				self::get_instance()->set_option( 'child_submit_button_title', $submit_button_title );
				self::get_instance()->set_option( 'child_global_footer', $global_footer );
				self::get_instance()->set_option( 'child_dashboard_footer', $dashboard_footer );
				self::get_instance()->set_option( 'child_remove_widget_welcome', $remove_widget_welcome );
				self::get_instance()->set_option( 'child_remove_widget_glance', $remove_widget_glance );
				self::get_instance()->set_option( 'child_remove_widget_activity', $remove_widget_activity );
				self::get_instance()->set_option( 'child_remove_widget_quick', $remove_widget_quick );
				self::get_instance()->set_option( 'child_remove_widget_news', $remove_widget_news );
				self::get_instance()->set_option( 'child_site_generator', $site_generator );
				self::get_instance()->set_option( 'child_generator_link', $generator_link );
				self::get_instance()->set_option( 'child_admin_css', $admin_css );
				self::get_instance()->set_option( 'child_login_css', $login_css );
				self::get_instance()->set_option( 'child_texts_replace', $texts_replace );
				if ( 'NOTCHANGE' !== $image_login ) {
					$old_file = self::get_instance()->get_option( 'child_login_image_path' );
					if ( ( $old_file != $image_login_path ) && ( $old_file != $image_favico_path ) ) {
						@unlink( $old_file );
					}
					self::get_instance()->set_option( 'child_login_image', $image_login );
					self::get_instance()->set_option( 'child_login_image_path', $image_login_path );
				}
				if ( 'NOTCHANGE' !== $image_favico ) {
					$old_file = self::get_instance()->get_option( 'child_favico_image_path' );
					if ( ( $old_file != $image_login_path ) && ( $old_file != $image_favico_path ) ) {
						@unlink( $old_file );
					}
					self::get_instance()->set_option( 'child_favico_image', $image_favico );
					self::get_instance()->set_option( 'child_favico_image_path', $image_favico_path );
				}

				self::get_instance()->set_option( 'child_hide_nag', $hide_nag_update );
				self::get_instance()->set_option( 'child_hide_screen_opts', $hide_screen_options );
				self::get_instance()->set_option( 'child_hide_help_box', $hide_help_box );

				self::get_instance()->set_option( 'child_hide_metabox_post_excerpt', $hide_metabox_post_excerpt );
				self::get_instance()->set_option( 'child_hide_metabox_post_slug', $hide_metabox_post_slug );
				self::get_instance()->set_option( 'child_hide_metabox_post_tags', $hide_metabox_post_tags );
				self::get_instance()->set_option( 'child_hide_metabox_post_author', $hide_metabox_post_author );
				self::get_instance()->set_option( 'child_hide_metabox_post_comments', $hide_metabox_post_comments );

				self::get_instance()->set_option( 'child_hide_metabox_post_revisions', $hide_metabox_post_revisions );
				self::get_instance()->set_option( 'child_hide_metabox_post_discussion', $hide_metabox_post_discussion );
				self::get_instance()->set_option( 'child_hide_metabox_post_categories', $hide_metabox_post_categories );
				self::get_instance()->set_option( 'child_hide_metabox_post_custom_fields', $hide_metabox_post_custom_fields );
				self::get_instance()->set_option( 'child_hide_metabox_post_trackbacks', $hide_metabox_post_trackbacks );

				self::get_instance()->set_option( 'child_hide_metabox_page_custom_fields', $hide_metabox_page_custom_fields );
				self::get_instance()->set_option( 'child_hide_metabox_page_author', $hide_metabox_page_author );
				self::get_instance()->set_option( 'child_hide_metabox_page_discussion', $hide_metabox_page_discussion );
				self::get_instance()->set_option( 'child_hide_metabox_page_revisions', $hide_metabox_page_revisions );
				self::get_instance()->set_option( 'child_hide_metabox_page_attributes', $hide_metabox_page_attributes );
				self::get_instance()->set_option( 'child_hide_metabox_page_slug', $hide_metabox_page_slug );
				self::get_instance()->set_option( 'child_preserve_branding', $preserve_branding );
			} else if ( $edit_site ) {
				$header   = serialize( array(
					'plugin_name'   => $plugin_name,
					'plugin_desc'   => $plugin_desc,
					'plugin_author' => $plugin_author,
					'author_uri'    => $plugin_author_uri,
					'plugin_uri'    => $plugin_uri,
				) );
				$branding = array(
					'site_url'                    => $website['url'],
					'plugin_header'               => $header,
					'hide_child_plugin'           => $plugin_hide,
					'disable_theme_plugin_change' => $disable_change,
					'show_support_button'         => $show_button,
					'support_email'               => $support_email,
					'support_message'             => $support_message,
					'remove_restore'              => $remove_restore,
					'remove_setting'              => $remove_setting,
					'remove_server_info'          => $remove_server_info,
					'remove_wp_tools'             => $remove_wp_tools,
					'remove_wp_setting'           => $remove_wp_setting,
					'button_contact_label'        => $button_contact_label,
					'send_email_message'          => $send_email_message,
					'override'                    => isset( $_POST['mainwp_branding_site_override'] ) ? intval( $_POST['mainwp_branding_site_override'] ) : 0,
				);

				$branding['id'] = isset( $_POST['mainwp_branding_site_branding_id'] ) ? intval( $_POST['mainwp_branding_site_branding_id'] ) : 0;

				$extra_settings = array(
					'submit_button_title'             => $submit_button_title,
					'message_return_sender'           => $message_return_sender,
					'remove_permalink'                => $remove_permalink,
					'show_button_in'                  => $show_button_in,
					'disable_wp_branding'             => isset( $_POST['mainwp_branding_site_disable_wp_branding'] ) ? intval( $_POST['mainwp_branding_site_disable_wp_branding'] ) : 0,
					'global_footer'                   => $global_footer,
					'dashboard_footer'                => $dashboard_footer,
					'remove_widget_welcome'           => $remove_widget_welcome,
					'remove_widget_glance'            => $remove_widget_glance,
					'remove_widget_activity'          => $remove_widget_activity,
					'remove_widget_quick'             => $remove_widget_quick,
					'remove_widget_news'              => $remove_widget_news,
					'site_generator'                  => $site_generator,
					'generator_link'                  => $generator_link,
					'admin_css'                       => $admin_css,
					'login_css'                       => $login_css,
					'texts_replace'                   => $texts_replace,
					'image_favico'                    => $image_favico,
					'hide_nag'                        => $hide_nag_update,
					'hide_screen_opts'                => $hide_screen_options,
					'hide_help_box'                   => $hide_help_box,
					'hide_metabox_post_excerpt'       => $hide_metabox_post_excerpt,
					'hide_metabox_post_slug'          => $hide_metabox_post_slug,
					'hide_metabox_post_tags'          => $hide_metabox_post_tags,
					'hide_metabox_post_author'        => $hide_metabox_post_author,
					'hide_metabox_post_comments'      => $hide_metabox_post_comments,
					'hide_metabox_post_revisions'     => $hide_metabox_post_revisions,
					'hide_metabox_post_discussion'    => $hide_metabox_post_discussion,
					'hide_metabox_post_categories'    => $hide_metabox_post_categories,
					'hide_metabox_post_custom_fields' => $hide_metabox_post_custom_fields,
					'hide_metabox_post_trackbacks'    => $hide_metabox_post_trackbacks,
					'hide_metabox_page_custom_fields' => $hide_metabox_page_custom_fields,
					'hide_metabox_page_author'        => $hide_metabox_page_author,
					'hide_metabox_page_discussion'    => $hide_metabox_page_discussion,
					'hide_metabox_page_revisions'     => $hide_metabox_page_revisions,
					'hide_metabox_page_attributes'    => $hide_metabox_page_attributes,
					'hide_metabox_page_slug'          => $hide_metabox_page_slug,
					'preserve_branding'               => $preserve_branding,
				);

				if ( 'NOTCHANGE' === $image_login ) {
					$extra_settings['login_image']      = isset( $current_extra_settings['login_image'] ) ? $current_extra_settings['login_image'] : '';
					$extra_settings['login_image_path'] = isset( $current_extra_settings['login_image_path'] ) ? $current_extra_settings['login_image_path'] : '';
				} else {
					$extra_settings['login_image']      = $image_login;
					$extra_settings['login_image_path'] = $image_login_path;
					$old_file                           = isset( $current_extra_settings['login_image_path'] ) ? $current_extra_settings['login_image_path'] : '';
					if ( ( $old_file != $image_login_path ) && ( $old_file != $image_favico_path ) ) {
						@unlink( $old_file );
					}
				}

				if ( 'NOTCHANGE' === $image_favico ) {
					$extra_settings['favico_image']      = isset( $current_extra_settings['favico_image'] ) ? $current_extra_settings['favico_image'] : '';
					$extra_settings['favico_image_path'] = isset( $current_extra_settings['favico_image_path'] ) ? $current_extra_settings['favico_image_path'] : '';
				} else {
					$extra_settings['favico_image']      = $image_login;
					$extra_settings['favico_image_path'] = $image_login_path;
					$old_file                            = isset( $current_extra_settings['favico_image_path'] ) ? $current_extra_settings['favico_image_path'] : '';
					if ( ( $old_file != $image_login_path ) && ( $old_file != $image_favico_path ) ) {
						@unlink( $old_file );
					}
				}

				$branding['extra_settings'] = serialize( $extra_settings );

				$result = MainWP_Branding_DB::get_instance()->update_branding( $branding );
				error_log( print_r( $result, true ) );
				update_option( 'mainwp_branding_need_to_update_site', 1 );
			}

			return $output;
		}

		return false;
	}

	public static function handle_upload_image( $file_input, $what, $max_width, $max_height ) {
		// assign error_message here
		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'];
		$base_url   = $upload_dir['baseurl'];
		$output     = array();
		$filename   = '';
		$filepath   = '';
		if ( UPLOAD_ERR_OK == $file_input['error'] ) {
			$tmp_file = $file_input['tmp_name'];
			if ( is_uploaded_file( $tmp_file ) ) {
				$file_size      = $file_input['size'];
				$file_type      = $file_input['type'];
				$file_name      = $file_input['name'];
				$file_extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );

				if ( ( $file_size > 500 * 1025 ) ) {
					$output['error'][] = ( 'login' === $what ) ? 0 : 3;
				} elseif (
					( 'image/jpeg' != $file_type ) &&
					( 'image/jpg' != $file_type ) &&
					( 'image/gif' != $file_type ) &&
					( 'image/png' != $file_type )
				) {
					$output['error'][] = ( 'login' === $what ) ? 1 : 4;
				} elseif (
					( 'jpeg' != $file_extension ) &&
					( 'jpg' != $file_extension ) &&
					( 'gif' != $file_extension ) &&
					( 'png' != $file_extension )
				) {
					$output['error'][] = ( 'login' === $what ) ? 1 : 4;
				} else {

					$dest_file = $base_dir . '/' . $file_name;
					$dest_file = dirname( $dest_file ) . '/' . wp_unique_filename( dirname( $dest_file ), basename( $dest_file ) );

					if ( move_uploaded_file( $tmp_file, $dest_file ) ) {
						if ( file_exists( $dest_file ) ) {
							list( $width, $height, $type, $attr ) = getimagesize( $dest_file );
						}

						$resize = false;
						if ( $width > $max_width ) {
							$dst_width = $max_width;
							if ( $height > $max_height ) {
								$dst_height = $max_height;
							} else {
								$dst_height = $height;
							}
							$resize = true;
						} else if ( $height > $max_height ) {
							$dst_width  = $width;
							$dst_height = $max_height;
							$resize     = true;
						}

						if ( $resize ) {
							$src          = $dest_file;
							$cropped_file = wp_crop_image( $src, 0, 0, $width, $height, $dst_width, $dst_height, false );
							if ( ! $cropped_file || is_wp_error( $cropped_file ) ) {
								$output['error'][] = ( 'login' === $what ) ? 8 : 9;
							} else {
								@unlink( $dest_file );
								$filename = basename( $cropped_file );
								$filepath = $cropped_file;
							}
						} else {
							$filename = basename( $dest_file );
							$filepath = $dest_file;
						}
					} else {
						$output['error'][] = ( 'login' === $what  ) ? 2 : 5;
					}
				}
			}
		}
		$output['fileurl']  = ! empty( $filename ) ? $base_url . '/' . $filename : '';
		$output['filepath'] = ! empty( $filepath ) ? $filepath : '';

		return $output;
	}

	public function perform_branding_child_plugin() {
		$siteid = $_POST['siteId'];
		if ( empty( $siteid ) ) {
			die( json_encode( 'FAIL' ) );
		}
		global $mainWPBrandingExtensionActivator;

		if ( ! isset( $_POST['override'] ) || ! isset( $_POST['branding_id'] ) ) {
			$website = apply_filters( 'mainwp-getsites', $mainWPBrandingExtensionActivator->get_child_file(), $mainWPBrandingExtensionActivator->get_child_key(), $siteid );
			if ( $website && is_array( $website ) ) {
				$website = current( $website );
			}
			if ( is_array( $website ) ) {
				$result = MainWP_Branding_DB::get_instance()->get_branding_by( 'site_url', $website['url'] );
				// if overrided dont update.
				if ( $result->override ) {
					die( json_encode( array( 'result' => 'OVERRIDED' ) ) );
				}
			} else {
				die( json_encode( array( 'error' => 'Error: site empty' ) ) );
			}
		}

		$override = $_POST['override'];
		$branding = null;
		if ( isset( $_POST['branding_id'] ) && $_POST['branding_id'] ) {
			$branding = MainWP_Branding_DB::get_instance()->get_branding_by( 'id', $_POST['branding_id'] );
		}

		$header = $extra_settings = array();
		if ( is_object( $branding ) ) {
			$header         = unserialize( $branding->plugin_header );
			$extra_settings = unserialize( $branding->extra_settings );
		}
		$post_data = array( 'action' => 'update_branding' );
		if ( $override && is_object( $branding ) ) {
			$header                                   = unserialize( $branding->plugin_header );
			$settings                                 = array(
				'child_plugin_name'          => $header['plugin_name'],
				'child_plugin_desc'          => $header['plugin_desc'],
				'child_plugin_author'        => $header['plugin_author'],
				'child_plugin_author_uri'    => $header['author_uri'],
				'child_plugin_plugin_uri'    => $header['plugin_uri'],
				'child_plugin_hide'          => $branding->hide_child_plugin,
				'child_disable_change'       => $branding->disable_theme_plugin_change,
				'child_show_support_button'  => $branding->show_support_button,
				'child_support_email'        => $branding->support_email,
				'child_support_message'      => $branding->support_message,
				'child_remove_restore'       => $branding->remove_restore,
				'child_remove_setting'       => $branding->remove_setting,
				'child_remove_server_info'   => $branding->remove_server_info,
				'child_remove_wp_tools'      => $branding->remove_wp_tools,
				'child_remove_wp_setting'    => $branding->remove_wp_setting,
				'child_button_contact_label' => $branding->button_contact_label,
				'child_send_email_message'   => $branding->send_email_message,
			);
			$settings['child_submit_button_title']    = $extra_settings['submit_button_title'];
			$settings['child_message_return_sender']  = $extra_settings['message_return_sender'];
			$settings['child_remove_permalink']       = $extra_settings['remove_permalink'];
			$settings['child_show_support_button_in'] = $extra_settings['show_button_in'];
			$settings['child_global_footer']          = $extra_settings['global_footer'];
			$settings['child_dashboard_footer']       = $extra_settings['dashboard_footer'];
			$settings['child_remove_widget_welcome']  = $extra_settings['remove_widget_welcome'];
			$settings['child_remove_widget_glance']   = $extra_settings['remove_widget_glance'];
			$settings['child_remove_widget_activity'] = $extra_settings['remove_widget_activity'];
			$settings['child_remove_widget_quick']    = $extra_settings['remove_widget_quick'];
			$settings['child_remove_widget_news']     = $extra_settings['remove_widget_news'];
			$settings['child_site_generator']         = $extra_settings['site_generator'];
			$settings['child_generator_link']         = $extra_settings['generator_link'];
			$settings['child_admin_css']              = $extra_settings['admin_css'];
			$settings['child_login_css']              = $extra_settings['login_css'];
			$settings['child_texts_replace']          = $extra_settings['texts_replace'];
			$settings['child_login_image']            = $extra_settings['login_image'];
			$settings['child_favico_image']           = $extra_settings['favico_image'];
			$settings['child_hide_nag']               = $extra_settings['hide_nag'];
			$settings['child_hide_screen_opts']       = $extra_settings['hide_screen_opts'];
			$settings['child_hide_help_box']          = $extra_settings['hide_help_box'];

			$settings['child_hide_metabox_post_excerpt']  = $extra_settings['hide_metabox_post_excerpt'];
			$settings['child_hide_metabox_post_slug']     = $extra_settings['hide_metabox_post_slug'];
			$settings['child_hide_metabox_post_tags']     = $extra_settings['hide_metabox_post_tags'];
			$settings['child_hide_metabox_post_author']   = $extra_settings['hide_metabox_post_author'];
			$settings['child_hide_metabox_post_comments'] = $extra_settings['hide_metabox_post_comments'];

			$settings['child_hide_metabox_post_revisions']     = $extra_settings['hide_metabox_post_revisions'];
			$settings['child_hide_metabox_post_discussion']    = $extra_settings['hide_metabox_post_discussion'];
			$settings['child_hide_metabox_post_categories']    = $extra_settings['hide_metabox_post_categories'];
			$settings['child_hide_metabox_post_custom_fields'] = $extra_settings['hide_metabox_post_custom_fields'];
			$settings['child_hide_metabox_post_trackbacks']    = $extra_settings['hide_metabox_post_trackbacks'];

			$settings['child_hide_metabox_page_custom_fields'] = $extra_settings['hide_metabox_page_custom_fields'];
			$settings['child_hide_metabox_page_author']        = $extra_settings['hide_metabox_page_author'];
			$settings['child_hide_metabox_page_discussion']    = $extra_settings['hide_metabox_page_discussion'];
			$settings['child_hide_metabox_page_revisions']     = $extra_settings['hide_metabox_page_revisions'];
			$settings['child_hide_metabox_page_attributes']    = $extra_settings['hide_metabox_page_attributes'];
			$settings['child_hide_metabox_page_slug']          = $extra_settings['hide_metabox_page_slug'];
			$settings['child_preserve_branding']               = $extra_settings['preserve_branding'];

			$post_data['specical'] = true;
		} else {
			$settings = $this->option;
		}

		if ( isset( $settings['child_login_image'] ) && ! empty( $settings['child_login_image'] ) ) {
			$settings['child_login_image_url'] = $settings['child_login_image'];
		} else {
			$settings['child_login_image_url'] = '';
		}

		if ( isset( $settings['child_favico_image'] ) && ! empty( $settings['child_favico_image'] ) ) {
			$settings['child_favico_image_url'] = $settings['child_favico_image'];
		} else {
			$settings['child_favico_image_url'] = '';
		}

		if ( isset( $settings['child_admin_css'] ) ) {
			$style                       = stripslashes( $settings['child_admin_css'] );
			$style                       = preg_replace( '|^[\s]*<script>|', '', $style );
			$style                       = preg_replace( '|<\/script>[\s]*$|', '', $style );
			$style                       = trim( $style );
			$settings['child_admin_css'] = $style;
		}

		if ( isset( $settings['child_login_css'] ) ) {
			$style                       = stripslashes( $settings['child_login_css'] );
			$style                       = preg_replace( '/^[\s]*<script>/', '', $style );
			$style                       = preg_replace( '/<\/script>[\s]*$/', '', $style );
			$style                       = trim( $style );
			$settings['child_login_css'] = $style;
		}

		// this is post from site edit page
		if ( is_object( $branding ) ) {
			$settings['child_disable_wp_branding'] = $extra_settings['disable_wp_branding'] ? 'Y' : 'N';
		}

		$post_data['settings'] = base64_encode( serialize( $settings ) );

		$information = apply_filters( 'mainwp_fetchurlauthed', $mainWPBrandingExtensionActivator->get_child_file(), $mainWPBrandingExtensionActivator->get_child_key(), $siteid, 'branding_child_plugin', $post_data );
		die( json_encode( $information ) );
	}
}
