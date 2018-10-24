<?php
class MainWPWordfencePlugin
{    
    private $option_handle = 'mainwp_wordfence_plugin_option';
    private $option = array();
   
    private static $order = "";
    private static $orderby = "";
    
    //Singleton
    private static $instance = null;    
    static function Instance()
    {
        if (MainWPWordfencePlugin::$instance == null) {
            MainWPWordfencePlugin::$instance = new MainWPWordfencePlugin();
        }
        return MainWPWordfencePlugin::$instance;
    }
    
    public function __construct() {
        $this->option = get_option($this->option_handle);
    }
    
    public function admin_init() {       
        add_action('wp_ajax_mainwp_wfc_upgrade_noti_dismiss', array($this,'dismissNoti'));
        add_action('wp_ajax_mainwp_wfc_active_plugin', array($this,'active_plugin'));
        add_action('wp_ajax_mainwp_wfc_upgrade_plugin', array($this,'upgrade_plugin')); 
        add_action('wp_ajax_mainwp_wfc_showhide_plugin', array($this,'showhide_plugin')); 
        add_action('wp_ajax_mainwp_wfc_scan_now', array($this,'ajax_scan_now')); 
    }
    
    public function get_option($key = null, $default = '') {
        if (isset($this->option[$key]))
            return $this->option[$key];
        return $default;
    }
    
    public function set_option($key, $value) {
        $this->option[$key] = $value;
        return update_option($this->option_handle, $this->option);
    }
    
    public static function gen_plugin_dashboard_tab($websites) {

       $orderby = "name";    
       $_order = "desc";
       if (isset($_GET['wfc_orderby']) && !empty($_GET['wfc_orderby'])) {            
           $orderby = $_GET['wfc_orderby'];
       }    
       if (isset($_GET['wfc_order']) && !empty($_GET['wfc_order'])) {            
           $_order = $_GET['wfc_order'];
       }        

       $name_order = $version_order = $status_order = $last_scan_order = $time_order = $url_order = $hidden_order = "";     
       
       if (isset($_GET['wfc_orderby'])) {
            if ($_GET['wfc_orderby'] == "name") {            
                $name_order = ($_order == "desc") ? "asc" : "desc";                     
            } else if ($_GET['wfc_orderby'] == "version") {            
                $version_order = ($_order == "desc") ? "asc" : "desc";                     
            } else if ($_GET['wfc_orderby'] == "lastscan") {
                $last_scan_order = ($_order == "desc") ? "asc" : "desc";                     
            } else if ($_GET['wfc_orderby'] == "time") {
                $time_order = ($_order == "desc") ? "asc" : "desc";                     
            } else if ($_GET['wfc_orderby'] == "url") {
                $url_order = ($_order == "desc") ? "asc" : "desc";                     
            } else if ($_GET['wfc_orderby'] == "hidden") {
                $hidden_order = ($_order == "desc") ? "asc" : "desc";                     
            } else if ($_GET['wfc_orderby'] == "status") {
                $status_order = ($_order == "desc") ? "asc" : "desc";                     
            } 
       }
       
       
 
       self::$order = $_order;
       self::$orderby = $orderby;        
       usort($websites, array('MainWPWordfencePlugin', "wordfence_data_sort"));          
   ?>
       <table id="mainwp-table-plugins" class="wp-list-table widefat plugins" cellspacing="0">
         <thead>
         <tr>
           <th class="check-column">
               <input type="checkbox"  id="cb-select-all-1" >
           </th>
           <th scope="col" class="manage-column sortable <?php echo $name_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=name&wfc_order=<?php echo (empty($name_order) ? 'asc' : $name_order); ?>"><span><?php _e('Site','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column sortable <?php echo $url_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=url&wfc_order=<?php echo (empty($url_order) ? 'asc' : $url_order); ?>"><span><?php _e('URL','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column">
               <span><?php _e('Trigger','mainwp'); ?></span>
           </th>
           <th scope="col" class="manage-column sortable <?php echo $last_scan_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=lastscan&wfc_order=<?php echo (empty($last_scan_order) ? 'asc' : $last_scan_order); ?>"><span><?php _e('Last Scan','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column sortable <?php echo $status_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=status&wfc_order=<?php echo (empty($status_order) ? 'asc' : $status_order); ?>"><span><?php _e('Status','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column sortable <?php echo $version_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=version&wfc_order=<?php echo (empty($version_order) ? 'asc' : $version_order); ?>"><span><?php _e('Plugin Version','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column <?php echo $hidden_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=hidden&wfc_order=<?php echo (empty($hidden_order) ? 'asc' : $hidden_order); ?>"><span><?php _e('Plugin Hidden','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
         </tr>
         </thead>
         <tfoot>
         <tr>
           <th class="check-column">
               <input type="checkbox"  id="cb-select-all-2" >
           </th>
           <th scope="col" class="manage-column sortable <?php echo $name_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=name&wfc_order=<?php echo (empty($name_order) ? 'asc' : $name_order); ?>"><span><?php _e('Site','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column sortable <?php echo $url_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=url&wfc_order=<?php echo (empty($url_order) ? 'asc' : $url_order); ?>"><span><?php _e('URL','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column">
               <span><?php _e('Trigger','mainwp'); ?></span>
           </th>
            <th scope="col" class="manage-column sortable <?php echo $last_scan_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=lastscan&wfc_order=<?php echo (empty($last_scan_order) ? 'asc' : $last_scan_order); ?>"><span><?php _e('Last Scan','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>     
           <th scope="col" class="manage-column sortable <?php echo $status_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=status&wfc_order=<?php echo (empty($status_order) ? 'asc' : $status_order); ?>"><span><?php _e('Status','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
           <th scope="col" class="manage-column sortable <?php echo $version_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=version&wfc_order=<?php echo (empty($version_order) ? 'asc' : $version_order); ?>"><span><?php _e('Plugin Version','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>     
            <th scope="col" class="manage-column <?php echo $hidden_order; ?>">
               <a href="?page=Extensions-Mainwp-Wordfence-Extension&wfc_orderby=hidden&wfc_order=<?php echo (empty($hidden_order) ? 'asc' : $hidden_order); ?>"><span><?php _e('Plugin Hidden','mainwp'); ?></span><span class="sorting-indicator"></span></a>
           </th>
         </tr>
         </tfoot>
           <tbody id="the-mwp-wordfence-list" class="list:sites">
            <?php 
            if (is_array($websites) && count($websites) > 0) {                
               self::getPluginDashboardTableRow($websites);                  
            } else {
               _e("<tr><td colspan=\"8\">No websites were found with the Wordfence plugin installed.</td></tr>");
            }
            ?>
           </tbody>
     </table>
   <?php
   }

    public static function getPluginDashboardTableRow($websites) {   
       $dismiss = array();
       if (session_id() == '') session_start();        
       if (isset($_SESSION['mainwp_wfc_dismiss_upgrade_plugin_notis'])) {
           $dismiss = $_SESSION['mainwp_wfc_dismiss_upgrade_plugin_notis'];
       }                

       if (!is_array($dismiss))
           $dismiss = array();       
       
       $url_loader = plugins_url('images/loader.gif', dirname(__FILE__));
       
       foreach ($websites as $website) {
           $location = "admin.php?page=Wordfence";             
           $website_id = $website['id'];
           $lastscan = isset($website['lastscan']) ? $website['lastscan'] : 0;    
           $status = isset($website['status']) ? $website['status'] : 0;    
           $cls_active = (isset($website['wordfence_active']) && !empty($website['wordfence_active'])) ? "active" : "inactive";
           $cls_update = (isset($website['wordfence_upgrade'])) ? "update" : "";
           $cls_update = ($cls_active == "inactive") ? "update" : $cls_update;
           $showhide_action = ($website['hide_wordfence'] == 1) ? 'show' : 'hide';
           $showhide_link = '<a href="#" class="mwp_wfc_showhide_plugin" showhide="' . $showhide_action . '">'. ($showhide_action === "show" ? __('Show Wordfence plugin') : __('Hide Wordfence plugin')) . '</a>';
           
           $td_status = "";
           if (empty($status)) {
                $scr = 'images/wf-no.png';
                $td_status = '<span style="text-align: center;display: block"><img src="' . MainWPWordfenceExtension::$plugin_url  . $scr . '"></span>';
           } else {
               if ($status == 1) {
                   $scr = 'images/wf-ok.png';
               } else {
                   $scr = 'images/wf-issues.png';
               }  
               $td_status = '<span style="text-align: center;display: block"><a style="display: inline-block" href="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=result&site_id=' . $website_id . '"><img src="' . MainWPWordfenceExtension::$plugin_url  . $scr . '"></a></span>';
           }

           ?>
           <tr class="<?php echo $cls_active . " " . $cls_update; ?>" website-id="<?php echo $website_id; ?>">
               <th class="check-column">
                   <input type="checkbox"  name="checked[]">
               </th>
               <td>
                   <a href="admin.php?page=managesites&dashboard=<?php echo $website_id; ?>"><?php echo $website['name']; ?></a><br/>
                   <div class="row-actions"><span class="dashboard"><a href="admin.php?page=managesites&dashboard=<?php echo $website_id; ?>"><?php _e("Dashboard");?></a></span> |  <span class="edit"><a href="admin.php?page=managesites&id=<?php echo $website_id; ?>"><?php _e("Edit");?></a> | <?php echo $showhide_link; ?></span></div>                    
                   <div class="wfc-action-working"><span class="status" style="display:none;"></span><span class="loading" style="display:none;"><img src="<?php echo $url_loader; ?>"> <?php _e("Please wait..."); ?></span></div>
               </td>               
               <td>
                   <a href="<?php echo $website['url']; ?>" target="_blank"><?php echo $website['url']; ?></a><br/>
                   <div class="row-actions"><span class="edit"><a target="_blank" href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php echo $website_id; ?>"><?php _e("Open WP-Admin");?></a></span> | <span class="edit"><a href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php echo $website_id; ?>&location=<?php echo base64_encode($location); ?>" target="_blank"><?php _e("Open Wordfence");?></a></span></div>                    
               </td>
               <td>
                   <a href="#" class="mwp_wfc_scan_now_lnk"><?php echo __("Scan Now", "mainwp"); ?></a></span> |                
                   <a href="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=result&site_id=<?php echo $website_id; ?>" ><?php echo __("Show results", "mainwp"); ?></a></span> | 
                   <a href="admin.php?page=Extensions-Mainwp-Wordfence-Extension&action=traffic&site_id=<?php echo $website_id; ?>" ><?php echo __("Live Traffic", "mainwp"); ?></a></span>
                   <div class="wfc-scan-working"><span class="loading hidden-field"><img src="<?php echo $url_loader; ?>"> <?php _e("Running ...", "mainwp"); ?></span><span class="status hidden-field"></span></div>
               </td>
               <td>
                   <?php echo !empty($lastscan) ? MainWPWordfenceUtility::formatTimestamp($lastscan) : ""; ?>
               </td>
                <td>
                   <?php echo $td_status; ?>
               </td>
               <td>
               <?php 
                   if (isset($website['wordfence_plugin_version']))
                       echo $website['wordfence_plugin_version'];
                   else 
                       echo "&nbsp;";
               ?>
               </td>     
               <td>
                   <span class="wordfence_hidden_title"><?php 
                        echo ($website['hide_wordfence'] == 1) ? __("Yes") : __("No"); 
                   ?>
                </span>
               </td>
           </tr>        
            <?php    
           if (!isset($dismiss[$website_id])) {  
               $active_link = $update_link = "";    
               $version = ""; 
               $plugin_slug = "wordfence/wordfence.php";
               if (isset($website['wordfence_active']) && empty($website['wordfence_active']))
                   $active_link = '<a href="#" class="mwp_wfc_active_plugin" >' . __('Activate Wordfence plugin') . '</a>';


               if (isset($website['wordfence_upgrade'])) { 
                   if (isset($website['wordfence_upgrade']['new_version']))
                       $version = $website['wordfence_upgrade']['new_version'];
                   $update_link = '<a href="#" class="mwp_wfc_upgrade_plugin" >' . __('Update Wordfence plugin'). '</a>';
                   if (isset($website['wordfence_upgrade']['plugin']))
                       $plugin_slug = $website['wordfence_upgrade']['plugin'];
               }

               if (!empty($active_link) || !empty($update_link)) {
                   $location = "plugins.php";                    
                   $link_row = $active_link .  ' | ' . $update_link;
                   $link_row = rtrim($link_row, ' | ');
                   $link_row = ltrim($link_row, ' | ');                    
                   ?>
                   <tr class="plugin-update-tr">
                       <td colspan="8" class="plugin-update">
                           <div class="ext-upgrade-noti update-message" plugin-slug="<?php echo $plugin_slug; ?>" website-id="<?php echo $website_id; ?>" version="<?php echo $version; ?>">
                               <span style="float:right"><a href="#" class="wfc_plugin_upgrade_noti_dismiss"><?php _e("Dismiss"); ?></a></span>                    
                               <?php echo $link_row; ?>
                               <span class="mwp-wfc-row-working"><span class="status"></span><img class="hidden-field" src="<?php echo plugins_url('images/loader.gif', dirname(__FILE__)); ?>"/></span>
                           </div>
                       </td>
                   </tr>
                   <?php  
               }
           }                
       }
   }

    public static function wordfence_data_sort($a, $b) {        
        if (self::$orderby == "version") {
            $a = $a['wordfence_plugin_version'];
            $b = $b['wordfence_plugin_version'];
            $cmp = version_compare($a, $b);            
        } else if (self::$orderby == "url"){
            $a = $a['url'];
            $b = $b['url'];   
            $cmp = strcmp($a, $b); 
        } else if (self::$orderby == "hidden"){
            $a = $a['hide_wordfence'];
            $b = $b['hide_wordfence'];   
            $cmp = $a - $b; 
        } else if (self::$orderby == "lastscan"){
            $a = $a['lastscan'];
            $b = $b['lastscan'];   
            $cmp = $a - $b; 
        } else if (self::$orderby == "status"){
            $a = $a['status'];
            $b = $b['status'];   
            $cmp = $a - $b; 
        } else {
            $a = $a['name'];
            $b = $b['name'];   
            $cmp = strcmp($a, $b); 
        }     
        if ($cmp == 0)
            return 0;
        
        if (self::$order == 'desc')
            return ($cmp > 0) ? -1 : 1;
        else 
            return ($cmp > 0) ? 1 : -1;                        
    }

    public function get_websites_with_the_plugin($websites, $selected_group = 0) {                       
        $websites_wordfence = array();        
        
        $wordfenceHide = $this->get_option('hide_the_plugin');
        
        if (!is_array($wordfenceHide))
            $wordfenceHide = array();
        
        if (is_array($websites) && count($websites)) {
            if (empty($selected_group)) {            
                foreach($websites as $website) {
                    if ($website && $website->plugins != '')  { 
                        $settings = MainWPWordfenceDB::Instance()->getSettingBy('site_id', $website->id);
                        $plugins = json_decode($website->plugins, 1);                           
                        if (is_array($plugins) && count($plugins) != 0) {                            
                            foreach ($plugins as $plugin)
                            {                            
                                if ($plugin['slug'] == "wordfence/wordfence.php") {                                    
                                    $site = MainWPWordfenceUtility::mapSite($website, array('id', 'name' , 'url'));
                                    if ($plugin['active'])
                                        $site['wordfence_active'] = 1;
                                    else 
                                        $site['wordfence_active'] = 0;     
                                    // get upgrade info
                                    $site['wordfence_plugin_version'] = $plugin['version'];
                                    $plugin_upgrades = json_decode($website->plugin_upgrades, 1);                                     
                                    if (is_array($plugin_upgrades) && count($plugin_upgrades) > 0) {                                        
                                        if (isset($plugin_upgrades["wordfence/wordfence.php"])) {
                                            $upgrade = $plugin_upgrades["wordfence/wordfence.php"];
                                            if (isset($upgrade['update'])) {                                                
                                                $site['wordfence_upgrade'] = $upgrade['update'];                                                
                                            }
                                        }
                                    }
                                    
                                    $site['hide_wordfence'] = 0;
                                    $site['lastscan'] = $settings->lastscan;
                                    $site['status'] = $settings->status;
                                    if (isset($wordfenceHide[$website->id]) && $wordfenceHide[$website->id]) {
                                        $site['hide_wordfence'] = 1;
                                    }                                    
                                    $websites_wordfence[] = $site;                                    
                                    break;                                    
                                }
                            }
                        }
                    }
                }            
            } else {
                global $mainWPWordfenceExtensionActivator;
                
                $group_websites = apply_filters('mainwp-getdbsites', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), array(), array($selected_group));  
                $sites = array();
                foreach($group_websites as $site) {
                    $sites[] = $site->id;
                }                 
                foreach($websites as $website) {
                    if ($website && $website->plugins != '' && in_array($website->id, $sites))  { 
                        $plugins = json_decode($website->plugins, 1);                       
                        if (is_array($plugins) && count($plugins) != 0) {
                            foreach ($plugins as $plugin)
                            {                            
                                if ($plugin['slug'] == "wordfence/wordfence.php") {
                                    $site = MainWPWordfenceUtility::mapSite($website, array('id', 'name' , 'url'));
                                    if ($plugin['active'])
                                        $site['wordfence_active'] = 1;
                                    else 
                                        $site['wordfence_active'] = 0;     
                                    $site['wordfence_plugin_version'] = $plugin['version'];
                                    
                                    // get upgrade info
                                    $plugin_upgrades = json_decode($website->plugin_upgrades, 1); 
                                    if (is_array($plugin_upgrades) && count($plugin_upgrades) > 0) {                                        
                                        if (isset($plugin_upgrades["wordfence/wordfence.php"])) {
                                            $upgrade = $plugin_upgrades["wordfence/wordfence.php"];
                                            if (isset($upgrade['update'])) {                                                
                                                $site['wordfence_upgrade'] = $upgrade['update'];                                                
                                            }
                                        }
                                    }                                    
                                    $site['hide_wordfence'] = 0;
                                    if (isset($wordfenceHide[$website->id]) && $wordfenceHide[$website->id]) {
                                        $site['hide_wordfence'] = 1;
                                    }     
                                    $site['lastscan'] = $settings->lastscan;
                                    $site['status'] = $settings->status;
                                    $websites_wordfence[] = $site;
                                    break;
                                }
                            }
                        }
                    }
                }   
            }
        } 
        
        // if search action
        $search_sites = array();               
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $find = trim($_GET['s']);
            foreach($websites_wordfence as $website ) {                
                if (stripos($website['name'], $find) !== false || stripos($website['url'], $find) !== false) {
                    $search_sites[] = $website;
                }
            }
            $websites_wordfence = $search_sites;
        }
        unset($search_sites);        
       
        return $websites_wordfence;
    } 
          
    public static function gen_select_sites($websites, $selected_group) {
        global $mainWPWordfenceExtensionActivator;
        //$websites = apply_filters('mainwp-getsites', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), null);              
        $groups = apply_filters('mainwp-getgroups', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), null);        
        $search = (isset($_GET['s']) && !empty($_GET['s'])) ? trim($_GET['s']) : "";
        ?> 
                   
        <div class="alignleft actions bulkactions">
            <select id="mwp_wfc_plugin_action">
                <option selected="selected" value="-1"><?php _e("Bulk Actions"); ?></option>
                <option value="activate-selected"><?php _e("Active"); ?></option>
                <option value="update-selected"><?php _e("Update"); ?></option>
                <option value="hide-selected"><?php _e("Hide"); ?></option>
                <option value="show-selected"><?php _e("Show"); ?></option>
            </select>
            <input type="button" value="<?php _e("Apply"); ?>" class="button action" id="wfc_plugin_doaction_btn" name="">
        </div>
                   
        <div class="alignleft actions">
            <form action="" method="GET">
                <input type="hidden" name="page" value="Extensions-Mainwp-Wordfence-Extension">
                <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"><?php _e('No search results.','mainwp'); ?></span>
                <input type="text" class="mainwp_autocomplete ui-autocomplete-input" name="s" autocompletelist="sites" value="<?php echo stripslashes($search); ?>" autocomplete="off">
                <datalist id="sites">
                    <?php
                    if (is_array($websites) && count($websites) > 0) {
                        foreach ($websites as $website) {                    
                            echo "<option>" . $website['name']. "</option>";                    
                        }
                    }
                    ?>                
                </datalist>
                <input type="submit" name="" class="button" value="Search Sites">
            </form>
        </div>    
        <div class="alignleft actions">
            <form method="post" action="admin.php?page=Extensions-Mainwp-Wordfence-Extension">
                <select name="mainwp_wfc_plugin_groups_select">
                <option value="0"><?php _e("Select a group"); ?></option>
                <?php
                if (is_array($groups) && count($groups) > 0) {
                    foreach ($groups as $group) {
                        $_select = "";
                        if ($selected_group == $group['id'])
                            $_select = 'selected ';                    
                        echo '<option value="' . $group['id'] . '" ' . $_select . '>' . $group['name'] . '</option>';
                    }     
                }
                ?>
                </select>&nbsp;&nbsp;                     
                <input class="button" type="submit" name="wfc_plugin_btn_display" id="wfc_plugin_btn_display"value="<?php _e("Display", "mainwp"); ?>">
            </form>  
        </div>    
        <?php       
        return;
    }
    
        
    public function dismissNoti() {
        $website_id = $_POST['siteId'];
        $version = $_POST['new_version'];
        if ($website_id) {    
            session_start();
            $dismiss = $_SESSION['mainwp_wfc_dismiss_upgrade_plugin_notis'];
            if (is_array($dismiss) && count($dismiss) > 0) {
                $dismiss[$website_id] = 1;
            } else {
                $dismiss = array();
                $dismiss[$website_id] = 1;
            }
            $_SESSION['mainwp_wfc_dismiss_upgrade_plugin_notis'] = $dismiss;
            die('updated');
        }
        die('nochange');
    }
    
    public function active_plugin() {
        do_action('mainwp_activePlugin');
        die();
    }
    
    public function upgrade_plugin() {
        do_action('mainwp_upgradePluginTheme');
        die();
    }
    
    public function showhide_plugin() {
        $siteid = isset($_POST['websiteId']) ? $_POST['websiteId'] : null;
        $showhide = isset($_POST['showhide']) ? $_POST['showhide'] : null;
        if ($siteid !== null && $showhide !== null) {            
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'set_showhide',
                                'showhide' => $showhide
                            );
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            
            if (is_array($information) && isset($information['result']) && $information['result'] === "SUCCESS") {
                $hide_wordfence = $this->get_option('hide_the_plugin');
                if (!is_array($hide_wordfence))
                    $hide_wordfence = array();
                $hide_wordfence[$siteid] = ($showhide === "hide") ? 1 : 0;
                $this->set_option('hide_the_plugin', $hide_wordfence);
            }            
            die(json_encode($information)); 
        }
        die();
    }
    
    function ajax_scan_now() {
        $siteid = $_POST['siteId'];
        if (!empty($siteid)) {            
            global $mainWPWordfenceExtensionActivator;
            $post_data = array( 'mwp_action' => 'start_scan');
            $information = apply_filters('mainwp_fetchurlauthed', $mainWPWordfenceExtensionActivator->getChildFile(), $mainWPWordfenceExtensionActivator->getChildKey(), $siteid, 'wordfence', $post_data);			
            if ((isset($information['result']) && $information['result'] == "SUCCESS") || 
                (isset($information['error']) && $information['error'] == "SCAN_RUNNING")) {                
                $update = array('site_id' => $siteid,
                                'lastscan' => time());                            
                MainWPWordfenceDB::Instance()->updateSetting($update);            
            }
            die(json_encode($information)); 
        }
        die();
    }
}
