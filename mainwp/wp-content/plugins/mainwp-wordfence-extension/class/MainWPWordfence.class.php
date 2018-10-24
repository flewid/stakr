<?php
class MainWPWordfence
{    
    
    public function __construct() { 
       
    }
    
    public static function init() {
              
               
    }
  
    public function admin_init() {
        foreach(array('activityLogUpdate', 'loadFirstActivityLog', 'loadIssues', 'deleteIssue', 'bulkOperation', 'deleteFile', 'restoreFile', 'updateIssueStatus', 'updateAllIssues' , 'saveConfig', 'ticker', 'reverseLookup', 'updateLiveTraffic', 'blockIP', 'unblockIP', 'loadStaticPanel', 'downgradeLicense') as $func){
            add_action('wp_ajax_mainwp_wfc_' . $func, 'MainWPWordfence::ajaxReceiver');
        }         
        wp_localize_script('mainwp-wordfence-extension-admin-log', 'mainwp_WordfenceAdminVars', array(
            'ajaxURL' => admin_url('admin-ajax.php'),
            'firstNonce' => wp_create_nonce('wp-ajax'),
            'siteBaseURL' => MainWPWordfenceUtility::getSiteBaseURL(),
            'debugOn' => 0,
            'actUpdateInterval' => 2	            
        ));
        
        add_filter('mainwp-sitestable-getcolumns', array($this, 'sitestable_getcolumns'), 10);
        add_filter('mainwp-sitestable-item', array($this, 'sitestable_item'), 10);
        add_action('mainwp-site-synced', array(&$this, 'site_synced'), 10, 1);
        add_action('mainwp-wordfence-sites', array($this, "renderTabs"));
    }     
    
    public static function openSite()
    {            
        $id = $_GET['websiteid'];
        global $mainWPWordfenceExtensionActivator;
        $websites = apply_filters('mainwp-getdbsites', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), array($id));            
        $website = null;
        if ($websites && is_array($websites)) {
            $website = current($websites);
        }
        
        $open_location = "";
        if (isset($_GET['open_location'])) $open_location = $_GET['open_location'];                
        ?>
        <div id="mainwp_background-box">   
            <?php 
                if (function_exists("mainwp_current_user_can") && !mainwp_current_user_can("dashboard", "access_wpadmin_on_child_sites")) { 
                    mainwp_do_not_have_permissions("WP-Admin on child sites");
                } else {
            ?>
            <?php  _e('Will redirect to your website immediately.','mainwp'); ?>
                    <form method="POST" action="<?php echo MainWPWordfenceUtility::getGetDataAuthed($website, 'index.php' , 'where', $open_location); ?>" id="mfc_redirectForm">
                    </form>
            <?php } ?>
        </div>
        <?php       
    }
    
    public function sitestable_getcolumns( $columns) {
        $columns['wfc_status'] = __('Wordfence', 'mainwp');        
        return $columns;
    }
    
    public function sitestable_item( $item ) {
        $site_id = $item['id'];
        
        $settings = MainWPWordfenceDB::Instance()->getSettingBy('site_id', $site_id);
            
        $status = 0;
        
        if ($settings) {                                     
            $status = $settings->status;  
        }
        
        if (empty($status)) {
             $scr = 'images/wf-no.png';
             $item['wfc_status'] = '<span style="text-align: center;display: block"><img src="' . MainWPWordfenceExtension::$plugin_url  . $scr . '"></span>';
        } else {
            if ($status == 1) {
                $scr = 'images/wf-ok.png';
            } else {
                $scr = 'images/wf-issues.png';
            }  
            $item['wfc_status'] = '<span style="text-align: center;display: block"><a href="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=result&site_id=' . $site_id . '"><img src="' . MainWPWordfenceExtension::$plugin_url  . $scr . '"></a></span>';
        }
        
        return $item;
    }
    
    public function site_synced($website) {       
         if ($website && $website->plugins != '')  { 
            $plugins = json_decode($website->plugins, 1);   
            $status = 0;
            if (is_array($plugins) && count($plugins) != 0) {                            
                foreach ($plugins as $plugin)
                {                            
                    if ($plugin['slug'] == "wordfence/wordfence.php") {                                                            
                        if ($plugin['active'])
                            $status = 1;                            
                        break;            
                    }
                }
            }
            
            $update = array('site_id' => $website->id);
            
            if ($status == 1) {
                global $mainWPWordfenceExtensionActivator;
                $post_data = array( 'mwp_action' => 'load_issues');
                $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $website->id, 'wordfence', $post_data);			                                
                $count_issues = 0;
                if(is_array($information)) {
                    if (isset($information['lastScanCompleted']) && $information['lastScanCompleted'] == "ok"){                                            
                        if (isset($information['summary'])) {
                            if (isset($information['summary']['totalCritical']))
                                $count_issues += $information['summary']['totalCritical'];
                            if (isset($information['summary']['totalWarning']))
                                $count_issues += $information['summary']['totalWarning'];
                            if ($count_issues > 0)
                                $status = 2;    
                        }                   
                    }  
                    if (isset($information['apiKey'])) {
                        $update['apiKey'] = $information['apiKey'];
                        $update['isPaid'] = isset($information['isPaid']) ? $information['isPaid'] : 0;
                    }
                }
            }            
            $update['status'] =  $status;            
            //error_log(print_r($update, true));
            MainWPWordfenceDB::Instance()->updateSetting($update);
         }
    }
    
    public static function renderMetabox() {
        $website_id = isset($_GET['dashboard']) ? $_GET['dashboard'] : 0;
        if (empty($website_id))
                return;
        $settings = MainWPWordfenceDB::Instance()->getSettingBy('site_id', $website_id);
        $status = $lastscan = 0;        
        if ($settings) {
            $status = $settings->status;
            $lastscan = $settings->lastscan;
        }
        
        if ($status == 0) {
            echo __("The Wordfence plugin not found on the child site.", "mainwp");
            return;
        } else {
            if ($status == 1) {
                $scr = 'images/wf-ok-large.png';
                $status_txt = __("Status: No issues Detected", "mainwp");
            } else {
                $scr = 'images/wf-issues-large.png';
                $status_txt = __("Status: Issues Detected", "mainwp");
            }  
            
            $icon_status = '<img src="' . MainWPWordfenceExtension::$plugin_url  . $scr . '">';
            $scan_lnk = '<a href="#" class="wfc_metabox_scan_now_lnk" site-id="' . $website_id . '">' . __("Scan Now", "mainwp"). '</a> | ';
            $result_lnk = '<a href="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=result&site_id=' . $website_id . '">' . __("Show Results", "mainwp"). '</a> | ';
            $traffic_lnk = '<a href="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=traffic&site_id=' . $website_id . '">' . __("Live Traffic", "mainwp"). '</a> | ';
            $dashboard_lnk = '<a href="admin.php?page=Extensions-Mainwp-Wordfence-Extension">' . __("Wordfence Dashboard", "mainwp"). '</a>';
            
            $url_loader = plugins_url('images/loader.gif', dirname(__FILE__));
            ?>
                <div class="wfc_metabox_icon"><?php echo $icon_status; ?></div>
                <div class="wfc_metabox_text">
                    <div class="wfc_metabox_row"><?php echo $status_txt; ?></div>
                    <div class="wfc_metabox_row"><?php echo __("Last Scan", "mainwp") . ": " . ( $lastscan ? MainWPWordfenceUtility::formatTimestamp($lastscan) : "");  ; ?></div>
                    <div class="wfc_metabox_row"><?php echo __("Shortcuts", "mainwp") . ": " . $scan_lnk . $result_lnk . $traffic_lnk . $dashboard_lnk ; ?></div>                                    
                    <div class="wfc_metabox_row" id="wfc_metabox_working_row"><span class="loading hidden-field"><img src="<?php echo $url_loader; ?>"> <?php _e("Running ...", "mainwp"); ?></span><span class="status hidden-field"></span></div>                                    
                </div>
                <div class="clear">&nbsp;</div>
            <?php
        }
    }
    
    public static function render() {     
        self::WordfenceQSG();
        self::renderTabs();        
    }
   
    public static function renderTabs($website = null) {        
        global $current_user; 
        
        if (isset($_GET['action']) && $_GET['action'] == "open_site") {
            self::openSite();
            return;
        } 
        
        if (!empty($website)) {
            $settings = MainWPWordfenceDB::Instance()->getSettingBy('site_id', $website->id);
            if (empty($settings->status))
                return;                
        }        
        
        $style_tab_dashboard = $style_tab_scan = $style_tab_settings = $style_tab_traffic = ' style="display: none" ';                
        $scan_site_id = $traffic_site_id = 0;       
        $do_save_settings = $show_traffic_network = false;
        $lnk_tab_live_traffic_network = "admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=network_traffic";
        $display_scan_in_widget = false;
        
        if (!empty($website)) {            
            $display_scan_in_widget = true;
            $style_tab_scan = "";
            $scan_site_id = $website->id;            
        }
        
        if (!$display_scan_in_widget) {
            if (isset($_GET['action'])) {
                if ($_GET['action'] == "result" && isset($_GET['site_id']) && !empty($_GET['site_id'])) {
                    $style_tab_scan = "";
                    $scan_site_id = $_GET['site_id'];
                } else if ($_GET['action'] == "traffic" && isset($_GET['site_id']) && !empty($_GET['site_id'])) {
                    $style_tab_traffic = "";
                    $traffic_site_id = $_GET['site_id'];                
                } else if ($_GET['action'] == "setting") {
                    $style_tab_settings = "";
                } else if ($_GET['action'] == "network_traffic") {
                    $show_traffic_network = true;
                    $lnk_tab_live_traffic_network = "";                
                } 
            } else if (isset($_GET['save']) && $_GET['save'] == 'setting') {
                $do_save_settings = true;
                $style_tab_settings = "";            
            } else {
                $style_tab_dashboard = "";
            }  
        }
        
        global $mainWPWordfenceExtensionActivator;
          
        $websites = apply_filters('mainwp-getsites', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), null);              
        $sites_ids = array();
        if (is_array($websites)) {
            foreach ($websites as $website) {
                $sites_ids[] = $website['id'];
                
            }                
        }
          
        $option = array('plugin_upgrades' => true, 
                        'plugins' => true);
        
        $dbwebsites = apply_filters('mainwp-getdbsites', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $sites_ids, array(), $option);              
        
        $selected_group = 0;       
        
        if(isset($_POST['mainwp_wfc_plugin_groups_select'])) {
            $selected_group = intval($_POST['mainwp_wfc_plugin_groups_select']);            
        }           
        $dbwebsites_wordfence = MainWPWordfencePlugin::Instance()->get_websites_with_the_plugin($dbwebsites, $selected_group);         
        //print_r($dbwebsites_wordfence);
        $title_display = "";
        if ( $scan_site_id || $traffic_site_id) {
            $_site_id = $scan_site_id ? $scan_site_id : ($traffic_site_id ? $traffic_site_id : 0);
            if ($_site_id) {
                $_website = apply_filters('mainwp-getsites', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $_site_id, null);              
                if (is_array($_website)) {
                    $_website = current($_website);                    
                }
                if ($scan_site_id)
                    $title_display = $_website['name'] . " " . __("WordFence Scan Results", "mainwp");
                else
                    $title_display = $_website['name'] . " " .  __("WordFence Live Traffic", "mainwp") ;
            }
        } else if ($show_traffic_network) {
            $title_display = __("Network Live Traffic", "mainwp");
        }
        unset($dbwebsites);        
        ?>
            <div class="<?php echo !$display_scan_in_widget ? "wrap" : ""; ?>" id="mainwp-ap-option">
            <div class="clearfix"></div>           
            <div class="inside">                 
                <div id="mainwp_wfc_settings">
                    <div class="mainwp_error error" id="mwpwfcerror_box"></div>
                    <div class="clear">
                        <?php if (!$display_scan_in_widget) { ?>
                            <br />
                            <a id="mwp_wfc_dashboard_tab_lnk" href="#" class="mainwp_action left <?php  echo (empty($style_tab_dashboard) ? "mainwp_action_down" : ""); ?>"><?php _e("Wordfence Dashboard"); ?></a><a <?php echo $show_traffic_network ? 'id="mwp_wfc_network_traffic_tab_lnk"' : "" ?>  href="<?php echo $show_traffic_network ? "#" : $lnk_tab_live_traffic_network; ?>" class="mainwp_action mid <?php  echo ($show_traffic_network ? "mainwp_action_down" : ""); ?>"><?php _e("Network Live Traffic"); ?></a><a id="mwp_wfc_scan_tab_lnk" href="#" <?php echo $style_tab_scan; ?> class="mainwp_action mid <?php  echo (empty($style_tab_scan) ? "mainwp_action_down" : ""); ?>"><?php _e("Scan Dashboard"); ?></a><a id="mwp_wfc_traffic_tab_lnk" href="#" <?php echo $style_tab_traffic; ?> class="mainwp_action mid <?php  echo (empty($style_tab_traffic) ? "mainwp_action_down" : ""); ?>"><?php _e("Live Traffic"); ?></a><a id="mwp_wfc_settings_tab_lnk" href="#" class="mainwp_action right <?php  echo (empty($style_tab_settings) ? "mainwp_action_down" : ""); ?>"><?php _e("WordFence Settings"); ?></a>
                            <br> 
                            <?php
                            if (!empty($title_display))
                                echo "<h3>" . $title_display . "</h3>";
                            else {
                                echo "<br>";
                            }
                        }
                        
                        if (!$display_scan_in_widget) {
                        ?>
                        <div id="mwp_wfc_dashboard_tab" <?php echo $style_tab_dashboard; ?>>
                            <div class="mwp_wfc_top_box"> 
                                <div class="wfc-inside">
                                    <div class="wfc-content">      
                                        <div class="mainwp_wfc_logo"><a href="#" title="Wordfence"><img src="<?php echo plugins_url('images/wfc-logo.png', dirname(__FILE__)); ?>" alt="logo"/></a></div>
                                        <p> 
                                            <span id="mainwp_wfc_remind_change_status"></span>
                                            <a href="#" id="mainwp-wfc-run-scan" class="button-hero button button-primary wfc-run-scan" title="<?php _e("Start a Wordfence Scan"); ?>"><?php _e("Start a Wordfence Scan"); ?></a>                        
                                        </p> 
                                        <div class="clear"></div>                    
                                    </div>
                                </div>
                            </div>
                            <br>
                            <?php if (!$display_scan_in_widget) { ?>                                    
                                    <div class="tablenav top">
                                    <?php MainWPWordfencePlugin::gen_select_sites($dbwebsites_wordfence, $selected_group); ?>  
                                        <input type="button" class="mainwp-upgrade-button button-primary button" 
                                            value="<?php _e("Sync Data"); ?>" id="dashboard_refresh" style="background-image: none!important; float:right; padding-left: .6em !important;">
                                    </div>                            
                                    <?php MainWPWordfencePlugin::gen_plugin_dashboard_tab($dbwebsites_wordfence); ?>                            
                            <?php } ?>
                        </div>
                        <?php
                        } else {
                            if (false) {
                            ?>
                            <div class="mwp_wfc_top_box"> 
                                <div class="wfc-inside">
                                    <div class="wfc-content">      
                                        <div class="mainwp_wfc_logo"><a href="#" title="Wordfence"><img src="<?php echo plugins_url('images/wfc-logo.png', dirname(__FILE__)); ?>" alt="logo"/></a></div>
                                        <p> 
                                            <span id="mainwp_wfc_remind_change_status"></span>
                                            <a href="#" id="mainwp-wfc-widget-run-scan" class="button-hero button button-primary wfc-run-scan" title="<?php _e("Start a Wordfence Scan"); ?>"><?php _e("Start a Wordfence Scan"); ?></a>                        
                                        </p> 
                                        <div class="clear"></div>                    
                                    </div>
                                </div>
                            </div>                            
                            <?php       
                            }
                        }
                            $wfc_active_sites = array();
                            foreach($dbwebsites_wordfence as $wfc_website) {
                                if (isset($wfc_website['wordfence_active']) && !empty($wfc_website['wordfence_active'])) {
                                    $wfc_active_sites[$wfc_website['id']] = $wfc_website['name']; 
                                }
                            }    
                            foreach($wfc_active_sites as $wp_id => $site_name) {
                                $w = new MainWPWordfenceConfigSite($wp_id); // new: to load data
                                $cacheType = $w->get_cacheType();                            
                                ?>                                        
                                <span class="wfc_NetworkTrafficItemProcess" site-id="<?php echo $wp_id; ?>" site-name="<?php echo htmlspecialchars($site_name); ?>" status="queue" cacheType="<?php echo $cacheType; ?>" newestActivityTime="0"></span>                                        
                                <?php
                            }       
                        ?>
                        <div id="mwp_wfc_network_traffic_tab" <?php echo $show_traffic_network ? "" : 'style="display: none"'; ?>>                                                            
                            <?php  
                                if ($show_traffic_network) {        
                                    MainWPWordfenceLiveTraffic::gen_network_traffic_tab($wfc_active_sites);                                                 
                                }
                            ?>   
                        </div> 
                        <div id="mwp_wfc_scan_tab" <?php echo $style_tab_scan; ?>>
                            <?php 
                                if ($scan_site_id) { 
                                    MainWPWordfenceLog::gen_result_tab($scan_site_id);                             
                                } 
                            ?>                            
                        </div>
                        <form method="post" id="wfConfigForm" action="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=setting">
                            <div id="mwp_wfc_settings_tab" <?php echo $style_tab_settings; ?>>                                                            
                                <?php
                                    if ($do_save_settings) {
                                        MainWPWordfenceSetting::gen_save_settings();             
                                    } else {
                                        MainWPWordfenceSetting::gen_settings_tab($wfc_active_sites); 
                                    }
                                ?>   
                            </div>
                        </form>                        
                        <div id="mwp_wfc_traffic_tab" <?php echo $style_tab_traffic; ?>>                                                            
                            <?php     
                                if ($traffic_site_id) {                                    
                                    MainWPWordfenceLiveTraffic::gen_traffic_tab($traffic_site_id);                                                 
                                }
                            ?>   
                        </div>                        
                    </div>
                <div class="clear"></div>
                </div>
            </div>
        </div>              
    <?php
    }
       
    public static function WordfenceQSG() {
        $plugin_data =  get_plugin_data( MAINWP_WORDFENCE_EXT_PLUGIN_FILE, false );         
        $description = $plugin_data['Description'];
        $extraHeaders = array('DocumentationURI' => 'Documentation URI');
        $file_data = get_file_data(MAINWP_WORDFENCE_EXT_PLUGIN_FILE, $extraHeaders);
        $documentation_url  = $file_data['DocumentationURI'];
        ?>
        <div  class="mainwp_ext_info_box" id="cs-pth-notice-box">
            <div class="mainwp-ext-description"><?php echo $description; ?></div><br/>
            <b><?php echo __("Need Help?"); ?></b> <?php echo __("Review the Extension"); ?> <a href="<?php echo $documentation_url; ?>" target="_blank"><i class="fa fa-book"></i> <?php echo __('Documentation'); ?></a>. 
                    <a href="#" id="mainwp-wordfence-quick-start-guide"><i class="fa fa-info-circle"></i> <?php _e('Show Quick Start Guide','mainwp'); ?></a></div>
                    <div  class="mainwp_ext_info_box" id="mainwp-wfc-tips" style="color: #333!important; text-shadow: none!important;">
                      <span><a href="#" class="mainwp-show-tut" number="1"><i class="fa fa-book"></i> <?php _e('Wordfence Dashboard','mainwp') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-tut"  number="2"><i class="fa fa-book"></i> <?php _e('WordFence Settings','mainwp') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-tut"  number="3"><i class="fa fa-book"></i> <?php _e('How to Scan Child Sites','mainwp') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="mainwp-show-tut"  number="4"><i class="fa fa-book"></i> <?php _e('Monitor Child Sites Live Traffic','mainwp') ?></a></span><span><a href="#" id="mainwp-wfc-tips-dismiss" style="float: right;"><i class="fa fa-times-circle"></i> <?php _e('Dismiss','mainwp'); ?></a></span>
                      <div class="clear"></div>
                      <div id="mainwp-wfc-tuts">
                        <div class="mainwp-wfc-tut" number="1">
                            <h3>Wordfence Dashboard</h3> 
                            <p>From the Wordfence Dashboard page, you can monitor all of your child sites where you have the Wordfence plugin installed. In the sites list, you will be notified if the plugin has an update available or if the plugin is deactivated.</p>
                            <p>The provided links and bulk actions will allow you to Update and Activate the Plugin.</p>
                            <p>In the Trigger Column, you will find useful link actions. From here you can trigger the scanning process, show scanning results or to see the Live Traffic page for the child site.</p>
                            <p>You can also hide the Plugin on child sites. Simply by clicking the Hide Wordfence Plugin you can hide it on a single site (Show Wordfence Plugin for unhiding it)</p>
                            <p>or use bulk actions to hide on multiple sites. Select the sites where you want to hide the plugin, choose the Hide action and click the Apply button.</p>
                            <p>To unhide the plugin on multiple sites, select the wanted sites, choose the Show action and click the Apply button.</p>                
                        </div>
                        <div class="mainwp-wfc-tut"  number="2">
                            <h3>WordFence Settings</h3>       
                            <p><strong>Global Settings</strong></p>     
                            <p>The Wordfence Settings tab enables you to control the Wordfence options across all your child sites. It allows you to set:</p>    
                            <ol>
                                <li>Scan Schedule</li>
                                <li>Basic Settings</li>
                                <li>Alerts</li>
                                <li>Live Traffic View</li>
                                <li>Scans to Include</li>
                                <li>Firewall Rules</li>
                                <li>Login Security Options</li>
                                <li>Other Options</li>
                            </ol>            
                            <p><strong>Saving the Wordfence options will overwrite options on your child sites.</strong></p>
                            <p><strong>Individual Site Settings</strong></p>     
                            <p>The extension enables you to set Wordfence options individually as per site basis. To do this,</p>    
                            <ol>
                                <li>Locate the wanted child site in the Manage sites list</li>
                                <li>Click the Edit link right under the child site name</li>
                                <li>Scroll down and locate the Wordfence option boxes</li>
                                <li>Locate the Override General Settings option in the Wordfence Settings box</li>
                                <li>Set to YES</li>
                                <li>Use the rest of Wordfence option boxes to set your preferences</li>
                                <li>Click the Save settings button at the bottom of the page.</li>
                            </ol>            
                        </div>
                        <div class="mainwp-wfc-tut"  number="3">
                            <h3>How to Scan Child Sites</h3>  
                            <p>
                                The Wordfence extension enables you to trigger scanning process on child sites directly from your dashboard. To do this, Go to the Wordfence Dashboard page (MainWP > Extensions > MainWP Wordfence Extension ).
                                In the upper part of the page, you will be able to find the Start a Wordfence Scan button
                            </p>            
                            <img src="http://docs.mainwp.com/wp-content/uploads/2014/09/start-scan-all-1024x77.png">              
                            <p>This button will start a scan process on all child sites. If you want to scan just an individual site, locate the child site in the Wordfence dashboard and click the Scan Now action link.</p>
                            <img src="http://docs.mainwp.com/wp-content/uploads/2014/09/scan-now-1-1024x52.png">
                            <p>After the scan process is done, you will be able to use Show Results link to see the results. Click it and the new Scanning Results tab will open for you.</p>
                            <img src="http://docs.mainwp.com/wp-content/uploads/2014/09/show-results-1024x52.png">
                        </div>
                        <div class="mainwp-wfc-tut"  number="4">
                            <h3>Monitor Child Sites Live Traffic</h3>
                            <p>If you want to monitor live traffic on your child sites directly from your dashboard, you can do that by clicking the Live Traffic action link in the Wordfence Dashboard page.</p>
                            <ol>
                                <li>Locate the wanted child site in the list</li>
                                <li>Click the Live Traffic link in the Trigger column</li>
                                <li>The live traffic tab will be opened</li>
                            </ol>

                            <img src="http://docs.mainwp.com/wp-content/uploads/2014/09/live-traffic-1024x52.png">

                            <p>If the Live Traffic feature is disabled, you need to enable it. You can do that in the Wordfence Settings tab.</p>
                            <ol>
                                <li>Locate the Basic Settings box.</li>
                                <li>Find the Enable Live Traffic View option and check the checkbox</li>
                                <li>Click the Save Settings button</li>
                                <li>Click the Sync Data button</li>
                            </ol>
                        </div>                        
                      </div>
                    </div>
        <?php
    }
    
      
    public static function ajaxReceiver(){
            if(! MainWPWordfenceUtility::isAdmin()){
                    die(json_encode(array('error' => "You appear to have logged out or you are not an admin. Please sign-out and sign-in again.")));
            }
            $func = (isset($_POST['action']) && $_POST['action']) ? $_POST['action'] : $_GET['action'];
            $nonce = (isset($_POST['nonce']) && $_POST['nonce']) ? $_POST['nonce'] : $_GET['nonce'];
            if(! wp_verify_nonce($nonce, 'wp-ajax')){ 
                    die(json_encode(array('error' => "Your browser sent an invalid security token to MainWP Wordfence Extension. Please try reloading this page or signing out and in again.")));
            }
            //func is e.g. wordfence_ticker so need to munge it
            $func = str_replace('mainwp_wfc_', '', $func);
            $returnArr = call_user_func('MainWPWordfence::ajax_' . $func . '_callback');
            if($returnArr === false){
                    $returnArr = array('error' => "MainWP Wordfence Extension encountered an internal error executing that request.");
            }

            if(! is_array($returnArr)){
                    error_log("Function $func did not return an array and did not generate an error.");
                    $returnArr = array();
            }
            if(isset($returnArr['nonce'])){
                    error_log("MainWP Wordfence Extension ajax function return an array with 'nonce' already set. This could be a bug.");
            }
            $returnArr['nonce'] = wp_create_nonce('wp-ajax');
            die(json_encode($returnArr));
            exit;
    }
    
    
    public static function ajax_loadFirstActivityLog_callback() {
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {            
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'get_log');
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            $events = array();
            if (isset($information['events'])) {
                $events = $information['events'];
                unset($information['events']);
            }            
            
            $output = $information;
            $newestItem = 0;
            ob_start();
            if(sizeof($events) > 0){
                    $debugOn = isset($information['debugOn']) ? $information['debugOn'] : false;                        
                    $sumEvents = array();
                    $timeOffset = isset($information['timeOffset']) ? $information['timeOffset'] : 3600 * get_option('gmt_offset');
                    foreach($events as $e){
                            if(strpos($e['msg'], 'SUM_') !== 0){
                                    if( $debugOn || $e['level'] < 4){
                                            $typeClass = '';
                                            if($debugOn){
                                                    $typeClass = ' wf' . $e['type'];
                                            }
                                            echo '<div class="wfActivityLine' . $typeClass . '">[' . date('M d H:i:s', $e['ctime'] + $timeOffset) . ']&nbsp;' . $e['msg'] . '</div>';
                                    }
                            }
                            $newestItem = $e['ctime'];
                    }                   
            } else {
                _e("A live stream of what Wordfence is busy with right now will appear in this box.", "mainwp");
                $output['not_found_events'] = true;
            }
            $output['result'] =  ob_get_clean();  
            $output['lastctime'] =  $newestItem;
            die(json_encode($output)); 
        }
        die();
    }
    
    public static function ajax_activityLogUpdate_callback() {
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'update_log', 'lastctime' => $_POST['lastctime']);
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die();        
    }
    
    public static function ajax_loadIssues_callback() {
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'load_issues');
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            
            if(is_array($information)) {
                if (isset($information['lastScanCompleted']) && $information['lastScanCompleted'] == 'ok'){                                            
                    if (isset($information['summary'])) {
                        $count_issues = 0;
                        if (isset($information['summary']['totalCritical']))
                            $count_issues += $information['summary']['totalCritical'];
                        if (isset($information['summary']['totalWarning']))
                            $count_issues += $information['summary']['totalWarning']; 
                        $status = 1;
                        if ($count_issues > 0)
                            $status = 2;
                        $update = array('site_id' => $siteid, 
                                        'status' => $status);                        
                        MainWPWordfenceDB::Instance()->updateSetting($update);
                    }                   
                }            
            }                        
            die(json_encode($information));
        }
        die(); 
    }
    
    public static function ajax_deleteIssue_callback() {
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'delete_issues',
                                'id' => $_POST['id']);
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die(); 
    }
    
    public static function ajax_bulkOperation_callback(){
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'bulk_operation',
                                'op' => $_POST['op'],
                                'ids' => $_POST['ids']
                                );
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die();
    }
    
    public static function ajax_deleteFile_callback(){
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'delete_file',
                                'issueID' => $_POST['issueID']                                
                                );
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die();
    }
    
    public static function ajax_restoreFile_callback(){
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'restore_file',
                                'issueID' => $_POST['issueID']                                
                                );
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die();        
    }
          
    public static function ajax_updateIssueStatus_callback() {
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {              
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'update_issues_status', 
                                'id' => $_POST['id'], 
                                'status' => $_POST['status']);
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die(); 
    }
    
    public static function ajax_updateAllIssues_callback() {
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'update_all_issues', 'op' => $_POST['op']);
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die(); 
    }
    
    public static function ajax_ticker_callback(){
        $siteid = $_POST['site_id'];  
        $mode = $_POST['mode'];
        if (!empty($siteid)) {  
            $cacheType = $_POST['cacheType'];
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'ticker', 
                                'alsoGet' => $_POST['alsoGet'],
                                'otherParams' => $_POST['otherParams']                                
                            );
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			            
            if (is_array($information) && isset($information['cacheType'])) {
                $site_cacheType = $information['cacheType'];
                if ($mode == 'activity') {
                    if ($cacheType != $site_cacheType) {
                        $information['reload'] = 'reload';                    
                        MainWPWordfenceDB::Instance()->updateSetting(array('site_id' => $siteid, 
                                                    'cacheType' => $site_cacheType)
                                                );
                    }
                }
                $information['site_id'] = $siteid;
                if (isset($_POST['forceUpdate']))
                    $information['forceUpdate'] = true;
                else {
                    $information['forceUpdate'] = false;
                }
            } 
            die(json_encode($information));
        }
        die(); 
    }
        
    public static function ajax_reverseLookup_callback(){         
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'reverse_lookup', 'ips' => $_POST['ips']);
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            die(json_encode($information));
        }
        die();
    }
      
    public static function ajax_updateLiveTraffic_callback(){         
//        $siteid = $_POST['site_id'];
//        if (!empty($siteid)) {  
//            global $mainWPWordfenceExtensionActivator;
//            $post_data = array( 'mwp_action' => 'update_live_traffic', 'liveTrafficEnabled' => $_POST['liveTrafficEnabled']);
//            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
//            if (is_array($information) && isset($information['ok']) && $information['ok']) {
//                $w = new MainWPWordfenceConfigSite($siteid); // new: to load data
//                MainWPWordfenceConfigSite::set('liveTrafficEnabled', $_POST['liveTrafficEnabled']); 
//                MainWPWordfenceConfigSite::save_settings();
//            }
//            die(json_encode($information));
//        }
        die();
    }
    
    public static function ajax_saveConfig_callback(){
        return self::handlePostSettings();          
    }
    
    static function handlePostSettings($site_id = null) {        
        $opts = MainWPWordfenceConfig::parseOptions();
        $emails = array();
        foreach(explode(',', preg_replace('/[\r\n\s\t]+/', '', $opts['alertEmails'])) as $email){
                if(strlen($email) > 0){
                        $emails[] = $email;
                }
        }
        if(sizeof($emails) > 0){
                $badEmails = array();
                foreach($emails as $email){
                        if(! preg_match('/^[^@]+@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,11})$/i', $email)){
                                $badEmails[] = $email;
                        }
                }
                if(sizeof($badEmails) > 0){
                        return array('errorMsg' => "The following emails are invalid: " . htmlentities(implode(', ', $badEmails)) );
                }
                $opts['alertEmails'] = implode(',', $emails);
        } else {
                $opts['alertEmails'] = '';
        }
        $opts['scan_exclude'] = preg_replace('/[\r\n\s\t]+/', '', $opts['scan_exclude']);

        $whiteIPs = array();
        foreach(explode(',', preg_replace('/[\r\n\s\t]+/', '', $opts['whitelisted'])) as $whiteIP){
                if(strlen($whiteIP) > 0){
                        $whiteIPs[] = $whiteIP;
                }
        }
        if(sizeof($whiteIPs) > 0){
                $badWhiteIPs = array();
                foreach($whiteIPs as $whiteIP){
                        if(! preg_match('/^[\[\]\-\d]+\.[\[\]\-\d]+\.[\[\]\-\d]+\.[\[\]\-\d]+$/', $whiteIP)){
                                $badWhiteIPs[] = $whiteIP;
                        }
                }
                if(sizeof($badWhiteIPs) > 0){
                        return array('errorMsg' => "Please make sure you separate your IP addresses with commas. The following whitelisted IP addresses are invalid: " . htmlentities(implode(', ', $badWhiteIPs)) );
                }
                $opts['whitelisted'] = implode(',', $whiteIPs);
        } else {
                $opts['whitelisted'] = '';
        }
                
        $userBlacklist = array();
        foreach(explode(',', $opts['loginSec_userBlacklist']) as $user){
                $user = trim($user);
                if(strlen($user) > 0){
                        $userBlacklist[] = $user;
                }
        }
        if(sizeof($userBlacklist) > 0){
                $opts['loginSec_userBlacklist'] = implode(',', $userBlacklist);
        } else {
                $opts['loginSec_userBlacklist'] = '';
        }
        
        $validIPs = array();     
        $invalidIPs = array();
        foreach(explode(',', preg_replace('/[\r\n\s\t]+/', '', $opts['liveTraf_ignoreIPs'])) as $val){
                if(strlen($val) > 0){
                        if(MainWPWordfenceUtility::isValidIP($val)){
                                $validIPs[] = $val;
                        } else {
                                $invalidIPs[] = $val;
                        }
                }
        }
        if(sizeof($invalidIPs) > 0){
                return array('errorMsg' => "The following IPs you selected to ignore in live traffic reports are not valid: " . wp_kses(implode(', ', $invalidIPs), array()) );
        }
        if(sizeof($validIPs) > 0){
                $opts['liveTraf_ignoreIPs'] = implode(',', $validIPs);
        }

        if(preg_match('/[a-zA-Z0-9\d]+/', $opts['liveTraf_ignoreUA'])){
                $opts['liveTraf_ignoreUA'] = trim($opts['liveTraf_ignoreUA']);
        } else {
                $opts['liveTraf_ignoreUA'] = '';
        }
                
        if ($site_id) {           
            $override = isset($_POST['mainwp_wfc_override_global_setting']) && $_POST['mainwp_wfc_override_global_setting'] ? 1 : 0;            
            MainWPWordfenceDB::Instance()->updateSetting(array('site_id' => $site_id, 'override' => $override, 'settings' => serialize($opts)));
        } else {
            foreach($opts as $key => $val){               
                    MainWPWordfenceConfig::set($key, $val);                
            }
        }        
               
        if (is_array($_POST['apiKey']) && count($_POST['apiKey']) > 0) {
            $_error = "";
            foreach($_POST['apiKey'] as $wid => $_apiKey) {    
                $_apiKey = trim($_apiKey);
                //error_log($wid . "===>" . $_apiKey);
                if($_apiKey && (! preg_match('/^[a-fA-F0-9]+$/', $_apiKey)) ){ //User entered something but it's garbage.
                    $_error .= $_apiKey . "<br>";
                } else {                    
                    MainWPWordfenceDB::Instance()->updateSetting(array(
                                                                    'site_id' => $wid,
                                                                    'apiKey' => $_apiKey,
                                                                ));
                }
            }            
            if (!empty($_error)) {
                $_error = "You entered an API key but it is not in a valid format. It must consist only of characters A to F and 0 to 9:" . "<br>" .$_error;
                return array('errorMsg' => $_error);
            }            
        } 
        
        return array('ok' => 1, 'reload' => "", 'paidKeyMsg' => false );
    }
   
    public static function ajax_blockIP_callback(){        
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'block_ip', 
                                'IP' => $_POST['IP'],
                                'perm' => isset($_POST['perm']) ? $_POST['perm'] : 0,
                                'reason' => $_POST['reason']
                            );            
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			            
            
            $network = isset($_POST['network']) && !empty($_POST['network']) ? true : false;
            if ($network) {
                if (is_array($information) && isset($information['error'])) {
                    $information['_error'] = $information['error'];
                    unset($information['error']);
                }
                if (is_array($information) && isset($information['errorMsg'])) {
                    $information['_errorMsg'] = $information['errorMsg'];
                    unset($information['errorMsg']);
                }
            }
            
            die(json_encode($information));
        }
        die();        
    }
      
    public static function ajax_unblockIP_callback(){        
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'unblock_ip', 
                                'IP' => $_POST['IP']                               
                            );
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			            
            
            $network = isset($_POST['network']) && !empty($_POST['network']) ? true : false;
            if ($network) {
                if (is_array($information) && isset($information['error'])) {
                    $information['_error'] = $information['error'];
                    unset($information['error']);
                }
                if (is_array($information) && isset($information['errorMsg'])) {
                    $information['_errorMsg'] = $information['errorMsg'];
                    unset($information['errorMsg']);
                }
            }
            
            die(json_encode($information));
        }
        die();        
    }
    
    public static function ajax_loadStaticPanel_callback(){
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'load_static_panel', 
                                'mode' => $_POST['mode']                               
                            );
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			                        
            die(json_encode($information));
        }
        die();           
    }
       
    public static function ajax_downgradeLicense_callback(){
        $siteid = $_POST['site_id'];
        if (!empty($siteid)) {  
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'downgrade_license');
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			            
            
            $update = array('site_id' => $siteid);
            $perform = false;
            
            if(is_array($information) && isset($information['isPaid'])) {
                $perform = true;
                $update['isPaid'] = $information['isPaid'];            
                $update['apiKey'] = $information['apiKey'];
            }
            if ($perform)
                MainWPWordfenceDB::Instance()->updateSetting($update);            
            
            die(json_encode($information));
        }
        die();
    }
        
}
