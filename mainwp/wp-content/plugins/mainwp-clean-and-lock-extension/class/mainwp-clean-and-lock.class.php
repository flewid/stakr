<?php

class MainWP_Clean_And_Lock {

	public static $instance = null;
	public $plugin_handle = 'mainwp_cal_nonce';
	public $option_handle = 'mainwp_cal_options';
	public $option = array();

	static function get_instance() {
		if ( null === MainWP_Clean_And_Lock::$instance ) {
			MainWP_Clean_And_Lock::$instance = new MainWP_Clean_And_Lock(); }
		return MainWP_Clean_And_Lock::$instance;
	}

	public function __construct() {
		$this->option = get_option( $this->option_handle );
		add_action( 'admin_menu', array( $this, 'remove_menus' ) );
		add_filter( 'login_redirect', array( $this, 'redirect_dashboard' ), 999, 3 );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function init() {
		$redirect_url = $this->get_option( 'redirect_url', '' );
		if ( ! empty( $redirect_url ) ) {
			if ( ! is_admin() && (strpos( $_SERVER['REQUEST_URI'], $redirect_url ) === false) && (strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) === false) && (strpos( $_SERVER['REQUEST_URI'], 'cron' ) === false) ) {
				header( 'Location: ' . $redirect_url, true, 301 );
				exit();
			}
		}
	}

	public function admin_init() {
		$_pos = strlen( $_SERVER['REQUEST_URI'] ) - strlen( '/wp-admin/' );
		$hide_menus = MainWP_Clean_And_Lock::get_instance()->get_option( 'hide_wpmenu', array() );
		if ( ! is_array( $hide_menus ) ) {
			$hide_menus = array(); }
		$hide_wp_dashboard = in_array( 'dashboard', $hide_menus );
		if ( ($hide_wp_dashboard && strpos( $_SERVER['REQUEST_URI'], 'index.php' )) || (strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) !== false && strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) == $_pos) ) {
			wp_redirect( admin_url( 'admin.php?page=mainwp_tab' ) );
			die();
		}
		$this->handle_post_settings();
	}

	function redirect_dashboard( $redirect_to = '', $request = null, $user = false ) {
		if ( $user instanceof WP_User && $user->ID ) {
			$login_url = admin_url( 'admin.php?page=mainwp_tab' );
		} else {
			$login_url = $redirect_to;
		}
		return $login_url;
	}

	public function remove_menus() {
		$this->option = get_option( $this->option_handle ); // reload options
		$hide_menus = $this->get_option( 'hide_wpmenu', array() );
		if ( ! is_array( $hide_menus ) ) {
			$hide_menus = array(); }

		$menus_slug = array(
			'dashboard' => 'index.php',
			'posts' => 'edit.php',
			'media' => 'upload.php',
			'pages' => 'edit.php?post_type=page',
			'appearance' => 'themes.php',
			'comments' => 'edit-comments.php',
			'users' => 'users.php',
			'tools' => 'tools.php',
		);

		foreach ( $hide_menus as $menu ) {
			if ( isset( $menus_slug[ $menu ] ) ) {
				remove_menu_page( $menus_slug[ $menu ] );
			}
		}
	}

	function mod_rewrite_rules( $pRules ) {
		$home_root = parse_url( home_url() );
		if ( isset( $home_root['path'] ) ) {
			$home_root = trailingslashit( $home_root['path'] ); } else {
			$home_root = '/'; }

			$rules = "<IfModule mod_rewrite.c>\n";
			$rules .= "RewriteEngine On\n";
			$rules .= "RewriteBase $home_root\n";

			foreach ( $pRules as $value ) {
				if ( ! empty( $value['query'] ) ) {
					$rules .= $value['rule'] . ' ' . $value['match'] . ' ' . $value['query'] . "\n"; }
			}

			$rules .= "</IfModule>\n";

			return $rules;
	}

	public static function clear_rewrite_htaccess() {
		include_once( ABSPATH . '/wp-admin/includes/misc.php' );
		$home_path = ABSPATH;
		$htaccess_file = $home_path . '.htaccess';
		if ( function_exists( 'save_mod_rewrite_rules' ) ) {
			$rules = explode( "\n", '' );
			insert_with_markers( $htaccess_file, 'MainWP Secure and Clean.', $rules );
		}
	}

	public function update_rewrite_htaccess( $force_clear = false ) {

		$allow_ips_address = $this->get_option( 'allow_ips_address', '' );
		//        $trusted_refers = $this->get_option("trusted_refers", "");

		$rulesRewrite = array();
		if ( ! empty( $allow_ips_address ) || ! empty( $trusted_refers ) ) {
			include_once( ABSPATH . '/wp-admin/includes/misc.php' );

			$allow_ips_address = ! empty( $allow_ips_address ) ? explode( "\n", trim( $allow_ips_address ) ) : array();
			//            $trusted_refers = !empty($trusted_refers) ? explode("\n", $trusted_refers) : array();

			if ( count( $allow_ips_address ) > 0 ) {
				$rulesRewrite[] = array( 'rule' => 'RewriteCond', 'match' => '%{REQUEST_URI}', 'query' => '^(.*)?wp-login\.php(.*)$ [OR]' );
				$rulesRewrite[] = array( 'rule' => 'RewriteCond', 'match' => '%{REQUEST_URI}', 'query' => '^(.*)?wp-admin$' );
				foreach ( $allow_ips_address as $ip ) {
					if ( ! empty( $ip ) ) {
						$ip = str_replace( '.', '\.', trim( $ip ) );
						$rulesRewrite[] = array( 'rule' => 'RewriteCond', 'match' => '%{REMOTE_ADDR}', 'query' => '!^' . $ip . '$' );
					}
				}
				$rulesRewrite[] = array( 'rule' => 'RewriteRule', 'match' => '^(.*)$', 'query' => '- [R=403,L]' );
			}

			//            if (count($trusted_refers) > 0) {
			//                $rulesRewrite[] = array("rule" => "RewriteCond", "match" => "%{REQUEST_METHOD}" ,"query" => "POST");
			//                foreach($trusted_refers as $refer) {
			//                    if (!empty($refer)) {
			//                        if (preg_match('/(https?:\/\/)?(.+)/', $refer, $matchs)) {
			//                            $_query = "";
			//                            $_refer = str_replace(".", "\.", trim($matchs[2]));
			//                            if (empty($matchs[1])) {
			//                                $_query = "!^http(s)?://(.*)?" . $_refer . " [NC]";
			//                            } else
			//                                $_query = "!^" . $matchs[1] . "(.*)?" . $_refer . " [NC]";
			//                            $rulesRewrite[] = array("rule" => "RewriteCond", "match" => "%{HTTP_REFERER}", "query" => $_query);
			//                        }
			//                    }
			//                }
			//                $rulesRewrite[] = array("rule" => "RewriteCond", "match" => "%{REQUEST_URI}" ,"query" => "^(.*)?wp-login\.php(.*)$ [OR]");
			//                $rulesRewrite[] = array("rule" => "RewriteCond", "match" => "%{REQUEST_URI}" ,"query" => "^(.*)?wp-admin$");
			//                $rulesRewrite[] = array("rule" => "RewriteRule", "match" => "^(.*)$", "query" => "- [F]");
			//            }
		}

		if ( ! empty( $rulesRewrite ) ) {
			//Create rewrite ruler
			$rules = $this->mod_rewrite_rules( $rulesRewrite );
			$home_path = ABSPATH;
			$htaccess_file = $home_path . '.htaccess';
			if ( function_exists( 'save_mod_rewrite_rules' ) ) {
				$rules_arr = explode( "\n", $rules );
				insert_with_markers( $htaccess_file, 'MainWP Secure and Clean.', $rules_arr ); // dont remove "." it will generate a strange bug
			}
		} else {
			self::clear_rewrite_htaccess();
		}

		return true;
	}

	function get_system_name() {
		$name = php_uname();
		$sys_name = 'other';
		if ( stripos( $name, 'windows' ) === 0 ) {
			$sys_name = 'windows';
		} else if ( stripos( $name, 'linux' ) === 0 ) {
			$sys_name = 'linux';
		}
		return $sys_name;
	}

	public function update_authen_htaccess( $force_clear = false ) {
		include_once( ABSPATH . '/wp-admin/includes/misc.php' );
		$home_path = ABSPATH;
		if ( function_exists( 'save_mod_rewrite_rules' ) ) {
			$wpadmin_user = $this->get_option( 'wpadmin_user', '' );
			$wpadmin_pass = $this->get_option( 'wpadmin_passwd', '' );

			$htaccess_file1 = $home_path . 'wp-admin/.htaccess';
			$htpasswd_file1 = $home_path . 'wp-admin/.htpasswd';
			$session_name1 = 'MainWP Secure and Clean - Apache Password Protect wp-admin';
			$sys = $this->get_system_name();
			if ( ! empty( $wpadmin_user ) && ! empty( $wpadmin_pass ) ) {
				$rules_str = 'AuthUserFile "' . $htpasswd_file1 . "\"\n";
				$rules_str .= "AuthName \"Please, enter your WP-Admin Username and Password\"\n";
				$rules_str .= "AuthType Basic\n";
				$rules_str .= "<Limit GET POST>\n";
				$rules_str .= "require valid-user\n";
				$rules_str .= "</Limit>\n";

				$rules = explode( "\n", $rules_str );
				insert_with_markers( $htaccess_file1, $session_name1, $rules );
				$authen_pass = $wpadmin_pass;
				if ( 'linux' === $sys ) {
					$authen_pass = crypt( $wpadmin_pass, substr( $wpadmin_pass, 0, 2 ) ); }
				$passwd_str = $wpadmin_user . ':' . $authen_pass;
				$fopen = fopen( $htpasswd_file1, 'w+' );
				fwrite( $fopen, $passwd_str );
				fclose( $fopen );
			} else {
				$rules = explode( "\n", '' );
				insert_with_markers( $htaccess_file1, $session_name1, $rules );
			}

			$wplogin_user = $this->get_option( 'wplogin_user', '' );
			$wplogin_pass = $this->get_option( 'wplogin_passwd', '' );

			$htaccess_file2 = $home_path . '.htaccess';
			$htpasswd_file2 = $home_path . '.htpasswd';
			$session_name2 = 'MainWP Secure and Clean - Apache Password Protect wp-login.php';
			if ( ! empty( $wplogin_user ) && ! empty( $wplogin_pass ) ) {
				$rules_str = 'AuthUserFile "' . $htpasswd_file2 . "\"\n";
				$rules_str .= "AuthName \"Please, enter your wp-login.php Username and Password\"\n";
				$rules_str .= "AuthType Basic\n";
				$rules_str .= "<Files wp-login.php>\n";
				$rules_str .= "require valid-user\n";
				$rules_str .= "</Files>\n";

				$rules = explode( "\n", $rules_str );
				insert_with_markers( $htaccess_file2, $session_name2, $rules );
				$authen_pass = $wplogin_pass;
				if ( 'linux' === $sys ) {
					$authen_pass = crypt( $wplogin_pass, substr( $wplogin_pass, 0, 2 ) ); }
				$passwd_str = $wplogin_user . ':' . $authen_pass;
				$fopen = fopen( $htpasswd_file2, 'w+' );
				fwrite( $fopen, $passwd_str );
				fclose( $fopen );
			} else {
				$rules = explode( "\n", '' );
				insert_with_markers( $htaccess_file2, $session_name2, $rules );
			}
		}
		return true;
	}

	public function get_option( $key = null, $default = '' ) {
		if ( isset( $this->option[ $key ] ) ) {
			return $this->option[ $key ]; }
		return $default;
	}

	public function set_option( $key, $value ) {
		$this->option[ $key ] = $value;
		return update_option( $this->option_handle, $this->option );
	}

	public function handle_post_settings() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'cal_save_settings' ) ) {
			if ( isset( $_POST['cal-save-settings-btn'] ) ) {
				$this->set_option( 'redirect_url', trim( $_POST['mwp-cal-setting-redirect-url'] ) );

				$ips_address = array();
				$allow_ips_address = trim( $_POST['cal_allow_login_from_ip_address'] );
				$allow_ips_address = ! empty( $allow_ips_address ) ? explode( "\n", $allow_ips_address ) : array();
				foreach ( $allow_ips_address as $ip ) {
					$ip = trim( $ip );
					if ( ! empty( $ip ) ) {
						$ips_address[] = $ip; }
				}
				$txt_ips_address = count( $ips_address ) > 0 ? implode( "\n", $ips_address ) : '';
				$this->set_option( 'allow_ips_address', $txt_ips_address );

				//            $refers = array();
				//            $trusted_refers = trim($_POST['cal_trusted_refers']);
				//            $trusted_refers = !empty($trusted_refers) ? explode("\n", $trusted_refers) : array();
				//            foreach($trusted_refers as $refer) {
				//                $refer = trim($refer);
				//                if (!empty($refer))
				//                    $refers[] = $refer;
				//            }
				//            $txt_refers = count($refers) > 0 ? implode("\n", $refers) : "";
				//            $this->set_option('trusted_refers', $txt_refers);

				$this->set_option( 'wpadmin_user', sanitize_text_field( $_POST['cal_wpadmin_lock_user_name'] ) );
				//$password_hashed1 = wp_hash_password($_POST['cal_wpadmin_lock_passwd']);
				$password_hashed1 = trim( $_POST['cal_wpadmin_lock_passwd'] );
				$this->set_option( 'wpadmin_passwd', $password_hashed1 );

				$this->set_option( 'wplogin_user', sanitize_text_field( $_POST['cal_wplogin_lock_user_name'] ) );
				//$password_hashed2 = wp_hash_password($_POST['cal_wplogin_lock_passwd']);
				$password_hashed2 = trim( $_POST['cal_wplogin_lock_passwd'] );
				$this->set_option( 'wplogin_passwd', $password_hashed2 );

				$hide_menus = array();
				if ( isset( $_POST['cal_hide_wpmenu'] ) && is_array( $_POST['cal_hide_wpmenu'] ) && count( $_POST['cal_hide_wpmenu'] ) > 0 ) {
					foreach ( $_POST['cal_hide_wpmenu'] as $value ) {
						$hide_menus[] = $value;
					}
				}
				$this->set_option( 'hide_wpmenu', $hide_menus );
			} else if ( isset( $_POST['cal-unlock-btn'] ) ) {
				$this->set_option( 'allow_ips_address', '' );
				$this->set_option( 'wpadmin_user', '' );
				$this->set_option( 'wpadmin_passwd', '' );
				$this->set_option( 'wplogin_user', '' );
				$this->set_option( 'wplogin_passwd', '' );
			}
			$this->option = get_option( $this->option_handle ); // reload options
			$this->update_rewrite_htaccess();
			$this->update_authen_htaccess();
			wp_redirect( admin_url( 'admin.php?page=Extensions-Mainwp-Clean-And-Lock-Extension&message=1' ) );
			die();
		}
		return false;
	}

	public static function render() {
		self::render_qsg();
		$message = '';
		if ( isset( $_GET['message'] ) && 1 == $_GET['message'] ) {
			$message = __( 'Settings saved.' );
		}

		$redirect_url = MainWP_Clean_And_Lock::get_instance()->get_option( 'redirect_url', '' );
		$allow_ip_login = MainWP_Clean_And_Lock::get_instance()->get_option( 'allow_ips_address', '' );
		//        $trusted_refers = MainWP_Clean_And_Lock::get_instance()->get_option("trusted_refers", "");
		$wpadmin_user = MainWP_Clean_And_Lock::get_instance()->get_option( 'wpadmin_user', '' );
		$wpadmin_pass = MainWP_Clean_And_Lock::get_instance()->get_option( 'wpadmin_passwd', '' );
		$wplogin_user = MainWP_Clean_And_Lock::get_instance()->get_option( 'wplogin_user', '' );
		$wplogin_pass = MainWP_Clean_And_Lock::get_instance()->get_option( 'wplogin_passwd', '' );
		$hide_menus = MainWP_Clean_And_Lock::get_instance()->get_option( 'hide_wpmenu', array() );

		if ( ! is_array( $hide_menus ) ) {
			$hide_menus = array(); }
		?>
        <form method="post" action="admin.php?page=Extensions-Mainwp-Clean-And-Lock-Extension">        
			<?php if ( ! empty( $message ) ) { ?>
				<div class="mainwp_info-box-yellow"><?php echo $message; ?></div>
			<?php }
			?>
            <div id="mainwp_aup_ajax_message_zone" class="mainwp_info-box-yellow hidden"></div>
            <div id="mainwp_aup_ajax_error_zone" class="mainwp_info-box-red hidden"></div>
            <fieldset class="mainwp-secure-and-clear-box">   
                <table class="wp-list-table widefat" cellspacing="0">
                    <thead>
                        <tr>          
                            <th scope="col" colspan="2">
								<strong><i class="fa fa-cog"></i> <?php _e( '301 Redirect', 'mainwp-clean-and-lock-extension' ); ?></strong>
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th style="border:none !important" colspan="2">&nbsp;</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
							<th class="title"><span><?php _e( '301 Redirect URL', 'mainwp-clean-and-lock-extension' ); ?></span></th>
							<td><input type="text" name="mwp-cal-setting-redirect-url" value="<?php echo htmlspecialchars( stripslashes( $redirect_url ) ); ?>">
								<br><span class="description"><?php _e( 'Enter URL where you want to redirect all not-wp-admin pages of your dashboard site.', 'mainwp-clean-and-lock-extension' ); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>         
            </fieldset>
            <br>       
            <fieldset class="mainwp-secure-and-clear-box">   
                <table class="wp-list-table widefat" cellspacing="0">
                    <thead>
                        <tr>          
                            <th scope="col" colspan="2">
								<strong><i class="fa fa-cog"></i> <?php _e( 'Dashboard Lock Down', 'mainwp-clean-and-lock-extension' ); ?></strong>
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th style="border:none !important" colspan="2">&nbsp;</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
							<th class="title"><span><?php _e( 'Allow Login from' ); ?> <?php do_action( 'mainwp_renderToolTip', __( 'This feature will allow login only from whitelisted IP Addresses', 'mainwp-clean-and-lock-extension' ) ); ?></span></th>
                            <td>
                                <table>
                                    <tr>
                                        <td>
											<textarea id="cal_allow_login_from_ip_address" style="float:left" name="cal_allow_login_from_ip_address" cols="30" rows="5"><?php echo esc_textarea( $allow_ip_login ); ?></textarea>
											<br class="clearfix"><span class="description"><?php _e( 'Enter IP Address you want to allow. Enter 1 Address per row.', 'mainwp-clean-and-lock-extension' ); ?></span>
                                        </td>
                                        <td>
											<div style="margin-top: 1em; text-align: center !important; font-size: 18px;"><?php echo __( 'We detect your current IP to be:', 'mainwp-clean-and-lock-extension' ) . "<br/><br/><span style='font-size: 30px; color: #ddd;'>" . $_SERVER['REMOTE_ADDR'] . '</span>'; ?></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <th scope="col" colspan="2" style="border-bottom: 1px dotted #eeeeee;">&nbsp;</th>
                        </tr>
                        <tr>
                            <th scope="col" colspan="2">&nbsp;</th>
                        </tr>
                        <tr>
							<th class="title"><span><?php _e( 'WP-Admin Lock', 'mainwp-clean-and-lock-extension' ); ?> <?php do_action( 'mainwp_renderToolTip', __( 'This feature will password protect WordPress Admin (/wp-admin/) area.', 'mainwp-clean-and-lock-extension' ) ); ?></span></th>
                            <td>
                                <div>
                                    <div class="cal-setting-cell">       
										<input type="text" placeholder="<?php _e( 'Username', 'mainwp-clean-and-lock-extension' ); ?>" value="<?php echo htmlspecialchars( stripslashes( $wpadmin_user ) ); ?>" name="cal_wpadmin_lock_user_name" id="cal_wpadmin_lock_user_name">
										<br><span class="description"><?php _e( 'User for wp-admin folder', 'mainwp-clean-and-lock-extension' ); ?></span> 
                                    </div>                                      
                                    <div class="cal-setting-cell"> 
										<input type="text" placeholder="<?php _e( 'Password', 'mainwp-clean-and-lock-extension' ); ?>" maxlength="18" autocomplete="off" value="<?php echo htmlspecialchars( stripslashes( $wpadmin_pass ) ); ?>" name="cal_wpadmin_lock_passwd" id="cal_wpadmin_lock_passwd">
										<br><span class="description"><?php _e( 'The Password for the wp-admin folder', 'mainwp-clean-and-lock-extension' ); ?></span>                             
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
							<th class="title"><span><?php _e( 'WP-Login.php Lock' ); ?> <?php do_action( 'mainwp_renderToolTip', __( 'This feature will password protect WordPress Login (/wp-login.php/) page.', 'mainwp-clean-and-lock-extension' ) ); ?></span></th>
                            <td>
                                <div>
                                    <div class="cal-setting-cell">       
										<input type="text" placeholder="<?php _e( 'Username', 'mainwp-clean-and-lock-extension' ); ?>" value="<?php echo htmlspecialchars( stripslashes( $wplogin_user ) ); ?>" name="cal_wplogin_lock_user_name" id="cal_wplogin_lock_user_name">
										<br><span class="description"><?php _e( 'User for the file wp-login.php', 'mainwp-clean-and-lock-extension' ); ?></span> 
                                    </div>                                      
                                    <div class="cal-setting-cell"> 
										<input type="text" placeholder="<?php _e( 'Password', 'mainwp-clean-and-lock-extension' ); ?>" maxlength="18" autocomplete="off" value="<?php echo htmlspecialchars( stripslashes( $wplogin_pass ) ); ?>" name="cal_wplogin_lock_passwd" id="cal_wplogin_lock_passwd">
										<br><span class="description"><?php _e( 'The Password for the file wp-login.php', 'mainwp-clean-and-lock-extension' ); ?></span>                             
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>          
                            <th scope="col" colspan="2" style="border-bottom: 1px dotted #eeeeee;">&nbsp;</th>
                        </tr>
                        <tr>
                            <th scope="col" colspan="2">&nbsp;</th>
                        </tr>
                        <tr>          
                            <th scope="col" colspan="2">
					<div  class="mainwp_info-box-yellow"><?php _e( 'Use the Unlock button to unlock your Dashboard site. It will remove all restrictions from your .htaccess files (Allowed IP Addresses, WP-Admin and WP-login.php locks)', 'mainwp-clean-and-lock-extension' ); ?></div>
                    </th>
                    </tr>
                    <tr>
						<th class="title"><span><?php _e( 'Unlock Dashboard', 'mainwp-clean-and-lock-extension' ); ?></span></th>
                        <td>
							<input type="submit" value="<?php _e( 'Unlock', 'mainwp-clean-and-lock-extension' ); ?>" class="button-primary" name="cal-unlock-btn">
                        </td>
                    </tr>
                    </tbody>
                </table>         
            </fieldset>
            <br>
			<?php
			$wp_menu_items = array(
				'dashboard' => __( 'Dashboard', 'mainwp-clean-and-lock-extension' ),
				'posts' => __( 'Posts', 'mainwp-clean-and-lock-extension' ),
				'media' => __( 'Media', 'mainwp-clean-and-lock-extension' ),
				'pages' => __( 'Pages' ),
				'appearance' => __( 'Appearance', 'mainwp-clean-and-lock-extension' ),
				'comments' => __( 'Comments', 'mainwp-clean-and-lock-extension' ),
				'users' => __( 'Users', 'mainwp-clean-and-lock-extension' ),
				'tools' => __( 'Tools', 'mainwp-clean-and-lock-extension' ),
			);
			?>
            <fieldset class="mainwp-secure-and-clear-box">   
                <table class="wp-list-table widefat" cellspacing="0">
                    <thead>
                        <tr>          
                            <th scope="col" colspan="2">
								<strong><i class="fa fa-cog"></i> <?php _e( 'Dashboard Site Cleanup', 'mainwp-clean-and-lock-extension' ); ?></strong>
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th style="border:none !important" colspan="2">&nbsp;</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
							<th class="title"><span><?php _e( 'Hide WP Menus', 'mainwp-clean-and-lock-extension' ); ?></span></th>
                            <td>
                                <ul class="cal_checkboxes">
									<?php
									foreach ( $wp_menu_items as $name => $item ) {
										$_selected = '';
										if ( in_array( $name, $hide_menus ) ) {
											$_selected = 'checked'; }
										?>
										<li><input type="checkbox" name="cal_hide_wpmenu[]" <?php echo $_selected; ?> value="<?php echo $name; ?>"> <?php echo $item; ?></li>
									<?php }
									?>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>         
            </fieldset>
            <p class="submit">                                    
				<input type="submit" name="cal-save-settings-btn" id="cal-save-settings-btn" class="button-primary" value="<?php _e( 'Save Settings', 'mainwp-clean-and-lock-extension' ); ?>">                                        
            </p>
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'cal_save_settings' ); ?>" />
        </form>
		<?php
	}

	public static function render_qsg() {
		$plugin_data = get_plugin_data( MAINWP_CLEAN_AND_LOCK_PLUGIN_FILE, false );
		$description = $plugin_data['Description'];
		$extraHeaders = array( 'DocumentationURI' => 'Documentation URI' );
		$file_data = get_file_data( MAINWP_CLEAN_AND_LOCK_PLUGIN_FILE, $extraHeaders );
		$documentation_url = $file_data['DocumentationURI'];
		?>
        <div  class="mainwp_ext_info_box" id="pd-pth-notice-box">
			<div class="mainwp-ext-description"><?php echo $description; ?></div><br/>
			<b><?php echo __( 'Need Help?' ); ?></b> <?php echo __( 'Review the Extension' ); ?> <a href="<?php echo $documentation_url; ?>" target="_blank"><i class="fa fa-book"></i> <?php echo __( 'Documentation' ); ?></a>. 
			<a href="#" id="mainwp-cal-quick-start-guide"><i class="fa fa-info-circle"></i> <?php _e( 'Show Quick Start Guide', 'mainwp' ); ?></a></div>
        <div  class="mainwp_ext_info_box" id="mainwp-cal-tips" style="color: #333!important; text-shadow: none!important;">
			<span><a href="#" class="mainwp-show-cal-tut" number="1"><i class="fa fa-book"></i> <?php _e( '301 Redirects', 'mainwp' ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-cal-tut"  number="2"><i class="fa fa-book"></i> <?php _e( 'Dashboard Lock Down', 'mainwp' ) ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-cal-tut"  number="3"><i class="fa fa-book"></i> <?php _e( 'Dashboard Site Clean Up', 'mainwp' ) ?></a></span><span><a href="#" id="mainwp-cal-tips-dismiss" style="float: right;"><i class="fa fa-times-circle"></i> <?php _e( 'Dismiss', 'mainwp' ); ?></a></span>
            <div class="clear"></div>
            <div id="mainwp-cal-tuts">
                <div class="mainwp-cal-tut" number="1">
                    <h3>301 Redirects</h3>         
                    <p>This feature enables you to redirect all visits on not-wp-admin pages of your dashboard site. Enter an URL Address where you want to redirect visitors and click the Save Settings button.</p> 
                    <p><strong>This may affect your wp-cron function. If you notice scheduled functions are not working please remove this redirect.</strong></p>                  
                </div>
                <div class="mainwp-cal-tut"  number="2">
                    <h3>Dashboard Lock Down</h3>
                    <p>To limit login access to certain IP address(es), add IP address(es) in the Allow Login From field</p>
                    <p>To add Username and Password to wp-admin pages enter wanted username and password in the WP-Admin Lock set of fields.</p>
                    <p>To add Username and Password to wp-login.php file enter wanted username and password in the WP-Login.php Lock set of fields.</p>
                    <p>Click the Save Settings button.</p>
                </div>
                <div class="mainwp-cal-tut"  number="3">
                    <h3>Dashboard Site Clean Up</h3>
                    <p>The MainWP Clean and Lock extension enables you to remove unwanted WordPress menus from the dashboard backend.</p>
                    <ol>
                        <li>At the bottom locate the Dashboard Site Cleanup section</li>
                        <li>Check all menus you want to remove</li>
                        <li>Click Save Settings button.</li>
                    </ol>
                </div>
            </div>
        </div>
		<?php
	}
}
