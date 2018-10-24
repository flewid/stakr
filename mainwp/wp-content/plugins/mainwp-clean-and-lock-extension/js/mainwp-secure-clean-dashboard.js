jQuery( document ).ready(function ($) {

});

jQuery( document ).ready(function ($) {
	jQuery( '.mainwp-show-cal-tut' ).on('click', function () {
		jQuery( '.mainwp-cal-tut' ).hide();
		var num = jQuery( this ).attr( 'number' );
		jQuery( '.mainwp-cal-tut[number="' + num + '"]' ).show();
		mainwp_setCookie( 'cal_quick_tut_number', jQuery( this ).attr( 'number' ) );
		return false;
	});

	jQuery( '#mainwp-cal-quick-start-guide' ).on('click', function () {
		if (mainwp_getCookie( 'cal_quick_guide' ) == 'on') {
			mainwp_setCookie( 'cal_quick_guide', '' ); } else {
			mainwp_setCookie( 'cal_quick_guide', 'on' ); }
			cal_showhide_quick_guide();
			return false;
	});
	jQuery( '#mainwp-cal-tips-dismiss' ).on('click', function () {
		mainwp_setCookie( 'cal_quick_guide', '' );
		cal_showhide_quick_guide();
		return false;
	});

	cal_showhide_quick_guide();

	jQuery( '#mainwp-cal-dashboard-tips-dismiss' ).on('click', function () {
		$( this ).closest( '.mainwp_info-box-yellow' ).hide();
		mainwp_setCookie( 'cal_dashboard_notice', 'hide', 2 );
		return false;
	});
});

cal_showhide_quick_guide = function () {
	var show = mainwp_getCookie( 'cal_quick_guide' );
	if (show == 'on') {
		jQuery( '#mainwp-cal-tips' ).show();
		jQuery( '#mainwp-cal-quick-start-guide' ).hide();
		cal_showhide_quick_tut();
	} else {
		jQuery( '#mainwp-cal-tips' ).hide();
		jQuery( '#mainwp-cal-quick-start-guide' ).show();
	}

	if ('hide' == mainwp_getCookie( 'cal_dashboard_notice' )) {
		jQuery( '#mainwp-cal-dashboard-tips-dismiss' ).closest( '.mainwp_info-box-yellow' ).hide();
	}
}

cal_showhide_quick_tut = function () {
	var tut = mainwp_getCookie( 'cal_quick_tut_number' );
	jQuery( '.mainwp-cal-tut' ).hide();
	jQuery( '.mainwp-cal-tut[number="' + tut + '"]' ).show();
}
