jQuery( document ).ready(function () {
	jQuery( '#infobox-uptime' ).insertBefore( '#mainwp-tabs' );
	jQuery( '#errorbox-uptime' ).insertBefore( '#mainwp-tabs' );
	//   ajaxurl is defined by WordPress
   //  jQuery('input.urm_add_new_monitor_button').click(function(event){                                          
    //      inline_window('monitor_form','Create a Monitor',jQuery('.monitors'),500,310,event);
    //  })
	jQuery( '#aum_monitor_reload' ).live('click', function () {
		jQuery( this ).closest( 'form' ).submit();
	})

	urm_apply_check = function (me, event) {

		var action = jQuery( '#aum_form_monitor_urls select[name=monitor_action]' ).val();
		var number_checked = -1;
		number_checked = jQuery( 'div.monitors input[name=checkbox_url]:checkbox:checked' ).length;

		if (number_checked < 1) {
			alert( 'Please choose at least one item.' );
			return;
		} else {
			//jQuery.ajaxSetup({async:false});
			switch (action) {
				case 'display':
				case 'hidden':
					jQuery( 'div.monitors input[name=checkbox_url]:checked' ).each(function () {
						var das = 1;
						var img_path = '/app/views/admin/images/ok.png';
						if (action == 'hidden') {
							das = 0;
							img_path = '/app/views/admin/images/nok.png';
						}
						img_path = urm_plugin_url + img_path;
						jQuery( this ).parent().parent().parent().find( 'div#loading_status i' ).show();
						var me = this;
						jQuery.post(
							ajaxurl,
							{
								action: 'admin_uptime_monitors_display_dashboard',
								'url_id': jQuery( this ).parent().parent().parent().find( '.url_actions' ).attr( 'url_id' ),
								'dashboard': das,
								'wp_nonce': jQuery( "#mainwp_aum_extension_display_dashboard_nonce" ).val()
								},
							function (response, status) {
								jQuery( me ).parent().parent().parent().find( 'div#loading_status i' ).hide();
								jQuery( me ).removeAttr( 'checked' );
								jQuery( me ).parent().parent().parent().find( '.url_display img' ).attr( 'src', img_path );
							})
					});
					break;
				case 'delete':
					if ( ! confirm( 'Are you sure to delele selected items?' )) {
						break; }
					jQuery( 'div.monitors input[name=checkbox_url]:checked' ).each(function () {
						click_link = jQuery( this ).parent().parent().parent().find( 'div.url_delete_link' );
						urm_delete_monitor_button( click_link );
					});
					break;
				case 'pause':
					jQuery( 'div.monitors input[name=checkbox_url]:checked' ).each(function () {
						url_row_obj = jQuery( this ).parent().parent().parent();
						if (url_row_obj.find( '.aum_action_link' ).hasClass( 'pause' )) {
							url_row_obj.find( 'div#loading_status i' ).show();
							url_row_obj.find( '.pause' ).click();
						}
						jQuery( this ).removeAttr( 'checked' );
					});
					break;
				case 'start':
					jQuery( 'div.monitors input[name=checkbox_url]:checked' ).each(function (event) {
						url_row_obj = jQuery( this ).parent().parent().parent();
						if (url_row_obj.find( '.aum_action_link' ).hasClass( 'start' )) {
							url_row_obj.find( 'div#loading_status i' ).show();
							url_row_obj.find( '.start' ).click();
						}
						jQuery( this ).removeAttr( 'checked' );
					});
					break;
				default:
					break;
			}
			jQuery( 'div.monitors input[name=checkall]' ).removeAttr( 'checked' );
		}
	}
	//jQuery('td.monitor_name').click(function(event){
	td_monitor_name = function (me, event) {
		if (jQuery( me ).hasClass( 'active_monitor' )) {
			jQuery( 'td.monitor_name' ).removeClass( 'active_monitor' );
			if (jQuery( '.url_row' ).length > 0) {
				jQuery( '.url_row' ).remove(); }
			return;
		}
		if (jQuery( 'td.monitor_name' ).length > 0) {
			jQuery( 'td.monitor_name' ).removeClass( 'active_monitor' ); }
		jQuery( me ).addClass( 'active_monitor' );
		get_monitor_urls( event, jQuery( me ).parent() );
	}

	//jQuery('.monitor_edit_link').click(function(event){
	monitor_edit_link = function (me, event) {
		inline_window( 'update_monitor&monitor_id=' + jQuery( me ).parent().parent().attr( 'monitor_id' ), 'Update Monitor', jQuery( '.monitors' ), 500, 500, event );
	}

	jQuery( 'body' ).mousemove(function (event) {
		if (jQuery( '.aumloading' ).length > 0) {
			jQuery( '.aumloading' ).css( 'left', (event.pageX + 17) + 'px' );
			jQuery( '.aumloading' ).css( 'top', (event.pageY + 27) + 'px' );
		}
	})
})
function inline_window(action, title, container_obj, width, height, event, offset_top) {
	if (jQuery( '.aum_popup' ).length > 0) {
		jQuery( '.aum_popup' ).remove(); }
	container_obj.prepend( '<iframe class="aum_popup" name="' + action + '" src="' + ajaxurl + '?action=admin_uptime_monitors_' + action + '&title=' + title + '" width="' + width + '" height="' + height + '" ></iframe>' );
	popup_width = jQuery( '.aum_popup' ).width();
	popup_height = jQuery( '.aum_popup' ).height();
	container_width = container_obj.width();
	event_target_height = jQuery( event.target ).height();

	if (offset_top === undefined) {
		offset_top = 0; }

	//            leftVal=event.pageX-(popup_width/2)+"px";
	//            topVal=event.pageY-(popup_height/2)+"px";
	//            $('.aum_popup').css({left:leftVal,top:topVal}).show().fadeOut(1500);
	//            console.log('x-y: ' + event.pageX + ' : ' + event.pageY);
	//            console.log('x-y: ' + popup_width + ' : ' + popup_height);
	jQuery( '.aum_popup' ).css( 'left', (parseInt( container_width - popup_width ) / 2) + 'px' );
	jQuery( '.aum_popup' ).css( 'top', (jQuery( event.target ).position().top / 2 + event_target_height + 10 - offset_top) + 'px' );
}
function inline_window2(action, title, container_obj, width, height, event) {
	if (jQuery( '.aum_popup' ).length > 0) {
		jQuery( '.aum_popup' ).remove(); }
	container_obj.prepend( '<iframe class="aum_popup" name="aum_popup" src="' + ajaxurl + '?action=admin_uptime_monitors_' + action + '&title=' + title + '" width="' + width + '" height="' + height + '" ></iframe>' );

	popup_width = jQuery( '.aum_popup' ).width();
	popup_height = jQuery( '.aum_popup' ).height();
	container_width = container_obj.width();
	event_target_height = jQuery( event.target ).height();

	jQuery( '.aum_popup' ).css( 'left', (parseInt( container_width - popup_width ) / 2) + 'px' );
	jQuery( '.aum_popup' ).css( 'top', (jQuery( event.target ).position().top - 100 + Math.round( event_target_height / 2 )) + 'px' );
}
function get_monitor_urls(event, monitor_row_obj) {
	var data = {
		action: 'admin_uptime_monitors_get_urls',
		monitor_id: monitor_row_obj.attr( 'monitor_id' ),
		what: jQuery( '[name="display_what"]' ).val()
	};
	// ajaxurl is defined by WordPress
	show_loading( event );
	jQuery.post(ajaxurl, data, function (response) {
		jQuery( 'i.aumloading' ).remove();
		hide_loading();
		insert_urls_rows( monitor_row_obj, response );
	});
}
function insert_urls_rows(monitor_row_obj, html) {
	if (jQuery( '.url_row' ).length > 0) {
		jQuery( '.url_row' ).remove(); }
	monitor_row_obj.after( '<tr class="url_row new_url" monitor_id="' + monitor_row_obj.attr( 'monitor_id' ) + '"><td colspan=3 class="url_cell new_url_cell"  ><input type="button" class="aum_button aum_add_new_url_button aum_button2" value="+" />&nbsp; Add URL</td></td>' );
	monitor_row_obj.after( html );
}
function show_loading(event) {
	jQuery( 'body' ).append( '<i class="fa fa-spinner fa-pulse aumloading"></i>' );
	jQuery( '.aumloading' ).css( 'left', (event.pageX + 17) + 'px' );
	jQuery( '.aumloading' ).css( 'top', (event.pageY + 27) + 'px' );
}
function hide_loading() {
	jQuery( '.aumloading' ).remove();
}
//jQuery('.aum_add_new_url_button').click(function(event){
urm_add_new_monitor_button = function (me, event, monitor_id, nonce) {
	inline_window( 'url_form&monitor_id=' + monitor_id + '&wp_nonce=' + nonce, 'Add monitor', jQuery( '#aum_form_monitor_urls' ), 500, 500, event, 200 );
}
//jQuery('.url_delete_link').bind('click',function(){
urm_delete_monitor_button = function (me, nonce) {
	var url_row_obj = me.parent().parent();
	url_row_obj.find( 'div#loading_status i' ).show();
	jQuery.post(ajaxurl, {action: 'admin_uptime_monitors_delete_url', 'url_id': me.parent().attr( 'url_id' ), 'wp_nonce': nonce}, function (response) {
		if (response == 'success') {
			url_row_obj.html( 'Monitor was deleted.' );
		} else {
			url_row_obj.find( 'div#loading_status i' ).hide(); }
	});
}
//jQuery('.url_delete_link').bind('click',function(){
urm_dashboard_delete_monitor_button = function (me, event, nonce) {
	show_loading( event );
	jQuery.post(ajaxurl, {action: 'admin_uptime_monitors_delete_url', 'url_id': jQuery( me ).parent().attr( 'url_id' ), 'wp_nonce': nonce}, function (response) {
		if (response == 'success') {
			jQuery( me ).parent().parent().remove(); }
		hide_loading( event );
	});
}

//jQuery('.url_edit_link').click(function(event){
urm_edit_monitor_button = function (me, event, nonce) {
	inline_window( 'update_url&url_id=' + jQuery( me ).parent().attr( 'url_id' ) + '&wp_nonce=' + nonce, 'Update URL', jQuery( '.monitors' ), 500, 425, event, 100 );
}
//jQuery('.status_link').click(function(event){
urm_status_monitor_button = function (me, event, nonce) {
	var current_status = jQuery( me ).hasClass( 'start' ) ? 'start' : 'pause';
	var status_link_obj = jQuery( me );
	var data = {
		action: 'admin_uptime_monitors_url_' + (jQuery( me ).hasClass( 'start' ) ? 'start' : 'pause'),
		url_id: jQuery( me ).parent().attr( 'url_id' ),
		wp_nonce: nonce
	};
	show_loading( event );
	jQuery( me ).closest( '.mainwp-row' ).find( 'div#loading_status i' ).show();
	jQuery.post(ajaxurl, data, function (response) {
		jQuery( me ).closest( '.mainwp-row' ).find( 'div#loading_status i' ).hide();
		hide_loading();
		if (response == 'success') {
			if (current_status == 'start') {
				status_link_obj.removeClass( 'start' ).addClass( 'pause' );
				status_link_obj.find( 'i' ).removeClass( 'fa-play' ).addClass( 'fa-pause' );
				status_link_obj.parent().parent().find( '.aum_upm_status' ).removeClass( 'paused' ).addClass( status_link_obj.parent().parent().find( '.last_event' ).attr( 'last_event' ) );
			} } else {
			status_link_obj.removeClass( 'pause' ).addClass( 'start' );
			status_link_obj.find( 'i' ).removeClass( 'fa-pause' ).addClass( 'fa-play' );
			status_link_obj.parent().parent().find( '.aum_upm_status' ).removeClass( 'down' ).removeClass( 'up' ).removeClass( 'seems_down' ).removeClass( 'not_checked' ).addClass( 'paused' );
			}
			jQuery( me ).parent().parent().find( 'div#loading_status i' ).hide();
	});
}
//jQuery('.stats_link').click(function(event){
urm_stats_monitor_button = function (me, event, nonce) {
	url_id = jQuery( me ).parent().attr( 'url_id' );
	inline_window2( 'statistics_table&url_id=' + url_id + '&wp_nonce=' + nonce, 'URL Statistics And Reports', jQuery( '.monitors' ), 500, 450, event );
}

jQuery( document ).ready(function ($) {
	jQuery( '.mainwp-show-tut' ).on('click', function () {
		jQuery( '.mainwp-aum-tut' ).hide();
		var num = jQuery( this ).attr( 'number' );
		console.log( num );
		jQuery( '.mainwp-aum-tut[number="' + num + '"]' ).show();
		aum_setCookie( 'aum_quick_tut_number', jQuery( this ).attr( 'number' ) );
		return false;
	});

	jQuery( '#mainwp-aum-quick-start-guide' ).on('click', function () {
		if (aum_getCookie( 'aum_quick_guide' ) == 'on') {
			aum_setCookie( 'aum_quick_guide', '' ); } else {
			aum_setCookie( 'aum_quick_guide', 'on' ); }
			aum_showhide_quick_guide();
			return false;
	});
	jQuery( '#mainwp-aum-tips-dismiss' ).on('click', function () {
		aum_setCookie( 'aum_quick_guide', '' );
		aum_showhide_quick_guide();
		return false;
	});

	aum_showhide_quick_guide();

	jQuery( '#mainwp-aum-dashboard-tips-dismiss' ).on('click', function () {
		$( this ).closest( '.mainwp_info-box-yellow' ).hide();
		aum_setCookie( 'aum_dashboard_notice', 'hide', 2 );
		return false;
	});

});

aum_showhide_quick_guide = function (show, tut) {
	var show = aum_getCookie( 'aum_quick_guide' );
	var tut = aum_getCookie( 'aum_quick_tut_number' );

	if (show == 'on') {
		jQuery( '#mainwp-aum-tips' ).show();
		jQuery( '#mainwp-aum-quick-start-guide' ).hide();
		aum_showhide_quick_tut();
	} else {
		jQuery( '#mainwp-aum-tips' ).hide();
		jQuery( '#mainwp-aum-quick-start-guide' ).show();
	}

	if ('hide' == aum_getCookie( 'aum_dashboard_notice' )) {
		jQuery( '#mainwp-aum-dashboard-tips-dismiss' ).closest( '.mainwp_info-box-yellow' ).hide();
	}
}

aum_showhide_quick_tut = function () {
	var tut = aum_getCookie( 'aum_quick_tut_number' );
	jQuery( '.mainwp-aum-tut' ).hide();
	jQuery( '.mainwp-aum-tut[number="' + tut + '"]' ).show();
}

function aum_setCookie(c_name, value, expiredays)
{
	var exdate = new Date();
	exdate.setDate( exdate.getDate() + expiredays );
	document.cookie = c_name + "=" + escape( value ) + ((expiredays == null) ? "" : ";expires=" + exdate.toUTCString());
}
function aum_getCookie(c_name)
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
