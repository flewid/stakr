<?php
class MainWPWordfenceSetting
{            
    //Singleton
    private static $instance = null;      
    static function Instance()
    {
        if (MainWPWordfenceSetting::$instance == null) {
            MainWPWordfenceSetting::$instance = new MainWPWordfenceSetting();
        }
        return MainWPWordfenceSetting::$instance;
    }
    
    public function __construct() {
        
    }
    
    public function admin_init() {         
        add_action('wp_ajax_mainwp_wfc_save_settings', array($this,'ajax_save_settings'));        
        add_action('mainwp-extension-sites-edit', array(&$this, 'site_edit'),9,1);   
        add_action('mainwp_update_site', array(&$this, 'update_site'), 8, 1);
        add_action('wp_ajax_mainwp_wfc_save_settings_reload', array($this,'ajax_save_settings_reload'));        
       
    }
        
    public function site_edit($website = null)
    {
        self::gen_settings_tab(array($website), true);	
    }	
        
    public function update_site($websiteId) {              
        if (isset($_POST['submit']) && $websiteId) {               
            update_option('mainwp_wfc_do_save_individual_setting', 'yes');
            MainWPWordfence::handlePostSettings($websiteId);              
        }
    }
    
    function ajax_save_settings() {
            $siteid = $_POST['siteId'];	
            if (empty($siteid))	
                die(json_encode('FAIL')); 			
            
            $w = new MainWPWordfenceConfigSite($siteid); // new: to load data
            $cacheType = $w->get_cacheType();
            $apiKey = MainWPWordfenceConfigSite::$apiKey;
            
            if ($override = $w->is_override()) {
                $options = MainWPWordfenceConfigSite::load_settings();
            } else {
                $options = MainWPWordfenceConfig::load_settings();                
            } 
            $individual = isset($_POST['individual']) && $_POST['individual'] ? true : false;
            
            if (!$individual && $override) {               
                // if not individual and overrided dont update.
                die(json_encode(array('result' =>'OVERRIDED'))); 			
            }
            
            global $mainWPWordfenceExtensionActivator;
            $post_data = array('mwp_action' => 'save_setting',
                               'apiKey' => $apiKey);                       
            $post_data['settings'] = base64_encode(serialize($options));            
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			            
            //print_r($information);
            if (is_array($information)) {                 
                $update = array('site_id' => $siteid);
                $perform = false;
                if(isset($information['isPaid'])) {
                    $perform = true;
                    $update['isPaid'] = $information['isPaid'];            
                    $update['apiKey'] = $information['apiKey'];
                }
                if(isset($information['cacheType']) && $cacheType != $information['cacheType']) {   
                    $perform = true;
                    $update['cacheType'] = $information['cacheType'];                    
                } 
                if ($perform)
                    MainWPWordfenceDB::Instance()->updateSetting($update);
            }   
            
            die(json_encode($information)); 
    }
    
    public function ajax_save_settings_reload() {
        $siteid = $_POST['siteId'];	
        if (empty($siteid))	
            die('Error reload.'); 
        
        $w = new MainWPWordfenceConfigSite($siteid);
        $is_Paid = MainWPWordfenceConfigSite::get('isPaid');
        $api_Key = MainWPWordfenceConfigSite::get('apiKey');        
        ?>
<tr><th>Wordfence API Key:</th><td><input type="text" class="apiKey" name="apiKey[<?php echo $siteid; ?>]" value="<?php echo esc_attr($api_Key); ?>" size="80" />&nbsp;
                <?php if($is_Paid){ ?>
                The currently active API Key is a Premium Key. <span style="font-weight: bold; color: #0A0;">Premium scanning enabled!</span>
                <?php } else {?>
                The currently active API Key is a <span style="color: #F00; font-weight: bold;">Free Key</a>.
                <?php } ?>
        </td></tr>                      
        <tr><th>&nbsp;</th><td>
                <?php if($is_Paid){ ?>
                <table border="0"><tr><td><a href="https://www.wordfence.com/manage-wordfence-api-keys/" target="_blank"><input type="button" value="Renew your premium license" /></a></td><td>&nbsp;</td><td><input type="button" value="Downgrade to a free license" onclick="MWP_WFAD.downgradeLicense($siteid);" /></td></tr></table>
                <?php } ?>
        </td></tr>  
        <?php
        die();        
    }
    
    public static function gen_save_settings() {
        
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
        $all_the_plugin_sites = array();
        foreach($dbwebsites as $website) {
            if ($website && $website->plugins != '')  { 
                $plugins = json_decode($website->plugins, 1);                           
                if (is_array($plugins) && count($plugins) != 0) {                            
                    foreach ($plugins as $plugin)
                    {                            
                        if ($plugin['slug'] == "wordfence/wordfence.php") {                                    
                            if ($plugin['active']) {
                                $all_the_plugin_sites[] = MainWPWordfenceUtility::mapSite($website, array('id', 'name'));
                                break;
                            }
                        }
                    }
                }
            }
        }
        $url_loader = plugins_url('images/loader.gif', dirname(__FILE__));      
        if (count($all_the_plugin_sites) > 0) {			
                echo '<h3>' . __('Saving Settings to child sites ...','mainwp'). '</h3>';
                foreach($all_the_plugin_sites as $website) 
                {			
                        echo '<div><strong>' . $website['name'] .'</strong>: ';
                        echo '<span class="itemToProcess" siteid="' . $website['id'] . '" status="queue"><span class="loading" style="display: none"><img src="' . $url_loader . '"/></span> <span class="status">Queue</span><br />';											                        
                        echo '<div style="display: none" class="detailed"></div>';
                        echo '</div><br />';
                }	
                ?>
                <div id="mainwp_wfc_save_setting_ajax_message" class="mainwp_info-box-yellow hidden"></div>
                <script>
                    jQuery(document).ready(function($) {
                        mainwp_wfc_save_setting_start_next();
                    })
                </script>
                <?php
                return true;
        } else {
            echo '<div class="mainwp_info-box-yellow">' . __('No child sites with the Wordfence Security plugin installed.','mainwp') . '</div>';
            ?>
            <script>
                jQuery(document).ready(function($) {
                    setTimeout(function() {
                        location.href = 'admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=setting';
                    }, 3000);
                })
            </script>
            <?php
        }        
    }
    
    public static function gen_settings_tab($websites = array(), $edit_site = false) {        
        $url_loader = plugins_url('images/loader.gif', dirname(__FILE__));        
        $_edit_site_id = $override = 0;
        ?>
        <script type="text/javascript">
            var mainwp_WFSLevels = <?php echo json_encode(MainWPWordfenceConfig::$securityLevels); ?>;        
            <?php     
            if ($edit_site) {
                $website = current($websites);
                if (is_object($website)) {
                    $_edit_site_id = $website->id;                     
                    if (get_option('mainwp_wfc_do_save_individual_setting') == 'yes') { 
                        update_option('mainwp_wfc_do_save_individual_setting', '');               
                    ?> 
                        jQuery(document).ready(function($){  
                            mainwp_wfc_save_site_settings(<?php echo $_edit_site_id; ?>);
                        })                
            <?php                   
                    } 
                }
                $w = new MainWPWordfenceConfigSite($_edit_site_id);   
                $override = $w->is_override(); 
                $is_Paid = MainWPWordfenceConfigSite::get('isPaid', 0, $_edit_site_id);
                $api_Key = MainWPWordfenceConfigSite::get('apiKey', "", $_edit_site_id);
            }
            
        ?>
        </script>
        <?php
            if (!$edit_site) {  
                $website_ids = array();
                foreach($websites as $site_id => $site_name) {   
                    $website_ids[] = $site_id;
                }
                $w = new MainWPWordfenceConfig($website_ids);     
                $is_Paids = $w->get_isPaids();
                $api_Keys = $w->get_apiKeys();
                $is_Paid = true; // to enable input premium options for network
                //print_r($api_Keys);
            }
        ?>
        <div class="wordfenceModeElem" id="mwp_wordfenceMode_settings"></div>        
        <div class="mwp_wfc_settings_form_content">
        <?php if (!$edit_site) { ?>
                <div class="postbox mainwp_wfc_postbox closed">   
                    <div class="handlediv"><br /></div> 
                    <h3 class="mainwp_box_title" ><span><?php echo __("Licenses <span class=\"wfc_postbox_title\">(click right arrow to open or close section)</span>", "mainwp"); ?></span></h3>
                    <div class="inside" id="mainwp_wfc_postbox_setting_license"> 
                        <table>
                            <tbody>   
                            <?php if (is_array($websites) && count($websites) > 0) {
                                        ?>
                                <tr><td colspan="2">Wordfence API Keys <a href="https://www.wordfence.com/wordfence-signup/" target="_blank">Click Here to Upgrade to Wordfence Premium now.</a></td></tr>
                                        <?php
                                        foreach($websites as $site_id => $site_name) {                                        
                                            $is_Paid = isset($is_Paids[$site_id]) ? $is_Paids[$site_id] : 0;                                         
                                            $api_Key = isset($api_Keys[$site_id]) ? $api_Keys[$site_id] : "";                                        
                                        ?>                                        
                                        <tr><th><?php echo $site_name; ?></th><td><input type="text" class="apiKey" name="apiKey[<?php echo $site_id; ?>]" value="<?php echo esc_attr($api_Key); ?>" size="80" />&nbsp;&nbsp;                                           
                                                    <?php if($is_Paid){ ?>
                                                    The currently active API Key is a Premium Key. <span style="font-weight: bold; color: #0A0;">Premium scanning enabled!</span>
                                                    <?php } else {?>
                                                    The currently active API Key is a <span style="color: #F00; font-weight: bold;">Free Key</a>.
                                                    <?php } ?>
                                            </td></tr>                      
                                            <tr><th>&nbsp;</th><td>
                                                    <?php if($is_Paid){ ?>
                                                    <table border="0"><tr><td><a href="https://www.wordfence.com/manage-wordfence-api-keys/" target="_blank"><input type="button" value="Renew your premium license" /></a></td><td>&nbsp;</td><td><input type="button" value="Downgrade to a free license" onclick="MWP_WFAD.downgradeLicense(<?php echo $site_id; ?>);" /></td></tr></table>
                                                    <?php } ?>
                                            </td></tr> 
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr><td colspan="2">No websites were found with the Wordfence plugin installed.</td></tr>
                                        <?php
                                    }                            
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>         
        <?php } ?>            
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div> 
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Settings", "mainwp") : __("Scan Schedule", "mainwp"); ?></span></h3>
            <div class="inside"> 
                <div id="mwp_wfc_edit_setting_ajax_message">
                    <div class="status mainwp_info-box-yellow" style="display:none;"></div>
                    <span class="loading" style="display:none;"><img src="<?php echo $url_loader; ?>"> <?php _e("Saving ..." ,"mainwp"); ?></span>
                    <div style="display: none" class="detailed"></div>                    
                </div>
                <table>
                    <tbody>
                        <?php if ($edit_site) { ?>   
                            <tr>
                                <th><?php _e('Override General Settings','mainwp'); ?> <?php do_action('mainwp_renderToolTip', __('Set to YES if you want to overwrite global wordfence options.','mainwp')); ?></th>
                                <td>
                                  <div class="mainwp-checkbox">
                                        <input type="checkbox" id="mainwp_wfc_override_global_setting" name="mainwp_wfc_override_global_setting"  <?php echo ($override == 0 ? '' : 'checked="checked"'); ?> value="1"/>
                                        <label for="mainwp_wfc_override_global_setting"></label>
                                  </div>
                               </td>
                            </tr>
                            <tr><th>&nbsp;</th><td>&nbsp;</td></tr>
                        <?php } ?>  
                        <tr><th>Scan Schedule</th><td>
                                <select id="scheduleScan" name="scheduleScan">
                                        <option value=""<?php $w->sel('scheduleScan', ''); ?>>N/A</option>
                                        <option value="twicedaily"<?php $w->sel('scheduleScan', 'twicedaily'); ?>>Twice a Day</option>
                                        <option value="daily"<?php $w->sel('scheduleScan', 'daily'); ?>>Once a Day</option>
                                        <option value="weekly"<?php $w->sel('scheduleScan', 'weekly'); ?>>Once a Week</option>
                                        <option value="monthly"<?php $w->sel('scheduleScan', 'monthly'); ?>>Once a Month</option>
                                </select>
                                </td></tr>                                               
                    </tbody>
                </table>
            </div>
        </div>                     
        <?php
        if($edit_site) { 
        ?>            
            <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div> 
            <h3 class="mainwp_box_title" ><span><?php echo __("Wordfence License", "mainwp"); ?></span></h3>
            <div class="inside"> 
                <table>
                    <tbody  id="mwp_wfc_license_body">                         
                        <tr><th>Wordfence API Key:</th><td><input type="text" class="apiKey" name="apiKey[<?php echo $_edit_site_id; ?>]" value="<?php echo esc_attr($api_Key); ?>" size="80" />&nbsp;&nbsp;
                                <?php if($is_Paid){ ?>
                                The currently active API Key is a Premium Key. <span style="font-weight: bold; color: #0A0;">Premium scanning enabled!</span>
                                <?php } else {?>
                                The currently active API Key is a <span style="color: #F00; font-weight: bold;">Free Key</a>.
                                <?php } ?>
                        </td></tr>                      
                        <tr><th>&nbsp;</th><td>
                                <?php if($is_Paid){ ?>
                                <table border="0"><tr><td><a href="https://www.wordfence.com/manage-wordfence-api-keys/" target="_blank"><input type="button" value="Renew your premium license" /></a></td><td>&nbsp;</td><td><input type="button" value="Downgrade to a free license" onclick="MWP_WFAD.downgradeLicense(<?php echo $_edit_site_id; ?>);" /></td></tr></table>
                                <?php } ?>
                        </td></tr>                        
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        }            
    ?>                        
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div> 
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Basic Settings", "mainwp") : __("Basic Settings", "mainwp"); ?></span></h3>
            <div class="inside"> 
                <table>
                    <tbody>
                        <tr><th class="wfConfigEnable">Enable firewall </th><td><input type="checkbox" id="firewallEnabled" class="wfConfigElem" name="firewallEnabled" value="1" <?php $w->cb('firewallEnabled'); ?> />&nbsp;<span style="color: #F00;">NOTE:</span> This checkbox enables ALL firewall functions including IP, country and advanced blocking and the "Firewall Rules" below.</td></tr>
                        <tr><th class="wfConfigEnable">Enable login security</th><td><input type="checkbox" id="loginSecurityEnabled" class="wfConfigElem" name="loginSecurityEnabled" value="1" <?php $w->cb('loginSecurityEnabled'); ?> />&nbsp;This option enables all "Login Security" options. You can modify individual options further down this page.</td></tr>
                        <tr><th class="wfConfigEnable">Enable Live Traffic View</th><td><input type="checkbox" id="liveTrafficEnabled" class="wfConfigElem" name="liveTrafficEnabled" value="1" <?php $w->cb('liveTrafficEnabled'); ?> onclick="MWP_WFAD.reloadConfigPage = true; return true;" />&nbsp;This option enables live traffic logging.</td></tr>
                        <tr><th class="wfConfigEnable">Advanced Comment Spam Filter</th><td><input type="checkbox" id="advancedCommentScanning" class="wfConfigElem" name="advancedCommentScanning" value="1" <?php $w->cbp('advancedCommentScanning'); if(! $is_Paid){ ?>onclick="alert('This is a paid feature because it places significant additional load on our servers.'); jQuery('#advancedCommentScanning').attr('checked', false); return false;" <?php } ?> />&nbsp;<span style="color: #F00;">Premium Feature</span> In addition to free comment filtering (see below) this option filters comments against several additional real-time lists of known spammers and infected hosts.</td></tr>
                        <tr><th class="wfConfigEnable">Check if this website is being "Spamvertised"</th><td><input type="checkbox" id="spamvertizeCheck" class="wfConfigElem" name="spamvertizeCheck" value="1" <?php $w->cbp('spamvertizeCheck'); if(! $is_Paid){ ?>onclick="alert('This is a paid feature because it places significant additional load on our servers.'); jQuery('#spamvertizeCheck').attr('checked', false); return false;" <?php } ?> />&nbsp;<span style="color: #F00;">Premium Feature</span> When doing a scan, Wordfence will check with spam services if your site domain name is appearing as a link in spam emails.</td></tr>
                        <tr><th class="wfConfigEnable">Check if this website IP is generating spam</th><td><input type="checkbox" id="checkSpamIP" class="wfConfigElem" name="checkSpamIP" value="1" <?php $w->cbp('checkSpamIP'); if(! $is_Paid){ ?>onclick="alert('This is a paid feature because it places significant additional load on our servers.'); jQuery('#checkSpamIP').attr('checked', false); return false;" <?php } ?> />&nbsp;<span style="color: #F00;">Premium Feature</span> When doing a scan, Wordfence will check with spam services if your website IP address is listed as a known source of spam email.</td></tr>
                        <tr><th class="wfConfigEnable">Enable automatic scheduled scans</th><td><input type="checkbox" id="scheduledScansEnabled" class="wfConfigElem" name="scheduledScansEnabled" value="1" <?php $w->cb('scheduledScansEnabled'); ?> />&nbsp;Regular scans ensure your site stays secure.</td></tr>
                        <tr><th class="wfConfigEnable">Update Wordfence automatically when a new version is released?</th><td><input type="checkbox" id="autoUpdate" class="wfConfigElem" name="autoUpdate" value="1" <?php $w->cb('autoUpdate'); ?> />&nbsp;Automatically updates Wordfence to the newest version within 24 hours of a new release.</td></tr>
                        <tr><th>Where to email alerts:</th><td><input type="text" id="alertEmails" name="alertEmails" value="<?php $w->f('alertEmails'); ?>" size="50" />&nbsp;<span class="wfTipText">Separate multiple emails with commas</span></td></tr>
                        <tr><th>Security Level:</th><td>
                                <select id="securityLevel" name="securityLevel" onchange="MWP_WFAD.changeSecurityLevel(); return true;">
                                        <option value="0"<?php $w->sel('securityLevel', '0'); ?>>Level 0: Disable all Wordfence security measures</option>
                                        <option value="1"<?php $w->sel('securityLevel', '1'); ?>>Level 1: Light protection. Just the basics</option>
                                        <option value="2"<?php $w->sel('securityLevel', '2'); ?>>Level 2: Medium protection. Suitable for most sites</option>
                                        <option value="3"<?php $w->sel('securityLevel', '3'); ?>>Level 3: High security. Use this when an attack is imminent</option>
                                        <option value="4"<?php $w->sel('securityLevel', '4'); ?>>Level 4: Lockdown. Protect the site against an attack in progress at the cost of inconveniencing some users</option>
                                        <option value="CUSTOM"<?php $w->sel('securityLevel', 'CUSTOM'); ?>>Custom settings</option>
                                </select>
                                </td></tr>
                        <tr><th>How does Wordfence get IPs:</th><td>
                                <select id="howGetIPs" name="howGetIPs">
                                        <option value="">Set this option if you're seeing visitors from fake IP addresses or who appear to be from your internal network but aren't.</option>
                                        <option value="REMOTE_ADDR"<?php $w->sel('howGetIPs', 'REMOTE_ADDR'); ?>>Use PHP's built in REMOTE_ADDR. Use this if you're not using Nginx or any separate front-end proxy or firewall. Try this first.</option>
                                        <option value="HTTP_X_REAL_IP"<?php $w->sel('howGetIPs', 'HTTP_X_REAL_IP'); ?>>Use the X-Real-IP HTTP header which my Nginx, firewall or front-end proxy is setting. Try this next.</option>
                                        <option value="HTTP_X_FORWARDED_FOR"<?php $w->sel('howGetIPs', 'HTTP_X_FORWARDED_FOR'); ?>>Use the X-Forwarded-For HTTP header which my Nginx, firewall or front-end proxy is setting.</option>
                                        <option value="HTTP_CF_CONNECTING_IP"<?php $w->sel('howGetIPs', 'HTTP_CF_CONNECTING_IP'); ?>>I'm using Cloudflare so use the "CF-Connecting-IP" HTTP header to get a visitor IP</option>
                                </select>
                                </td></tr>                                        
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!$edit_site) { ?>
        <div>                                    
            <input type="button" onclick="MWP_WFAD.saveConfig();" class="button-primary" value="<?php _e("Save Settings", "mainwp"); ?>">                                                        
            <img class="wfcSaveOpts" style="display: none" src="<?php echo $url_loader; ?>"><span class="wfSavedMsg">&nbsp;Your changes have been saved!</span>            
        </div>
        <?php } ?>
        <br>                                   
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div>           
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Alerts", "mainwp") : __("Alerts", "mainwp"); ?></span></h3>
            <div class="inside">                
                 <table>
                    <tbody>
                        <?php
                        $emails = $w->get_AlertEmails();
                        if(sizeof($emails) < 1){ 
                            echo "<tr><th colspan=\"2\" style=\"color: #F00;\">You have not configured an email to receive alerts yet. Set this up under \"Basic Options\" above.</th></tr>\n";
                        }
                        ?>
                        <tr><th>Email me when Wordfence is automatically updated</th><td><input type="checkbox" id="alertOn_update" class="wfConfigElem" name="alertOn_update" value="1" <?php $w->cb('alertOn_update'); ?>/>&nbsp;If you have automatic updates enabled (see above), you'll get an email when an update occurs.</td></tr>
                        <tr><th>Alert on critical problems</th><td><input type="checkbox" id="alertOn_critical" class="wfConfigElem" name="alertOn_critical" value="1" <?php $w->cb('alertOn_critical'); ?>/></td></tr>
                        <tr><th>Alert on warnings</th><td><input type="checkbox" id="alertOn_warnings" class="wfConfigElem" name="alertOn_warnings" value="1" <?php $w->cb('alertOn_warnings'); ?>/></td></tr>
                        <tr><th>Alert when an IP address is blocked</th><td><input type="checkbox" id="alertOn_block" class="wfConfigElem" name="alertOn_block" value="1" <?php $w->cb('alertOn_block'); ?>/></td></tr>
                        <tr><th>Alert when someone is locked out from login</th><td><input type="checkbox" id="alertOn_loginLockout" class="wfConfigElem" name="alertOn_loginLockout" value="1" <?php $w->cb('alertOn_loginLockout'); ?>/></td></tr>
                        <tr><th>Alert when the "lost password" form is used for a valid user</th><td><input type="checkbox" id="alertOn_lostPasswdForm" class="wfConfigElem" name="alertOn_lostPasswdForm" value="1" <?php $w->cb('alertOn_lostPasswdForm'); ?>/></td></tr>
                        <tr><th>Alert me when someone with administrator access signs in</th><td><input type="checkbox" id="alertOn_adminLogin" class="wfConfigElem" name="alertOn_adminLogin" value="1" <?php $w->cb('alertOn_adminLogin'); ?>/></td></tr>
                        <tr><th>Alert me when a non-admin user signs in</th><td><input type="checkbox" id="alertOn_nonAdminLogin" class="wfConfigElem" name="alertOn_nonAdminLogin" value="1" <?php $w->cb('alertOn_nonAdminLogin'); ?>/></td></tr>
                        <tr><th>Maximum email alerts to send per hour</th><td>&nbsp;<input type="text" id="alert_maxHourly" name="alert_maxHourly" value="<?php $w->f('alert_maxHourly'); ?>" size="4" />0 or empty means unlimited alerts will be sent.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>  
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div>           
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Live Traffic View", "mainwp") : __("Live Traffic View", "mainwp"); ?></span></h3>
            <div class="inside">                
                 <table>
                    <tbody>
                        <tr><th>Don't log signed-in users with publishing access:</th><td><input type="checkbox" id="liveTraf_ignorePublishers" name="liveTraf_ignorePublishers" value="1" <?php $w->cb('liveTraf_ignorePublishers'); ?> /></td></tr>
                        <tr><th>List of comma separated usernames to ignore:</th><td><input type="text" name="liveTraf_ignoreUsers" id="liveTraf_ignoreUsers" value="<?php echo $w->getHTML('liveTraf_ignoreUsers'); ?>" /></td></tr>
                        <tr><th>List of comma separated IP addresses to ignore:</th><td><input type="text" name="liveTraf_ignoreIPs" id="liveTraf_ignoreIPs" value="<?php echo $w->getHTML('liveTraf_ignoreIPs'); ?>" /></td></tr>
                        <tr><th>Browser user-agent to ignore:</th><td><input type="text" name="liveTraf_ignoreUA" id="liveTraf_ignoreUA" value="<?php echo $w->getHTML('liveTraf_ignoreUA'); ?>" /></td></tr>
                    </tbody>
                </table>
            </div>
        </div>  
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div>           
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Scans to include", "mainwp") : __("Scans to include", "mainwp"); ?></span></h3>
            <div class="inside">                
                 <table>
                    <tbody>    
                        <?php if($is_Paid){ ?>
                        <tr><th>Scan public facing site for vulnerabilities?</th><td><input type="checkbox" id="scansEnabled_public" class="wfConfigElem" name="scansEnabled_public" value="1" <?php $w->cb('scansEnabled_public'); ?></td></tr>
                        <?php } else { ?>
                        <tr><th style="color: #F00;">Scan public facing site for vulnerabilities? (<a href="https://www.wordfence.com/wordfence-signup/" target="_blank">Paid members only</a>)</th><td><input type="checkbox" id="scansEnabled_public" class="wfConfigElem" name="scansEnabled_public" value="1" DISABLED ?></td></tr>
                        <?php } ?>

                        <tr><th>Scan for the HeartBleed vulnerability?</th><td><input type="checkbox" id="scansEnabled_heartbleed" class="wfConfigElem" name="scansEnabled_heartbleed" value="1" <?php $w->cb('scansEnabled_heartbleed'); ?></td></tr>
                        <tr><th>Scan core files against repository versions for changes</th><td><input type="checkbox" id="scansEnabled_core" class="wfConfigElem" name="scansEnabled_core" value="1" <?php $w->cb('scansEnabled_core'); ?>/></td></tr>

                        <tr><th>Scan theme files against repository versions for changes</th><td><input type="checkbox" id="scansEnabled_themes" class="wfConfigElem" name="scansEnabled_themes" value="1" <?php $w->cb('scansEnabled_themes'); ?>/></td></tr>
                        <tr><th>Scan plugin files against repository versions for changes</th><td><input type="checkbox" id="scansEnabled_plugins" class="wfConfigElem" name="scansEnabled_plugins" value="1" <?php $w->cb('scansEnabled_plugins'); ?>/></td></tr>
                        <tr><th>Scan for signatures of known malicious files</th><td><input type="checkbox" id="scansEnabled_malware" class="wfConfigElem" name="scansEnabled_malware" value="1" <?php $w->cb('scansEnabled_malware'); ?>/></td></tr>
                        <tr><th>Scan file contents for backdoors, trojans and suspicious code</th><td><input type="checkbox" id="scansEnabled_fileContents" class="wfConfigElem" name="scansEnabled_fileContents" value="1" <?php $w->cb('scansEnabled_fileContents'); ?>/></td></tr>
                        <tr><th>Scan posts for known dangerous URLs and suspicious content</th><td><input type="checkbox" id="scansEnabled_posts" class="wfConfigElem" name="scansEnabled_posts" value="1" <?php $w->cb('scansEnabled_posts'); ?>/></td></tr>
                        <tr><th>Scan comments for known dangerous URLs and suspicious content</th><td><input type="checkbox" id="scansEnabled_comments" class="wfConfigElem" name="scansEnabled_comments" value="1" <?php $w->cb('scansEnabled_comments'); ?>/></td></tr>
                        <tr><th>Scan for out of date plugins, themes and WordPress versions</th><td><input type="checkbox" id="scansEnabled_oldVersions" class="wfConfigElem" name="scansEnabled_oldVersions" value="1" <?php $w->cb('scansEnabled_oldVersions'); ?>/></td></tr>
                        <tr><th>Check the strength of passwords</th><td><input type="checkbox" id="scansEnabled_passwds" class="wfConfigElem" name="scansEnabled_passwds" value="1" <?php $w->cb('scansEnabled_passwds'); ?>/></td></tr>
                        <tr><th>Scan options table</th><td><input type="checkbox" id="scansEnabled_options" class="wfConfigElem" name="scansEnabled_options" value="1" <?php $w->cb('scansEnabled_options'); ?>/></td></tr>
                        <tr><th>Monitor disk space</th><td><input type="checkbox" id="scansEnabled_diskSpace" class="wfConfigElem" name="scansEnabled_diskSpace" value="1" <?php $w->cb('scansEnabled_diskSpace'); ?>/></td></tr>
                        <tr><th>Scan for unauthorized DNS changes</th><td><input type="checkbox" id="scansEnabled_dns" class="wfConfigElem" name="scansEnabled_dns" value="1" <?php $w->cb('scansEnabled_dns'); ?>/></td></tr>
                        <tr><th>Scan files outside your WordPress installation</th><td><input type="checkbox" id="other_scanOutside" class="wfConfigElem" name="other_scanOutside" value="1" <?php $w->cb('other_scanOutside'); ?> /></td></tr>
                        <tr><th>Scan image files as if they were executable</th><td><input type="checkbox" id="scansEnabled_scanImages" class="wfConfigElem" name="scansEnabled_scanImages" value="1" <?php $w->cb('scansEnabled_scanImages'); ?> /></td></tr>
                        <tr><th>Enable HIGH SENSITIVITY scanning. May give false positives.</th><td><input type="checkbox" id="scansEnabled_highSense" class="wfConfigElem" name="scansEnabled_highSense" value="1" <?php $w->cb('scansEnabled_highSense'); ?> /></td></tr>
                        <tr><th>Exclude files from scan that match these wildcard patterns. Comma separated.</th><td><input type="text" id="scan_exclude" class="wfConfigElem" name="scan_exclude" size="20" value="<?php echo $w->getHTML('scan_exclude'); ?>" />e.g. *.sql,*.tar,backup*.zip</td></tr>
                    </tbody>
                </table>
            </div>
        </div>   
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div>           
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Firewall Rules", "mainwp") : __("Firewall Rules", "mainwp"); ?></span></h3>
            <div class="inside">                
                 <table>
                    <tbody>   
                        <tr><th>Immediately block fake Google crawlers:</th><td><input type="checkbox" id="blockFakeBots" class="wfConfigElem" name="blockFakeBots" value="1" <?php $w->cb('blockFakeBots'); ?>/></td></tr>
                        <tr><th>How should we treat Google's crawlers</th><td>
                                <select id="neverBlockBG" class="wfConfigElem" name="neverBlockBG">
                                        <option value="neverBlockVerified"<?php $w->sel('neverBlockBG', 'neverBlockVerified'); ?>>Verified Google crawlers have unlimited access to this site</option>
                                        <option value="neverBlockUA"<?php $w->sel('neverBlockBG', 'neverBlockUA'); ?>>Anyone claiming to be Google has unlimited access</option>
                                        <option value="treatAsOtherCrawlers"<?php $w->sel('neverBlockBG', 'treatAsOtherCrawlers'); ?>>Treat Google like any other Crawler</option>
                                </select></td></tr>	
                        <?php $include_dir = MainWPWordfenceExtension::$plugin_dir ."includes/"; ?>
                        <tr><th>If anyone's requests exceed:</th><td><?php $rateName='maxGlobalRequests'; require($include_dir . 'wfRate.php'); ?> then <?php $throtName='maxGlobalRequests_action'; require($include_dir . 'wfAction.php'); ?></td></tr>
                        <tr><th>If a crawler's page views exceed:</th><td><?php $rateName='maxRequestsCrawlers'; require($include_dir . 'wfRate.php'); ?> then <?php $throtName='maxRequestsCrawlers_action'; require($include_dir . 'wfAction.php'); ?></td></tr>
                        <tr><th>If a crawler's pages not found (404s) exceed:</th><td><?php $rateName='max404Crawlers'; require($include_dir . 'wfRate.php'); ?> then <?php $throtName='max404Crawlers_action'; require($include_dir . 'wfAction.php'); ?></td></tr>
                        <tr><th>If a human's page views exceed:</th><td><?php $rateName='maxRequestsHumans'; require($include_dir . 'wfRate.php'); ?> then <?php $throtName='maxRequestsHumans_action'; require($include_dir . 'wfAction.php'); ?></td></tr>
                        <tr><th>If a human's pages not found (404s) exceed:</th><td><?php $rateName='max404Humans'; require($include_dir . 'wfRate.php'); ?> then <?php $throtName='max404Humans_action'; require($include_dir . 'wfAction.php'); ?></td></tr>
                        <tr><th>If 404's for known vulnerable URL's exceed:</th><td><?php $rateName='maxScanHits'; require($include_dir . 'wfRate.php'); ?> then <?php $throtName='maxScanHits_action'; require($include_dir . 'wfAction.php'); ?></td></tr>
                        <tr><th>How long is an IP address blocked when it breaks a rule:</th><td>
                                <select id="blockedTime" class="wfConfigElem" name="blockedTime">
                                        <option value="60"<?php $w->sel('blockedTime', '60'); ?>>1 minute</option>
                                        <option value="300"<?php $w->sel('blockedTime', '300'); ?>>5 minutes</option>
                                        <option value="1800"<?php $w->sel('blockedTime', '1800'); ?>>30 minutes</option>
                                        <option value="3600"<?php $w->sel('blockedTime', '3600'); ?>>1 hour</option>
                                        <option value="7200"<?php $w->sel('blockedTime', '7200'); ?>>2 hours</option>
                                        <option value="21600"<?php $w->sel('blockedTime', '21600'); ?>>6 hours</option>
                                        <option value="43200"<?php $w->sel('blockedTime', '43200'); ?>>12 hours</option>
                                        <option value="86400"<?php $w->sel('blockedTime', '86400'); ?>>1 day</option>
                                        <option value="172800"<?php $w->sel('blockedTime', '172800'); ?>>2 days</option>
                                        <option value="432000"<?php $w->sel('blockedTime', '432000'); ?>>5 days</option>
                                        <option value="864000"<?php $w->sel('blockedTime', '864000'); ?>>10 days</option>
                                        <option value="2592000"<?php $w->sel('blockedTime', '2592000'); ?>>1 month</option>
                                </select></td></tr> 
                    </tbody>
                </table>
            </div>
        </div>      
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div>           
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Login Security Options", "mainwp") : __("Login Security Options", "mainwp"); ?></span></h3>    
            <div class="inside">                
                 <table>
                    <tbody>   
                       <tr><th>Enforce strong passwords?</th><td>
                                <select class="wfConfigElem" id="loginSec_strongPasswds" name="loginSec_strongPasswds">
                                        <option value="">Do not force users to use strong passwords</option>
                                        <option value="pubs"<?php $w->sel('loginSec_strongPasswds', 'pubs'); ?>>Force admins and publishers to use strong passwords (recommended)</option>
                                        <option value="all"<?php $w->sel('loginSec_strongPasswds', 'all'); ?>>Force all members to use strong passwords</option>
                                </select>
                        <tr><th>Lock out after how many login failures</th><td>
                                <select id="loginSec_maxFailures" class="wfConfigElem" name="loginSec_maxFailures">
                                        <option value="1"<?php $w->sel('loginSec_maxFailures', '1'); ?>>1</option>
                                        <option value="2"<?php $w->sel('loginSec_maxFailures', '2'); ?>>2</option>
                                        <option value="3"<?php $w->sel('loginSec_maxFailures', '3'); ?>>3</option>
                                        <option value="4"<?php $w->sel('loginSec_maxFailures', '4'); ?>>4</option>
                                        <option value="5"<?php $w->sel('loginSec_maxFailures', '5'); ?>>5</option>
                                        <option value="6"<?php $w->sel('loginSec_maxFailures', '6'); ?>>6</option>
                                        <option value="7"<?php $w->sel('loginSec_maxFailures', '7'); ?>>7</option>
                                        <option value="8"<?php $w->sel('loginSec_maxFailures', '8'); ?>>8</option>
                                        <option value="9"<?php $w->sel('loginSec_maxFailures', '9'); ?>>9</option>
                                        <option value="10"<?php $w->sel('loginSec_maxFailures', '10'); ?>>10</option>
                                        <option value="20"<?php $w->sel('loginSec_maxFailures', '20'); ?>>20</option>
                                        <option value="30"<?php $w->sel('loginSec_maxFailures', '30'); ?>>30</option>
                                        <option value="40"<?php $w->sel('loginSec_maxFailures', '40'); ?>>40</option>
                                        <option value="50"<?php $w->sel('loginSec_maxFailures', '50'); ?>>50</option>
                                        <option value="100"<?php $w->sel('loginSec_maxFailures', '100'); ?>>100</option>
                                        <option value="200"<?php $w->sel('loginSec_maxFailures', '200'); ?>>200</option>
                                        <option value="500"<?php $w->sel('loginSec_maxFailures', '500'); ?>>500</option>
                                </select>
                                </td></tr>
                        <tr><th>Lock out after how many forgot password attempts</th><td>
                                <select id="loginSec_maxForgotPasswd" class="wfConfigElem" name="loginSec_maxForgotPasswd">
                                        <option value="1"<?php $w->sel('loginSec_maxForgotPasswd', '1'); ?>>1</option>
                                        <option value="2"<?php $w->sel('loginSec_maxForgotPasswd', '2'); ?>>2</option>
                                        <option value="3"<?php $w->sel('loginSec_maxForgotPasswd', '3'); ?>>3</option>
                                        <option value="4"<?php $w->sel('loginSec_maxForgotPasswd', '4'); ?>>4</option>
                                        <option value="5"<?php $w->sel('loginSec_maxForgotPasswd', '5'); ?>>5</option>
                                        <option value="6"<?php $w->sel('loginSec_maxForgotPasswd', '6'); ?>>6</option>
                                        <option value="7"<?php $w->sel('loginSec_maxForgotPasswd', '7'); ?>>7</option>
                                        <option value="8"<?php $w->sel('loginSec_maxForgotPasswd', '8'); ?>>8</option>
                                        <option value="9"<?php $w->sel('loginSec_maxForgotPasswd', '9'); ?>>9</option>
                                        <option value="10"<?php $w->sel('loginSec_maxForgotPasswd', '10'); ?>>10</option>
                                        <option value="20"<?php $w->sel('loginSec_maxForgotPasswd', '20'); ?>>20</option>
                                        <option value="30"<?php $w->sel('loginSec_maxForgotPasswd', '30'); ?>>30</option>
                                        <option value="40"<?php $w->sel('loginSec_maxForgotPasswd', '40'); ?>>40</option>
                                        <option value="50"<?php $w->sel('loginSec_maxForgotPasswd', '50'); ?>>50</option>
                                        <option value="100"<?php $w->sel('loginSec_maxForgotPasswd', '100'); ?>>100</option>
                                        <option value="200"<?php $w->sel('loginSec_maxForgotPasswd', '200'); ?>>200</option>
                                        <option value="500"<?php $w->sel('loginSec_maxForgotPasswd', '500'); ?>>500</option>
                                </select>
                                </td></tr>
                        <tr><th>Count failures over what time period</th><td>
                                <select id="loginSec_countFailMins" class="wfConfigElem" name="loginSec_countFailMins">
                                        <option value="5"<?php $w->sel('loginSec_countFailMins', '5'); ?>>5 minutes</option>
                                        <option value="10"<?php $w->sel('loginSec_countFailMins', '10'); ?>>10 minutes</option>
                                        <option value="30"<?php $w->sel('loginSec_countFailMins', '30'); ?>>30 minutes</option>
                                        <option value="60"<?php $w->sel('loginSec_countFailMins', '60'); ?>>1 hour</option>
                                        <option value="120"<?php $w->sel('loginSec_countFailMins', '120'); ?>>2 hours</option>
                                        <option value="360"<?php $w->sel('loginSec_countFailMins', '360'); ?>>6 hours</option>
                                        <option value="720"<?php $w->sel('loginSec_countFailMins', '720'); ?>>12 hours</option>
                                        <option value="1440"<?php $w->sel('loginSec_countFailMins', '1440'); ?>>1 day</option>
                                </select>	
                                </td></tr>
                        <tr><th>Amount of time a user is locked out</th><td>
                                <select id="loginSec_lockoutMins" class="wfConfigElem" name="loginSec_lockoutMins">
                                        <option value="5"<?php $w->sel('loginSec_lockoutMins', '5'); ?>>5 minutes</option>
                                        <option value="10"<?php $w->sel('loginSec_lockoutMins', '10'); ?>>10 minutes</option>
                                        <option value="30"<?php $w->sel('loginSec_lockoutMins', '30'); ?>>30 minutes</option>
                                        <option value="60"<?php $w->sel('loginSec_lockoutMins', '60'); ?>>1 hour</option>
                                        <option value="120"<?php $w->sel('loginSec_lockoutMins', '120'); ?>>2 hours</option>
                                        <option value="360"<?php $w->sel('loginSec_lockoutMins', '360'); ?>>6 hours</option>
                                        <option value="720"<?php $w->sel('loginSec_lockoutMins', '720'); ?>>12 hours</option>
                                        <option value="1440"<?php $w->sel('loginSec_lockoutMins', '1440'); ?>>1 day</option>
                                        <option value="2880"<?php $w->sel('loginSec_lockoutMins', '2880'); ?>>2 days</option>
                                        <option value="7200"<?php $w->sel('loginSec_lockoutMins', '7200'); ?>>5 days</option>
                                        <option value="14400"<?php $w->sel('loginSec_lockoutMins', '14400'); ?>>10 days</option>
                                        <option value="28800"<?php $w->sel('loginSec_lockoutMins', '28800'); ?>>20 days</option>
                                        <option value="43200"<?php $w->sel('loginSec_lockoutMins', '43200'); ?>>30 days</option>
                                        <option value="86400"<?php $w->sel('loginSec_lockoutMins', '86400'); ?>>60 days</option>
                                </select>	
                                </td></tr>
                        <tr><th>Immediately lock out invalid usernames</th><td><input type="checkbox" id="loginSec_lockInvalidUsers" class="wfConfigElem" name="loginSec_lockInvalidUsers" <?php $w->cb('loginSec_lockInvalidUsers'); ?> /></td></tr>
                        <tr><th>Don't let WordPress reveal valid users in login errors</th><td><input type="checkbox" id="loginSec_maskLoginErrors" class="wfConfigElem" name="loginSec_maskLoginErrors" <?php $w->cb('loginSec_maskLoginErrors'); ?> /></td></tr>
                        <tr><th>Prevent users registering 'admin' username if it doesn't exist</th><td><input type="checkbox" id="loginSec_blockAdminReg" class="wfConfigElem" name="loginSec_blockAdminReg" <?php $w->cb('loginSec_blockAdminReg'); ?> /></td></tr>
                        <tr><th>Prevent discovery of usernames through '?/author=N' scans</th><td><input type="checkbox" id="loginSec_disableAuthorScan" class="wfConfigElem" name="loginSec_disableAuthorScan" <?php $w->cb('loginSec_disableAuthorScan'); ?> /></td></tr>
                        <tr><th>Immediately block the IP of users who try to sign in as these usernames</th><td><input type="text" name="loginSec_userBlacklist" id="loginSec_userBlacklist" value="<?php echo $w->getHTML('loginSec_userBlacklist'); ?>" size="40" />&nbsp;(Comma separated. Existing users won't be blocked.)</td></tr>
                        </tbody>
                </table>  
            </div>
        </div>
        <?php
        $dashboard_ip = $_SERVER['SERVER_ADDR'];
        $your_ip = $_SERVER['REMOTE_ADDR'];
        $white_list = $w->getHTML('whitelisted');
        
        if (empty($white_list))
            $white_list = $dashboard_ip;
        else {
            if (strpos($white_list, $dashboard_ip) === false) {

               $white_list = $dashboard_ip ."," . $white_list;
            }
        }
        
        ?>
        <div class="postbox mainwp_wfc_postbox">   
            <div class="handlediv"><br /></div>           
            <h3 class="mainwp_box_title" ><span><?php echo $edit_site ? __("Wordfence Other Options", "mainwp") : __("Other Options", "mainwp"); ?></span></h3>    
            <div class="inside">                
                 <table>
                    <tbody> 
                        <tr><th>Whitelisted IP addresses that bypass all rules:</th><td><input type="text" name="whitelisted" id="whitelisted" value="<?php echo $white_list; ?>" size="40" /></td></tr>
                        <tr><th>&nbsp;</th>
                            <td>We recommend whitelisting your Dashboard and your IP before making changes<br>
                                Dashboard IP: <?php echo $dashboard_ip; ?><br/>
                                Your IP: <?php echo $your_ip; ?><br/>
                            </td>
                        </tr>
                        <tr><th colspan="2" style="color: #999;">Whitelisted IP's must be separated by commas. You can specify ranges using the following format: 123.23.34.[1-50]<br />Wordfence automatically whitelists <a href="http://en.wikipedia.org/wiki/Private_network" target="_blank">private networks</a> because these are not routable on the public Internet.<br /><br /></th></tr>                        
                        <tr><th>Immediately block IP's that access these URLs:</th><td><input type="text" name="bannedURLs" id="bannedURLs" value="<?php echo $w->getHTML('bannedURLs'); ?>" size="40" /></td></tr>
                        <tr><th colspan="2" style="color: #999;">Separate multiple URL's with commas. If you see an attacker repeatedly probing your site for a known vulnerability you can use this to immediately block them.<br /><br /></th></tr>

                        <tr><th>Hide WordPress version</th><td><input type="checkbox" id="other_hideWPVersion" class="wfConfigElem" name="other_hideWPVersion" value="1" <?php $w->cb('other_hideWPVersion'); ?> /></td></tr>
                        <tr><th>Hold anonymous comments using member emails for moderation</th><td><input type="checkbox" id="other_noAnonMemberComments" class="wfConfigElem" name="other_noAnonMemberComments" value="1" <?php $w->cb('other_noAnonMemberComments'); ?> /></td></tr>
                        <tr><th>Filter comments for malware and phishing URL's</th><td><input type="checkbox" id="other_scanComments" class="wfConfigElem" name="other_scanComments" value="1" <?php $w->cb('other_scanComments'); ?> /></td></tr>
                        <tr><th>Check password strength on profile update</th><td><input type="checkbox" id="other_pwStrengthOnUpdate" class="wfConfigElem" name="other_pwStrengthOnUpdate" value="1" <?php $w->cb('other_pwStrengthOnUpdate'); ?> /></td></tr>
                        <tr><th>Participate in the Real-Time WordPress Security Network</th><td><input type="checkbox" id="other_WFNet" class="wfConfigElem" name="other_WFNet" value="1" <?php $w->cb('other_WFNet'); ?> /></td></tr>
                        <tr><th>Maximum memory Wordfence can use</th><td><input type="text" id="maxMem" name="maxMem" value="<?php $w->f('maxMem'); ?>" size="4" />Megabytes</td></tr>
                        <tr><th>Maximum execution time for each scan stage</th><td><input type="text" id="maxExecutionTime" name="maxExecutionTime" value="<?php $w->f('maxExecutionTime'); ?>" size="4" />Blank for default. Must be greater than 9.</td></tr>
                        <tr><th>Update interval in seconds (2 is default)</th><td><input type="text" id="actUpdateInterval" name="actUpdateInterval" value="<?php $w->f('actUpdateInterval'); ?>" size="4" />Setting higher will reduce browser traffic but slow scan starts, live traffic &amp; status updates.</td></tr>
                        <tr><th>Enable debugging mode (increases database load)</th><td><input type="checkbox" id="debugOn" class="wfConfigElem" name="debugOn" value="1" <?php $w->cb('debugOn'); ?> /></td></tr>
                        <tr><th>Delete Wordfence tables and data on deactivation?</th><td><input type="checkbox" id="deleteTablesOnDeact" class="wfConfigElem" name="deleteTablesOnDeact" value="1" <?php $w->cb('deleteTablesOnDeact'); ?> /></td></tr>
                        <tr><th>Disable Wordfence Cookies</th><td><input type="checkbox" id="disableCookies" class="wfConfigElem" name="disableCookies" value="1" <?php $w->cb('disableCookies'); ?> />(when enabled all visits in live traffic will appear to be new visits)</td></tr>
                        <tr><th>Start all scans remotely</th><td><input type="checkbox" id="startScansRemotely" class="wfConfigElem" name="startScansRemotely" value="1" <?php $w->cb('startScansRemotely'); ?> />(Try this if your scans aren't starting and your site is publicly accessible)</td></tr>
                        <tr><th>Disable config caching</th><td><input type="checkbox" id="disableConfigCaching" class="wfConfigElem" name="disableConfigCaching" value="1" <?php $w->cb('disableConfigCaching'); ?> />(Try this if your options aren't saving)</td></tr>
                        <tr><th>Add a debugging comment to HTML source of cached pages.</th><td><input type="checkbox" id="addCacheComment" class="wfConfigElem" name="addCacheComment" value="1" <?php $w->cb('addCacheComment'); ?> /></td></tr>
                    </tbody>
                </table>  
            </div>
        </div>
        
        <?php if (!$edit_site) { ?>
        <div>                                    
            <input type="button" onclick="MWP_WFAD.saveConfig();" class="button-primary" value="<?php _e("Save Settings", "mainwp"); ?>">                                                        
            <img class="wfcSaveOpts" style="display: none" src="<?php echo $url_loader; ?>"><span class="wfSavedMsg">&nbsp;Your changes have been saved!</span>
        </div>
        <?php } ?>
        </div>
    <?php
        self::gen_template_scripts();
    }     
   
    static function gen_template_scripts() {
        ?>
        <script type="text/x-jquery-template" id="wfContentBasicOptions">
        <div>
        <h3>Basic Options</h3>
        <p>
                Using Wordfence is simple. Install Wordfence, enter an email address on this page to send alerts to, and then do your first scan and work through the security alerts we provide.
                We give you a few basic security levels to choose from, depending on your needs. Remember to hit the "Save" button to save any changes you make. 
        </p>
        <p>
                If you use the free edition of Wordfence, you don't need to worry about entering an API key in the "API Key" field above. One is automatically created for you. If you choose to <a href="https://www.wordfence.com/wordfence-signup/" target="_blank">upgrade to Wordfence Premium edition</a>, you will receive an API key. You will need to copy and paste that key into the "API Key"
                field above and hit "Save" to activate your key.
        </p>
        </div>
        </script>       
        <script type="text/x-jquery-template" id="wfContentScansToInclude">
        <div>
        <h3>Scans to Include</h3>
        <p>
                This section gives you the ability to fine-tune what we scan. 
                If you use many themes or plugins from the public WordPress directory we recommend you 
                enable theme and plugin scanning. This will verify the integrity of all these themes and plugins and alert you of any changes.
        <p>
        <p>
                The option to "scan files outside your WordPress installation" will cause Wordfence to do a much wider security scan
                that is not limited to your base WordPress directory and known WordPress subdirectories. This scan may take longer
                but can be very useful if you have other infected files outside this WordPress installation that you would like us to look for.
        </p>
        </div>
        </script>
        
        <script type="text/x-jquery-template" id="wfContentLoginSecurity">
        <div>
        <h3>Login Security</h3>
        <p>
                We have found that real brute force login attacks make hundreds or thousands of requests trying to guess passwords or user login names. 
                So in general you can leave the number of failed logins before a user is locked out as a fairly high number.
                We have found that blocking after 20 failed attempts is sufficient for most sites and it allows your real site users enough
                attempts to guess their forgotten passwords without getting locked out.
        </p>
        </div>
        </script>                
        <?php
    }
}
