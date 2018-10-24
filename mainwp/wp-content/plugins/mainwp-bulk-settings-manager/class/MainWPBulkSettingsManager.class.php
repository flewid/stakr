<?php

class MainWPBulkSettingsManager {
	public static $plugin_translate = "bulk_settings_manager_extension";
	public static $nonce_token = "bulk_settings_manager_nonce_";
	public static $messages = array();
	public static $error_messages = array();
	private static $instance = null;
	public $plugin_handle = "bulk_settings_manager";
	protected $child_version = "0.1";

	public function __construct() {
		global $pagenow;

		if ( is_admin() ) {
			// Add our scripts only on plugin extension page
			if ( $pagenow == 'admin.php' && isset( $_REQUEST['page'] ) && strcasecmp( $_REQUEST['page'], 'Extensions-Mainwp-Bulk-Settings-Manager' ) === 0 ) {
				add_action( 'init', array( $this, 'init' ) );
			}

			// Ajax requests
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_list', array( $this, 'ajax_list' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_save', array( $this, 'ajax_save' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_delete', array( $this, 'ajax_delete' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_send_to_child', array( $this, 'ajax_send_to_child' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_send_to_child_step_2', array(
				$this,
				'ajax_send_to_child_step_2'
			) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_export', array( $this, 'ajax_export' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_preview', array( $this, 'ajax_preview' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_save_notes', array( $this, 'ajax_save_notes' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_load_notes', array( $this, 'ajax_load_notes' ) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_keyring_entries', array(
				$this,
				'ajax_keyring_entries'
			) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_save_keyring_name', array(
				$this,
				'ajax_save_keyring_name'
			) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_delete_entry_from_keyring', array(
				$this,
				'ajax_delete_entry_from_keyring'
			) );
			add_action( 'wp_ajax_mainwp_bulk_settings_manager_settings', array( $this, 'ajax_settings' ) );
		}
	}

	public static function Instance() {
		if ( MainWPBulkSettingsManager::$instance == null ) {
			MainWPBulkSettingsManager::$instance = new MainWPBulkSettingsManager();
		}

		return MainWPBulkSettingsManager::$instance;
	}

	/**
	 * @return bool
	 *
	 * Check if user can access this plugin
	 */
	public function check_permissions() {
		if ( has_filter( "mainwp_currentusercan" ) ) {
			if ( ! apply_filters( "mainwp_currentusercan", true, "extension", "mainwp-bulk-settings-manager" ) ) {

				return false;
			}
		} else {
			if ( ! current_user_can( "manage_options" ) ) {

				return false;
			}
		}

		return true;
	}

	/**
	 * Set new name for keyring
	 */
	public function ajax_save_keyring_name() {
		$this->ajax_check_permissions( 'save_keyring_name' );

		$id   = ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );
		$name = ( isset( $_POST['name'] ) ? trim( (string) $_POST['name'] ) : '' );

		$keyring = MainWPBulkSettingsManagerDB::Instance()->get_key_ring_by_id( $id );

		if ( empty( $keyring ) ) {
			$this->json_error( __( "This keyring does not exist", self::$plugin_translate ) );
		}

		if ( MainWPBulkSettingsManagerDB::Instance()->update_keyring_name( $id, $name ) === false ) {
			$this->json_error( __( "Cannot change keyring name", self::$plugin_translate ) );
		}

		$this->json_ok();
	}

	/**
	 * Display all entries displayed as ajax grid
	 */
	public function ajax_list() {
		$this->ajax_check_permissions( 'list' );

		$type = ( isset( $_POST['type'] ) ? (string) $_POST['type'] : '' );
		$page = ( isset( $_GET['page'] ) ? ( intval( $_GET['page'] ) - 1 ) : 0 );
		if ( $page < 0 ) {
			$page = 0;
		}
		// Its our limit
		$limit      = ( isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 10 );
		$sort_by    = ( isset( $_GET['sort-by'] ) ? (string) $_GET['sort-by'] : 'name' );
		$sort_order = ( isset( $_GET['sort-order'] ) && (string) $_GET['sort-order'] == 'asc' ? 'asc' : 'desc' );
		$name       = ( isset( $_GET['name'] ) ? trim( (string) $_GET['name'] ) : '' );

		switch ( $type ) {
			case 'entry':
				$entry_sort   = array( 'name', 'url', 'created_time' );
				$test_sort_by = array_search( $sort_by, $entry_sort );
				if ( $test_sort_by !== false ) {
					$sort_by = $entry_sort[ $test_sort_by ];
				} else {
					$sort_by = $entry_sort[0];
				}

				$entries = MainWPBulkSettingsManagerDB::Instance()->get_id_name_url_from_all_entries( $limit, $page * $limit, $sort_by, $sort_order, $name );
				$count   = MainWPBulkSettingsManagerDB::Instance()->get_all_entries_count( $name );
				break;

			case 'keyring':
				$keyring_sort = array( 'name' );
				$test_sort_by = array_search( $sort_by, $keyring_sort );
				if ( $test_sort_by !== false ) {
					$sort_by = $keyring_sort[ $test_sort_by ];
				} else {
					$sort_by = $keyring_sort[0];
				}

				$entries = MainWPBulkSettingsManagerDB::Instance()->get_key_rings( $limit, $page * $limit, $sort_by, $sort_order, $name );
				$count   = MainWPBulkSettingsManagerDB::Instance()->get_key_rings_count( $name );
				break;

			case 'history':
				$history_sort = array( 'entry_id', 'created_time' );
				$test_sort_by = array_search( $sort_by, $history_sort );
				if ( $test_sort_by !== false ) {
					$sort_by = $history_sort[ $test_sort_by ];
				} else {
					$sort_by = $history_sort[0];
				}

				$entries = MainWPBulkSettingsManagerDB::Instance()->get_history( $limit, $page * $limit, $sort_by, $sort_order );
				$count   = MainWPBulkSettingsManagerDB::Instance()->get_history_count();
				break;

			default:
				$this->json_error( __( "Wrong type for ajax_list", self::$plugin_translate ) );
		}

		$entries_array = array();
		foreach ( $entries as $entry ) {
			$temp_entry = $entry;

			if ( isset( $temp_entry['created_time'] ) ) {
				$temp_entry['created_time'] = $this->format_timestamp( $entry['created_time'] );
			}

			if ( isset( $temp_entry['edited_time'] ) ) {
				$temp_entry['edited_time'] = $this->format_timestamp( $entry['edited_time'] );
			}

			$entries_array[] = $temp_entry;
		}

		$this->json_ok( null, array(
			'entries'    => $entries_array,
			'pagination' => array(
				'count' => $limit,
				'page'  => ( $page + 1 ),
				'pages' => ceil( $count['c'] / $limit ),
				'size'  => $count['c']
			)
		) );
	}

	/**
	 * @param $timestamp
	 *
	 * @return string
	 *
	 * Return time in user format
	 */
	private function format_timestamp( $timestamp ) {
		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
	}

	/**
	 * Checking permission
	 * If Team Control is installed - check extension priveleges
	 * Other case - check manage_options
	 * Additionally verify nonce token
	 */
	protected function ajax_check_permissions( $action, $get = false ) {
		if ( has_filter( "mainwp_currentusercan" ) ) {
			if ( ! apply_filters( "mainwp_currentusercan", true, "extension", "mainwp-bulk-settings-manager" ) ) {
				$this->json_error( mainwp_do_not_have_permissions( "MainWP Bulk Settings Manager Extension " . esc_html( $action ), false ) );
			}
		} else {
			if ( ! current_user_can( "manage_options" ) ) {
				$this->json_error( mainwp_do_not_have_permissions( "MainWP Bulk Settings Manager Extension " . esc_html( $action ), false ) );
			}
		}

		if ( $get ) {
			if ( ! isset( $_GET['wp_nonce'] ) || ! wp_verify_nonce( $_GET['wp_nonce'], MainWPBulkSettingsManager::$nonce_token . $action ) ) {
				$this->json_error( __( 'Error: Wrong or expired request. Please reload page', self::$plugin_translate ) );
			}
		} else {
			if ( ! isset( $_POST['wp_nonce'] ) || ! wp_verify_nonce( $_POST['wp_nonce'], MainWPBulkSettingsManager::$nonce_token . $action ) ) {
				$this->json_error( __( 'Error: Wrong or expired request. Please reload page', self::$plugin_translate ) );
			}
		}
	}

	/**
	 * @param $error
	 *
	 * Send error message through json
	 */
	public function json_error( $error ) {
		die( wp_send_json( array( 'error' => $error ) ) );
	}

	/**
	 * @param null $message
	 * @param null $data
	 * @param bool|true $die
	 *
	 * Send json ok message
	 */
	public function json_ok( $message = null, $data = null, $die = true ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( is_null( $data ) ) {
			if ( is_null( $message ) ) {
				echo json_encode( array( 'success' => 1 ) );
			} else {
				echo json_encode( array( 'success' => $message ) );
			}
		} else {
			if ( is_null( $message ) ) {
				echo json_encode( array( 'success' => 1, 'data' => $data ) );
			} else {
				echo json_encode( array( 'success' => $message, 'data' => $data ) );
			}
		}
		if ( $die ) {
			die();
		}
	}

	/**
	 * Save or edit form through ajax
	 */
	public function ajax_save() {
		$this->ajax_check_permissions( 'save' );

		// Remove \" from datas
		$_POST = stripslashes_deep( $_POST );

		$id = ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );

		if ( $id > 0 ) {
			$entry = MainWPBulkSettingsManagerDB::Instance()->get_entry_by_id( $id );
			if ( empty( $entry ) ) {
				$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
			}
		}

		$settings = ( isset( $_POST['settings'] ) ? (string) $_POST['settings'] : '' );

		if ( strlen( $settings ) < 3 ) {
			$this->json_error( __( "Missing settings info", self::$plugin_translate ) );
		}

		$return = $this->save( $settings, $id );

		if ( isset( $return['error'] ) ) {
			$this->json_error( $return['error'] );
		}

		$this->json_ok( null, array( 'id' => $return['id'] ), true );
	}

	/**
	 * @param $data
	 * @param $id
	 * @param string $custom_name
	 *
	 * @return array
	 *
	 * Save form to database
	 */
	private function save( $data, $id, $custom_name = "" ) {
		$output = $this->custom_parse_str( $data );

		$count = count( $output );

		// Args passed to child
		$args = array(
			'post'   => array(), // $_POST
			'get'    => array(), // $_GET
			'nonce'  => array(), // all nonces
			'search' => array( 'ok' => array(), 'fail' => array() ) // Text to search
		);

		// All form fields used to again display form to user
		$all_args = array();

		$settings_name = "";
		$settings_url  = "";

		if ( $count == 0 ) {
			return array( 'error' => __( "Please set at least one widget", self::$plugin_translate ) );
		}
		try {
			for ( $i = 0; $i < $count; ++ $i ) {
				// Always first is field type
				if ( strcmp( $output[ $i ][0], "field_type" ) !== 0 ) {
					return array( 'error' => __( "Missing field_type inside parser", self::$plugin_translate ) );
				}

				$field_type = $output[ $i ][1];

				if ( strcmp( $field_type, "text_field" ) === 0 ) {
					$text_fields = array(
						'text_field_description',
						'text_field_name',
						'text_field_value',
						'text_field_type'
					);
					$text_data   = $this->get_fields( 'text_field', $output, $i + 1, $text_fields );

					if ( strlen( $text_data['text_field_name'] ) < 1 ) {
						return array( 'error' => __( "Please set name for all text fields", self::$plugin_translate ) );
					}

					if ( strcmp( $text_data['text_field_type'], "post" ) !== 0 && strcmp( $text_data['text_field_type'], "get" ) !== 0 ) {
						return array( 'error' => __( "Wrong text_field_type", self::$plugin_translate ) . ' : ' . esc_html( $text_data['text_field_type'] ) );
					}

					$all_args[] = $text_data;

					if ( $text_data['text_field_type'] == "post" ) {
						$args['post'][] = $this->urlencode_two( $text_data['text_field_name'], $text_data['text_field_value'] );
					} else {
						$args['get'][] = $this->urlencode_two( $text_data['text_field_name'], $text_data['text_field_value'] );
					}

					$i += 4;
				} else if ( strcmp( $field_type, "textarea_field" ) === 0 ) {
					$textarea_fields = array(
						'textarea_field_description',
						'textarea_field_name',
						'textarea_field_value',
						'textarea_field_type'
					);
					$textarea_data   = $this->get_fields( 'textarea_field', $output, $i + 1, $textarea_fields );

					if ( strlen( $textarea_data['textarea_field_name'] ) < 1 ) {
						return array( 'error' => __( "Please set name for all textarea fields", self::$plugin_translate ) );
					}

					if ( strcmp( $textarea_data['textarea_field_type'], "post" ) !== 0 && strcmp( $textarea_data['textarea_field_type'], "get" ) !== 0 ) {
						return array( 'error' => __( "Wrong textarea_field_type", self::$plugin_translate ) . ' : ' . esc_html( $textarea_data['textarea_field_type'] ) );
					}

					$all_args[] = $textarea_data;

					if ( $textarea_data['textarea_field_type'] == "post" ) {
						$args['post'][] = $this->urlencode_two( $textarea_data['textarea_field_name'], $textarea_data['textarea_field_value'] );
					} else {
						$args['get'][] = $this->urlencode_two( $textarea_data['textarea_field_name'], $textarea_data['textarea_field_value'] );
					}

					$i += 4;
				} else if ( strcmp( $field_type, "submit_field" ) === 0 ) {
					$text_fields = array( 'submit_field_name', 'submit_field_value', 'submit_field_type' );
					$submit_data = $this->get_fields( 'submit_field', $output, $i + 1, $text_fields );

					if ( strlen( $submit_data['submit_field_name'] ) < 1 ) {
						return array( 'error' => __( "Please set name for all submit fields", self::$plugin_translate ) );
					}

					if ( strcmp( $submit_data['submit_field_type'], "post" ) !== 0 && strcmp( $submit_data['submit_field_type'], "get" ) !== 0 ) {
						return array( 'error' => __( "Wrong text_field_type", self::$plugin_translate ) . ' : ' . esc_html( $submit_data['submit_field_type'] ) );
					}

					$all_args[] = $submit_data;

					if ( $submit_data['submit_field_type'] == "post" ) {
						$args['post'][] = $this->urlencode_two( $submit_data['submit_field_name'], $submit_data['submit_field_value'] );
					} else {
						$args['get'][] = $this->urlencode_two( $submit_data['submit_field_name'], $submit_data['submit_field_value'] );
					}

					$i += 3;
				} else if ( strcmp( $field_type, "settings_field" ) === 0 ) {
					$settings_fields = array( 'settings_field_name', 'settings_field_url' );
					$settings_data   = $this->get_fields( 'settings_field', $output, $i + 1, $settings_fields );
					$i += 2;
					$settings_keyring_array = array();

					$settings_new_keyring = $this->get_fields( 'settings_field_keyring', $output, $i + 1, array( 'settings_field_keyring' ), false );

					if ( isset( $settings_new_keyring['settings_field_keyring'] ) ) {
						$i += 1;
						$settings_new_keyring_name = trim( $settings_new_keyring['settings_field_keyring'] );

						if ( strlen( $settings_new_keyring_name ) > 0 ) {
							if ( MainWPBulkSettingsManagerDB::Instance()->insert_key_ring( $settings_new_keyring_name ) === false ) {
								return array( 'error' => __( "Cannot create new keyring", self::$plugin_translate ) );
							}

							$settings_keyring_array[] = MainWPBulkSettingsManagerDB::Instance()->get_insert_id();
						}
					}

					for ( $ii = 0; $ii < 9999; ++ $ii ) {
						// Check if all keyrings exists
						$keyring_select_data = $this->get_fields( 'keyring_select', $output, $i + 1, array( 'settings_field_keyring_select' ), false );

						if ( ! isset( $keyring_select_data['settings_field_keyring_select'] ) ) {
							break;
						}
						$i += 1;
						$keyring_id = $keyring_select_data['settings_field_keyring_select'];

						if ( MainWPBulkSettingsManagerDB::Instance()->get_key_ring_by_id( $keyring_id ) === false ) {
							return array( 'error' => __( "This keyring does not exist", self::$plugin_translate ) );
						}

						$settings_keyring_array[] = $keyring_id;
					}


					// Create new keyring if user put something
					unset( $settings_data['settings_field_keyring'] );

					if ( $custom_name != "" ) {
						$settings_name = $custom_name;
					} else {
						$settings_name = $settings_data['settings_field_name'];
					}

					$settings_url = $settings_data['settings_field_url'];

					if ( isset( $settings_url[0] ) && $settings_url[0] == '/' ) {
						$settings_url = substr( $settings_url, 1 );
					}

					if ( strlen( $settings_name ) < 1 ) {
						return array( 'error' => __( "Please set settings name", self::$plugin_translate ) );
					}

					if ( strlen( $settings_url ) < 1 ) {
						return array( 'error' => __( "Please set settings url", self::$plugin_translate ) );
					}
				} else if ( strcmp( $field_type, "nonce_field" ) === 0 ) {
					$nonce_fields = array( 'nonce_field_name', 'nonce_field_arg' );
					$nonce_data   = $this->get_fields( 'nonce_field', $output, $i + 1, $nonce_fields );

					if ( strlen( $nonce_data['nonce_field_name'] ) < 1 ) {
						return array( 'error' => __( "Please set nonce name", self::$plugin_translate ) );
					}

					if ( strlen( $nonce_data['nonce_field_arg'] ) == 0 ) {
						$args['nonce'][] = $this->urlencode_two( '_nonce', $nonce_data['nonce_field_name'] );
					} else {
						$args['nonce'][] = $this->urlencode_two( $nonce_data['nonce_field_arg'], $nonce_data['nonce_field_name'] );
					}

					$all_args[] = $nonce_data;

					$i += 2;
				} else if ( strcmp( $field_type, "search_field" ) === 0 ) {
					$search_fields = array( 'search_field_ok', 'search_field_fail' );
					$search_data   = $this->get_fields( 'search_field', $output, $i + 1, $search_fields );

					$strlen_ok   = strlen( $search_data['search_field_ok'] );
					$strlen_fail = strlen( $search_data['search_field_fail'] );

					if ( $strlen_ok < 1 && $strlen_fail < 1 ) {
						return array( 'error' => __( "Please set OK text or Fail text inside search text", self::$plugin_translate ) );
					}

					if ( $strlen_ok > 0 ) {
						$args['search']['ok'][] = $search_data['search_field_ok'];
					}

					if ( $strlen_fail > 0 ) {
						$args['search']['fail'][] = $search_data['search_field_fail'];
					}

					$all_args[] = $search_data;

					$i += 2;

				} else if ( strcmp( $field_type, "selectbox_field" ) === 0 ) {
					$selected_count = 0;

					$selectbox_fields = array(
						'selectbox_field_description',
						'selectbox_field_name',
						'selectbox_field_type',
						'selectbox_field_type_send'
					);
					$selectbox_data   = $this->get_fields( 'selectbox_field', $output, $i + 1, $selectbox_fields );

					if ( strlen( $selectbox_data['selectbox_field_name'] ) < 1 ) {
						return array( 'error' => __( "Please set name for selectbox", self::$plugin_translate ) );
					}

					if ( strcmp( $selectbox_data['selectbox_field_type'], "radio" ) !== 0 && strcmp( $selectbox_data['selectbox_field_type'], "checkbox" ) !== 0 ) {
						return array( 'error' => __( "Wrong selectbox_field_type", self::$plugin_translate ) . ' : ' . esc_html( $selectbox_data['selectbox_field_type'] ) );
					}

					if ( strcmp( $selectbox_data['selectbox_field_type_send'], "post" ) !== 0 && strcmp( $selectbox_data['selectbox_field_type_send'], "get" ) !== 0 ) {
						return array( 'error' => __( "Wrong selectbox_field_type_send", self::$plugin_translate ) . ' : ' . esc_html( $selectbox_data['selectbox_field_type_send'] ) );
					}

					$selectbox_field_name = $selectbox_data['selectbox_field_name'];
					$i += 4;

					// Prevent infinite loop
					$args_temp = array();

					$selectbox_additional_fields = array(
						'selectbox_field_checkbox',
						'selectbox_field_label',
						'selectbox_field_value'
					);
					for ( $t = 0; $t < 200; ++ $t ) {
						$selectbox_additional_data = $this->get_fields( 'selectbox_field_additional', $output, $i + 1, $selectbox_additional_fields, false );
						if ( empty( $selectbox_additional_data ) ) {
							break;
						}
						unset( $selectbox_additional_data['field_type'] );

						$selectbox_additional_data['selectbox_field_checkbox'] = (int) $selectbox_additional_data['selectbox_field_checkbox'];
						if ( $selectbox_additional_data['selectbox_field_checkbox'] != 0 && $selectbox_additional_data['selectbox_field_checkbox'] != 1 ) {
							return array( 'error' => __( "Wrong selectbox_field_checkbox", self::$plugin_translate ) . ' ' . esc_html( $selectbox_additional_data['selectbox_field_checkbox'] ) );
						}

						if ( $selectbox_additional_data['selectbox_field_checkbox'] == 1 ) {
							++ $selected_count;
							if ( $selectbox_data['selectbox_field_type'] == "checkbox" ) {
								if ( $selectbox_data['selectbox_field_type_send'] == 'post' ) {
									$args['post'][] = $this->urlencode_two( $selectbox_field_name . '[]', $selectbox_additional_data['selectbox_field_value'] );
								} else {
									$args['get'][] = $this->urlencode_two( $selectbox_field_name . '[]', $selectbox_additional_data['selectbox_field_value'] );
								}
							} else {
								if ( $selectbox_data['selectbox_field_type_send'] == 'post' ) {
									$args['post'][] = $this->urlencode_two( $selectbox_field_name, $selectbox_additional_data['selectbox_field_value'] );
								} else {
									$args['get'][] = $this->urlencode_two( $selectbox_field_name, $selectbox_additional_data['selectbox_field_value'] );
								}
							}
						}

						$selectbox_field_label_strlen = strlen( $selectbox_additional_data['selectbox_field_label'] );

						if ( $selectbox_field_label_strlen < 1 ) {
							return array( 'error' => __( "Plase set labels for all selectboxes", self::$plugin_translate ) );
						}

						$i += 3;
						$args_temp[] = $selectbox_additional_data;
					}

					if ( $selectbox_data['selectbox_field_type'] == "radio" && $selected_count > 1 ) {
						return array( 'error' => __( "You can select only one entry in radio type. You select", self::$plugin_translate ) . ' ' . $selected_count );
					}

					$selectbox_data['fields'] = $args_temp;
					$all_args[]               = $selectbox_data;
				} else {
					return array( 'error' => __( "Unknown field type", self::$plugin_translate ) . ' : ' . esc_html( $field_type ) );
				}


			}
		} catch ( Exception $e ) {
			return array( 'error' => __( "Unknown error in parser", self::$plugin_translate ) . ' : ' . esc_html( $e->getMessage() ) );
		}

		if ( empty( $settings_name ) || empty( $settings_url ) ) {
			return array( 'error' => __( "Missing settings name or url", self::$plugin_translate ) );
		}

		// Settings fields should be always first
		$settings_args = array(
			'field_type'          => 'settings_field',
			'settings_field_name' => $settings_name,
			'settings_field_url'  => $settings_url
		);
		array_unshift( $all_args, $settings_args );

		$args['post']  = implode( "&", $args['post'] );
		$args['get']   = implode( "&", $args['get'] );
		$args['nonce'] = implode( "&", $args['nonce'] );

		$to_save = json_encode( array( 'args' => $args, 'all_args' => $all_args ) );

		if ( $id > 0 ) {
			if ( MainWPBulkSettingsManagerDB::Instance()->update_entry( $settings_name, $settings_url, $to_save, $id ) === false ) {
				return array( 'error' => __( "Cannot update entry", self::$plugin_translate ) );
			}
		} else {
			if ( MainWPBulkSettingsManagerDB::Instance()->insert_entry( $settings_name, $settings_url, $to_save ) === false ) {
				return array( 'error' => __( "Cannot insert new entry", self::$plugin_translate ) );
			}

			$id = MainWPBulkSettingsManagerDB::Instance()->get_insert_id();
		}

		if ( MainWPBulkSettingsManagerDB::Instance()->delete_key_ring_to_entry_by_entry_id( $id ) === false ) {
			return array( 'error' => __( "Cannot delete connections between entry and keyring", self::$plugin_translate ) );
		}

		if ( ! isset( $settings_keyring_array ) || empty( $settings_keyring_array ) ) {
			$settings_keyring_array = array( 1 );
		}

		foreach ( $settings_keyring_array as $keyring_id ) {
			if ( MainWPBulkSettingsManagerDB::Instance()->insert_key_ring_to_entry( $keyring_id, $id ) === false ) {
				return array( 'error' => __( "Cannot insert connections between entry and keyring", self::$plugin_translate ) );
			}
		}

		return array( 'id' => $id );
	}

	/**
	 * @param $str
	 *
	 * We received form data as one string
	 * We cannot use parse_str function because it wrongly recognize arrays
	 *
	 * @return array
	 */
	protected function custom_parse_str( $str ) {
		$return = array();

		$exploded = explode( "&", $str );

		if ( is_array( $exploded ) ) {
			foreach ( $exploded as $explode ) {
				$list = explode( "=", $explode, 2 );

				// We skip if something is invalid
				if ( is_array( $list ) && count( $list ) == 2 ) {
					$name = (string) urldecode( trim( $list[0] ) );
					if ( substr( $name, 0, 16 ) != 'fake_radio_name_' ) {
						$val      = (string) urldecode( trim( $list[1] ) );
						$return[] = array( $name, $val );
					}
				}
			}
		}

		return $return;
	}

	/**
	 * @param $name
	 * @param $output
	 * @param $counter
	 * @param $fields
	 * @param bool|true $display_error
	 *
	 * Helper function which allows us to retrive all necessary keys for forms
	 *
	 * @return array|void
	 */
	private function get_fields( $name, $output, $counter, $fields, $display_error = true ) {
		$return = array();

		for ( $i = 0; $i < count( $fields ); ++ $i ) {
			if ( isset( $output[ $counter + $i ] ) ) {
				$field_name = $output[ $counter + $i ][0];
				$field_key  = array_search( $field_name, $fields );
				if ( $field_key !== false ) {
					$return[ $fields[ $field_key ] ] = $output[ $counter + $i ][1];
				}
			} else {
				if ( $display_error ) {
					$this->json_error( __( "Not enough fields when parsing " . esc_html( $name ), self::$plugin_translate ) );
				} else {
					return;
				}
			}
		}

		if ( count( $fields ) != count( $return ) ) {
			if ( $display_error ) {
				$this->json_error( __( "Cannot find all fields", self::$plugin_translate ) );
			} else {
				return;
			}
		}

		return array_merge( array( 'field_type' => $name ), $return );
	}

	/**
	 * @param $first
	 * @param $second
	 *
	 * Urlencode key and value
	 *
	 * @return string
	 */
	private function urlencode_two( $first, $second ) {
		return urlencode( $first ) . '=' . urlencode( $second );
	}

	/**
	 * Delete forms through ajax
	 */
	public function ajax_delete() {
		$this->ajax_check_permissions( 'delete' );

		$ids  = ( isset( $_POST['ids'] ) ? array_map( 'intval', (array) $_POST['ids'] ) : array() );
		$type = ( isset( $_POST['type'] ) ? trim( (string) $_POST['type'] ) : '' );

		switch ( $type ) {
			case 'key':
				$entry = MainWPBulkSettingsManagerDB::Instance()->get_entries_by_ids( $ids );

				if ( count( $entry ) != count( $ids ) ) {
					$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
				}

				if ( MainWPBulkSettingsManagerDB::Instance()->delete_entries_from_key_ring( $ids ) === false ) {
					$this->json_error( __( "Cannot delete entry from keyring", self::$plugin_translate ) );
				}

				if ( MainWPBulkSettingsManagerDB::Instance()->delete_entries_by_ids( $ids ) === false ) {
					$this->json_error( __( "Cannot delete entries", self::$plugin_translate ) );
				}
				break;

			case 'keyring':
				if ( in_array( 1, $ids ) ) {
					$this->json_error( __( "You cannot delete default keyring", self::$plugin_translate ) );
				}

				$entry = MainWPBulkSettingsManagerDB::Instance()->get_key_rings_by_ids( $ids );

				if ( count( $entry ) != count( $ids ) ) {
					$this->json_error( __( "This keyring does not exist", self::$plugin_translate ) );
				}

				if ( MainWPBulkSettingsManagerDB::Instance()->delete_keyrings_conntections_by_ids( $ids ) === false ) {
					$this->json_error( __( "Cannot delete keyrings connections", self::$plugin_translate ) );
				}

				if ( MainWPBulkSettingsManagerDB::Instance()->delete_keyrings_by_ids( $ids ) === false ) {
					$this->json_error( __( "Cannot delete keyrings", self::$plugin_translate ) );
				}
				break;

			default:
				$this->json_error( __( "Invalid type", self::$plugin_translate ) );
		}

		$this->json_ok();
	}

	/**
	 * First stage of sending forms to child
	 * We get info to which sites we will sending infos
	 */
	public function ajax_send_to_child() {
		$this->ajax_check_permissions( 'send_to_child' );

		$sites  = ( isset( $_POST['sites'] ) ? array_map( 'intval', (array) $_POST['sites'] ) : array() );
		$groups = ( isset( $_POST['groups'] ) ? array_map( 'intval', (array) $_POST['groups'] ) : array() );
		$id     = ( isset( $_POST['id'] ) ? array_map( 'intval', (array) $_POST['id'] ) : array() );
		$type   = ( isset( $_POST['type'] ) ? trim( (string) $_POST['type'] ) : '' );

		switch ( $type ) {
			case 'key':
			case 'keyring':
				$entries = MainWPBulkSettingsManagerDB::Instance()->get_entries_id_name_by_id( $id );

				if ( count( $entries ) != count( $id ) ) {
					$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
				}
				break;

			default:
				$this->json_error( __( "Invalid type", self::$plugin_translate ) );
		}

		if ( ! empty( $sites ) ) {
			$websites = MainWP_DB::Instance()->getWebsitesByIds( $sites );
		} else if ( ! empty( $groups ) ) {
			$websites = MainWP_DB::Instance()->getWebsitesByGroupIds( $groups );
		} else {
			$this->json_error( __( "You doesn't select site or group", self::$plugin_translate ) );
		}

		if ( count( $websites ) == 0 ) {
			$this->json_error( __( "Sites you are select doesn't exist", self::$plugin_translate ) );
		}

		$return_ids           = array();
		$return_urls          = array();
		$return_entries       = array();
		$return_entries_names = array();

		foreach ( $websites as $website ) {
			$return_ids[]  = $website->id;
			$return_urls[] = $website->url;
		}

		foreach ( $entries as $entry ) {
			$return_entries[]       = $entry->id;
			$return_entries_names[] = $entry->name;
		}

		$this->json_ok( null, array(
			'ids'           => $return_ids,
			'urls'          => $return_urls,
			'entries'       => $return_entries,
			'entries_names' => $return_entries_names,
			'interval'      => (int) get_option( 'mainwp_bulk_settings_manager_interval' )
		) );
	}

	/**
	 * Second stage of sending forms to child
	 */
	public function ajax_send_to_child_step_2() {
		$this->ajax_check_permissions( 'send_to_child_step_2' );

		global $mainwpBulkSettingsManagerExtensionActivator;

		$website_id = ( isset( $_POST['website_id'] ) ? intval( $_POST['website_id'] ) : 0 );

		$website = MainWP_DB::Instance()->getWebsiteById( $website_id );

		if ( empty( $website ) ) {
			$this->json_error( __( "Website does not exist", self::$plugin_translate ) );
		}

		$id = ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );

		$entry = MainWPBulkSettingsManagerDB::Instance()->get_entry_by_id( $id );
		if ( empty( $entry ) ) {
			$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
		}

		$unserialized = json_decode( $entry->settings, true );

		$unserialized['args'] = $this->boilerplate_support( $unserialized['args'], $website->url );
		$unserialized['args'] = $this->spinner_support( $unserialized['args'] );

		unset( $unserialized['all_args'] );

		// We save all datas inside save() function
		$post_data = array(
			'action' => 'skeleton_key_visit_site_as_browser',
			'url'    => $entry->url,
			'args'   => $unserialized['args']
		);

		$information = apply_filters( 'mainwp_fetchurlauthed', $mainwpBulkSettingsManagerExtensionActivator->getChildFile(), $mainwpBulkSettingsManagerExtensionActivator->getChildKey(), $website_id, 'skeleton_key', $post_data );

		$this->check_child_response( $information, __( "Cannot send datas to child", self::$plugin_translate ) );

		$preview = array();
		if ( isset( $information['url'] ) ) {
			$preview['url'] = $information['url'];
		}

		if ( isset( $information['get'] ) ) {
			$preview['get'] = $information['get'];
		}

		if ( isset( $information['post'] ) ) {
			$preview['post'] = $information['post'];
		}

		if ( strlen( $information['content'] ) < 2 ) {
			$information['content'] = 'Site return empty content';
		}

		$preview_secret = wp_generate_password( 15, false, false );
		if ( MainWPBulkSettingsManagerDB::Instance()->insert_preview( $preview_secret, $information['content'], json_encode( $preview ), $id, $website_id ) === false ) {
			$this->json_error( __( "Cannot insert preview", self::$plugin_translate ) );
		}

		$preview_id = MainWPBulkSettingsManagerDB::Instance()->get_insert_id();

		function mainwp_bulk_settings_manager_remove_empty_internal( $value ) {
			$value = trim( $value );

			// Must return false to remove
			return ( strlen( $value ) > 5 );
		}

		// Some kind of generic findind of error and updated divs
		preg_match_all( '%<div[^<>]*?class=["\'][^"\']*?(?:notice|updated)[^"\']*?["\'][^<>]*?>(.*?)</div>%s', $information['content'], $update_messages );
		if ( isset( $update_messages[1] ) ) {
			$update_messages = array_map( 'wp_strip_all_tags', str_replace( array(
				'<br>',
				'<br />'
			), ' ', $update_messages[1] ) );
			$update_messages = array_filter( $update_messages, 'mainwp_bulk_settings_manager_remove_empty_internal' );
		}
		preg_match_all( '%<div[^<>]*?class=["\'][^"\']*?(?:error)[^"\']*?["\'][^<>]*?>(.*?)</div>%s', $information['content'], $error_messages );
		if ( isset( $error_messages[1] ) ) {
			$error_messages = array_map( 'wp_strip_all_tags', str_replace( array(
				'<br>',
				'<br />'
			), ' ', $error_messages[1] ) );
			$error_messages = array_filter( $error_messages, 'mainwp_bulk_settings_manager_remove_empty_internal' );
		}

		$this->json_ok( null, array(
			'preview_id'          => $preview_id,
			'preview_secret'      => $preview_secret,
			'search_ok_counter'   => $information['search_ok_counter'],
			'search_fail_counter' => $information['search_fail_counter'],
			'ok_messages'         => $update_messages,
			'error_messages'      => $error_messages
		) );
	}

	/**
	 * @param $content
	 * @param $website_url
	 *
	 * @return mixed
	 *
	 * If boilerplate plugin is installed, we support [tags]
	 */
	public function boilerplate_support( $content, $website_url ) {
		if ( defined( "MAINWP_BOILERPLATE_PLUGIN_FILE" ) && get_option( 'mainwp_bulk_settings_manager_use_boilerplate', 0 ) == 1 ) {
			$tokens      = Boilerplate_DB::get_instance()->get_tokens();
			$site_tokens = Boilerplate_DB::get_instance()->get_site_tokens( $website_url );

			$tokens_search = array();
			$tokens_values = array();

			foreach ( $tokens as $token ) {
				$tokens_search[] = urlencode( '[' . $token->token_name . ']' );
				$tokens_values[] = isset( $site_tokens[ $token->id ] ) ? urlencode( $site_tokens[ $token->id ]->token_value ) : "";
			}

			if ( ! empty( $tokens_search ) ) {
				if ( isset( $content['post'] ) ) {
					$content['post'] = str_replace( $tokens_search, $tokens_values, $content['post'] );
				}

				if ( isset( $content['get'] ) ) {
					$content['get'] = str_replace( $tokens_search, $tokens_values, $content['get'] );
				}
			}
		}

		return $content;
	}

	/**
	 * @param $content
	 *
	 * @return mixed
	 *
	 * If spinner plugin is installed we support it
	 */
	public function spinner_support( $content ) {
		if ( defined( "MAINWP_SPINNER_PLUGIN_FILE" ) && get_option( 'mainwp_bulk_settings_manager_use_spinner', 0 ) == 1 ) {
			$mainwp_spinner = MainWP_Spinner::get_instance();

			if ( isset( $content['post'] ) ) {
				$spinner_data    = $mainwp_spinner->generate_spin_data( $content['post'] );
				$content['post'] = $mainwp_spinner->filter_text( $content['post'], $spinner_data, true );
			}

			if ( isset( $content['get'] ) ) {
				$spinner_data   = $mainwp_spinner->generate_spin_data( $content['get'] );
				$content['get'] = $mainwp_spinner->filter_text( $content['get'], $spinner_data, true );
			}
		}

		return $content;
	}

	/**
	 * @param $response
	 * @param $error_message
	 *
	 * Check if we receive error message from child
	 */
	protected function check_child_response( $response, $error_message ) {
		if ( ! isset( $response['success'] ) || $response['success'] != 1 ) {
			if ( isset( $response['error'] ) ) {
				$this->json_error( $response['error'] );
			} else {
				$this->json_error( $error_message );
			}
		}
	}

	/**
	 * Preview params that were send to child through ajax
	 */
	public function ajax_preview() {
		$this->ajax_check_permissions( 'preview', true );

		$id     = ( isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0 );
		$type   = ( isset( $_GET['type'] ) ? intval( $_GET['type'] ) : 0 );
		$secret = ( isset( $_GET['secret'] ) ? (string) $_GET['secret'] : 0 );

		$entry = MainWPBulkSettingsManagerDB::Instance()->get_preview_by_id( $id );
		if ( empty( $entry ) ) {
			$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
		}

		// Its unsave to print external content in our domain so we are using iframe sandbox
		// Also to prevent preview by ID we are adding additional secret
		if ( strcasecmp( $entry->secret, $secret ) !== 0 ) {
			$this->json_error( __( "Secret mismatch", self::$plugin_translate ) );
		}

		if ( $type == 1 ) {
			echo '<script>if ("sandbox" in document.createElement("iframe")) {} else document.write("Your browser does not support iframe sandbox");</script><iframe sandbox="" scrolling="yes" width="90%" height="98%" srcdoc="' . esc_attr( $entry->content ) . '"></iframe>';
		} else {
			$params = json_decode( $entry->params, true );
			if ( isset( $params['url'] ) ) {
				echo 'URL: ' . esc_html( $params['url'] ) . '<br />';
			}
			if ( isset( $params['get'] ) ) {
				echo '$_GET:<br /><pre>' . esc_html( print_r( $params['get'], true ) ) . '</pre>';
			}
			if ( isset( $params['post'] ) ) {
				echo '$_POST:<br /><pre>' . esc_html( print_r( $params['post'], true ) ) . '</pre>';
			}
		}

		die();
	}

	/**
	 * Delete entry from keyring
	 */
	public function ajax_delete_entry_from_keyring() {
		$this->ajax_check_permissions( 'delete_entry_from_keyring' );

		$entry_id = ( isset( $_POST['entry_id'] ) ? intval( $_POST['entry_id'] ) : 0 );
		$ring_id  = ( isset( $_POST['keyring_id'] ) ? intval( $_POST['keyring_id'] ) : 0 );

		$entry = MainWPBulkSettingsManagerDB::Instance()->get_entry_by_id( $entry_id );

		if ( empty( $entry ) ) {
			$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
		}

		$keyring = MainWPBulkSettingsManagerDB::Instance()->get_key_ring_by_id( $ring_id );

		if ( empty( $entry ) ) {
			$this->json_error( __( "This keyring does not exist", self::$plugin_translate ) );
		}

		if ( MainWPBulkSettingsManagerDB::Instance()->delete_key_ring_to_entry_by_entry_id_and_keyring_id( $entry_id, $ring_id ) === false ) {
			$this->json_error( __( "Cannot delete connection", self::$plugin_translate ) );
		}

		// If its not inside any keyring, set default one

		$current_entries = MainWPBulkSettingsManagerDB::Instance()->get_key_rings_ids_by_entry_id( $entry_id );

		if ( count( $current_entries ) == 0 ) {
			if ( MainWPBulkSettingsManagerDB::Instance()->insert_key_ring_to_entry( 1, $entry_id ) === false ) {
				$this->json_error( __( "Cannot add entry to default keyring", self::$plugin_translate ) );
			}
		}

		$this->json_ok();
	}

	/**
	 * Change settings tab
	 */
	public function ajax_settings() {
		$this->ajax_check_permissions( 'settings' );

		if ( isset( $_POST['delete_history'] ) ) {
			if ( MainWPBulkSettingsManagerDB::Instance()->delete_all_preview() === false ) {
				$this->json_error( __( "Cannot delete previous previews", self::$plugin_translate ) );
			}
		}

		if ( isset( $_POST['interval'] ) ) {
			$interval = intval( $_POST['interval'] );

			if ( ! update_option( 'mainwp_bulk_settings_manager_interval', $interval ) ) {
				$this->json_error( __( "Cannot update interval", self::$plugin_translate ) );
			}
		}

		if ( isset( $_POST['boilerplate'] ) ) {
			$boilerplate = ( $_POST['boilerplate'] == '1' ? 1 : 0 );
			if ( ! update_option( 'mainwp_bulk_settings_manager_use_boilerplate', $boilerplate ) ) {
				$this->json_error( __( "Cannot update boilerplate", self::$plugin_translate ) );
			}
		}

		if ( isset( $_POST['spinner'] ) ) {
			$spinner = ( $_POST['spinner'] == '1' ? 1 : 0 );
			if ( ! update_option( 'mainwp_bulk_settings_manager_use_spinner', $spinner ) ) {
				$this->json_error( __( "Cannot update spinner", self::$plugin_translate ) );
			}
		}

		$this->json_ok();
	}

	/**
	 * Save notes for keyring and entries
	 */
	public function ajax_save_notes() {
		$this->ajax_check_permissions( 'save_notes' );

		$id   = ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );
		$note = ( isset( $_POST['note'] ) ? trim( $_POST['note'] ) : '' );
		$type = ( isset( $_POST['type'] ) ? trim( (string) $_POST['type'] ) : '' );

		switch ( $type ) {
			case 'entry':
				$entry = MainWPBulkSettingsManagerDB::Instance()->get_entry_by_id( $id );
				break;

			case 'keyring':
				$entry = MainWPBulkSettingsManagerDB::Instance()->get_key_ring_by_id( $id );
				break;

			default:
				$this->json_error( __( "Invalid type", self::$plugin_translate ) );
		}

		if ( empty( $entry ) ) {
			$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
		}

		switch ( $type ) {
			case 'entry':
				if ( MainWPBulkSettingsManagerDB::Instance()->update_entry_note( $id, $note ) === false ) {
					$this->json_error( __( "Cannot update note", self::$plugin_translate ) );
				}
				break;

			case 'keyring':
				if ( MainWPBulkSettingsManagerDB::Instance()->update_key_ring_note( $id, $note ) === false ) {
					$this->json_error( __( "Cannot update note", self::$plugin_translate ) );
				}
				break;

			default:
				$this->json_error( __( "Invalid type", self::$plugin_translate ) );
		}

		$this->json_ok();
	}

	/**
	 * Get entries for given keyring (when user click on keyring name)
	 */
	public function ajax_keyring_entries() {
		$this->ajax_check_permissions( 'keyring_entries' );

		$id = ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );

		$key_ring = MainWPBulkSettingsManagerDB::Instance()->get_key_ring_by_id( $id );

		if ( empty( $key_ring ) ) {
			$this->json_error( __( "This keyring does not exist", self::$plugin_translate ) );
		}

		$entries = MainWPBulkSettingsManagerDB::Instance()->get_entries_by_key_ring( $id );

		$this->json_ok( null, array( 'entries' => $entries ) );
	}

	/**
	 * Load notes for keyring and entries
	 */
	public function ajax_load_notes() {
		$this->ajax_check_permissions( 'load_notes' );

		$id   = ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );
		$type = ( isset( $_POST['type'] ) ? trim( (string) $_POST['type'] ) : '' );

		switch ( $type ) {
			case 'entry':
				$entry = MainWPBulkSettingsManagerDB::Instance()->get_entry_by_id( $id );
				break;

			case 'keyring':
				$entry = MainWPBulkSettingsManagerDB::Instance()->get_key_ring_by_id( $id );
				break;

			default:
				$this->json_error( __( "Invalid type", self::$plugin_translate ) );
		}

		if ( empty( $entry ) ) {
			$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
		}

		$this->json_ok( null, array( 'note' => $entry->note ) );
	}

	/**
	 * Export one or many forms as file
	 */
	public function ajax_export() {
		$this->ajax_check_permissions( 'export', true );

		$ids = ( isset( $_GET['ids'] ) ? (string) $_GET['ids'] : '' );
		$ids = explode( ",", $ids );
		$ids = array_map( 'intval', (array) $ids );

		if ( empty( $ids ) ) {
			$this->json_error( __( "Please select at least one settings", self::$plugin_translate ) );
		}

		$out = "";

		$names = array();

		foreach ( $ids as $id ) {
			$entry = MainWPBulkSettingsManagerDB::Instance()->get_entry_by_id( $id );

			if ( empty( $entry ) ) {
				$this->json_error( __( "This entry does not exist", self::$plugin_translate ) );
			}

			$unserialized = json_decode( $entry->settings, true );

			$content = $this->create_custom_str( $unserialized['all_args'] );
			$hash    = sha1( $content );

			$out .= "-----BEGIN BULK SETTINGS MANAGER KEY-----\r\n";
			$out .= base64_encode( $hash . '|' . $content );
			$out .= "\r\n-----END BULK SETTINGS MANAGER KEY-----\r\n";

			$names[] = $entry->name;
		}

		header( "Pragma: public" );
		header( "Expires: -1" );
		header( "Cache-Control: public, must-revalidate, post-check=0, pre-check=0" );
		header( "Content-type: application/x-msdownload", true, 200 );
		header( "Content-Disposition: attachment; filename='" . esc_attr( implode( " - ", $names ) ) . ".txt'" );
		echo $out;
		die();
	}

	/**
	 * @param $array
	 * @param int $recursion
	 *
	 * Convert array to string
	 *
	 * @return string
	 */
	protected function create_custom_str( $array, $recursion = 0 ) {
		if ( $recursion > 10 ) {
			wp_die( __( "Too many recursion inside create_custom_str", self::$plugin_translate ) );
		}
		$str = array();

		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				$str[] = $this->create_custom_str( $val, ( $recursion + 1 ) );
			} else {
				$str[] = urlencode( trim( $key ) ) . '=' . urlencode( trim( $val ) );
			}
		}

		return implode( "&", $str );

	}

	public function init() {
		// Check permissions
		if ( ! $this->check_permissions() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		load_plugin_textdomain( self::$plugin_translate, false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	function admin_enqueue_scripts() {
		wp_register_script( $this->plugin_handle . 'angular-core', plugins_url( '../js/angular.min.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( $this->plugin_handle . 'ng-tasty', plugins_url( '../js/ng-tasty-tpls.min.js', __FILE__ ), array( $this->plugin_handle . 'angular-core' ) );
		wp_register_script( $this->plugin_handle . 'ng-sanitize', plugins_url( '../js/angular-sanitize.min.js', __FILE__ ), array( $this->plugin_handle . 'angular-core' ) );
		wp_register_script( $this->plugin_handle . 'app', plugins_url( '../js/app.js', __FILE__ ), array(
			$this->plugin_handle . 'ng-tasty',
			$this->plugin_handle . 'ng-sanitize',
			'jquery-ui-sortable',
			'jquery-ui-draggable',
			'jquery-ui-core',
			'jquery-ui-droppable'
		) );

		wp_register_style( $this->plugin_handle . 'app', plugins_url( '../css/app.css', __FILE__ ) );
		wp_enqueue_style( $this->plugin_handle . 'app' );

		wp_localize_script( $this->plugin_handle . 'app', $this->plugin_handle . '_translations', $this->get_js_translations() );
		wp_localize_script( $this->plugin_handle . 'app', $this->plugin_handle . '_security_nonce', $this->get_nonce() );

		wp_enqueue_script( $this->plugin_handle . 'app' );
	}

	/**
	 * @return array
	 *
	 * Print translations for JS
	 */
	public function get_js_translations() {
		$translations = array();
		$this->add_translation( $translations, 'Please select websites or a groups in the Select Sites box.', __( 'Please select websites or a groups in the Select Sites box.', self::$plugin_translate ) );

		return $translations;
	}

	/**
	 * @param $array
	 * @param $key
	 * @param $val
	 *
	 * Add translations for JS
	 */
	private function add_translation( &$array, $key, $val ) {
		if ( ! is_array( $array ) ) {
			$array = array();
		}

		$text = str_replace( ' ', '_', $key );
		$text = preg_replace( "/[^A-Za-z0-9_]/", '', $text );

		$array[ $text ] = $val;
	}

	/**
	 * @return array
	 *
	 * Generate nonces
	 */
	public function get_nonce() {
		$nonce_ids = array(
			'save',
			'list',
			'delete',
			'send_to_child',
			'send_to_child_step_2',
			'export',
			'preview',
			'save_notes',
			'load_notes',
			'keyring_entries',
			'save_keyring_name',
			'delete_entry_from_keyring',
			'settings'
		);

		$generated_nonce = array();

		foreach ( $nonce_ids as $id ) {
			$generated_nonce[ $id ] = wp_create_nonce( MainWPBulkSettingsManager::$nonce_token . $id );
		}

		return $generated_nonce;
	}

	/**
	 * Render wp-admin/admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager
	 */
	public function settings() {
		// Check permissions
		if ( ! $this->check_permissions() ) {
			return;
		}

		if ( isset( $_POST['import_file'] ) ) {
			$this->import_file();
		}

		if ( isset( $_POST['import_text'] ) ) {
			$this->import_text();
		}

		MainWPBulkSettingsManagerView::render_view();
	}

	/**
	 * Import form using file
	 */
	private function import_file() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], MainWPBulkSettingsManager::$nonce_token . 'import' ) ) {
			self::add_error_message( __( 'Error: Wrong or expired request. Please reload page', self::$plugin_translate ) );

			return;
		}

		if ( ! isset( $_FILES['import']['tmp_name'] ) || ! @is_file( $_FILES['import']['tmp_name'] ) || ! is_uploaded_file( $_FILES['import']['tmp_name'] ) ) {
			self::add_error_message( __( 'Please send file to import', self::$plugin_translate ) );

			return;
		}

		$content = @file_get_contents( $_FILES['import']['tmp_name'] );
		@unlink( $_FILES['import']['tmp_name'] );

		$this->import( $content );
	}

	/**
	 * @param $message
	 * Add error message which be displayed as error inside view
	 */
	public static function add_error_message( $message ) {
		self::$error_messages[] = wp_strip_all_tags( $message );
	}

	/**
	 * @param $content
	 * @param string $custom_name
	 *
	 * Import form
	 */
	public function import( $content, $custom_name = "" ) {
		if ( strlen( $content ) < 10 ) {
			self::add_error_message( __( 'File you send is empty', self::$plugin_translate ) );

			return;
		}

		$import_datas = array();

		// We are allowing content with header and without
		preg_match_all( "/-----BEGIN BULK SETTINGS MANAGER KEY-----[\r\n]*(.*?)[\r\n]*-----END BULK SETTINGS MANAGER KEY-----/s", $content, $matches );

		if ( ! isset( $matches[1] ) || count( $matches[1] ) == 0 ) {
			$import_datas[] = $content;
		} else {
			$import_datas = $matches[1];
		}

		for ( $i = 0; $i < count( $import_datas ); ++ $i ) {
			$data = (string) base64_decode( $import_datas[ $i ] );

			if ( $data === false ) {
				self::add_error_message( __( 'Cannot decode base64 data from form ' . ( $i + 1 ), self::$plugin_translate ) );

				return;
			}

			$exploded = explode( "|", $data );

			if ( ! isset( $exploded[1] ) ) {
				self::add_error_message( __( 'Missing checksum information in form ' . ( $i + 1 ), self::$plugin_translate ) );

				return;
			}

			$checksum = $exploded[0];
			$content  = $exploded[1];

			if ( strcasecmp( $checksum, sha1( $content ) ) !== 0 ) {
				self::add_error_message( __( 'Invalid checksum information in form ' . ( $i + 1 ), self::$plugin_translate ) );

				return;
			}

			$return = $this->save( $content, 0, $custom_name );

			if ( isset( $return['error'] ) ) {
				self::add_error_message( $return['error'] );

				return;
			}
		}

		self::add_message( __( 'Import successfull', self::$plugin_translate ) );

		if ( count( $import_datas ) == 1 && isset( $return['id'] ) ) {
			// We are using JS because php headers are already sent
			echo '<script>window.location.replace("' . admin_url( 'admin.php?page=Extensions-Mainwp-Bulk-Settings-Manager&id=' . (int) $return['id'] . '&just_imported=1' ) . '");</script>';
		}
	}

	/**
	 * @param $message
	 * Add message which be displayed as info inside view
	 */
	public static function add_message( $message ) {
		self::$messages[] = wp_strip_all_tags( $message );
	}

	/**
	 * Import field using <textarea name="import">
	 */
	private function import_text() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], MainWPBulkSettingsManager::$nonce_token . 'import' ) ) {
			self::add_error_message( __( 'Error: Wrong or expired request. Please reload page', self::$plugin_translate ) );

			return;
		}

		$text        = ( isset( $_POST['import'] ) ? trim( stripslashes( (string) $_POST['import'] ) ) : '' );
		$import_name = ( isset( $_POST['import_name'] ) ? trim( stripslashes( (string) $_POST['import_name'] ) ) : '' );

		if ( strlen( $text ) < 10 ) {
			self::add_error_message( __( 'Please set import text', self::$plugin_translate ) );

			return;
		}

		$datas = array();
		$url   = 'please fill url';

		$this->import( $text, $import_name );
	}

	/**
	 * @param $website_id
	 *
	 * Check if given website exists
	 */
	protected function is_website_exist( $website_id ) {
		$website_id = intval( $website_id );

		if ( $website_id > 0 && ! MainWP_DB::Instance()->getWebsiteById( $website_id ) ) {
			$this->json_error( __( "Website does not exist", self::$plugin_translate ) );
		}
	}
}