<?php
/*
Plugin-Name: Wordfence Security
Plugin-URI: http://www.wordfence.com/
Description: Wordfence Security - Anti-virus, Firewall and High Speed Cache
Author: Wordfence
Version: 5.2.1
Author-URI: http://www.wordfence.com/
*/

class MainWPWordfenceConfig {
        private static $option_handle = 'mainwp_wordfence_config_option';        
        public static $option = array();  
        public static $apiKeys = array();
        public static $isPaids = array();
        
        public static $options_filter = array(
            'alertEmails',
            'alertOn_adminLogin',
            'alertOn_block',
            'alertOn_critical',
            'alertOn_loginLockout',
            'alertOn_lostPasswdForm',
            'alertOn_nonAdminLogin',
            'alertOn_update',
            'alertOn_warnings',
            'alert_maxHourly',
            'autoUpdate',
            'firewallEnabled',
            'howGetIPs',
            'liveTrafficEnabled',
            'loginSec_blockAdminReg',
            'loginSec_countFailMins',
            'loginSec_disableAuthorScan',
            'loginSec_lockInvalidUsers',
            'loginSec_lockoutMins',
            'loginSec_maskLoginErrors',
            'loginSec_maxFailures',
            'loginSec_maxForgotPasswd',
            'loginSec_strongPasswds',
            'loginSec_userBlacklist',
            'loginSecurityEnabled',
            'other_scanOutside',
            'scan_exclude',
            'scansEnabled_comments',
            'scansEnabled_core',
            'scansEnabled_diskSpace',
            'scansEnabled_dns',
            'scansEnabled_fileContents',
            'scansEnabled_heartbleed',
            'scansEnabled_highSense',
            'scansEnabled_malware',
            'scansEnabled_oldVersions',
            'scansEnabled_options',
            'scansEnabled_passwds',
            'scansEnabled_plugins',
            'scansEnabled_posts',
            'scansEnabled_scanImages',
            'scansEnabled_themes',
            'scheduledScansEnabled',
            'securityLevel',
            'scheduleScan', // mainwp custom options
            'blockFakeBots',
            'neverBlockBG',
            'maxGlobalRequests',
            'maxGlobalRequests_action',     
            'maxRequestsCrawlers',
            'maxRequestsCrawlers_action',
            'max404Crawlers',
            'max404Crawlers_action',
            'maxRequestsHumans',
            'maxRequestsHumans_action',
            'max404Humans',
            'max404Humans_action',
            'maxScanHits',
            'maxScanHits_action',
            'blockedTime',      
            'liveTraf_ignorePublishers',
            'liveTraf_ignoreUsers',
            'liveTraf_ignoreIPs',
            'liveTraf_ignoreUA',
            
            'whitelisted',
            'bannedURLs',
            'other_hideWPVersion',
            'other_noAnonMemberComments',
            'other_scanComments',
            'other_pwStrengthOnUpdate',
            'other_WFNet',
            'maxMem',
            'maxExecutionTime',
            'actUpdateInterval',
            'debugOn',
            'deleteTablesOnDeact',
            'disableCookies',
            'startScansRemotely',
            'disableConfigCaching',
            'addCacheComment',
            //'isPaid',
            "advancedCommentScanning", 
            "checkSpamIP", 
            "spamvertizeCheck", 
            'scansEnabled_public'
        );

                
	public static $securityLevels = array(
		array( //level 0
			"checkboxes" => array(
				"alertOn_critical" => false,
				"alertOn_update" => false,
				"alertOn_warnings" => false,
				"alertOn_throttle" => false,
				"alertOn_block" => false,
				"alertOn_loginLockout" => false,
				"alertOn_lostPasswdForm" => false,
				"alertOn_adminLogin" => false,
				"alertOn_nonAdminLogin" => false,
				"liveTrafficEnabled" => true,
				"advancedCommentScanning" => false,
				"checkSpamIP" => false,
				"spamvertizeCheck" => false,
				"liveTraf_ignorePublishers" => true,
				//"perfLoggingEnabled" => false,
				"scheduledScansEnabled" => false,
				"scansEnabled_public" => false,
				"scansEnabled_heartbleed" => true,
				"scansEnabled_core" => false,
				"scansEnabled_themes" => false,
				"scansEnabled_plugins" => false,
				"scansEnabled_malware" => false,
				"scansEnabled_fileContents" => false,
				"scansEnabled_posts" => false,
				"scansEnabled_comments" => false,
				"scansEnabled_passwds" => false,
				"scansEnabled_diskSpace" => false,
				"scansEnabled_options" => false,
				"scansEnabled_dns" => false,
				"scansEnabled_scanImages" => false,
				"scansEnabled_highSense" => false,
				"scansEnabled_oldVersions" => false,
				"firewallEnabled" => false,
				"blockFakeBots" => false,
				"autoBlockScanners" => false,
				"loginSecurityEnabled" => false,
				"loginSec_lockInvalidUsers" => false,
				"loginSec_maskLoginErrors" => false,
				"loginSec_blockAdminReg" => false,
				"loginSec_disableAuthorScan" => false,
				"other_hideWPVersion" => false,
				"other_noAnonMemberComments" => false,
				"other_scanComments" => false,
				"other_pwStrengthOnUpdate" => false,
				"other_WFNet" => true,
				"other_scanOutside" => false,
				"deleteTablesOnDeact" => false,
				"autoUpdate" => false,
				"disableCookies" => false,
				"startScansRemotely" => false,
				"disableConfigCaching" => false,
				"addCacheComment" => false,
				"allowHTTPSCaching" => false,
				"debugOn" => false
			),
			"otherParams" => array(
				'securityLevel' => '0',
				"alertEmails" => "", "liveTraf_ignoreUsers" => "", "liveTraf_ignoreIPs" => "", "liveTraf_ignoreUA" => "",  "apiKey" => "", "maxMem" => '256', 'scan_exclude' => '', 'whitelisted' => '', 'bannedURLs' => '', 'maxExecutionTime' => '', 'howGetIPs' => '', 'actUpdateInterval' => '', 'alert_maxHourly' => 0, 'loginSec_userBlacklist' => '',
				"neverBlockBG" => "neverBlockVerified",
				"loginSec_countFailMins" => "5",
				"loginSec_lockoutMins" => "5",
				'loginSec_strongPasswds' => '',
				'loginSec_maxFailures' => "500",
				'loginSec_maxForgotPasswd' => "500",
				'maxGlobalRequests' => "DISABLED",
				'maxGlobalRequests_action' => "throttle",
				'maxRequestsCrawlers' => "DISABLED",
				'maxRequestsCrawlers_action' => "throttle",
				'maxRequestsHumans' => "DISABLED",
				'maxRequestsHumans_action' => "throttle",
				'max404Crawlers' => "DISABLED",
				'max404Crawlers_action' => "throttle",
				'max404Humans' => "DISABLED",
				'max404Humans_action' => "throttle",
				'maxScanHits' => "DISABLED",
				'maxScanHits_action' => "throttle",
				'blockedTime' => "300"
			)
		),
		array( //level 1
			"checkboxes" => array(
				"alertOn_critical" => true,
				"alertOn_update" => false,
				"alertOn_warnings" => false,
				"alertOn_throttle" => false,
				"alertOn_block" => true,
				"alertOn_loginLockout" => true,
				"alertOn_lostPasswdForm" => false,
				"alertOn_adminLogin" => true,
				"alertOn_nonAdminLogin" => false,
				"liveTrafficEnabled" => true,
				"advancedCommentScanning" => false,
				"checkSpamIP" => false,
				"spamvertizeCheck" => false,
				"liveTraf_ignorePublishers" => true,
				//"perfLoggingEnabled" => false,
				"scheduledScansEnabled" => true,
				"scansEnabled_public" => false,
				"scansEnabled_heartbleed" => true,
				"scansEnabled_core" => true,
				"scansEnabled_themes" => false,
				"scansEnabled_plugins" => false,
				"scansEnabled_malware" => true,
				"scansEnabled_fileContents" => true,
				"scansEnabled_posts" => true,
				"scansEnabled_comments" => true,
				"scansEnabled_passwds" => true,
				"scansEnabled_diskSpace" => true,
				"scansEnabled_options" => true,
				"scansEnabled_dns" => true,
				"scansEnabled_scanImages" => false,
				"scansEnabled_highSense" => false,
				"scansEnabled_oldVersions" => true,
				"firewallEnabled" => true,
				"blockFakeBots" => false,
				"autoBlockScanners" => true,
				"loginSecurityEnabled" => true,
				"loginSec_lockInvalidUsers" => false,
				"loginSec_maskLoginErrors" => true,
				"loginSec_blockAdminReg" => true,
				"loginSec_disableAuthorScan" => true,
				"other_hideWPVersion" => true,
				"other_noAnonMemberComments" => true,
				"other_scanComments" => true,
				"other_pwStrengthOnUpdate" => true,
				"other_WFNet" => true,
				"other_scanOutside" => false,
				"deleteTablesOnDeact" => false,
				"autoUpdate" => false,
				"disableCookies" => false,
				"startScansRemotely" => false,
				"disableConfigCaching" => false,
				"addCacheComment" => false,
				"allowHTTPSCaching" => false,
				"debugOn" => false
			),
			"otherParams" => array(
				'securityLevel' => '1',
				"alertEmails" => "", "liveTraf_ignoreUsers" => "", "liveTraf_ignoreIPs" => "", "liveTraf_ignoreUA" => "",  "apiKey" => "", "maxMem" => '256', 'scan_exclude' => '', 'whitelisted' => '', 'bannedURLs' => '', 'maxExecutionTime' => '', 'howGetIPs' => '', 'actUpdateInterval' => '', 'alert_maxHourly' => 0, 'loginSec_userBlacklist' => '',
				"neverBlockBG" => "neverBlockVerified",
				"loginSec_countFailMins" => "5",
				"loginSec_lockoutMins" => "5",
				'loginSec_strongPasswds' => 'pubs',
				'loginSec_maxFailures' => "50",
				'loginSec_maxForgotPasswd' => "50",
				'maxGlobalRequests' => "DISABLED",
				'maxGlobalRequests_action' => "throttle",
				'maxRequestsCrawlers' => "DISABLED",
				'maxRequestsCrawlers_action' => "throttle",
				'maxRequestsHumans' => "DISABLED",
				'maxRequestsHumans_action' => "throttle",
				'max404Crawlers' => "DISABLED",
				'max404Crawlers_action' => "throttle",
				'max404Humans' => "DISABLED",
				'max404Humans_action' => "throttle",
				'maxScanHits' => "DISABLED",
				'maxScanHits_action' => "throttle",
				'blockedTime' => "300"
			)
		),
		array( //level 2
			"checkboxes" => array(
				"alertOn_critical" => true,
				"alertOn_update" => false,
				"alertOn_warnings" => true,
				"alertOn_throttle" => false,
				"alertOn_block" => true,
				"alertOn_loginLockout" => true,
				"alertOn_lostPasswdForm" => true,
				"alertOn_adminLogin" => true,
				"alertOn_nonAdminLogin" => false,
				"liveTrafficEnabled" => true,
				"advancedCommentScanning" => false,
				"checkSpamIP" => false,
				"spamvertizeCheck" => false,
				"liveTraf_ignorePublishers" => true,
				//"perfLoggingEnabled" => false,
				"scheduledScansEnabled" => true,
				"scansEnabled_public" => false,
				"scansEnabled_heartbleed" => true,
				"scansEnabled_core" => true,
				"scansEnabled_themes" => false,
				"scansEnabled_plugins" => false,
				"scansEnabled_malware" => true,
				"scansEnabled_fileContents" => true,
				"scansEnabled_posts" => true,
				"scansEnabled_comments" => true,
				"scansEnabled_passwds" => true,
				"scansEnabled_diskSpace" => true,
				"scansEnabled_options" => true,
				"scansEnabled_dns" => true,
				"scansEnabled_scanImages" => false,
				"scansEnabled_highSense" => false,
				"scansEnabled_oldVersions" => true,
				"firewallEnabled" => true,
				"blockFakeBots" => false,
				"autoBlockScanners" => true,
				"loginSecurityEnabled" => true,
				"loginSec_lockInvalidUsers" => false,
				"loginSec_maskLoginErrors" => true,
				"loginSec_blockAdminReg" => true,
				"loginSec_disableAuthorScan" => true,
				"other_hideWPVersion" => true,
				"other_noAnonMemberComments" => true,
				"other_scanComments" => true,
				"other_pwStrengthOnUpdate" => true,
				"other_WFNet" => true,
				"other_scanOutside" => false,
				"deleteTablesOnDeact" => false,
				"autoUpdate" => false,
				"disableCookies" => false,
				"startScansRemotely" => false,
				"disableConfigCaching" => false,
				"addCacheComment" => false,
				"allowHTTPSCaching" => false,
				"debugOn" => false
			),
			"otherParams" => array(
				'securityLevel' => '2',
				"alertEmails" => "", "liveTraf_ignoreUsers" => "", "liveTraf_ignoreIPs" => "", "liveTraf_ignoreUA" => "",  "apiKey" => "", "maxMem" => '256', 'scan_exclude' => '', 'whitelisted' => '', 'bannedURLs' => '', 'maxExecutionTime' => '', 'howGetIPs' => '', 'actUpdateInterval' => '', 'alert_maxHourly' => 0, 'loginSec_userBlacklist' => '',
				"neverBlockBG" => "neverBlockVerified",
				"loginSec_countFailMins" => "240",
				"loginSec_lockoutMins" => "240",
				'loginSec_strongPasswds' => 'pubs',
				'loginSec_maxFailures' => "20",
				'loginSec_maxForgotPasswd' => "20",
				'maxGlobalRequests' => "DISABLED",
				'maxGlobalRequests_action' => "throttle",
				'maxRequestsCrawlers' => "DISABLED",
				'maxRequestsCrawlers_action' => "throttle",
				'maxRequestsHumans' => "DISABLED",
				'maxRequestsHumans_action' => "throttle",
				'max404Crawlers' => "DISABLED",
				'max404Crawlers_action' => "throttle",
				'max404Humans' => "DISABLED",
				'max404Humans_action' => "throttle",
				'maxScanHits' => "DISABLED",
				'maxScanHits_action' => "throttle",
				'blockedTime' => "300"
			)
		),
		array( //level 3
			"checkboxes" => array(
				"alertOn_critical" => true,
				"alertOn_update" => false,
				"alertOn_warnings" => true,
				"alertOn_throttle" => false,
				"alertOn_block" => true,
				"alertOn_loginLockout" => true,
				"alertOn_lostPasswdForm" => true,
				"alertOn_adminLogin" => true,
				"alertOn_nonAdminLogin" => false,
				"liveTrafficEnabled" => true,
				"advancedCommentScanning" => false,
				"checkSpamIP" => false,
				"spamvertizeCheck" => false,
				"liveTraf_ignorePublishers" => true,
				//"perfLoggingEnabled" => false,
				"scheduledScansEnabled" => true,
				"scansEnabled_public" => false,
				"scansEnabled_heartbleed" => true,
				"scansEnabled_core" => true,
				"scansEnabled_themes" => false,
				"scansEnabled_plugins" => false,
				"scansEnabled_malware" => true,
				"scansEnabled_fileContents" => true,
				"scansEnabled_posts" => true,
				"scansEnabled_comments" => true,
				"scansEnabled_passwds" => true,
				"scansEnabled_diskSpace" => true,
				"scansEnabled_options" => true,
				"scansEnabled_dns" => true,
				"scansEnabled_scanImages" => false,
				"scansEnabled_highSense" => false,
				"scansEnabled_oldVersions" => true,
				"firewallEnabled" => true,
				"blockFakeBots" => false,
				"autoBlockScanners" => true,
				"loginSecurityEnabled" => true,
				"loginSec_lockInvalidUsers" => false,
				"loginSec_maskLoginErrors" => true,
				"loginSec_blockAdminReg" => true,
				"loginSec_disableAuthorScan" => true,
				"other_hideWPVersion" => true,
				"other_noAnonMemberComments" => true,
				"other_scanComments" => true,
				"other_pwStrengthOnUpdate" => true,
				"other_WFNet" => true,
				"other_scanOutside" => false,
				"deleteTablesOnDeact" => false,
				"autoUpdate" => false,
				"disableCookies" => false,
				"startScansRemotely" => false,
				"disableConfigCaching" => false,
				"addCacheComment" => false,
				"allowHTTPSCaching" => false,
				"debugOn" => false
			),
			"otherParams" => array(
				'securityLevel' => '3',
				"alertEmails" => "", "liveTraf_ignoreUsers" => "", "liveTraf_ignoreIPs" => "", "liveTraf_ignoreUA" => "",  "apiKey" => "", "maxMem" => '256', 'scan_exclude' => '', 'whitelisted' => '', 'bannedURLs' => '', 'maxExecutionTime' => '', 'howGetIPs' => '', 'actUpdateInterval' => '', 'alert_maxHourly' => 0, 'loginSec_userBlacklist' => '',
				"neverBlockBG" => "neverBlockVerified",
				"loginSec_countFailMins" => "1440",
				"loginSec_lockoutMins" => "1440",
				'loginSec_strongPasswds' => 'all',
				'loginSec_maxFailures' => "10",
				'loginSec_maxForgotPasswd' => "10",
				'maxGlobalRequests' => "960",
				'maxGlobalRequests_action' => "throttle",
				'maxRequestsCrawlers' => "960",
				'maxRequestsCrawlers_action' => "throttle",
				'maxRequestsHumans' => "60",
				'maxRequestsHumans_action' => "throttle",
				'max404Crawlers' => "60",
				'max404Crawlers_action' => "throttle",
				'max404Humans' => "60",
				'max404Humans_action' => "throttle",
				'maxScanHits' => "30",
				'maxScanHits_action' => "throttle",
				'blockedTime' => "1800"
			)
		),
		array( //level 4
			"checkboxes" => array(
				"alertOn_critical" => true,
				"alertOn_update" => false,
				"alertOn_warnings" => true,
				"alertOn_throttle" => false,
				"alertOn_block" => true,
				"alertOn_loginLockout" => true,
				"alertOn_lostPasswdForm" => true,
				"alertOn_adminLogin" => true,
				"alertOn_nonAdminLogin" => false,
				"liveTrafficEnabled" => true,
				"advancedCommentScanning" => false,
				"checkSpamIP" => false,
				"spamvertizeCheck" => false,
				"liveTraf_ignorePublishers" => true,
				//"perfLoggingEnabled" => false,
				"scheduledScansEnabled" => true,
				"scansEnabled_public" => false,
				"scansEnabled_heartbleed" => true,
				"scansEnabled_core" => true,
				"scansEnabled_themes" => false,
				"scansEnabled_plugins" => false,
				"scansEnabled_malware" => true,
				"scansEnabled_fileContents" => true,
				"scansEnabled_posts" => true,
				"scansEnabled_comments" => true,
				"scansEnabled_passwds" => true,
				"scansEnabled_diskSpace" => true,
				"scansEnabled_options" => true,
				"scansEnabled_dns" => true,
				"scansEnabled_scanImages" => false,
				"scansEnabled_highSense" => false,
				"scansEnabled_oldVersions" => true,
				"firewallEnabled" => true,
				"blockFakeBots" => true,
				"autoBlockScanners" => true,
				"loginSecurityEnabled" => true,
				"loginSec_lockInvalidUsers" => true,
				"loginSec_maskLoginErrors" => true,
				"loginSec_blockAdminReg" => true,
				"loginSec_disableAuthorScan" => true,
				"other_hideWPVersion" => true,
				"other_noAnonMemberComments" => true,
				"other_scanComments" => true,
				"other_pwStrengthOnUpdate" => true,
				"other_WFNet" => true,
				"other_scanOutside" => false,
				"deleteTablesOnDeact" => false,
				"autoUpdate" => false,
				"disableCookies" => false,
				"startScansRemotely" => false,
				"disableConfigCaching" => false,
				"addCacheComment" => false,
				"allowHTTPSCaching" => false,
				"debugOn" => false
			),
			"otherParams" => array(
				'securityLevel' => '4',
				"alertEmails" => "", "liveTraf_ignoreUsers" => "", "liveTraf_ignoreIPs" => "", "liveTraf_ignoreUA" => "",  "apiKey" => "", "maxMem" => '256', 'scan_exclude' => '', 'whitelisted' => '', 'bannedURLs' => '', 'maxExecutionTime' => '', 'howGetIPs' => '', 'actUpdateInterval' => '', 'alert_maxHourly' => 0, 'loginSec_userBlacklist' => '',
				"neverBlockBG" => "neverBlockVerified",
				"loginSec_countFailMins" => "1440",
				"loginSec_lockoutMins" => "1440",
				'loginSec_strongPasswds' => 'all',
				'loginSec_maxFailures' => "5",
				'loginSec_maxForgotPasswd' => "5",
				'maxGlobalRequests' => "960",
				'maxGlobalRequests_action' => "throttle",
				'maxRequestsCrawlers' => "960",
				'maxRequestsCrawlers_action' => "throttle",
				'maxRequestsHumans' => "30",
				'maxRequestsHumans_action' => "block",
				'max404Crawlers' => "30",
				'max404Crawlers_action' => "block",
				'max404Humans' => "60",
				'max404Humans_action' => "block",
				'maxScanHits' => "10",
				'maxScanHits_action' => "block",
				'blockedTime' => "7200"
			)
		)
	);
        
        
        public function __construct($site_ids = array()) {
            $settings = MainWPWordfenceDB::Instance()->getSettings($site_ids);    
//            error_log(print_r($site_ids, true));
//            error_log(print_r($settings, true));
            foreach($settings as $setting) {
                if ($setting->status != 0) {
                    self::$isPaids[$setting->site_id] = $setting->isPaid;
                    self::$apiKeys[$setting->site_id] = $setting->apiKey;                    
                }
            }
            
            self::$option = get_option(self::$option_handle, false);
            if(self::$option === false) 
                self::setDefaults();
        }
        
        public function get_isPaids() {
            return self::$isPaids;
        }
        
        public function get_apiKeys() {
            return self::$apiKeys;
        }
        
        public static function load_settings() {   
            self::$option = get_option(self::$option_handle, false);
            if(self::$option === false) 
                self::setDefaults();
            return self::$option;
        }
                
        public static function get($key = null, $default = '', $site_id = 0) {
            if ($site_id) {
                if ($key == "isPaid" && isset(self::$isPaids[$site_id])) 
                    return self::$isPaids[$site_id];
                else if ($key == "apiKey" && isset(self::$apiKeys[$site_id]))
                    return self::$apiKeys[$site_id];
            } else if (isset(self::$option[$key]))
                return self::$option[$key];
            return $default;
        }

        public static function set($key, $value) {
            self::$option[$key] = $value;
            return update_option(self::$option_handle, self::$option);
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
	public static function parseOptions(){
		$ret = array();
		foreach(self::$securityLevels[2]['checkboxes'] as $key => $val){ //value is not used. We just need the keys for validation
                    if (in_array($key, self::$options_filter)) {
			$ret[$key] = isset($_POST[$key]) ? '1' : '0';
                    }
		}
		foreach(self::$securityLevels[2]['otherParams'] as $key => $val){
                    if (in_array($key, self::$options_filter)) {
			if(isset($_POST[$key])){
				$ret[$key] = $_POST[$key];
			} else {
				//error_log("Missing options param \"$key\" when parsing parameters.");
			}
                    }
		}
                
                $ret['scheduleScan'] = isset($_POST['scheduleScan']) ? $_POST['scheduleScan'] : 0;
             
		/* for debugging only:
		foreach($_POST as $key => $val){
			if($key != 'action' && $key != 'nonce' && (! array_key_exists($key, self::$checkboxes)) && (! array_key_exists($key, self::$otherParams)) ){
				error_log("Unrecognized option: $key");
			}
		}
		*/
		return $ret;
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
        
        public function get_AlertEmails(){
            self::getAlertEmails();		
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
