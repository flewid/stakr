(function ($) {
	var bulkSettingsManagerWidgets;
	var extScope;

	// MainWp Bulk Settings Manager translation
	function _mwskt(text, _var1, _var2, _var3) {
		if (text == undefined || text == '') return text;
		var strippedText = text.replace(/ /g, '_');
		strippedText = strippedText.replace(/[^A-Za-z0-9_]/g, '');

		if (strippedText == '') return text.replace('%1', _var1).replace('%2', _var2).replace('%3', _var3);

		if (bulk_settings_manager_translations == undefined) return text.replace('%1', _var1).replace('%2', _var2).replace('%3', _var3);
		if (bulk_settings_manager_translations[strippedText] == undefined) return text.replace('%1', _var1).replace('%2', _var2).replace('%3', _var3);

		return bulk_settings_manager_translations[strippedText].replace('%1', _var1).replace('%2', _var2).replace('%3', _var3);
		return text;
	}

	// For all generic errors
	var mainwp_bulk_settings_manager_generic_error = function (message) {
		return _mwskt("Generic error: ") + message;
	};

	var mainwp_bulk_settings_manager_display_error = function (message) {
		show_error('bulk_settings_manager_error', message);
	};

	var mainwp_bulk_settings_manager_clear_error = function () {
		hide_error('bulk_settings_manager_error');
		hide_error('bulk_settings_manager_message');
	}

	var mainwp_bulk_settings_manager_display_message = function (message) {
		show_error('bulk_settings_manager_message', message);
	};

	/**
	 * Check each ajax request using this function
	 * We know if something goes wrong
	 **/
	var mainwp_bulk_settings_manager_check_error_in_request = function (data, message, display) {
		if (data.constructor === {}.constructor) {
			if ('success' in data) {
				return true;
			} else if ('error' in data) {
				if (display === undefined) {
					mainwp_bulk_settings_manager_display_error(data.error);
					return false;
				} else {
					return data.error
				}
			} else {
				if (display === undefined) {
					mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error(message));
					return false;
				} else {
					return mainwp_bulk_settings_manager_generic_error(message);
				}
			}
		}
		else {
			if (display === undefined) {
				mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error(message));
				return false;
			} else {
				return mainwp_bulk_settings_manager_generic_error(message);
			}
		}
	};

	// We need this in order to $_POST and $_GET work properly in PHP
	angular.module('httpPostFix', [], function ($httpProvider) {
		// Use x-www-form-urlencoded Content-Type
		$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

		// Override $http service's default transformRequest
		$httpProvider.defaults.transformRequest = [function (data) {
			/**
			 * The workhorse; converts an object to x-www-form-urlencoded serialization.
			 * @param {Object} obj
			 * @return {String}
			 */
			var param = function (obj) {
				var query = '';
				var name, value, fullSubName, subName, subValue, innerObj, i;

				for (name in obj) {
					value = obj[name];

					if (value instanceof Array) {
						for (i = 0; i < value.length; ++i) {
							subValue = value[i];
							fullSubName = name + '[' + i + ']';
							innerObj = {};
							innerObj[fullSubName] = subValue;
							query += param(innerObj) + '&';
						}
					}
					else if (value instanceof Object) {
						for (subName in value) {
							subValue = value[subName];
							fullSubName = name + '[' + subName + ']';
							innerObj = {};
							innerObj[fullSubName] = subValue;
							query += param(innerObj) + '&';
						}
					}
					else if (value !== undefined && value !== null) {
						query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
					}
				}

				return query.length ? query.substr(0, query.length - 1) : query;
			};

			return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
		}];
	});

	var app = angular.module('ngBulkSettingsManagerApp', ['httpPostFix', 'ngSanitize', 'ngTasty']);

	app.config(function ($locationProvider) {
		$locationProvider.html5Mode({enabled: true, requireBase: false});
	});

	app.factory("bulkSettingsManagerRequest", function ($http) {
		return {
			// Get all values for tables
			getAll: function (type, params) {
				return $http.post(ajaxurl + "?" + params, {
					'action': 'mainwp_bulk_settings_manager_list',
					'wp_nonce': bulk_settings_manager_security_nonce['list'],
					'type': type
				}).error(function () {
					mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("bulkSettingsManagerRequest.error"));
				});
			}
		};
	});

	app.controller('ngBulkSettingsManagerController', function ($scope, $http, $filter, $q, $sanitize, bulkSettingsManagerRequest, $compile, $timeout) {
		$scope.scope_toggled_keyring = {};
		$scope.scope_toggled_keyring_datas = {};

		$scope.scope_display_edit_keyring = {};
		$scope.scope_edit_keyring = {};

		$scope.scope_checkbox_keyring = {};

		angular.element('#ngBulkSettingsManagerId').show();

		/**
		 * Select tab
		 * @param setTab
		 */
		$scope.select_tab = function (setTab) {
			// Remove &id=sth from url without page reload
			if ($scope.tab == 'edit') {
				if (typeof (history.replaceState) != "undefined") {
					var new_url = window.location.href.split("?");
					if (new_url.length > 0) {
						history.replaceState(null, 'MainWP Bulk Settings Manager Extension', new_url[0] + "?page=Extensions-Mainwp-Bulk-Settings-Manager");
					}
				}
			}
			$scope.tab = setTab;
		};

		/**
		 * Check if tab is selected
		 * @param checkTab
		 * @returns {boolean}
		 */
		$scope.is_selected = function (checkTab) {
			return $scope.tab === checkTab;
		};

		/**
		 * Send key to child
		 */
		$scope.send_to_child = function (type) {
			if (confirm("Are you sure want to proceed?") != true) {
				return;
			}

			mainwp_bulk_settings_manager_clear_error();

			var selection_class = "";
			if (type == "key") {
				selection_class = ".mainwp_select_sites_key";
			} else {
				selection_class = ".mainwp_select_sites_keyring";
			}

			var selected_sites = [];
			var selected_groups = [];

			if (type == 'keyring') {
				$scope.checked_values = jQuery('.bulk_settings_manager_checkbox_keyring_subkey:checked').map(function () {
					// If we have have open one keyring without keys there is hidden checkbox there
					return this.value === "" ? null : this.value;
				}).get();
			} else {
				$scope.checked_values = jQuery('.bulk_settings_manager_checkbox:checked').map(function () {
					return this.value;
				}).get();
			}

			if ($scope.checked_values.length == 0) {
				mainwp_bulk_settings_manager_display_error(_mwskt('You must select at least one key'));
				return;
			}

			if (jQuery(selection_class+' #select_by').val() == 'site') {
				jQuery(selection_class+" input[name='selected_sites[]']:checked").each(function (i) {
					selected_sites.push(jQuery(this).val());
				});

				if (selected_sites.length == 0) {
					mainwp_bulk_settings_manager_display_error(_mwskt('Please select websites or a groups in the Select Sites box.'));
					return;
				}
			}
			else {
				jQuery(selection_class+" input[name='selected_groups[]']:checked").each(function (i) {
					selected_groups.push(jQuery(this).val());
				});

				if (selected_groups.length == 0) {
					mainwp_bulk_settings_manager_display_error(_mwskt('Please select websites or a groups in the Select Sites box.'));
					return;
				}
			}

			// Get websites id's and urls
			$http.post(ajaxurl, {
				'action': 'mainwp_bulk_settings_manager_send_to_child',
				'sites': selected_sites,
				'groups': selected_groups,
				'id': $scope.checked_values,
				'type': type,
				'wp_nonce': bulk_settings_manager_security_nonce['send_to_child']
			}).success(function (d) {
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "send_to_child")) {
					$scope.send_to_child_ids = d.data.ids;
					$scope.send_to_child_urls = d.data.urls;
					$scope.send_to_child_entries = d.data.entries;
					$scope.send_to_child_entries_names = d.data.entries_names;
					$scope.syncing_delay_default = parseInt(d.data.interval);

					angular.element(document.getElementById('syncing_message')).empty();

					if ($scope.send_to_child_ids.length == 0) {
						angular.element(document.getElementById('syncing_message')).append('<p>' + _mwskt('No websites to send') + '</p>');
						return;
					}

					if ($scope.send_to_child_entries.length == 0) {
						angular.element(document.getElementById('syncing_message')).append('<p>' + _mwskt('No childs to send') + '</p>');
						return;
					}

					// Set progress bar
					var syncing_total = $scope.send_to_child_ids.length * $scope.send_to_child_entries.length;
					jQuery("#syncing_current").html('0');
					jQuery("#syncing_total").html(syncing_total);
					jQuery('#syncing_progress').progressbar({value: 0, max: syncing_total});
					jQuery('#syncing_div').show();

					var send_to_child_promise = $scope.send_to_child_step_2(0);

					for (i = 1; i < syncing_total; ++i) {
						send_to_child_promise = send_to_child_promise.then(function (ii) {
							return $scope.send_to_child_step_2(ii);
						});
					}

					send_to_child_promise.then(function () {
						angular.element(document.getElementById('syncing_message')).append('<div class="mainwp_info-box">' + _mwskt('Child Sites have been updated') + '</div>');
					});

				}
			}).error(function () {
				mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("send_to_child.error"))
			});
		};

		/**
		 * Counter for send to child
		 */
		$scope.send_to_child_timeout = function () {
			$timeout(function () {
				if ($scope.scope_syncing_delay > 0) {
					--$scope.scope_syncing_delay;
				}

				if ($scope.scope_syncing_delay > 0) {
					$scope.send_to_child_timeout();
				}
			}, 1000);
		};

		/**
		 * Here we star counter and after it, run proper send to child
		 *
		 * @param i
		 * @returns {*}
		 */
		$scope.send_to_child_step_2 = function (i) {
			$scope.scope_syncing_delay = $scope.syncing_delay_default;
			$scope.send_to_child_timeout(i);

			return $timeout(function () {
				return $scope.send_to_child_step_3(i);
			}, 1000 * $scope.syncing_delay_default);
		};

		/**
		 * Main send to child functionality
		 *
		 * @param i
		 * @returns {d.promise|Function|*|promise}
		 */
		$scope.send_to_child_step_3 = function (i) {
			var website_i = i % $scope.send_to_child_ids.length;
			var website_id = $scope.send_to_child_ids[website_i];
			var entry_i = Math.trunc(i / $scope.send_to_child_ids.length);
			var entry_id = $scope.send_to_child_entries[entry_i];

			angular.element(document.getElementById('syncing_message')).append('<div style="padding: .5em 0; border-bottom: 1px Solid #e5e5e5;"><b>' + $sanitize($scope.send_to_child_urls[website_i] + " - " + $scope.send_to_child_entries_names[entry_i]) + '</b> <span id="send_to_child_tr_id_' + i + '" ><i class="fa fa-spinner fa-pulse"></i> ' + _mwskt("Pending") + '</span></div>');

			var deferred = $q.defer();
			$http.post(ajaxurl, {
				'action': 'mainwp_bulk_settings_manager_send_to_child_step_2',
				'wp_nonce': bulk_settings_manager_security_nonce['send_to_child_step_2'],
				'website_id': website_id,
				'id': entry_id
			}).success(function (d) {
				var message = mainwp_bulk_settings_manager_check_error_in_request(d, "send_to_child_step_2", 1);
				if (message !== true) {
					angular.element(document.getElementById('send_to_child_tr_id_' + i)).replaceWith('<span style="color: #a00"><i class="fa fa-exclamation-circle"></i> ERROR: ' + $sanitize(message) + '</span>');

				} else {
					var sandbox_iframe_content = "";
					var output_content = "";

					if (d.data.search_ok_counter > 0) {
						output_content += '<span style="color: #0073aa"><i class="fa fa-check-circle"></i> Found <b>' + $sanitize(d.data.search_ok_counter) + '</b>search OK text.</span> ';
					}

					if (d.data.search_fail_counter > 0) {
						output_content += '<span style="color: #a00"><i class="fa fa-exclamation-circle"></i> Found <b>' + $sanitize(d.data.search_fail_counter) + '</b>search FAIL text.</span> ';
					}

					if (d.data.ok_messages.length > 0) {
						output_content += '<span style="color: #0073aa"><i class="fa fa-check-circle"></i> We detect generic OK message: ' + $sanitize(d.data.ok_messages.join(", ")) + '</span> ';
					}

					if (d.data.error_messages.length > 0) {
						output_content += '<span style="color: #a00"><i class="fa fa-exclamation-circle"></i> We detect generic fail message: ' + $sanitize(d.data.error_messages.join(", ")) + '</span> ';
					}

					if (output_content == "") {
						output_content += 'Check changes manually';
					}

					if ("sandbox" in document.createElement("iframe")) {
						output_content += ' <a href="/?TB_inline&width=1200&height=auto&inlineId=bulk_settings_manager_preview" style="text-decoration: none;" class="thickbox" ng-click="preview(' + d.data.preview_id + ', 1, \'' + d.data.preview_secret + '\')"><i class="fa fa-eye"></i> Review Changes</a>';
						output_content += ' | <a href="/?TB_inline&width=1200&height=auto&inlineId=bulk_settings_manager_preview" style="text-decoration: none;" class="thickbox" ng-click="preview(' + d.data.preview_id + ', 0, \'' + d.data.preview_secret + '\')"><i class="fa fa-eye"></i> Review Parameters</a>';
					}
					angular.element(document.getElementById('send_to_child_tr_id_' + i)).replaceWith($compile(output_content)($scope));
				}

				jQuery("#syncing_current").html(i + 1);
				jQuery('#syncing_progress').progressbar({value: i + 1});
				deferred.resolve(i + 1);
			}).error(function () {
				var output_content = '<span style="color: red;"><b>send_to_child_step_2.error</b></span>';
				angular.element(document.getElementById('send_to_child_tr_id_' + i)).replaceWith($compile(output_content)($scope));
				jQuery("#syncing_current").html(i + 1);
				jQuery('#syncing_progress').progressbar({value: i + 1});
				deferred.resolve(i + 1);
			});

			return deferred.promise;
		};

		/**
		 * Delete setting
		 * @param id
		 */
		$scope.delete_settings = function (id, type) {
			if (confirm("Are you sure want to proceed?") == true) {
				mainwp_bulk_settings_manager_clear_error();
				$http.post(ajaxurl, {
					'action': 'mainwp_bulk_settings_manager_delete',
					'ids': jQuery.isArray(id) ? id : [id],
					'type': type,
					'wp_nonce': bulk_settings_manager_security_nonce['delete']
				}).success(function (d) {
					if (mainwp_bulk_settings_manager_check_error_in_request(d, "delete_settings")) {
						if (type == 'keyring') {
							$scope.reload_keyring();
						} else {
							$scope.reload_key();
						}
					}
				}).error(function () {
					mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("delete_settings.error"));
				});
			}
		};

		/**
		 * Delete multiple settings
		 */
		$scope.delete_settings_bulk = function (type) {
			if (type == 'keyring') {
				var ids = jQuery('.bulk_settings_manager_keyring_checkbox:checked').map(function () {
					return this.value;
				}).get();
			} else {
				var ids = jQuery(".bulk_settings_manager_checkbox:checked").map(function () {
					return $(this).val();
				}).get();
			}

			if (ids.length == 0) {
				mainwp_bulk_settings_manager_display_error(_mwskt("Please select at least one entry"));
				return;
			}

			$scope.delete_settings(ids, type);
		};

		/**
		 * Cancel editing current key
		 * @param id
		 */
		$scope.cancel_editing = function (id) {
			$scope.select_tab('dashboard');
		};

		/**
		 * Remove all fields from key
		 * @param id
		 */
		$scope.remove_all_fields = function (id) {
			if (confirm("Are you sure want to proceed?") == true) {
				jQuery("#left_widgets_list_" + id).html('');
			}
		};

		/**
		 * Reset all key fields (so make them empty)
		 * @param id
		 */
		$scope.reset_all_fields = function (id) {
			if (confirm("Are you sure want to proceed?") == true) {
				jQuery("#left_widgets_list_" + id + " input").each(function () {
					$(this).val('');
					$(this).removeAttr('checked');
				});
			}
		};

		/**
		 * Download selected keys as file
		 * @param ids
		 */
		$scope.export_settings = function (ids) {
			window.open(ajaxurl + '?action=mainwp_bulk_settings_manager_export&wp_nonce=' + bulk_settings_manager_security_nonce['export'] + '&ids=' + ids);
		};

		/**
		 * Download selected keys as file
		 */
		$scope.export_bulk = function () {
			var ids = jQuery(".bulk_settings_manager_checkbox:checked").map(function () {
				return $(this).val();
			}).get();

			if (ids.length == 0) {
				mainwp_bulk_settings_manager_display_error(_mwskt("Please select at least one entry"));
				return;
			}

			$scope.export_settings(ids);
		};

		/**
		 * Display child response inside sandboxed iframe
		 *
		 * @param id
		 * @param type
		 * @param secret
		 */
		$scope.preview = function (id, type, secret) {
			var iframe = document.getElementById("bulk_settings_manager_preview_iframe");
			if (!iframe) {
				iframe = document.createElement("iframe");
				iframe.setAttribute("id", "bulk_settings_manager_preview_iframe");
				iframe.setAttribute("scrolling", "yes");
				iframe.setAttribute("width", "98%");
				iframe.setAttribute("height", "98%");
				document.getElementById("bulk_settings_manager_preview").appendChild(iframe);
			}

			iframe.setAttribute("sandbox", "");
			iframe.setAttribute("src", ajaxurl + "?action=mainwp_bulk_settings_manager_preview&type=" + type + "&id=" + id + "&wp_nonce=" + bulk_settings_manager_security_nonce['preview'] + "&secret=" + secret);
		};

		/**
		 * Show notes inside popup for keyring and keys after user click
		 *
		 * @param id
		 * @param name
		 * @param type
		 */
		$scope.show_notes = function (id, name, type) {
			jQuery('#mainwp_notes_overlay').height(jQuery(document).height());
			jQuery('#mainwp_notes_overlay').show();
			jQuery('#mainwp_notes').show();
			$scope.notes_status = _mwskt("Loading note");
			$scope.notes_title = _mwskt("Notes for ") + name;
			$scope.notes_content = "";
			$scope.notes_id = id;
			$scope.notes_type = type;
			$scope.load_notes(id);

		};

		/**
		 * Hide notes
		 */
		$scope.hide_notes = function () {
			jQuery('#mainwp_notes').hide();
			jQuery('#mainwp_notes_overlay').hide();
			jQuery('#mainwp_notes_status').html('');
		};

		/**
		 * Save notes in database
		 */
		$scope.save_notes = function () {
			$http.post(ajaxurl, {
				'action': 'mainwp_bulk_settings_manager_save_notes',
				'id': $scope.notes_id,
				'note': $scope.notes_content,
				'type': $scope.notes_type,
				'wp_nonce': bulk_settings_manager_security_nonce['save_notes']
			}).success(function (d) {
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "save_notes")) {
					$scope.notes_status = _mwskt("Notes saved");
				} else {
					$scope.notes_status = _mwskt("Cannot save note");
				}
			}).error(function () {
				mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("save_notes.error"));
			});
		};

		/**
		 * Load notes for keyring or key
		 * @param id
		 */
		$scope.load_notes = function (id) {
			$http.post(ajaxurl, {
				'action': 'mainwp_bulk_settings_manager_load_notes',
				'id': id,
				'type': $scope.notes_type,
				'wp_nonce': bulk_settings_manager_security_nonce['load_notes']
			}).success(function (d) {
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "load_notes")) {
					$scope.notes_status = "";
					$scope.notes_content = d.data.note;
				} else {
					$scope.notes_status = _mwskt("Cannot load note");
				}
			}).error(function () {
				mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("load_notes.error"));
			});
		};

		/**
		 * Display keys for given keyring after user click
		 * We support also hide
		 * @param id
		 */
		$scope.toggle_keyring = function (id, force_open) {
			force_open = force_open || 0;

			if (force_open == 1 || typeof $scope.scope_toggled_keyring[id] === 'undefined' || $scope.scope_toggled_keyring[id] == 0) {
				$scope.scope_toggled_keyring[id] = 1;
			} else {
				$scope.scope_toggled_keyring[id] = 0;
			}

			if (typeof $scope.scope_toggled_keyring_datas[id] === 'undefined') {

				$http.post(ajaxurl, {
					'action': 'mainwp_bulk_settings_manager_keyring_entries',
					'id': id,
					'wp_nonce': bulk_settings_manager_security_nonce['keyring_entries']
				}).success(function (d) {
					if (mainwp_bulk_settings_manager_check_error_in_request(d, "toggle_keyring")) {
						if (d.data.entries.length == 0) {
							$scope.scope_toggled_keyring_datas[id] = [{'name': 'No entries'}];
						} else {
							$scope.scope_toggled_keyring_datas[id] = d.data.entries;
						}
					}
				}).error(function () {
					mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("toggle_keyring.error"));
				});
			}
		};

		/**
		 * After user import new key, we can preselect it for him
		 * @param id
		 */
		$scope.use_this_key = function (id) {
			id = parseInt(id);
			jQuery(".bulk_settings_manager_checkbox[value='" + id + "']").attr('checked', true);
			$scope.select_tab('dashboard');
		};

		/**
		 * Toggle editing keyring name
		 * @param id
		 * @param name
		 */
		$scope.enable_editing_keyring = function (id, name) {
			if (typeof $scope.scope_display_edit_keyring[id] === 'undefined' || $scope.scope_display_edit_keyring[id] == 0) {
				$scope.scope_edit_keyring[id] = name;
				$scope.scope_display_edit_keyring[id] = 1;
			} else {
				$scope.scope_display_edit_keyring[id] = 0;
				$scope.edit_keyring(id);
			}
		};

		/**
		 * Update keyring name in database
		 * @param id
		 */
		$scope.edit_keyring = function (id) {
			$http.post(ajaxurl, {
				'action': 'mainwp_bulk_settings_manager_save_keyring_name',
				'id': id,
				'name': $scope.scope_edit_keyring[id],
				'wp_nonce': bulk_settings_manager_security_nonce['save_keyring_name']
			}).success(function (d) {
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "toggle_keyring")) {
					$scope.reload_keyring();
					mainwp_bulk_settings_manager_display_message(_mwskt("Name changed"));
				}
			}).error(function () {
				mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("edit_keyring.error"));
			});
		};

		/**
		 * Remove entry from keyring
		 * @param keyring_id
		 * @param entry_id
		 */
		$scope.remove_from_keyring = function (keyring_id, entry_id) {
			$http.post(ajaxurl, {
				'action': 'mainwp_bulk_settings_manager_delete_entry_from_keyring',
				'keyring_id': keyring_id,
				'entry_id': entry_id,
				'wp_nonce': bulk_settings_manager_security_nonce['delete_entry_from_keyring']
			}).success(function (d) {
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "toggle_keyring")) {
					delete $scope.scope_toggled_keyring_datas[keyring_id];
					$scope.toggle_keyring(keyring_id, true);
					mainwp_bulk_settings_manager_display_message(_mwskt("Key removed from keyring"));
				}
			}).error(function () {
				mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("remove_from_keyring.error"));
			});
		};

		$scope.checkbox_keyring_toggle = function (id) {
			$scope.toggle_keyring(id, 1);
		};

		// Here we set functions for table
		$scope.keyring_filter = {
			'name': ''
		};

		$scope.key_filter = {
			'name': ''
		}

		$scope.init_get_keyring = {
			'count': 10,
			'page': 1,
			'sortBy': 'name',
			'sortOrder': 'dsc'
		};

		$scope.init_get_key = {
			'count': 10,
			'page': 1,
			'sortBy': 'name',
			'sortOrder': 'dsc'
		};

		$scope.init_get_history = {
			'count': 10,
			'page': 1,
			'sortBy': 'id',
			'sortOrder': 'dsc'
		};

		$scope.keyring_theme = {
			'loadOnInit': true
		};

		$scope.key_theme = {
			'loadOnInit': true
		};

		$scope.history_theme = {
			'loadOnInit': true
		};

		$scope.reload_keyring_callback = function () {
		};
		$scope.reload_key_callback = function () {
		};
		$scope.reload_history_callback = function () {
		};

		$scope.reload_keyring = function () {
			$scope.scope_toggled_keyring = {};
			$scope.scope_toggled_keyring_datas = {};
			$scope.reload_keyring_callback();
		};

		$scope.reload_key = function () {
			$scope.reload_key_callback();
		};

		$scope.reload_history = function () {
			$scope.reload_history_callback();
		};

		/**
		 * Get keyring table
		 *
		 * @param params
		 * @param paramsObj
		 * @returns {*}
		 */
		$scope.get_keyring = function (params, paramsObj) {
			return bulkSettingsManagerRequest.getAll('keyring', params).then(function (d) {
				d = d.data;
				var rows;
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "get_all_wrapper")) {
					if (d.data.entries.length == 0) {
						rows = [{'name': 'No Saved Keyrings'}];
					}
					else {
						rows = d.data.entries;
					}

					return {
						'rows': rows,
						'header': [{"key": "checkbox", "name": ""}, {"key": "name", "name": "Name"}, {
							"key": "keys",
							"name": "Keys"
						}, {"key": "actions", "name": "Actions"}],
						'pagination': d.data.pagination
					}
				}
			});
		};

		/**
		 * Get entries table
		 *
		 * @param params
		 * @param paramsObj
		 * @returns {*}
		 */
		$scope.get_key = function (params, paramsObj) {
			return bulkSettingsManagerRequest.getAll('entry', params).then(function (d) {
				d = d.data;
				var rows;
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "get_all_wrapper")) {
					if (d.data.entries.length == 0) {
						rows = [{'name': 'No Saved Keys'}];
					}
					else {
						rows = d.data.entries;
					}

					return {
						'rows': rows,
						'header': [{"key": "checkbox", "name": ""}, {"key": "name", "name": "Name"}, {
							"key": "url",
							"name": "Url"
						}, {"key": "created_time", "name": "Time"}, {"key": "actions", "name": "Actions"}],
						'pagination': d.data.pagination
					}
				}
			});
		};

		/**
		 * Get history table
		 *
		 * @param params
		 * @param paramsObj
		 * @returns {*}
		 */
		$scope.get_history = function (params, paramsObj) {
			return bulkSettingsManagerRequest.getAll('history', params).then(function (d) {
				d = d.data;
				var rows;
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "get_all_wrapper")) {
					if (d.data.entries.length == 0) {
						rows = [{'name': 'No history'}];
					}
					else {
						rows = d.data.entries;
					}

					return {
						'rows': rows,
						'header': [{"key": "entry_id", "name": "Entry name"}, {"key": "url", "name": "Url"}, {
							"key": "created_time",
							"name": "Time"
						}, {"key": "actions", "name": "Actions"}],
						'pagination': d.data.pagination
					}
				}
			});
		};

		/**
		 * Delete history and change interval
		 * @param type
		 */
		$scope.change_settings = function (type) {
			var params = {
				'action': 'mainwp_bulk_settings_manager_settings',
				'wp_nonce': bulk_settings_manager_security_nonce['settings']
			};

			switch (type) {
				case 'history':
					if (confirm("Are you sure want to proceed?") != true) {
						return;
					}

					params['delete_history'] = 1;
					break;

				case 'interval':
					var interval = jQuery("#mainwp_bulk_settings_manager_interval").val() || 5;
					params['interval'] = interval;
					break;

				case 'boilerplate':
					params['boilerplate'] = (jQuery("#mainwp_bulk_settings_manager_boilerplate_checkbox").attr("checked") ? 1 : 0);
					break;

				case 'spinner':
					params['spinner'] = (jQuery("#mainwp_bulk_settings_manager_spinner_checkbox").attr("checked") ? 1 : 0);
					break;
			}

			$http.post(ajaxurl, params).success(function (d) {
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "toggle_keyring")) {
					switch (type) {
						case 'history':
							$scope.reload_history();
							mainwp_bulk_settings_manager_display_message(_mwskt("History cleared"));
							break;

						case 'interval':
							mainwp_bulk_settings_manager_display_message(_mwskt("Interval changed"));
							break;

						case 'boilerplate':
							mainwp_bulk_settings_manager_display_message(_mwskt("Boilerplate changed"));
							break;
						case 'spinner':
							mainwp_bulk_settings_manager_display_message(_mwskt("Spinner changed"));
							break;
					}
				}
			}).error(function () {
				mainwp_bulk_settings_manager_display_error(mainwp_bulk_settings_manager_generic_error("change_settings.error"));
			});
		};

	});

	// We are using some code from WordPress widgets
	bulkSettingsManagerWidgets = {
		init: function () {
			var self = this;
			var fake_id = 1000000;

			// Click on widgets on the left
			$(document.body).bind('click.widgets-toggle', function (e) {
				var target = $(e.target),
					css = {'z-index': 100},
					widget, inside, targetWidth, widgetWidth, margin;

				// Animation stuff
				if (target.parents('.widget-top').length && !target.parents('.available-widgets').length) {
					widget = target.closest('div.widget');
					inside = widget.children('.widget-inside');
					targetWidth = parseInt(widget.find('input.widget-width').val(), 10),
						widgetWidth = widget.parent().width();

					if (inside.is(':hidden')) {
						if (targetWidth > 250 && ( targetWidth + 30 > widgetWidth ) && widget.closest('div.widgets-sortables').length) {
							if (widget.closest('div.widget-liquid-right').length) {
								margin = 'margin-right';
							} else {
								margin = 'margin-left';
							}

							css[margin] = widgetWidth - ( targetWidth + 30 ) + 'px';
							widget.css(css);
						}
						widget.addClass('open');
						inside.slideDown('fast');
					} else {
						inside.slideUp('fast', function () {
							widget.attr('style', '');
							widget.removeClass('open');
						});
					}
					e.preventDefault();
				} else if (target.hasClass('widget-control-remove')) {
					target.closest('div.widget').remove();
					e.preventDefault();
				} else if (target.hasClass('widget-control-close')) {
					widget = target.closest('div.widget');
					widget.removeClass('open');
					bulkSettingsManagerWidgets.close(widget);
					e.preventDefault();
				}
			});

			// Make widget on the right draggable
			$('.widget-list').children('.widget').draggable({
				connectToSortable: 'div.widgets-sortables',
				handle: '> .widget-top > .widget-title',
				distance: 2,
				helper: 'clone',
				zIndex: 100,
				containment: 'document',
				stop: function (event, ui) {
					// We need to change fake name inside selectbox
					var $selectbox_to_change = $(ui.helper).find(".radio_field_add_click");
					if ($selectbox_to_change.length) {
						$(ui.helper).find(".selectbox_field_checkbox_click").prop("name", "fake_radio_name_" + fake_id);
						$(ui.helper).find(".selectbox_field_radio_click").prop("name", "fake_radio_name_" + fake_id);
						$selectbox_to_change.attr("rel", fake_id);
						fake_id += 1;
					}
				}
			});

			// Saving action
			$("#mainwp_bulk_settings_manager_add_new_button").bind('click', function (e) {
				e.preventDefault();
				bulkSettingsManagerWidgets.save('#mainwp_bulk_settings_manager_add_new_id', '#mainwp_bulk_settings_manager_add_new_form');
			});

			$("#mainwp_bulk_settings_manager_edit_button").bind('click', function (e) {
				e.preventDefault();
				bulkSettingsManagerWidgets.save('#mainwp_bulk_settings_manager_edit_id', '#mainwp_bulk_settings_manager_edit_form');
			});

			// Sortable widgets on the left
			$('div.widgets-sortables').sortable({
				placeholder: 'widget-placeholder',
				items: '> .widget',
				handle: '> .widget-top > .widget-title',
				cursor: 'move',
				distance: 2,
				containment: 'document',
				cancel: ".disable-sortable",
				start: function (event, ui) {
					var height, $this = $(this),
						$wrap = $this.parent(),
						inside = ui.item.children('.widget-inside');

					if (inside.css('display') === 'block') {
						inside.hide();
						$(this).sortable('refreshPositions');
					}

					if (!$wrap.hasClass('closed')) {
						// Lock all open sidebars min-height when starting to drag.
						// Prevents jumping when dragging a widget from an open sidebar to a closed sidebar below.
						height = ui.item.hasClass('ui-draggable') ? $this.height() : 1 + $this.height();
						$this.css('min-height', height + 'px');
					}
				},

				stop: function (event, ui) {
					ui.item.attr('style', '').removeClass('ui-draggable');
					bulkSettingsManagerWidgets.init_selectbox();
					bulkSettingsManagerWidgets.init_descriptions();
				},

				activate: function () {
					$(this).parent().addClass('widget-hover');
				},
				receive: function (event, ui) {
					sortableIn = 1;
				},
				over: function (e, ui) {
					sortableIn = 1;
				},
				out: function (e, ui) {
					sortableIn = 0;
				},
				beforeStop: function (e, ui) {
					if (sortableIn == 0) {
						ui.item.remove();
					}
				}
			}).sortable('option', 'connectWith', 'div.widgets-sortables');

			$('.available-widgets').droppable({
				tolerance: 'pointer'
			});

			// Enable dragging inside select box and checkbox on left side
			$(".widget-liquid-left").on("click", ".selectbox_field_add_click", function () {
				$(this).parent().parent().after($(".widget-liquid-right .selectbox_field_table tbody").html());
			});

			$(".widget-liquid-left").on("click", ".selectbox_field_remove_click", function () {
				$(this).parent().parent().remove();
			});

			// Click on + icon inside selectbox
			$(".widget-liquid-left").on("click", ".radio_field_add_click", function () {
				var fake_name_id = $(this).attr("rel");
				var $widget_html = $(".widget-liquid-right .radio_field_table tbody").clone();

				$widget_html.find(".selectbox_field_checkbox_click").attr("name", "fake_radio_name_" + fake_name_id);
				$widget_html.find(".selectbox_field_radio_click").attr("name", "fake_radio_name_" + fake_name_id);
				$widget_html.find(".radio_field_add_click").attr("rel", fake_name_id);

				$(this).parent().parent().after($widget_html.html());
			});

			$(".widget-liquid-left").on("click", ".radio_field_remove_click", function () {
				$(this).parent().parent().remove();
			});

			// Click checkbox inside checkbox
			$(".widget-liquid-left").on("click", ".selectbox_field_checkbox_click", function () {
				if ($(this).is(':checked')) {
					$(this).next("input[name='selectbox_field_checkbox']").val("1");
				} else {
					$(this).next("input[name='selectbox_field_checkbox']").val("0");
				}
			});

			// Click radio button inside selectbox
			$(".widget-liquid-left").on("click", ".selectbox_field_radio_click", function () {
				$(this).parent().parent().parent().find("input[name='selectbox_field_checkbox']").val("0");
				$(this).next("input[name='selectbox_field_checkbox']").val("1");
			});


			bulkSettingsManagerWidgets.init_selectbox();
			bulkSettingsManagerWidgets.init_descriptions();

			// For edit
			$(".bulk_settings_manager_description").each(function (event) {
				var description_value = this.value;
				$(this).parent().parent().parent().parent().find('.in-widget-title').text(description_value);
			});

			// Make settings open by default
			$(".widget_settings").slideDown('fast');
		},

		init_descriptions: function () {
			$(".bulk_settings_manager_description").on("input", function (event) {
				var description_value = this.value;
				// We are using text() because of XSS
				$(this).parent().parent().parent().parent().find('.in-widget-title').text(description_value);
			});
		},

		init_selectbox: function () {
			$(".selectbox_field_table tbody").sortable();
			$(".radio_field_table tbody").sortable();
		},

		/**
		 * Save key using ajax, we are using jquery serialization
		 * @param id_id
		 * @param form_id
		 */
		save: function (id_id, form_id) {
			mainwp_bulk_settings_manager_clear_error();
			var temp_id = $(id_id).val();
			var params = {
				'action': 'mainwp_bulk_settings_manager_save',
				'wp_nonce': bulk_settings_manager_security_nonce['save'],
				'id': $(id_id).val(),
				'settings': $(form_id).serialize()
			};

			$.post(ajaxurl, params, function (d) {
				if (mainwp_bulk_settings_manager_check_error_in_request(d, "add_new")) {
					if ("id" in d.data) {
						$(id_id).val(d.data.id);
						mainwp_bulk_settings_manager_display_message(_mwskt("Key Saved"));
					} else {
						mainwp_bulk_settings_manager_display_error(_mwskt("Missing id response"));
					}
				}
			}, 'json');
		},

		close: function (widget) {
			widget.children('.widget-inside').slideUp('fast', function () {
				widget.attr('style', '');
			});
		}
	};

	$(document).ready(
		function () {
			bulkSettingsManagerWidgets.init();
		}
	);

})(jQuery);

// Display tutorial
jQuery(document).ready(function ($) {
	jQuery('.mainwp_show_tut').on('click', function () {
		jQuery('.mainwp_skl_tut').hide();
		var num = jQuery(this).attr('number');
		jQuery('.mainwp_skl_tut[number="' + num + '"]').show();
		mainwp_setCookie('bulk_settings_manager_quick_tut_number', jQuery(this).attr('number'));
		return false;
	});

	jQuery('#mainwp_bulk_settings_manager_quick_start_guide').on('click', function () {
		if (mainwp_getCookie('bulk_settings_manager_quick_guide') == 'on') {
			mainwp_setCookie('bulk_settings_manager_quick_guide', '');
		} else {
			mainwp_setCookie('bulk_settings_manager_quick_guide', 'on');
		}
		bulk_settings_manager_showhide_quick_guide();
		return false;
	});

	jQuery('#mainwp_bulk_settings_manager_tips_dismiss').on('click', function () {
		mainwp_setCookie('bulk_settings_manager_quick_guide', '');
		bulk_settings_manager_showhide_quick_guide();
		return false;
	});

	bulk_settings_manager_showhide_quick_guide();

});

bulk_settings_manager_showhide_quick_guide = function () {
	var show = mainwp_getCookie('bulk_settings_manager_quick_guide');
	if (show == 'on') {
		jQuery('#mainwp_skl_tips').show();
		jQuery('#mainwp_bulk_settings_manager_quick_start_guide').hide();
		bulk_settings_manager_showhide_quick_tut();
	} else {
		jQuery('#mainwp_skl_tips').hide();
		jQuery('#mainwp_bulk_settings_manager_quick_start_guide').show();
	}

}

bulk_settings_manager_showhide_quick_tut = function () {
	var tut = mainwp_getCookie('bulk_settings_manager_quick_tut_number');
	jQuery('.mainwp_skl_tut').hide();
	jQuery('.mainwp_skl_tut[number="' + tut + '"]').show();
}

