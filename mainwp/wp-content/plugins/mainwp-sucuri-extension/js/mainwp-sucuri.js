/* Administration javascript for MainWP Sucuri Extension */

jQuery( document ).ready(function ($) {
	//$('.mainwp_boxout a[href*="ManageSitesSecurityScan"]').remove();
	$( '.mainwp_sucuri_report_content_box .handlediv' ).live('click', function () {
		var pr = $( this ).parent();
		if (pr.hasClass( 'closed' )) {
			pr.removeClass( 'closed' ); } else {
			pr.addClass( 'closed' ); }
	});
	$( '.mainwp_sucuri_report_content_box .handlelnk' ).live('click', function () {
		var pr = $( this ).parent();
		if (pr.hasClass( 'closed' )) {
			pr.removeClass( 'closed' );
			$( this ).text( __( 'Hide' ) );
		} else {
			pr.addClass( 'closed' );
			$( this ).text( __( 'Show' ) );
		}
		return false;
	});

	$( '#mainwp-sucuri-run-scan' ).live('click', function () {
		mainwp_sucuri_run_scan( this, false );
		return false;
	})

	mainwp_sucuri_run_scan = function (pObj, retring) {
		var data = {
			action: 'mainwp_sucuri_security_scan',
			siteId: $( 'input[name="mainwp_sucuri_site_id"]' ).val(),
			sucuriId: $( 'input[name="mainwp_sucuri_id"]' ).val(),
			wp_nonce: $( 'input[name="mainwp_sucuri_scan_nonce"]' ).val()
		}

		var statusEl = $( '#mwp_sucuri_scan_status' );
		if (retring == true) {
			statusEl.css( 'color', '#0074a2' );
			statusEl.html( ' ' + __( "Connection error detected. The Verify Certificate option has been switched to NO. Retrying in progress." ) ).fadeIn();
		}

		$( this ).attr( 'disabled', 'disabled' );
		$( '#mainwp-sucuri-run-scan' ).text( __( "Scanning..." ) );

		jQuery.post(ajaxurl, data, function (response) {
			if (response == 'retry_action') {
				jQuery( "#mainwp_sucuri_verify_certificate" ).val( 0 );
				mainwp_sucuri_run_scan( pObj, true );
			} else {
				statusEl.hide();
				$( '#mainwp-sucuri-run-scan' ).text( __( "Run Security Scan" ) );
				$( '#mainwp-sucuri-run-scan' ).removeAttr( 'disabled' );
				$( '#mainwp-sucuri-security-scan-result' ).html( response );
			}
		});
	}

	$( '.mainwp-sucuri-saved-report-show' ).live('click', function () {
		var parent = $( this ).closest( '.scr-inside-box' );

		if (parent.hasClass( 'closed' )) {
			parent.removeClass( 'closed' );
			$( this ).text( __( 'Hide' ) );
			if (parent.find( '.scr-report-content' ).html() !== '') { //loaded report
				return false; }
		} else {
			parent.addClass( 'closed' );
			$( this ).text( __( 'Show' ) );
			return false;
		}

		var statusEl = parent.find( '.mainwp-sucuri-report-action-status' );
		statusEl.html( '' );
		var data = {
			action: 'mainwp_sucuri_show_report',
			reportId: $( this ).attr( 'report-id' ),
			siteId: $( 'input[name="mainwp_sucuri_site_id"]' ).val(),
			wp_nonce: $( 'input[name="mainwp_sucuri_show_report_nonce"]' ).val()
		}
		parent.find( '.mainwp-sucuri-report-loading img' ).show();
		$.post(ajaxurl, data, function (response) {
			parent.find( '.mainwp-sucuri-report-loading img' ).hide();
			if ( ! response || response === 'FAIL') {
				statusEl.html( 'Loading Report failed' );
				statusEl.css( 'color', 'red' );
				parent.addClass( 'closed' );
				parent.find( '.mainwp-sucuri-saved-report-show' ).text( __( 'Show' ) );
			} else {
				parent.find( '.scr-report-content' ).html( response );
			}
		})
		return false;
	})

	$( '.mainwp-sucuri-saved-report-delete' ).live('click', function () {
		var parent = $( this ).closest( '.scr-inside-box' );
		var statusEl = parent.find( '.mainwp-sucuri-report-action-status' );
		statusEl.html( '' );
		var data = {
			action: 'mainwp_sucuri_delete_report',
			reportId: $( this ).attr( 'report-id' ),
			wp_nonce: $( 'input[name="mainwp_sucuri_delete_report_nonce"]' ).val()
		}
		parent.find( '.mainwp-sucuri-report-loading img' ).show();
		$.post(ajaxurl, data, function (response) {
			parent.find( '.mainwp-sucuri-report-loading img' ).hide();
			if (response && response === 'SUCCESS') {
				parent.find( '.mainwp-sucuri-saved-report-list-item' ).html( '<span style="color:#bbb">Report has been removed</span>' );
				parent.find( '.scr-report-content' ).remove();
			} else {
				statusEl.html( 'Delete failed' );
				statusEl.css( 'color', 'red' );
			}
		})
		return false;
	})

	$( '#mainwp_sucuri_remind_scan' ).on('change', function (e) {
		var data = {
			action: 'mainwp_sucuri_change_remind',
			sucuriId: $( 'input[name="mainwp_sucuri_id"]' ).val(),
			remind: $( this ).val(),
			wp_nonce: $( 'input[name="mainwp_sucuri_change_remind_nonce"]' ).val()
		}
		var statusEl = $( '#mainwp_sucuri_remind_change_status' );
		$.post(ajaxurl, data, function (response) {
			if (response == 'SUCCESS') {
				statusEl.css( 'color', '#21759b' );
				statusEl.html( "Saved" ).show().fadeOut( 2000 );
			} else {
				statusEl.css( 'color', 'red' );
				statusEl.html( "Saving failed" ).show().fadeOut( 2000 );
			}
		})
	})

	$( '#mainwp_sucuri_verify_certificate' ).on('change', function (e) {
		var data = {
			action: 'mainwp_sucuri_sslverify_certificate',
			security_sslverify: $( this ).val()
		}
		var statusEl = $( '.sucuri_sslverify_loading .status' );
		var loadingEl = $( '.sucuri_sslverify_loading i' );
		statusEl.hide();
		loadingEl.show();
		$.post(ajaxurl, data, function (response) {
			loadingEl.hide();
			if (response) {
				if (response.saved == '1') {
					statusEl.css( 'color', '#21759b' );
					statusEl.html( "Saved" ).show().fadeOut( 2000 );
				} else if (response.error) {
					statusEl.css( 'color', 'red' );
					statusEl.html( response.error ).show();
				} else {
					statusEl.css( 'color', 'red' );
					statusEl.html( "Saving failed" ).show();
				}
			} else {
				statusEl.css( 'color', 'red' );
				statusEl.html( "Saving failed" ).show();
			}
		}, 'json')
	})

})

jQuery( document ).ready(function ($) {
	jQuery( '.mainwp-show-tut' ).on('click', function () {
		jQuery( '.mainwp-sr-tut' ).hide();
		var num = jQuery( this ).attr( 'number' );
		console.log( num );
		jQuery( '.mainwp-sr-tut[number="' + num + '"]' ).show();
		sr_setCookie( 'sr_quick_tut_number', jQuery( this ).attr( 'number' ) );
		return false;
	});

	jQuery( '#mainwp-sr-quick-start-guide' ).on('click', function () {
		if (sr_getCookie( 'sr_quick_guide' ) == 'on') {
			sr_setCookie( 'sr_quick_guide', '' ); } else {
			sr_setCookie( 'sr_quick_guide', 'on' ); }
			sr_showhide_quick_guide();
			return false;
	});
	jQuery( '#mainwp-sr-tips-dismiss' ).on('click', function () {
		sr_setCookie( 'sr_quick_guide', '' );
		sr_showhide_quick_guide();
		return false;
	});

	sr_showhide_quick_guide();

	jQuery( '#mainwp-sr-dashboard-tips-dismiss' ).on('click', function () {
		$( this ).closest( '.mainwp_info-box-yellow' ).hide();
		sr_setCookie( 'sr_dashboard_notice', 'hide', 2 );
		return false;
	});

});

sr_showhide_quick_guide = function (show, tut) {
	var show = sr_getCookie( 'sr_quick_guide' );
	var tut = sr_getCookie( 'sr_quick_tut_number' );

	if (show == 'on') {
		jQuery( '#mainwp-sr-tips' ).show();
		jQuery( '#mainwp-sr-quick-start-guide' ).hide();
		sr_showhide_quick_tut();
	} else {
		jQuery( '#mainwp-sr-tips' ).hide();
		jQuery( '#mainwp-sr-quick-start-guide' ).show();
	}

	if ('hide' == sr_getCookie( 'sr_dashboard_notice' )) {
		jQuery( '#mainwp-sr-dashboard-tips-dismiss' ).closest( '.mainwp_info-box-yellow' ).hide();
	}
}

sr_showhide_quick_tut = function () {
	var tut = sr_getCookie( 'sr_quick_tut_number' );
	jQuery( '.mainwp-sr-tut' ).hide();
	jQuery( '.mainwp-sr-tut[number="' + tut + '"]' ).show();
}

function sr_setCookie(c_name, value, expiredays)
{
	var exdate = new Date();
	exdate.setDate( exdate.getDate() + expiredays );
	document.cookie = c_name + "=" + escape( value ) + ((expiredays == null) ? "" : ";expires=" + exdate.toUTCString());
}
function sr_getCookie(c_name)
{
	if (document.cookie.length > 0) {
		var c_start = document.cookie.indexOf( c_name + "=" );
		if (c_start != -1) {
			c_start = c_start + c_name.length + 1;
			var c_end = document.cookie.indexOf( ";", c_start );
			if (c_end == -1) {
				c_end = document.cookie.length; }
			return unescape( document.cookie.substring( c_start, c_end ) );
		}
	}
	return "";
}
