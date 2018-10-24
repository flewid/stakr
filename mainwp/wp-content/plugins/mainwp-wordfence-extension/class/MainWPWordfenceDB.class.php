<?php
class MainWPWordfenceDB
{    
    private $mainwp_wordfence_db_version = "1.7";        
    private $table_prefix;
    
    //Singleton
    private static $instance = null;
    
    static function Instance()
    {
        if (MainWPWordfenceDB::$instance == null) {
            MainWPWordfenceDB::$instance = new MainWPWordfenceDB();
        }
        return MainWPWordfenceDB::$instance;
    }
    //Constructor
    function __construct()
    {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . "mainwp_"; 
        
    }
	
    function tableName($suffix)
    {
        return $this->table_prefix . $suffix;
    }
		
    //Support old & new versions of wordpress (3.9+)
    public static function use_mysqli()
    {
        /** @var $wpdb wpdb */
        if (!function_exists( 'mysqli_connect' ) ) return false;

        global $wpdb;
        return ($wpdb->dbh instanceof mysqli);
    }
	
    //Installs new DB
    function install()
    {
        global $wpdb;        
        $currentVersion = get_site_option('mainwp_wordfence_db_version');                
        if ($currentVersion == $this->mainwp_wordfence_db_version) return;    
        
        $charset_collate = $wpdb->get_charset_collate();        
        $sql = array();
        
        $tbl = 'CREATE TABLE `' . $this->tableName('wordfence') . '` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`site_id` int(11) NOT NULL,
`status` tinyint(1) DEFAULT 0,
`apiKey` text NOT NULL,
`isPaid` tinyint(1) DEFAULT 0,
`lastscan` int(11) DEFAULT 0,
`settings` text NOT NULL,
`cacheType` VARCHAR(10),
`override` tinyint(1) NOT NULL DEFAULT 0';
        if ($currentVersion == '')
                    $tbl .= ',
PRIMARY KEY  (`id`)  ';
        $tbl .= ') ' . $charset_collate;
        $sql[] = $tbl;
        
        error_reporting(0); // make sure to disable any error output
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        foreach ($sql as $query)
        {
            dbDelta($query);
        }        
        
//        global $wpdb;
//        echo $wpdb->last_error;
//        exit(); 
        update_option('mainwp_wordfence_db_version', $this->mainwp_wordfence_db_version);
    }
    public function updateSetting($setting)
    {
         /** @var $wpdb wpdb */
        global $wpdb;        
        $id = isset($setting['id']) ? $setting['id'] : 0;		        
        $site_id = isset($setting['site_id']) ? $setting['site_id'] : 0;		        

        if ($id) {
            if ($wpdb->update($this->tableName('wordfence'), $setting, array('id' => intval($id)))) 				
                return $this->getSettingBy('id', $id);      			
        } else if ($site_id) {
            $current = $this->getSettingBy('site_id', $site_id);
            //error_log(print_r($current, true));
            if ($current) {       
                //error_log(print_r($setting, true));
                if ($wpdb->update($this->tableName('wordfence'), $setting, array('site_id' => intval($site_id)))) {				                                                    
                    return $this->getSettingBy('site_id', $site_id); 
                }
            } else {
                if($wpdb->insert($this->tableName('wordfence'), $setting)) 
                    return $this->getSettingBy('id', $wpdb->insert_id);     
            }            
        } else if($wpdb->insert($this->tableName('wordfence'), $setting)) {
            return $this->getSettingBy('id', $wpdb->insert_id);     
        }        		
        return false;
    }
    
    public function getSettingBy($by = 'id', $value = null) {
        global $wpdb;
        
        if (empty($by) || empty($value))
            return null;
        
        $sql = "";
        if ($by == 'id') {
            $sql = $wpdb->prepare("SELECT * FROM " . $this->tableName('wordfence') . " WHERE `id`=%d ", $value);
        } else if ($by == 'site_id') {
            $sql = $wpdb->prepare("SELECT * FROM " . $this->tableName('wordfence') . " WHERE `site_id` = %d ", $value);
        }         
        
        $setting = null;
        if (!empty($sql))
            $setting = $wpdb->get_row($sql);                
        return $setting;
    }
    
    public function getSettings($site_ids = array()) {
        global $wpdb;        
        if (!is_array($site_ids) || count($site_ids) <= 0)
            return array();
        $str_site_ids = implode(",", $site_ids);        
        $sql = "SELECT * FROM " . $this->tableName('wordfence') . " WHERE `site_id` IN (" . $str_site_ids . ") ";                
        return  $wpdb->get_results($sql);                        
    }
    
    protected function escape($data)
    {
        /** @var $wpdb wpdb */
        global $wpdb;
        if (function_exists('esc_sql')) return esc_sql($data);
        else return $wpdb->escape($data);
    }    
    
    public function query($sql)
    {
        if ($sql == null) return false;
        /** @var $wpdb wpdb */
        global $wpdb;
        $result = @self::_query($sql, $wpdb->dbh);

        if (!$result || (@self::num_rows($result) == 0)) return false;
        return $result;
    }	
	
    public static function _query($query, $link)
    {
        if (self::use_mysqli())
        {
            return mysqli_query($link, $query);
        }
        else
        {
            return mysql_query($query, $link);
        }
    }

    public static function fetch_object($result)
    {
        if (self::use_mysqli())
        {
            return mysqli_fetch_object($result);
        }
        else
        {
            return mysql_fetch_object($result);
        }
    }

    public static function free_result($result)
    {
        if (self::use_mysqli())
        {
            return mysqli_free_result($result);
        }
        else
        {
            return mysql_free_result($result);
        }
    }

    public static function data_seek($result, $offset)
    {
        if (self::use_mysqli())
        {
            return mysqli_data_seek($result, $offset);
        }
        else
        {
            return mysql_data_seek($result, $offset);
        }
    }

    public static function fetch_array($result, $result_type = null)
    {
        if (self::use_mysqli())
        {
            return mysqli_fetch_array($result, ($result_type == null ? MYSQLI_BOTH : $result_type));
        }
        else
        {
            return mysql_fetch_array($result, ($result_type == null ? MYSQL_BOTH : $result_type));
        }
    }

    public static function num_rows($result)
    {
        if (self::use_mysqli())
        {
            return mysqli_num_rows($result);
        }
        else
        {
            return mysql_num_rows($result);
        }
    }

    public static function is_result($result)
    {
        if (self::use_mysqli())
        {
            return ($result instanceof mysqli_result);
        }
        else
        {
            return is_resource($result);
        }
    }
	
    public function getResultsResult($sql)
    {
        if ($sql == null) return null;
        /** @var $wpdb wpdb */
        global $wpdb;
        return $wpdb->get_results($sql, OBJECT_K);
    }
}