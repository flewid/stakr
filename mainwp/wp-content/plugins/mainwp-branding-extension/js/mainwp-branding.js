var branding_MaxThreads = 3;
var branding_CurrentThreads = 0;
var branding_TotalThreads = 0;
var branding_FinishedThreads = 0;

mainwp_branding_start_next = function () {
	if (branding_TotalThreads == 0) {
		branding_TotalThreads = jQuery( '.mainwpBrandingSitesItem[status="queue"]' ).length; }

	while ((siteToBranding = jQuery( '.mainwpBrandingSitesItem[status="queue"]:first' )) && (siteToBranding.length > 0) && (branding_CurrentThreads < branding_MaxThreads)) {
		mainwp_branding_start_specific( siteToBranding );
	}
};

mainwp_branding_start_specific = function (pSiteToBranding) {
	branding_CurrentThreads++;
	pSiteToBranding.attr( 'status', 'progress' );
	var statusEl = pSiteToBranding.find( '.status' ).html( '<img src="' + mainwpParams['image_url'] + 'loader.gif"> ' + 'running ..' );
	var detailEl = pSiteToBranding.find( '.detail' );

	var data = {
		action: 'mainwp_branding_performbrandingchildplugin',
		siteId: pSiteToBranding.attr( 'siteid' )
	};

	jQuery.post(ajaxurl, data, function (response) {
		pSiteToBranding.attr( 'status', 'done' );
		if (response && response['result'] == 'OVERRIDED') {
			statusEl.html( 'Not Updated - Individual site settings are in use' ).show();
			statusEl.css( 'color', 'red' );
		} else if (response && response['result'] == 'SUCCESS') {
			statusEl.html( 'Successful' ).show();
			if (response['error'] && response['error']['login_image']) {
				var error = 'Login Image:' + response['error']['login_image'];
				detailEl.html( error );
				detailEl.css( 'color', 'red' );
			}
		} else if (response && response['error']) {
			statusEl.html( response['error'] ).show();
			statusEl.css( 'color', 'red' );
		} else {
			statusEl.html( __( 'Undefined Error' ) ).show();
			statusEl.css( 'color', 'red' );
		}

		branding_CurrentThreads--;
		branding_FinishedThreads++;
		if (branding_FinishedThreads == branding_TotalThreads && branding_FinishedThreads != 0) {
			jQuery( '#mainwp_branding_apply_setting_ajax_message_zone' ).html( 'Saved Settings to child sites.' ).fadeIn( 100 );
			setTimeout(function () {
				location.href = 'admin.php?page=Extensions-Mainwp-Branding-Extension';
			}, 3000);
		}
		mainwp_branding_start_next();
	}, 'json');
};


mainwp_branding_update_specical_site = function (site_id, bId, over) {
	var data = {
		action: 'mainwp_branding_performbrandingchildplugin',
		siteId: site_id,
		branding_id: bId,
		override: over
	};
	statusEl = jQuery( '#mainwp_branding_edit_site_ajax_message_zone' );
	jQuery.post(ajaxurl, data, function (response) {
		if (response && response['result'] == 'SUCCESS') {
			statusEl.html( 'Child site updated.' ).fadeIn();
		} else if (response && response['error']) {
			statusEl.css( 'color', 'red' );
			statusEl.html( response['error'] ).fadeIn();
		} else {
			statusEl.css( 'color', 'red' );
			statusEl.html( __( 'Error: update to child site' ) ).fadeIn();
		}
	}, 'json');
}

var mwp_branding_save_alert = false;

jQuery( document ).ready(function ($) {
	jQuery( '.mainwp-branding-tut-link' ).live('click', function () {
		var parent = jQuery( this ).closest( '.mainwp-branding-tut-box' );
		parent.find( '.mainwp-branding-tut-content' ).show();
		parent.find( '.mainwp-branding-tut-dismiss' ).show();
		jQuery( this ).hide();
		return false;
	});

	jQuery( '.mainwp-branding-tut-dismiss' ).live('click', function () {
		var parent = jQuery( this ).closest( '.mainwp-branding-tut-box' );
		parent.find( '.mainwp-branding-tut-content' ).hide();
		parent.find( '.mainwp-branding-tut-link' ).show();
		jQuery( this ).hide();
		return false;
	});

	$( '.add_text_replace' ).live('click', function () {
		var errors = [];
		$( '#mainwp_branding_texts_add_value' ).removeClass( 'form-invalid' );
		$( '#mainwp_branding_texts_add_replace' ).removeClass( 'form-invalid' );
		if ($.trim( $( '#mainwp_branding_texts_add_value' ).val() ) == '') {
			errors.push( __( 'Text can not be empty.' ) );
			$( '#mainwp_branding_texts_add_value' ).addClass( 'form-invalid' );
		}

		if ($.trim( $( '#mainwp_branding_texts_add_replace' ).val() ) == '') {
			errors.push( __( 'Text Replace can not be empty.' ) );
			$( '#mainwp_branding_texts_add_replace' ).addClass( 'form-invalid' );
		}

		if (errors.length > 0) {
			$( '#mainwp-branding-texts-replace-ajax-zone' ).html( errors.join( '<br />' ) ).show();
			return false;
		} else {
			$( '#mainwp-branding-texts-replace-ajax-zone' ).html( "" ).hide();
		}

		var parent = $( this ).closest( '.mainwp-branding-text-replace-row' );
		parent.before( $( "#mainwp-branding-text-replace-row-copy" ).html() ).fadeIn();
		var newRow = parent.prev();
		newRow.find( "input[name='mainwp_branding_texts_value[]']" ).val( $( '#mainwp_branding_texts_add_value' ).val() );
		newRow.find( "input[name='mainwp_branding_texts_replace[]']" ).val( $( '#mainwp_branding_texts_add_replace' ).val() );
		$( '#mainwp_branding_texts_add_value' ).val( '' );
		$( '#mainwp_branding_texts_add_replace' ).val( '' );
		return false;
	})

	$( '.delete_text_replace' ).live('click', function () {
		$( this ).closest( '.mainwp-branding-text-replace-row' ).remove();
		return false;
	});

	$( '.mwp_branding_reset_btn' ).live('click', function () {
		if ( ! confirm( "Are you sure you want to reset branding options?" )) {
			return false; }

		for (var k in mainwpBrandingDefaultOpts.checkboxes) {
			jQuery( '#' + k ).prop( "checked", mainwpBrandingDefaultOpts.checkboxes[k] );
		}
		for (var k in mainwpBrandingDefaultOpts.textareas) {
			jQuery( 'textarea[name="' + k + '"]' ).val( mainwpBrandingDefaultOpts.textareas[k] );
		}
		for (var k in mainwpBrandingDefaultOpts.textbox_id) {
			jQuery( '#' + k ).val( mainwpBrandingDefaultOpts.textbox_id[k] );
		}
		for (var k in mainwpBrandingDefaultOpts.tinyMCEs) {
			var editor = window.parent.tinymce.get( k );
			if (editor != null && typeof(editor) !== "undefined" && editor.isHidden() == false) {
				editor.setContent( mainwpBrandingDefaultOpts.tinyMCEs[k] );
			} else {
				var obj = $( '#' + k );
				obj.val( mainwpBrandingDefaultOpts.tinyMCEs[k] );
			}
		}
		for (var k in mainwpBrandingDefaultOpts.textbox_class) {
			if (/^(?:mainwp_branding_texts_value)$/.test( k )) {
				jQuery( '.' + k ).each(function () {
					jQuery( this ).closest( '.mainwp-branding-text-replace-row' ).each(function () {
						var row = this;
						if (jQuery( this ).closest( "#mainwp-branding-text-replace-row-copy" ).length == 0) {
							jQuery( row ).remove(); }
					});

				})
			}
		}
		mwp_branding_save_alert = true;
		mwp_branding_save_reminder();
		alert( "Click the Save Settings button in the bottom of the page to save changes." );
	})
})

function mwp_branding_save_reminder() {
	setTimeout(function () {
		if (mwp_branding_save_alert) {
			alert( "Click the Save Settings button in the bottom of the page to save changes." );
			mwp_branding_save_reminder();
		}
	}, 1000 * 60 * 10);
}

jQuery( document ).ready(function ($) {
	mainwp_branding_check_showhide_sections();

	$( '.mainwp_branding_postbox .handlediv' ).live('click', function () {
		var pr = $( this ).parent();
		if (pr.hasClass( 'closed' )) {
			mainwp_branding_set_showhide_section( pr, true ); } else { 			mainwp_branding_set_showhide_section( pr, false ); }
	});
});

mainwp_branding_set_showhide_section = function (obj, show) {
	var sec = obj.attr( 'section' );
	if (show) {
		obj.removeClass( 'closed' );
		mainwp_setCookie( 'mainwp_branding_showhide_section_' + sec, 'show' );
	} else {
		obj.addClass( 'closed' );
		mainwp_setCookie( 'mainwp_branding_showhide_section_' + sec, '' );
	}
}

mainwp_branding_check_showhide_sections = function () {
	var pr, sec;
	jQuery( '.mainwp_branding_postbox .handlediv' ).each(function () {
		pr = jQuery( this ).parent();
		sec = pr.attr( 'section' );
		if (mainwp_getCookie( 'mainwp_branding_showhide_section_' + sec ) == 'show') {
			mainwp_branding_set_showhide_section( pr, true );
		} else {
			mainwp_branding_set_showhide_section( pr, false );
		}
	});
}
