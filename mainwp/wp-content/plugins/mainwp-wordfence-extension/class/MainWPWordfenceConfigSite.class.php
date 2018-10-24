<?php

class MainWPWordfenceConfigSite extends MainWPWordfenceConfig{
    public static $option = array();        
    public static $override = 0;
    public static $site_id = 0;    
    public static $cacheType = "";    
    public static $apiKey = "";
    public static $isPaid = 0;
    public function __construct($site_id = null) {
        if ($site_id) {
            self::$site_id = $site_id;
            $settings = MainWPWordfenceDB::Instance()->getSettingBy('site_id', $site_id);
            if ($settings) {
                self::$option = unserialize($settings->settings); 
                self::$override = $settings->override;
                self::$cacheType = $settings->cacheType;    
                self::$apiKey = $settings->apiKey;                    
                self::$isPaid = $settings->isPaid;  
            }
        }            
        if(!is_array(self::$option) || empty(self::$option)) 
            self::setDefaults();
    }        

    public static function set($key, $value) {
        self::$option[$key] = $value;            
    }      
    
    public static function get($key = null, $default = '', $site_id = 0) {
        if ($key == "isPaid")
            return self::$isPaid;
        else if ($key == "apiKey")
            return self::$apiKey;
        else if (isset(self::$option[$key]))
            return self::$option[$key];
        return $default;
    }
    
    public function is_override() {
        return self::$override ? true : false;
    }
        
    public function get_cacheType() {
        return self::$cacheType;
    }
    
    public static function load_settings() {            
        return self::$option;
    }
    
    public static function save_settings() {                    
        MainWPWordfenceDB::Instance()->updateSetting(array('site_id' => self::$site_id, 
            'settings' => serialize(self::$option))
        );
    }
        
    public static function setDefaults(){
		foreach(self::$securityLevels[2]['checkboxes'] as $key => $val){
                    if (in_array($key, self::$options_filter)) {
                        if(self::get($key) === false){
                                self::set($key, $val ? '1' : '0');
                        }
                    }
		}
		foreach(self::$securityLevels[2]['otherParams'] as $key => $val){
                    if (in_array($key, self::$options_filter)) {
			if(self::get($key) === false){
				self::set($key, $val);
			}
                    }
		}

                self::set('encKey', substr(MainWPWordfenceUtility::bigRandomHex(),0 ,16) );
		if(self::get('maxMem', false) === false ){
			self::set('maxMem', '256');
		}
                
		if(self::get('other_scanOutside', false) === false){
			self::set('other_scanOutside', 0);
		}
	}
		
	public static function getHTML($key){
		return htmlspecialchars(self::get($key));
	}
	public static function inc($key){
		$val = self::get($key, false);
		if(! $val){
			$val = 0;
		}
		self::set($key, $val + 1);
	}
	public static function f($key){
		echo esc_attr(self::get($key));
	}
	public static function cbp($key){
		if(self::get('isPaid') && self::get($key)){
			echo ' checked ';
		}
	}
	public static function cb($key){
		if(self::get($key)){
			echo ' checked ';
		}
	}
	public static function sel($key, $val, $isDefault = false){
		if((! self::get($key)) && $isDefault){ echo ' selected '; }
		if(self::get($key) == $val){ echo ' selected '; }
	}
		
	public static function haveAlertEmails(){
		$emails = self::getAlertEmails();
		return sizeof($emails) > 0 ? true : false;
	}
	public static function getAlertEmails(){
		$dat = explode(',', self::get('alertEmails'));
		$emails = array();
		foreach($dat as $email){
			if(preg_match('/\@/', $email)){
				$emails[] = trim($email);
			}
		}
		return $emails;
	}
	public static function getAlertLevel(){
		if(self::get('alertOn_warnings')){
			return 2;
		} else if(self::get('alertOn_critical')){
			return 1;
		} else {
			return 0;
		}
	} 
        
        public static function liveTrafficEnabled($cacheType = null){
            if( (! self::get('liveTrafficEnabled')) || $cacheType == 'falcon' || $cacheType == 'php'){ return false; }
            return true;
	}        
}
?>
