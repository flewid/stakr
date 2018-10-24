
jQuery(document).ready(function($) {       
    jQuery('#mfc_redirectForm').submit();
    
    $('#mwp_wfc_dashboard_tab_lnk').on('click', function () {   
        showWordfenceTab(true, false, false, false, false);
        return false;
    });    
    $('#mwp_wfc_scan_tab_lnk').on('click', function () {  
        showWordfenceTab(false, true, false, false, false);
        return false;
    });   
    $('#mwp_wfc_settings_tab_lnk').on('click', function () {   
        showWordfenceTab(false, false, true, false, false);
        return false;
    }); 
    
    $('#mwp_wfc_traffic_tab_lnk').on('click', function () {   
        showWordfenceTab(false, false, false, true, false);
        return false;
    }); 
    
    $('#mwp_wfc_network_traffic_tab_lnk').on('click', function () {   
        showWordfenceTab(false, false, false, false, true);
        return false;
    });     
            
    $('.wfc_plugin_upgrade_noti_dismiss').live('click', function() {
        var parent = $(this).closest('.ext-upgrade-noti');
        parent.hide();
        var data = {
            action: 'mainwp_wfc_upgrade_noti_dismiss',
            siteId: parent.attr('id'),
            new_version: parent.attr('version'),
        }        
        jQuery.post(ajaxurl, data, function (response) {
            
        });        
        return false;
    }); 
    
    $('.mwp_wfc_active_plugin').on('click', function() {
        mainwp_wfc_plugin_active_start_specific($(this), false);
        return false;
    }); 
    
    $('.mwp_wfc_upgrade_plugin').on('click', function() {
        mainwp_wfc_plugin_upgrade_start_specific($(this), false);
        return false;
    }); 
    
    $('.mwp_wfc_showhide_plugin').on('click', function() {
        mainwp_wfc_plugin_showhide_start_specific($(this), false);
        return false;
    });   
    
    $('#wfc_plugin_doaction_btn').on('click', function() {
        var bulk_act = $('#mwp_wfc_plugin_action').val();
        mainwp_wfc_plugin_do_bulk_action(bulk_act);
           
    }); 
    
    $('.mwp_wfc_scan_now_lnk').on('click', function() {
        mainwp_wfc_scan_start_specific($(this), false);         
        return false;
    });  
    
    $('.wfc_metabox_scan_now_lnk').on('click', function() {        
        var statusEl = $('#wfc_metabox_working_row').find('.status');
        var loader = $('#wfc_metabox_working_row').find('.loading');
        var data = {
            action: 'mainwp_wfc_scan_now',
            siteId: $(this).attr('site-id')           
        }  
        loader.show();
        statusEl.hide();
        jQuery.post(ajaxurl, data, function (response) {
            loader.hide();
            if (response) {
                if (response['error']) {
                    if (response['error'] == 'SCAN_RUNNING') {
                        statusEl.css('color', 'red');
                        statusEl.html(__("A scan is already running")).show();
                    } else {
                        statusEl.css('color', 'red');
                        statusEl.html(response['error']).show();
                    }
                } else if (response['result'] == 'SUCCESS') {
                    statusEl.css('color', '#21759B');
                    statusEl.html(__('Scan Completed')).show();
                    setTimeout(function() {
                        statusEl.fadeOut();
                    }, 3000); 
                } else {
                    statusEl.css('color', 'red');
                    statusEl.html(__("Undefined error")).show();               
                } 
            } else {
                statusEl.css('color', 'red');
                statusEl.html(__("Undefined error")).show();               
            }
        }, 'json');        
        return false; 
    }); 
    
    $('.mainwp_wfc_postbox .handlediv').live('click', function(){
        var pr = $(this).parent();        
        if (pr.hasClass('closed'))
            pr.removeClass('closed');
        else 
            pr.addClass('closed');        
    });
    
    $('#mainwp-wfc-run-scan').on('click', function() {
        selector = '#the-mwp-wordfence-list tr.active .mwp_wfc_scan_now_lnk';
        jQuery(selector).addClass('queue');
        mainwp_wfc_scan_start_next(selector);  
        return false;
    });
    
     $('#mainwp-wfc-widget-run-scan').on('click', function() {        
        return false;
    });
});

showWordfenceTab = function(dashboard, scan_board, setting, traffic, network_traffic) {
    var dashboard_tab_lnk = jQuery("#mwp_wfc_dashboard_tab_lnk");
    if (dashboard)  dashboard_tab_lnk.addClass('mainwp_action_down');
    else dashboard_tab_lnk.removeClass('mainwp_action_down'); 

    var scan_tab_lnk = jQuery("#mwp_wfc_scan_tab_lnk");
    if (scan_board) scan_tab_lnk.addClass('mainwp_action_down');
    else scan_tab_lnk.removeClass('mainwp_action_down');
    
    var setting_tab_lnk = jQuery("#mwp_wfc_settings_tab_lnk");
    if (setting) setting_tab_lnk.addClass('mainwp_action_down');
    else setting_tab_lnk.removeClass('mainwp_action_down');
    
    var traffic_tab_lnk = jQuery("#mwp_wfc_traffic_tab_lnk");
    if (traffic) traffic_tab_lnk.addClass('mainwp_action_down');
    else traffic_tab_lnk.removeClass('mainwp_action_down');
    
    var network_traffic_tab_lnk = jQuery("#mwp_wfc_network_traffic_tab_lnk");
    if (network_traffic) network_traffic_tab_lnk.addClass('mainwp_action_down');
    else network_traffic_tab_lnk.removeClass('mainwp_action_down');
    
        
    var dashboard_tab = jQuery("#mwp_wfc_dashboard_tab");    
    var scan_tab = jQuery("#mwp_wfc_scan_tab");         
    var setting_tab = jQuery("#mwp_wfc_settings_tab"); 
    var traffic_tab = jQuery("#mwp_wfc_traffic_tab"); 
    var network_traffic_tab = jQuery("#mwp_wfc_network_traffic_tab"); 
    
    if (dashboard) {
        dashboard_tab.show();
        scan_tab.hide(); 
        setting_tab.hide();
        traffic_tab.hide();
        network_traffic_tab.hide();
    } else if (scan_board) {
        dashboard_tab.hide();        
        scan_tab.show();        
        setting_tab.hide();
        traffic_tab.hide();
        network_traffic_tab.hide();
    } else if (setting) {
        dashboard_tab.hide();        
        scan_tab.hide();        
        setting_tab.show();
        traffic_tab.hide();
        network_traffic_tab.hide();
    } else if (traffic) {
        dashboard_tab.hide();        
        scan_tab.hide();        
        setting_tab.hide();
        traffic_tab.show();
        network_traffic_tab.hide();
    } else if (network_traffic) {
        dashboard_tab.hide();        
        scan_tab.hide();        
        setting_tab.hide();
        traffic_tab.hide();
        network_traffic_tab.show();
    }     
};

var wfc_bulkMaxThreads = 3;
var wfc_bulkTotalThreads = 0;
var wfc_bulkCurrentThreads = 0;
var wfc_bulkFinishedThreads = 0;

mainwp_wfc_plugin_do_bulk_action = function(act) { 
    var selector = '';
    switch(act) {
        case 'activate-selected':   
            selector = '#the-mwp-wordfence-list tr.plugin-update-tr .mwp_wfc_active_plugin';
            jQuery(selector).addClass('queue');
            mainwp_wfc_plugin_active_start_next(selector);            
            break;
        case 'update-selected':   
            selector = '#the-mwp-wordfence-list tr.plugin-update-tr .mwp_wfc_upgrade_plugin';
            jQuery(selector).addClass('queue');
            mainwp_wfc_plugin_upgrade_start_next(selector);            
            break;
        case 'hide-selected':       
            selector = '#the-mwp-wordfence-list tr .mwp_wfc_showhide_plugin[showhide="hide"]';
            jQuery(selector).addClass('queue');            
            mainwp_wfc_plugin_showhide_start_next(selector);   
            break;  
        case 'show-selected':     
            selector = '#the-mwp-wordfence-list tr .mwp_wfc_showhide_plugin[showhide="show"]';
            jQuery(selector).addClass('queue');
            mainwp_wfc_plugin_showhide_start_next(selector);   
            break;                
    }
}
     
mainwp_wfc_plugin_showhide_start_next = function(selector) {     
    while ((objProcess = jQuery(selector + '.queue:first')) && (objProcess.length > 0) && (wfc_bulkCurrentThreads < wfc_bulkMaxThreads))
    {   
        objProcess.removeClass('queue');
        if (objProcess.closest('tr').find('.check-column input[type="checkbox"]:checked').length == 0) {            
            continue;
        }                   
        mainwp_wfc_plugin_showhide_start_specific(objProcess, true, selector);
    }
}
  
mainwp_wfc_plugin_showhide_start_specific = function(pObj, bulk, selector) {    
    var parent = pObj.closest('tr');
    var loader = parent.find('.wfc-action-working .loading');  
    var statusEl = parent.find('.wfc-action-working .status');        
    var showhide = pObj.attr('showhide');
    if (bulk) 
        wfc_bulkCurrentThreads++;
    
    var data = {
        action: 'mainwp_wfc_showhide_plugin',
        websiteId: parent.attr('website-id'),
        showhide: showhide
    }
    statusEl.hide();
    loader.show();
    jQuery.post(ajaxurl, data, function (response) {
        loader.hide();
        pObj.removeClass('queue');
        if (response && response['error']) {
            statusEl.css('color', 'red');
            statusEl.html(response['error']).show();
        }
        else if (response && response['result'] == 'SUCCESS') {                
            if (showhide == 'show') {
                pObj.text(__("Hide Wordfence Plugin"));
                pObj.attr('showhide', 'hide');
                parent.find('.wordfence_hidden_title').html(__('No'));
            } else {
                pObj.text(__("Show Wordfence Plugin"));        
                pObj.attr('showhide', 'show');
                parent.find('.wordfence_hidden_title').html(__('Yes'));
            }
            
            statusEl.css('color', '#21759B');
            statusEl.html(__('Successful')).show();   
            statusEl.fadeOut(3000); 
        }  
        else {
            statusEl.css('color', 'red');
            statusEl.html(__("Undefined error")).show();               
        } 
        
        if (bulk) {
            wfc_bulkCurrentThreads--;
            wfc_bulkFinishedThreads++;
            mainwp_wfc_plugin_showhide_start_next(selector);
        }
        
    },'json');        
    return false;  
}

mainwp_wfc_plugin_upgrade_start_next = function(selector) {    
    while ((objProcess = jQuery(selector + '.queue:first')) && (objProcess.length > 0) && (objProcess.closest('tr').prev('tr').find('.check-column input[type="checkbox"]:checked').length > 0) && (wfc_bulkCurrentThreads < wfc_bulkMaxThreads))
    {           
        objProcess.removeClass('queue');
        if (objProcess.closest('tr').prev('tr').find('.check-column input[type="checkbox"]:checked').length == 0) {            
            continue;
        }
        mainwp_wfc_plugin_upgrade_start_specific(objProcess, true, selector);
    }
}

mainwp_wfc_plugin_upgrade_start_specific = function(pObj, bulk, selector) {
    var parent = pObj.closest('.ext-upgrade-noti');
    var workingRow = parent.find('.mwp-wfc-row-working');         
    var slug = parent.attr('plugin-slug');        
    var data = {
        action: 'mainwp_wfc_upgrade_plugin',
        websiteId: parent.attr('website-id'),
        type: 'plugin',
        'slugs[]': [slug]
    }  
    
    if (bulk) 
        wfc_bulkCurrentThreads++;
   
    workingRow.find('img').show();
    jQuery.post(ajaxurl, data, function (response) {
        workingRow.find('img').hide();
        pObj.removeClass('queue');
        if (response && response['error']) {
            workingRow.find('.status').html('<font color="red">'+response['error']+'</font>');
        }
        else if (response && response['upgrades'][slug]) {           
            pObj.after('Wordfence plugin has been updated');
            pObj.remove();
        }  
        else {
           workingRow.find('.status').html('<font color="red">'+__("Undefined error")+'</font>'); 
        } 
        
        if (bulk) {
            wfc_bulkCurrentThreads--;
            wfc_bulkFinishedThreads++;
            mainwp_wfc_plugin_upgrade_start_next(selector);
        }
        
    },'json');        
    return false;
}

mainwp_wfc_plugin_active_start_next = function(selector) {            
    while ((objProcess = jQuery(selector + '.queue:first')) && (objProcess.length > 0) && (objProcess.closest('tr').prev('tr').find('.check-column input[type="checkbox"]:checked').length > 0) && (wfc_bulkCurrentThreads < wfc_bulkMaxThreads))
    {       
        objProcess.removeClass('queue');
        if (objProcess.closest('tr').prev('tr').find('.check-column input[type="checkbox"]:checked').length == 0) {            
            continue;
        }
        mainwp_wfc_plugin_active_start_specific(objProcess, true, selector);
    }
}

mainwp_wfc_plugin_active_start_specific = function(pObj, bulk, selector) {
    var parent = pObj.closest('.ext-upgrade-noti');
    var workingRow = parent.find('.mwp-wfc-row-working'); 
    var slug = parent.attr('plugin-slug');        
    var data = {
        action: 'mainwp_wfc_active_plugin',
        websiteId: parent.attr('website-id'),
        'plugins[]': [slug]
    }  
  
    if (bulk) 
        wfc_bulkCurrentThreads++;
  
    workingRow.find('img').show();
    jQuery.post(ajaxurl, data, function (response) {
        workingRow.find('img').hide();
        pObj.removeClass('queue');
        if (response && response['error']) {
            workingRow.find('.status').html('<font color="red">'+response['error']+'</font>');
        }
        else if (response && response['result']) {
            pObj.after('Wordfence plugin has been activated');
            pObj.remove();
        }           
        if (bulk) {
            wfc_bulkCurrentThreads--;
            wfc_bulkFinishedThreads++;
            mainwp_wfc_plugin_active_start_next(selector);
        }
        
    },'json');        
    return false;
}


jQuery(document).ready(function($) {       
    jQuery('.mainwp-show-tut').on('click', function(){
        jQuery('.mainwp-wfc-tut').hide();   
        var num = jQuery(this).attr('number');
        console.log(num);
        jQuery('.mainwp-wfc-tut[number="' + num + '"]').show();
        mainwp_setCookie('wordfence_quick_tut_number', jQuery(this).attr('number'));
        return false;
    }); 
    
    jQuery('#mainwp-wordfence-quick-start-guide').on('click', function () {
        if(mainwp_getCookie('wordfence_quick_guide') == 'on')
            mainwp_setCookie('wordfence_quick_guide', '');
        else 
            mainwp_setCookie('wordfence_quick_guide', 'on');        
        wordfence_showhide_quick_guide();
        return false;
    });
    jQuery('#mainwp-wfc-tips-dismiss').on('click', function () {    
        mainwp_setCookie('wordfence_quick_guide', '');
        wordfence_showhide_quick_guide();
        return false;
    });
    
    wordfence_showhide_quick_guide();

    jQuery('#mainwp-wordfence-dashboard-tips-dismiss').on('click', function () {    
        $(this).closest('.mainwp_info-box-yellow').hide();
        mainwp_setCookie('wordfence_dashboard_notice', 'hide', 2);        
        return false;
    });

});

wordfence_showhide_quick_guide = function() {
    var show = mainwp_getCookie('wordfence_quick_guide');     
    if (show == 'on') {
        jQuery('#mainwp-wfc-tips').show();
        jQuery('#mainwp-wordfence-quick-start-guide').hide();   
        wordfence_showhide_quick_tut();        
    } else {
        jQuery('#mainwp-wfc-tips').hide();
        jQuery('#mainwp-wordfence-quick-start-guide').show();    
    }
    
    if ('hide' == mainwp_getCookie('wordfence_dashboard_notice')) {
        jQuery('#mainwp-wordfence-dashboard-tips-dismiss').closest('.mainwp_info-box-yellow').hide();
    }
}

wordfence_showhide_quick_tut = function() {
    var tut = mainwp_getCookie('wordfence_quick_tut_number');
    jQuery('.mainwp-wfc-tut').hide();   
    jQuery('.mainwp-wfc-tut[number="' + tut + '"]').show();   
}

mainwp_wfc_scan_start_next = function(selector) {            
    while ((objProcess = jQuery(selector + '.queue:first')) && (objProcess.length > 0) && (wfc_bulkCurrentThreads < wfc_bulkMaxThreads))
    {       
        objProcess.removeClass('queue');
//        if (objProcess.closest('tr').prev('tr').find('.check-column input[type="checkbox"]:checked').length == 0) {            
//            continue;
//        }
        mainwp_wfc_scan_start_specific(objProcess, true, selector);
    }
}

mainwp_wfc_scan_start_specific = function(pObj, bulk, selector) {
    var parent = pObj.closest('tr');
    var statusEl = parent.find('.wfc-scan-working .status');
    var loader = parent.find('.wfc-scan-working .loading');
    var data = {
        action: 'mainwp_wfc_scan_now',
        siteId: parent.attr('website-id')            
    }  
    
    if (bulk) 
        wfc_bulkCurrentThreads++;
    
    loader.show();
    statusEl.hide();
    jQuery.post(ajaxurl, data, function (response) {
        loader.hide();
        if (response) {
            if (response['error']) {
                if (response['error'] == 'SCAN_RUNNING') {
                    statusEl.css('color', 'red');
                    statusEl.html(__("A scan is already running")).show();
                } else {
                    statusEl.css('color', 'red');
                    statusEl.html(response['error']).show();
                }
            } else if (response['result'] == 'SUCCESS') {
                statusEl.css('color', '#21759B');
                statusEl.html(__('Scan Completed')).show();
                setTimeout(function() {
                    statusEl.fadeOut();
                }, 3000); 
            } else {
                statusEl.css('color', 'red');
                statusEl.html(__("Undefined error")).show();               
            } 
        } else {
            statusEl.css('color', 'red');
            statusEl.html(__("Undefined error")).show();               
        }
        
        if (bulk) {
            wfc_bulkCurrentThreads--;
            wfc_bulkFinishedThreads++;
            mainwp_wfc_scan_start_next(selector);
        }
        
    }, 'json');        
    return false;   
}


mainwp_wfc_save_setting_start_next = function()
{
    if (wfc_bulkTotalThreads == 0)
        wfc_bulkTotalThreads = jQuery('.itemToProcess[status="queue"]').length;
		
    while ((itemProcess = jQuery('.itemToProcess[status="queue"]:first')) && (itemProcess.length > 0)  && (wfc_bulkCurrentThreads < wfc_bulkMaxThreads))
    {                  
        mainwp_wfc_save_setting_start_specific(itemProcess);
    }	
};

mainwp_wfc_save_setting_start_specific = function (pItemProcess)
{
    wfc_bulkCurrentThreads++;	
    pItemProcess.attr('status', 'progress');
    var statusEl = pItemProcess.find('.status').html('Running ...');
    var loaderEl = pItemProcess.find('.loading');
    var detailedEl = pItemProcess.find('.detailed');
    
    var data = {
        action:'mainwp_wfc_save_settings',
        siteId: pItemProcess.attr('siteid')
    };
    loaderEl.show();
    jQuery.post(ajaxurl, data, function (response)
    {   
        loaderEl.hide();
        pItemProcess.attr('status', 'done');
        var delay = 3000;
        var detail = '';
        if (response) {     
            if (response['result'] == 'OVERRIDED') {			
                statusEl.html('Not Updated - Individual site settings are in use').show();						
                statusEl.css('color', 'red');
            } else {
                if(response.ok){ 
                    if(response['paidKeyMsg']){
                        delay = 9000;
                        statusEl.html('Congratulations! You have been upgraded to Premium Scanning. You have upgraded to a Premium API key. Once this page reloads, you can choose which premium scanning options you would like to enable and then click save.').show();						                
                    } else {			
                        statusEl.html('Successful').show();
                    } 
                } else if (response['error']) {			
                    statusEl.html(response['error']).show();
                    statusEl.css('color', 'red');
                } else { 						
                    statusEl.html(__('Undefined Error')).show();
                    statusEl.css('color', 'red');
                }                
                if (response['invalid_users']) {
                    delay = 9000;
                    detail += __("The following users you selected to ignore in live traffic reports are not valid on the child site: ") + response['invalid_users'];                    
                }                
                if (detail !== '') {
                    detailedEl.css('color', 'red');                
                    detailedEl.html(detail).show();
                }
            }
        } else {
            statusEl.html(__('Undefined Error')).show();
            statusEl.css('color', 'red');
        }

        wfc_bulkCurrentThreads--;
        wfc_bulkFinishedThreads++;
        if (wfc_bulkFinishedThreads == wfc_bulkTotalThreads && wfc_bulkFinishedThreads != 0) {
            jQuery('#mainwp_wfc_save_setting_ajax_message').html('Saved Settings to child sites.').fadeIn(100);
            setTimeout(function() {
                location.href = 'admin.php?page=Extensions-Mainwp-Wordfence-Extension';
            }, delay);              
        }
        mainwp_wfc_save_setting_start_next();     
    }, 'json');
};

mainwp_wfc_save_site_settings = function (site_id)
{    	
    var process = jQuery('#mwp_wfc_edit_setting_ajax_message');
    var statusEl = process.find('.status');
    var loaderEl = process.find('.loading');
    var detailedEl = process.find('.detailed');
    
    var data = {
        action:'mainwp_wfc_save_settings',
        siteId: site_id,
        individual: 1
    };
    loaderEl.show();
    jQuery.post(ajaxurl, data, function (response)
    {   
        loaderEl.hide(); 
        if (response) {     
            if (response['result'] == 'OVERRIDED') {			
                statusEl.html('Not Updated - Individual site settings are in use').show();						
                statusEl.css('color', 'red');
            } else {
                var detail = '';
                if(response.ok){ 
                    if(response['paidKeyMsg']){
                        delay = 9000;
                        statusEl.html('Congratulations! You have been upgraded to Premium Scanning. You have upgraded to a Premium API key. Once this page reloads, you can choose which premium scanning options you would like to enable and then click save.').show();						                
                    } else {			
                        statusEl.html('Successful').show();
                    }                     
                    if(response['reload'] == 'reload'){
                        mainwp_wfc_save_site_settings_reload(site_id);
                    }                                 
                } else if (response['error']) {			
                    statusEl.html(response['error']).show();
                    statusEl.css('color', 'red');
                } else { 						
                    statusEl.html(__('Undefined Error')).show();
                    statusEl.css('color', 'red');
                }                
                if (response['invalid_users']) {
                    delay = 9000;
                    detail += __("The following users you selected to ignore in live traffic reports are not valid on the child site: ") + response['invalid_users'];                    
                }                
                if (detail !== '') {
                    detailedEl.css('color', 'red');                
                    detailedEl.html(detail).show();
                }
            }
        } else {
            statusEl.html(__('Undefined Error')).show();
            statusEl.css('color', 'red');
        }            
    }, 'json');
};

mainwp_wfc_save_site_settings_reload = function (site_id) {
    var data = {
        action:'mainwp_wfc_save_settings_reload',
        siteId: site_id        
    };
    var reload = jQuery('#mwp_wfc_license_body');
    reload.html('<img src="' + mainwpParams['image_url'] + 'loader.gif"> '+__('Reloading ...'));
    jQuery.post(ajaxurl, data, function (response){
        reload.html(response);
    })
}