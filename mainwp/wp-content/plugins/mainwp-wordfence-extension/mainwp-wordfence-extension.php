<?php
/*
Plugin Name: MainWP Wordfence Extension
Plugin URI: http://extensions.mainwp.com
Description: The WordFence Extension combines the power of your MainWP Dashboard with the popular WordPress Wordfence Plugin. It allows you to manage WordFence settings, Monitor Live Traffic and Scan your child sites directly from your dashboard. Requires MainWP Dashboard plugin.
Version: 0.0.7
Author: MainWP
Author URI: http://mainwp.com 
Support Forum URI: https://mainwp.com/forum/forumdisplay.php?108-Wordfence-Extension
Documentation URI: http://docs.mainwp.com/category/mainwp-extensions/mainwp-wordfence-extension/
Icon URI: http://extensions.mainwp.com/wp-content/uploads/2014/09/mainwp-wordfence-icon.png
*/
if (!defined('MAINWP_WORDFENCE_EXT_PLUGIN_FILE')) {
    define('MAINWP_WORDFENCE_EXT_PLUGIN_FILE', __FILE__);
}


class MainWPWordfenceExtension
{
    public static $instance = null;   
    public static $plugin_url;
    public $plugin_slug;
    public static $plugin_dir;    
    protected $option;    
    protected $option_handle = 'mainwp_wordfence_extension';    
    
    static function Instance()
    {
        if (MainWPWordfenceExtension::$instance == null) MainWPWordfenceExtension::$instance = new MainWPWordfenceExtension();
        return MainWPWordfenceExtension::$instance;
    }

    public function __construct()
    {
        self::$plugin_dir = plugin_dir_path(__FILE__);
        self::$plugin_url = plugin_dir_url(__FILE__);
        $this->plugin_slug = plugin_basename(__FILE__);
        $this->option = get_option($this->option_handle);
        
        add_action('init', array(&$this, 'init'));
        add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);
        add_action('admin_init', array(&$this, 'admin_init'));        
        MainWPWordfenceDB::Instance()->install();                
    }

    public function init()
    {
        
    }
 
    public function plugin_row_meta($plugin_meta, $plugin_file)
    {
        if ($this->plugin_slug != $plugin_file) return $plugin_meta;

        $plugin_meta[] = '<a href="?do=checkUpgrade" title="Check for updates.">Check for updates now</a>';
        return $plugin_meta;
    }

    public function admin_init()
    {
        wp_enqueue_style('mainwp-wordfence-extension', self::$plugin_url . 'css/mainwp-wordfence.css');
        wp_enqueue_script('mainwp-wordfence-extension', self::$plugin_url . 'js/mainwp-wordfence.js');        

        
        if (isset($_GET['page']) && ($_GET['page'] == "Extensions-Mainwp-Wordfence-Extension" || ($_GET['page'] == "managesites"))) {
            wp_enqueue_style('mainwp-wordfence-extension-colorbox-style', self::$plugin_url . 'css/colorbox.css');
            wp_enqueue_style('mainwp-wordfence-extension-dttable-style', self::$plugin_url . 'css/dt_table.css');                        
            
            wp_enqueue_script('mainwp-wordfence-extension-admin-log', self::$plugin_url . 'js/mainwp-wfc-log.js');        
            wp_enqueue_script('mainwp-wordfence-extension-jquery-tmpl', self::$plugin_url . 'js/jquery.tmpl.min.js', array('jquery'));
            wp_enqueue_script('mainwp-wordfence-extension-jquery-colorbox', self::$plugin_url . 'js/jquery.colorbox-min.js', array('jquery'));
            wp_enqueue_script('mainwp-wordfence-extension-jquery-dataTables', self::$plugin_url . 'js/jquery.dataTables.min.js', array('jquery'));        
        }
        
        if (isset($_GET['page']) && $_GET['page'] == "managesites") {
            wp_enqueue_script('mainwp-wordfence-extension-admin-log', self::$plugin_url . 'js/mainwp-wfc-log.js');        
        }        
        
        MainWPWordfence::init();
        $wfc = new MainWPWordfence();
        $wfc->admin_init();
        $wfc_plugin = new MainWPWordfencePlugin();
        $wfc_plugin->admin_init();   
        $wfc_setting = new MainWPWordfenceSetting();
        $wfc_setting->admin_init();   
    }
    
    public function get_option($key, $default = '') {
        if (isset($this->option[$key]))
            return $this->option[$key];
        return $default;
    }
    public function set_option($key, $value) {
        $this->option[$key] = $value;
        return update_option($this->option_handle, $this->option);
    }
    
        
}


function mainwp_wfc_extension_autoload($class_name)
{
    $allowedLoadingTypes = array('class', 'page');

    foreach ($allowedLoadingTypes as $allowedLoadingType)
    {
        $class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace(basename(__FILE__), '', plugin_basename(__FILE__)) . $allowedLoadingType . DIRECTORY_SEPARATOR . $class_name . '.' . $allowedLoadingType . '.php';
        if (file_exists($class_file))
        {
            require_once($class_file);
        }
    }
}

if (function_exists('spl_autoload_register'))
{
    spl_autoload_register('mainwp_wfc_extension_autoload');
}
else
{
    function __autoload($class_name)
    {
        mainwp_wfc_extension_autoload($class_name);
    }
}

register_activation_hook(__FILE__, 'mainwp_wordfence_extension_activate');
register_deactivation_hook(__FILE__, 'mainwp_wordfence_extension_deactivate');
function mainwp_wordfence_extension_activate()
{   
    update_option('mainwp_wordfence_extension_activated', 'yes');
    $extensionActivator = new MainWPWordfenceExtensionActivator();
    $extensionActivator->activate();
}
function mainwp_wordfence_extension_deactivate()
{   
    $extensionActivator = new MainWPWordfenceExtensionActivator();
    $extensionActivator->deactivate();
}

class MainWPWordfenceExtensionActivator
{
    protected $mainwpMainActivated = false;
    protected $childEnabled = false;
    protected $childKey = false;
    protected $childFile;
    protected $plugin_handle = "mainwp-wordfence-extension";
    protected $product_id = "MainWP Wordfence Extension"; 
    protected $software_version = "0.0.7";    
  
    public function __construct()
    {
        $this->childFile = __FILE__;        
        add_filter('mainwp-getextensions', array(&$this, 'get_this_extension'));
        $this->mainwpMainActivated = apply_filters('mainwp-activated-check', false);

        if ($this->mainwpMainActivated !== false)
        {
            $this->activate_this_plugin();
        }
        else
        {
            add_action('mainwp-activated', array(&$this, 'activate_this_plugin'));
        }
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_notices', array(&$this, 'mainwp_error_notice'));
    }

    function admin_init() {
        if (get_option('mainwp_wordfence_extension_activated') == 'yes')
        {
            delete_option('mainwp_wordfence_extension_activated');
            wp_redirect(admin_url('admin.php?page=Extensions'));
            return;
        }        
    }
    
    function get_this_extension($pArray)
    {
        $pArray[] = array('plugin' => __FILE__, 'api' => $this->plugin_handle,  'mainwp' => true, 'callback' => array(&$this, 'settings'), 'apiManager' => true);
        return $pArray;
    }
 
    public function getMetaboxes($metaboxes)
    {
        if (!$this->childEnabled) return $metaboxes;

        if (!is_array($metaboxes)) $metaboxes = array();
        if (isset($_GET['page']) && $_GET['page'] == 'managesites') {
        $metaboxes[] = array('plugin' => $this->childFile, 'key' => $this->childKey, 'metabox_title' => __("Wordfence Status", "mainwp"), 'callback' => array('MainWPWordfence', 'renderMetabox'));
        }
        return $metaboxes;
    }
    
    function settings()
    {
        do_action('mainwp-pageheader-extensions', __FILE__);
        if ($this->childEnabled)
        { 
            MainWPWordfence::render();
        }
        else
        {
            ?><div class="mainwp_info-box-yellow"><strong><?php _e("The Extension has to be enabled to change the settings."); ?></strong></div><?php
        }
        do_action('mainwp-pagefooter-extensions', __FILE__);
    }
    
    function activate_this_plugin()
    {
        $this->mainwpMainActivated = apply_filters('mainwp-activated-check', $this->mainwpMainActivated);

        $this->childEnabled = apply_filters('mainwp-extension-enabled-check', __FILE__);
        if (!$this->childEnabled) return;

        $this->childKey = $this->childEnabled['key'];
        
        if (function_exists("mainwp_current_user_can")&& !mainwp_current_user_can("extension", "mainwp-wordfence-extension"))
            return;
        add_filter('mainwp-getmetaboxes', array(&$this, 'getMetaboxes'));
        new MainWPWordfenceExtension();
    }

    public function getChildKey()
    {
        return $this->childKey;
    }

    public function getChildFile()
    {
        return $this->childFile;
    }

    function mainwp_error_notice()
    {
        global $current_screen;
        if ($current_screen->parent_base == 'plugins' && $this->mainwpMainActivated == false)
        {
            echo '<div class="error"><p>MainWP Client Reports Extension ' . __('requires <a href="http://mainwp.com/" target="_blank">MainWP</a> Plugin to be activated in order to work. Please install and activate <a href="http://mainwp.com/" target="_blank">MainWP</a> first.') . '</p></div>';
        }
    }
    
    public function update_option($option_name, $option_value)
    {
        $success = add_option($option_name, $option_value, '', 'no');

         if (!$success)
         {
             $success = update_option($option_name, $option_value);
         }

         return $success;
    }  
    
    public function activate() {                          
        $options = array (  'product_id' => $this->product_id,
                            'activated_key' => 'Deactivated',  
                            'instance_id' => apply_filters('mainwp-extensions-apigeneratepassword', 12, false),                            
                            'software_version' => $this->software_version
                        );               
        $this->update_option($this->plugin_handle . "_APIManAdder", $options);
    } 
    
    public function deactivate() {                                 
        $this->update_option($this->plugin_handle . "_APIManAdder", '');
    } 	

}


$mainWPWordfenceExtensionActivator = new MainWPWordfenceExtensionActivator();
