/*
Plugin-Name: Wordfence Security
Plugin-URI: http://www.wordfence.com/
Description: Wordfence Security - Anti-virus, Firewall and High Speed Cache
Author: Wordfence
Version: 5.2.1
Author-URI: http://www.wordfence.com/
*/

if(! window['mainwp_wordfenceAdmin']){ 
window['mainwp_wordfenceAdmin'] = {
	loadingCount: 0,
	colorboxQueue: [],
	colorboxOpen: false,
	mode: '',
	visibleIssuesPanel: 'new',	
	nonce: false,
        activityLogUpdatePending: false,
        tickerUpdatePending: false,
        newestActivityTime: 0, //must be 0 to force loading of all initially
	lastALogCtime: 0,
        reloadConfigPage: false,
	activityQueue: [],
	totalActAdded: 0,
	maxActivityLogItems: 1000,
	debugOn: false,
        siteId: 0,
        cacheType: '',
        liveTrafficEnabled: 0,
        forceUpdate: false,     
        bulkMaxThreads: 3,
        bulkTotalThreads: 0,
        bulkCurrentThreads: 0,
        bulkFinishedThreads: 0,
	init: function(){
            this.nonce = mainwp_WordfenceAdminVars.firstNonce; 
            var startTicker = false;
            if(jQuery('#mwp_wordfenceMode_scan').length > 0){
                    this.mode = 'scan';
                    this.siteId = jQuery('#mwp_wordfenceMode_scan').attr('site-id');
                    this.loadFirstActivityLog();
            } else if(jQuery('#mwp_wordfenceMode_activity').length > 0){
                        this.mode = 'activity';
                        this.siteId = jQuery('#mwp_wordfenceMode_activity').attr('site-id');
                        this.liveTrafficEnabled = jQuery('#mwp_wordfenceMode_activity').attr('liveTrafficEnabled');
                        this.cacheType = jQuery('#mwp_wordfenceMode_activity').attr('cacheType');   
                        mainwp_WordfenceAdminVars.actUpdateInterval = jQuery('#mwp_wordfenceMode_activity').attr('actUpdateInterval')
			var self = this;	                        						
//			jQuery('#mainwp_wfc_activity_real_time').change(function(){                            
//                            if(/^(?:falcon|php)$/.test(self.cacheType) ){
//                                jQuery('#mainwp_wfc_activity_real_time').attr('checked', false);
//                                self.colorbox('400px', "Live Traffic not available in high performance mode", "Please note that you can't enable live traffic when Falcon Engine or basic caching is enabled. This is done for performance reasons. If you want live traffic, go to the 'Performance Setup' menu and disable caching.");
//                            } else {                                
//                                setTimeout(function(){                 
//                                    self.updateLiveTraffic('mainwp_wfc_activity_real_time');
//                                }, 100);                                
//                            } 
//                        });                
			if(this.liveTrafficEnabled == 1 && this.cacheType != 'php' && this.cacheType != 'falcon'){
				this.activityMode = 'hit';
			} else {
				this.activityMode = 'loginLogout';
				this.switchTab(jQuery('#wfLoginLogoutTab'), 'wfTab1', 'wfDataPanel', 'wfActivity_loginLogout', function(){ MWP_WFAD.activityTabChanged(); });
			}
			startTicker = true;
            } else if(jQuery('#mwp_wordfenceMode_network_activity').length > 0){
                    var itemProcess = jQuery('.wfc_NetworkTrafficItemProcess[status="queue"]:first');
                    mainwp_WordfenceAdminVars.actUpdateInterval = jQuery('#mwp_wordfenceMode_network_activity').attr('actUpdateInterval')
                    if (itemProcess.length  > 0) {                            
                        this.siteId = itemProcess.attr('site-id');                        
                        this.cacheType = itemProcess.attr('cacheType');                        
                        itemProcess.attr('status', 'processing');

                        this.mode = 'network_activity';  
                        this.activityMode = 'hit';
                        startTicker = true;
                    }                         
            } else if(jQuery('#mwp_wordfenceMode_settings').length > 0){
                this.mode = 'settings';
            } else {
                this.mode = false;
            }	            

            if(this.mode){ //We are in a Wordfence page
                var self = this;  
                if(startTicker){                    
                    if (this.mode == 'activity') {
                        mainwp_WordfenceAdminVars.actUpdateInterval;
                    } else 
                        mainwp_WordfenceAdminVars.actUpdateInterval = 2;
                    this.updateTicker();
                    this.liveInt = setInterval(function(){ self.updateTicker(); }, mainwp_WordfenceAdminVars.actUpdateInterval);                    
                }
                jQuery(document).bind('cbox_closed', function(){ self.colorboxIsOpen = false; self.colorboxServiceQueue(); });
            }             
	},	
	showLoading: function(){
		this.loadingCount++;
		if(this.loadingCount == 1){
			jQuery('<div id="mwp_wordfenceWorking">Wordfence is working...</div>').appendTo('body');
		}
	},
	removeLoading: function(){
		this.loadingCount--;
		if(this.loadingCount == 0){
			jQuery('#mwp_wordfenceWorking').remove();
		}
	},
	startActivityLogUpdates: function(){
		var self = this;               
		setInterval(function(){                    
                    self.updateActivityLog();                    
                }, parseInt(mainwp_WordfenceAdminVars.actUpdateInterval));
	},
	updateActivityLog: function(){            
            if (this.siteId <= 0) //ok
                return;             
            if(this.activityLogUpdatePending){
                    return;
            }            
            this.activityLogUpdatePending = true;
            var self = this;            
            this.ajax('mainwp_wfc_activityLogUpdate', {
                    lastctime: this.lastALogCtime,
                    site_id: this.siteId 
                    }, function(res){ self.doneUpdateActivityLog(res); }, function(){ self.activityLogUpdatePending = false; }, true);

	},
	doneUpdateActivityLog: function(res){
		this.actNextUpdateAt = (new Date()).getTime() + parseInt(mainwp_WordfenceAdminVars.actUpdateInterval);
		if(res.ok){
			if(res.items.length > 0){
				this.activityQueue.push.apply(this.activityQueue, res.items);
				this.lastALogCtime = res.items[res.items.length - 1].ctime;
				this.processActQueue(res.currentScanID);
			}
		}
		this.activityLogUpdatePending = false;
	},
	processActQueue: function(currentScanID){
		if(this.activityQueue.length > 0){
			this.addActItem(this.activityQueue.shift());
			this.totalActAdded++;
			if(this.totalActAdded > this.maxActivityLogItems){
				jQuery('#mwp_consoleActivity div:first').remove();
				this.totalActAdded--;
			}
			var timeTillNextUpdate = this.actNextUpdateAt - (new Date()).getTime();
			var maxRate = 50 / 1000; //Rate per millisecond
			var bulkTotal = 0;
			while(this.activityQueue.length > 0 && this.activityQueue.length / timeTillNextUpdate > maxRate ){
				var item = this.activityQueue.shift();
				if(item){
					bulkTotal++;
					this.addActItem(item);
				}
			}
			this.totalActAdded += bulkTotal;
			if(this.totalActAdded > this.maxActivityLogItems){
				jQuery('#mwp_consoleActivity div:lt(' + bulkTotal + ')').remove();
				this.totalActAdded -= bulkTotal;
			}
			var minDelay = 100;
			var delay = minDelay;
			if(timeTillNextUpdate < 1){
				delay = minDelay;
			} else {
				delay = Math.round(timeTillNextUpdate / this.activityQueue.length);
				if(delay < minDelay){ delay = minDelay; }
			}
			var self = this;
			setTimeout(function(){ self.processActQueue(); }, delay);
		}
		jQuery('#mwp_consoleActivity').scrollTop(jQuery('#mwp_consoleActivity').prop('scrollHeight'));
	},
	processActArray: function(arr){
		for(var i = 0; i < arr.length; i++){
			this.addActItem(arr[i]);
		}
	},
	addActItem: function(item){
		if(! item){ return; }
		if(! item.msg){ return; }
		if(item.msg.indexOf('SUM_') == 0){
			this.processSummaryLine(item);
			jQuery('#mwp_consoleSummary').scrollTop(jQuery('#mwp_consoleSummary').prop('scrollHeight'));
			jQuery('#wfStartingScan').addClass('wfc-summary-ok').html('Done.');
		} else if(this.debugOn || item.level < 4){
			
			var html = '<div class="wfActivityLine';
			if(this.debugOn){
				html += ' wf' + item.type;
			}
			html += '">[' + item.date + ']&nbsp;' + item.msg + '</div>';
			jQuery('#mwp_consoleActivity').append(html);
			if(/Scan complete\./i.test(item.msg)){
				this.loadIssues();
			}
		}
	},
	processSummaryLine: function(item){
		if(item.msg.indexOf('SUM_START:') != -1){
			var msg = item.msg.replace('SUM_START:', '');
			jQuery('#mwp_consoleSummary').append('<div class="wfSummaryLine"><div class="wfc-summary-date">[' + item.date + ']</div><div class="wfc-summary-msg">' + msg + '</div><div class="wfc-summary-result"><div class="wfc-summary-loading"></div></div><div class="wfc-clear"></div>');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_ENDBAD') != -1){
			var msg = item.msg.replace('SUM_ENDBAD:', '');
			jQuery('div.wfc-summary-msg:contains("' + msg + '")').next().addClass('wfc-summary-bad').html('Problems found.');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_ENDFAILED') != -1){
			var msg = item.msg.replace('SUM_ENDFAILED:', '');
			jQuery('div.wfc-summary-msg:contains("' + msg + '")').next().addClass('wfc-summary-bad').html('Failed.');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_ENDOK') != -1){
			var msg = item.msg.replace('SUM_ENDOK:', '');
			jQuery('div.wfc-summary-msg:contains("' + msg + '")').next().addClass('wfc-summary-ok').html('Secure.');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_ENDSUCCESS') != -1){
			var msg = item.msg.replace('SUM_ENDSUCCESS:', '');
			jQuery('div.wfc-summary-msg:contains("' + msg + '")').next().addClass('wfc-summary-ok').html('Success.');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_ENDERR') != -1){
			var msg = item.msg.replace('SUM_ENDERR:', '');
			jQuery('div.wfc-summary-msg:contains("' + msg + '")').next().addClass('wfc-summary-err').html('An error occurred.');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_DISABLED:') != -1){
			var msg = item.msg.replace('SUM_DISABLED:', '');
			jQuery('#mwp_consoleSummary').append('<div class="wfSummaryLine"><div class="wfc-summary-date">[' + item.date + ']</div><div class="wfc-summary-msg">' + msg + '</div><div class="wfc-summary-result">Disabled [<a href="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=setting">Visit Options to Enable</a>]</div><div class="wfc-clear"></div>');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_PAIDONLY:') != -1){
			var msg = item.msg.replace('SUM_PAIDONLY:', '');
			jQuery('#mwp_consoleSummary').append('<div class="wfSummaryLine"><div class="wfc-summary-date">[' + item.date + ']</div><div class="wfc-summary-msg">' + msg + '</div><div class="wfc-summary-result"><a href="https://www.wordfence.com/wordfence-signup/" target="_blank">Paid Members Only</a></div><div class="wfc-clear"></div>');
			summaryUpdated = true;
		} else if(item.msg.indexOf('SUM_FINAL:') != -1){
			var msg = item.msg.replace('SUM_FINAL:', '');
			jQuery('#mwp_consoleSummary').append('<div class="wfSummaryLine"><div class="wfc-summary-date">[' + item.date + ']</div><div class="wfc-summary-msg wfc-summary-final">' + msg + '</div><div class="wfc-summary-result wfc-summary-ok">Scan Complete.</div><div class="wfc-clear"></div>');
		} else if(item.msg.indexOf('SUM_PREP:') != -1){
			var msg = item.msg.replace('SUM_PREP:', '');
			jQuery('#mwp_consoleSummary').empty().html('<div class="wfSummaryLine"><div class="wfc-summary-date">[' + item.date + ']</div><div class="wfc-summary-msg">' + msg + '</div><div class="wfc-summary-result" id="wfStartingScan"><div class="wfc-summary-loading"></div></div><div class="wfc-clear"></div>');
		} else if(item.msg.indexOf('SUM_KILLED:') != -1){
			var msg = item.msg.replace('SUM_KILLED:', '');
			jQuery('#mwp_consoleSummary').empty().html('<div class="wfSummaryLine"><div class="wfc-summary-date">[' + item.date + ']</div><div class="wfc-summary-msg">' + msg + '</div><div class="wfc-summary-result wfc-summary-ok">Scan Complete.</div><div class="wfc-clear"></div>');
		}
	},
	processActQueueItem: function(){
		var item = this.activityQueue.shift();
		if(item){
			jQuery('#mwp_consoleActivity').append('<div class="wfActivityLine wf' + item.type + '">[' + item.date + ']&nbsp;' + item.msg + '</div>');
			this.totalActAdded++;
			if(this.totalActAdded > this.maxActivityLogItems){
				jQuery('#mwp_consoleActivity div:first').remove();
				this.totalActAdded--;
			}
			if(item.msg == 'Scan complete.'){
				this.loadIssues();
			}
		}
	},
        downgradeLicense: function(site_id){
		this.colorbox('400px', "Confirm Downgrade", "Are you sure you want to downgrade your Wordfence Premium License? This will disable all Premium features and return you to the free version of Wordfence. <a href=\"https://www.wordfence.com/manage-wordfence-api-keys/\" target=\"_blank\">Click here to renew your paid membership</a> or click the button below to confirm you want to downgrade.<br /><br /><input type=\"button\" value=\"Downgrade and disable Premium features\" onclick=\"MWP_WFAD.downgradeLicenseConfirm(" + site_id + ");\" /><br />");
	},
	downgradeLicenseConfirm: function(site_id){
		jQuery.colorbox.close();
		this.ajax('mainwp_wfc_downgradeLicense', {site_id: site_id}, function(res){ location.reload(true); });
	},
        loadFirstActivityLog: function(){
            if (this.siteId <= 0) // ok
                return;            
            if(this.mode != 'scan'){
                    return;
            }                    
            if(this.activityLogUpdatePending){
                    return;
            }
            this.activityLogUpdatePending = true;
            var self = this;
            
            this.ajax('mainwp_wfc_loadFirstActivityLog', {site_id: this.siteId }, 
                            function(res){                                
                                self.doneLoadFirstActivityLog(res);                                                                                                                                                                          
                                self.loadIssues();                                 
                                self.startActivityLogUpdates();
                            }, 
                            function(){ 
                                self.activityLogUpdatePending = false; 
                                self.loadIssues(); 
                                self.startActivityLogUpdates();
                            });
	},        
        doneLoadFirstActivityLog: function(res){
            jQuery('#mwp_consoleActivity').html(res['result']);
            this.lastALogCtime = res['lastctime'];      
            if (res['not_found_events'])
                jQuery('#mwp_consoleSummary').html('Welcome to Wordfence!<br><br>To get started, simply click the "Scan Now" link at the "Wordfence Dashboard" tab to start your first scan.');
            else if (res['summary'])
                this.processActArray(res['summary']);  
            jQuery('#mwp_consoleActivity').scrollTop(jQuery('#mwp_consoleActivity').prop('scrollHeight'));
            jQuery('#mwp_consoleSummary').scrollTop(jQuery('#mwp_consoleSummary').prop('scrollHeight'));
             this.activityLogUpdatePending = false;
	},              
	loadIssues: function(callback){
            if (this.siteId <= 0) // ok
                return; 
            
            if(this.mode != 'scan'){
                    return;
            }
            
            var self = this;            
            this.ajax('mainwp_wfc_loadIssues', { site_id: this.siteId }, function(res){                    
                    self.displayIssues(res, callback);
                });
	},      
        switchTab: function(tabElement, tabClass, contentClass, selectedContentID, callback){
		jQuery('.' + tabClass).removeClass('selected');
		jQuery(tabElement).addClass('selected');
		jQuery('.' + contentClass).hide().html('<div class="wfLoadingWhite32"></div>');
		var func = function(){};
		if(callback){
			func = function(){ callback(); };
		}
		jQuery('#' + selectedContentID).fadeIn(func);
	},
	activityTabChanged: function(){
		var mode = jQuery('.wfDataPanel:visible')[0].id.replace('wfActivity_','');             
		if(! mode){ return; }                
		this.activityMode = mode;		
		this.reloadActivities();
	},
	reloadActivities: function(){
		jQuery('#wfActivity_' + this.activityMode).html('<div class="wfLoadingWhite32"></div>');
                if (this.mode == 'activity')
                    this.newestActivityTime = 0;
                else if (this.mode == 'network_activity') {
                    jQuery('.wfc_NetworkTrafficItemProcess').attr('newestActivityTime', 0);
                    jQuery('.wfc_NetworkTrafficItemProcess').attr('status', 'queue');
                }                    
		this.updateTicker(true);
	},
        initActivities: function(){
            if (this.activityModeSaved) 
                this.activityMode = this.activityModeSaved;                
            this.tickerUpdatePending = false;
            
            jQuery('#wfActivity_' + this.activityMode).html('<div class="wfLoadingWhite32"></div>');
            if (this.mode == 'activity')
                this.newestActivityTime = 0;
            else if (this.mode == 'network_activity') {
                jQuery('.wfc_NetworkTrafficItemProcess').attr('newestActivityTime', 0);
                jQuery('.wfc_NetworkTrafficItemProcess').attr('status', 'queue');
            }                    
        },
        restartActivities: function(){
                var self = this;
                this.initActivities();                 
		this.updateTicker();               
                this.liveInt = setInterval(function(){ self.updateTicker(); }, mainwp_WordfenceAdminVars.actUpdateInterval);                    
	},
        updateTicker: function(forceUpdate){                                
		if( (! forceUpdate) && this.tickerUpdatePending){
			return;
		} 
                if (this.mode == 'network_activity') {
                    var itemProcess = jQuery('.wfc_NetworkTrafficItemProcess[status="queue"]:first');
                    if (itemProcess.length  == 0) {
                        jQuery('.wfc_NetworkTrafficItemProcess').attr('status', 'queue');                
                        if (jQuery('.wfc_NetworkTrafficItemProcess[status="queue"]').length > 0)
                            this.updateTicker(forceUpdate);                  
                        return;
                    } 
                    this.siteId = itemProcess.attr('site-id');                    
                    this.cacheType = itemProcess.attr('cacheType');   
                    this.newestActivityTime = itemProcess.attr('newestActivityTime');
                    itemProcess.attr('status', 'processed');
                }
                               
                if (this.siteId <= 0) // ok
                    return;
                
                if (forceUpdate)
                    this.forceUpdate = forceUpdate;
                
		this.tickerUpdatePending = true;
		var self = this;
		var alsoGet = '';
		var otherParams = '';
		if((this.mode == 'activity' || this.mode == 'network_activity') && /^(?:404|hit|human|ruser|gCrawler|crawler|loginLogout)$/.test(this.activityMode)){
			alsoGet = 'logList_' + this.activityMode;
			otherParams = this.newestActivityTime;
		} 
                
                var data = { 
                    alsoGet: alsoGet,
                    otherParams: otherParams,                                        
                    forceUpdate: forceUpdate,
                    site_id: this.siteId,
                    cacheType: this.cacheType,                    
                    mode: this.mode
                };       
                
		this.ajax('mainwp_wfc_ticker', data, function(res){ 
                                if(res['reload'] == 'reload'){
                                    self.colorbox('400px', "Please reload this page", "A config option on the site has been change and requires a page reload. Click the button below to reload this page to update the menu.<br /><br /><center><input type='button' name='wfReload' value='Reload page' onclick='window.location.reload(true);' /></center>");
                                    return;
				}                                 
                                // to fix display
                                if (self.forceUpdate && !res['forceUpdate'])
                                    return;
                                else if (self.forceUpdate)
                                    self.forceUpdate = false;
                                
                                self.handleTickerReturn(res); 
                        }, function(){ 
                                self.tickerUpdatePending = false; 
                        }, true);
	},            
	handleTickerReturn: function(res){            
            this.tickerUpdatePending = false;
            var newMsg = "";
            var siteProcess;
            if (this.mode == 'network_activity') {
                siteProcess = jQuery('.wfc_NetworkTrafficItemProcess[site-id="' + res.site_id + '"]');
                var statusSite = jQuery('#wfLiveStatusSite');                                
                if (res.site_id != statusSite.attr('site-id')) {     
                    statusSite.attr('site-id', res.site_id);
                    statusSite.hide().html(siteProcess.attr('site-name') + ' -&nbsp;').fadeIn(200);
                }                
            }
                
            var oldMsg = jQuery('#wfLiveStatus').text();
            if( res.msg ){ 
                    newMsg = res.msg;
            } else {
                    newMsg = "Idle";
            }
            if(newMsg && newMsg != oldMsg){
                    jQuery('#wfLiveStatus').hide().html(newMsg).fadeIn(200);
            }                
            if(this.mode == 'activity' || this.mode == 'network_activity'){
                    if(res.alsoGet != 'logList_' + this.activityMode){ return; } //user switched panels since ajax request started
                    if(res.events.length > 0){
                        if(this.mode == 'activity') {
                            this.newestActivityTime = res.events[0]['ctime'];
                        } else if (this.mode == 'network_activity') {                            
                            siteProcess.attr('newestActivityTime', res.events[0]['ctime']);
                        }
                    }
                    var haveEvents = false;
                    if(jQuery('#wfActivity_' + this.activityMode + ' .wfActEvent').length > 0){
                            haveEvents = true;
                    }
                    if(res.events.length > 0){
                            if(! haveEvents){
                                    jQuery('#wfActivity_' + this.activityMode).empty();
                            }
                            for(i = res.events.length - 1; i >= 0; i--){
                                    var elemID = '#wfActEvent_' + res.events[i].id;
                                    if(jQuery(elemID).length < 1){
                                            res.events[i]['activityMode'] = this.activityMode;
                                            res.events[i]['site_id'] = res.site_id
                                            var newElem;
                                            if(this.activityMode == 'loginLogout'){
                                                    newElem = jQuery('#wfLoginLogoutEventTmpl').tmpl(res.events[i]);
                                            } else {
                                                    newElem = jQuery('#wfHitsEventTmpl').tmpl(res.events[i]);
                                            }
                                            jQuery(newElem).find('.wfTimeAgo').data('wfctime', res.events[i].ctime);
                                            newElem.prependTo('#wfActivity_' + this.activityMode).fadeIn();
                                    }
                            }
                            this.reverseLookupIPs(res.site_id);
                    } else {
                            if(! haveEvents){
                                    jQuery('#wfActivity_' + this.activityMode).html('<div>No events to report yet.</div>');
                            }
                    }
                    var self = this;
                    jQuery('.wfTimeAgo').each(function(idx, elem){
                            jQuery(elem).html(self.makeTimeAgo(res.serverTime - jQuery(elem).data('wfctime')) + ' ago');
                            });
            } 
	},
        utf8_to_b64: function ( str ) {
            return window.btoa(str);
            //return window.btoa(encodeURIComponent( escape( str )));
        },  
        staticNetworkTabChanged: function(){            
            var mode = jQuery('.wfDataPanel:visible')[0].id.replace('wfActivity_','');
            if(! mode){ return; }
            this.activityMode = mode;           
            jQuery('.wfc_NetworkTrafficItemProcess').attr('status', 'queue');
            var contentElem = '#wfActivity_' + this.activityMode;                          
            this.loadStaticNetworkPanelNext(contentElem, true);            
	},
        loadStaticNetworkPanelNext: function(contentEl, first){ 
            var itemProcess = jQuery('.wfc_NetworkTrafficItemProcess[status="queue"]:first');
            if (itemProcess.length  <= 0)                  
                return; 
            
            var site_id = itemProcess.attr('site-id');
            var site_name = itemProcess.attr('site-name');
            var self = this;
            this.ajax('mainwp_wfc_loadStaticPanel', {
                    site_id: site_id,
                    mode: this.activityMode
                    }, function(res){                             
                            res.site_id = site_id;  
                            res.site_name = site_name;
                            if (first)
                                jQuery(contentEl).empty();                            
                            self.completeLoadNetworkStaticPanel(res, contentEl);
                            itemProcess.attr('status', 'processed');                            
                            self.loadStaticNetworkPanelNext(contentEl);
                    });
        },
        completeLoadNetworkStaticPanel: function(res, contentEl){		
		if(res.results && res.results.length > 0){
			var tmpl;
			if(this.activityMode == 'topScanners' || this.activityMode == 'topLeechers'){
				tmpl = '#wfLeechersTmpl';
			} else if(this.activityMode == 'blockedIPs'){
				tmpl = '#wfBlockedIPsTmpl';
			} else if(this.activityMode == 'lockedOutIPs'){
				tmpl = '#wfLockedOutIPsTmpl';
			} else if(this.activityMode == 'throttledIPs'){
				tmpl = '#wfThrottledIPsTmpl';
			} else { return; }
			var i, j, chunk = 1000;
			var bigArray = res.results.slice(0);
			res.results = false;
			for(i = 0, j = bigArray.length; i < j; i += chunk){
				res.results = bigArray.slice(i, i + chunk);
				jQuery(tmpl).tmpl(res).appendTo(contentEl);
			}
			this.reverseLookupIPs(res.site_id);
		} else {
			if(this.activityMode == 'topScanners' || this.activityMode == 'topLeechers'){
				jQuery("<span>" + res.site_name + ": No site hits have been logged yet. Check back soon.</span><br>").appendTo(contentEl);
			} else if(this.activityMode == 'blockedIPs'){
				jQuery("<span>" + res.site_name + ": No IP addresses have been blocked yet. If you manually block an IP address or if Wordfence automatically blocks one, it will appear here.</span><br>").appendTo(contentEl);
			} else if(this.activityMode == 'lockedOutIPs'){
				jQuery("<span>" + res.site_name + ": No IP addresses have been locked out from signing in or using the password recovery system.</span><br>").appendTo(contentEl);
			} else if(this.activityMode == 'throttledIPs'){
				jQuery("<span>" + res.site_name + ": No IP addresses have been throttled yet. If an IP address accesses the site too quickly and breaks one of the Wordfence rules, it will appear here.</span><br>").appendTo(contentEl);
			} else { return; }
		}
	},        
        staticTabChanged: function(site_id){
            var mode = jQuery('.wfDataPanel:visible')[0].id.replace('wfActivity_','');
            if(! mode){ return; }
            this.activityMode = mode;
            
            var self = this;
            this.ajax('mainwp_wfc_loadStaticPanel', {
                    site_id: site_id,
                    mode: this.activityMode
                    }, function(res){ 
                            res.site_id = site_id;
                            self.completeLoadStaticPanel(res);
                    });          
	},        
	completeLoadStaticPanel: function(res){
		var contentElem = '#wfActivity_' + this.activityMode;
		jQuery(contentElem).empty();
		if(res.results && res.results.length > 0){
			var tmpl;
			if(this.activityMode == 'topScanners' || this.activityMode == 'topLeechers'){
				tmpl = '#wfLeechersTmpl';
			} else if(this.activityMode == 'blockedIPs'){
				tmpl = '#wfBlockedIPsTmpl';
			} else if(this.activityMode == 'lockedOutIPs'){
				tmpl = '#wfLockedOutIPsTmpl';
			} else if(this.activityMode == 'throttledIPs'){
				tmpl = '#wfThrottledIPsTmpl';
			} else { return; }
			var i, j, chunk = 1000;
			var bigArray = res.results.slice(0);
			res.results = false;
			for(i = 0, j = bigArray.length; i < j; i += chunk){
				res.results = bigArray.slice(i, i + chunk);
				jQuery(tmpl).tmpl(res).appendTo(contentElem);
			}
			this.reverseLookupIPs(res.site_id);
		} else {
			if(this.activityMode == 'topScanners' || this.activityMode == 'topLeechers'){
				jQuery(contentElem).html("No site hits have been logged yet. Check back soon.");
			} else if(this.activityMode == 'blockedIPs'){
				jQuery(contentElem).html("No IP addresses have been blocked yet. If you manually block an IP address or if Wordfence automatically blocks one, it will appear here.");
			} else if(this.activityMode == 'lockedOutIPs'){
				jQuery(contentElem).html("No IP addresses have been locked out from signing in or using the password recovery system.");
			} else if(this.activityMode == 'throttledIPs'){
				jQuery(contentElem).html("No IP addresses have been throttled yet. If an IP address accesses the site too quickly and breaks one of the Wordfence rules, it will appear here.");
			} else { return; }
		}
	},
        reverseLookupIPs: function(site_id){   
                
                if (site_id <= 0)
                    return;
                
		var ips = [];
		jQuery('.wfReverseLookup').each(function(idx, elem){
			var txt = jQuery(elem).text();
			if(/^\d+\.\d+\.\d+\.\d+$/.test(txt) && (! jQuery(elem).data('wfReverseDone'))){
				jQuery(elem).data('wfReverseDone', true);
				ips.push(jQuery(elem).text());
			}
		});
		if(ips.length < 1){ return; }
		var uni = {};
		var uniqueIPs = [];
		for(var i = 0; i < ips.length; i++){
			if(! uni[ips[i]]){
				uni[ips[i]] = true;
				uniqueIPs.push(ips[i]);
			}
		}
		this.ajax('mainwp_wfc_reverseLookup', {
			ips: uniqueIPs.join(','),
                        site_id: site_id
			},
			function(res){
				if(res.ok){
					jQuery('.wfReverseLookup').each(function(idx, elem){
						var txt = jQuery(elem).text();
						for(ip in res.ips){ 
							if(txt == ip){
								if(res.ips[ip]){
									jQuery(elem).html('<strong>Hostname:</strong>&nbsp;' + res.ips[ip]);
								} else {
									jQuery(elem).html('');
								}
							}
						}
						});
					}
				}, false, false);
	},
        makeIPTrafLink: function(IP, site_id){            
            var loc = '?_wfsf=IPTraf&nonce=child_temp_nonce&IP=' + encodeURIComponent(IP);
            return '&websiteid=' + site_id + '&open_location=' + this.utf8_to_b64(loc);
	},
        makeBlockNetworkLink: function(IP, site_id){            
            var loc = this.utf8_to_b64('admin.php?page=WordfenceWhois&wfnetworkblock=1&whoisval=' + IP);
            return "admin.php?page=SiteOpen&newWindow=yes&websiteid=" + site_id + "&location=" + loc;
	},
        makeWhoIsLink: function(IP){
            var loc = this.utf8_to_b64('admin.php?page=WordfenceWhois&whoisval=' + IP);
            return "admin.php?page=SiteOpen&newWindow=yes&websiteid=" + this.siteId + "&location=" + loc;
	},
        unblockIP: function(IP, site_id){
            var self = this;
            this.ajax('mainwp_wfc_unblockIP', {
                    IP: IP,
                    site_id: site_id
                    }, function(res){ 
                            self.reloadActivities(); 
                            });
	},        
//        updateLiveTraffic: function(elemID){
//            if (this.siteId <= 0)
//                return;
//            var self = this;
//            var enabled = jQuery('#' + elemID).is(':checked') ? 1 : 0;
//            var old = (enabled == 1) ? 0 : 1; 
//            this.ajax('mainwp_wfc_updateLiveTraffic', {
//                    liveTrafficEnabled: enabled,
//                    site_id: this.siteId
//                    }, function(res){ 
//                        if(!res || !res.ok){                                
//                            jQuery('#' + elemID).attr('checked', old ? true : false);
//                            return;
//                        } else {
//                            location.href = "admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=traffic&site_id=" + self.siteId;
//                        }
//                    });
//	},
        blockIP: function(IP, reason, site_id){            
            var self = this;
            this.ajax('mainwp_wfc_blockIP', {
                    IP: IP,
                    reason: reason,
                    site_id: site_id
                    }, function(res){ 
                            if(res.errorMsg){
                                    return;
                            } else {
                                    self.reloadActivities(); 
                            }
            });
	},
        unblockIPNetwork: function(IP){
            this.processBlockUnBlockIPNetwork(IP, '', 'unblock');
	},   
        blockIPNetwork: function(IP, reason){  
            this.processBlockUnBlockIPNetwork(IP, reason, 'block');
        },
        processBlockUnBlockIPNetwork: function(IP, reason, what){  
            this.activityModeSaved = this.activityMode;            
            var currentActContent = '#wfActivity_' + this.activityMode;
            this.tickerUpdatePending = true;            
            this.activityMode = 'network_blockip';
            clearInterval(this.liveInt);
            var html = '';
            if (what == 'block')
                html += "<h3>Block IP " + IP + " across network</h3>";            
            else
                html += "<h3>Un-Block IP " + IP + " across network</h3>";            
            
            jQuery('.wfc_NetworkTrafficItemProcess').each(function(){
                var siteName = jQuery(this).attr('site-name');
                var siteId = jQuery(this).attr('site-id');
                html += '<div style="margin-bottom: 5px"><strong>' + siteName + '</strong>: ';
                html += '<span class="itemToProcess" site-id="' + siteId + '" status="queue"><span class="loading" style="display: none"><img src="' + mainwpParams['image_url'] + 'loader.gif"></span> <span class="status">Queue</span><br />';                
                html += '</div>';
            });
            html += '<div id="wfc_block_ip_ajax_message" class="mainwp_info-box-yellow hidden"></div>';
            
            jQuery(currentActContent).empty();
            jQuery(html).appendTo(currentActContent);  
            
            this.bulkTotalThreads = jQuery('.itemToProcess[status="queue"]').length;
            this.bulkCurrentThreads = 0;
            this.bulkFinishedThreads = 0;                        
            
            this.blockIPNetworkStartNext(IP, reason, what);

	},        
        blockIPNetworkStartNext: function(IP, reason, what) {   
            while ((itemProcess = jQuery('.itemToProcess[status="queue"]:first')) && (itemProcess.length > 0) && (this.bulkCurrentThreads < this.bulkMaxThreads))
            {   
                itemProcess.removeClass('queue');                   
                this.blockIPNetworkStartSpecific(itemProcess, IP, reason, what);
            }             
        },
        blockIPNetworkStartSpecific: function(pItemProcess, IP, reason, what) {                         
            this.bulkCurrentThreads++;	
            pItemProcess.attr('status', 'processed');
            var statusEl = pItemProcess.find('.status').html('Running ...');
            var loaderEl = pItemProcess.find('.loading');     
            var site_id = pItemProcess.attr('site-id');
            var self = this;
            var action = 'mainwp_wfc_blockIP';
            if (what == 'unblock')
                action = 'mainwp_wfc_unblockIP';
            loaderEl.show();            
            this.ajax(action, {
                        IP: IP,
                        reason: reason,
                        site_id: site_id,
                        network: 1
                    }, function (response){
                            loaderEl.hide();                                                                                   
                            if (response) {                                     
                                if(response.ok){                                     			
                                    statusEl.html('Successful').show();                                     
                                } else if (response['_error']) {			
                                    statusEl.html(response['_error']).show();
                                    statusEl.css('color', 'red');
                                } else if (response['_errorMsg']) {			
                                    statusEl.html(response['_errorMsg']).show();
                                    statusEl.css('color', 'red');
                                } else { 						
                                    statusEl.html(__('Undefined Error')).show();
                                    statusEl.css('color', 'red');
                                }                                                               
                            } else {
                                statusEl.html(__('Undefined Error')).show();
                                statusEl.css('color', 'red');
                            }

                            self.bulkCurrentThreads--;
                            self.bulkFinishedThreads++;
                            if (self.bulkFinishedThreads == self.bulkTotalThreads && self.bulkFinishedThreads != 0) {
                                var msg = 'Block IP finished.';
                                if (what == 'unblock')
                                    msg = 'Un-Block IP finished.'                                    
                                jQuery('#wfc_block_ip_ajax_message').html(msg).fadeIn(100);
                                setTimeout(function() {             
                                    self.restartActivities();                            
                                }, 3000);              
                            }
                            self.blockIPNetworkStartNext(IP, reason, what); 
                }, 'json')                
        },
        makeTimeAgo: function(t){
		var months = Math.floor(t / (86400 * 30));
		var days = Math.floor(t / 86400);
		var hours = Math.floor(t / 3600);
		var minutes = Math.floor(t / 60);
		if(months > 0){
			days -= months * 30;
			return this.pluralize(months, 'month', days, 'day');
		} else if(days > 0){
			hours -= days * 24;
			return this.pluralize(days, 'day', hours, 'hour');
		} else if(hours > 0) {
			minutes -= hours * 60;
			return this.pluralize(hours, 'hour', minutes, 'min');
		} else if(minutes > 0) {
			//t -= minutes * 60;
			return this.pluralize(minutes, 'minute');
		} else {
			return Math.round(t) + " seconds";
		}
	},
        pluralize: function(m1, t1, m2, t2){
		if(m1 != 1) {
			t1 = t1 + 's';
		}
		if(m2 != 1) {
			t2 = t2 + 's';
		}
		if(m1 && m2){
			return m1 + ' ' + t1 + ' ' + m2 + ' ' + t2;
		} else {
			return m1 + ' ' + t1;
		}
	},
	sev2num: function(str){
		if(/wfProbSev1/.test(str)){
			return 1;
		} else if(/wfProbSev2/.test(str)){
			return 2;
		} else {
			return 0;
		}
	},
	displayIssues: function(res, callback){
		var self = this;
		try {
			res.summary['lastScanCompleted'] = res['lastScanCompleted'];
		} catch(err){ 
			res.summary['lastScanCompleted'] = 'Never';
		}                                
		jQuery('.wfIssuesContainer').hide();
		for(issueStatus in res.issuesLists){ 
			var containerID = 'wfIssues_dataTable_' + issueStatus;
			var tableID = 'wfIssuesTable_' + issueStatus;
			if(jQuery('#' + containerID).length < 1){
				//Invalid issue status
				continue;
			}
			if(res.issuesLists[issueStatus].length < 1){
				if(issueStatus == 'new'){
					if(res.lastScanCompleted == 'ok'){
						jQuery('#' + containerID).html('<p style="font-size: 20px; color: #0A0;">Congratulations! You have no security issues on your site.</p>');
					} else if(res['lastScanCompleted']){
						//jQuery('#' + containerID).html('<p style="font-size: 12px; color: #A00;">The latest scan failed: ' + res.lastScanCompleted + '</p>');
					} else {
						jQuery('#' + containerID).html();
					}
						
				} else {
					jQuery('#' + containerID).html('<p>There are currently <strong>no issues</strong> being ignored on this site.</p>');
				}
				continue;
			}
			jQuery('#' + containerID).html('<table cellpadding="0" cellspacing="0" border="0" class="display" id="' + tableID + '"></table>');

			jQuery.fn.dataTableExt.oSort['severity-asc'] = function(y,x){ x = MWP_WFAD.sev2num(x); y = MWP_WFAD.sev2num(y); if(x < y){ return 1; } if(x > y){ return -1; } return 0; };
			jQuery.fn.dataTableExt.oSort['severity-desc'] = function(y,x){ x = MWP_WFAD.sev2num(x); y = MWP_WFAD.sev2num(y); if(x > y){ return 1; } if(x < y){ return -1; } return 0; };

			jQuery('#' + tableID).dataTable({
				"bFilter": false,
				"bInfo": false,
				"bPaginate": false,
				"bLengthChange": false,
				"bAutoWidth": false,
				"aaData": res.issuesLists[issueStatus],
				"aoColumns": [
					{
						"sTitle": '<div class="th_wrapp">Severity</div>',
						"sWidth": '128px',
						"sClass": "center",
						"sType": 'severity',
						"fnRender": function(obj) {
							var cls = "";
							cls = 'wfProbSev' + obj.aData.severity;
							return '<span class="' + cls + '"></span>';
						}
					},
					{ 
						"sTitle": '<div class="th_wrapp">Issue</div>', 
						"bSortable": false,
						"sWidth": '400px',
						"sType": 'html',
						fnRender: function(obj){ 
							var tmplName = 'issueTmpl_' + obj.aData.type;                                                        
							return jQuery('#' + tmplName).tmpl(obj.aData).html();
						} 
					}
				]
			});
		}
		if(callback){
			jQuery('#wfIssues_' + this.visibleIssuesPanel).fadeIn(500, function(){ callback(); });
		} else {
			jQuery('#wfIssues_' + this.visibleIssuesPanel).fadeIn(500);
		}
		return true;
	},
        saveConfig: function(){
		var qstr = jQuery('.mwp_wfc_settings_form_content input, .mwp_wfc_settings_form_content select').serialize();
		var self = this;                
		jQuery('.wfSavedMsg').hide();
		jQuery('.wfcSaveOpts').show();
                
		this.ajax('mainwp_wfc_saveConfig', qstr, function(res){
			jQuery('.wfcSaveOpts').hide();
			if(res.ok){
                            jQuery('.wfSavedMsg').show();
                            setTimeout(function(){ 
                                jQuery('.wfSavedMsg').fadeOut();
                                location.href = "admin.php?page=Extensions-Mainwp-Wordfence-Extension&save=setting"
                            }, 2000);
			} else if(res.errorMsg){
                            return;
			} else {
                            self.colorbox('400px', 'An error occurred', 'We encountered an error trying to save your changes.');
			}
                });
	},
	changeSecurityLevel: function(){
		var level = jQuery('#securityLevel').val();
		for(var k in mainwp_WFSLevels[level].checkboxes){
			if(k != 'liveTraf_ignorePublishers'){
				jQuery('#' + k).prop("checked", mainwp_WFSLevels[level].checkboxes[k]);
			}
		}
		for(var k in mainwp_WFSLevels[level].otherParams){
			if(! /^(?:apiKey|securityLevel|alertEmails|liveTraf_ignoreUsers|liveTraf_ignoreIPs|liveTraf_ignoreUA|liveTraf_hitsMaxSize|maxMem|maxExecutionTime|actUpdateInterval)$/.test(k)){
                                if(k == 'apiKey') 
                                    jQuery('.' + k).val(mainwp_WFSLevels[level].otherParams[k]);
                                else
                                    jQuery('#' + k).val(mainwp_WFSLevels[level].otherParams[k]);
			}
		}
	},
        ajax: function(action, data, cb, cbErr, noLoading){
		if(typeof(data) == 'string'){
			if(data.length > 0){
				data += '&';
			}
			data += 'action=' + action + '&nonce=' + this.nonce;
		} else if(typeof(data) == 'object'){
			data['action'] = action;
			data['nonce'] = this.nonce;
		}
		if(! cbErr){
			cbErr = function(){};
		}
		var self = this;
		if(! noLoading){
			this.showLoading();
		}
		jQuery.ajax({
			type: 'POST',
			url: mainwp_WordfenceAdminVars.ajaxURL,
			dataType: "json",
			data: data,
			success: function(json){ 
				if(! noLoading){
					self.removeLoading();
				}
				if(json && json.nonce){
					self.nonce = json.nonce;
				}
				if(json && json.error){
                                    var msg = json.error;
                                    if (json.error == 'NOMAINWP') {
                                        msg = 'No MainWP Child plugin detected, first install and activate the plugin and add your site to MainWP afterwards.';
                                    }
                                    self.colorbox('400px', 'An error occurred', msg);
                                    
                                    if (json.error == 'NOMAINWP') 
                                        return;
				}
                                if(json && json.errorMsg){                                        
					self.colorbox('400px', 'An error occurred', json.errorMsg);
				}
				cb(json); 
			},
			error: function(){ 
				if(! noLoading){
					self.removeLoading();  
				}
				cbErr();
			}
			});
	},
	colorbox: function(width, heading, body){ 
		this.colorboxQueue.push([width, heading, body]);
		this.colorboxServiceQueue();
	},
	colorboxServiceQueue: function(){
		if(this.colorboxIsOpen){ return; }
		if(this.colorboxQueue.length < 1){ return; }
		var elem = this.colorboxQueue.shift();
		this.colorboxOpen(elem[0], elem[1], elem[2]);
	},
	colorboxOpen: function(width, heading, body){
		this.colorboxIsOpen = true;
		jQuery.colorbox({ width: width, html: "<h3>" + heading + "</h3><p>" + body + "</p>"});
	},
	scanRunningMsg: function(){ this.colorbox('400px', "A scan is running", "A scan is currently in progress. Please wait until it finishes before starting another scan."); },
	errorMsg: function(msg){ 
            this.colorbox('400px', "An error occurred:", msg); 
        },
        bulkOperation: function(op){
		var self = this;
		if(op == 'del' || op == 'repair'){
			var ids = jQuery('input.wf' + op + 'Checkbox:checked').map(function(){ return jQuery(this).val(); }).get();
			if(ids.length < 1){
				this.colorbox('400px', "No files were selected", "You need to select files to perform a bulk operation. There is a checkbox in each issue that lets you select that file. You can then select a bulk operation and hit the button to perform that bulk operation.");
				return;
			}
			if(op == 'del'){
				this.colorbox('400px', "Are you sure you want to delete?", "Are you sure you want to delete a total of " + ids.length + " files? Do not delete files on your system unless you're ABSOLUTELY sure you know what you're doing. If you delete the wrong file it could cause your WordPress website to stop functioning and you will probably have to restore from backups. If you're unsure, Cancel and work with your hosting provider to clean your system of infected files.<br /><br /><input type=\"button\" value=\"Delete Files\" onclick=\"MWP_WFAD.bulkOperationConfirmed('" + op + "');\" />&nbsp;&nbsp;<input type=\"button\" value=\"Cancel\" onclick=\"jQuery.colorbox.close();\" /><br />");
			} else if(op == 'repair'){
				this.colorbox('400px', "Are you sure you want to repair?", "Are you sure you want to repair a total of " + ids.length + " files? Do not repair files on your system unless you're sure you have reviewed the differences between the original file and your version of the file in the files you are repairing. If you repair a file that has been customized for your system by a developer or your hosting provider it may leave your system unusable. If you're unsure, Cancel and work with your hosting provider to clean your system of infected files.<br /><br /><input type=\"button\" value=\"Repair Files\" onclick=\"MWP_WFAD.bulkOperationConfirmed('" + op + "');\" />&nbsp;&nbsp;<input type=\"button\" value=\"Cancel\" onclick=\"jQuery.colorbox.close();\" /><br />");
			}
		} else {
			return;
		}
	},
	bulkOperationConfirmed: function(op){
                if (this.siteId <= 0) //ok
                    return; 
            
		jQuery.colorbox.close();
		var self = this;
		this.ajax('mainwp_wfc_bulkOperation', {
			op: op,
                        site_id: this.siteId,
			ids: jQuery('input.wf' + op + 'Checkbox:checked').map(function(){ return jQuery(this).val(); }).get()
			}, function(res){ self.doneBulkOperation(res); });
	},
	doneBulkOperation: function(res){
		var self = this;
		if(res.ok){
			this.loadIssues(function(){ self.colorbox('400px', res.bulkHeading, res.bulkBody); });
		} else {
			this.loadIssues(function(){});
		}
	},
	deleteFile: function(issueID){
                if (this.siteId <= 0) //ok
                    return; 
		var self = this;
		this.ajax('mainwp_wfc_deleteFile', {
			issueID: issueID,
                        site_id: this.siteId
			}, function(res){ self.doneDeleteFile(res); });
	},
	doneDeleteFile: function(res){
		var cb = false;
		var self = this;
		if(res.ok){
			this.loadIssues(function(){ self.colorbox('400px', "Success deleting file", "The file " + res.file + " was successfully deleted."); });
		} else if(res.cerrorMsg){
			this.loadIssues(function(){ self.colorbox('400px', 'An error occurred', res.cerrorMsg); });
		}
	},
	restoreFile: function(issueID){
                if (this.siteId <= 0) //ok
                    return; 
		var self = this;
		this.ajax('mainwp_wfc_restoreFile', { 
			issueID: issueID,
                        site_id: this.siteId
			}, function(res){ self.doneRestoreFile(res); });
	},
	doneRestoreFile: function(res){
		var self = this;
		if(res.ok){
			this.loadIssues(function(){ self.colorbox("400px", "File restored OK", "The file " + res.file + " was restored succesfully."); });
		} else	if(res.cerrorMsg){
			this.loadIssues(function(){ self.colorbox('400px', 'An error occurred', res.cerrorMsg); });
		}
	},
	deleteIssue: function(id){
                if (this.siteId <= 0) //ok
                    return;
		var self = this;
		this.ajax('mainwp_wfc_deleteIssue', { 
                            id: id,  
                            site_id: this.siteId
                        }, function(res){ 
                            self.loadIssues();
			});
	},
	updateIssueStatus: function(id, st){
                if (this.siteId <= 0) //ok
                    return;
            
		var self = this;
		this.ajax('mainwp_wfc_updateIssueStatus', { id: id, 'status': st, site_id: this.siteId }, function(res){ 
			if(res.ok){
				self.loadIssues();
			}
			});
	},
	updateAllIssues: function(op){ // deleteIgnored, deleteNew, ignoreAllNew                
                if (this.siteId <= 0) //ok
                    return;     
		var head = "Please confirm";
		if(op == 'deleteIgnored'){
			body = "You have chosen to remove all ignored issues. Once these issues are removed they will be re-scanned by Wordfence and if they have not been fixed, they will appear in the 'new issues' list. Are you sure you want to do this?";
		} else if(op == 'deleteNew'){
			body = "You have chosen to mark all new issues as fixed. If you have not really fixed these issues, they will reappear in the new issues list on the next scan. If you have not fixed them and want them excluded from scans you should choose to 'ignore' them instead. Are you sure you want to mark all new issues as fixed?";
		} else if(op == 'ignoreAllNew'){
			body = "You have chosen to ignore all new issues. That means they will be excluded from future scans. You should only do this if you're sure all new issues are not a problem. Are you sure you want to ignore all new issues?";
		} else {
			return;
		}
		this.colorbox('450px', head, body + '<br /><br /><center><input type="button" name="but1" value="Cancel" onclick="jQuery.colorbox.close();" />&nbsp;&nbsp;&nbsp;<input type="button" name="but2" value="Yes I\'m sure" onclick="jQuery.colorbox.close(); MWP_WFAD.confirmUpdateAllIssues(\'' + op + '\');" /><br />');
	},
	confirmUpdateAllIssues: function(op){
		var self = this;
		this.ajax('mainwp_wfc_updateAllIssues', { op: op, site_id : this.siteId }, function(res){ self.loadIssues(); });
	},
        ucfirst: function(str){
		str = "" + str;
		return str.charAt(0).toUpperCase() + str.slice(1);
	},
        makeDiffLink: function(dat, site_id){            
            var loc = '?_wfsf=diff&nonce=child_temp_nonce' +
                    '&file=' + encodeURIComponent(this.es(dat['file'])) +
                    '&cType=' + encodeURIComponent(this.es(dat['cType'])) +
                    '&cKey=' + encodeURIComponent(this.es(dat['cKey'])) +
                    '&cName=' + encodeURIComponent(this.es(dat['cName'])) +
                    '&cVersion=' + encodeURIComponent(this.es(dat['cVersion']));
            return '&websiteid=' + site_id + '&open_location=' + this.utf8_to_b64(loc);            
	},
        makeViewFileLink: function(file, site_id){                
            var loc = '?_wfsf=view&nonce=child_temp_nonce&file=' + encodeURIComponent(file);
            return '&websiteid=' + site_id + '&open_location=' + this.utf8_to_b64(loc);
	},
	es: function(val){
		if(val){
			return val;
		} else {
			return "";
		}
	},
	noQuotes: function(str){
		return str.replace(/"/g,'&#34;').replace(/\'/g, '&#145;');
	},
	commify: function(num){
		return ("" + num).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
	},         
	switchIssuesTab: function(elem, type){
		jQuery('.wfTab2').removeClass('selected');
		jQuery('.wfIssuesContainer').hide();
		jQuery(elem).addClass('selected');
		this.visibleIssuesPanel = type;
		jQuery('#wfIssues_' + type).fadeIn();
	},		
};
window['MWP_WFAD'] = window['mainwp_wordfenceAdmin'];
}
jQuery(function(){
    mainwp_wordfenceAdmin.init();
});
