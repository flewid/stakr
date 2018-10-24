<?php
/*
Plugin Name: MainWP Comments Extension
Plugin URI: http://extensions.mainwp.com
Description: MainWP Comments Extension is an extension for the MainWP plugin that enables you to manage comments on your child sites.
Version: 1.0
Author: MainWP
Author URI: http://mainwp.com
Icon URI: https://extensions.mainwp.com/wp-content/uploads/2014/02/mainwp-comments-extension-new.png
Documentation URI: http://docs.mainwp.com/category/mainwp-extensions/mainwp-comments-extension/
*/

if (!defined('MAINWP_COMMENTS_PLUGIN_FILE')) {
    define('MAINWP_COMMENTS_PLUGIN_FILE', __FILE__);
}

class MainWPCommentsExtension
{
    public static $instance = null;
    public  $plugin_handle = "mainwp-comments-extension";
    protected $plugin_url;
    private $plugin_slug;

    protected $mainWPComment;

    static function Instance()
    {
        if (MainWPCommentsExtension::$instance == null) MainWPCommentsExtension::$instance = new MainWPCommentsExtension();
        return MainWPCommentsExtension::$instance;
    }

    public function __construct()
    {
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->plugin_slug = plugin_basename(__FILE__);

        add_action('init', array(&$this, 'init'));
        add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);

        $this->mainWPComment = new MainWPComment();   
        $this->mainWPComment->init();
        add_action('admin_init', array(&$this, 'admin_init'));
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
        $this->mainWPComment->init_ajax();
        wp_enqueue_style('mainwp-comments-extension-css', $this->plugin_url . 'css/mainwp-comments.css');
        wp_enqueue_script('mainwp-comments-extension-js', $this->plugin_url . 'js/mainwp-comments.js');

        wp_localize_script('mainwp-comments-extension-js', 'mainwp_comments_security_nonces', $this->mainWPComment->security_nonces);
    }
}


function mainwp_comments_extension_autoload($class_name)
{
    $allowedLoadingTypes = array('class', 'page', 'widget');

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
    spl_autoload_register('mainwp_comments_extension_autoload');
}
else
{
    function __autoload($class_name)
    {
        mainwp_comments_extension_autoload($class_name);
    }
}


register_activation_hook(__FILE__, 'mainwp_comments_extension_activate');
register_deactivation_hook(__FILE__, 'mainwp_comments_extension_deactivate');

function mainwp_comments_extension_activate()
{   
    update_option('mainwp_comments_extension_activated', 'yes');
    $extensionActivator = new MainWPCommentsExtensionActivator();
    $extensionActivator->activate();
	$plugin_slug = plugin_basename(__FILE__);  	
	do_action('mainwp_enable_extension', $plugin_slug);
}

function mainwp_comments_extension_deactivate()
{   
    $extensionActivator = new MainWPCommentsExtensionActivator();
    $extensionActivator->deactivate();
}


class MainWPCommentsExtensionActivator
{
    protected $mainwpMainActivated = false;
    protected $childEnabled = false;
    protected $childKey = false;
    protected $childFile;
    protected $plugin_handle = "mainwp-comments-extension";
    protected $product_id = "MainWP Comments Extension"; 
    protected $software_version = "1.0"; 
    
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
        if (get_option('mainwp_comments_extension_activated') == 'yes')
        {
            delete_option('mainwp_comments_extension_activated');
            wp_redirect(admin_url('admin.php?page=Extensions'));
            return;
        }        
    }
    
    public function getMetaboxes($metaboxes)
    {
        if (!$this->childEnabled) return $metaboxes;

        if (!is_array($metaboxes)) $metaboxes = array();
        $metaboxes[] = array('plugin' => $this->childFile, 'key' => $this->childKey, 'metabox_title' => MainWPRecentComments::getName(), 'callback' => array(MainWPRecentComments::getClassName(), 'render'));
        return $metaboxes;
    }

    function get_this_extension($pArray)
    {
        $pArray[] = array('plugin' => __FILE__, 'api' => $this->plugin_handle, 'mainwp' => true, 'direct_page' => 'CommentBulkManage', 'apiManager' => true);
        return $pArray;
    }

    function activate_this_plugin()
    {
        $this->mainwpMainActivated = apply_filters('mainwp-activated-check', $this->mainwpMainActivated);

        $this->childEnabled = apply_filters('mainwp-extension-enabled-check', __FILE__);
        if (!$this->childEnabled) return;

        $this->childKey = $this->childEnabled['key'];
        if (function_exists("mainwp_current_user_can") && !mainwp_current_user_can("extension", "mainwp-comments-extension")) 
            return;  
        add_filter('mainwp-getmetaboxes', array(&$this, 'getMetaboxes')); 
        new MainWPCommentsExtension();
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
            echo '<div class="error"><p>MainWP Comments Extension ' . __('requires <a href="http://mainwp.com/" target="_blank">MainWP</a> Plugin to be activated in order to work. Please install and activate <a href="http://mainwp.com/" target="_blank">MainWP</a> first.') . '</p></div>';
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

$mainwpCommentsExtensionActivator = new MainWPCommentsExtensionActivator();
